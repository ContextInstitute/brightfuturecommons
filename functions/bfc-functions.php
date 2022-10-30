<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

define( 'BP_GROUPS_DEFAULT_EXTENSION', 'courtyard' );

// from https://neliosoftware.com/blog/how-to-upload-additional-file-types-in-wordpress/

add_filter( 'upload_mimes', 'my_myme_types', 1, 1 );
function my_myme_types( $mime_types ) {
  $mime_types['svg'] = 'image/svg+xml';     // Adding .svg extension
//   $mime_types['json'] = 'application/json'; // Adding .json extension
//   
//   unset( $mime_types['xls'] );  // Remove .xls extension
//   unset( $mime_types['xlsx'] ); // Remove .xlsx extension
  
  return $mime_types;
}

function bfc_member_dropdown( $type, $instance_id, $person, $follow_class ) {
	$output = '';
	if ($person > 0 && !bp_is_user_deleted($person)) {
		$user = bp_loggedin_user_id();
		$old_user = bp_current_member_switched();
		$output .= '<div class="dropdown-pane ' . $follow_class . '" id="' . $type . '-dropdown-' . esc_attr( $instance_id ) . '" data-dropdown data-hover="true" data-hover-pane="true" data-auto-focus="false">';
		if ( $person != $user ) {
			$output .= '<a href="/members/' . bp_core_get_username( $user ) . '/messages/compose/?r=' . bp_core_get_username( $person ) . '">Send a message</a><br>';
			if ( 'follow-active' == $follow_class ) {
				$output .= bp_get_add_follow_button( $person, $user );
			}
			if ( is_super_admin( $user ) ) {
				$output .= bp_get_last_activity( $person );
				$output .= bp_get_add_switch_button( $person );
			}
		} elseif ( $old_user ) {
			$output .= bp_get_add_switch_button( $old_user->ID );
		}
		$output .= '<a href="/members/' . bp_core_get_username( $person ) . '">Visit profile</a></div>';
	}
	return $output;
}

function bfc_reply_post_date() {
	/*
	 Replaces bbp_reply_post_date() in loop-single-reply.php and loop-search-reply.php 
	 Based on bbp_get_reply_post_date()
	*/
	$post_timestamp = get_post_time('U', true);
	// $post_date = get_post_time( 'M j, Y' );
	echo apply_filters( 'bfc_reply_post_date', bfc_nice_date ($post_timestamp) );
  }

