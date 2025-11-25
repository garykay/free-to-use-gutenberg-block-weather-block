<?php
/**
 * Settings for the Weather App
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WeatherAppSettings {

	private $option_name       = 'weather_app_api_key';
	private $cache_option_name = 'weather_app_cache_duration';

	public function __construct() {
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'settings_link' ) );
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_notices', array( $this, 'missing_api_key_notice' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
	}

	// Add settings link to plugin page
	public function settings_link( $links ) {
		$settings_link = '<a href="options-general.php?page=weather_app_settings">' . __( 'Settings', 'weather-app' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	// Add the settings page
	public function add_settings_page() {
		add_options_page(
			__( 'Weather App Settings', 'weather-app' ),
			__( 'Weather App', 'weather-app' ),
			'manage_options',
			'weather_app_settings',
			array( $this, 'render_settings_page' )
		);
	}

	// Enqueue admin styles
	public function enqueue_admin_styles( $hook_suffix ) {
		// Only load on our settings page
		if ( $hook_suffix !== 'settings_page_weather_app_settings' ) {
			return;
		}

		wp_enqueue_style(
			'weather-app-admin-dashboard',
			plugins_url( 'admin-dashboard.css', __FILE__ ),
			array(),
			filemtime( plugin_dir_path( __FILE__ ) . 'admin-dashboard.css' )
		);
	}

	// Register the settings
	public function register_settings() {
		register_setting( 'weather_app_settings', $this->option_name );
		register_setting( 'weather_app_settings', $this->cache_option_name );

		add_settings_section(
			'weather_app_api_section',
			__( 'API Configuration', 'weather-app' ),
			array( $this, 'api_section_callback' ),
			'weather_app_settings'
		);

		add_settings_section(
			'weather_app_cache_section',
			__( 'Cache Settings', 'weather-app' ),
			array( $this, 'cache_section_callback' ),
			'weather_app_settings'
		);

		add_settings_field(
			$this->option_name,
			__( 'Weather API Key', 'weather-app' ),
			array( $this, 'api_key_field_callback' ),
			'weather_app_settings',
			'weather_app_api_section'
		);

		add_settings_field(
			$this->cache_option_name,
			__( 'Cache Duration', 'weather-app' ),
			array( $this, 'cache_duration_field_callback' ),
			'weather_app_settings',
			'weather_app_cache_section'
		);
	}

	// Notice if API key is missing
	public function missing_api_key_notice() {
		if ( empty( get_option( $this->option_name ) ) ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . __( 'Weather App: Please set your OpenWeatherMap API key in the settings.', 'weather-app' ) . '</p></div>';
		}
	}

	// Render the settings page HTML
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$api_key        = get_option( $this->option_name );
		$cache_duration = get_option( $this->cache_option_name, 30 );
		?>
		<div class="wrap weather-app-dashboard">
			<div class="weather-dashboard-header">
				<h1><span class="dashicons dashicons-cloud"></span><?php _e( 'Weather App Dashboard', 'weather-app' ); ?></h1>
				<p class="dashboard-subtitle"><?php _e( 'Configure your weather widget settings and manage API access', 'weather-app' ); ?></p>
			</div>

			<div class="weather-dashboard-grid">
				<div class="dashboard-card status-card">
					<h3><span class="dashicons dashicons-yes-alt"></span><?php _e( 'Status Overview', 'weather-app' ); ?></h3>
					<div class="status-items">
						<div class="status-item <?php echo ! empty( $api_key ) ? 'status-success' : 'status-error'; ?>">
							<span class="status-indicator"></span>
							<span class="status-text">
								<?php echo ! empty( $api_key ) ? __( 'API Key Configured', 'weather-app' ) : __( 'API Key Missing', 'weather-app' ); ?>
							</span>
						</div>
						<div class="status-item status-info">
							<span class="status-indicator"></span>
							<span class="status-text">
								<?php printf( __( 'Cache Duration: %d minutes', 'weather-app' ), $cache_duration ); ?>
							</span>
						</div>
					</div>
					
					<?php if ( empty( $api_key ) ) : ?>
					<div class="api-key-cta">
						<h4><span class="dashicons dashicons-warning"></span><?php _e( 'Get Your API Key', 'weather-app' ); ?></h4>
						<p><?php _e( 'You need a free API key from WeatherAPI to use this plugin. It only takes a minute to sign up!', 'weather-app' ); ?></p>
						<a href="https://www.weatherapi.com/" target="_blank" class="button button-cta">
							<span class="dashicons dashicons-external"></span>
							<?php _e( 'Get Free API Key', 'weather-app' ); ?>
						</a>
					</div>
					<?php endif; ?>
				</div>

				<form method="post" action="options.php" id="weather-app-form" class="dashboard-card settings-card">
					<h3><span class="dashicons dashicons-admin-settings"></span><?php _e( 'Configuration', 'weather-app' ); ?></h3>
					<?php
					settings_fields( 'weather_app_settings' );
					do_settings_sections( 'weather_app_settings' );
					?>
					<div class="form-actions">
						<?php
						submit_button(
							__( 'Save Settings', 'weather-app' ),
							'primary',
							'save_changes',
							false,
							array(
								'id'    => 'save_changes',
								'class' => 'button-primary',
							)
						);
						?>
						<button type="button" class="button button-danger" id="remove_key"><?php _e( 'Clear API Key', 'weather-app' ); ?></button>
					</div>
				</form>
			</div>

			<div class="weather-dashboard-footer">
				<div class="dashboard-card info-card">
					<h3><span class="dashicons dashicons-info"></span><?php _e( 'Quick Help', 'weather-app' ); ?></h3>
					<ul>
						<li><strong><?php _e( 'API Key:', 'weather-app' ); ?></strong> <?php _e( 'Get your free API key from WeatherAPI.com', 'weather-app' ); ?></li>
						<li><strong><?php _e( 'Cache Duration:', 'weather-app' ); ?></strong> <?php _e( 'Controls how long weather data is cached to reduce API calls', 'weather-app' ); ?></li>
						<li><strong><?php _e( 'Recommended:', 'weather-app' ); ?></strong> <?php _e( '15-60 minutes depending on your needs', 'weather-app' ); ?></li>
					</ul>
				</div>
			</div>
		</div>
		<script>
			document.addEventListener('DOMContentLoaded', function () {
				const apiKeyInput = document.querySelector('input[name="<?php echo $this->option_name; ?>"]');
				const cacheSelect = document.querySelector('select[name="<?php echo $this->cache_option_name; ?>"]');
				const saveButton = document.getElementById('save_changes');
				const removeButton = document.getElementById('remove_key');

				// Handle API key masking.
				if (apiKeyInput && apiKeyInput.value.trim() !== '') {
					apiKeyInput.classList.add('masked');
					
					// Show real value when focused.
					apiKeyInput.addEventListener('focus', function() {
						this.classList.remove('masked');
					});
					
					// Mask again when losing focus (but keep real value).
					apiKeyInput.addEventListener('blur', function() {
						if (this.value.trim() !== '') {
							this.classList.add('masked');
						}
					});
				}

				function toggleSaveButton() {
					// Enable save button if there's any input in API key field OR if cache duration is selected.
					const hasApiKey = apiKeyInput && apiKeyInput.value.trim() !== '';
					const hasCacheValue = cacheSelect && cacheSelect.value !== '';
					
					if (hasApiKey || hasCacheValue) {
						saveButton.disabled = false;
						saveButton.classList.remove('button-disabled');
					} else {
						saveButton.disabled = true;
						saveButton.classList.add('button-disabled');
					}
				}

				// Initial state - enable save button since cache always has a value.
				saveButton.disabled = false;
				saveButton.classList.remove('button-disabled');

				// Add event listeners.
				if (apiKeyInput) {
					apiKeyInput.addEventListener('input', function() {
						// Handle masking for new input.
						if (this.value.trim() !== '') {
							this.classList.add('masked');
						} else {
							this.classList.remove('masked');
						}
						toggleSaveButton();
					});
				}
				
				if (cacheSelect) {
					cacheSelect.addEventListener('change', toggleSaveButton);
				}

				if (removeButton) {
					removeButton.addEventListener('click', function () {
						if (confirm('<?php _e( 'Are you sure you want to remove the API key?', 'weather-app' ); ?>')) {
							if (apiKeyInput) {
								apiKeyInput.value = '';
								apiKeyInput.classList.remove('masked');
							}
							toggleSaveButton();
							document.getElementById('weather-app-form').submit();
						}
					});
				}
			});
		</script>
		<?php
	}

	// API Settings section callback.
	public function api_section_callback() {
		echo '<p>' . __( 'Configure your Weather API access settings below.', 'weather-app' ) . '</p>';
	}

	// Cache Settings section callback.
	public function cache_section_callback() {
		echo '<p>' . __( 'Adjust cache settings to optimize performance and API usage.', 'weather-app' ) . '</p>';
	}

	// API Key field callback.
	public function api_key_field_callback() {
		$api_key = get_option( $this->option_name, '' );
		?>
		<div class="input-group">
			<input type="text" name="<?php echo esc_attr( $this->option_name ); ?>" 
					value="<?php echo esc_attr( $api_key ); ?>"
					placeholder="Enter your Weather API key" 
					class="regular-text api-key-input" />
			<p class="description">
				<?php _e( 'Get your API key from', 'weather-app' ); ?> 
				<a href="https://www.weatherapi.com/" target="_blank">WeatherAPI.com</a>
			</p>
		</div>
		<?php
	}

	// Cache Duration field callback.
	public function cache_duration_field_callback() {
		$cache_duration = get_option( $this->cache_option_name, 30 );
		?>
		<div class="input-group">
			<select name="<?php echo esc_attr( $this->cache_option_name ); ?>" class="regular-text">
				<option value="5" <?php selected( $cache_duration, 5 ); ?>><?php _e( '5 minutes', 'weather-app' ); ?></option>
				<option value="15" <?php selected( $cache_duration, 15 ); ?>><?php _e( '15 minutes', 'weather-app' ); ?></option>
				<option value="30" <?php selected( $cache_duration, 30 ); ?>><?php _e( '30 minutes (Default)', 'weather-app' ); ?></option>
				<option value="60" <?php selected( $cache_duration, 60 ); ?>><?php _e( '1 hour', 'weather-app' ); ?></option>
				<option value="120" <?php selected( $cache_duration, 120 ); ?>><?php _e( '2 hours', 'weather-app' ); ?></option>
				<option value="360" <?php selected( $cache_duration, 360 ); ?>><?php _e( '6 hours', 'weather-app' ); ?></option>
				<option value="720" <?php selected( $cache_duration, 720 ); ?>><?php _e( '12 hours', 'weather-app' ); ?></option>
			</select>
			<p class="description">
				<?php _e( 'How long to cache weather data before fetching fresh data from the API. Longer cache reduces API calls but may show outdated weather.', 'weather-app' ); ?>
			</p>
		</div>
		<?php
	}
}

// Initialize the settings page.
new WeatherAppSettings();
?>