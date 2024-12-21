<?php

function upload_all_file_handler()
{
    if (!empty($_FILES['upload_allcsv_file'])) {
        $upload_dir = wp_upload_dir();
        $subdir = 'csv-data';
        $target_dir = $upload_dir['basedir'] . '/' . $subdir;
        if (!file_exists($target_dir)) {
            wp_mkdir_p($target_dir);
        }
        foreach ($_FILES['upload_allcsv_file']['tmp_name'] as $key => $tmp_name) {
            $file_name = $_FILES['upload_allcsv_file']['name'][$key];
            $file_tmp = $_FILES['upload_allcsv_file']['tmp_name'][$key];
            $file_type = $_FILES['upload_allcsv_file']['type'][$key];
            $target_path = $target_dir . '/' . sanitize_file_name($file_name);
            if (move_uploaded_file($file_tmp, $target_path)) {
                continue;
            } else {
                wp_send_json_success(['message' => "Failed to upload: $file_name"]);
            }
        }
        wp_send_json_success(['message' => 'All files uploaded successfully.']);
    }
    wp_die();
}

add_action('wp_ajax_upload_all_file_handler', 'upload_all_file_handler');

function delete_csv_file_handler()
{
    $upload_dir = wp_upload_dir();
    $subdir = 'csv-data';
    $target_dir = $upload_dir['basedir'] . '/' . $subdir;

    if (file_exists($target_dir)) {
        // Recursively delete the directory
        $files = array_diff(scandir($target_dir), ['.', '..']);
        foreach ($files as $file) {
            $file_path = $target_dir . '/' . $file;
            is_dir($file_path) ? delete_csv_folder($file_path) : unlink($file_path);
        }
        rmdir($target_dir);

        wp_send_json_success(['message' => 'CSV folder deleted successfully.']);
    } else {
        wp_send_json_error(['message' => 'CSV folder does not exist.']);
    }
    wp_die();
}

function delete_csv_folder($folder)
{
    $files = array_diff(scandir($folder), ['.', '..']);
    foreach ($files as $file) {
        $file_path = $folder . '/' . $file;
        is_dir($file_path) ? delete_csv_folder($file_path) : unlink($file_path);
    }
    rmdir($folder);
}

add_action('wp_ajax_delete_csv_file_handler', 'delete_csv_file_handler');
