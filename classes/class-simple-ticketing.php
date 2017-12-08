<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * We create the class for the plugin
 * @author: Eric Zeidan <ezeidan@kapturall.com>
 */

class st_plugin{

	/**
	 * The Class Constructor
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * init_hooks
	 *
	 * Load all needed hooks
	 *
	 * @author Eric Zeidan <eric@zeidan.info>
	 * @since 1.0
	 */
	public function init_hooks() {
		add_action( 'admin_menu',array($this,"st_add_option_menu"));
		add_action( 'admin_enqueue_scripts', array($this, "st_admin_init"));
		add_action( 'wp_enqueue_scripts', array($this, "st_front_scripts"));
		add_action( 'init', array($this, 'st_create_posttypes'));
		add_filter( 'single_template', array($this, 'st_single_template'), 99 );
		add_filter( 'archive_template', array($this, 'st_display_template' ), 99);
		add_filter( 'enter_title_here', array($this, 'st_change_title_text') );
		add_action ( 'init', array($this, 'st_front_end_save_message') );
		add_filter( 'init', array($this,'st_rewrite_rules'), 10, 0);
		add_filter( 'query_vars', array($this, 'st_query_vars_filter') );
		add_action( 'template_redirect', array($this, 'front_end_new_ticket'));
		add_action( 'init', array($this, 'st_custom_post_status') );
		add_action( 'post_submitbox_misc_actions', array($this, 'st_post_submitbox_misc_actions' ));
		add_action( 'init', array($this, 'st_rewrite_tag'), 10, 0);

		// add twitter adresse and setting option to plugins list
		add_filter( 'plugin_row_meta', array($this,"st_row_meta"), 10, 2);
		add_filter( 'plugin_action_links_simple-ticketing/new-login-url.php', array($this,'st_action_links'));

		add_action( 'admin_init', array($this, 'st_redirect'));
	}
	/**
	 * st_activate
	 *
	 * Set the Option for redirection to admin on activation
	 *
	 * @author Eric Zeidan <eric@zeidan.info>
	 * @since 1.0
	 */
	public function st_activate() {
		add_option('st_do_activation_redirect', true);
	}

	/**
	 * st_redirect
	 *
	 * Redirect to admin on activation
	 *
	 * @author Eric Zeidan <eric@zeidan.info>
	 * @since 1.0
	 */
	public function st_redirect() {
		if (get_option('st_do_activation_redirect', false)) {
			delete_option('st_do_activation_redirect');
			if (!isset($_GET['activate-multi'])) {
				flush_rewrite_rules();
				wp_redirect("options-general.php?page=simple-ticketing%2Fclasses%2Fclass-simple-ticketing.php");
			}
		}
	}

	function st_rewrite_tag() {
		add_rewrite_tag( '%st-action%', '([^&]+)' );
	}

	public function st_rewrite_rules() {
			add_rewrite_rule( '^mensajes/st-action/([^/]*)/?', 'index.php?post_type=ticketing&st-action=$matches[1]','top' );
	}

	public function st_query_vars_filter( $vars ){
		$vars[] = "st-action";
		return $vars;
	}

	public  function st_front_scripts(){
		wp_enqueue_script( 'jquery');
		wp_enqueue_script('dashicons');
		wp_enqueue_script( 'list_table_script', ST_BASE_URL . '/assets/js/list.min.js', array( 'jquery'),'', true);
		wp_enqueue_style( 'st_front_style', ST_BASE_URL . '/assets/css/styles.css' );
        wp_enqueue_style( 'st_grid_style', ST_BASE_URL . '/assets/css/simple-grid.min.css' );
		wp_enqueue_script( 'st_front_script', ST_BASE_URL . '/assets/js/main-ticketing.js', array('jquery'),'', true);
		wp_localize_script( 'st_front_script', 'myajaxvars', array('ajaxurl' => admin_url( 'admin-ajax.php' )));
	}