function bfc_nice_date ($post_timestamp){
	/*
	* $post_timestamp should be in GMT
	*/

	if(time() - $post_timestamp  < 7*86400 ) {
		return bp_core_time_since ($post_timestamp);
	} else {
		return date ('M j, Y', $post_timestamp);
	}
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

		if (isset($bp->groups->current_group->slug) && $bp->groups->current_group->slug == $bp->current_item) {
			$bp->bp_options_nav[$bp->groups->current_group->slug]['documents']['name'] = 'Uploads';
			$bp->bp_options_nav[$bp->groups->current_group->slug]['activity']['name'] = 'Timeline';
		}


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
	<div class="group-home-page">
		<h2 class="user-home-welcome">
			<span class="user-home-welcome-welcome">Welcome to the </span>
			<span class="user-home-welcome-name"><?php echo esc_html( bp_get_group_name() ); ?>'s dashboard</span>
		</h2>

		<div class="bfc-group-description"><?php bp_current_group_description();?></div>
		<div class="bfc-group-organizers"><?php
			$group_admins = groups_get_group_admins( bp_get_current_group_id() );
			
			$ga_count = 0;
			$olabel = " - group ";
			(1 < count( $group_admins )) ? $olabel .= "stewards" : $olabel .=  "steward"; 
			$is_follow_active = bp_is_active('activity') && function_exists('bp_is_activity_follow_active') && bp_is_activity_follow_active();
			$follow_class = $is_follow_active ? 'follow-active' : '';
			foreach ($group_admins as $admin) {
				$ga_count++;
				if ( 1 < $ga_count ) {
					echo ", ";
				}
				?>
				<span class="item-avatar" data-toggle="steward-dropdown-<?php echo esc_attr( $admin->user_id ); ?>">
					<?php echo bp_core_fetch_avatar( array( 'item_id' => $admin->user_id, 'type'   => 'thumb', 'width'  => '40', 'height' => '40' )); 
					echo " " .  bp_core_get_user_displayname($admin->user_id); ?></span>
				<?php 
				$type = 'steward';
				$instance_id = $admin->user_id;
				$person = $instance_id;
				echo bfc_member_dropdown( $type, $instance_id, $person, $follow_class );
			}
			echo $olabel;
			?>
		</div>
		

		<div id="bfc-user-panels" class="bfc-user-panels">
			<?php
				global $bfc_dropdown_prefix;
				$bfc_dropdown_prefix = 'dt';
				?>
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
			<?php $bfc_dropdown_prefix = 'ac';?>
			<?php if ( is_active_sidebar( 'dash_left_panel' ) ) : ?>
				<div id="bfc-dash-panel-top" class="bfc-user-panel-top widget-area" data-accordion-item>
				<a href="#" class="accordion-title">Latest Group Updates</a>
				<div class="accordion-content" data-tab-content>
					<?php dynamic_sidebar( 'dash_left_panel' ); ?>
				</div></div><!-- #bfc-dash-panel-le-top -->

			<?php endif; ?>
			<?php if ( is_active_sidebar( 'dash_center_panel' ) ) : ?>
				<div id="bfc-dash-panel-middle" class="bfc-user-panel-middle widget-area" data-accordion-item>
				<a href="#" class="accordion-title">Latest Forum Posts</a>
				<div class="accordion-content" data-tab-content>
					<?php dynamic_sidebar( 'dash_center_panel' ); ?>
				</div></div><!-- #bfc-dash-panel-middle -->
			<?php endif; ?>

			<?php if ( is_active_sidebar( 'dash_right_panel' ) ) : ?>
				<div id="bfc-dash-panel-bottom" class="bfc-user-panel-bottom widget-area" data-accordion-item>
				<a href="#" class="accordion-title">Latest Group Docs</a>
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
	if ( bp_get_total_group_count_for_user( bp_loggedin_user_id() ) >0 ) {
		$args['scope'] = 'personal';
		$args['include'] = $groupids['groups'];
	} else {
		$args['scope'] = 'other';
	}
  }

  if ( $args['scope'] === 'others' ) { // You can change the scope value
	$args['exclude'] = $groupids['groups'];
  } 
  $qs = build_query( $args );

  return $qs;

}

add_filter( 'bp_nouveau_get_members_directory_nav_items', 'bfc_followers_nav' ); 

function bfc_followers_nav  ( $nav_items ) {
	if ( is_user_logged_in() ) {
		// If follow component is active and the user has no followers yet - expands on bp_nouveau_get_members_directory_nav_items() in wp-content/plugins/buddyboss-platform/bp-templates/bp-nouveau/includes/members/functions.php
		if ( bp_is_active( 'activity' ) && bp_is_activity_follow_active() ) {
			$counts = bp_total_follow_counts();

			if ( empty( $counts['following'] ) ) {
				$nav_items['following'] = array(
					'component' => 'members',
					'slug'      => 'following', // slug is used because BP_Core_Nav requires it, but it's the scope
					'li_class'  => array(),
					'link'      => bp_loggedin_user_domain() . bp_get_follow_slug() . '/my-following/',
					'text'      => __( 'Following', 'buddyboss' ),
					'count'     => 0,
					'position'  => 16,
				);
			}
		// }
		
		// if ( bp_is_active( 'activity' ) && bp_is_activity_follow_active() ) {
		// 	$counts = bp_total_follow_counts();
			if ( empty( $counts['followers'] ) ) {$counts['followers'] = 0;}

			$nav_items['followers'] = array(
				'component' => 'members',
				'slug'      => 'followers', // slug is used because BP_Core_Nav requires it, but it's the scope
				'li_class'  => array(),
				'link'      => bp_loggedin_user_domain() . bp_get_follow_slug() . '/my-followers/',
				'text'      => __( 'Followers', 'bfcommons-theme' ),
				'count'     => $counts['followers'],
				'position'  => 20,
			);
		}
	return $nav_items;
	}	
}

