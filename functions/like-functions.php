<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Output the state buttons inside a forum post Loop.
 *
 * @since BuddyPress 3.0.0
 */
function bfc_like_state($post_id) { // from bp_nouveau_activity_state() /buddyboss-platform/bp-templates/bp-nouveau/includes/activity/template-tags.php line 256

	$like_users_string       = bfc_like_users_string( $post_id );
	$favorited_users = bfc_like_users_tooltip_string( $post_id );
	// if ($like_users_string == '') { return '';}
	
	?>
	<div class="like-state <?php echo $like_users_string ? 'has-likes' : ''; ?>">
		<?php echo $favorited_users ? '<a href="javascript:void(0);">': ''; ?>
			<span class="like-text<?php echo $favorited_users ?' hint--bottom hint--medium hint--multiline':''; ?> like-state-likes" style="<?php echo $like_users_string ? 'display:inline-block' : 'display:none'; ?>"<?php echo ( $favorited_users ) ? ' data-hint="'.$favorited_users : ''; ?>" data-post_id="<?php echo $post_id; ?>">
				<?php echo $like_users_string ?: ''; ?>
			</span>
		<?php echo $favorited_users ? '</a>': ''; ?>
	</div>
	<?php
}

function bfc_widget_like_state($post_id) { 
	
	$like_users_string       = bfc_like_users_string( $post_id );
	$favorited_users = bfc_like_users_tooltip_string( $post_id );
	
	?>
	<div class="like-state <?php echo $like_users_string ? 'has-likes' : ''; ?>">
		<!-- <a href="javascript:void(0);" class="like-state-likes"> -->
			<span class="like-text" style="<?php echo $like_users_string ? 'display:inline-block' : 'display:none'; ?>" data-post_id="<?php echo $post_id; ?>">
				<?php echo $like_users_string ?: ''; ?>
			</span>
		<!-- </a> -->
	</div>
	<?php
}

/**
 * Get like count for forum post
 *
 * @since BuddyBoss 1.0.0
 *
 * @param $post_id
 *
 * @return int|string
 */
