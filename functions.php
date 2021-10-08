<?php
/**
 * @package BuddyBoss Child
 * The parent theme functions are located at /buddyboss-theme/inc/theme/functions.php
 * This theme's function are in /functions/
 * For more info: https://developer.wordpress.org/themes/basics/theme-functions/
 */


// Theme setup options
require_once(get_stylesheet_directory().'/functions/theme-setup.php'); 

// bfcom-specific functions
require_once(get_stylesheet_directory().'/functions/bfc-functions.php');

// bfcom-specific widgets
require_once(get_stylesheet_directory().'/functions/widgets.php');

?>
