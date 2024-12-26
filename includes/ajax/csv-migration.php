<?php
add_action('wp_ajax_migrate_csv_handler', 'migrate_csv_handler');
function migrate_csv_handler()
{
    global $wpdb;

    // Get the CSV file name from the request and sanitize it
    $csv_file = isset($_POST['csv_file']) ? sanitize_text_field($_POST['csv_file']) : '';

    if (!empty($csv_file)) {
        // Trim extra spaces and normalize the file name
        $csv_file = trim($csv_file);

        // Remove the .csv extension and sanitize the table name
        $base_name = preg_replace('/\.csv$/i', '', $csv_file);
        $sanitized_name = preg_replace('/[^a-zA-Z0-9_-]/', '', $base_name);

        // Create the final table name without adding an extra 'wp_' prefix
        $table_name = $wpdb->prefix . $sanitized_name;

        // Define the target file path
        $upload_dir = wp_upload_dir();
        $subdir = 'csv-data';
        $target_dir = $upload_dir['basedir'] . '/' . $subdir;
        $csv_path = $target_dir . '/' . $csv_file;

        // Check if the CSV file exists
        if (!file_exists($csv_path)) {
            wp_send_json_error('CSV file not found.', 404);
        }

        // Read the CSV file
        $csv_data = array_map('str_getcsv', file($csv_path));
        if (empty($csv_data)) {
            wp_send_json_error('CSV file is empty or invalid.', 400);
        }

        // Extract the headers from the first row
        $headers = array_map(function ($header) {
            return preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower(trim($header)));
        }, $csv_data[0]);

        // Ensure table exists
        $charset_collate = $wpdb->get_charset_collate();
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            // Build SQL columns from headers
            $columns = array_map(function ($header) {
                return "`$header` text";
            }, $headers);

            $sql = "CREATE TABLE $table_name (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                " . implode(', ', $columns) . ",
                PRIMARY KEY (id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }

        // Truncate the table (clear all existing data)
        $wpdb->query("TRUNCATE TABLE $table_name");

        // Check for missing columns and add them
        $existing_columns = $wpdb->get_col("DESCRIBE $table_name", 0); // Get existing column names
        $missing_columns = array_diff($headers, $existing_columns);

        foreach ($missing_columns as $missing_column) {
            $wpdb->query("ALTER TABLE $table_name ADD `$missing_column` text");
        }

        // Insert data into the table
        for ($i = 1; $i < count($csv_data); $i++) {
            $row = array_combine($headers, $csv_data[$i]);

            // Ensure only valid columns are inserted
            $filtered_row = array_filter($row, function ($key) use ($existing_columns) {
                return in_array($key, $existing_columns);
            }, ARRAY_FILTER_USE_KEY);

            $wpdb->insert($table_name, $filtered_row);
        }

        wp_send_json_success("Table '$sanitized_name' flushed and data inserted successfully.");
    }

    wp_die();
}




// =======================================================================
// add_action('wp_ajax_migrate_csv_handler', 'migrate_csv_handler');
// function migrate_csv_handler()
// {
//     global $wpdb;

//     // Get the CSV file name from the request and sanitize it
//     $csv_file = isset($_POST['csv_file']) ? sanitize_text_field($_POST['csv_file']) : '';

//     if (!empty($csv_file)) {
//         // Trim extra spaces and normalize the file name
//         $csv_file = trim($csv_file);

//         // Remove the .csv extension and sanitize the table name
//         $base_name = preg_replace('/\.csv$/i', '', $csv_file);
//         $sanitized_name = preg_replace('/[^a-zA-Z0-9_-]/', '', $base_name);

//         // Create the final table name without adding an extra 'wp_' prefix
//         $table_name = $wpdb->prefix . $sanitized_name;

//         // Define the target file path
//         $upload_dir = wp_upload_dir();
//         $subdir = 'csv-data';
//         $target_dir = $upload_dir['basedir'] . '/' . $subdir;
//         $csv_path = $target_dir . '/' . $csv_file;

//         // Check if the CSV file exists
//         if (!file_exists($csv_path)) {
//             wp_send_json_error('CSV file not found.', 404);
//         }

//         // Read the CSV file
//         $csv_data = array_map('str_getcsv', file($csv_path));
//         if (empty($csv_data)) {
//             wp_send_json_error('CSV file is empty or invalid.', 400);
//         }

//         // Extract the headers from the first row
//         $headers = array_map(function ($header) {
//             return preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower(trim($header)));
//         }, $csv_data[0]);

