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
			(1 < count( $group_admins )) ? $olabel = "Organizers: " : $olabel =  "Organizer: "; 
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

?>