add_action( 'bp_ajax_querystring', 'bfc_followers_args', 99, 2 );

function bfc_followers_args ( $qs, $object ) {
  
  if ( 'members' !== $object ) {
    return $qs;
  }

  if ( ! is_user_logged_in() ) {
    return $qs;
  }

  $args = wp_parse_args( $qs );

  if ( !isset ($args['scope'])) {
	$args['scope'] = 'all';
  }
  
  if ( $args['scope'] === 'followers' ) { 
	$args['include'] = bp_get_follower_ids( array ('user_id' => bp_loggedin_user_id()) );
	$qs = build_query( $args );
  } 
 
  return $qs;
}


function bfc_group_members( $group_id = false, $role = array() ) {

	if ( ! $group_id ) {
		return '';
	}

	$members = new BP_Group_Member_Query(
		array(
			'group_id'     => $group_id,
			'per_page'     => 10,
			'page'         => 1,
			'group_role'   => $role,
			'exclude'      => false,
			'search_terms' => false,
			'type'         => 'first_joined',
		)
	);

	$total   = $members->total_users;
	$members = array_values( $members->results );

	if ( ! empty( $members ) ) {
		?><span class="bs-group-members">
		<?php
		foreach ( $members as $member ) {
			$avatar = bp_core_fetch_avatar(
				array(
					'item_id'    => $member->ID,
					'avatar_dir' => 'avatars',
					'object'     => 'user',
					'type'       => 'thumb',
					'html'       => false,
				)
			);
			$uname = esc_attr( bp_core_get_user_displayname( $member->ID ) );
			?>
			<div class="bfc-tooltip">
				<img src="<?php echo $avatar; ?>"
				 alt="<?php echo $uname; ?>" class=".bfc-rounded"/>
				<span class="bfc-tooltiptext"><a href="/members/<?php echo bp_core_get_username( $member->ID );?>"><?php echo $uname; ?></a>
				<?php 
				if (groups_is_user_admin( $member->ID, $group_id )) {
					echo '<br>group steward';
					} 
				?>
			</span>
			</div>
			<?php
		}
		?>
		</span>
		<?php
		if ( $total - sizeof( $members ) != 0 ) {
			$member_count = $total - sizeof( $members );
			?>
			<span class="members">
				<span class="members-count-g">+<?php echo esc_html( $member_count  ); ?></span> <?php printf( _n( 'member', 'members', $member_count, 'bfcommons-theme' ) ); ?>
			</span>
			<?php
		}
	}

}

