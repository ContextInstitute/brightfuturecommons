<?php

define( 'BP_GROUPS_DEFAULT_EXTENSION', 'courtyard' );

function bfc_avatar_dropdown( $type, $source, $follow_class ) {
	$user = bp_loggedin_user_id();
	$person = $source;
	if ( 'reply-author' == $type ) {
		$person = bbp_get_reply_author_id( $source );
	}
	$output = '<div class="dropdown-pane ' . $follow_class . '" id="' . $type . '-dropdown-' . esc_attr( $source ) . '" data-dropdown data-hover="true" data-hover-pane="true" data-auto-focus="false">';
	if ( $person != $user ) {
		$output .= '<a href="/members/' . bp_core_get_username( $user ) . '/messages/compose/?r=' . bp_core_get_username( $person ) . '">Send a message</a><br>';
		if ( 'follow-active' == $follow_class ) {
			$output .= bp_get_add_follow_button( $person, $user );
		}
	}
	$output .= '<a href="/members/' . bp_core_get_username( $person ) . '">Visit profile</a><br>Plus info from profile</div>';
	return $output;
}

function bfc_reply_post_date() {
	/*
	 Replaces bbp_reply_post_date() in loop-single-reply.php and loop-search-reply.php 
	 Based on bbp_get_reply_post_date()
	*/
	$post_date = get_post_time( 'M j, Y' );
	echo apply_filters( 'bfc_reply_post_date', bfc_nice_date ($post_date) );
  }

function bfc_nice_date ($post_date){
	$today = current_time('M j, Y');
	$yestertime = current_time('timestamp') - 86400;
	$yesterday = date('M j, Y', $yestertime );
	// $post_date = get_post_time( 'M j, Y' );
	if ($post_date==$today) {
		$result = "Today at " . get_post_time( 'g:i A' );
	} elseif ($post_date==$yesterday) {
		$result =  "Yesterday at " . get_post_time( 'g:i A' );
	} else {
		$result = get_the_date('M j, Y');
	}
	return $result;
}
/**
 * Add custom sub-tab on groups page.
 */
function buddypress_custom_group_tab() {

	// Avoid fatal errors when plugin is not available.
	if ( ! function_exists( 'bp_core_new_subnav_item' ) ||
		 ! function_exists( 'bp_is_single_item' ) ||
		 ! function_exists( 'bp_is_groups_component' ) ||
		 ! function_exists( 'bp_get_group_permalink' ) ||
		 empty( get_current_user_id() ) ) {

		return;

	}

	// Check if we are on group page.
	if ( bp_is_groups_component() && bp_is_single_item() ) {

		global $bp;

		// var_dump($bp->groups->nav);

		// Get current group page link.
		// $group_link = bp_get_group_permalink( $bp->groups->current_group );

		// Tab args.
		$tab_args = array(
			'name'                => bp_get_group_name( $bp->groups->current_group ),
			'slug'                => 'courtyard',
			'screen_function'     => 'custom_tab_screen',
			'position'            => 5,
			'parent_url'          => bp_get_group_permalink( $bp->groups->current_group ),
			'parent_slug'         => bp_get_group_slug( $bp->groups->current_group ),
			// 'default_subnav_slug' => 'group-courtyard',
			// 'item_css_id'         => 'group-courtyard',
		);

		// Add sub-tab.
		bp_core_new_subnav_item( $tab_args, 'groups' );
				
	}
}

add_action( 'bp_setup_nav', 'buddypress_custom_group_tab', 20 );

/**
 * Set template for new tab.
 */
function custom_tab_screen() {

	// Add title and content here - last is to call the members plugin.php template.
	add_action( 'bp_template_title', 'custom_group_tab_title' );
	add_action( 'bp_template_content', 'custom_group_tab_content' );
	bp_core_load_template( 'buddypress/members/single/plugins' );

}

/**
 * Set title for custom tab.
 */
function custom_group_tab_title() {
	echo esc_html__( 'Custom Tab', 'default_content' );
}

/**
 * Display content of custom tab.
 */
