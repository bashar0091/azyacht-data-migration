<?php

function render_table($title = '', $btntext = '', $formclass = '')
{
?>
    <form method="post" action="" class="<?php echo esc_html($formclass); ?>">
        <table class="wp-list-table widefat fixed striped">
            <tbody>
                <tr>
                    <td>
                        <h3>Delete Existing <u><?php echo esc_html($title); ?></u></h3>
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
