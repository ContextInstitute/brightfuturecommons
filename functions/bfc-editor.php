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
			'tinymce'           => true, //always true to enable the filters above
			'teeny'             => $r['teeny'],
			'quicktags'         => $r['quicktags'],
			'dfw'               => $r['dfw'],
			) );

			// Remove additional TinyMCE plugins after outputting the editor
			remove_filter( 'tiny_mce_plugins', 'bbp_get_tiny_mce_plugins' );
			remove_filter( 'teeny_mce_plugins', 'bbp_get_tiny_mce_plugins' );
			remove_filter( 'teeny_mce_buttons', 'bbp_get_teeny_mce_buttons' );
			remove_filter( 'quicktags_settings', 'bbp_get_quicktags_settings' );


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

add_action('bbp_kses_allowed_tags','bfc_extra_allowed_html_tags', 189);
add_action('init','bfc_extra_allowed_html_tags', 199);

function bfc_extra_allowed_html_tags(){
	global $allowedtags;
	$allowedtags = wp_kses_allowed_html('post');
	$disallowed = array ('address', 'article', 'aside', 'blockquote', 'details', 'div', 'fieldset', 'footer', 'header', 'hgroup', 'hr', 'main', 'nav', 'section');
	foreach ($disallowed as $dtag) {
		unset($allowedtags[$dtag]);
	}
}

// Fixes a conflict between the bp_doc system and the BuddyBoss document system.
// Also makes the urls readable outside of the group.
// Added to both topic and reply content filters in the bfc_override_bb_content_display function below.


function bfc_fix_doc_url( $content ){
    // Only run regex if content contains docs links
    if (strpos($content, '/docs/') !== false) {
        $new_content = preg_replace('#href="https:[^>]+?/docs/(\w+?)#', 'href="/docs/\1', $content);
        return $new_content;
    }
    return $content;
}

/**
 * BFC Compatibility Fix for BB Theme v2.4+
 * 
 * BB Theme v2.4+ introduced content filters that break WordPress oEmbed functionality.
 * Since BFC uses TinyMCE and relies on standard WP embed features, we bypass BB's 
 * content processing entirely and use WordPress's native content filters instead.
 */

function bfc_override_bb_content_display() {
    // Remove all BB content filters that interfere with embeds
    remove_all_filters('bbp_get_reply_content');
    remove_all_filters('bbp_get_topic_content');

    // Also handle profile content filters
    remove_all_filters('bp_get_the_profile_field_value');
    remove_all_filters('xprofile_get_field_data');
    
    // Apply standard WordPress content processing to both replies and topics
    $content_filters = array(
        'wptexturize',
        'convert_smilies', 
        'convert_chars',
        'wpautop',
        'shortcode_unautop',
        'prepend_attachment',
        'do_shortcode'  // Added this for shortcode support
    );
    
    foreach ($content_filters as $filter) {
        add_filter('bbp_get_reply_content', $filter);
        add_filter('bbp_get_topic_content', $filter);

        // Add same processing to profile fields
        add_filter('bp_get_the_profile_field_value', $filter);
        add_filter('xprofile_get_field_data', $filter);
    }
    
    // Add embed processing at the right priority
    global $wp_embed;
	if ($wp_embed) {
		add_filter('bbp_get_reply_content', array($wp_embed, 'run_shortcode'), 8);
		add_filter('bbp_get_reply_content', array($wp_embed, 'autoembed'), 8);
		add_filter('bbp_get_topic_content', array($wp_embed, 'run_shortcode'), 8);
		add_filter('bbp_get_topic_content', array($wp_embed, 'autoembed'), 8);

        // Add embed processing to profile fields
        add_filter('bp_get_the_profile_field_value', array($wp_embed, 'run_shortcode'), 8);
        add_filter('bp_get_the_profile_field_value', array($wp_embed, 'autoembed'), 8);
        add_filter('xprofile_get_field_data', array($wp_embed, 'run_shortcode'), 8);
        add_filter('xprofile_get_field_data', array($wp_embed, 'autoembed'), 8);
	}

	// Re-add BFC's custom doc URL fix after removing all filters
	// Fixes a conflict between the bp_doc system and the BuddyBoss document system.
	// Also makes the urls readable outside of the group.
    add_filter('bbp_get_reply_content', 'bfc_fix_doc_url', 15);
    add_filter('bbp_get_topic_content', 'bfc_fix_doc_url', 15);

    // Apply doc URL fix to profile content too
    add_filter('bp_get_the_profile_field_value', 'bfc_fix_doc_url', 15);
    add_filter('xprofile_get_field_data', 'bfc_fix_doc_url', 15);
}
add_action('init', 'bfc_override_bb_content_display', 25);

// Configure Heartbeat for forum pagesand profile pages to avoid nonce expiration
function bfc_modify_heartbeat_settings($settings) {
    if (bbp_is_forum() || bbp_is_topic() || bbp_is_reply() || bbp_is_single_forum() || bbp_is_single_topic() || bp_is_user_profile_edit()) {
        $settings['interval'] = 30; // Beat every 30 seconds
        $settings['suspension'] = false; // Don't suspend heartbeat
    }
    return $settings;
}
add_filter('heartbeat_settings', 'bfc_modify_heartbeat_settings');

// Send nonce data with heartbeat
function bfc_heartbeat_send($response, $data) {
    if (isset($data['bfc_forum_active'])) {
        $response['bfc_new_nonce'] = wp_create_nonce('bbp-new-reply');
        $response['bfc_topic_nonce'] = wp_create_nonce('bbp-new-topic');
		$response['bfc_profile_nonce'] = wp_create_nonce('bp_xprofile_edit'); 
    }
    return $response;
}
add_filter('heartbeat_send', 'bfc_heartbeat_send', 10, 2);

// Receive heartbeat response and update nonces
function bfc_heartbeat_receive() {
    if (bbp_is_forum() || bbp_is_topic() || bbp_is_reply() || bbp_is_single_forum() || bbp_is_single_topic() || bp_is_user_profile_edit()) {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Tell heartbeat we're on a forum page
			$(document).on('heartbeat-send', function(e, data) {
				if ($('.bbp-reply-form, .bbp-topic-form').length) {
					data.bfc_forum_active = true;
				}
				if ($('#profile-edit-form, .standard-form').length) {
					data.bfc_profile_active = true;
				}
			});
            
            // Update nonces when heartbeat responds
            $(document).on('heartbeat-tick', function(e, data) {
                if (data.bfc_new_nonce) {
                    $('input[name="bbp_reply_nonce"]').val(data.bfc_new_nonce);
                    $('input[name="_wpnonce"]').val(data.bfc_new_nonce);
                }
				
				if (data.bfc_profile_nonce) {  // Add this block
     			   $('input[name="_wpnonce_update_profile"]').val(data.bfc_profile_nonce);
				}
    
				if (data.bfc_topic_nonce) {
					$('input[name="bbp_topic_nonce"]').val(data.bfc_topic_nonce);
					$('input[name="_wpnonce"]').val(data.bfc_topic_nonce);
				}
            });
        });
        </script>
        <?php
    }
}
add_action('wp_footer', 'bfc_heartbeat_receive');

?>