function custom_group_tab_content() {
?>
	<div class="user-home-page">
		<h2 class="user-home-welcome">
			<span class="user-home-welcome-welcome">Welcome to the  </span>
			<span class="user-home-welcome-name"><?php echo esc_html( bp_get_group_name() ); ?></span>
			<span class="user-home-welcome-welcome"> courtyard</span>
		</h2>

		<div class="bfc-group-description"><?php bp_current_group_description();?></div>
		<div class="bfc-group-organizers"><?php
			$group_admins = groups_get_group_admins( bp_get_current_group_id() );
			
			$ga_count = 0;
			(1 < count( $group_admins )) ? $olabel = "Stewards: " : $olabel =  "Steward: "; 
			echo $olabel;
			$is_follow_active = bp_is_active('activity') && function_exists('bp_is_activity_follow_active') && bp_is_activity_follow_active();
			$follow_class = $is_follow_active ? 'follow-active' : '';
			foreach ($group_admins as $admin) {
				$ga_count++;
				if ( 1 < $ga_count ) {
					echo ", ";
				}
				?>
				<span class="item-avatar" data-toggle="organizer-dropdown-<?php echo esc_attr( $admin->user_id ); ?>">
					<?php echo bp_core_fetch_avatar( array( 'item_id' => $admin->user_id, 'type'   => 'thumb', 'width'  => '40', 'height' => '40' )); 
					echo " " .  bp_core_get_user_displayname($admin->user_id); ?></span>
				<?php 
				$type = 'organizer';
				$source = $admin->user_id;
				echo bfc_avatar_dropdown ($type,$source,$follow_class);
			}
			?>
		</div>
		

		<div id="bfc-user-panels" class="bfc-user-panels">
			<?php if ( is_active_sidebar( 'dash_left_panel' ) ) : ?>
				<div id="bfc-dash-panel-left" class="bfc-user-panel widget-area">
					<?php dynamic_sidebar( 'dash_left_panel' ); ?>
				</div><!-- #bfc-dash-panel-left -->

			<?php endif; ?>
			<?php if ( is_active_sidebar( 'dash_center_panel' ) ) : ?>
				<div id="bfc-dash-panel-center" class="bfc-user-panel widget-area">
					<?php dynamic_sidebar( 'dash_center_panel' ); ?>
				</div><!-- #bfc-dash-panel-center -->
			<?php endif; ?>

			<?php if ( is_active_sidebar( 'dash_right_panel' ) ) : ?>
				<div id="bfc-dash-panel-right" class="bfc-user-panel widget-area">
					<?php dynamic_sidebar( 'dash_right_panel' ); ?>
				</div><!-- #bfc-dash-panel-right -->
			<?php endif; ?>
		</div>

		<div id="bfc-user-accordion" class="bfc-user-accordion accordion" data-accordion data-allow-all-closed="true">
			<?php if ( is_active_sidebar( 'dash_left_panel' ) ) : ?>
				<div id="bfc-dash-panel-top" class="bfc-user-panel-top widget-area" data-accordion-item>
				<a href="#" class="accordion-title">Latest Updates</a>
				<div class="accordion-content" data-tab-content>
					<?php dynamic_sidebar( 'dash_left_panel' ); ?>
				</div></div><!-- #bfc-dash-panel-le-top -->

			<?php endif; ?>
			<?php if ( is_active_sidebar( 'dash_center_panel' ) ) : ?>
				<div id="bfc-dash-panel-middle" class="bfc-user-panel-middle widget-area" data-accordion-item>
				<a href="#" class="accordion-title">Forum Posts</a>
				<div class="accordion-content" data-tab-content>
					<?php dynamic_sidebar( 'dash_center_panel' ); ?>
				</div></div><!-- #bfc-dash-panel-middle -->
			<?php endif; ?>

			<?php if ( is_active_sidebar( 'dash_right_panel' ) ) : ?>
				<div id="bfc-dash-panel-bottom" class="bfc-user-panel-bottom widget-area" data-accordion-item>
				<a href="#" class="accordion-title">Blog Posts</a>
				<div class="accordion-content" data-tab-content>
					<?php dynamic_sidebar( 'dash_right_panel' ); ?>
				</div></div><!-- #bfc-dash-panel-bottom -->
			<?php endif; ?>
		</div>

	</div>

	<script>
		jQuery(document).foundation();
	</script>

<?php return;}

/*	This function restructures the Groups directory page so the default tab is "My Groups". 
	It also adds a second tab for "Other Groups".
*/

