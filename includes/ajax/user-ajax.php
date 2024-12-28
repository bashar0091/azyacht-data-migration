<?php

function create_user_column_handler()
{
    // Define the path to the JSON file
    $usercolumn_json_file = plugin_dir_path(__FILE__) . '../../json-data/user-table-column.json';

    // Check if the JSON file exists
    if (file_exists($usercolumn_json_file)) {
        $json_data = json_decode(file_get_contents($usercolumn_json_file), true);

        global $wpdb;

        // Loop through JSON data to add columns to the user table
        foreach ($json_data as $user_data) {
            foreach ($user_data as $key => $value) {
                // Check if the column already exists in the user table
                $column_exists = $wpdb->get_results(
                    $wpdb->prepare(
                        "SHOW COLUMNS FROM {$wpdb->users} LIKE %s",
                        $key
                    )
                );

                // If the column doesn't exist, add it
                if (empty($column_exists)) {
                    $wpdb->query(
                        $wpdb->prepare(
                            "ALTER TABLE {$wpdb->users} ADD {$key} VARCHAR(255) DEFAULT ''"
                        )
                    );
                }
            }
        }

        // Send success response
        wp_send_json_success(['success' => true]);
    }

    wp_die();
}

add_action('wp_ajax_create_user_column_handler', 'create_user_column_handler');


// migrate users handler
function migrate_user_handler()
{
    $upload_dir = wp_upload_dir();
    $csv_file_path = $upload_dir['basedir'] . '/yacht-data/csv/Person.csv';
    $usercolumn_json_file = plugin_dir_path(__FILE__) . '../../json-data/user-table-column.json';
    $json_data = file_get_contents($usercolumn_json_file);
    $data = json_decode($json_data, true);

    if (is_array($data) && !empty($data)) {
        $user_column_keys = array_keys($data[0]);

        if (file_exists($csv_file_path)) {
            if (($handle = fopen($csv_file_path, 'r')) !== false) {
                $headers = fgetcsv($handle);

                while (($row = fgetcsv($handle)) !== false) {
                    $user_data = array_combine($headers, $row);

                    $first_name = $user_data['FirstName'];
                    $last_name = $user_data['Surname'];
                    $user_email = !empty($user_data['WorkEMail']) ? $user_data['WorkEMail'] : strtolower($user_data['FirstName'] . '_' . $user_data['Surname']) . '@gmail.com';
                    $user_login = strtolower($user_data['KnownAs']);
                    $display_name = $first_name . ' ' . $last_name;
                    $password = wp_generate_password();

                    $userdata = array(
                        'user_login'    => $user_login,
                        'user_pass'     => $password,
                        'user_email'    => $user_email,
                        'first_name'    => $first_name,
                        'last_name'     => $last_name,
                        'nickname'      => $user_login,
                        'display_name'  => $display_name,
                        'role'          => 'civi_user_candidate'
                    );

                    $user_id = wp_insert_user($userdata);
                    if (is_wp_error($user_id)) {
                        $error_message = $user_id->get_error_message();
                        wp_send_json_error(['message' => 'Error inserting user: ' . $error_message]);
                    }

                    // need userID change also in csv
                    $update_data = array();
                    foreach ($user_column_keys as $key) {
                        if (isset($user_data[$key])) {
                            $update_data[$key] = $user_data[$key];
                        }
                    }

                    global $wpdb;
                    $wpdb->update(
                        $wpdb->users,
                        $update_data,
                        array('ID' => $user_id)
                    );
                }
                fclose($handle);
                wp_send_json_success(['message' => 'Users imported and updated successfully.']);
            }
        }
    }

    wp_die();
}

add_action('wp_ajax_migrate_user_handler', 'migrate_user_handler');
