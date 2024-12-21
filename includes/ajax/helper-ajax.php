<?php

function show_csv_handler()
{
    // Get the CSV file name from the request
    $csv_file_name = isset($_POST['csv_file_name']) ? $_POST['csv_file_name'] : '';

    // Check if file name is provided
    if (empty($csv_file_name)) {
        wp_send_json_error(['message' => 'No CSV file specified.']);
        wp_die();
    }

    // Get the upload directory and set the target CSV directory
    $upload_dir = wp_upload_dir();
    $subdir = 'csv-data';
    $target_dir = $upload_dir['basedir'] . '/' . $subdir;

    // Ensure the CSV file exists
    $csv_file_path = $target_dir . '/' . $csv_file_name;
    if (!file_exists($csv_file_path)) {
        wp_send_json_error(['message' => 'CSV file not found.']);
        wp_die();
    }

    // Read the CSV file and parse the data
    $csv_data = [];
    if (($handle = fopen($csv_file_path, 'r')) !== FALSE) {
        // Get the header row
        $header = fgetcsv($handle);

        // Loop through the file and store each row
        while (($row = fgetcsv($handle)) !== FALSE) {
            $csv_data[] = array_combine($header, $row);
        }
        fclose($handle);
    }

    // Shuffle the data and get the first 5 random rows
    shuffle($csv_data);
    $csv_data = array_slice($csv_data, 0, 5);

    // Send the parsed CSV data in the response
    wp_send_json_success(['csv_data' => $csv_data]);
    wp_die();
}

add_action('wp_ajax_show_csv_handler', 'show_csv_handler');
