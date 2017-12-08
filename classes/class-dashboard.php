<?php

if(!class_exists('stDashboardWindows')) {
    class stDashboardWindows {

        private $userid;

        public function __construct()
        {
            if(is_admin()) {
                $this->initHooks();
                $this->userid = get_current_user_id();
            }
        }

        public function initHooks() {
            add_action( 'wp_dashboard_setup', array($this, 'addTicketDashboardWidgets'));
            add_action( 'admin_init', array($this, 'dashboard_new_ticket'));
        }

        public function addTicketDashboardWidgets() {
            wp_add_dashboard_widget('list_ticket_dashboard_widget', 'Sistema de Tickets', array($this, 'ticketListDashboardWidget'));
            wp_add_dashboard_widget('create_ticket_dashboard_widget', 'Crear un Nuevo Mensaje', array($this, 'createTicketListDashboardWidget'));
        }

        public function ticketListDashboardWidget() {
            if (current_user_can('administrator')) {
                $posts = get_posts('&post_type=ticketing&order=DESC&posts_per_page=-1');
            } else {
                $posts = get_posts("&post_type=ticketing&order=DESC&posts_per_page=-1&author=$this->userid");
            } ?>
            <div id="tickets">
                <p><?php $counter = StHelpers::getInstance()->get_main_message_status_for_user($this->userid);
                    if($counter) {
                        echo $counter ? "Tienes " . $counter . " mensajes nuevos " : "";
                    } else {
                        echo "No tienes mensajes nuevos";
                    }
                    ?></p>
            </div>
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
                    foreach ( $posts as $post ) : setup_postdata( $post );
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
                    endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php ;
            wp_reset_postdata();
        }

        public function createTicketListDashboardWidget() {
                    global $input; ?>
            <form id="new-st" method="POST" action="">
                    <?php
                    $input = isset($_POST['input']) ? $_POST['input'] : "";
                    if (isset($GLOBALS['answer'])) {
                        echo $GLOBALS['answer'];
                    } ?>

                    <?php wp_nonce_field('dashboard-st-action', 'dashboard-st-nonce-field'); ?>
                    <div clas="form-group">
                        <label for="title"><?php _e('Asunto', 'st_plugin'); ?></label>
                        <input type="text" class="form-control" id="title" name="title"
                               value="<?php echo isset($_POST['title']) ? $_POST['title'] : ""; ?>">
                    </div>
                    <div class="form-group your-request">
                        <label for="stn-editor"><?php _e('Tu Mensaje', 'st_plugin'); ?></label><br/>
                        <?php $content = "";
                        $editor_id = "stn-editor";
                        wp_editor($content, $editor_id, array('textarea_name' => "input[0][message]", 'teeny' => true, 'media_buttons' => false)); ?>
                    </div>
                    <input type="hidden" name="input[0][userid]" value="<?php echo get_current_user_id(); ?>">
                    <input type="hidden" name="input[0][type]" value="mainmessage">
                    <button class="btn btn-md btn-submit" id="volver" onclick="history.back()" type="button"><i class="fa fa-chevron-left"></i> Volver</button>
                    <input type="submit" class="btn btn-success btn-send-msg" value"<?php _e('Enviar', 'st_plugin'); ?>">
            </form>
       <?php }

        public function dashboard_new_ticket() {
            global $answer;
            global $input;
            if(isset($_POST['dashboard-st-nonce-field'])) {
                if(!wp_verify_nonce($_POST['dashboard-st-nonce-field'], 'dashboard-st-action')) {
                    wp_die('no script kiddies!');
                }

                if(isset($_POST['title']) && isset($_POST['input'])) {
                    $input = $_POST['input'];
                    if(empty($input[0]['message'])) {
                        $answer = "<div class='msg-error'>" . __('No se puede enviar un mensaje vacio, intenta de nuevo',  ST_TEXT_DOMAIN) . "</div>";
                        wp_die( 'No se puede enviar un mensaje vacio, intenta de nuevo', null, array( 'back_link' => true ) );
                        return;
                    }
                    $title = $_POST['title'];
                    $mypost = get_page_by_title(wp_strip_all_tags($title), "", 'ticketing');
                    if($mypost) {
                        wp_die("<div class='error-msg'>" . __('Tenemos un problema, ya existe un mensaje con el mismo asunto, Intentalo de nuevo.', ST_TEXT_DOMAIN) . "</div>", null, array('back_link' => true));
                        return;
                    }
                    $args = array(
                        'post_type' => 'ticketing',
                        'post_title' => wp_strip_all_tags($title),
                        'post_status' => 'publish',
                        'post_author' => get_current_user_id(),
                        'post_date' => date("Y-m-d H:i:s")
                    );
                    $postid = wp_insert_post($args);
                    if($postid) {
                        $meta = update_post_meta($postid, '_ticketng_details', $input);
                        $updatemeta = StHelpers::getInstance()->update_message_status($postid, 1);
                    }
                    if($meta) {
                        $mail = StHelpers::getInstance()->email_trigger($input[0]['type'], $input[0]['userid'], $postid);
                        $_SESSION['replying'] = 1;

                        wp_safe_redirect(home_url($_POST['_wp_http_referer']));
                        die;
                    } else {
                        $answer = "<div class='alert alert-danger'>" . __('Existe un problema, intentalo de nuevo m&aacute;s tarde', ST_TEXT_DOMAIN) . "</div>";
                        return;
                    }
                } else {
                    $answer = "<div class='alert alert-danger'>" . __('Todos los Campos son requeridos', ST_TEXT_DOMAIN) . "</div>";
                    return;
                }
            }
        }

    }

    $db = new stDashboardWindows();
}