function bfc_like_users_string( $post_id ) { // bp_activity_get_favorite_users_string from buddyboss-platform/bp-activity/bp-activity-functions.php line 1103

	$like_count      = get_post_meta($post_id, 'like_count', true );
	$like_count      = ( isset( $like_count ) && ! empty( $like_count ) ) ? $like_count : 0;
	$favorited_users = get_post_meta($post_id, 'bbp_like_users', true);

	if ( empty( $favorited_users ) || ! is_array( $favorited_users ) ) {
		return 0;
	}

	if ( $like_count > sizeof( $favorited_users ) ) {
		$like_count = sizeof( $favorited_users );
	}

	$current_user_fav = false;
	if ( bp_loggedin_user_id() && in_array( bp_loggedin_user_id(), $favorited_users ) ) {
		$current_user_fav = true;
		if ( sizeof( $favorited_users ) > 1 ) {
			$pos = array_search( bp_loggedin_user_id(), $favorited_users );
			unset( $favorited_users[ $pos ] );
		}
	}

	$return_str = '';
	if ( 1 == $like_count ) {
		if ( $current_user_fav ) {
			$return_str = __( 'You like this', 'bfcommons-theme' );
		} else {
			$user_data         = get_userdata( array_pop( $favorited_users ) );
			$user_display_name = ! empty( $user_data ) ? bp_core_get_user_displayname( $user_data->ID ) : __( 'Unknown', 'bfcommons-theme' );
			$return_str        = $user_display_name . ' ' . __( 'likes this', 'bfcommons-theme' );
		}
	} elseif ( 2 == $like_count ) {
		if ( $current_user_fav ) {
			$return_str .= __( 'You and', 'bfcommons-theme' ) . ' ';

			$user_data         = get_userdata( array_pop( $favorited_users ) );
			$user_display_name = ! empty( $user_data ) ? bp_core_get_user_displayname( $user_data->ID ) : __( 'Unknown', 'bfcommons-theme' );
			$return_str       .= $user_display_name . ' ' . __( 'like this', 'bfcommons-theme' );
		} else {
			$user_data         = get_userdata( array_pop( $favorited_users ) );
			$user_display_name = ! empty( $user_data ) ? bp_core_get_user_displayname( $user_data->ID ) : __( 'Unknown', 'bfcommons-theme' );
			$return_str       .= $user_display_name . ' ' . __( 'and', 'bfcommons-theme' ) . ' ';

			$user_data         = get_userdata( array_pop( $favorited_users ) );
			$user_display_name = ! empty( $user_data ) ? bp_core_get_user_displayname( $user_data->ID ) : __( 'Unknown', 'bfcommons-theme' );
			$return_str       .= $user_display_name . ' ' . __( 'like this', 'bfcommons-theme' );
		}
	} elseif ( 3 == $like_count ) {

		if ( $current_user_fav ) {
			$return_str .= __( 'You,', 'bfcommons-theme' ) . ' ';

			$user_data         = get_userdata( array_pop( $favorited_users ) );
			$user_display_name = ! empty( $user_data ) ? bp_core_get_user_displayname( $user_data->ID ) : __( 'Unknown', 'bfcommons-theme' );
			$return_str       .= $user_display_name . ' ' . __( 'and', 'bfcommons-theme' ) . ' ';

			$user_data         = get_userdata( array_pop( $favorited_users ) );
			$user_display_name = ! empty( $user_data ) ? bp_core_get_user_displayname( $user_data->ID ) : __( 'Unknown', 'bfcommons-theme' );
			$return_str       .= $user_display_name . ' ' . __( 'like this.', 'bfcommons-theme' ) . ' ';

		} else {

			$user_data         = get_userdata( array_pop( $favorited_users ) );
			$user_display_name = ! empty( $user_data ) ? bp_core_get_user_displayname( $user_data->ID ) : __( 'Unknown', 'bfcommons-theme' );
			$return_str       .= $user_display_name . ', ';

			$user_data         = get_userdata( array_pop( $favorited_users ) );
			$user_display_name = ! empty( $user_data ) ? bp_core_get_user_displayname( $user_data->ID ) : __( 'Unknown', 'bfcommons-theme' );
			$return_str       .= $user_display_name . ' ' . __( 'and', 'bfcommons-theme' ) . ' ';

			$user_data         = get_userdata( array_pop( $favorited_users ) );
			$user_display_name = ! empty( $user_data ) ? bp_core_get_user_displayname( $user_data->ID ) : __( 'Unknown', 'bfcommons-theme' );
			$return_str       .= $user_display_name . ' ' . __( 'like this.', 'bfcommons-theme' ) . ' ';

		}
	} elseif ( 3 < $like_count ) {

		$like_count = ( isset( $like_count ) && ! empty( $like_count ) ) ? (int) $like_count - 3 : 0;

		if ( $current_user_fav ) {
			$return_str .= __( 'You,', 'bfcommons-theme' ) . ' ';

			$user_data         = get_userdata( array_pop( $favorited_users ) );
			$user_display_name = ! empty( $user_data ) ? bp_core_get_user_displayname( $user_data->ID ) : __( 'Unknown', 'bfcommons-theme' );
			$return_str       .= $user_display_name . ', ';

			$user_data         = get_userdata( array_pop( $favorited_users ) );
			$user_display_name = ! empty( $user_data ) ? bp_core_get_user_displayname( $user_data->ID ) : __( 'Unknown', 'bfcommons-theme' );
			$return_str       .= $user_display_name . ' ' . __( 'and', 'bfcommons-theme' ) . ' ';

		} else {
			$user_data         = get_userdata( array_pop( $favorited_users ) );
			$user_display_name = ! empty( $user_data ) ? bp_core_get_user_displayname( $user_data->ID ) : __( 'Unknown', 'bfcommons-theme' );
			$return_str       .= $user_display_name . ', ';

			$user_data         = get_userdata( array_pop( $favorited_users ) );
			$user_display_name = ! empty( $user_data ) ? bp_core_get_user_displayname( $user_data->ID ) : __( 'Unknown', 'bfcommons-theme' );
			$return_str       .= $user_display_name . ', ';

			$user_data         = get_userdata( array_pop( $favorited_users ) );
			$user_display_name = ! empty( $user_data ) ? bp_core_get_user_displayname( $user_data->ID ) : __( 'Unknown', 'bfcommons-theme' );
			$return_str       .= $user_display_name . ' ' . __( 'and', 'bfcommons-theme' ) . ' ';
		}

		if ( $like_count > 1 ) {
			$return_str .= $like_count . ' ' . __( 'others like this', 'bfcommons-theme' );
		} else {
			$return_str .= $like_count . ' ' . __( 'other like this', 'bfcommons-theme' );
		}
	} else {
		$return_str = $like_count;
	}

	return $return_str;
}

