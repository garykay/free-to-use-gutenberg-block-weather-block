# Roomworks Media Weather Block

Contributors:      Roomworks Media
Tags:              block, weather, forecast, widgets
Tested up to:      6.6
Stable tag:        0.1.0
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

## Description

An interactive WordPress block that displays weather information using the WordPress Interactivity API. This block allows you to display current weather conditions with customizable location, temperature units, and styling options.

### Features

- **Customizable Location**: Display weather for any location
- **Temperature Units**: Support for Celsius and Fahrenheit
- **Speed Units**: Choose between km/h and other units for wind speed
- **Interactive Design**: Built with WordPress Interactivity API
- **Styling Options**: 
  - Customizable background colors
  - Flexible icon positioning (left/right)
  - Typography controls (font size, weight, style)
  - Spacing controls (padding/margin)
  - Border and layout options
- **Extra Information Toggle**: Option to show/hide additional weather details

## Installation

1. Upload the plugin files to the `/wp-content/plugins/roomworksmedia-weather-app` directory, or install the plugin through the WordPress plugins screen directly.

2. Activate the plugin through the 'Plugins' screen in WordPress

3. Add the "Roomworksmedia Forecast" block to any post or page through the block editor

4. Configure your WeatherAPI.com API key (see API setup below)

## API Setup - WeatherApi.Com

This plugin uses the [WeatherApi.com](https://www.weatherapi.com/) API to fetch weather data. 

**Setup Steps:**
1. Sign up for a free account at WeatherApi.com
2. Get your API key from the dashboard
3. Configure the API key in your WordPress settings

## Block Configuration

The weather block supports the following customizable attributes:

- **Location**: Set the location for weather data (default: London)
- **Temperature Unit**: Choose between Celsius (C) or Fahrenheit (F)
- **Wind Speed Unit**: Select preferred unit for wind speed display
- **Background Color**: Customize the block background
- **Icon Position**: Position weather icon on left or right
- **Extra Information**: Toggle additional weather details

## For Developers

**Development Setup:**
1. Fork the plugin repository
2. Run `npm install` to install dependencies
3. Run `npm start` to start the development server
4. Make your changes and submit a pull request

**Block Structure:**
- Uses WordPress Block API version 3
- Built with Interactivity API support
- Includes comprehensive styling and typography controls
- Supports responsive design with layout options
