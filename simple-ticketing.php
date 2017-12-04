<?php
/*
Plugin Name: Simple Ticketing
Description: A Simple Ticketing Messaging Plugin
Version: 1.0
Author: Eric Zeidan
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 *  We include the main class
 */
include('inc/class-simple-ticketing.php');

/*
*   =================================================================================================
*   CLASSES
*   Include all the Classes you need in the 'inc/' folder and add class-yourname.php
*   automatically.
*   =================================================================================================
*/
foreach (glob(__DIR__ . "/inc/classes/*.php") as $filename)
    include $filename;

/**
 * We create the instance
 */
$simpleticketing = new St_plugin();

/**
 * Functions for redirect on activation and include action on activation of plugin
 */
register_activation_hook(__FILE__, array($simpleticketing, "st_activate"));


