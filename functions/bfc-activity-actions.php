<?php
/**
 * BFC Activity Actions Functions.
 *
 * These functions handle the registration and recording of the following actions:
 * bulk_add_to_group, left_group, follow, followed, unfollow and unfollowed.
 * Additionally, they handle the recording, deleting and formatting of notifications
 * for appropriate users.
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

add_action( 'bp_register_activity_actions', 'bfc_register_activity_actions' );

function bfc_register_activity_actions (){
	$bp = buddypress();

	if ( ! bp_is_active( 'activity' ) ) {
		return false;
	}

    bp_activity_set_action(
        $bp->groups->id,
        'bulk_add_to_group',
        __( 'Added users in bulk to a group', 'bfcommons-theme' ),
        'bfc_activity_format_group_action_bulk_add',
        __( 'Bulk add users to group', 'bfcommons-theme' ),
        array( 'activity', 'group', 'member', 'member_groups' )
     );

     bp_activity_set_action(
        $bp->groups->id,
        'left_group',
        __( 'User has left a group', 'bfcommons-theme' ),
        'bfc_activity_format_group_action_left_group',
        __( 'Left group', 'bfcommons-theme' ),
        array( 'activity', 'group', 'member', 'member_groups' )
     );

     bp_activity_set_action(
        $bp->members->id,
        'follow',
        __( 'User follows another member', 'bfcommons-theme' ),
        'bfc_activity_format_member_action_follow',
        __( 'Follow', 'bfcommons-theme' ),
        array( 'activity', 'member' )
     );

     bp_activity_set_action(
        $bp->members->id,
        'followed',
        __( 'User is followed by another member', 'bfcommons-theme' ),
        'bfc_activity_format_member_action_followed',
        __( 'Followed', 'bfcommons-theme' ),
        array( 'activity', 'member' )
     );

     bp_activity_set_action(
        $bp->members->id,
        'unfollow',
        __( 'User stops following another member', 'bfcommons-theme' ),
        'bfc_activity_format_member_action_unfollow',
        __( 'Unfollow', 'bfcommons-theme' ),
        array( 'activity', 'member' )
     );

     bp_activity_set_action(
        $bp->members->id,
        'unfollowed',
        __( 'User is no longer followed by another member', 'bfcommons-theme' ),
        'bfc_activity_format_member_action_unfollowed',
        __( 'Unfollowed', 'bfcommons-theme' ),
        array( 'activity', 'member' )
     );
}

// from https://gist.github.com/rohmann/6151699 ; modified with bfc_bulk_join_group()

add_action('load-users.php',function() {

	if(isset($_GET['action']) && isset($_GET['bp_gid']) && isset($_GET['users'])) {
		$group_id = $_GET['bp_gid'];
		$users = $_GET['users'];
		$added = 0;
		$newusers = array();
		foreach ($users as $user_id) {
			if(bfc_bulk_join_group( $group_id, $user_id )){
				$added++;
				$newusers[] = $user_id;
			}
		}
		// Record this in activity feeds.
		// if( !is_serialized( $newusers ) ) {
		// 	$newusers = maybe_serialize($newusers);
		// }
		if ( bp_is_active( 'activity' ) &&  $added ) {
			$act_id = groups_record_activity( array(
				'type'    => 'bulk_add_to_group',
				'item_id' => $group_id,
			) );
			$content = implode( ',', $newusers);
			bp_activity_add_meta($act_id,'added_users', $content);
		}

	}
		//Add some Javascript to handle the form submission
		add_action('admin_footer',function(){ ?>
		<script>
			jQuery("select[name='action']").append(jQuery('<option value="groupadd">Add to BP Group</option>'));
			jQuery("#doaction").click(function(e){
				if(jQuery("select[name='action'] :selected").val()=="groupadd") { e.preventDefault();
					gid=prompt("Please enter a BuddyPres Group ID","1");
					jQuery(".wrap form").append('<input type="hidden" name="bp_gid" value="'+gid+'" />').submit();
				}
			});
		</script>
		<?php
		});
	});

function bfc_bulk_join_group( $group_id, $user_id = 0 ) {

	if ( empty( $user_id ) )
		return false;
	
	// Check if the user has an outstanding invite. If so, delete it.
	if ( groups_check_user_has_invite( $user_id, $group_id ) )
		groups_delete_invite( $user_id, $group_id );
	
	// Check if the user has an outstanding request. If so, delete it.
	if ( groups_check_for_membership_request( $user_id, $group_id ) )
		groups_delete_membership_request( null, $user_id, $group_id );
	
	// User is already a member, just return true.
	if ( groups_is_user_member( $user_id, $group_id ) )
		return false;
	
	$new_member                = new BP_Groups_Member;
	$new_member->group_id      = $group_id;
	$new_member->user_id       = $user_id;
	$new_member->inviter_id    = 0;
	$new_member->is_admin      = 0;
	$new_member->user_title    = '';
	$new_member->date_modified = bp_core_current_time();
	$new_member->is_confirmed  = 1;
	
	if ( !$new_member->save() )
		return false;
	
	// $bp = buddypress();
	
	// if ( !isset( $bp->groups->current_group ) || !$bp->groups->current_group || $group_id != $bp->groups->current_group->id )
	// 	$group = groups_get_group( $group_id );
	// else
	// 	$group = $bp->groups->current_group;
	
	/**
	 * Fires after a user joins a group.
	 *
	 * @since BuddyPress 1.0.0
	 *
	 * @param int $group_id ID of the group.
	 * @param int $user_id  ID of the user joining the group.
	 */
	do_action( 'bfc_bult_join_group', $group_id, $user_id );
	
	return true;
}