add_filter( 'bp_nouveau_get_groups_directory_nav_items', 'bfc_groups_dir_nav' ); 

function bfc_groups_dir_nav  ( $nav ) {
	if ( is_user_logged_in() ) {

		unset($nav['all']);
		$nav['personal']['li_class'] = 'selected';
		$nav['personal']['position'] = '5';


		$my_groups_count = bp_get_total_group_count_for_user( bp_loggedin_user_id() );
		$other_groups_count = bp_get_total_group_count() - $my_groups_count;
		if ( $other_groups_count ) {
			$nav['others'] = array(
				'component' => 'groups',
				'slug'      => 'others', // slug is used because BP_Core_Nav requires it, but it's the scope
				'li_class'  => array(),
				'link'      => bp_get_groups_directory_permalink(),
				'text'      => __( 'Other Groups', 'buddypress' ),
				'count'     => $other_groups_count,
				'position'  => 20,
			);
		}
	}
	return $nav;
}

add_action( 'bp_ajax_querystring', 'bfc_custom_group_args', 9999, 2 );

function bfc_custom_group_args ( $qs, $object ) {
  
  if ( 'groups' !== $object ) {
    return $qs;
  }

  if ( ! is_user_logged_in() ) {
    return $qs;
  }

  $groupids = BP_Groups_Member::get_group_ids( get_current_user_id());

  $args = wp_parse_args( $qs );

  if ( !isset ($args['scope'])) {
	$args['scope'] = 'personal';
	$args['include'] = $groupids['groups'];
  }

  if ( $args['scope'] === 'others' ) { // You can change the scope value
	$args['exclude'] = $groupids['groups'];
  } 
  $qs = build_query( $args );

  return $qs;

}

/**
 * Output the state buttons inside an Activity Loop.
 *
 * @since BuddyPress 3.0.0
 */
// TODO: move to template-tags??
function bfc_post_state() {

	$post_id         = get_the_ID();
	$like_text       = bfc_post_get_like_users_string( $post_id );
	$liked_users     = bfc_post_get_like_users_tooltip_string( $post_id );

	?>
	<!--  TODO: rename styling from activity to post. keeping in order to follow threads... -->
	<div class="activity-state <?php echo $like_text ? 'has-likes' : ''; ?>">
		<a href="javascript:void(0);" class="activity-state-likes">
			<span class="like-text hint--bottom hint--medium hint--multiline" data-hint="<?php echo ( $liked_users ) ? $liked_users : ''; ?>">
				<?php echo $like_text ?: ''; ?>
			</span>
		</a>
	</div>
	<?php
}

/**
 * Get like count for post
 *
 * @since BuddyBoss 1.0.0
 *
 * @param $post_id
 *
 * @return int|string
 */
