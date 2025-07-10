<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://portfolio.mjlayasan.com/
 * @since      1.0.0
 *
 * @package    Member_Directory_Sraper
 * @subpackage Member_Directory_Sraper/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Member_Directory_Sraper
 * @subpackage Member_Directory_Sraper/includes
 * @author     MJ Layasan <msmjsuarez@gmail.com>
 */
class Member_Directory_Sraper_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'member-directory-sraper',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
