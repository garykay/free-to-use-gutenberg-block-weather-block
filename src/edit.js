import { __ } from '@wordpress/i18n';
import axios from 'axios';
import {
	useBlockProps,
	ColorPalette,
	InspectorControls,
} from '@wordpress/block-editor';
import { useEffect, useState } from 'react';
import {
	ToggleControl,
	Panel,
	PanelBody,
	PanelRow,
	RangeControl,
} from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { location, unit, extraInfo, iconSize, titleColor, titleSize, speedUnit } =
		attributes;
	const blockProps = useBlockProps();
	const [ data, setData ] = useState( null );

	// Function to toggle temperature unit between Celsius and Fahrenheit
	const toggleUnit = () => {
		setAttributes( { unit: unit === 'C' ? 'F' : 'C' } );
	};

	// Function to toggle wind speed unit between km/h and mph
	const toggleSpeedUnit = () => {
		setAttributes( { speedUnit: speedUnit === 'km/h' ? 'mph' : 'km/h' } );
	};

	// Fetch weather data from WordPress REST API (which acts as a proxy)
	useEffect( () => {
		if ( location ) {
			const weatherApiUrl = `/wp-json/weather-app/v1/data?location=${ location }`;

			const fetchData = async () => {
				try {
					const response = await axios.get( weatherApiUrl );
					console.log( response.data.forecast.forecastday[ 0 ].hour );
					setData( response.data );
					document.cookie = `weatherData=${ JSON.stringify(
						response.data
					) };max-age=3600`;
				} catch ( error ) {
					console.error( 'Error fetching weather data:', error );
				}
			};
			fetchData();
		}
	}, [ location ] );

	// Set icon size
	const setIconSize = ( value ) => {
		setAttributes( { iconSize: value } );
	};

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<Panel header="Settings">
					<PanelBody title="Set Location" initialOpen={ true }>
						<PanelRow>
							<label htmlFor="location">
								{ __( 'Location:', 'weather-app' ) }
							</label>
							<input
								type="text"
								id="location"
								value={ location || '' }
								onChange={ ( event ) =>
									setAttributes( {
										location: event.target.value,
									} )
								}
								placeholder={ __(
									'Enter a location',
									'weather-app'
								) }
							/>
						</PanelRow>
						<PanelRow>
							<ToggleControl
								label={ __(
									'Show temp in Fahrenheit',
									'weather-app'
								) }
								checked={ unit === 'F' }
								defaultChecked={ false }
								onChange={ toggleUnit }
							/>
						</PanelRow>
						<PanelRow>
							<ToggleControl
								label={ __(
									'Show wind speed in mph',
									'weather-app'
								) }
								checked={ speedUnit === 'mph' }
								defaultChecked={ false }
								onChange={ toggleSpeedUnit }
							/>
						</PanelRow>
						<PanelRow>
							<ToggleControl
								label={ __(
									'Show extra information',
									'weather-app'
								) }
								checked={ extraInfo }
								defaultChecked={ false }
								onChange={ () =>
									setAttributes( {
										extraInfo: ! extraInfo,
									} )
								}
							/>
						</PanelRow>
						<PanelRow>
							<RangeControl
								label={ __( 'Icon Size', 'weather-app' ) }
								value={ iconSize }
								onChange={ setIconSize }
								min={ 32 }
								max={ 70 }
							/>
						</PanelRow>
						<PanelRow>
							<ColorPalette
								label={ __( 'Title Color', 'weather-app' ) }
								value={ titleColor }
								onChange={ ( newColor ) =>
									setAttributes( { titleColor: newColor } )
								}
							/>
						</PanelRow>
						<PanelRow>
							<RangeControl
								__nextHasNoMarginBottom
								label={ __( 'Title Size (px)', 'weather-app' ) }
								value={ titleSize }
								onChange={ ( value ) =>
									setAttributes( { titleSize: value } )
								}
								min={ 16 }
								max={ 150 }
							/>
						</PanelRow>
					</PanelBody>
				</Panel>
			</InspectorControls>

			<div className="weather-container">
				{ data && data.location ? (
					<>
						<h3
							className="weather_title"
							style={ { color: titleColor, fontSize: titleSize } }
						>
							{ data.location.name }
							<img
								src={ `https://${ data.current.condition.icon }` }
								alt={ data.current.condition.text }
								style={ { height: iconSize } }
							/>
						</h3>
						<p>
							<span>
								{ unit === 'C'
									? `Temperature: ${ data.current.temp_c }°C`
									: `Temperature: ${ data.current.temp_f }°F` }
							</span>

							<span>
								{ speedUnit === 'km/h'
									? `Wind: ${ data.current.wind_kph } km/h`
									: `Wind: ${ data.current.wind_mph } mph` }
							</span>

							<span>
								Condition: { data.current.condition.text }
							</span>
						</p>

						{ extraInfo &&
							data.forecast.forecastday[ 0 ].hour.map( ( hour ) => {
								const time = hour.time.split( ' ' )[ 1 ]; // Extracts the 'HH:mm' part from 'YYYY-MM-DD HH:mm'
								return (
									<div
										key={ hour.time_epoch }
										className="forcast"
									>
										<p>
											{ time } -
											<span>
												{ unit === 'C'
													? `Temperature: ${ hour.temp_c }°C`
													: `Temperature: ${ hour.temp_f }°F` }
											</span>
											<img
												src={ `https://${ hour.condition.icon }` }
												alt={ hour.condition.text }
												style={ {
													height: iconSize,
													width: iconSize,
												} }
											/>
										</p>
									</div>
								);
							} ) }
					</>
				) : (
					<p>{ __( 'Loading weather data...', 'weather-app' ) }</p>
				) }
			</div>
		</div>
	);
}