function bfc_activity_format_group_action_bulk_add( $action, $activity ) {

	$actor = bp_core_get_userlink( $activity->user_id );
	$added_users = explode (',', bp_activity_get_meta($activity->id, 'added_users'));
	$users = array();
	foreach ($added_users as $added_user) {
		$users[] = bp_core_get_userlink($added_user);
	}
	$users_str = implode (', ', $users);
	$group_obj = groups_get_group($activity->item_id);
	$group = bp_get_group_link($group_obj);
	$action = sprintf( __( '%s bulk-added %s to %s', 'bfcommons-theme' ), $actor, $users_str, $group);
    
 
    /**
     * Filters the formatted activity action update string.
     *
     * @since BuddyPress 1.2.0
     *
     * @param string               $action   Activity action string value.
     * @param BP_Activity_Activity $activity Activity item object.
     */
    return apply_filters( 'bfc_activity_format_group_action_bulk_add', $action, $activity );
}

function bfc_activity_format_group_action_left_group( $action, $activity ) {
	$user_link = bp_core_get_userlink( $activity->user_id );

	$group      = groups_get_group( $activity->item_id );
	$group_link = '<a href="' . esc_url( bp_get_group_permalink( $group ) ) . '">' . esc_html( $group->name ) . '</a>';

	$action = sprintf( __( '%1$s left the group %2$s', 'bfcommons-theme' ), $user_link, $group_link );

	/**
	 * Filters the 'left_group' activity actions.
	 *
	 * @since BuddyPress 2.0.0
	 *
	 * @param string $action   The 'left_group' activity actions.
	 * @param object $activity Activity data object.
	 */
	return apply_filters( 'bfc_activity_format_group_action_left_group', $action, $activity );
}

add_action( 'groups_leave_group', 'groups_left_group', 10, 2 );

function groups_left_group( $group_id, $user_id = 0 ) {
	global $bp;

	if ( empty( $user_id ) )
		$user_id = bp_loggedin_user_id();

	// Record this in activity streams
	$activity_id = groups_record_activity( array(
		'type'    => 'left_group',
		'item_id' => $group_id,
		'user_id' => $user_id,
	) );

	$group_admins = groups_get_group_admins($group_id);

	foreach ($group_admins as $admin) {

		bp_notifications_add_notification( array(
			'user_id'           => $admin->user_id,
			'item_id'           => $activity_id,
			'secondary_item_id' => $group_id,
			'component_name'    => 'extras',
			'component_action'  => 'left_group',
			'date_notified'     => bp_core_current_time(),
			'is_new'            => 1,
		) );

	}

	// Modify group meta
	groups_update_groupmeta( $group_id, 'last_activity', bp_core_current_time() );

	return true;
}

function bfc_activity_format_member_action_follow( $action, $activity ) {
	$follower_link = bp_core_get_userlink( $activity->user_id );

	$leader_link  = bp_core_get_userlink( $activity->secondary_item_id );

	$action = sprintf( __( '%1$s is now following %2$s', 'bfcommons-theme' ), $follower_link, $leader_link );

	/**
	 * Filters the 'follow' activity actions.
	 *
	 * @since BuddyPress 2.0.0
	 *
	 * @param string $action   The 'follow' activity actions.
	 * @param object $activity Activity data object.
	 */
	return apply_filters( 'bfc_activity_format_member_action_follow', $action, $activity );
}

add_action( 'bp_start_following', 'member_follows', 10, 2 );