function bfc_post_get_like_users_string( $post_id ) {

	$like_count      = get_post_meta( $post_id, 'bfc_like_count', true );
	$like_count      = ( isset( $like_count ) && ! empty( $like_count ) ) ? $like_count : 0;
	$liked_users     = get_post_meta( $post_id, 'bfc_like_users', true );

	if ( empty( $liked_users ) || ! is_array( $liked_users ) ) {
		return 0;
	}

	if ( $like_count > sizeof( $liked_users ) ) {
		$like_count = sizeof( $liked_users );
	}

	$current_user_fav = false;
	if ( bp_loggedin_user_id() && in_array( bp_loggedin_user_id(), $liked_users ) ) {
		$current_user_fav = true;
		if ( sizeof( $liked_users ) > 1 ) {
			$pos = array_search( bp_loggedin_user_id(), $liked_users );
			unset( $liked_users[ $pos ] );
		}
	}

	$return_str = '';
	if ( 1 == $like_count ) {
		if ( $current_user_fav ) {
			$return_str = __( 'You like this', 'buddyboss' );
		} else {
			$user_data         = get_userdata( array_pop( $liked_users ) );
			$user_display_name = ! empty( $user_data ) ? bp_core_get_user_displayname( $user_data->ID ) : __( 'Unknown', 'buddyboss' );
			$return_str        = $user_display_name . ' ' . __( 'likes this', 'buddyboss' );
		}
	} elseif ( 2 == $like_count ) {
		if ( $current_user_fav ) {
			$return_str .= __( 'You and', 'buddyboss' ) . ' ';

			$user_data         = get_userdata( array_pop( $liked_users ) );
			$user_display_name = ! empty( $user_data ) ? bp_core_get_user_displayname( $user_data->ID ) : __( 'Unknown', 'buddyboss' );
			$return_str       .= $user_display_name . ' ' . __( 'like this', 'buddyboss' );
		} else {
			$user_data         = get_userdata( array_pop( $liked_users ) );
			$user_display_name = ! empty( $user_data ) ? bp_core_get_user_displayname( $user_data->ID ) : __( 'Unknown', 'buddyboss' );
			$return_str       .= $user_display_name . ' ' . __( 'and', 'buddyboss' ) . ' ';

			$user_data         = get_userdata( array_pop( $liked_users ) );
			$user_display_name = ! empty( $user_data ) ? bp_core_get_user_displayname( $user_data->ID ) : __( 'Unknown', 'buddyboss' );
			$return_str       .= $user_display_name . ' ' . __( 'like this', 'buddyboss' );
		}
	} elseif ( 3 == $like_count ) {

		if ( $current_user_fav ) {
			$return_str .= __( 'You,', 'buddyboss' ) . ' ';

			$user_data         = get_userdata( array_pop( $liked_users ) );
			$user_display_name = ! empty( $user_data ) ? bp_core_get_user_displayname( $user_data->ID ) : __( 'Unknown', 'buddyboss' );
			$return_str       .= $user_display_name . ' ' . __( 'and', 'buddyboss' ) . ' ';

			$return_str .= ' ' . __( '1 other like this', 'buddyboss' );
		} else {

			$user_data         = get_userdata( array_pop( $liked_users ) );
			$user_display_name = ! empty( $user_data ) ? bp_core_get_user_displayname( $user_data->ID ) : __( 'Unknown', 'buddyboss' );
			$return_str       .= $user_display_name . ', ';

			$user_data         = get_userdata( array_pop( $liked_users ) );
			$user_display_name = ! empty( $user_data ) ? bp_core_get_user_displayname( $user_data->ID ) : __( 'Unknown', 'buddyboss' );
			$return_str       .= $user_display_name . ' ' . __( 'and', 'buddyboss' ) . ' ';

			$return_str .= ' ' . __( '1 other like this', 'buddyboss' );
		}
	} elseif ( 3 < $like_count ) {

		$like_count = ( isset( $like_count ) && ! empty( $like_count ) ) ? (int) $like_count - 2 : 0;

		if ( $current_user_fav ) {
			$return_str .= __( 'You,', 'buddyboss' ) . ' ';

			$user_data         = get_userdata( array_pop( $liked_users ) );
			$user_display_name = ! empty( $user_data ) ? bp_core_get_user_displayname( $user_data->ID ) : __( 'Unknown', 'buddyboss' );
			$return_str       .= $user_display_name . ' ' . __( 'and', 'buddyboss' ) . ' ';
		} else {
			$user_data         = get_userdata( array_pop( $liked_users ) );
			$user_display_name = ! empty( $user_data ) ? bp_core_get_user_displayname( $user_data->ID ) : __( 'Unknown', 'buddyboss' );
			$return_str       .= $user_display_name . ', ';

			$user_data         = get_userdata( array_pop( $liked_users ) );
			$user_display_name = ! empty( $user_data ) ? bp_core_get_user_displayname( $user_data->ID ) : __( 'Unknown', 'buddyboss' );
			$return_str       .= $user_display_name . ' ' . __( 'and', 'buddyboss' ) . ' ';
		}

		if ( $like_count > 1 ) {
			$return_str .= $like_count . ' ' . __( 'others like this', 'buddyboss' );
		} else {
			$return_str .= $like_count . ' ' . __( 'other like this', 'buddyboss' );
		}
	} else {
		$return_str = $like_count;
	}

	return $return_str;
}

/**
 * Get users for post like tooltip
 *
 * @since BuddyBoss 1.0.0
 *
 * @param $post_id
 *
 * @return string
 */
