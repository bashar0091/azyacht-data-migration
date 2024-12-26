<?php

function render_table($title = '', $btntext = '', $formclass = '')
{
?>
    <form method="post" action="" class="<?php echo esc_html($formclass); ?>">
        <table class="wp-list-table widefat fixed striped">
            <tbody>
                <tr>
                    <td>
                        <h3><?php echo esc_html($title); ?></h3>
                    </td>
                    <td>
                        <button type="submit" class="button button-primary"><?php echo esc_html($btntext); ?></button>
                        <div><b class="reloading_text"></b></div>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
<?php
}



function migration_form_render($title = '', $folder_path = '', $type = '')
{
    // Set hidden file_type field value based on passed type (csv or json)
    $file_type = '';
    if ($type == 'csv') {
        $file_type = 'csv';
    } elseif ($type == 'json') {
        $file_type = 'json';
    }
?>
    <div style="margin-top: 50px;">
        <h3><?php echo wp_kses_post($title) ?></h3>
        <?php
        if (file_exists($folder_path)) {
            // Get all files in the directory
            $files = array_diff(scandir($folder_path), array('..', '.'));

            // Filter out files that are not csv or json based on the passed type
            $files = array_filter($files, function ($file) use ($file_type) {
                return pathinfo($file, PATHINFO_EXTENSION) === $file_type;
            });
        ?>
            <form action="" class="migrate_csv_form">
                <table>
                    <tr>
                        <td>
                            <label>
                                <p><?php echo wp_kses_post($title) ?> File</p>
                                <p>
                                    <?php
                                    if (!empty($files)) {
                                        echo '<select name="file_name" id="file_name" required>';
                                        echo '<option>Select ' . $type . ' file</option>';
                                        foreach ($files as $file) {
                                            echo '<option value="' . esc_attr($file) . '">' . esc_html($file) . '</option>';
                                        }
                                        echo '</select>';
                                    }
                                    ?>
                                </p>
                            </label>
                            <label>
                                <p>Database Table Name</p>
                                <p>
                                    <?php
                                    $json_file = plugin_dir_path(__FILE__) . '../json-data/database-table-name.json';
                                    $json_data = file_get_contents($json_file);
                                    $data = json_decode($json_data, true);
                                    if ($data && is_array($data)) {
                                        echo '<select name="database_table_name">';
                                        echo '<option>Select Table Name</option>';
                                        foreach ($data as $key => $value) {
                                            echo '<option value="' . esc_attr($value) . '">' . esc_html($key) . '</option>';
                                        }
                                        echo '</select>';
                                    }
                                    ?>
                                </p>
                            </label>

                            <!-- Hidden file_type input to indicate file type (csv or json) -->
                            <input type="hidden" name="file_type" value="<?php echo esc_attr($file_type); ?>">

                            <div>
                                <?php
                                if ($type == 'csv') {
                                ?>
                                    <button type="button" class="button button-primary test_csv_btn">Test CSV</button>
                                <?php
                                }
                                ?>

                                <button type="submit" class="button button-primary">Migrate <?php echo wp_kses_post($type) ?></button>
                                <div><b class="reloading_text"></b></div>
                            </div>
                        </td>
                    </tr>
                </table>
            </form>

            <pre style="overflow-x: scroll;border:1px solid red;padding:10px;display:none" class="csv_viewer"></pre>
        <?php
        }
        ?>
    </div>
<?php
}
