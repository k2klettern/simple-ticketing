<?php
/**
 * Created by PhpStorm.
 * User: Eric Zeidan
 * Date: 06/02/2017
 * Time: 14:46
 */

if(!class_exists('StHelpers')) {
    class StHelpers
    {
        /**
         *
         * @var Singleton
         */
        private static $instance;

        private $options_name = "_st_options_nots";
        private $plugin_options;

        /**
         * The Class Constructor
         */
        public function __construct()
        {
            $this->plugin_options = get_option($this->options_name);
        }

        /**
         * @return GetChannels|Singleton
         */
        public static function getInstance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * @param $type
         * @param $user
         * @param $postid
         * @return bool
         */
        public function email_trigger($type, $user = null, $postid)
        {
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
            $headers[] = 'From: CAAE <caae@caae.es>' . "\r\n";
            $perma = preg_replace ('/^(http)?s?:?\/\/[^\/]*(\/?.*)$/i', '$2', '' . get_permalink($postid));


            if (isset($user) && get_current_user_id() != $user) {
                $users = array(0 => $user);
            } elseif (!isset($user)) {
                $users = $this->plugin_options['users'];
            }

            $meta = get_post_meta($postid, '_ticketng_details', true);
            $first_value = reset($meta);
            $last_value = end($meta);


            if (isset($this->plugin_options['enabledusern']) && $type == 'answer') {
                $permalink = "extranet.caae.es" . $perma;
                $subject = __('Una nueva respuesta en el siguiente Mensaje', 'st_plugin');
                $userid = $first_value['userid'];
                // aqui meter los otros correos
                $userdata = get_userdata($userid);
                $to = $userdata->data->user_email;
                $body = __('Hola ' . $userdata->data->display_name . ', Hay un nueva respuesta mensaje, chequealo en el siguiente enlace <br>' . $permalink, 'st_plugin');
                $mail = wp_mail($to, $subject, $body, $headers);
                if(is_wp_error($mail))
                    write_log($mail);
            }

            if (isset($this->plugin_options['enabledslack'])) {
                switch ($type) {
                    case 'mainmessage';
                        $message = __('Hay un nuevo Mensaje, Por favor chequealo en el siguiente enlace ' . $permalink, 'st_plugin');;
                        break;
                    case 'answer';
                        $message = __('Hay un nuevo Mensaje, Cehquealo en el siguiente enlace ' . $permalink, 'st_plugin');
                        break;
                }
                $icon = ":robot_face:";

                $mail = $this->slack_send($message, NULL, $icon);
                if(is_wp_error($mail))
                    write_log($mail);
            }

            if (isset($this->plugin_options['enabled'])) {
                $permalink = "gestionsemillas.caae.es" . $perma;

                $users = $this->plugin_options['users'];
                $users[] = $last_value['userid'];

                if($users) {
                    foreach ($users as $userid) {
                        $userdata = get_userdata($userid);
                        $to = $userdata->data->user_email;
                        switch ($type) {
                            case 'mainmessage';
                                $subject = __('Un Nuevo Mensaje espera tu respuesta', 'st_plugin');
                                $body = __('Hay un nuevo Mensaje en el siguiente enlace <br> ' . $permalink, 'st_plugin');
                                break;
                            case 'answer';
                                $subject = __('Una nueva respuesta a su mensaje', 'st_plugin');
                                $body = __('Hay una nueva respuesta a su mensaje en el siguiente enlace <br>' . $permalink, 'st_plugin');
                                break;
                        }

                        $mail = wp_mail($to, $subject, $body, $headers);
                        if(is_wp_error($mail))
                            write_log($mail);
                    }
                }
            }
            if (isset($mail) && !is_wp_error($mail)) {
                return $mail;
            } else {
                return;
            }
        }

        private function slack_send($message, $room = "engineering", $icon = ":robot_face:")
        {
            $room = ($room) ? $room : "engineering";
            $data = "payload=" . json_encode(array(
                    //            "channel"       =>  "#{$room}",
                    "text" => $message,
                    "icon_emoji" => $icon
                ));

            $url = $this->plugin_options['slackurl'];
            $args = array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'body' => $data,
                'cookies' => array()
            );
            $result = wp_remote_post($url, $args);

            return $result;
        }


        public function build_footer_buttons()
        {
            if (is_user_logged_in() && current_user_can('read')) {
                $buttons = '<div class="st-action-buttons">';
                $buttons .= '<p><a href="' . $this->get_structured_link('st-action', 'new') . '" class="btn btn-success">' . __('Nuevo Mensaje', 'st_plugin') . '</a></p>';
                //$buttons .= '<p><button type="submit" href="' . get_home_url() . '/ticketing/new/" class="btn btn-default">' . __('New Ticket', 'st_plugin') . '</button></p>';
                $buttons .= '</div>';


            } else {
                $buttons = "<p><a href='" . wp_login_url() . "' class='btn btn-success'>" . __('Debes Ingresar para enviar un Mensaje', 'st_plugin') . "</a></p>";
            }
            return $buttons;
        }

        public function build_footer_buttons_front()
        {
            if (is_user_logged_in() && current_user_can('read')) {
                $buttons = '<div class="st-action-buttons">';
                $buttons .= '<p><a href="' . home_url() . '/mensajes/' . $this->get_structured_link('st-action', 'new') . '">' . __('Nuevo Mensaje', 'st_plugin') . '</a></p>';
                //$buttons .= '<p><button type="submit" href="' . get_home_url() . '/ticketing/new/" class="btn btn-default">' . __('New Ticket', 'st_plugin') . '</button></p>';
                $buttons .= '</div>';


            } else {
                $buttons = "<p><a href='" . wp_login_url() . "' class='btn btn-default'>" . __('Debes Ingresar para enviar un Mensaje', 'st_plugin') . "</a></p>";
            }
            return $buttons;
        }

        public function get_the_status($postid)
        {
            $result = get_post_meta($postid, '_is_new', true);
            $post = get_post($postid);
            $uid = get_current_user_id();
            $lastuser = $this->get_last_answer_user_id($postid);
            if ($result == 1 && $post->post_author != $uid) {
                $response = "Mensaje Recibido";
            } elseif ($result == 2 && $post->post_author == $uid && $uid != $lastuser) {
                $response = "Nueva Respuesta recibida";
            } elseif ($result == 2 && $post->post_author == $uid && $uid == $lastuser) {
                $response = "Respuesta enviada";
            } elseif ($result == 2 && $post->post_author != $uid && $uid == $lastuser) {
                $response = "Respuesta enviada";
            } elseif ($result == 2 && $post->post_author != $uid) {
                $response = "Respuesta Recibida";
            } elseif ($result == 2) {
                $response = "Respondido";
            } elseif ($result == 1) {
                $response = "Mensaje Nuevo";
            } else {
                $response = "Mensaje Recibido";
            }

            return $response;
        }

        public function build_action_buttons($postid)
        {
            $nonce = wp_create_nonce('buttons-ticketing');
            $buttons = "<div class=\"st-action-buttons\">";
            if (!is_user_logged_in()) {
                $buttons .= "<p><a href='" . wp_login_url() . "' class='btn btn-default'>" . __('Login here', 'st_plugin') . "</a></p></div>";
                return $buttons;
            }
            if (current_user_can('read')) {
                $buttons .= "<a class=\"delete-ticket\" href=\"#\" data-postid=\"" . $postid . "\" data-nonce=\"" . $nonce . "\"><span class=\"dashicons dashicons-trash\"></span></a></span>";
            }
            if (current_user_can('administrator', 'editor') && get_post_status($postid) != "closed") {
                $buttons .= "<a class=\"close-ticket\" href=\"#\" data-postid=\"" . $postid . "\" data-nonce=\"" . $nonce . "\"><span class=\"dashicons dashicons-lock\"></span></a></span>";
            } elseif (current_user_can('administrator', 'editor') && get_post_status($postid) == "closed") {
                $buttons .= "<a class=\"reopen-ticket\" href=\"#\" data-postid=\"" . $postid . "\" data-nonce=\"" . $nonce . "\"><span class=\"dashicons dashicons-unlock\"></span></a></span>";
            }
            $buttons .= "</div>";

            return $buttons;
        }

        private function get_structured_link($varname, $value)
        {
            if ('' != get_option('permalink_structure')) {
                // using pretty permalinks, append to url
                $read = user_trailingslashit($varname . '/' . $value); // www.example.com/pagename/test
            } else {
                $read = add_query_arg($varname, $value); // www.example.com/pagename/?test
            }
            return $read;
        }

        public function get_message_status($postid)
        {
            $result = get_post_meta($postid, '_is_new', true);
            return $result;
        }

        public function update_message_status($postid, $value)
        {
            $result = update_post_meta($postid, '_is_new', $value);
            return $result;
        }

        public function get_messages_by_solicitud_ref($solicitudid)
        {
            global $post;
            ?>
            <form method="POST" action=""><?php
            $postt = get_posts(
                array(
                    'post_type' => 'ticketing',
                    'numberpost' => 1,
                    'meta_query' => array( array(
                        'key' => '_reference',
                        'value' => $solicitudid
                    )
                    )
                )
            );
            $postt = $postt[0];

            $input = get_post_meta($postt->ID, '_ticketng_details', true);

            // Display the fields we need, using the current value.
            if (is_array($input) && isset($input[0])) {
                foreach ($input as $key => $value) {
                    $actualuser = get_user_by('ID', $input[$key]['userid']);
                    ?>
                    <div class="st-row">
                        <div
                            class="st-col-1"><?php echo isset($input[$key]['message']) ? $input[$key]['message'] : ""; ?></div>
                        <?php if ($key == 0) { ?>
                            <div class="st-col-2 main-msg"><span class="inmsg"><span
                                        class="user-name"><?php echo $actualuser->first_name . " " . $actualuser->last_name; ?></span><br/><span
                                        class="nick-name"><?php echo $actualuser->user_nicename; ?></span></div>
                        <?php } else { ?>
                            <div class="st-col-2"><span class="inmsg"><span
                                        class="user-name"><?php echo $actualuser->first_name . " " . $actualuser->last_name; ?></span><br/><span
                                        class="nick-name"><?php echo $actualuser->user_nicename; ?></span></div>
                        <?php } ?>
                    </div>
                    <?php setlocale(LC_ALL, "es_ES", 'Spanish_Spain', 'Spanish'); ?>
                    <div
                        class="st-details"><?php echo isset($input[$key]['timestamp']) ? iconv('ISO-8859-2', 'UTF-8', strftime("%A %d de %B del %Y %T", $input[$key]['timestamp'])) : "Creado el: " . $post->post_date; ?></div>
                    <?php
                }
                      $key++;
                        if(current_user_can('read') && $postt->post_status != 'closed') {
                            wp_nonce_field('ticketing-action', 'ticketing-nonce-field');
                            ?>
                            <p>
                                <h2 class="secound-title"><?php _e('Tu Respuesta', 'st_plugin'); ?></h2>
                                <?php
                                $content = isset($input[$key]['message']) ? $input[$key]['message'] : "";
                                $editor_id = "st-editor";

                                wp_editor( $content, $editor_id, array('textarea_name' => "input[$key][message]", 'teeny' => true, 'media_buttons' => false, 'editor_height' => '25px' ) );
                                ?>
                            </p>
                            <input type="hidden" name="input[<?php echo $key; ?>][userid]"
                                   value="<?php echo isset($input[$key]['userid']) ? $input[$key]['userid'] : get_current_user_id(); ?>">
                            <input type="hidden" name="postid" value="<?php echo $postt->ID; ?>">
                            <input type="hidden" name="key" value="<?php echo $key; ?>">
                            <input type="hidden" name="input[<?php echo $key; ?>][type]" value="answer">
                            <input type="hidden" name="input[<?php echo $key; ?>][timestamp]" value="<?php echo date('U'); ?>">
                            <button class="btn btn-md btn-submit" onclick="history.back()" type="button"><i class="fa fa-chevron-left"></i> Volver</button>
                            <input type="submit" class="btn btn-success btn-send-msg" value="<?php _e('Enviar', 'st_plugin'); ?>">
                            <?php
                            if($input[$key-1]['userid'] != get_current_user_id()) {
                                $updatemeta = StHelpers::getInstance()->update_message_status($postt->ID, 0);
                            }
                        } elseif ($postt->post_status != 'closed') {
                            echo "<p><a href='" . wp_login_url() . "'>" . __('Debes Ingresar para ver el mensaje', 'st_plugin') . "</a></p>";
                        } else {
                            _e('Este mensaje esta cerrado!', 'st_plugin');
                        }
                        } else {
                             wp_nonce_field('front-st-action', 'front-st-nonce-field'); ?>
                            <div class="form-group your-request">
                                <label for="stn-editor"><?php _e('Tu Mensaje', 'st_plugin'); ?></label><br/>
                                <?php $content = "";
                                $editor_id = "stn-editor";
                                wp_editor( $content, $editor_id, array('textarea_name' => "input[0][message]", 'teeny' => true, 'media_buttons' => false, 'editor_height' => '35px'  ) ); ?>
                            </div>
                            <input type="hidden" name="title" value="<?php echo $post->post_title; ?>">
                            <input type="hidden" name="referencia" value="<?php echo $post->ID; ?>">
                            <input type="hidden" name="input[0][userid]" value="<?php echo get_current_user_id(); ?>">
                            <input type="hidden" name="input[0][type]" value="mainmessage">
                            <button class="btn btn-md btn-submit" onclick="history.back()" type="button"><i class="fa fa-chevron-left"></i> Volver</button>
                            <input type="submit" class="btn btn-success btn-send-msg" value"<?php _e('Enviar', 'st_plugin'); ?>">
                    <?php
            } ?>
                </form>
            <?php
        }

        public function get_status_by_solicitud_ref($solicitudid)
        {
            $postt = get_posts(
                array(
                    'post_type' => 'ticketing',
                    'numberpost' => 1,
                    'meta_query' => array(array(
                        'key' => '_reference',
                        'value' => $solicitudid
                    )
                    )
                )
            );
            $postt = $postt[0];

            if($postt) {
                $response = "<p><a href=\"" . get_permalink($postt->ID) . "\">Ver</a>";
                $isnew = $this->get_message_status($postt->ID);
                $response .= ($isnew) ? " <i class=\"fa fa-star\" aria-hidden=\"true\" style=\"color: #ffa500;\"></i> Nuevo</td>" : "</p>";
                return $response;
            } else {
                return false;
            }
        }
        public function get_last_answer_user_id($postid) {
            $data = get_post_meta($postid, '_ticketng_details', true);
            $last = end($data);
            return($last['userid']);
        }

        public function get_main_message_status_for_user($uid) {
            if(!current_user_can('administrator')) {
                $messages = get_posts(array('post_type' => 'ticketing', 'order' => 'DESC', 'posts_per_page' => -1, 'author' => $uid));
            } else {
                $messages = get_posts(array('post_type' => 'ticketing', 'order' => 'DESC', 'posts_per_page' => -1));
            }

            $counter = 0;
            foreach($messages as $msg) {
                $lastuid = $this->get_last_answer_user_id($msg->ID);
                if($status = $this->get_message_status($msg->ID) && $lastuid != $uid) {
                    $counter++;
                }
            }
            return $counter;
        }
    }




}
