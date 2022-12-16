<?php
function bfc_get_the_content( $args = array() ) {

	// Parse arguments against default values
	$r = bbp_parse_args(
		$args,
		array(
			'context'           => 'topic',
			'before'            => '<div class="bbp-the-content-wrapper">',
			'after'             => '</div>',
			'wpautop'           => true,
			'media_buttons'     => true,
			'textarea_rows'     => '12',
			'tabindex'          => bbp_get_tab_index(),
			'tabfocus_elements' => 'bbp_topic_title,bbp_topic_tags',
			'editor_class'      => 'bbp-the-content',
			'tinymce'           => true,
			'teeny'             => false,
			'quicktags'         => false,
			'dfw'               => false,
		),
		'get_the_content'
	);

	// If using tinymce, remove our escaping and trust tinymce
	if ( bbp_use_wp_editor() && ( false !== $r['tinymce'] ) ) {
		remove_filter( 'bbp_get_form_forum_content', 'esc_textarea' );
		remove_filter( 'bbp_get_form_topic_content', 'esc_textarea' );
		remove_filter( 'bbp_get_form_topic_content', 'bbp_code_trick_reverse' );
		remove_filter( 'bbp_get_form_reply_content', 'esc_textarea' );
		remove_filter( 'bbp_get_form_reply_content', 'bbp_code_trick_reverse' );
	}

	// Assume we are not editing
	$post_content = call_user_func( 'bbp_get_form_' . $r['context'] . '_content' );

	// Start an output buffor
	ob_start();

	// Output something before the editor
	if ( ! empty( $r['before'] ) ) {
		echo $r['before'];
	}

	// Use TinyMCE if available
	// if ( bbp_use_wp_editor() ) :

		// Enable additional TinyMCE plugins before outputting the editor
		add_filter( 'tiny_mce_plugins', 'bbp_get_tiny_mce_plugins' );
		add_filter( 'teeny_mce_plugins', 'bbp_get_tiny_mce_plugins' );
		add_filter( 'teeny_mce_buttons', 'bbp_get_teeny_mce_buttons' );
		add_filter( 'quicktags_settings', 'bbp_get_quicktags_settings' );


			// Output the editor
			wp_editor( $post_content, 'bbp_' . $r['context'] . '_content', array(
			'wpautop'           => $r['wpautop'],
			'media_buttons'     => $r['media_buttons'],
			'textarea_rows'     => $r['textarea_rows'],
			'tabindex'          => $r['tabindex'],
			'tabfocus_elements' => $r['tabfocus_elements'],
			'editor_class'      => $r['editor_class'],
			'tinymce'           => true, //$r['tinymce'],
			'teeny'             => $r['teeny'],
			'quicktags'         => $r['quicktags'],
			'dfw'               => $r['dfw'],
			) );

			// Remove additional TinyMCE plugins after outputting the editor
			remove_filter( 'tiny_mce_plugins', 'bbp_get_tiny_mce_plugins' );
			remove_filter( 'teeny_mce_plugins', 'bbp_get_tiny_mce_plugins' );
			remove_filter( 'teeny_mce_buttons', 'bbp_get_teeny_mce_buttons' );
			remove_filter( 'quicktags_settings', 'bbp_get_quicktags_settings' );

			/**
			 * Fallback to normal textarea.
			 *
			 * Note that we do not use esc_textarea() here to prevent double
			 * escaping the editable output, mucking up existing content.
			 */


		// Output something after the editor
		if ( ! empty( $r['after'] ) ) {
			echo $r['after'];
		}

		// Put the output into a usable variable
		$output = ob_get_clean();

		return apply_filters( 'bfc_get_the_content', $output, $args, $post_content );
}

add_filter( 'bp_xprofile_field_type_textarea_editor_args', 'bfc_profile_textarea_editor');

function bfc_profile_textarea_editor($editor_args) {
	$bfc_args = array (
		'context'           => 'profile',
		// 'before'            => '<div class="bbp-the-content-wrapper">',
		// 'after'             => '</div>',
		'wpautop'           => true,
		'media_buttons'     => true,
		'textarea_rows'     => '12',
		// 'tabindex'          => bbp_get_tab_index(),
		// 'tabfocus_elements' => 'bbp_topic_title,bbp_topic_tags',
		// 'editor_class'      => 'bbp-the-content',
		'tinymce'           => true,
		'teeny'             => false,
		'quicktags'         => false,
		'dfw'               => false,
		);
	return bbp_parse_args ($bfc_args,$editor_args);
}

add_action('bbp_kses_allowed_tags','bfc_extra_allowed_html_tags', 199);
add_action('init','bfc_extra_allowed_html_tags', 199);

function bfc_extra_allowed_html_tags(){
	global $allowedtags;
	$allowedtags['h1'] = 	array(
			'class' => array(),
			'id'    => array(),
			'style' => array(),
		);
	$allowedtags['h2'] = 	array(
			'class' => array(),
			'id'    => array(),
			'style' => array(),
		);
	$allowedtags['h3'] = 	array(
			'class' => array(),
			'id'    => array(),
			'style' => array(),
		);
	$allowedtags['span']['style'] = array();
}
?>
