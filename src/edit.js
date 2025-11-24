import { __ } from '@wordpress/i18n';
import axios from 'axios';
import {
	useBlockProps,
	InspectorControls,
	InnerBlocks,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import { useEffect, useState } from 'react';
import { useSelect } from '@wordpress/data';
import {
	ToggleControl,
	Panel,
	PanelBody,
	PanelRow,
	SelectControl,
} from '@wordpress/components';

export default function Edit( { attributes, setAttributes, clientId } ) {
	const { location, unit, extraInfo, speedUnit, iconPosition } = attributes;
	const blockProps = useBlockProps();
	const [ data, setData ] = useState( null );

	// Get inner blocks to track heading content changes
	const innerBlocks = useSelect(
		( select ) => {
			return select( blockEditorStore ).getBlocks( clientId );
		},
		[ clientId ]
	);

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
					setData( response.data );
					document.cookie = `weatherData=${ JSON.stringify(
						response.data
					) };max-age=3600`;
				} catch ( error ) {
					// eslint-disable-next-line no-console
					console.error( 'Error fetching weather data:', error );
				}
			};
			fetchData();
		}
	}, [ location ] );

	// Watch for changes in heading content and update location attribute
	useEffect( () => {
		if (
			innerBlocks &&
			innerBlocks.length > 0 &&
			innerBlocks[ 0 ].name === 'core/heading'
		) {
			const headingContent = innerBlocks[ 0 ].attributes?.content;
			if ( headingContent && headingContent !== location ) {
				// Extract plain text from HTML content
				const tempDiv = document.createElement( 'div' );
				tempDiv.innerHTML = headingContent;
				const plainText =
					tempDiv.textContent || tempDiv.innerText || '';

				// Only update if it's different and not empty
				if ( plainText.trim() && plainText.trim() !== location ) {
					setAttributes( { location: plainText.trim() } );
				}
			}
		}
	}, [ innerBlocks, location, setAttributes ] );

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
							<SelectControl
								label={ __( 'Icon Position', 'weather-app' ) }
								value={ iconPosition }
								options={ [
									{ label: 'Right of Title', value: 'right' },
									{ label: 'Left of Title', value: 'left' },
									{ label: 'Above Title', value: 'top' },
									{ label: 'Below Title', value: 'bottom' },
								] }
								onChange={ ( value ) =>
									setAttributes( { iconPosition: value } )
								}
							/>
						</PanelRow>
					</PanelBody>
				</Panel>
			</InspectorControls>

			<div className="weather-container">
				<div
					className={ `weather-header icon-position-${ iconPosition }` }
				>
					<InnerBlocks
						allowedBlocks={ [ 'core/heading' ] }
						template={ [
							[
								'core/heading',
								{
									content: location || 'Weather Location',
									level: 3,
									placeholder: __(
										'Enter location for weather…',
										'weather-app'
									),
								},
							],
						] }
						templateLock="all"
					/>
					{ data && data.location && (
						<>
							{ iconPosition === 'top' && (
								<img
									className="weather-icon"
									src={ `https://${ data.current.condition.icon }` }
									alt={ data.current.condition.text }
								/>
							) }
							{ iconPosition === 'left' && (
								<img
									className="weather-icon"
									src={ `https://${ data.current.condition.icon }` }
									alt={ data.current.condition.text }
								/>
							) }
							{ iconPosition === 'right' && (
								<img
									className="weather-icon"
									src={ `https://${ data.current.condition.icon }` }
									alt={ data.current.condition.text }
								/>
							) }
							{ iconPosition === 'bottom' && (
								<img
									className="weather-icon"
									src={ `https://${ data.current.condition.icon }` }
									alt={ data.current.condition.text }
								/>
							) }
						</>
					) }
				</div>

				{ data && data.location ? (
					<>
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
							data.forecast.forecastday[ 0 ].hour.map(
								( hour ) => {
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
													className="weather-icon"
													src={ `https://${ hour.condition.icon }` }
													alt={ hour.condition.text }
												/>
											</p>
										</div>
									);
								}
							) }
					</>
				) : (
					<p>{ __( 'Loading weather data…', 'weather-app' ) }</p>
				) }
			</div>
		</div>
	);
}
