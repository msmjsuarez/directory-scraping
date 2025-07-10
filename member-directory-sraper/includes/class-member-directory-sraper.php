<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://portfolio.mjlayasan.com/
 * @since      1.0.0
 *
 * @package    Member_Directory_Sraper
 * @subpackage Member_Directory_Sraper/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Member_Directory_Sraper
 * @subpackage Member_Directory_Sraper/includes
 * @author     MJ Layasan <msmjsuarez@gmail.com>
 */
class Member_Directory_Sraper
{
	protected $loader;
	protected $plugin_name;
	protected $version;
	protected $admin_settings;
	protected $scraper_handler;

	public function __construct()
	{
		if (defined('MEMBER_DIRECTORY_SRAPER_VERSION')) {
			$this->version = MEMBER_DIRECTORY_SRAPER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'member-directory-sraper';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies()
	{


		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-member-directory-sraper-loader.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-member-directory-sraper-i18n.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-member-directory-sraper-admin.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-member-directory-sraper-public.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-member-directory-scraper-admin-settings.php';

		require_once plugin_dir_path(__FILE__) . '/class-member-directory-scraper-handler.php';

		$this->admin_settings = new Member_Directory_Scraper_Admin_Settings();
		$this->scraper_handler = new Member_Directory_Scraper_Handler();

		$this->loader = new Member_Directory_Sraper_Loader();
	}

	private function set_locale()
	{

		$plugin_i18n = new Member_Directory_Sraper_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	private function define_admin_hooks()
	{

		$plugin_admin = new Member_Directory_Sraper_Admin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
	}

	private function define_public_hooks()
	{

		$plugin_public = new Member_Directory_Sraper_Public($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
	}

	public function run()
	{
		$this->loader->run();
	}

	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	public function get_loader()
	{
		return $this->loader;
	}

	public function get_version()
	{
		return $this->version;
	}
}
