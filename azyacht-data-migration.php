<?php

/**
 * Plugin Name: Azyachting Data Migration
 * Description: 
 * Version:     1.0.0
 * Author:      Orbit It
 * Author URI:  
 * Text Domain: azyachting-data-migration
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access to the plugin file
defined('ABSPATH') || exit;

/**
 * Require files
 */
require_once plugin_dir_path(__FILE__) . 'includes/helper-function.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax/existing-data-delete.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax/upload-csv.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax/helper-ajax.php';

/**
 * CSS and JS added
 */
function azdata_enqueue_scripts()
{
    // JS file 
    wp_enqueue_script('custom-admin-script', plugin_dir_url(__FILE__) . 'assets/js/custom-admin.js', array('jquery'), '1.0.0', true);

    // dynamic data to js 
    wp_localize_script('custom-admin-script', 'dataAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
    ));
}
add_action('admin_enqueue_scripts', 'azdata_enqueue_scripts');

// add admin menu 
add_action('admin_menu', 'custom_admin_menu');
function custom_admin_menu()
{
    add_menu_page(
        'Data Migration',
        'Data Migration',
        'manage_options',
        'data-migration',
        'data_migration_content',
        'dashicons-admin-generic',
        20
    );
}

function data_migration_content()
{
    $upload_dir = wp_upload_dir();
    $subdir = 'csv-data';
    $target_dir = $upload_dir['basedir'] . '/' . $subdir;
?>
    <div class="wrap">
        <h3 style="color: red;"><b>Please Backup your <u>database</u>, If you restore deleted item in future.</b></h3>
        <?php render_table('Candidate', 'Delete', 'existing_candidate_delete'); ?>
        <?php render_table('Company', 'Delete', 'existing_company_delete'); ?>
        <?php render_table('Job', 'Delete', 'existing_job_delete'); ?>
        <?php render_table('Email', 'Delete', 'existing_email_delete'); ?>
        <?php render_table('Notification', 'Delete', 'existing_notification_delete'); ?>

        <div>
            <h3>Data Save on Server</h3>
            <?php
            if (!file_exists($target_dir)) {
            ?>
                <form action="" class="upload_all_file_form" enctype="multipart/form-data">
                    <div>
                        <label>
                            Upload All Csv File
                            <input type="file" name="upload_allcsv_file[]" accept=".csv" multiple required>
                        </label>
                    </div>
                    <div>
                        <button type="submit" class="button button-primary">Save File to Server</button>
                    </div>
                    <div><b class="reloading_text"></b></div>
                </form>
            <?php
            } else {
            ?>
                <form action="" class="delete_all_file_form">
                    <div>
                        <button type="submit" class="button button-primary" style="background:red;">Delete CSV Folder</button>
                    </div>
                    <div><b class="reloading_text"></b></div>
                </form>
            <?php
            }
            ?>

        </div>

        <div style="margin-top: 50px;">
            <h3>Data Migration</h3>
            <?php
            if (file_exists($target_dir)) {
                // Get all CSV files in the directory
                $csv_files = array_diff(scandir($target_dir), array('..', '.'));

                // Filter out files that are not CSV
                $csv_files = array_filter($csv_files, function ($file) {
                    return pathinfo($file, PATHINFO_EXTENSION) === 'csv';
                });
            ?>
                <form action="">
                    <table>
                        <tr>
                            <td>
                                <label>
                                    <p>CSV File</p>
                                    <p>
                                        <?php
                                        if (!empty($csv_files)) {
                                            echo '<select name="csv_file" id="csv_file" required>';
                                            echo '<option>Select CSV</option>';
                                            foreach ($csv_files as $file) {
                                                echo '<option value="' . esc_attr($file) . '">' . esc_html($file) . '</option>';
                                            }
                                            echo '</select>';
                                        }
                                        ?>
                                    </p>
                                    <div>
                                        <button type="button" class="button button-primary test_csv_btn">Test Csv</button>
                                        <div><b class="reloading_text"></b></div>
                                    </div>
                                </label>
                            </td>
                        </tr>
                    </table>
                </form>

                <pre style="overflow-x: scroll;border:1px solid red;padding:10px;display:none" class="csv_viewer"></pre>
            <?php
            }
            ?>
        </div>
    </div>
<?php
}
