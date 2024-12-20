<?php

add_action('wp_ajax_delete_existing_candidate', 'delete_existing_candidate_handler');
function delete_existing_candidate_handler()
{
    $args = array(
        'role'    => 'civi_user_candidate',
        'fields'  => 'ID',
    );
    $users = get_users($args);
    if (!empty($users)) {
        foreach ($users as $user_id) {
            $meta_keys = get_user_meta($user_id);
            foreach ($meta_keys as $meta_key => $meta_value) {
                delete_user_meta($user_id, $meta_key);
            }
            wp_delete_user($user_id);
        }
        wp_send_json_success(['message' => 'Candidate deleted successfully.']);
    } else {
        wp_send_json_success(['message' => 'No Candidate Found.']);
    }

    wp_die();
}


add_action('wp_ajax_delete_existing_posttypedata', 'delete_existing_posttypedata');
function delete_existing_posttypedata()
{
    $posttype = isset($_POST['posttype']) ? $_POST['posttype'] : '';
    $name = '';
    if ($posttype == 'company') {
        $name = 'Company';
    } else if ($posttype == 'jobs') {
        $name = 'Job';
    } else if ($posttype == 'email-log') {
        $name = 'Email';
    } else if ($posttype == 'notification') {
        $name = 'Notification';
    }
    global $wpdb;
    $posts = get_posts([
        'post_type' => $posttype,
        'posts_per_page' => -1,
        'post_status' => 'any',
    ]);
    if (!empty($posts)) {
        foreach ($posts as $post) {
            $wpdb->delete($wpdb->postmeta, ['post_id' => $post->ID]);
            wp_delete_post($post->ID, true);
        }
        wp_send_json_success(['message' => $name . ' deleted successfully.']);
    } else {
        wp_send_json_success(['message' => 'No ' . $name . ' Found.']);
    }


    wp_die();
}
