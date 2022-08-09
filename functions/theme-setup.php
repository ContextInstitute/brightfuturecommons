<?php

/****************************** THEME SETUP ******************************/

/**
 * Sets up theme for translation
 *
 * @since BuddyBoss Child 1.0.0
 */
function buddyboss_theme_child_languages()
{
  /**
   * Makes child theme available for translation.
   * Translations can be added into the /languages/ directory.
   */

  // Translate text from the PARENT theme.
  // load_theme_textdomain( 'buddyboss-theme', get_stylesheet_directory() . '/languages' );

  // Translate text from the CHILD theme only.
  // Change 'buddyboss-theme' instances in all child theme files to 'bfcommons-theme'.
  load_theme_textdomain( 'bfcommons-theme', get_stylesheet_directory() . '/languages' );

}
add_action( 'after_setup_theme', 'buddyboss_theme_child_languages' );

/**
 * Enqueues scripts and styles for child theme front-end.
 *
 * @since Boss Child Theme  1.0.0
 */
function buddyboss_theme_child_scripts_styles()
{
	/**
	 * Scripts and Styles loaded by the parent theme can be unloaded if needed
	 * using wp_deregister_script or wp_deregister_style.
	 *
	 * See the WordPress Codex for more information about those functions:
	 * http://codex.wordpress.org/Function_Reference/wp_deregister_script
	 * http://codex.wordpress.org/Function_Reference/wp_deregister_style
	 **/

	// Register Foundation scripts
	wp_enqueue_script( 'foundation-js', get_stylesheet_directory_uri() . '/foundation-sites/dist/js/foundation.min.js', array('jquery'), "true" );

	// Register Foundation styles
	// wp_enqueue_style( 'foundation-css', get_stylesheet_directory_uri() . '/foundation-sites/dist/css/foundation.min.css', array(), "false", 'all' );

	// Styles
	wp_enqueue_style( 'buddyboss-child-css', get_stylesheet_directory_uri().'/assets/css/custom.css', '', '1.0.0' );

	// Javascript
	wp_enqueue_script( 'buddyboss-child-js', get_stylesheet_directory_uri().'/assets/js/custom.js', array('jquery'), '1.0.0', "true");

	// Liker script
	wp_enqueue_script( 'bfc-liker-js', get_stylesheet_directory_uri().'/assets/js/like.js', array('jquery'), "true" );
	wp_localize_script( 'bfc-liker-js', 'bfcAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));

	wp_dequeue_script( 'buddyboss-bbpress-reply-ajax');

	wp_enqueue_style( 'bfc-editor-css',	get_stylesheet_directory_uri().'/assets/css/editor.css');
	wp_enqueue_style( 'bfc-dashicons-css',	get_stylesheet_directory_uri().'/assets/css/dashicons.css');
	wp_enqueue_style( 'bfc-icons-css',	get_stylesheet_directory_uri().'/assets/icons/bfc-icons.css');
	wp_enqueue_style( 'bfc-fonts-css',	get_stylesheet_directory_uri().'/assets/fonts/bfc-fonts.css');



  
}
add_action( 'wp_enqueue_scripts', 'buddyboss_theme_child_scripts_styles', 9999 );

?>
