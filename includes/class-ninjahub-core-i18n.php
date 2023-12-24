<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://https://github.com/MustafaShaaban/ninjahub-pro
 * @since      1.0.0
 *
 * @package    Ninjahub_Core
 * @subpackage Ninjahub_Core/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Ninjahub_Core
 * @subpackage Ninjahub_Core/includes
 * @author     Mustafa Shaaban <Mustafashaaban22@gmail.com>
 */
class Ninjahub_Core_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'ninjahub-core',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