function bfc_post_get_like_users_tooltip_string( $post_id ) {

	$current_user_id = get_current_user_id();
	$favorited_users = get_post_meta( $post_id, 'bfc_like_users', true );

	if ( ! empty( $favorited_users ) ) {
		$like_text       = bfc_post_get_like_users_string( $post_id );
		$favorited_users = array_reduce(
			$favorited_users,
			function ( $carry, $user_id ) use ( $current_user_id, $like_text ) {
				if ( $user_id != $current_user_id ) {
					$user_display_name = bp_core_get_user_displayname( $user_id );
					if ( strpos( $like_text, $user_display_name ) === false ) {
						$carry .= $user_display_name . '&#10;';
					}
				}

				return $carry;
			}
		);
	}

	return $favorited_users;
}

// Start button stuff

/**
 * Output the action buttons inside a Post Loop.
 *
 * @since BuddyPress 3.0.0
 *
 * @param array $args See bp_nouveau_wrapper() for the description of parameters.
 */
function bfc_post_entry_buttons( $args = array() ) {
	$output = join( ' ', bfc_get_post_entry_buttons( $args ) );

	ob_start();

	/**
	 * Fires at the end of the activity entry meta data area.
	 *
	 * @since BuddyPress 1.2.0
	 */
	// TODO: do we need this?
	// do_action( 'bp_activity_entry_meta' );

	$output .= ob_get_clean();

	$has_content = trim( $output, ' ' );
	if ( ! $has_content ) {
		return;
	}

	if ( ! $args ) {
		$args = array( 'classes' => array( 'activity-meta' ) );
	}

	bp_nouveau_wrapper( array_merge( $args, array( 'output' => $output ) ) );
}

/**
 * Get the action buttons inside a Post Loop,
 *
 * @todo  This function is too large and needs refactoring and reviewing.
 */
