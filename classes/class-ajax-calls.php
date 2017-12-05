<?php
/**
 * Created by PhpStorm.
 * User: Eric Zeidan
 * Date: 10/02/2017
 * Time: 11:31
 */

Class StAjaxCalls{

    /**
     * The Class Constructor
     */
    public function __construct() {
        add_action( 'wp_ajax_nopriv_st_delete_ticketing', array($this, 'st_delete_ticketing') );
        add_action( 'wp_ajax_st_delete_ticketing', array($this, 'st_delete_ticketing') );
        add_action( 'wp_ajax_nopriv_st_close_ticketing', array($this, 'st_close_ticketing') );
        add_action( 'wp_ajax_st_close_ticketing', array($this, 'st_close_ticketing') );
        add_action( 'wp_ajax_nopriv_st_reopen_ticketing', array($this, 'st_reopene_ticketing') );
        add_action( 'wp_ajax_st_reopen_ticketing', array($this, 'st_reopen_ticketing') );
    }

    function st_delete_ticketing() {
        if ( !wp_verify_nonce( $_REQUEST['nonce'], "buttons-ticketing")) {
            exit("No naughty business please");
        }
        $post_id = $_POST['postid'];

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX) {
            $trashed = wp_trash_post( $post_id  );

            if(is_wp_error($trashed)) {
                $result['type'] = 'error';
                $result['text'] = 'error update : ' . json_decode($trashed);
            } else {
                $result = array('type' => 'success', 'text' => 'Post trashed succesfully');
            }
        } else {
            $result = array('type' => 'error', 'text' => 'Not ajax call');
        }

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $result = json_encode($result);
            echo $result;
        }
        else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }

        die();

    }

    function st_close_ticketing() {
        if ( !wp_verify_nonce( $_REQUEST['nonce'], "buttons-ticketing")) {
            exit("No naughty business please");
        }
        $post_id = $_POST['postid'];
        $my_post = array(
            'ID'           => $post_id,
            'post_status' => "closed"
        );
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX) {
            $trashed = wp_update_post( $my_post  );

            if(is_wp_error($trashed)) {
                $result['type'] = 'error';
                $result['text'] = 'error update : ' . json_decode($trashed);
            } else {
                $result = array('type' => 'success', 'text' => 'Post closed succesfully');
            }
        } else {
            $result = array('type' => 'error', 'text' => 'Not ajax call');
        }

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $result = json_encode($result);
            echo $result;
        }
        else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }

        die();

    }

    function st_reopen_ticketing() {
        if ( !wp_verify_nonce( $_REQUEST['nonce'], "buttons-ticketing")) {
            exit("No naughty business please");
        }
        $post_id = $_POST['postid'];
        $my_post = array(
            'ID'           => $post_id,
            'post_status' => "publish"
        );
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX) {
            $trashed = wp_update_post( $my_post  );

            if(is_wp_error($trashed)) {
                $result['type'] = 'error';
                $result['text'] = 'error update : ' . json_decode($trashed);
            } else {
                $result = array('type' => 'success', 'text' => 'Post closed succesfully');
            }
        } else {
            $result = array('type' => 'error', 'text' => 'Not ajax call');
        }

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $result = json_encode($result);
            echo $result;
        }
        else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }

        die();

    }
}

new StAjaxCalls();


