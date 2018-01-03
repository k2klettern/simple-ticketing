<?php
/**
 * Created by PhpStorm.
 * User: Eric Zeidan
 * Date: 06/02/2017
 * Time: 16:16
 */

ob_start();
get_header();

$main_column_size = 12;
?>
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="col-12">
                <?php
                // Start the loop.
                $key=0;
                while (have_posts()) :
                the_post();
                $key++;
                if (current_user_can('read') && $post->post_status != 'closed') {
                wp_nonce_field('ticketing-action', 'ticketing-nonce-field');
                ?>
                <div class="col-12">
                    <h1 class="page-header"><?php the_title(); ?></h1>
                </div>
                <div class="col-12">
                    <form method="POST" action=""><?php

                        // Include the single post content template.
                        $input = get_post_meta($post->ID, '_ticketng_details', true);

                        // Display the fields we need, using the current value.
                        if(is_array($input) && isset($input[0])) {
                            foreach ($input as $key => $value) {
                                $actualuser = get_user_by('ID', $input[$key]['userid']);
                                ?>
                                <div class="st-row">
                                    <div class="st-col-1"><?php echo isset($input[$key]['message']) ? $input[$key]['message'] : ""; ?></div>
                                    <?php if($key==0) { ?>
                                        <div class="st-col-2 main-msg"><span class="inmsg"><span class="user-name"><?php echo $actualuser->first_name . " " . $actualuser->last_name; ?></span><br/><span class="nick-name"><?php echo $actualuser->user_nicename; ?></span></div>
                                    <?php } else { ?>
                                    <div class="st-col-2"><span class="inmsg"><span class="user-name"><?php echo $actualuser->first_name . " " . $actualuser->last_name; ?></span><br/><span class="nick-name"><?php echo $actualuser->user_nicename; ?></span></div>
                                    <?php } ?>
                                </div>
                                <?php setlocale(LC_ALL, "es_ES", 'Spanish_Spain', 'Spanish'); ?>
                                <div class="st-details"><?php echo isset($input[$key]['timestamp']) ? iconv('ISO-8859-2', 'UTF-8', strftime("%A %d de %B del %Y %T", $input[$key]['timestamp'])) : "Creado el: " . $post->post_date; ?></div>
                                <?php
                            }
                            $key++;
                            if(current_user_can('read') && $post->post_status != 'closed') {
                                wp_nonce_field('ticketing-action', 'ticketing-nonce-field');
                                ?>
                                <p>
                                    <h2 class="secound-title"><?php _e('Tu Respuesta', ST_TEXT_DOMAIN); ?></h2>
                                    <?php
                                    $content = isset($input[$key]['message']) ? $input[$key]['message'] : "";
                                    $editor_id = "st-editor";

                                    wp_editor( $content, $editor_id, array('textarea_name' => "input[$key][message]", 'teeny' => true, 'media_buttons' => false  ) );
                                    ?>
                                </p>
                                <input type="hidden" name="input[<?php echo $key; ?>][userid]"
                                       value="<?php echo isset($input[$key]['userid']) ? $input[$key]['userid'] : get_current_user_id(); ?>">
                                <input type="hidden" name="postid" value="<?php echo $post->ID; ?>">
                                <input type="hidden" name="key" value="<?php echo $key; ?>">
                                <input type="hidden" name="input[<?php echo $key; ?>][type]" value="answer">
                                <input type="hidden" name="input[<?php echo $key; ?>][timestamp]" value="<?php echo date('U'); ?>">
                                <button class="btn btn-default" onclick="history.back()" type="button">Volver</button>
                                <button class="btn btn-default" type="submit"><?php _e('Enviar', ST_TEXT_DOMAIN); ?></button>
                                <?php
                                if($input[$key-1]['userid'] != get_current_user_id()) {
                                    $updatemeta = StHelpers::getInstance()->update_message_status($post->ID, 0);
                                }
                            } elseif ($post->post_status != 'closed') {
                                echo "<p><a href='" . wp_login_url() . "'>" . __('Debes Ingresar para ver el mensaje', ST_TEXT_DOMAIN) . "</a></p>";
                            } else {
                                _e('Este mensaje esta cerrado!', ST_TEXT_DOMAIN);
                            }
                            } else {
                            if(current_user_can('subscriber')) {
                                wp_nonce_field('ticketing-action', 'ticketing-nonce-field');?>

                                <label for="stn-editor"><?php _e('Tu Requerimiento', ST_TEXT_DOMAIN); ?></label><br/>
                                   <?php $content = "";
                                    $editor_id = "stn-editor";
                                    wp_editor( $content, $editor_id, array('textarea_name' => "input[$key][message]", 'teeny' => true, 'media_buttons' => false, 'textarea_rows' => 3  ) ); ?>

                                <input type="hidden" name="input[<?php echo $key; ?>][userid]" value="<?php echo isset($input[$key]['userid']) ? $input[$key]['userid'] : get_current_user_id(); ?>">
                                <input type="hidden" name="postid" value="<?php echo $post->ID; ?>">
                                <input type="hidden" name="input[0][timestamp]" value="<?php echo date('U'); ?>">
                                <button class="btn btn-md btn-submit" onclick="history.back()" type="button"><i class="fa fa-chevron-left"></i> Volver</button>
                                <input type="submit" class="btn btn-success btn-send-msg" value="<?php _e('Enviar', ST_TEXT_DOMAIN); ?>">
                                <?php
                                if($input[$key-1]['userid'] != get_current_user_id()) {
                                    $updatemeta = StHelpers::getInstance()->update_message_status($post->ID, 0);
                                }
                            } else {
                                echo "<p><a href='" . wp_login_url() . "'>". __('Ingresa aqu&iacute; para enviar un Mensaje', ST_TEXT_DOMAIN) . "</a></p>";
                            }
                        }
                        }

                        // End of the loop.
                        endwhile;
                        ?>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<?php get_footer(); ?>
