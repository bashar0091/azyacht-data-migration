<?php

function generate_core_folder_handler()
{
    $main_folder = 'yacht-data';
    $csv_folder = 'csv';
    $json_folder = 'json';
    $upload_dir = wp_upload_dir();
    $main_folder_path = $upload_dir['basedir'] . '/' . $main_folder;
    $csv_folder_path = $main_folder_path . '/' . $csv_folder;
    $json_folder_path = $main_folder_path . '/' . $json_folder;
    mkdir($main_folder_path, 0755, true);
    mkdir($csv_folder_path, 0755, true);
    mkdir($json_folder_path, 0755, true);
    wp_send_json_success(['message' => 'Folders Created Successfully']);
    wp_die();
}

add_action('wp_ajax_generate_core_folder_handler', 'generate_core_folder_handler');

// ==============
function delete_core_folder_handler()
{
    $main_folder = 'yacht-data';
    $csv_folder = 'csv';
    $json_folder = 'json';
    $upload_dir = wp_upload_dir();
    $main_folder_path = $upload_dir['basedir'] . '/' . $main_folder;
    function delete_folder($folder_path)
    {
        if (is_dir($folder_path)) {
            $files = array_diff(scandir($folder_path), array('.', '..'));
            foreach ($files as $file) {
                $file_path = $folder_path . '/' . $file;
                is_dir($file_path) ? delete_folder($file_path) : unlink($file_path);
            }
            rmdir($folder_path);
        }
    }
    if (file_exists($main_folder_path)) {
        delete_folder($main_folder_path);
    }
    wp_send_json_success(['message' => 'Folders Deleted Successfully']);
    wp_die();
}

add_action('wp_ajax_delete_core_folder_handler', 'delete_core_folder_handler');



// ========
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
    $main_folder = 'yacht-data';
    $csv_folder = 'csv';
    $upload_dir = wp_upload_dir();
    $main_folder_path = $upload_dir['basedir'] . '/' . $main_folder;
    $csv_folder_path = $main_folder_path . '/' . $csv_folder;

    // Ensure the CSV file exists
    $csv_file_path = $csv_folder_path . '/' . $csv_file_name;
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
