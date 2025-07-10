=== Directory Scraping Toolkit ===

Contributors: MJ Layasan  
Requires at least: Node.js v14+, WordPress 6.8  
Tested up to: 6.8  
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html  

== Description ==

This toolkit is composed of two parts:

1. **main-directory-scraping** (Node.js script)
2. **member-directory-scraper** (WordPress Plugin)

These tools are designed to scrape the Ontario Sign Association Member Directory:
https://www.ontariosignassociation.com/member-directory

== Installation & Usage ==

=== PART 1: Node.js Script (main-directory-scraping) ===

1. Make sure you have Node.js installed (https://nodejs.org/).
2. Open your terminal or command prompt.
3. Navigate to the `main-directory-scraping` folder.
4. Run the script with the following command:

node scraper.js


This script scrapes member profile links and outputs them for use in the WordPress plugin.

---

=== PART 2: WordPress Plugin (member-directory-scraper) ===

1. Upload the `member-directory-scraper` folder to your `/wp-content/plugins/` directory, or install via the WordPress Plugin Installer.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to `WP Admin > Member Scraper`.
4. Click **"Start Scraping Each Profile"** to begin scraping detailed member data.
5. Wait for the scraping process to complete.
6. Click **"Export Displayed Data to CSV"** to download the data in CSV format.

== Notes ==

- Scraping may take time depending on the number of profiles and server speed.
- Data includes fields such as Company Name, Email, Website, Address and more.
- Make sure your hosting or local server has no limitations on external requests.

== Changelog ==

= 1.0.0 =
* Initial release with directory scraper and export to CSV feature.

