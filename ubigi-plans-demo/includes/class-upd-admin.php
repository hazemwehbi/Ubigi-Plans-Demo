<?php
defined( 'ABSPATH' ) || exit;
final class UPD_Admin {
	public static function menu() {
		add_menu_page( 'Ubigi Plans Demo', 'Ubigi Demo', 'manage_options', 'upd', array( __CLASS__, 'page' ), 'dashicons-admin-site-alt3', 26 );
		add_submenu_page( 'upd', __( 'Dashboard', 'ubigi-plans-demo' ), __( 'Dashboard', 'ubigi-plans-demo' ), 'manage_options', 'upd', array( __CLASS__, 'page' ) );
		add_submenu_page( 'upd', __( 'Countries & regions', 'ubigi-plans-demo' ), __( 'Countries & regions', 'ubigi-plans-demo' ), 'manage_categories', 'edit-tags.php?taxonomy=upd_destination&post_type=upd_plan' );
		foreach ( array(
			'settings' => __( 'Settings & API', 'ubigi-plans-demo' ),
			'ai'       => __( 'AI Assistant', 'ubigi-plans-demo' ),
			'logs'     => __( 'Logs', 'ubigi-plans-demo' ),
			'status'   => __( 'System Status', 'ubigi-plans-demo' ),
			'api'      => __( 'API Documentation', 'ubigi-plans-demo' ),
		) as $slug => $label ) {
			add_submenu_page( 'upd', $label, $label, 'manage_options', 'upd-' . $slug, array( __CLASS__, 'page' ) ); }
	}
	public static function boxes() {
		add_meta_box( 'upd-fields', __( 'Plan specifications', 'ubigi-plans-demo' ), array( __CLASS__, 'fields' ), 'upd_plan', 'normal', 'high' ); }
	public static function fields( $p ) {
		wp_nonce_field( 'upd_plan', 'upd_nonce' );
		$s = UPD_Plugin::options();
		foreach ( array(
			'gb'       => __( 'Data in GB', 'ubigi-plans-demo' ),
			'days'     => __( 'Validity in days', 'ubigi-plans-demo' ),
			'price'    => __( 'Price', 'ubigi-plans-demo' ),
			'currency' => __( 'Currency ISO code', 'ubigi-plans-demo' ),
			'type'     => __( 'Type: local, regional or global', 'ubigi-plans-demo' ),
		) as $key => $label ) {
			$value = get_post_meta( $p->ID, '_upd_' . $key, true );
			if ( '' === $value ) {
					$value = array(
						'gb'       => 1,
						'days'     => 7,
						'price'    => 0,
						'currency' => $s['currency'],
						'type'     => 'local',
					)[ $key ]; }
			echo '<p><label for="upd-' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label><br>';
			if ( 'type' === $key ) {
				echo '<select id="upd-type" name="upd[type]">';
				foreach ( array( 'local', 'regional', 'global' ) as $type ) {
						echo '<option ' . selected( $value, $type, false ) . '>' . esc_html( $type ) . '</option>';
				} echo '</select>'; } else {
				echo '<input required id="upd-' . esc_attr( $key ) . '" name="upd[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '" ' . ( 'currency' === $key ? 'maxlength="3" pattern="[A-Z]{3}"' : 'type="number" min="' . ( 'price' === $key ? '0' : ( 'days' === $key ? '1' : '0.01' ) ) . '" max="' . ( 'days' === $key ? '3650' : '100000' ) . '" step="' . ( 'days' === $key ? '1' : '0.01' ) . '"' ) . '>'; }
				echo '</p>';
		}
		echo '<p>' . esc_html__( 'Assign one or more countries or regions using the destination box. All packages are demo data.', 'ubigi-plans-demo' ) . '</p>';
	}
	public static function validate( $raw ) {
		$result = array();
		foreach ( array(
			'gb'    => array( 0.01, 100000 ),
			'days'  => array( 1, 3650 ),
			'price' => array( 0, 100000 ),
		) as $k => $range ) {
			if ( ! isset( $raw[ $k ] ) || ! is_scalar( $raw[ $k ] ) || ! is_numeric( $raw[ $k ] ) || $raw[ $k ] < $range[0] || $raw[ $k ] > $range[1] || ( 'days' === $k && floor( (float) $raw[ $k ] ) !== (float) $raw[ $k ] ) ) {
				return new WP_Error( 'invalid', 'Invalid numeric plan field: ' . $k ); }
			$result[ $k ] = 'days' === $k ? (int) $raw[ $k ] : round( (float) $raw[ $k ], 2 );
		}
		if ( ! isset( $raw['currency'], $raw['type'] ) || ! is_string( $raw['currency'] ) || ! preg_match( '/^[A-Z]{3}$/D', $raw['currency'] ) || ! in_array( $raw['type'], array( 'local', 'regional', 'global' ), true ) ) {
			return new WP_Error( 'invalid', 'Invalid currency or plan type.' ); }
		return $result + array(
			'currency' => $raw['currency'],
			'type'     => $raw['type'],
		);
	}
	public static function save( $id, $p ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || wp_is_post_revision( $id ) || ! isset( $_POST['upd_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['upd_nonce'] ) ), 'upd_plan' ) || ! current_user_can( 'edit_post', $id ) ) {
			return; }
		$raw  = isset( $_POST['upd'] ) && is_array( $_POST['upd'] ) ? map_deep( wp_unslash( $_POST['upd'] ), 'sanitize_text_field' ) : array();
		$data = self::validate( $raw );
		if ( is_wp_error( $data ) ) {
			wp_die(
				esc_html( $data->get_error_message() ),
				'',
				array(
					'response'  => 400,
					'back_link' => true,
				)
			); }
		foreach ( $data as $key => $value ) {
			update_post_meta( $id, '_upd_' . $key, $value ); }
	}
	private static function settings_save() {
		check_admin_referer( 'upd_settings' );
		$old      = UPD_Plugin::options();
		$raw      = wp_unslash( $_POST );
		$currency = is_string( $raw['currency'] ?? null ) ? strtoupper( $raw['currency'] ) : '';
		if ( ! preg_match( '/^[A-Z]{3}$/D', $currency ) ) {
			add_settings_error( 'upd', 'currency', __( 'Use a three-letter currency code.', 'ubigi-plans-demo' ) );
			return; }
		$url = isset( $raw['webhook'] ) && is_string( $raw['webhook'] ) ? esc_url_raw( trim( $raw['webhook'] ) ) : '';
		if ( $url && ( 'https' !== wp_parse_url( $url, PHP_URL_SCHEME ) || ! wp_http_validate_url( $url ) ) ) {
			add_settings_error( 'upd', 'url', __( 'Webhook must be a public HTTPS URL.', 'ubigi-plans-demo' ) );
			return; }
		$model = sanitize_text_field( is_string( $raw['model'] ?? null ) ? $raw['model'] : '' );
		if ( ! preg_match( '/^[a-zA-Z0-9._-]{1,100}$/D', $model ) ) {
			add_settings_error( 'upd', 'model', __( 'Invalid model identifier.', 'ubigi-plans-demo' ) );
			return; }
		$s = array(
			'currency'        => $currency,
			'cache'           => max( 0, min( 3600, (int) ( $raw['cache'] ?? 300 ) ) ),
			'per_page'        => max( 1, min( 50, (int) ( $raw['per_page'] ?? 9 ) ) ),
			'model'           => $model,
			'ai'              => isset( $raw['ai'] ) ? 1 : 0,
			'webhook_enabled' => isset( $raw['webhook_enabled'] ) ? 1 : 0,
			'webhook'         => $url,
			'delete_data'     => isset( $raw['delete_data'] ) ? 1 : 0,
		);
		foreach ( array( 'ai_key', 'webhook_secret' ) as $name ) {
			if ( isset( $raw[ 'clear_' . $name ] ) ) {
				delete_option( 'upd_' . $name ); } elseif ( ! empty( $raw[ $name ] ) && is_string( $raw[ $name ] ) ) {
							update_option( 'upd_' . $name, sanitize_text_field( $raw[ $name ] ), false ); }
		}
		if ( $s['webhook_enabled'] && ( ! $url || ! UPD_Plugin::secret( 'webhook_secret' ) ) ) {
			$s['webhook_enabled'] = 0;
			add_settings_error( 'upd', 'secret', __( 'Webhook disabled: provide a URL and signing secret.', 'ubigi-plans-demo' ) ); }
		update_option( 'upd_settings', $s, false );
		UPD_Plugin::invalidate();
		UPD_Plugin::log( 'settings_saved', true );
		add_settings_error( 'upd', 'saved', __( 'Settings saved.', 'ubigi-plans-demo' ), 'success' );
	}
	public static function page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return; }
		// Sélection d’écran en lecture seule ; settings_save() vérifie le nonce des modifications.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : 'upd';
		if ( 'upd-settings' === $page && 'POST' === sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			self::settings_save(); }
		$s = UPD_Plugin::options();
		echo '<div class="wrap"><h1>Ubigi Plans Demo</h1><p>' . esc_html__( 'Training catalogue. No real eSIM connectivity or payments.', 'ubigi-plans-demo' ) . '</p>';
		settings_errors( 'upd' );
		if ( 'upd-settings' === $page ) {
			echo '<form method="post">';
			wp_nonce_field( 'upd_settings' );
			echo '<table class="form-table">';
			foreach ( array(
				'currency' => __( 'Default ISO currency for new plans (no conversion)', 'ubigi-plans-demo' ),
				'cache'    => __( 'Cache seconds (0–3600)', 'ubigi-plans-demo' ),
				'per_page' => __( 'Plans per page (1–50)', 'ubigi-plans-demo' ),
				'model'    => __( 'OpenAI model', 'ubigi-plans-demo' ),
				'webhook'  => __( 'n8n webhook HTTPS URL', 'ubigi-plans-demo' ),
			) as $key => $label ) {
				echo '<tr><th><label for="upd-' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td><input class="regular-text" id="upd-' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $s[ $key ] ) . '"></td></tr>'; }
			foreach ( array(
				'ai_key'         => __( 'OpenAI API key', 'ubigi-plans-demo' ),
				'webhook_secret' => __( 'Webhook signing secret', 'ubigi-plans-demo' ),
			) as $key => $label ) {
				echo '<tr><th>' . esc_html( $label ) . '</th><td><input aria-label="' . esc_attr( $label ) . '" type="password" name="' . esc_attr( $key ) . '" autocomplete="new-password" value=""><p>' . esc_html( UPD_Plugin::secret( $key ) ? __( 'Configured. Leave blank to retain.', 'ubigi-plans-demo' ) : __( 'Not configured.', 'ubigi-plans-demo' ) ) . '</p><label><input type="checkbox" name="clear_' . esc_attr( $key ) . '">' . esc_html__( 'Remove stored secret', 'ubigi-plans-demo' ) . '</label></td></tr>'; }
			foreach ( array(
				'ai'              => __( 'Enable AI (sends selected plan content to OpenAI)', 'ubigi-plans-demo' ),
				'webhook_enabled' => __( 'Enable n8n (sends plan content, including drafts, to configured URL)', 'ubigi-plans-demo' ),
				'delete_data'     => __( 'Delete ALL plugin plans, destinations and created pages on uninstall', 'ubigi-plans-demo' ),
			) as $key => $label ) {
				echo '<tr><th>' . esc_html( $label ) . '</th><td><input aria-label="' . esc_attr( $label ) . '" type="checkbox" name="' . esc_attr( $key ) . '" ' . checked( $s[ $key ], 1, false ) . '></td></tr>'; }
			echo '</table><p>' . esc_html__( 'Secrets are never sent to the frontend. Prefer UPD_AI_KEY and UPD_WEBHOOK_SECRET constants in wp-config.php; stored options are protected by WordPress access control, not encrypted at rest.', 'ubigi-plans-demo' ) . '</p>';
			submit_button();
			echo '</form>';
		} elseif ( 'upd-ai' === $page ) {
			wp_enqueue_script( 'upd-admin', plugins_url( 'admin/assets/ai.js', UPD_FILE ), array( 'wp-i18n' ), UPD_VERSION, true );
			wp_set_script_translations( 'upd-admin', 'ubigi-plans-demo', __DIR__ . '/../languages' );
			echo '<form id="upd-ai" data-endpoint="' . esc_url( rest_url( 'ubigi/v1/ai' ) ) . '" data-nonce="' . esc_attr( wp_create_nonce( 'wp_rest' ) ) . '"><p><label>' . esc_html__( 'Plan', 'ubigi-plans-demo' ) . ' <select name="id" required>';
			foreach ( get_posts(
				array(
					'post_type'   => 'upd_plan',
					'post_status' => array( 'publish', 'draft', 'pending', 'private' ),
					'numberposts' => -1,
				)
			) as $p ) {
				echo '<option value="' . esc_attr( $p->ID ) . '">' . esc_html( $p->post_title ) . '</option>'; }
			echo '</select></label></p><p><label>' . esc_html__( 'Task', 'ubigi-plans-demo' ) . ' <select name="task">';
			foreach ( array( 'description', 'seo', 'summary', 'review', 'rewrite' ) as $task ) {
					echo '<option>' . esc_html( $task ) . '</option>';
			} echo '</select></label></p><p><label>' . esc_html__( 'Language', 'ubigi-plans-demo' ) . ' <input name="language" maxlength="50" value="English" required></label></p><button class="button button-primary">' . esc_html__( 'Generate draft', 'ubigi-plans-demo' ) . '</button><p role="status"></p><label for="upd-draft">' . esc_html__( 'Review the draft, then copy approved text to the plan editor. Nothing is published automatically.', 'ubigi-plans-demo' ) . '</label><br><textarea id="upd-draft" rows="16" class="large-text" readonly></textarea></form>';
		} elseif ( 'upd-logs' === $page ) {
			echo '<table class="widefat striped"><thead><tr><th>UTC</th><th>' . esc_html__( 'Event', 'ubigi-plans-demo' ) . '</th><th>' . esc_html__( 'Result', 'ubigi-plans-demo' ) . '</th></tr></thead><tbody>';
			foreach ( get_option( 'upd_logs', array() ) as $row ) {
					echo '<tr><td>' . esc_html( $row['time'] ) . '</td><td>' . esc_html( $row['event'] ) . '</td><td>' . esc_html( $row['ok'] ? 'OK' : 'Failed' ) . '</td></tr>';
			} echo '</tbody></table><p>' . esc_html__( 'Only the last 100 events are retained. Request bodies, URLs, credentials and provider errors are not logged.', 'ubigi-plans-demo' ) . '</p>';
		} elseif ( 'upd-api' === $page ) {
			echo '<h2>REST API</h2><p>' . esc_html__( 'Public GET endpoints; administrative changes use the WordPress editor with capability and nonce checks.', 'ubigi-plans-demo' ) . '</p><pre>' . esc_html( rest_url( 'ubigi/v1/plans' ) . '?country=jp&min_price=0&max_price=30&min_gb=3&min_days=7&page=1&per_page=9' ) . "\n" . esc_html( rest_url( 'ubigi/v1/plans/123' ) ) . "\n" . esc_html( rest_url( 'ubigi/v1/countries' ) ) . "\n" . esc_html( rest_url( 'ubigi/v1/health' ) ) . '</pre><p>' . esc_html__( 'POST /ubigi/v1/ai requires manage_options and cookie authentication with X-WP-Nonce, or a WordPress application password over HTTPS.', 'ubigi-plans-demo' ) . '</p>';
		} elseif ( 'upd-status' === $page ) {
			global $wp_version;
			foreach ( array(
				'Plugin'             => UPD_VERSION,
				'PHP'                => PHP_VERSION,
				'WordPress'          => $wp_version,
				'HTTPS'              => is_ssl() ? 'Yes' : 'No',
				'AI configured'      => ( $s['ai'] && UPD_Plugin::secret( 'ai_key' ) ) ? 'Yes' : 'No',
				'Webhook configured' => ( $s['webhook_enabled'] && $s['webhook'] ) ? 'Yes' : 'No',
				'WP-Cron'            => defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ? 'Disabled: configure a server cron' : 'Enabled (traffic dependent)',
			) as $k => $v ) {
				echo '<p><strong>' . esc_html( $k ) . ':</strong> ' . esc_html( $v ) . '</p>'; }
		} else {
			$counts = wp_count_posts( 'upd_plan' );
			echo '<h2>' . esc_html__( 'Dashboard', 'ubigi-plans-demo' ) . '</h2><p>' . esc_html(
				sprintf(
					/* translators: 1 : forfaits publiés, 2 : brouillons, 3 : destinations. */
					__( 'Published plans: %1$d · Drafts: %2$d · Destinations: %3$d', 'ubigi-plans-demo' ),
					$counts->publish,
					$counts->draft,
					wp_count_terms(
						array(
							'taxonomy'   => 'upd_destination',
							'hide_empty' => false,
						)
					)
				)
			) . '</p>';
			foreach ( get_option( 'upd_pages', array() ) as $label => $id ) {
				echo '<p><a class="button" href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html( ucfirst( $label ) ) . '</a></p>'; }
			echo '<p>AI: ' . esc_html( $s['ai'] ? 'Enabled' : 'Disabled' ) . ' · n8n: ' . esc_html( $s['webhook_enabled'] ? 'Enabled' : 'Disabled' ) . '</p><p>' . esc_html__( 'Use Plans to edit packages and Countries & regions to manage destinations. Installation never changes your theme, menu or home page.', 'ubigi-plans-demo' ) . '</p>';
		}
		echo '</div>';
	}
}
