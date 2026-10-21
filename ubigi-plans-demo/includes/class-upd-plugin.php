<?php
defined( 'ABSPATH' ) || exit;

final class UPD_Plugin {
	public static function boot() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action(
			'init',
			static function () {
				load_plugin_textdomain( 'ubigi-plans-demo', false, dirname( plugin_basename( UPD_FILE ) ) . '/languages' );
			}
		);
		add_action( 'rest_api_init', array( 'UPD_API', 'register' ) );
		add_action( 'admin_menu', array( 'UPD_Admin', 'menu' ) );
		add_action( 'add_meta_boxes', array( 'UPD_Admin', 'boxes' ) );
		add_action( 'save_post_upd_plan', array( 'UPD_Admin', 'save' ), 10, 2 );
		add_action( 'save_post_upd_plan', array( __CLASS__, 'changed' ), 20, 2 );
		add_action( 'deleted_post', array( __CLASS__, 'invalidate' ) );
		add_action( 'set_object_terms', array( __CLASS__, 'invalidate' ) );
		add_action( 'created_upd_destination', array( __CLASS__, 'invalidate' ) );
		add_action( 'edited_upd_destination', array( __CLASS__, 'invalidate' ) );
		add_action( 'delete_upd_destination', array( __CLASS__, 'invalidate' ) );
		foreach ( array( 'added_post_meta', 'updated_post_meta', 'deleted_post_meta' ) as $hook ) {
			add_action( $hook, array( __CLASS__, 'meta_changed' ), 10, 4 ); }
		add_action( 'upd_webhook', array( __CLASS__, 'webhook' ), 10, 1 );
		add_shortcode( 'ubigi_plans', array( __CLASS__, 'catalogue' ) );
		add_shortcode( 'ubigi_countries', array( __CLASS__, 'countries' ) );
		add_filter( 'the_content', array( __CLASS__, 'detail' ) );
	}
	public static function options() {
		return wp_parse_args(
			get_option( 'upd_settings', array() ),
			array(
				'currency'        => 'EUR',
				'cache'           => 300,
				'per_page'        => 9,
				'ai'              => 0,
				'model'           => 'gpt-4.1-mini',
				'webhook'         => '',
				'webhook_enabled' => 0,
				'delete_data'     => 0,
			)
		); }
	public static function register() {
		register_post_type(
			'upd_plan',
			array(
				'labels'          => array(
					'name'          => __( 'Plans', 'ubigi-plans-demo' ),
					'singular_name' => __( 'Plan', 'ubigi-plans-demo' ),
				),
				'public'          => true,
				'show_in_menu'    => 'upd',
				'show_in_rest'    => false,
				'supports'        => array( 'title', 'editor', 'revisions' ),
				'rewrite'         => array( 'slug' => 'data-plan' ),
				'capability_type' => 'post',
				'map_meta_cap'    => true,
			)
		);
		register_taxonomy(
			'upd_destination',
			'upd_plan',
			array(
				'labels'            => array( 'name' => __( 'Countries & regions', 'ubigi-plans-demo' ) ),
				'public'            => true,
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => false,
				'rewrite'           => false,
			)
		);
	}
	public static function activate( $network_wide = false ) {
		global $wp_version;
		if ( $network_wide ) {
			wp_die( esc_html__( 'Activate this plugin separately on each site. Network activation is not supported.', 'ubigi-plans-demo' ) ); }
		if ( version_compare( PHP_VERSION, '8.1', '<' ) || version_compare( $wp_version, '6.5', '<' ) ) {
			wp_die( esc_html__( 'Requires WordPress 6.5 and PHP 8.1 or newer.', 'ubigi-plans-demo' ) ); }
		self::register();
		if ( ! get_option( 'upd_installed' ) ) {
			add_option( 'upd_settings', self::options(), '', false );
			$destinations = array(
				'fr'    => 'France',
				'jp'    => 'Japan',
				'us'    => 'United States',
				'eu'    => 'Europe',
				'world' => 'Global',
				'ma'    => 'Morocco',
			);
			foreach ( $destinations as $slug => $name ) {
				if ( ! term_exists( $slug, 'upd_destination' ) ) {
						wp_insert_term( $name, 'upd_destination', array( 'slug' => $slug ) ); }
			}
			$seed = array( array( 'fr', 5, 15, 9, 'local' ), array( 'fr', 10, 30, 15, 'local' ), array( 'jp', 3, 7, 7, 'local' ), array( 'jp', 10, 30, 18, 'local' ), array( 'us', 5, 15, 12, 'local' ), array( 'us', 20, 30, 29, 'local' ), array( 'eu', 10, 30, 22, 'regional' ), array( 'eu', 25, 30, 39, 'regional' ), array( 'world', 5, 30, 35, 'global' ), array( 'world', 10, 30, 59, 'global' ), array( 'ma', 3, 7, 8, 'local' ), array( 'ma', 10, 30, 21, 'local' ) );
			foreach ( $seed as $row ) {
				list($code, $gb, $days, $price, $type) = $row;
				$key                                   = $code . '-' . $gb . '-' . $days;
				$existing                              = get_posts(
					array(
						'post_type'   => 'upd_plan',
						'post_status' => 'any',
						'meta_key'    => '_upd_seed',
						'meta_value'  => $key,
						'numberposts' => 1,
					)
				);
				if ( $existing ) {
					continue; }
				$id = wp_insert_post(
					array(
						'post_type'    => 'upd_plan',
						'post_status'  => 'publish',
						'post_title'   => $destinations[ $code ] . ' ' . $gb . ' GB',
						'post_content' => 'Demo travel data package for ' . $destinations[ $code ] . '. Explore the destination with a simple prepaid data allowance. This is sample content; no connectivity or purchase is provided.',
					),
					true
				);
				if ( is_wp_error( $id ) ) {
					wp_die( esc_html( $id->get_error_message() ) ); }
				foreach ( array(
					'gb'       => $gb,
					'days'     => $days,
					'price'    => $price,
					'type'     => $type,
					'currency' => 'EUR',
					'seed'     => $key,
				) as $k => $v ) {
					update_post_meta( $id, '_upd_' . $k, $v ); }
				wp_set_object_terms( $id, $code, 'upd_destination' );
			}
			$pages = get_option( 'upd_pages', array() );
			foreach ( array(
				'plans'     => 'ubigi_plans',
				'countries' => 'ubigi_countries',
			) as $slug => $shortcode ) {
				if ( ! empty( $pages[ $slug ] ) && get_post( $pages[ $slug ] ) ) {
					continue; }
				$existing = get_page_by_path( 'ubigi-' . $slug );
				if ( $existing ) {
					continue; }
				$id = wp_insert_post(
					array(
						'post_type'    => 'page',
						'post_status'  => 'publish',
						'post_title'   => ucfirst( $slug ),
						'post_name'    => 'ubigi-' . $slug,
						'post_content' => '[' . $shortcode . ']',
					),
					true
				);
				if ( is_wp_error( $id ) ) {
					wp_die( esc_html( $id->get_error_message() ) ); }
				$pages[ $slug ] = $id;
				update_option( 'upd_pages', $pages, false );
			}
			update_option( 'upd_installed', UPD_VERSION, false );
		}
		self::invalidate();
		flush_rewrite_rules();
	}
	public static function invalidate() {
		update_option( 'upd_cache_version', wp_generate_uuid4(), false ); }
	public static function meta_changed( $mid, $id, $key, $value ) {
		if ( str_starts_with( $key, '_upd_' ) ) {
			self::invalidate(); } }
	public static function changed( $id, $post ) {
		self::invalidate();
		$s = self::options();
		if ( ! $s['webhook_enabled'] || ! $s['webhook'] || wp_is_post_revision( $id ) || 'auto-draft' === $post->post_status ) {
			return; }
		if ( ! wp_next_scheduled( 'upd_webhook', array( $id ) ) ) {
			wp_schedule_single_event( time() + 10, 'upd_webhook', array( $id ) ); }
	}
	public static function log( $event, $ok ) {
		$logs = get_option( 'upd_logs', array() );
		array_unshift(
			$logs,
			array(
				'time'  => gmdate( 'c' ),
				'event' => sanitize_key( $event ),
				'ok'    => (bool) $ok,
			)
		);
		update_option( 'upd_logs', array_slice( $logs, 0, 100 ), false );
	}
	public static function secret( $name ) {
		$constant = 'UPD_' . strtoupper( $name );
		return defined( $constant ) ? constant( $constant ) : get_option( 'upd_' . $name, '' ); }
	public static function webhook( $id ) {
		$s = self::options();
		if ( ! $s['webhook_enabled'] || ! $s['webhook'] || ! get_post( $id ) || 'upd_plan' !== get_post_type( $id ) ) {
			return; }
		$body   = wp_json_encode(
			array(
				'event'    => 'plan.updated',
				'event_id' => wp_generate_uuid4(),
				'plan'     => UPD_API::item( get_post( $id ) ),
			)
		);
		$secret = self::secret( 'webhook_secret' );
		$result = wp_safe_remote_post(
			$s['webhook'],
			array(
				'timeout'     => 15,
				'redirection' => 0,
				'headers'     => array(
					'Content-Type'      => 'application/json',
					'X-Ubigi-Signature' => hash_hmac( 'sha256', $body, $secret ),
				),
				'body'        => $body,
			)
		);
		self::log( 'webhook', ! is_wp_error( $result ) && wp_remote_retrieve_response_code( $result ) >= 200 && wp_remote_retrieve_response_code( $result ) < 300 );
	}
	public static function assets() {
		wp_enqueue_style( 'upd', plugins_url( 'public/assets/catalogue.css', UPD_FILE ), array(), UPD_VERSION );
	}
	public static function catalogue() {
		self::assets();
		wp_enqueue_script( 'upd', plugins_url( 'public/assets/catalogue.js', UPD_FILE ), array( 'wp-i18n' ), UPD_VERSION, true );
		wp_set_script_translations( 'upd', 'ubigi-plans-demo', __DIR__ . '/../languages' );
		$s = self::options();
		ob_start(); ?>
	<section class="upd-app" data-api="<?php echo esc_url( rest_url( 'ubigi/v1/' ) ); ?>" data-size="<?php echo esc_attr( $s['per_page'] ); ?>">
	<div class="upd-hero"><span class="upd-eyebrow"><?php esc_html_e( 'UBIGI · PLANS DEMO', 'ubigi-plans-demo' ); ?></span><h2><?php esc_html_e( 'Your next destination. Connected.', 'ubigi-plans-demo' ); ?></h2><p><?php esc_html_e( 'Explore international data plans for your next adventure.', 'ubigi-plans-demo' ); ?></p><span class="upd-demo"><?php esc_html_e( 'Demo catalogue · No real eSIM, payments or connectivity', 'ubigi-plans-demo' ); ?></span></div>
	<form class="upd-filters">
	<label><?php esc_html_e( 'Search destination', 'ubigi-plans-demo' ); ?><input name="search" type="search" maxlength="100" placeholder="<?php esc_attr_e( 'Where are you travelling?', 'ubigi-plans-demo' ); ?>"></label>
	<label><?php esc_html_e( 'Country / region', 'ubigi-plans-demo' ); ?><select name="country"><option value=""><?php esc_html_e( 'All destinations', 'ubigi-plans-demo' ); ?></option>
		<?php
		foreach ( get_terms(
			array(
				'taxonomy'   => 'upd_destination',
				'hide_empty' => false,
			)
		) as $t ) {
				echo '<option value="' . esc_attr( $t->slug ) . '">' . esc_html( $t->name ) . '</option>'; }
		?>
		</select></label>
	<label><?php esc_html_e( 'Maximum price', 'ubigi-plans-demo' ); ?><input name="max_price" type="number" min="0" max="100000" step="0.01"></label>
	<label><?php esc_html_e( 'Minimum GB', 'ubigi-plans-demo' ); ?><input name="min_gb" type="number" min="0" max="100000" step="0.1"></label>
	<label><?php esc_html_e( 'Minimum days', 'ubigi-plans-demo' ); ?><input name="min_days" type="number" min="1" max="3650"></label>
	<button type="submit"><?php esc_html_e( 'Find a plan', 'ubigi-plans-demo' ); ?></button><button type="reset" class="upd-secondary"><?php esc_html_e( 'Reset', 'ubigi-plans-demo' ); ?></button>
	</form><noscript><?php esc_html_e( 'Enable JavaScript to filter plans. Browse all plans below.', 'ubigi-plans-demo' ); ?><ul>
		<?php
		foreach ( get_posts(
			array(
				'post_type'   => 'upd_plan',
				'numberposts' => -1,
			)
		) as $p ) {
								echo '<li><a href="' . esc_url( get_permalink( $p ) ) . '">' . esc_html( $p->post_title ) . '</a></li>'; }
		?>
		</ul></noscript>
	<p class="upd-status" role="status" aria-live="polite"></p><div class="upd-grid"></div><nav class="upd-pagination" aria-label="<?php esc_attr_e( 'Plan pages', 'ubigi-plans-demo' ); ?>"><button data-prev type="button"><?php esc_html_e( 'Previous', 'ubigi-plans-demo' ); ?></button><span data-page></span><button data-next type="button"><?php esc_html_e( 'Next', 'ubigi-plans-demo' ); ?></button></nav>
	</section>
		<?php
		return ob_get_clean();
	}
	public static function countries() {
		self::assets();
		$pages = get_option( 'upd_pages', array() );
		$base  = ! empty( $pages['plans'] ) ? get_permalink( $pages['plans'] ) : home_url( '/' );
		$html  = '<section class="upd-app"><h2>' . esc_html__( 'Explore destinations', 'ubigi-plans-demo' ) . '</h2><div class="upd-grid">';
		foreach ( get_terms(
			array(
				'taxonomy'   => 'upd_destination',
				'hide_empty' => false,
			)
		) as $t ) {
			/* translators: %d : nombre de forfaits de démonstration pour cette destination. */
			$html .= '<a class="upd-card" href="' . esc_url( add_query_arg( 'destination', $t->slug, $base ) ) . '"><span class="upd-eyebrow">' . esc_html( strtoupper( $t->slug ) ) . '</span><h3>' . esc_html( $t->name ) . '</h3><p>' . esc_html( sprintf( __( '%d demo plans', 'ubigi-plans-demo' ), $t->count ) ) . '</p></a>'; }
		return $html . '</div></section>';
	}
	public static function detail( $content ) {
		if ( ! is_singular( 'upd_plan' ) || ! in_the_loop() || ! is_main_query() || post_password_required() ) {
			return $content; }
		self::assets();
		$p = UPD_API::item( get_post() );
		return '<section class="upd-app"><div class="upd-card"><span class="upd-demo">' . esc_html__( 'Demo only · No purchase or real connectivity', 'ubigi-plans-demo' ) . '</span><h2>' . esc_html( $p['gb'] . ' GB · ' . $p['days'] . ' ' . __( 'days', 'ubigi-plans-demo' ) ) . '</h2><p class="upd-price">' . esc_html( $p['price'] . ' ' . $p['currency'] ) . '</p><p>' . esc_html( implode( ', ', array_column( $p['destinations'], 'name' ) ) ) . '</p><p>' . esc_html( ucfirst( $p['type'] ) ) . '</p></div>' . $content . '</section>';
	}
}
