<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

define( 'BP_GROUPS_DEFAULT_EXTENSION', 'courtyard' );


function bfc_member_dropdown( $type, $instance_id, $person, $follow_class ) {
	$user = bp_loggedin_user_id();
	$output = '<div class="dropdown-pane ' . $follow_class . '" id="' . $type . '-dropdown-' . esc_attr( $instance_id ) . '" data-dropdown data-hover="true" data-hover-pane="true" data-auto-focus="false">';
	if ( $person != $user ) {
		$output .= '<a href="/members/' . bp_core_get_username( $user ) . '/messages/compose/?r=' . bp_core_get_username( $person ) . '">Send a message</a><br>';
		if ( 'follow-active' == $follow_class ) {
			$output .= bp_get_add_follow_button( $person, $user );
		}
	}
	$output .= '<a href="/members/' . bp_core_get_username( $person ) . '">Visit profile</a></div>';
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
		$result = $post_date;
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
				<span class="item-avatar" data-toggle="steward-dropdown-<?php echo esc_attr( $admin->user_id ); ?>">
					<?php echo bp_core_fetch_avatar( array( 'item_id' => $admin->user_id, 'type'   => 'thumb', 'width'  => '40', 'height' => '40' )); 
					echo " " .  bp_core_get_user_displayname($admin->user_id); ?></span>
				<?php 
				$type = 'steward';
				$instance_id = $admin->user_id;
				$person = $instance_id;
				echo bfc_member_dropdown( $type, $instance_id, $person, $follow_class );
			}
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
		if ( bp_is_active( 'activity' ) && bp_is_activity_follow_active() ) {
			$counts = bp_total_follow_counts();

			if ( ! empty( $counts['followers'] ) ) {
				$nav_items['followers'] = array(
					'component' => 'members',
					'slug'      => 'followers', // slug is used because BP_Core_Nav requires it, but it's the scope
					'li_class'  => array(),
					'link'      => bp_loggedin_user_domain() . bp_get_follow_slug() . '/my-followers/',
					'text'      => __( 'Followers', 'buddyboss' ),
					'count'     => $counts['followers'],
					'position'  => 20,
				);
			}
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

function bfc_remove_bp_forum_notifications () {
	if ('no' == (bp_get_user_meta(bp_loggedin_user_id(), 'notification_forums_following_reply', true ))) {
		bp_update_user_meta (bp_loggedin_user_id(), 'notification_forums_following_reply', 'yes' );
	}
	if ('no' == (bp_get_user_meta(bp_loggedin_user_id(), 'notification_forums_following_topic', true ))) {
		bp_update_user_meta (bp_loggedin_user_id(), 'notification_forums_following_topic', 'yes' );
	}
}
add_action( 'bp_notification_settings', 'bfc_remove_bp_forum_notifications', 99 );

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
				<span class="members-count-g">+<?php echo esc_html( $member_count  ); ?></span> <?php printf( _n( 'member', 'members', $member_count, 'buddyboss-theme' ) ); ?>
			</span>
			<?php
		}
	}

}

function bfc_latest_post ($ugroup_id = 0) {

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

function bfc_rename_group_navs ($link_text,$nav_item,$displayed_nav){
	if ('Discussions' == $link_text && 'groups'== $displayed_nav) {$link_text = 'Forum';}
	return $link_text;
}

add_filter( 'bp_nouveau_get_nav_link_text', 'bfc_rename_group_navs',10,3);

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
						echo $comment_count . ' ' . __( 'Comments', 'buddyboss' );
					} else {
						echo $comment_count . ' ' . __( 'Comment', 'buddyboss' );
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

?>
