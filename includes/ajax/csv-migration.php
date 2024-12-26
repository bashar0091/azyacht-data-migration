<?php
add_action('wp_ajax_migrate_file_handler', 'migrate_file_handler');
function migrate_file_handler()
{
    global $wpdb;

    $file_type = sanitize_text_field($_POST['file_type'] ?? '');
    $file_name = sanitize_text_field($_POST['file_name'] ?? '');
    $table_name = sanitize_text_field($_POST['database_table_name'] ?? '');

    // Add WordPress table prefix
    $table_name = $wpdb->prefix . $table_name;

    // Process file based on type (CSV or JSON)
    if ($file_type === 'csv') {
        process_csv_file($file_name, $table_name);
    } elseif ($file_type === 'json') {
        process_json_file($file_name, $table_name);
    } else {
        wp_send_json_error("Invalid file type.");
    }
    wp_send_json_success(['message' => 'Migrated Successfully']);

    wp_die();
}

function process_csv_file($file_name, $table_name)
{
    global $wpdb;

    $upload_dir = wp_upload_dir();
    $csv_file_path = $upload_dir['basedir'] . '/yacht-data/csv/' . $file_name;

    if (!file_exists($csv_file_path)) {
        wp_send_json_error("CSV file not found.");
    }

    $file = fopen($csv_file_path, 'r');
    if (!$file) {
        wp_send_json_error("Unable to open the CSV file.");
    }

    $headers = fgetcsv($file);

    // Check if table exists, and delete it if it does
    $wpdb->query("DROP TABLE IF EXISTS `$table_name`");

    // Create table with unique_id as BIGINT primary key
    $columns = ["`unique_id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY"];
    foreach ($headers as $header) {
        $columns[] = "`$header` LONGTEXT";
    }

    // Create table with the unique_id column
    $create_table_sql = "CREATE TABLE `$table_name` (" . implode(", ", $columns) . ")";
    $wpdb->query($create_table_sql);

    // Insert CSV data into the table
    while ($row = fgetcsv($file)) {
        $data = array_combine($headers, $row);
        $wpdb->insert($table_name, $data);
    }

    fclose($file);

    wp_send_json_success("Data successfully inserted into `$table_name`.");
}

function process_json_file($file_name, $table_name)
{
    global $wpdb;

    $upload_dir = wp_upload_dir();
    $json_file_path = $upload_dir['basedir'] . '/yacht-data/json/' . $file_name;

    if (!file_exists($json_file_path)) {
        wp_send_json_error("JSON file not found.");
    }

    $json_data = file_get_contents($json_file_path);
    $data_array = json_decode($json_data, true);

    if ($data_array === null) {
        wp_send_json_error("Invalid JSON data.");
    }

    $headers = array_keys($data_array[0]);

    // Check if table exists, and delete it if it does
    $wpdb->query("DROP TABLE IF EXISTS `$table_name`");

    // Create table with unique_id as BIGINT primary key
    $columns = ["`unique_id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY"];
    foreach ($headers as $header) {
        $columns[] = "`$header` LONGTEXT";
    }

    // Create table with the unique_id column
    $create_table_sql = "CREATE TABLE `$table_name` (" . implode(", ", $columns) . ")";
    $wpdb->query($create_table_sql);

    // Insert JSON data into the table
    foreach ($data_array as $data) {
        $wpdb->insert($table_name, $data);
    }

    wp_send_json_success("Data successfully inserted into `$table_name`.");
}
