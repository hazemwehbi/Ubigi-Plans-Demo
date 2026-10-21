<?php
/**
 * Plugin Name: Ubigi Plans Demo
 * Description: Catalogue pédagogique de forfaits internationaux avec API REST et intégrations éditoriales facultatives. Aucun paiement ni connexion eSIM réelle.
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Text Domain: ubigi-plans-demo
 * License: GPL-2.0-or-later
 */
defined( 'ABSPATH' ) || exit;
define( 'UPD_VERSION', '1.0.0' );
define( 'UPD_FILE', __FILE__ );
require_once __DIR__ . '/includes/class-upd-plugin.php';
require_once __DIR__ . '/includes/class-upd-api.php';
require_once __DIR__ . '/includes/class-upd-admin.php';
register_activation_hook( __FILE__, array( 'UPD_Plugin', 'activate' ) );
register_deactivation_hook(
	__FILE__,
	static function () {
		wp_unschedule_hook( 'upd_webhook' );
		flush_rewrite_rules();
	}
);
UPD_Plugin::boot();
