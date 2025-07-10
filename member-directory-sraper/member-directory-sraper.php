<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://portfolio.mjlayasan.com/
 * @since             1.0.0
 * @package           Member_Directory_Sraper
 *
 * @wordpress-plugin
 * Plugin Name:       Member Directory Scraper
 * Plugin URI:        https://demo.mjlayasan.com
 * Description:       This plugin is to scrape a members profile data from a directory.
 * Version:           1.0.0
 * Author:            MJ Layasan
 * Author URI:        https://portfolio.mjlayasan.com//
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       member-directory-sraper
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('MEMBER_DIRECTORY_SRAPER_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-member-directory-sraper-activator.php
 */
function activate_member_directory_sraper()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-member-directory-sraper-activator.php';
	Member_Directory_Sraper_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-member-directory-sraper-deactivator.php
 */
function deactivate_member_directory_sraper()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-member-directory-sraper-deactivator.php';
	Member_Directory_Sraper_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_member_directory_sraper');
register_deactivation_hook(__FILE__, 'deactivate_member_directory_sraper');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-member-directory-sraper.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_member_directory_sraper()
{

	$plugin = new Member_Directory_Sraper();
	$plugin->run();
}
run_member_directory_sraper();