/**
 * Get users for post likes tooltip
 *
 * @since BuddyBoss 1.0.0
 *
 * @param $post_id
 *
 * @return string
 */
function bfc_like_users_tooltip_string( $post_id ) { // from bp_activity_get_favorite_users_tooltip_string buddyboss-platform/bp-activity/bp-activity-functions.php line 1210

	$current_user_id = get_current_user_id();
	$favorited_users = get_post_meta($post_id, 'bbp_like_users', true );

	if ( ! empty( $favorited_users ) ) {
		$like_users_string       = bfc_like_users_string( $post_id );
		$favorited_users = array_reduce(
			$favorited_users,
			function ( $carry, $user_id ) use ( $current_user_id, $like_users_string ) {
				if ( $user_id != $current_user_id ) {
					$user_display_name = bp_core_get_user_displayname( $user_id );
					if ( strpos( $like_users_string, $user_display_name ) === false ) {
						$carry .= $user_display_name . ', ';
					}
				}
				return $carry;
			}
		);
		if ($favorited_users) {
			$pos = strrpos( $favorited_users , ', ' );
			if( $pos == false ) {return $favorited_users;}
			$search_length  = strlen( ', ' );
			$fav_user_cleaned = substr_replace($favorited_users, '', $pos, $search_length);
			return $fav_user_cleaned;
		}
	}

	return $favorited_users;
}

/** AJAX functions */

add_action("wp_ajax_bfc_post_like", "bfc_ajax_mark_post_like");
add_action("wp_ajax_bfc_post_unlike", "bfc_ajax_unmark_post_like");



/**
 * Mark an post as liked via a POST request.
 *
 * @since BuddyPress 3.0.0
 *
 * @return string JSON reply
 */

function bfc_ajax_mark_post_like() {
	if ( ! bp_is_post_request() ) {
		// wp_send_json_error();
	}
	// Nonce check!
	if (! wp_verify_nonce( $_POST['nonce'], 'bfc_post_liker' ) ) {
		wp_send_json_error();
	}
	if ( bfc_post_add_like_user( $_POST['post_id'] ) ) { // from bp_activity_add_user_favorite buddyboss-platform/bp-activity/bp-activity-functions.php line 941
		$response = array(
			'content'    => __( 'Unlike', 'bfcommons-theme' ),
			'like_users_string' => bfc_like_users_string( $_POST['post_id'] ),
			'tooltip'    => bfc_like_users_tooltip_string( $_POST['post_id'] ),
		); 

		wp_send_json_success( $response );
	} else {
		wp_send_json_error();
	}
	wp_die();
}

/**
 * Un-like a post via a POST request.
 *
 * @since BuddyPress 3.0.0
 *
 * @return string JSON reply
 */
function bfc_ajax_unmark_post_like () { //bp_nouveau_ajax_unmark_activity_favorite
	if ( ! bp_is_post_request() ) {
		wp_send_json_error();
	}

	// Nonce check!
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'bfc_post_liker' ) ) {
		wp_send_json_error();
	}
	if ( bfc_post_remove_like_user( $_POST['post_id'] ) ) {
		$response = array(
			'content'    => __( 'Like', 'bfcommons-theme' ),
			'like_users_string' => bfc_like_users_string( $_POST['post_id'] ),
			'tooltip'    => bfc_like_users_tooltip_string( $_POST['post_id'] ),
		); 

		wp_send_json_success( $response );

	} else {
		wp_send_json_error();
	}
	wp_die();
}

/**
 * Return the total like count for a specified user.
 *
 * @since BuddyPress 1.2.0
 *
 * @param int $user_id ID of user being queried. Default: displayed user ID.
 * @return int The total favorite count for the specified user.
 */