function bfc_get_post_entry_buttons( $args ) {
	$buttons = array();

	// if ( ! isset( $GLOBALS['activities_template'] ) ) {
	// 	return $buttons;
	// }

	$post_id    = get_the_ID();
//	$activity_type  = bp_get_activity_type();
	$parent_element = '';
	$button_element = 'a';

	if ( ! $post_id ) {
		return $buttons;
	}

	/*
	 * If the container is set to 'ul' force the $parent_element to 'li',
	 * else use parent_element args if set.
	 *
	 * This will render li elements around anchors/buttons.
	 */
	if ( isset( $args['container'] ) && 'ul' === $args['container'] ) {
		$parent_element = 'li';
	} elseif ( ! empty( $args['parent_element'] ) ) {
		$parent_element = $args['parent_element'];
	}

	$parent_attr = ( ! empty( $args['parent_attr'] ) ) ? $args['parent_attr'] : array();

	/*
	 * If we have an arg value for $button_element passed through
	 * use it to default all the $buttons['button_element'] values
	 * otherwise default to 'a' (anchor)
	 * Or override & hardcode the 'element' string on $buttons array.
	 *
	 */
	if ( ! empty( $args['button_element'] ) ) {
		$button_element = $args['button_element'];
	}

	if ( bfc_post_can_like() && bfc_is_post_like_active() ) {

		// If button element set attr needs to be data-* else 'href'.
		if ( 'button' === $button_element ) {
			$key = 'data-bp-nonce';
		} else {
			$key = 'href';
		}

		if ( ! bfc_get_post_is_like() ) {
			$fav_args = array(
				'parent_element' => $parent_element,
				'parent_attr'    => $parent_attr,
				'button_element' => $button_element,
				'link_class'     => 'button fav bp-secondary-action',
				// 'data_bp_tooltip'  => __( 'Like', 'buddyboss' ),
				'link_text'      => __( 'Like', 'buddyboss' ),
				'aria-pressed'   => 'false',
				'link_attr'      => bfc_get_post_like_link(),
			);

		} else {
			$fav_args = array(
				'parent_element' => $parent_element,
				'parent_attr'    => $parent_attr,
				'button_element' => $button_element,
				'link_class'     => 'button unfav bp-secondary-action',
				// 'data_bp_tooltip' => __( 'Unlike', 'buddyboss' ),
				'link_text'      => __( 'Unlike', 'buddyboss' ),
				'aria-pressed'   => 'true',
				'link_attr'      => bfc_get_post_unlike_link(),
			);
		}

		$buttons['post_like'] = array(
			'id'                => 'post_like',
			'position'          => 4,
			'component'         => 'post', // TODO: verify
			'parent_element'    => $parent_element,
			'parent_attr'       => $parent_attr,
			'must_be_logged_in' => true,
			'button_element'    => $fav_args['button_element'],
			'link_text'         => sprintf( '<span class="bp-screen-reader-text">%1$s</span>  <span class="like-count">%2$s</span>', esc_html( $fav_args['link_text'] ), esc_html( $fav_args['link_text'] ) ),
			'button_attr'       => array(
				$key           => $fav_args['link_attr'],
				'class'        => $fav_args['link_class'],
				// 'data-bp-tooltip' => $fav_args['data_bp_tooltip'],
				'aria-pressed' => $fav_args['aria-pressed'],
			),
		);
	}

	// TODO: verify this works. I think we're done and don't need to do all the following stuff.
	return $buttons;

	/*
	 * The view conversation button and the comment one are sharing
	 * the same id because when display_comments is on stream mode,
	 * it is not possible to comment an activity comment and as we
	 * are updating the links to avoid sorting the activity buttons
	 * for each entry of the loop, it's a convenient way to make
	 * sure the right button will be displayed.
	 */
	// if ( $activity_type === 'activity_comment' ) {
		// TODO: do we need this?
		// $buttons['activity_conversation'] = array(
		// 	'id'                => 'activity_conversation',
		// 	'position'          => 5,
		// 	'component'         => 'activity',
		// 	'parent_element'    => $parent_element,
		// 	'parent_attr'       => $parent_attr,
		// 	'must_be_logged_in' => false,
		// 	'button_element'    => $button_element,
		// 	'button_attr'       => array(
		// 		'class'               => 'button view bp-secondary-action bp-tooltip',
		// 		'data-bp-tooltip'     => __( 'View Conversation', 'buddyboss' ),
		// 		'data-bp-tooltip-pos' => 'up',
		// 	),
		// 	'link_text'         => sprintf(
		// 		'<span class="bp-screen-reader-text">%1$s</span>',
		// 		__( 'View Conversation', 'buddyboss' )
		// 	),
		// );

		// // If button element set add url link to data-attr.
		// if ( 'button' === $button_element ) {
		// 	$buttons['activity_conversation']['button_attr']['data-bp-url'] = bp_get_activity_thread_permalink();
		// } else {
		// 	$buttons['activity_conversation']['button_attr']['href'] = bp_get_activity_thread_permalink();
		// 	$buttons['activity_conversation']['button_attr']['role'] = 'button';
		// }

		// /*
		// * We always create the Button to make sure we always have the right numbers of buttons,
		// * no matter the previous activity had less.
		// */
	// } else {
		// TODO: verify not needed -- comments are handled differently in posts.
		// $buttons['activity_conversation'] = array(
		// 	'id'                => 'activity_conversation',
		// 	'position'          => 5,
		// 	'component'         => 'activity',
		// 	'parent_element'    => $parent_element,
		// 	'parent_attr'       => $parent_attr,
		// 	'must_be_logged_in' => true,
		// 	'button_element'    => $button_element,
		// 	'button_attr'       => array(
		// 		'id'            => 'acomment-comment-' . $activity_id,
		// 		'class'         => 'button acomment-reply bp-primary-action',
		// 		// 'data-bp-tooltip' => __( 'Comment', 'buddyboss' ),
		// 		'aria-expanded' => 'false',
		// 	),
		// 	'link_text'         => sprintf(
		// 		'<span class="bp-screen-reader-text">%1$s</span> <span class="comment-count">%2$s</span>',
		// 		__( 'Comment', 'buddyboss' ),
		// 		__( 'Comment', 'buddyboss' )
		// 	),
		// );

		// // If button element set add href link to data-attr.
		// if ( 'button' === $button_element ) {
		// 	$buttons['activity_conversation']['button_attr']['data-bp-url'] = bp_get_activity_comment_link();
		// } else {
		// 	$buttons['activity_conversation']['button_attr']['href'] = bp_get_activity_comment_link();
		// 	$buttons['activity_conversation']['button_attr']['role'] = 'button';
		// }
	// }

	/**
	 * Filter to add your buttons, use the position argument to choose where to insert it.
	 *
	 * @since BuddyPress 3.0.0
	 *
	 * @param array $buttons     The list of buttons.
	 * @param int   $activity_id The current activity ID.
	 */
	$buttons_group = apply_filters( 'bp_nouveau_get_activity_entry_buttons', $buttons, $post_id );

	if ( ! $buttons_group ) {
		return $buttons;
	}

	// It's the first entry of the loop, so build the Group and sort it.
	if ( ! isset( bp_nouveau()->activity->entry_buttons ) || ! is_a( bp_nouveau()->activity->entry_buttons, 'BP_Buttons_Group' ) ) {
		$sort                                 = true;
		bp_nouveau()->activity->entry_buttons = new BP_Buttons_Group( $buttons_group );

		// It's not the first entry, the order is set, we simply need to update the Buttons Group.
	} else {
		$sort = false;
		bp_nouveau()->activity->entry_buttons->update( $buttons_group );
	}

	$return = bp_nouveau()->activity->entry_buttons->get( $sort );

	if ( ! $return ) {
		return array();
	}

	// Remove the Comment button if the user can't comment.
	if ( ! bp_activity_can_comment() && $activity_type !== 'activity_comment' ) {
		unset( $return['activity_conversation'] );
	}

	/**
	 * Leave a chance to adjust the $return
	 *
	 * @since BuddyPress 3.0.0
	 *
	 * @param array $return      The list of buttons ordered.
	 * @param int   $activity_id The current activity ID.
	 */
	do_action_ref_array( 'bp_nouveau_return_activity_entry_buttons', array( &$return, $activity_id ) );

	return $return;
}

