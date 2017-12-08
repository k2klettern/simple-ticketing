<div class="table-responsive">
    <table class="table table-bordered table-hover table-striped">
        <thead>
        <tr>
            <th><span class="sort"
                      data-sort="postname"><?php _e('Mensaje', 'st_plugin'); ?></span>
            </th>
            <th><span class="sort"
                      data-sort="status"><?php _e('Estado', 'st_plugin'); ?></span>
            </th>
            <th><span class="sort"
                      data-sort="date"><?php _e('Fecha', 'st_plugin'); ?></span>
            </th>
            <th><span class="sort"
                      data-sort="moddate"><?php _e('&Uacute;ltima Respuesta', 'st_plugin'); ?></span>
            </th>
            <th><?php _e('Acciones', 'st_plugin'); ?></th>
        </tr>
        </thead>
        <tbody class="list">
        <?php
        // Start the Loop.
        while (have_posts()) : the_post();
            echo "<tr>";
            echo "<td class=\"postname\"><a href=\"" . get_permalink($post->ID) . "\">" . get_the_title($post->ID) . "</a></td>";
            echo "<td class=\"status\">" . StHelpers::getInstance()->get_the_status($post->ID) . "</td>";
            echo "<td class=\"date\">" . get_the_date() . "</td>";
            echo "<td class=\"moddate\">" . $post->post_modified_gmt . "</td>";
            echo "<td>" . StHelpers::getInstance()->build_action_buttons($post->ID);
            $isnew = StHelpers::getInstance()->get_message_status($post->ID);
            if ($isnew == 1)
                echo "<i class=\"fa fa-star\" aria-hidden=\"true\" style=\"color: #ffa500;\"></i> Nuevo</td>";
            elseif ($isnew == 2)
                echo "<i class=\"fa fa-reply\" aria-hidden=\"true\" style=\"color: #ffa500;\"></i> Nueva Respuesta</td>";
            else
                echo"</td>";
            echo "</tr>";
            // End the loop.
        endwhile; ?>
        </tbody>
    </table>