function member_follows( &$follow ) {

	if ( empty( $follower_id ) )
		$follower_id = bp_loggedin_user_id();

	// Record this in activity streams
	$activity_id = bp_activity_add( array(
		'component' => 'members',
		'type'    => 'follow',
		'item_id' => $follow->id,
		'user_id' => $follow->follower_id,
		'secondary_item_id' => $follow->leader_id,
		'privacy' => 'onlyme',
	) );

	// Add notification
	bp_notifications_add_notification( array(
		'user_id'           => $follow->leader_id,
		'item_id'           => $activity_id,
		'secondary_item_id' => $follow->follower_id,
		'component_name'    => 'extras',
		'component_action'  => 'follow',
		'date_notified'     => bp_core_current_time(),
		'is_new'            => 1,
	) );

	return true;
}

function bfc_activity_format_member_action_followed( $action, $activity ) {
	$follower_link = bp_core_get_userlink( $activity->secondary_item_id );

	$leader_link  = bp_core_get_userlink( $activity->user_id );

	$action = sprintf( __( '%1$s is now following %2$s', 'bfcommons-theme' ), $follower_link, $leader_link );

	/**
	 * Filters the 'followed' activity actions.
	 *
	 * @since BuddyPress 2.0.0
	 *
	 * @param string $action   The 'followed' activity actions.
	 * @param object $activity Activity data object.
	 */
	return apply_filters( 'bfc_activity_format_member_action_followed', $action, $activity );
}

add_action( 'bp_start_following', 'member_followed', 11, 2 );

function member_followed( &$follow ) {

	if ( empty( $follower_id ) )
		$follower_id = bp_loggedin_user_id();

	// Record this in activity streams
	bp_activity_add( array(
		'component' => 'members',
		'type'    => 'followed',
		'item_id' => $follow->id,
		'user_id' => $follow->leader_id,
		'secondary_item_id' => $follow->follower_id,
		'privacy' => 'onlyme',
	) );

	// // Modify group meta
	// groups_update_groupmeta( $group_id, 'last_activity', bp_core_current_time() );

	return true;
}

function bfc_activity_format_member_action_unfollow( $action, $activity ) {
	$follower_link = bp_core_get_userlink( $activity->user_id );

	$leader_link  = bp_core_get_userlink( $activity->secondary_item_id );

	$action = sprintf( __( '%1$s is no longer following %2$s', 'bfcommons-theme' ), $follower_link, $leader_link );

	/**
	 * Filters the 'unfollow' activity actions.
	 *
	 * @since BuddyPress 2.0.0
	 *
	 * @param string $action   The 'unfollow' activity actions.
	 * @param object $activity Activity data object.
	 */
	return apply_filters( 'bfc_activity_format_member_action_unfollow', $action, $activity );
}

add_action( 'bp_stop_following', 'member_unfollows', 10, 2 );

function member_unfollows( &$follow ) {

	if ( empty( $follower_id ) )
		$follower_id = bp_loggedin_user_id();

	// Record this in activity streams
	$activity_id = bp_activity_add( array(
		'component' => 'members',
		'type'    => 'unfollow',
		'item_id' => $follow->id,
		'user_id' => $follow->follower_id,
		'secondary_item_id' => $follow->leader_id,
		'privacy' => 'onlyme',
	) );

	// Add notification
	bp_notifications_add_notification( array(
		'user_id'           => $follow->leader_id,
		'item_id'           => $activity_id,
		'secondary_item_id' => $follow->follower_id,
		'component_name'    => 'extras',
		'component_action'  => 'unfollow',
		'date_notified'     => bp_core_current_time(),
		'is_new'            => 1,
	) );

	return true;
}

function bfc_activity_format_member_action_unfollowed( $action, $activity ) {
	$follower_link = bp_core_get_userlink( $activity->secondary_item_id );

	$leader_link  = bp_core_get_userlink( $activity->user_id );

	$action = sprintf( __( '%1$s is no longer following %2$s', 'bfcommons-theme' ), $follower_link, $leader_link );

	/**
	 * Filters the 'unfollowed' activity actions.
	 *
	 * @since BuddyPress 2.0.0
	 *
	 * @param string $action   The 'unfollowed' activity actions.
	 * @param object $activity Activity data object.
	 */
	return apply_filters( 'bfc_activity_format_member_action_unfollowed', $action, $activity );
}

add_action( 'bp_stop_following', 'member_unfollowed', 11, 2 );

function member_unfollowed( &$follow ) {

	if ( empty( $follower_id ) )
		$follower_id = bp_loggedin_user_id();

	// Record this in activity streams
	bp_activity_add( array(
		'component' => 'members',
		'type'    => 'unfollowed',
		'item_id' => $follow->id,
		'user_id' => $follow->leader_id,
		'secondary_item_id' => $follow->follower_id,
		'privacy' => 'onlyme',
	) );

	// // Modify group meta
	// groups_update_groupmeta( $group_id, 'last_activity', bp_core_current_time() );

	return true;
}

/** Notifications *************************************************************/

/**
 * @param array $component_names
 *
 * @return array
 * Add a follow component for notification.
 *
 */
