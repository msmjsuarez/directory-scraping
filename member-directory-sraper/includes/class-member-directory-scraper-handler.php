<?php
if (!defined('WPINC')) {
    die;
}

class Member_Directory_Scraper_Handler
{
    public function __construct()
    {
        add_action('admin_post_mds_export_single_row', array($this, 'handle_single_csv_export'));
        add_action('admin_post_mds_export_all_csv', array($this, 'handle_all_csv_export'));
    }

    /**
     * Load basic company/profile links
     */
    private function load_csv_data()
    {
        $filepath = plugin_dir_path(dirname(__FILE__)) . 'data/member_profiles.csv';
        $rows = [];

        if (!file_exists($filepath) || !is_readable($filepath)) {
            return $rows;
        }

        if (($handle = fopen($filepath, 'r')) !== false) {
            $headers = fgetcsv($handle); // get column headers
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

    /**
     * Load full profile data (for exporting all)
     */
    private function load_full_csv_data()
    {
        $filepath = plugin_dir_path(dirname(__FILE__)) . 'data/full_member_profiles.csv';
        $rows = [];

        if (!file_exists($filepath) || !is_readable($filepath)) {
            return $rows;
        }

        if (($handle = fopen($filepath, 'r')) !== false) {
            $headers = fgetcsv($handle); // Read headers
            if (!$headers) {
                fclose($handle);
                return $rows;
            }

            while (($data = fgetcsv($handle)) !== false) {
                // Check for header-data mismatch
                if (count($data) !== count($headers)) {
                    continue;
                }

                $row = array_combine($headers, $data);
                $rows[] = $row;
            }

            fclose($handle);
        }

        return $rows;
    }

    /**
     * Handle individual CSV export (if you still need it)
     */
    public function handle_single_csv_export()
    {
        if (
            !isset($_POST['mds_export_single_nonce']) ||
            !wp_verify_nonce($_POST['mds_export_single_nonce'], 'mds_export_single_csv')
        ) {
            wp_die('Security check failed.');
        }

        $index = intval($_POST['row_index']);
        $scraped_data = $this->load_csv_data();

        if (!isset($scraped_data[$index])) {
            wp_die('Invalid row selected.');
        }

        $row = $scraped_data[$index];

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="member_data_row_' . $index . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, array_keys($row));
        fputcsv($output, array_values($row));
        fclose($output);
        exit;
    }

    /**
     * Handle export of all detailed profile data
     */
    public function handle_all_csv_export()
    {
        if (
            !current_user_can('manage_options') ||
            !isset($_POST['mds_export_all_nonce']) ||
            !wp_verify_nonce($_POST['mds_export_all_nonce'], 'mds_export_all_csv')
        ) {
            wp_die('Unauthorized request.');
        }

        $data = $this->load_full_csv_data(); // raw array with many fields
        $filename = 'member_profiles_full.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Custom headers matching displayed layout
        fputcsv($output, [
            'Company Name',
            'Contact Name',
            'Phone',
            'Email',
            'Address',
            'City',
            'Province',
            'Website',
            'Member Type'
        ]);

        foreach ($data as $row) {
            fputcsv($output, [
                $row['company_name'] ?? '',
                trim(($row['First name'] ?? '') . ' ' . ($row['Last name'] ?? '')),
                $row['Phone'] ?? '',
                $row['Email'] ?? '',
                $row['Address'] ?? '',
                $row['City'] ?? '',
                $row['Province/State'] ?? '',
                $row['Web Site'] ?? '',
                $row['Title'] ?? '',
            ]);
        }

        fclose($output);
        exit;
    }
}
