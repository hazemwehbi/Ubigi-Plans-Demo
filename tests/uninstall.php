<?php
/** Tests destructifs du cycle de vie, sur une installation WordPress jetable uniquement. */
$root=getenv('UPD_WP_ROOT');
if(!$root || !is_file($root.'/wp-load.php')) { exit("Set UPD_WP_ROOT to a disposable installation.\n"); }
require $root.'/wp-load.php';
require_once ABSPATH.'wp-admin/includes/plugin.php';
if(!class_exists('UPD_Plugin')) { throw new RuntimeException('Activate the plugin first.'); }
function verify_lifecycle($ok,$name) { if(!$ok) { throw new RuntimeException('FAIL: '.$name); } echo 'PASS: '.$name."\n"; }
$pages=get_option('upd_pages');
$unrelated=wp_insert_post(array('post_type'=>'page','post_status'=>'publish','post_title'=>'Unrelated QA page'));
$settings=UPD_Plugin::options(); $settings['delete_data']=0; update_option('upd_settings',$settings,false);
update_option('upd_ai_key','temporary-test-key',false);
wp_schedule_single_event(time()+3600,'upd_webhook',array(1));
define('WP_UNINSTALL_PLUGIN','ubigi-plans-demo/ubigi-plans-demo.php');
include $root.'/wp-content/plugins/ubigi-plans-demo/uninstall.php';
verify_lifecycle((int)wp_count_posts('upd_plan')->publish===12,'default uninstall preserves plans');
verify_lifecycle(get_option('upd_pages')===$pages,'default uninstall preserves created pages');
verify_lifecycle(!get_option('upd_ai_key') && !wp_next_scheduled('upd_webhook',array(1)),'uninstall removes secrets and queued jobs');
$settings['delete_data']=1; update_option('upd_settings',$settings,false);
include $root.'/wp-content/plugins/ubigi-plans-demo/uninstall.php';
global $wpdb;
verify_lifecycle((int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type=%s",'upd_plan'))===0,'opt-in uninstall deletes plans');
verify_lifecycle((int)wp_count_terms(array('taxonomy'=>'upd_destination','hide_empty'=>false))===0,'opt-in uninstall deletes destinations');
verify_lifecycle(!get_post($pages['plans']) && !get_post($pages['countries']),'opt-in uninstall deletes owned pages');
verify_lifecycle((bool)get_post($unrelated),'uninstall preserves unrelated pages');
wp_delete_post($unrelated,true);
verify_lifecycle(!get_option('upd_installed'),'opt-in uninstall removes installation marker');
// Recréer les exemples pour la vérification visuelle ou les prochains tests.
UPD_Plugin::activate();
echo "Lifecycle checks complete; demo re-seeded.\n";
