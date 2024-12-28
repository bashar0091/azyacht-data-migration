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
require_once plugin_dir_path(__FILE__) . 'includes/ajax/helper-ajax.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax/csv-migration.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax/user-ajax.php';

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
    $main_folder = 'yacht-data';
    $csv_folder = 'csv';
    $json_folder = 'json';
    $upload_dir = wp_upload_dir();
    $main_folder_path = $upload_dir['basedir'] . '/' . $main_folder;
    $csv_folder_path = $main_folder_path . '/' . $csv_folder;
    $json_folder_path = $main_folder_path . '/' . $json_folder;
?>
    <div class="wrap">
        <h3 style="color: red;"><b>Please Backup your <u>database</u>, If you restore deleted item in future.</b></h3>

        <?php if (!file_exists($main_folder_path)) : ?>
            <?php render_table('Generate Core Folder', 'Generate', 'generate_core_folder'); ?>
        <?php else : ?>
            <?php render_table('Folder Generated [' . $main_folder_path . ']', 'Delete', 'delete_core_folder'); ?>
        <?php endif; ?>


        <?php
        if (file_exists($main_folder_path)) {
            migration_form_render('Data Migration CSV', $csv_folder_path, 'csv');
            migration_form_render('Data Migration JSON', $json_folder_path, 'json');
        ?>

            <div style="margin-top: 50px;">
                <h3>User Migration</h3>
                <form action="" class="user_migration_form">
                    <button type="button" class="button button-primary create_user_column_btn">Create Column</button>
                    <button type="button" class="button button-primary migrate_user_btn">Migrate User</button>
                    <div><b class="reloading_text"></b></div>
                </form>
            </div>
        <?php
        }
        ?>
    </div>
<?php
}