	public function st_admin_init(){
		wp_enqueue_script( "jquery" );
		if(get_current_screen()->base == 'settings_page_simple-ticketing/inc/class-simple-ticketing') {
			wp_enqueue_script('clone-admin-script', ST_BASE_URL .'/assets/js/clone-admin-script.js', array('jquery'), false);
		}
			wp_enqueue_style( 'datatables-admin-styles', ST_BASE_URL . '/assets/css/dataTables.bootstrap.min.css' );
	}
	/**
	 * add_option_menu
	 *
	 * Loading the Options Menu
	 *
	 * @author Eric Zeidan <eric@zeidan.info>
	 * @since 1.0
	 */
	public function st_add_option_menu(){
		add_options_page("st_plugin", "Simple Ticketing System", "read", __FILE__, array($this, 'admin_menu'));
	}

	public function st_create_posttypes() {
		register_post_type( 'ticketing',
			array(
				'labels' => array(
					'name' => __( 'Tickets' ),
					'singular_name' => __( 'Ticket' ),
					'search_items'      => __( 'Search Ticket' ),
					'all_items'         => __( 'All Ticket' ),
					'parent_item'       => __( 'Parent Ticket' ),
					'parent_item_colon' => __( 'Parent Ticket:' ),
					'edit_item'         => __( 'Edit Ticket' ),
					'update_item'       => __( 'Update Ticket' ),
					'add_new_item'      => __( 'Add New Ticket' ),
					'new_item_name'     => __( 'New Ticket' ),
					'menu_name'         => __( 'Tickets' ),
				),
				'public' => true,
				'supports' => array( 'title' ),
				'has_archive' => true,
				'rewrite' => array('slug' => 'ticketing'),
				'menu_icon' => 'dashicons-tickets'
			)
		);
	}

	public function st_display_template( $archive_template ) {
		global $post;
		if ( is_post_type_archive ( 'ticketing' ) ) {
			$archive_template = ST_BASE_DIR . '/templates/ticketing-template.php';
		}
		return $archive_template;
	}

	public function st_single_template($single_template) {
		global $post;

		if ($post->post_type == 'ticketing') {
			$single_template = ST_BASE_DIR . '/templates/single-ticketing.php';
		}
		return $single_template;
	}


	public function st_change_title_text( $title ){
		$screen = get_current_screen();

		if  ( 'ticketing' == $screen->post_type ) {
			$title = 'Asunto';
		}

		return $title;
	}
	/**
	 * st_front_scripts
	 *
	 * Loading the Code for the Admin
	 *
	 * @author Eric Zeidan <eric@zeidan.info>
	 * @since 1.0
	 */
	public function admin_menu(){
		require_once ST_BASE_DIR . 'templates/st-options.php';
	}

	/**
	 * st_row_meta
	 *
	 * Information about Author
	 *
	 * @author Eric Zeidan <eric@zeidan.info>
	 * @since 1.0
	 */
	public function st_row_meta( $links, $file ) {
		if ( strpos( $file, 'simple-ticketing/class-simple-ticketing.php' ) !== false ) {
			$new_links = array(
				'twitter' => '<a href="http://twitter.com/ericjanzei" target="_blank">Twitter</a>',
			);

			$links = array_merge( $links, $new_links );
		}

		return $links;
	}

	public function st_front_end_save_message() {
		if (!isset($_POST['ticketing-nonce-field']))
			return false;

		$nonce = $_POST['ticketing-nonce-field'];

		// Verify that the nonce is valid.
		if (!wp_verify_nonce($nonce, 'ticketing-action'))
			wp_die('no script kiddies!');


		// If this is an autosave, our form has not been submitted,
		//     so we don't want to do anything.
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			return false;

		// Check the user's permissions.
//		if ('page' == $_POST['post_type']) {
//
//			if (!current_user_can('read', $post->ID))
//				return false;
//
//		} else {
//
//			if (!current_user_can('read', $post->ID))
//				return false;
//		}

		/* OK, its safe for us to save the data now. */

		// Sanitize the user input.
		if (isset($_POST['input'])) {
			$input = $_POST['input'];
			$key = isset($_POST['key']) ? $_POST['key'] : null;
			if(empty($input[$key]['message'])) {
				$answer = "<div class='msg-error'>" . __('No se puede enviar un mensaje vacio, intenta de nuevo',  ST_TEXT_DOMAIN) . "</div>";
				wp_die( 'No se puede enviar un mensaje vacio, intenta de nuevo', null, array( 'back_link' => true ) );
				return;
			}
			$postid = $_POST['postid'];
			$data = get_post_meta($postid, '_ticketng_details', true);
			if($data) {
				array_push($data, $input[$key]);
				$data = array_filter($data, function ($value) {
					return $value['message'] !== '';
				});
			} else {
				$data = $input;
			}
			// Update the meta field.
			$updated = update_post_meta($postid, '_ticketng_details', $data);
			if($updated) {
				$mail = StHelpers::getInstance()->email_trigger('answer', null, $postid);
				$answer = "<div class='msg-success'>" . __('Mensaje Actualizado', ST_TEXT_DOMAIN) . "</div>";
				$_SESSION['replying'] = 1;
				$updatemeta = StHelpers::getInstance()->update_message_status($postid, 2);
				return;
			} else {
				$answer = "<div class='msg-error'>" . __('Tenemos un problema, intenta de nuevo',  ST_TEXT_DOMAIN) . "</div>";
				return;
			}
		}
	}
	/**
	 * st_action_links
	 *
	 * Links for the admin page on plugins list
	 *
	 * @author Eric Zeidan <eric@zeidan.info>
	 * @since 1.0
	 */
	public function st_action_links ( $links ) {
		$mylinks = array(
			'<a href="' . admin_url( 'options-general.php?page=simple-ticketing/classes/class-simple-ticketing.php' ) . '">' . __('Settings','kapwhi') . '</a>',
		);
		return array_merge( $links, $mylinks );
	}

