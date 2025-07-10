<?php

if (!defined('WPINC')) {
    die;
}

class Member_Directory_Scraper_Admin_Settings
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_plugin_admin_menu'));
    }

    public function add_plugin_admin_menu()
    {
        if (current_user_can('manage_options')) {
            add_menu_page(
                'Member Directory Scraper',
                'Member Scraper',
                'manage_options',
                'member-directory-scraper',
                array($this, 'display_settings_page'),
                'dashicons-download',
                26
            );
        }
    }

    public function display_settings_page()
    {
        $url = get_option('mds_scraper_url', 'https://www.ontariosignassociation.com/member-directory');
        $scraped = $this->load_csv_data(plugin_dir_path(dirname(__FILE__)) . 'data/member_profiles.csv');

        $should_scrape_now = isset($_POST['mds_scrape_submit']) && check_admin_referer('mds_scrape_action', 'mds_scrape_nonce');

        $full_profiles = [];

        if ($should_scrape_now) {
            $full_profiles = $this->scrape_full_profiles(); // Scrapes and writes CSV
        } else {
            $full_profiles = $this->load_full_csv_data(); // Load from existing CSV
        }


?>
        <div class="wrap">
            <h1>Member Directory Scraper</h1>

            <!-- Scrape Button -->
            <hr>
            <form method="post" action="">
                <?php wp_nonce_field('mds_scrape_action', 'mds_scrape_nonce'); ?>
                <input type="submit" name="mds_scrape_submit" class="button button-primary" value="Start Scraping Each Profile">
            </form>

            <!-- Main Scraped List -->
            <hr>
            <h2>Main Directory Scraped Results</h2>
            <?php if (!empty($scraped)) : ?>
                <table class="widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Company Name</th>
                            <th>Profile URL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($scraped as $row) : ?>
                            <tr>
                                <td><?php echo esc_html($row['company_name']); ?></td>
                                <td><a href="<?php echo esc_url($row['profile_url']); ?>" target="_blank"><?php echo esc_html($row['profile_url']); ?></a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>No scraped data found. Please run the scraper first.</p>
            <?php endif; ?>

            <!-- Detailed Scraped Results -->
            <?php if (!empty($full_profiles)) : ?>
                <hr>
                <h2>Detailed Profile Data</h2>

                <!-- Export All Button -->
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom: 20px;">
                    <?php wp_nonce_field('mds_export_all_csv', 'mds_export_all_nonce'); ?>
                    <input type="hidden" name="action" value="mds_export_all_csv">
                    <input type="submit" class="button button-secondary" value="Export Displayed Data to CSV">
                </form>

                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>Company Name</th>
                            <th>Contact Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>City</th>
                            <th>Province</th>
                            <th>Website</th>
                            <th>Member Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($full_profiles as $profile): ?>
                            <tr>
                                <td><?php echo esc_html($profile['company_name'] ?? ''); ?></td>
                                <td><?php echo esc_html(($profile['First name'] ?? '') . ' ' . ($profile['Last name'] ?? '')); ?></td>
                                <td><?php echo esc_html($profile['Phone'] ?? ''); ?></td>
                                <td><?php echo esc_html($profile['Email'] ?? ''); ?></td>
                                <td><?php echo esc_html($profile['Address'] ?? ''); ?></td>
                                <td><?php echo esc_html($profile['City'] ?? ''); ?></td>
                                <td><?php echo esc_html($profile['Province/State'] ?? ''); ?></td>
                                <td><?php echo esc_html($profile['Web Site'] ?? ''); ?></td>
                                <td><?php echo esc_html($profile['Title'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
<?php
    }

    private function load_csv_data($filepath)
    {
        $rows = [];

        if (!file_exists($filepath) || !is_readable($filepath)) {
            return $rows;
        }

        if (($handle = fopen($filepath, 'r')) !== false) {
            $headers = fgetcsv($handle); // first line = headers
            while (($data = fgetcsv($handle)) !== false) {
                $row = array_combine($headers, $data);
                if (!empty($row['company_name']) && !empty($row['profile_url'])) {
                    $rows[] = $row;
                }
            }
            fclose($handle);
        }

        return $rows;
    }

    private function scrape_full_profiles()
    {
        $filepath = plugin_dir_path(dirname(__FILE__)) . 'data/member_profiles.csv';
        $results = [];

        if (!file_exists($filepath) || !is_readable($filepath)) {
            return $results;
        }

        if (($handle = fopen($filepath, 'r')) !== false) {
            $headers = fgetcsv($handle);
            while (($data = fgetcsv($handle)) !== false) {
                $row = array_combine($headers, $data);
                $company_name = $row['company_name'] ?? '';
                $profile_url  = $row['profile_url'] ?? '';

                if ($profile_url) {
                    $html = wp_remote_get($profile_url);
                    if (is_wp_error($html)) continue;

                    $body = wp_remote_retrieve_body($html);
                    @$doc = new DOMDocument();
                    @$doc->loadHTML($body);
                    $xpath = new DOMXPath($doc);

                    $containers = $xpath->query("//div[contains(@class, 'fieldSubContainer')]");
                    $fields = [];

                    foreach ($containers as $container) {
                        $labelNode = $xpath->query(".//div[contains(@class, 'fieldLabel')]/span", $container)->item(0);
                        $valueNode = $xpath->query(".//div[contains(@class, 'fieldBody')]/span", $container)->item(0);

                        if ($labelNode && $valueNode) {
                            $label = trim($labelNode->nodeValue);
                            $value = trim($valueNode->nodeValue);
                            $fields[$label] = $value;
                        }
                    }

                    $results[] = array_merge([
                        'company_name' => $company_name,
                        'profile_url'  => $profile_url,
                    ], $fields);
                }
            }
            fclose($handle);
        }

        // Save full data to CSV for export
        if (!empty($results)) {
            $filepath_full = plugin_dir_path(dirname(__FILE__)) . 'data/full_member_profiles.csv';
            $output = fopen($filepath_full, 'w');
            fputcsv($output, array_keys($results[0]));
            foreach ($results as $row) {
                fputcsv($output, array_values($row));
            }
            fclose($output);
        }

        return $results;
    }

    private function load_full_csv_data()
    {
        $filepath = plugin_dir_path(dirname(__FILE__)) . 'data/full_member_profiles.csv';
        $rows = [];

        if (!file_exists($filepath) || !is_readable($filepath)) {
            return $rows;
        }

        if (($handle = fopen($filepath, 'r')) !== false) {
            $headers = fgetcsv($handle);
            while (($data = fgetcsv($handle)) !== false) {
                if (count($headers) === count($data)) {
                    $rows[] = array_combine($headers, $data);
                }
            }
            fclose($handle);
        }

        return $rows;
    }
}
