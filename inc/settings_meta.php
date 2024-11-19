<?php
/**
 * Settings for the Weather App
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WeatherAppSettings {

	private $option_name = 'weather_app_api_key';

	public function __construct() {
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'settings_link' ) );
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_notices', array( $this, 'missing_api_key_notice' ) );
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

	// Register the settings
	public function register_settings() {
		register_setting( 'weather_app_settings', $this->option_name );

		add_settings_section(
			'weather_app_settings_section',
			__( 'Weather App Settings', 'weather-app' ),
			array( $this, 'settings_section_callback' ),
			'weather_app_settings'
		);

		add_settings_field(
			$this->option_name,
			__( 'Weather API Key', 'weather-app' ),
			array( $this, 'api_key_field_callback' ),
			'weather_app_settings',
			'weather_app_settings_section'
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
		?>
		<div class="wrap">
			<h1><?php _e( 'Weather App Settings', 'weather-app' ); ?></h1>
			<form method="post" action="options.php" id="weather-app-form">
				<?php
				settings_fields( 'weather_app_settings' );
				do_settings_sections( 'weather_app_settings' );
				submit_button( null, 'primary', 'save_changes', false, array( 'id' => 'save_changes' ) );
				?>
				<button type="button" class="button button-danger" id="remove_key"><?php _e( 'Remove Key', 'weather-app' ); ?></button>
			</form>
		</div>
		<script>
			document.addEventListener('DOMContentLoaded', function () {
				const apiKeyInput = document.querySelector('input[name="<?php echo $this->option_name; ?>"]');
				const saveButton = document.getElementById('save_changes');
				const removeButton = document.getElementById('remove_key');

				function toggleSaveButton() {
					if (apiKeyInput.value.trim() === '') {
						saveButton.disabled = true;
						saveButton.classList.add('button-disabled');
					} else {
						saveButton.disabled = false;
						saveButton.classList.remove('button-disabled');
					}
				}

				toggleSaveButton();

				apiKeyInput.addEventListener('input', toggleSaveButton);

				removeButton.addEventListener('click', function () {
					if (confirm('<?php _e( 'Are you sure you want to remove the API key?', 'weather-app' ); ?>')) {
						apiKeyInput.value = '';
						toggleSaveButton();
						document.getElementById('weather-app-form').submit();
					}
				});
			});
		</script>
		<style>
			.button-disabled {
				background-color: #ccc;
				pointer-events: none;
			}
			button#remove_key {
				background-color: #dc3232 !important;
				border-color: #dc3232 !important;
				color: #fff;
			}
		</style>
		<?php
	}

	// Settings section callback
	public function settings_section_callback() {
		echo '<p>' . __( 'Enter your Weather API Key below.', 'weather-app' ) . '</p>';
	}

	// API Key field callback
	public function api_key_field_callback() {
		$api_key = get_option( $this->option_name, '' );
		if ( ! empty( $api_key ) ) {
			$api_key = str_repeat( '*', 10 ) . substr( $api_key, 10 );
		}
		?>
		<input type="text" name="<?php echo esc_attr( $this->option_name ); ?>" placeholder="<?php echo esc_attr( $api_key ); ?>" />
		<?php
	}
}

// Initialize the settings page
new WeatherAppSettings();
?>