// used to display the latest post for a group on the group directory page
function bfc_latest_post ($ugroup_id = 0) {

	// Bail if group has no forum
	if ( !bp_group_is_forum_enabled( groups_get_group( $ugroup_id ) ) ) {
		return;
	}

	$topics_query = array(
		'post_type'           => bbp_get_topic_post_type(),
		'post_parent__in'     => groups_get_groupmeta( $ugroup_id, $meta_key = 'forum_id', $single = true),
		'posts_per_page'      => 1,
		'post_status'         => array( bbp_get_public_status_id(), bbp_get_closed_status_id() ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'meta_key'            => '_bbp_last_active_time',
		'orderby'             => 'meta_value',
		'order'               => 'DESC',
	);

	global $bsp_style_settings_la ;
	$avatar_size = (!empty($bsp_style_settings_la['AvatarSize']) ? $bsp_style_settings_la['AvatarSize']  : '40') ;

	$is_follow_active = bp_is_active('activity') && function_exists('bp_is_activity_follow_active') && bp_is_activity_follow_active();
	$follow_class = $is_follow_active ? 'follow-active' : '';

	$widget_query = new WP_Query( $topics_query );
    // Bail if no topics are found
    if ( ! $widget_query->have_posts() ) {
    return;
    } ?>
    <ul class="bfc-la-ul">
    <?php
    $widget_query->the_post();
    $topic_id    = bbp_get_topic_id( $widget_query->post->ID );
    $author_link = '';
    
    //check if this topic has a reply
    $reply = get_post_meta( $topic_id, '_bbp_last_reply_id',true);
    
    //if no reply the author
    if (empty ($reply)) {
        $author_avatar = bbp_get_topic_author_link( array( 'post_id' => $topic_id, 'type' => 'avatar', 'size' => $avatar_size ) );
        $author_name = bbp_get_topic_author_link( array( 'post_id' => $topic_id, 'type' => 'name' ) );
    //if has a reply then get the author of the reply
    } else { 
        $author_avatar = bbp_get_reply_author_link( array( 'post_id' => $reply, 'type' => 'avatar', 'size' => $avatar_size) );
        $author_name = bbp_get_reply_author_link( array( 'post_id' => $reply, 'type' => 'name') );
    } 
    
    // Create excerpt
    $post_id = empty ($reply)? $topic_id : $reply;
    $bfc_excerpt = wp_trim_words(bbp_get_reply_content($post_id), 15);
    ?>

    <li class="bfc-la-li">
    <div class="bfc-la-topic-author-avatar topic-author bfc-rounded">
    <span data-toggle="reply-author-dropdown-<?php echo esc_attr( $post_id ); ?>"><?php bbp_reply_author_avatar( $post_id,  $size = 40 ); ?></span><br>
   
   <?php 
    echo '</div><div class="bfc-la-topic-text">';
    //if no replies set the link to the topic
    if (empty ($reply)) {?>
        <a class="bsp-la-reply-topic-title" href="<?php bbp_topic_permalink( $topic_id ); ?>"><?php bbp_topic_title( $topic_id ); ?></a>
    <?php } 
    //if replies then set link to the latest reply
    else { 
        echo '<a class="bsp-la-reply-topic-title" href="' . esc_url( bbp_get_reply_url( $reply ) ) . '" >' . bbp_get_reply_topic_title( $reply ) . '</a>';
    } ?>
    
        <?php if ( ! empty( $settings['show_count'] ) && bbp_get_topic_post_type() == get_post_type()) {
                        $topic = get_the_ID(); ?>
                            <span class="bsp-topic-posts">
                                <?php if ( ! empty( $settings['reply_count_label'] )) echo $settings['reply_count_label'] ; ?>
                                <?php bbp_topic_reply_count($topic); ?>
                            </span>
        <?php } 
        
        echo '<div class="bfc-la-topic-excerpt">' . $bfc_excerpt . '</div>';
        echo '<span class="bfc-la-topic-author-name topic-author">' . $author_name . '</span>';
    ?>
    </li>
    </ul>
    <?php
    wp_reset_postdata();
}

function bfc_rename_forum_nav ($link_text,$nav_item,$displayed_nav){
	if (('Forum Threads' == $link_text || 'Discussions' == $link_text) && 'groups'== $displayed_nav) {
		$link_text = 'Forum';
		return $link_text;
	} elseif ('notifications' == $nav_item->slug && 'groups'== $displayed_nav && strpos( $nav_item->parent_slug,'manage')) {
		$link_text = 'Admin Emails';
		return $link_text;
	} else {
		return $link_text;
	}
}

add_filter( 'bp_nouveau_get_nav_link_text', 'bfc_rename_forum_nav',10,3);


/**
 * Output the state buttons in the Activity Update widget.
 *
 * @since BuddyPress 3.0.0
 */
function bfc_widget_activity_state() {

	$activity_id     = bp_get_activity_id();
	$like_text       = bp_activity_get_favorite_users_string( $activity_id );
	$comment_count   = bp_activity_get_comment_count();
	// $favorited_users = bp_activity_get_favorite_users_tooltip_string( $activity_id );

	?>
	<div class="activity-state <?php echo $like_text ? 'has-likes' : ''; ?> <?php echo $comment_count ? 'has-comments' : ''; ?>">
		<span class="activity-state-likes like-text">
			<?php echo $like_text ?: ''; ?>
		</span>
		<?php if ($like_text) : ?>
			<span class="ac-state-separator">&middot;</span>
		<?php endif;
		if ( bp_activity_can_comment() && $comment_count) :
			$activity_state_comment_class['activity_state_comment_class'] = 'activity-state-comments';
			$activity_state_class            = apply_filters( 'bp_nouveau_get_activity_comment_buttons_activity_state', $activity_state_comment_class, $activity_id );
			?>
			<!-- <a href="#" class="<?php echo esc_attr( trim( implode( ' ', $activity_state_class ) ) ); ?>"> -->
				<span class="comments-count">
					<?php
					if ( $comment_count > 1 ) {
						echo $comment_count . ' ' . __( 'Comments', 'bfcommons-theme' );
					} else {
						echo $comment_count . ' ' . __( 'Comment', 'bfcommons-theme' );
					}
					?>
				</span>
			<!-- </a> -->
		<?php endif; ?>
	</div>
	<?php
}

function bfc_get_forum_title( $post = 0 ) {
	$post = get_post( $post );

	$title = isset( $post->post_title ) ? $post->post_title : '';
	$id    = isset( $post->ID ) ? $post->ID : 0;
	return apply_filters( 'bfc_forum_title', $title, $id );
}

// from https://stackoverflow.com/questions/31558464/how-to-change-members-per-page-in-buddypress-members-directory
function bfc_members_per_page( $retval ) {
    $retval['per_page'] = 32;
    return $retval;
}
add_filter( 'bp_after_has_members_parse_args', 'bfc_members_per_page' );

add_filter('bp_activity_get_visibility_levels', 'bfc_activity_visibility_levels'); 

function bfc_activity_visibility_levels($allowed_visibilities){
	unset( $allowed_visibilities['public'] );
	return $allowed_visibilities;
}

add_filter( 'bp_before_activity_add_parse_args', 'bfc_custom_change_privacy_when_public', 100 );

function bfc_custom_change_privacy_when_public( $r ) {  
if ( 'public' === $r['privacy'] ) {    $r['privacy'] = 'loggedin';  }  
return $r;
}

add_filter ('bp_nouveau_feedback_messages', 'bfc_feedback_messages');

function bfc_feedback_messages($feedback_messages) {
	if(bp_loggedin_user_id() == bp_displayed_user_id()) {
		$user_possessive = "Your";
	} else {
		$user_possessive = esc_html( xprofile_get_field_data( 'First Name', bp_displayed_user_id() ) ) . "'s";
	}

	$feedback_messages['member-media-loading'] = array(
		'type'    => 'loading',
		'message' => sprintf( __( 'Loading %s photos. Please wait.', 'bfcommons-theme' ), $user_possessive ));
	$feedback_messages['member-activity-loading'] = array(
		'type'    => 'loading',
		'message' => sprintf( __( 'Loading %s updates. Please wait.', 'bfcommons-theme' ), $user_possessive ));
	$feedback_messages['member-blogs-loading'] = array(
		'type'    => 'loading',
		'message' => sprintf( __( 'Loading %s blog. Please wait.', 'bfcommons-theme' ), $user_possessive ));
	$feedback_messages['member-friends-loading'] = array(
		'type'    => 'loading',
		'message' => sprintf( __( 'Loading %s friends. Please wait.', 'bfcommons-theme' ), $user_possessive ));
	$feedback_messages['member-mutual-friends-loading'] = array(
		'type'    => 'loading',
		'message' => sprintf( __( 'Loading %s mutual connections. Please wait.', 'bfcommons-theme' ), $user_possessive ));
	$feedback_messages['member-groups-loading'] = array(
		'type'    => 'loading',
		'message' => sprintf( __( 'Loading %s groups. Please wait.', 'bfcommons-theme' ), $user_possessive ));
	$feedback_messages['member-media-loading'] = array(
		'type'    => 'loading',
		'message' => sprintf( __( 'Loading %s photos. Please wait.', 'bfcommons-theme' ), $user_possessive ));
	$feedback_messages['member-document-loading'] = array(
		'type'    => 'loading',
		'message' => sprintf( __( 'Loading %s files. Please wait.', 'bfcommons-theme' ), $user_possessive ));
	$feedback_messages['member-video-loading'] = array(
		'type'    => 'loading',
		'message' => sprintf( __( 'Loading %s videos. Please wait.', 'bfcommons-theme' ), $user_possessive ));
	$feedback_messages['group-members-search-none'] = array(
		'type'    => 'info',
		'message' => __( 'Sorry, no one with that name found within this group.', 'bfcommons-theme' ));
	$feedback_messages['group-requests-loading'] = array(
		'type'    => 'loading',
		'message' => __( 'Loading the people who requested to join the group. Please wait.', 'bfcommons-theme' ));
	$feedback_messages['directory-members-loading'] = array(
		'type'    => 'loading',
		'message' => __( 'Loading people of the network. Please wait.', 'bfcommons-theme' ));
	$feedback_messages['members-loop-none'] = array(
		'type'    => 'info',
		'message' => __( 'Sorry, no one was found.', 'bfcommons-theme' ));
	return $feedback_messages;
}

/**
 * Checks the current page to see if current_page_parent should be removed from the blog link or added to a cpt archive pagelink
 * 
 * From https://gist.github.com/cr0ybot/6236d1fccf315ec5aa2af7580d3a348c
 */
function bfc_cpt_menu_highlight( $classes, $item, $args ) {
	// Remove current_page_parent from Blog if not on a post
	$cpp = array_search( 'current_page_parent', $classes );
	if ( $cpp !== false && $item->type === 'post_type' ) {
		$qo = get_queried_object();
		if ( ( $qo instanceof WP_Post && $qo->post_type !== 'post' ) ||
	 		$qo instanceof WP_Post_Type && $qo->name !== 'post' ) {
			array_splice($classes, $cpp - 1, 1);
		}
	}

	// Add current_page_parent for cpts
	else {
		$pt = get_post_type();
		if ( $pt !== 'page' && $item->object === $pt ) {
			$classes[] = 'current_page_parent';
		}
	}

	return $classes;
}
add_filter( 'nav_menu_css_class', 'bfc_cpt_menu_highlight', 10, 3 );

add_filter( 'bbp_show_lead_topic', '__return_false');
add_filter( 'bbp_use_wp_editor', '__return_true');


function bfc_trim_words( $text, $num_words = 55, $more = null ) {
    if ( null === $more ) {
        $more = __( '&hellip;' );
    }
 
    $original_text = $text;
    // $text          = wp_strip_all_tags( $text );
    $num_words     = (int) $num_words;
 
    /*
     * translators: If your word count is based on single characters (e.g. East Asian characters),
     * enter 'characters_excluding_spaces' or 'characters_including_spaces'. Otherwise, enter 'words'.
     * Do not translate into your own language.
     */
    if ( strpos( _x( 'words', 'Word count type. Do not translate!' ), 'characters' ) === 0 && preg_match( '/^utf\-?8$/i', get_option( 'blog_charset' ) ) ) {
        $text = trim( preg_replace( "/[\n\r\t ]+/", ' ', $text ), ' ' );
        preg_match_all( '/./u', $text, $words_array );
        $words_array = array_slice( $words_array[0], 0, $num_words + 1 );
        $sep         = '';
    } else {
        $words_array = preg_split( "/[\n\r\t ]+/", $text, $num_words + 1, PREG_SPLIT_NO_EMPTY );
        $sep         = ' ';
    }
 
    if ( count( $words_array ) > $num_words ) {
        array_pop( $words_array );
        $text = implode( $sep, $words_array );
        $text = $text . $more;
    } else {
        $text = implode( $sep, $words_array );
    }
 
    /**
     * Filters the text content after words have been trimmed.
     *
     * @since 3.3.0
     *
     * @param string $text          The trimmed text.
     * @param int    $num_words     The number of words to trim the text to. Default 55.
     * @param string $more          An optional string to append to the end of the trimmed text, e.g. &hellip;.
     * @param string $original_text The text before it was trimmed.
     */
    return apply_filters( 'bfc_trim_words', $text, $num_words, $more, $original_text );
}

/**
 * Simple helper function for make menu item objects
 * See https://www.daggerhartlab.com/dynamically-add-item-to-wordpress-menus/
 * @param $title      - menu item title
 * @param $url        - menu item url
 * @param $order      - where the item should appear in the menu
 * @param int $parent - the item's parent item
 * @return \stdClass
 */ 
function _custom_nav_menu_item( $title, $url, $order, $parent = 0 ){
	$item = new stdClass();
	$item->ID = 1000000 + $order + $parent;
	$item->db_id = $item->ID;
	$item->title = $title;
	$item->url = $url;
	$item->menu_order = $order;
	$item->menu_item_parent = $parent;
	$item->type = '';
	$item->object = '';
	$item->object_id = '';
	$item->classes = array();
	$item->target = '';
	$item->attr_title = '';
	$item->description = '';
	$item->xfn = '';
	$item->status = '';
	return $item;
  }

add_filter( 'wp_get_nav_menu_items', 'bfc_mygroups_nav_menu_items', 20, 2 );
function bfc_mygroups_nav_menu_items( $items, $menu ) {

	if ( $menu->slug == "bfcom-main-menu" && bp_loggedin_user_id()) {
		foreach ($items as $item) {
			if ($item->title == "Groups") {
				$mygroups = groups_get_user_groups(bp_loggedin_user_id());
				$group_ids = (array)$mygroups['groups'];
				$order = 1100;
				foreach ($group_ids as $group_id) {
					$order++;
					$group_obj = groups_get_group( $group_id );
					$items[] =  _custom_nav_menu_item($group_obj->name, bp_get_group_permalink( $group_obj ), $order, $item->ID );
				}
			}
		}
	}
  return $items;
}

if ( ! function_exists( 'bfc_comment' ) ) {

	function bfc_comment( $comment, $args, $depth ) {
		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( 'div' == $args['style'] ) {
			$tag       = 'div';
			$add_below = 'comment';
		} else {
			$tag       = 'li';
			$add_below = 'div-comment';
		}
		$is_follow_active = bp_is_active('activity') && function_exists('bp_is_activity_follow_active') && bp_is_activity_follow_active();
		$follow_class = $is_follow_active ? 'follow-active' : '';
		$user = bp_loggedin_user_id();
		$type = 'comment';
		$person = $comment->user_id;
		$post_id = get_comment_ID();

		?>

		<<?php echo esc_attr( $tag ); ?> <?php comment_class( $args['has_children'] ? 'parent' : '', $comment ); ?> id="comment-<?php comment_ID(); ?>">

	<article id="div-comment-<?php comment_ID(); ?>" class="comment-body">

			<?php
			if ( 0 != $args['avatar_size'] ) {
				$user_link = function_exists( 'bp_core_get_user_domain' ) ? bp_core_get_user_domain( $comment->user_id ) : get_comment_author_url( $comment );
				?>
				<div class="comment-author vcard data-bp-item-id="<?php echo $person; ?>" data-bp-item-component="members"">
					<span class="bfc-la-topic-author-avatar topic-author bfc-dropdown-span" data-toggle="<?php echo $type . '-dropdown-' . esc_attr( $post_id ); ?>">
						<?php echo get_avatar( $comment, $args['avatar_size'] ); ?>
					</span>
					<?php echo bfc_member_dropdown( $type, $post_id, $person, $follow_class );?>
				</div>
			<?php } ?>

		<div class="comment-content-wrap">
			<div class="comment-meta comment-metadata">
				<?php
				printf(
					/* translators: %s: Author related metas. */
					__( '%s', 'buddyboss-theme' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.I18n.NoEmptyStrings
					sprintf(
						'<cite class="fn comment-author">%s</cite>',
						get_comment_author_link( $comment )
					)
				);
				?>
				<a class="comment-date" href="<?php echo esc_url( get_comment_link( $comment, $args ) ); ?>">
					<?php
					printf(
						/* translators: %s: Author comment date. */
						__( '%1$s', 'buddyboss-theme' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.I18n.NoEmptyStrings
						get_comment_date( '', $comment ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.I18n.NoEmptyStrings
						get_comment_time() // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.I18n.NoEmptyStrings
					);
					?>
				</a>
			</div>

			<?php if ( '0' == $comment->comment_approved ) { ?>
				<p>
					<em class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'buddyboss-theme' ); ?></em>
				</p>
			<?php } ?>

			<div class="comment-text">
				<?php
				comment_text(
					$comment,
					array_merge(
						$args,
						array(
							'add_below' => $add_below,
							'depth'     => $depth,
							'max_depth' => $args['max_depth'],
						)
					)
				);
				?>
			</div>

			<?php if ( !class_exists('Simple_Comment_Editing') || (class_exists('Simple_Comment_Editing') && !bfc_docs_can_edit_comment ($comment) )) : ?>
			<footer class="comment-footer">
				<?php 
				if (current_user_can ('edit_others_posts')) {
					edit_comment_link( __( 'Admin Edit', 'buddyboss-theme' ), '', '' );
				}
				comment_reply_link(
					array_merge(
						$args,
						array(
							'reply_text'    => __( 'Reply to this comment' ),
							'add_below' => $add_below,
							'depth'     => $depth,
							'max_depth' => $args['max_depth'],
							'before'    => '',
							'after'     => '',
						)
					)
				);
				?>
			</footer>
			<?php endif; ?>
		</div>		</article>
		<?php
	}
}

add_filter('bp_nouveau_get_classes', 'bfc_photos_is_parent', 10 , 3);

function bfc_photos_is_parent( $class_list, $classes, $item) {
	if ('albums' == bp_current_action() && 'photos' == $item->slug && bp_current_component() == 'groups') {
		$classes[] = 'current';
		$classes[] = 'selected';
	}
	return join( ' ', $classes );
}

/**
 * Exclude users from BuddyPress members list.
 * 
 * From https://buddydev.com/hiding-users-on-buddypress-based-site/
 *
 * @param array $args args.
 *
 * @return array
 */
function bfc_exclude_users( $args ) {
    // do not exclude in admin.
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return $args;
    }
 
    $excluded = isset( $args['exclude'] ) ? $args['exclude'] : array();
 
    if ( ! is_array( $excluded ) ) {
        $excluded = explode( ',', $excluded );
    }
 
    // Change it with the actual numeric user ids.
    $user_ids = array( 1, 3 ); // user ids to exclude.
 
    $excluded = array_merge( $excluded, $user_ids );
 
    $args['exclude'] = $excluded;
 
    return $args;
}
 
add_filter( 'bp_xprofile_is_richtext_enabled_for_field', 'bfc_disable_rt_function', 10, 2 );
function bfc_disable_rt_function( $enabled, $field_id ) {
  if ( 8 != $field_id ) {
    $enabled = false;
  }
  return $enabled;
}

?>