//         // Ensure table exists
//         $charset_collate = $wpdb->get_charset_collate();
//         if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
//             // Build SQL columns from headers
//             $columns = array_map(function ($header) {
//                 return "`$header` text";
//             }, $headers);

//             $sql = "CREATE TABLE $table_name (
//                 id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//                 " . implode(', ', $columns) . ",
//                 PRIMARY KEY (id)
//             ) $charset_collate;";

//             require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
//             dbDelta($sql);
//         }

//         // Truncate the table (clear all existing data)
//         $wpdb->query("TRUNCATE TABLE $table_name");

//         // Check for missing columns and add them
//         $existing_columns = $wpdb->get_col("DESCRIBE $table_name", 0); // Get existing column names
//         $missing_columns = array_diff($headers, $existing_columns);

//         foreach ($missing_columns as $missing_column) {
//             $wpdb->query("ALTER TABLE $table_name ADD `$missing_column` text");
//         }

//         // Insert data into the table
//         for ($i = 1; $i < count($csv_data); $i++) {
//             // Ensure row matches the number of headers
//             if (count($headers) === count($csv_data[$i])) {
//                 $row = array_combine($headers, $csv_data[$i]);

//                 // Ensure only valid columns are inserted
//                 $filtered_row = array_filter($row, function ($key) use ($existing_columns) {
//                     return in_array($key, $existing_columns);
//                 }, ARRAY_FILTER_USE_KEY);

//                 $wpdb->insert($table_name, $filtered_row);
//             } else {
//                 error_log('Row skipped due to mismatched column count: ' . print_r($csv_data[$i], true));
//             }
//         }

//         wp_send_json_success("Table '$sanitized_name' flushed and data inserted successfully.");
//     }

//     wp_die();
// }




// =======================================================================

// add_action('wp_ajax_migrate_csv_handler', 'migrate_csv_handler');
// function migrate_csv_handler()
// {
//     global $wpdb;

//     $csv_file = isset($_POST['csv_file']) ? sanitize_text_field($_POST['csv_file']) : '';

//     if (!empty($csv_file)) {
//         $csv_file = trim($csv_file);
//         $base_name = preg_replace('/\.csv$/i', '', $csv_file);
//         $sanitized_name = preg_replace('/[^a-zA-Z0-9_-]/', '', $base_name);
//         $table_name = $wpdb->prefix . $sanitized_name;

//         $upload_dir = wp_upload_dir();
//         $subdir = 'csv-data';
//         $target_dir = $upload_dir['basedir'] . '/' . $subdir;
//         $csv_path = $target_dir . '/' . $csv_file;

//         if (!file_exists($csv_path)) {
//             wp_send_json_error('CSV file not found.', 404);
//         }

//         $csv_data = array_map('str_getcsv', file($csv_path));
//         if (empty($csv_data)) {
//             wp_send_json_error('CSV file is empty or invalid.', 400);
//         }

//         $headers = array_map(function ($header) {
//             return preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower(trim($header)));
//         }, $csv_data[0]);

//         $charset_collate = $wpdb->get_charset_collate();
//         if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
//             $columns = array_map(function ($header) {
//                 return "`$header` text";
//             }, $headers);

//             $sql = "CREATE TABLE $table_name (
//                 id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//                 " . implode(', ', $columns) . ",
//                 PRIMARY KEY (id)
//             ) $charset_collate;";

//             require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
//             dbDelta($sql);
//         }

//         $wpdb->query("TRUNCATE TABLE $table_name");

//         $existing_columns = $wpdb->get_col("DESCRIBE $table_name", 0);
//         $missing_columns = array_diff($headers, $existing_columns);

//         foreach ($missing_columns as $missing_column) {
//             $wpdb->query("ALTER TABLE $table_name ADD `$missing_column` text");
//         }

//         for ($i = 1; $i < count($csv_data); $i++) {
//             $row = $csv_data[$i];

//             // Handle mismatched column counts
//             if (count($row) < count($headers)) {
//                 $row = array_pad($row, count($headers), null); // Pad missing values
//             } elseif (count($row) > count($headers)) {
//                 $row = array_slice($row, 0, count($headers)); // Trim extra values
//             }

//             $row = array_combine($headers, $row);

//             $filtered_row = array_filter($row, function ($key) use ($existing_columns) {
//                 return in_array($key, $existing_columns);
//             }, ARRAY_FILTER_USE_KEY);

//             $wpdb->insert($table_name, $filtered_row);
//         }

//         wp_send_json_success("Table '$sanitized_name' flushed and data inserted successfully.");
//     }

//     wp_die();
// }