function bfc_post_can_like() {
	return true;
}

/**
 * Check whether Post Like is enabled.
 *
 * @see bp_is_activity_like_active()
 * 
 * @param bool $default Optional. Fallback value if not found in the database.
 *                      Default: true.
 * @return bool True if Like is enabled, otherwise false.
 */
function bfc_is_post_like_active( $default = true ) {

	/**
	 * Filters whether or not Activity Like is enabled.
	 *
	 * @since BuddyBoss 1.0.0
	 *
	 * @param bool $value Whether or not Activity Like is enabled.
	 */
	// TODO: I don't get it.
	return (bool) apply_filters( 'bfc_is_post_like_active', (bool) bp_get_option( '_bp_enable_activity_like', $default ) );
}

/**
 * Return whether the current post is in a current user's likes.
 *
 * @see bp_get_activity_is_favorite()
 *
 * @global object $activities_template {@link BP_Activity_Template}
 *
 * @return bool True if user favorite, false otherwise.
 */
function bfc_get_post_is_like() {
	global $posts_template;

	/**
	 * Filters whether the current post is in the current user's likes.
	 *
	 * @since BuddyPress 1.2.0
	 *
	 * @param bool $value Whether or not the current post is in the current user's likes.
	 */
	return (bool) apply_filters( 'bfc_get_post_is_like', in_array( $posts_template->post->id, (array) $posts_template->my_likes ) );
}

/**
 * Return the post like link.
 *
 * @see bp_get_activity_favorite_link()
 *
 * @global object $posts_template {@link BP_Activity_Template}
 *
 * @return string The activity favorite link.
 */
function bfc_get_post_like_link() {
	global $posts_template;

	/**
	 * Filters the activity favorite link.
	 *
	 * @since BuddyPress 1.2.0
	 *
	 * @param string $value Constructed link for favoriting the activity comment.
	 */
	return apply_filters( 'bfc_get_post_like_link', wp_nonce_url( home_url( bp_get_activity_root_slug() . '/favorite/' . $activities_template->activity->id . '/' ), 'mark_favorite' ) );
}

/**
 * Return the activity unfavorite link.
 *
 * @since BuddyPress 1.2.0
 *
 * @global object $activities_template {@link BP_Activity_Template}
 *
 * @return string The activity unfavorite link.
 */
function bp_get_activity_unfavorite_link() {
	global $activities_template;

	/**
	 * Filters the activity unfavorite link.
	 *
	 * @since BuddyPress 1.2.0
	 *
	 * @param string $value Constructed link for unfavoriting the activity comment.
	 */
	return apply_filters( 'bp_get_activity_unfavorite_link', wp_nonce_url( home_url( bp_get_activity_root_slug() . '/unfavorite/' . $activities_template->activity->id . '/' ), 'unmark_favorite' ) );
}

?>
