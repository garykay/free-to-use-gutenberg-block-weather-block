<?php
/**
 * Generate unique id for aria-controls.
 *
 * @var string $unique_id
 * @package category
 */

$unique_id = wp_unique_id( 'p-' );

/**
 * Get block attributes.
 */
$location        = ! empty( $attributes['location'] ) ? esc_html( $attributes['location'] ) : 'London';
$unit            = $attributes['unit'] ?? 'C';
$speed_unit      = $attributes['speedUnit'] ?? 'km/h';
$extra_info      = $attributes['extraInfo'] ?? 'false';
$title_color     = $attributes['titleColor'] ?? '#000';
$title_size      = $attributes['titleSize'] ?? 20;
$title_font_weight = $attributes['titleFontWeight'] ?? 'normal';
$icon_size       = $attributes['iconSize'] ?? 50;
$icon_position   = $attributes['iconPosition'] ?? 'right';
/**
 * Fetch weather data from weather API.
 */
$location2       = str_replace( ' ', '-', $location );
$weather_api_ey  = get_option( 'weather_app_api_key' );
$weather_api_url = "https://api.weatherapi.com/v1/forecast.json?key={$weather_api_ey}&q={$location2}&aqi=no";
$transient_key   = 'weather_data_' . sanitize_title( $location2 );

/**
 * Get weather data from transient.
 */
$weather_info = get_transient( $transient_key );

if ( ! $weather_info ) {
	// Fetch weather data if not cached or expired using fetch API.
	$weather_data = wp_remote_retrieve_body( wp_remote_get( $weather_api_url ) );
	if ( $weather_data ) {
		$weather_info = json_decode( $weather_data );
		set_transient( $transient_key, $weather_info, 30 * MINUTE_IN_SECONDS );
	}
}
?>

<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php
	if ( $weather_info && isset( $weather_info->current ) ) :
		$temp          = $unit === 'F' ? $weather_info->current->temp_f : $weather_info->current->temp_c;
		$wind          = $speed_unit === 'mph' ? $weather_info->current->wind_mph : $weather_info->current->wind_kph;
		$condition     = $weather_info->current->condition->text;
		$icon          = $weather_info->current->condition->icon;
		$forecast_temp = $weather_info->forecast->forecastday[0]->hour
		?>
		<div class="weather-header icon-position-<?php echo esc_attr( $icon_position ); ?>">
			<div class="weather-title-content">
				<?php 
				// Render InnerBlocks content with proper WordPress block processing
				if ( ! empty( $content ) ) {
					// Use do_blocks to ensure proper processing of Gutenberg content
					echo do_blocks( $content );
				} else {
					echo '<h3>' . esc_html( ucfirst( $location ) ) . '</h3>';
				}
				?>
			</div>
			
			<?php if ( $icon_position === 'top' ) : ?>
				<img class="weather-icon" src="<?php echo esc_url( $icon ); ?>" alt="<?php echo esc_attr( $condition ); ?>">
			<?php endif; ?>
			
			<?php if ( $icon_position === 'left' ) : ?>
				<img class="weather-icon" src="<?php echo esc_url( $icon ); ?>" alt="<?php echo esc_attr( $condition ); ?>">
			<?php endif; ?>
			
			<?php if ( $icon_position === 'right' ) : ?>
				<img class="weather-icon" src="<?php echo esc_url( $icon ); ?>" alt="<?php echo esc_attr( $condition ); ?>">
			<?php endif; ?>
			
			<?php if ( $icon_position === 'bottom' ) : ?>
				<img class="weather-icon" src="<?php echo esc_url( $icon ); ?>" alt="<?php echo esc_attr( $condition ); ?>">
			<?php endif; ?>
		</div>
		<p>
			<?php printf( esc_html__( 'Temperature: %1$.1f °%2$s', 'weather-app' ), $temp, esc_html( $unit ) ); ?><br>
			<?php printf( esc_html__( 'Wind: %1$s %2$s', 'weather-app' ), $wind, esc_html( $speed_unit ) ); ?><br>
			<?php printf( esc_html__( 'Condition: %s', 'weather-app' ), esc_html( $condition ) ); ?>
		</p>

		<?php if ( 'true' == $extra_info ) : ?>
			<button
				data-wp-on--click="actions.toggleOpen"
				data-wp-bind--aria-expanded="context.isOpen"
				aria-controls="<?php echo esc_attr( $unique_id ); ?>"
				class="toggle-button"
				>
				<?php esc_html_e( 'View forecast', 'weather-app' ); ?>
			</button>
			<div
				id="<?php echo esc_attr( $unique_id ); ?>"
				data-wp-bind--hidden="!context.isOpen"
				class="forecast">
				<?php if ( $forecast_temp ) : ?>
					<?php foreach ( $forecast_temp as $hour ) : ?>
						<?php
						$hour_temp = $unit === 'F' ? $hour->temp_f : $hour->temp_c;
						$hour_unit = $unit === 'F' ? 'F' : 'C';
						?>
						<p>
							<?php printf( esc_html__( '%1$s: %2$.1f °%3$s', 'weather-app' ), date( 'H:i', strtotime( $hour->time ) ), $hour_temp, esc_html( $hour_unit ) ); ?>
							<img class="weather-icon" src="<?php echo esc_url( $hour->condition->icon ); ?>" alt="<?php echo esc_attr( $hour->condition->text ); ?>">
						</p>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>

	<?php else : ?>
		<p><?php esc_html_e( 'Weather data not available.', 'weather-app' ); ?></p>
	<?php endif; ?>
</div>