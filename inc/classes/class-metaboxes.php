<?php
/**
 * Created by PhpStorm.
 * User: Eric Zeidan
 * Date: 02/02/2017
 * Time: 11:59
 */

class STMetaBoxClass {

    /**
     * The Class Constructor
     */
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'st_add_metabox'));
        add_action('save_post', array($this, 'save'));
        add_action('the_content', array($this, 'custom_message'));
    }

    public function st_add_metabox($post_type) {
        $post_types = array('ticketing');

        //limit meta box to certain post types
        if (in_array($post_type, $post_types)) {
        add_meta_box('ticketingbox',
            __('Ticketing','st_plugin'),
            array($this, 'st_fields_metabox'),
            $post_type,
            'normal',
            'high');
        }
    }

    public function st_fields_metabox($post) {

        // Add an nonce field so we can check for it later.
        wp_nonce_field('ticketng_nonce_check', 'ticketng_nonce_check_value');

        $input = get_post_meta($post->ID, '_ticketng_details', true);
        $reference = get_post_meta($post->ID, '_reference', true);
        //Display the reference
        ?>
        <p><label for="reference"><?php _e('Referencia', 'st_plugin'); ?></label>
        <select name="referencia" id="referencia" class="form-control">
        <option value="">Selecciona una Solicitud</option>
        <?php $posts = get_posts(array(
            'post_type' => "solicitudes",
            'post_status' => array('publish', 'pendiente', 'aprobada', 'revision', 'cerrada'),
            'post_author' => get_current_user_id(),
            'order' => 'DESC'
        ));

        if($posts) {
            foreach ($posts as $post) { ?>
                <option value="<?php echo $post->ID; ?>" <?php selected($reference, $post->ID); ?>><?php echo $post->post_title; ?></option>
            <?php  }
        }
        ?>
        </select>
        </p> <?php
        // Display the fields we need, using the current value.
        if(isset($input[0])) {
            foreach ($input as $key => $value) {
                $actualuser = get_user_by('ID', get_current_user_id()); ?>
                    <label
                        for="message-<?php echo $key; ?>"><?php _e('Message from: ' . $actualuser->nickname . ' Send: ' . date('l dS \o\f F Y h:i:s A', $input[$key]['timestamp']) , 'st_plugin'); ?></label><br/>
                    <?php $content = isset($input[$key]['message']) ? $input[$key]['message'] : "";;
                    $editor_id = "message-$key";
                    wp_editor( $content, $editor_id, array('quicktags' => false, 'textarea_name' => "input[$key][message]", 'textarea_rows' => 3,'teeny' => true, 'media_buttons' => false  ) ); ?>
                <input type="hidden" name="input[<?php echo $key; ?>][userid]"
                       value="<?php echo isset($input[$key]['userid']) ? $input[$key]['userid'] : get_current_user_id(); ?>">
                <input type="hidden" name="input[<?php echo $key; ?>][type]"
                       value="<?php echo ($key >= 1) ? 'answer' : 'mainmessage'; ?>">
                <input type="hidden" name="input[<?php echo $key; ?>][timestamp]" value="<?php echo (isset($input[$key]['timestamp'])) ? $input[$key]['timestamp'] : get_post_time('U', true); ?>">
                <?php
            }
            $key++;?>
                <label for="str-editor"><?php _e('Your Reply', 'st_plugin'); ?></label><br/>
                <?php $content = "";
                $editor_id = "str-editor";
                wp_editor( $content, $editor_id, array('textarea_name' => "input[$key][message]", 'teeny' => true, 'media_buttons' => false  ) ); ?>
            <input type="hidden" name="input[<?php echo $key; ?>][userid]" value="<?php echo isset($input[$key]['userid']) ? $input[$key]['userid'] : get_current_user_id(); ?>">
            <input type="hidden" name="input[<?php echo $key; ?>][type]" value="answer">
            <input type="hidden" name="input[<?php echo $key; ?>][timestamp]" value="<?php echo date('U'); ?>">
        <?php } else { ?>
            <p>
                <label for="strn-editor"><?php _e('Your Request', 'st_plugin'); ?></label><br/>
                <?php $content = "";
                $editor_id = "strn-editor";
                wp_editor( $content, $editor_id, array('textarea_name' => "input[0][message]", 'teeny' => true, 'media_buttons' => false  ) ); ?>
            </p>
            <input type="hidden" name="input[0][userid]" value="<?php echo isset($input[0]['userid']) ? $input[0]['userid'] : get_current_user_id(); ?>">
            <input type="hidden" name="input[0][type]" value="mainmessage">
            <input type="hidden" name="input[0][timestamp]" value="<?php echo date('U'); ?>">
            <?php

            }
        }

    public function save($post_id) {

        /*
        * We need to verify this came from the our screen and with
        * proper authorization,
        * because save_post can be triggered at other times.
        */

        // Check if our nonce is set.
        if (!isset($_POST['ticketng_nonce_check_value']))
            return $post_id;

        $nonce = $_POST['ticketng_nonce_check_value'];

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($nonce, 'ticketng_nonce_check'))
            return $post_id;

        // If this is an autosave, our form has not been submitted,
        //     so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return $post_id;

        // Check the user's permissions.
        if ('page' == $_POST['post_type']) {

            if (!current_user_can('edit_page', $post_id))
                return $post_id;

        } else {

            if (!current_user_can('edit_post', $post_id))
                return $post_id;
        }

        /* OK, its safe for us to save the data now. */

        // Sanitize the user input.
        if (isset($_POST['input'])) {
            $data = $_POST['input'];
            $data = array_filter($data, function($value) { return $value['message'] !== ''; });
            // Update the meta field.
            $update = update_post_meta($post_id, '_ticketng_details', $data);
            $updateref = update_post_meta($post_id, '_reference', $_POST['referencia']);
            $last = array_pop($data);
            $post_author = get_post_field( 'post_author', $post_id );
            if($update) {
                $mail = StHelpers::getInstance()->email_trigger($last['type'], $post_author, $post_id);
                if(!is_wp_error($mail)) {
                    echo "se envio correo";
                } else {
                    wp_die(json_enconde($mail));
                }
            }
        }

    }

    public function custom_message($content) {
        global $post;
        //retrieve the metadata values if they exist
        $data = get_post_meta($post->ID, '_ticketng_details', true);
        if (!empty($data) && !is_array($data)) {
            $custom_message = "<div style='background-color: #FFEBE8;border-color: #C00;padding: 2px;margin:2px;font-weight:bold;text-align:center'>";
            $custom_message .= $data['description']."<br/>";
            $custom_message .= "</div>";
            $content = $custom_message . $content;
        }

        return $content;
    }
}

new STMetaBoxClass();