	public function front_end_new_ticket() {
		global $answer;
		if(isset($_POST['front-st-nonce-field'])) {
			if(!wp_verify_nonce($_POST['front-st-nonce-field'], 'front-st-action')) {
				wp_die('no script kiddies!');
			}

			if(!is_user_logged_in()) {
				return false;
			}

			if(isset($_POST['title']) && isset($_POST['input'])) {
				$input = $_POST['input'];
				if(empty($input[0]['message'])) {
					$answer = "<div class='msg-error'>" . __('No se puede enviar un mensaje vacio, intenta de nuevo',  ST_TEXT_DOMAIN) . "</div>";
					wp_die( 'No se puede enviar un mensaje vacio, intenta de nuevo', null, array( 'back_link' => true ) );
					return;
				}
				$title = $_POST['title'];
				$reference = $_POST['referencia'];
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
					$updatedref = update_post_meta($postid, '_reference', $reference);
					$updatemeta = StHelpers::getInstance()->update_message_status($postid, 1);
				}
				if($meta) {
					$mail = StHelpers::getInstance()->email_trigger($input[0]['type'], $input[0]['userid'], $postid);
					$_SESSION['replying'] = 1;
					//$answer = "<div class='alert alert-success'>" . __('Mensaje Creado, para verlo haz click en el siguiente enlance ', ST_TEXT_DOMAIN) . "<a href='" . get_permalink($postid) . "'>Link.</a></div>";

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

	public function st_custom_post_status(){
		register_post_status( 'closed', array(
			'label'                     => _x( 'Cerrado', 'post' ),
			'public'                    => true,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Cerrado <span class="count">(%s)</span>', 'Cerrado <span class="count">(%s)</span>' ),
		) );
	}


	public function st_append_post_status_list(){
		global $post;
		$complete = '';
		$label = '';
		if($post->post_type == 'post'){
			if($post->post_status == 'closed'){
				$complete = ' selected="selected"';
				$label = '<span id="post-status-display"> Cerrado</span>';
			}
			echo '
          <script>
          jQuery(document).ready(function($){
          alert("Done");
               $("select#post_status").append("<option value="closed" '.$complete.'>Closed</option>");
               $(".misc-pub-section label").append("'.$label.'");
          });
          </script>
          ';
		}
	}

	public function st_post_submitbox_misc_actions(){

		global $post;

		//only when editing a post
		if( $post->post_type == 'ticketing' ){

			// custom post status: approved
			$complete = '';
			$label = '';

			if( $post->post_status == 'closed' ){
				$complete = 'selected=\"selected\"';
				$label = '<span id=\"post-status-display\"> Cerrado</span>';
			}

			echo '<script>'.
				'jQuery(document).ready(function($){'.
				'$("select#post_status").append('.
				'"<option value=\"closed\" '.$complete.'>'.
				'Closed'.
				'</option>"'.
				');'.
				'$(".misc-pub-section label").append("'.$label.'");'.
				'});'.
				'</script>';
		}
	}


}
