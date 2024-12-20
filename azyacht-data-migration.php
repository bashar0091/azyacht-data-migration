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
?>
    <div class="wrap">
        <h3 style="color: red;"><b>Please Backup your <u>database</u>, If you restore deleted item in future.</b></h3>
        <?php render_table('Candidate', 'Delete', 'existing_candidate_delete'); ?>
        <?php render_table('Company', 'Delete', 'existing_company_delete'); ?>
        <?php render_table('Job', 'Delete', 'existing_job_delete'); ?>
        <?php render_table('Email', 'Delete', 'existing_email_delete'); ?>
        <?php render_table('Notification', 'Delete', 'existing_notification_delete'); ?>
    </div>
<?php
}
