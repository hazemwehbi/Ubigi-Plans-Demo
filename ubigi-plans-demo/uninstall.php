<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;
// Toujours retirer les secrets et tâches ; supprimer les contenus uniquement sur option.
wp_unschedule_hook( 'upd_webhook' );
delete_option( 'upd_ai_key' );
delete_option( 'upd_webhook_secret' );
$settings = get_option( 'upd_settings', array() );
if ( empty( $settings['delete_data'] ) ) {
	return; }
require_once __DIR__ . '/includes/class-upd-plugin.php';
UPD_Plugin::register();
foreach ( get_posts(
	array(
		'post_type'   => 'upd_plan',
		'post_status' => array_keys( get_post_stati() ),
		'numberposts' => -1,
		'fields'      => 'ids',
	)
) as $id ) {
	wp_delete_post( $id, true ); }
foreach ( get_terms(
	array(
		'taxonomy'   => 'upd_destination',
		'hide_empty' => false,
		'fields'     => 'ids',
	)
) as $id ) {
	wp_delete_term( $id, 'upd_destination' ); }
foreach ( get_option( 'upd_pages', array() ) as $id ) {
	wp_delete_post( $id, true ); }
foreach ( array( 'upd_settings', 'upd_logs', 'upd_pages', 'upd_installed', 'upd_cache_version' ) as $key ) {
	delete_option( $key ); }