function bfc_get_total_like_count_for_user( $user_id = 0 ) { // bp_get_total_favorite_count_for_user buddyboss-platform/bp-activity/bp-activity-template.php line 3122
	$retval = false;

	$user_id = empty( $user_id )
		? bp_displayed_user_id()
		: $user_id;

	// Get user meta if user ID exists.
	if ( ! empty( $user_id ) ) {

		// Get activities from user meta.
		$liked_posts = bp_get_user_meta( $user_id, 'bfc_liked_posts', true );
		if ( ! empty( $liked_posts ) ) {
			$retval = count( $liked_posts );
		}
	}
	

	/**
	 * Filters the total favorite count for a user.
	 *
	 * @since BuddyPress 1.2.0
	 * @since BuddyPress 2.6.0 Added the `$user_id` parameter.
	 *
	 * @param int|bool $retval  Total favorite count for a user. False on no favorites.
	 * @param int      $user_id ID of the queried user.
	 */
	return apply_filters( 'bfc_get_total_like_count_for_user', $retval, $user_id );
}

/** Favorites ****************************************************************/

/**
 * Get a users liked posts.
 *
 * @since BuddyPress 1.2.0
 *
 * @param int $user_id ID of the user whose favorites are being queried.
 * @return array IDs of the user's liked posts.
 */
function bfc_get_user_likes( $user_id = 0 ) { //bp_activity_get_user_favorites buddyboss-platform/bp-activity/bp-activity-functions.php line 912

	// Fallback to logged in user if no user_id is passed.
	if ( empty( $user_id ) ) {
		$user_id = bp_displayed_user_id();
	}

	// Get favorites for user.
	$favs = bp_get_user_meta( $user_id, 'bfc_liked_posts', true ); 

	/**
	 * Filters the liked posts for a specified user.
	 *
	 * @since BuddyPress 1.2.0
	 *
	 * @param array $favs Array of user's liked posts.
	 */
	return apply_filters( 'bfc_get_user_likes', $favs );
}

/**
 * Add a post as a liked item for a user.
 *
 * @since BuddyPress 1.2.0
 *
 * @param int $post_id ID of post being favorited.
 * @param int $user_id     ID of the user favoriting post.
 * @return bool True on success, false on failure.
 */
function bfc_post_add_like_user( $post_id, $user_id = 0 ) {

	// Fallback to logged in user if no user_id is passed.
	if ( empty( $user_id ) ) {
		$user_id = bp_loggedin_user_id();
	}
	$my_favs =  bp_get_user_meta( $user_id, 'bfc_liked_posts', true ); 

	if ( empty( $my_favs ) || ! is_array( $my_favs ) ) {
		$my_favs = array();
	}
	// Bail if the user has already liked this post.
	if ( in_array( $post_id, $my_favs ) ) {
		return false;
	}

	// Add to user's favorites.
	$my_favs[] = $post_id;

	// Update the total number of users who have liked this post.
	$fav_count = get_post_meta( $post_id, 'like_count', true ); // todo
	$fav_count = ! empty( $fav_count ) ? (int) $fav_count + 1 : 1;

	// Update the users who have liked this post.
	$users = get_post_meta($post_id, 'bbp_like_users', true );
	if ( empty( $users ) || ! is_array( $users ) ) {
		$users = array();
	}
	// Add to post's favorited users.
	$users[] = $user_id;

	// Update user meta.
	bp_update_user_meta( $user_id, 'bfc_liked_posts', array_unique( $my_favs ) );

	// Update post meta
	update_post_meta( $post_id, 'bbp_like_users', array_unique( $users ) );

	// Update post meta counts.
	if ( update_post_meta( $post_id, 'like_count', $fav_count ) ) {

		/**
		 * Fires if update_post_meta() for like_count is successful and before returning a true value for success.
		 *
		 * @since BuddyPress 1.2.1
		 *
		 * @param int $post_id ID of post being liked.
		 * @param int $user_id     ID of the user doing the liking.
		 */
		do_action( 'bfc_post_add_like_user', $post_id, $user_id );

		// Success.
		return true;

		// Saving meta was unsuccessful for an unknown reason.
	} else {

		/**
		 * Fires if update_post_meta() for like_count is unsuccessful and before returning a false value for failure.
		 *
		 * @since BuddyPress 1.5.0
		 *
		 * @param int $post_id ID of post being liked.
		 * @param int $user_id     ID of the user doing the liking.
		 */
		do_action( 'bfc_post_add_like_user_fail', $post_id, $user_id ); //todo

		return false;
	}
}