function add_notification_components( $component_names = array() ) {
    // Force $component_names to be an array.
    if ( ! is_array( $component_names ) ) {
        $component_names = array();
    }
    // Add 'follow' component to registered components array.
    array_push( $component_names, 'extras' );
	// array_push( $component_names, 'left' );
    // Return component's with 'follow' appended.
    return $component_names;
}
add_filter( 'bp_notifications_get_registered_components', 'add_notification_components' );

/**
 * Notification formatting callback for left_group notifications.
 *
 * @since BuddyPress 1.0.0
 *
 * @param string $action            The kind of notification being rendered.
 * @param int    $item_id           The primary item ID.
 * @param int    $secondary_item_id The secondary item ID.
 * @param int    $total_items       The total number of messaging-related notifications
 *                                  waiting for the user.
 * @param string $format            'string' for BuddyBar-compatible notifications;
 *                                  'array' for WP Toolbar. Default: 'string'.
 * @param int    $notification_id   The notification ID.
 * @param string $screen            The screen.
 *
 * @return array|string
 */

// function left_group_format_notifications( $content, $item_id, $secondary_item_id, $total_items, $format = 'string', $action ) {
// 	if( $action == 'left_group' ){
// 		$activity = new BP_Activity_Activity( $item_id );
// 		$person = $activity->user_id;
// 		$group  = groups_get_group( $secondary_item_id );
// 		$group_name = esc_html( $group->name );

// 		$left_group_title = bp_core_get_user_displayname( $person ) . ' left the group ' . $group_name;
// 		$left_group_link  = bp_activity_get_permalink( $item_id );
// 		$left_group_text  = bp_core_get_user_displayname( $person ) . ' left the group ' . $group_name;
// 		return apply_filters( 'left_group_notification_filter', '<a href="' . esc_url( $left_group_link ) . '" title="' . esc_attr( $left_group_title ) . '">' . esc_html( $left_group_text ) . '</a>', $left_group_text, $left_group_link );
// 	}
// }


// add_filter( 'bp_notifications_get_notifications_for_user', 'left_group_format_notifications', 11, 6 );

/**
 * Notification formatting callback for follow notifications.
 *
 * @since BuddyPress 1.0.0
 *
 * @param string $action            The kind of notification being rendered.
 * @param int    $item_id           The primary item ID.
 * @param int    $secondary_item_id The secondary item ID.
 * @param int    $total_items       The total number of messaging-related notifications
 *                                  waiting for the user.
 * @param string $format            'string' for BuddyBar-compatible notifications;
 *                                  'array' for WP Toolbar. Default: 'string'.
 * @param int    $notification_id   The notification ID.
 * @param string $screen            The screen.
 *
 * @return array|string
 */
function follow_format_notifications( $content, $item_id, $secondary_item_id, $total_items, $format = 'string', $action ) {

	switch ($action) {
		case 'follow':
			$follow_title = bp_core_get_user_displayname( $secondary_item_id ) . ' is now following you.';
			$follow_link  = bp_activity_get_permalink( $item_id );
			$follow_text  = bp_core_get_user_displayname( $secondary_item_id ) . ' is now following you.';
			return apply_filters( 'follow_notification_filter', '<a href="' . esc_url( $follow_link ) . '" title="' . esc_attr( $follow_title ) . '">' . esc_html( $follow_text ) . '</a>', $follow_text, $follow_link );
			break;

		case 'unfollow':
			$follow_title = bp_core_get_user_displayname( $secondary_item_id ) . ' is no longer following you.';
			$follow_link  = bp_activity_get_permalink( $item_id );
			$follow_text  = bp_core_get_user_displayname( $secondary_item_id ) . ' is no longer following you.';
			return apply_filters( 'unfollow_notification_filter', '<a href="' . esc_url( $follow_link ) . '" title="' . esc_attr( $follow_title ) . '">' . esc_html( $follow_text ) . '</a>', $follow_text, $follow_link );
			break;

		case 'left_group':
			$activity = new BP_Activity_Activity( $item_id );
			$person = $activity->user_id;
			$group  = groups_get_group( $secondary_item_id );
			$group_name = esc_html( $group->name );

			$left_group_title = bp_core_get_user_displayname( $person ) . ' left the group ' . $group_name . ' where you are a steward';
			$left_group_link  = bp_activity_get_permalink( $item_id );
			$left_group_text  = bp_core_get_user_displayname( $person ) . ' left the group ' . $group_name . ' where you are a steward';
			return apply_filters( 'left_group_notification_filter', esc_html( $left_group_text ), $left_group_text, $left_group_link );
			break;
		default:
			break;
	}

}
add_filter( 'bp_notifications_get_notifications_for_user', 'follow_format_notifications', 10, 6 );

?>