/**
 * Remove a post as a like item for a user.
 *
 * @since BuddyPress 1.2.0
 *
 * @param int $post_id ID of post being unfavorited.
 * @param int $user_id     ID of the user unfavoriting post.
 * @return bool True on success, false on failure.
 */
function bfc_post_remove_like_user( $post_id, $user_id = 0 ) { // bp_activity_remove_user_favorite

	// Fallback to logged in user if no user_id is passed.
	if ( empty( $user_id ) ) {
		$user_id = bp_loggedin_user_id();
	}

	$my_favs = bp_get_user_meta( $user_id, 'bfc_liked_posts', true );
	$my_favs = array_flip( (array) $my_favs );

	// Bail if the user has not previously favorited the item.
	if ( ! isset( $my_favs[ $post_id ] ) ) {
		return false;
	}

	// Remove the fav from the user's favs.
	unset( $my_favs[ $post_id ] );
	$my_favs = array_unique( array_flip( $my_favs ) );

	// Update the total number of users who have liked this post.
	$fav_count = get_post_meta( $post_id, 'like_count', true );

	// Update the users who have liked this post.
	$users = get_post_meta( $post_id, 'bbp_like_users', true );
	if ( empty( $users ) || ! is_array( $users ) ) {
		$users = array();
	}

	if ( in_array( $user_id, $users ) ) {
		$pos = array_search( $user_id, $users );
		unset( $users[ $pos ] );
	}

	// Update post meta
	update_post_meta( $post_id, 'bbp_like_users', array_unique( $users ) );

	if ( ! empty( $fav_count ) ) {

		// Deduct from total favorites.
		if ( update_post_meta( $post_id, 'like_count', (int) $fav_count - 1 ) ) {

			// Update users favorites.
			if ( bp_update_user_meta( $user_id, 'bfc_liked_posts', $my_favs ) ) {

				/**
				 * Fires if bp_update_user_meta() is successful and before returning a true value for success.
				 *
				 * @since BuddyPress 1.2.1
				 *
				 * @param int $post_id ID of post being unfavorited.
				 * @param int $user_id     ID of the user doing the unfavoriting.
				 */
				do_action( 'bfc_post_remove_like_user', $post_id, $user_id );

				// Success.
				return true;

				// Error updating.
			} else {
				return false;
			}

			// Error updating favorite count.
		} else {
			return false;
		}

		// Error getting favorite count.
	} else {
		return false;
	}
}

function bfc_like_button( $post_id ) {
	$reply_author = bbp_get_reply_author_id ( $post_id );
	$user_id = bp_loggedin_user_id();
	if ($reply_author == $user_id) {
		return '';
	}

	$type = 'like';
	$aria_pressed = 'false';
	$like_text = 'Like';

	$favorited_users = get_post_meta($post_id, 'bbp_like_users', true );
	if ( !empty( $favorited_users ) && is_array( $favorited_users ) ) {
		if ( bp_loggedin_user_id() && in_array( bp_loggedin_user_id(), $favorited_users ) ) {
			$type = 'unlike';
			$aria_pressed = 'true';
			$like_text = 'Unlike';
		}
	}

	$action = 'bfc_post_' . $type;
	$nonce = wp_create_nonce('bfc_post_liker');
	$link = admin_url('admin-ajax.php?action='.$action.'&post_id='.$post_id.'&nonce='.$nonce);

	$button = '<div class="bp-generic-meta post-like-meta action"><div class="generic-button">';
	$button .= '<a class="bfc-post-like button ' .$type. '" data-nonce="' .$nonce. '" data-post_id="' .$post_id. '"href="' .$link. '"  aria-pressed="' .$aria_pressed. '">';
	$button .= '<span class="bp-screen-reader-text">'.$like_text.'</span>'; 
	$button .= '<span class="bfc-post-like-text">'.$like_text.'</span></a></div></div>';
	echo $button;
}
?>
