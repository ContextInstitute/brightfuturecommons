<?php

/**
 * Register our sidebars and widgetized areas on the user and group home pages.
 * The 'name' creates a section in Appearance > Widgets in the Admin backend
 * The 'id' is used as a selector in the HTML of the relevant page template
 * See https://developer.wordpress.org/reference/functions/register_sidebar/
 *
 */
function bfc_sidebars_init() {

	register_sidebar( array(
		'name'          => 'User Home Left Panel',
		'id'            => 'user_left_panel',
		'before_widget' => '<div>',
		'after_widget'  => '</div>',
		'before_title'  => '<h2 class="rounded">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => 'User Home Center Panel',
		'id'            => 'user_center_panel',
		'before_widget' => '<div>',
		'after_widget'  => '</div>',
		'before_title'  => '<h2 class="rounded">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => 'User Home Right Panel',
		'id'            => 'user_right_panel',
		'before_widget' => '<div>',
		'after_widget'  => '</div>',
		'before_title'  => '<h2 class="rounded">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => 'Group Dash Left Panel',
		'id'            => 'dash_left_panel',
		'before_widget' => '<div>',
		'after_widget'  => '</div>',
		'before_title'  => '<h2 class="rounded">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => 'Group Dash Right Panel',
		'id'            => 'dash_right_panel',
		'before_widget' => '<div>',
		'after_widget'  => '</div>',
		'before_title'  => '<h2 class="rounded">',
		'after_title'   => '</h2>',
	) );

}

add_action( 'widgets_init', 'bfc_sidebars_init' );

function bfc_register_messages_widget() {
	register_widget( 'bfc_messages_widget' );
}
add_action( 'widgets_init', 'bfc_register_messages_widget' );


/**
 * new widget to show topics, but with latest author
 * modified from the bbPress Style Pack Display Topics widget
 */ 

function register_la_widget() {
    register_widget("bsp_Activity_Widget");

}

add_action('widgets_init', 'register_la_widget');


//latest activity widget
class bsp_Activity_Widget extends WP_Widget {

	/**
	 * bbPress Topic Widget
	 *
	 * Registers the topic widget
	 *
	 * @since bbPress (r2653)
	 *
	 * @uses apply_filters() Calls 'bbp_topics_widget_options' with the
	 *                        widget options
	 */
	public function __construct() {
		$widget_ops = apply_filters( 'bsp_topics_widget_options', array(
			'classname'   => 'widget_display_topics',
			'description' => __( 'A list of recent topics, sorted by popularity or freshness with latest author.', 'bbp-style-pack' )
		) );

		parent::__construct( false, __( '(Style Pack) Latest Activity', 'bbp-style-pack' ), $widget_ops );
	}

	/**
	 * Register the widget
	 *
	 * @since bbPress (r3389)
	 *
	 * @uses register_widget()
	 */
	public static function register_widget() {
		register_widget( 'bsp_Activity_Widget' );
	}

	/**
	 * Displays the output, the topic list
	 *
	 * @since bbPress (r2653)
	 *
	 * @param mixed $args
	 * @param array $instance
	 * @uses apply_filters() Calls 'bbp_topic_widget_title' with the title
	 * @uses bbp_topic_permalink() To display the topic permalink
	 * @uses bbp_topic_title() To display the topic title
	 * @uses bbp_get_topic_last_active_time() To get the topic last active
	 *                                         time
	 * @uses bbp_get_topic_id() To get the topic id
	 */
	public function widget( $args = array(), $instance = array() ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance );

		// Typical WordPress filter
		$settings['title'] = apply_filters( 'widget_title',           $settings['title'], $instance, $this->id_base );

		// bbPress filter
		$settings['title'] = apply_filters( 'bsp_latest_activity_widget_title', $settings['title'], $instance, $this->id_base );
		
		//set default for exclude
		
		//see if we have multiple forums
			if (bp_is_group()) {
				$settings['post_parent__in'] = groups_get_groupmeta( bp_get_current_group_id(), $meta_key = 'forum_id', $single = true);
				
			} else {
				$bfc_user_groups = groups_get_user_groups( bp_loggedin_user_id() );
				$uforum = array() ;
				foreach ($bfc_user_groups['groups'] as $ugroup) {
					$uforum_id = groups_get_groupmeta( $ugroup, $meta_key = 'forum_id', $single = true);

					$uforum[] .= $uforum_id[0];
				}
				$settings['post_parent__in'] = $uforum;
				
			}
				
		// How do we want to order our results?
		switch ( $settings['order_by'] ) {

			// Order by most recent replies
			case 'freshness' :
				$topics_query = array(
					'post_type'           => bbp_get_topic_post_type(),
					'post_parent'         => $settings['parent_forum'],
					'posts_per_page'      => (int) $settings['max_shown'],
					'post_status'         => array( bbp_get_public_status_id(), bbp_get_closed_status_id() ),
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
					'meta_key'            => '_bbp_last_active_time',
					'orderby'             => 'meta_value',
					'order'               => 'DESC',
				);
				break;

			// Order by total number of replies
			case 'popular' :
				$topics_query = array(
					'post_type'           => bbp_get_topic_post_type(),
					'post_parent'         => $settings['parent_forum'],
					'posts_per_page'      => (int) $settings['max_shown'],
					'post_status'         => array( bbp_get_public_status_id(), bbp_get_closed_status_id() ),
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
					'meta_key'            => '_bbp_reply_count',
					'orderby'             => 'meta_value',
					'order'               => 'DESC'
				);
				break;

			// Order by which topic was created most recently
			case 'newness' :
			default :
				$topics_query = array(
					'post_type'           => bbp_get_topic_post_type(),
					'post_parent'         => $settings['parent_forum'],
					'posts_per_page'      => (int) $settings['max_shown'],
					'post_status'         => array( bbp_get_public_status_id(), bbp_get_closed_status_id() ),
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
					'order'               => 'DESC'
				);
				break;
		}
		//set size for avatar
		global $bsp_style_settings_la ;
		$avatar_size = (!empty($bsp_style_settings_la['AvatarSize']) ? $bsp_style_settings_la['AvatarSize']  : '40') ;
		
		
		//allow other plugin (eg private groups) to filter this query
		$topics_query = apply_filters( 'bsp_activity_widget', $topics_query ) ;
		
		// The default forum query with allowed forum ids array added
		//reset the max to be shown
		$topics_query['posts_per_page'] =(int) $settings['max_shown'] ;
		
		//add any include/exclude forums ;
		if (!empty ($settings['post_parent__not_in'])) $topics_query['post_parent__not_in'] = $settings['post_parent__not_in'] ;
		else $topics_query['post_parent__in']= $settings['post_parent__in'] ;
		
		// Note: private and hidden forums will be excluded via the
		// bbp_pre_get_posts_normalize_forum_visibility action and function.
		$widget_query = new WP_Query( $topics_query );
				// Bail if no topics are found
		if ( ! $widget_query->have_posts() ) {
			return;
		}

		$is_follow_active = bp_is_active('activity') && function_exists('bp_is_activity_follow_active') && bp_is_activity_follow_active();
		$follow_class = $is_follow_active ? 'follow-active' : '';

		
		echo $args['before_widget'];
		
		echo '<span class="bsp-la-title">' . $args['before_title'] . $settings['title'] . $args['after_title'] . '</span>' ;
		?>
		
		<ul class="bfc-la-ul">

			<?php while ( $widget_query->have_posts() ) :
				

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
				<div class="bfc-la-topic-author-avatar topic-author">
				<span data-toggle="reply-author-dropdown-<?php echo esc_attr( $post_id ); ?>"><?php bbp_reply_author_avatar( $post_id,  $size = 40 ); ?></span><br>
				<?php 
				$type = 'reply-author';
				$source = $post_id;
				echo bfc_avatar_dropdown ($type,$source,$follow_class);
				?>
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
					
					if ( ! empty( $settings['show_freshness'] ) ) : ?>
					<?php $output = bbp_get_topic_last_active_time( $topic_id ) ; 
						//shorten freshness?
						if ( ! empty( $settings['shorten_freshness'] ) ) $output = preg_replace( '/, .*[^ago]/', ' ', $output );
							echo '<span class="bsp-activity-freshness bsp-la-freshness">'.$output. '</span>'; 
					endif; ?>
					
					<?php if ( ! bp_is_group()) : ?>
					<div class = "bsp-activity-forum">
						<?php
						$forum = bbp_get_topic_forum_id($topic_id);
						$forum1 = get_the_title($forum) ;
						$forum2 = esc_url( bbp_get_forum_permalink( $forum )) ;
					?>
						<a class="bsp-la-forum-title bbp-forum-title" href="<?php echo $forum2; ?>"><?php echo $forum1 ; ?></a>
					</div></div>
					<?php endif; ?>
				
				</li>

			<?php endwhile; ?>

		</ul>

		<?php echo $args['after_widget'];

		// Reset the $post global
		wp_reset_postdata();
	}

	/**
	 * Update the topic widget options
	 *
	 * @since bbPress (r2653)
	 *
	 * @param array $new_instance The new instance options
	 * @param array $old_instance The old instance options
	 */
	public function update( $new_instance = array(), $old_instance = array() ) {
		$instance                 = $old_instance;
		$instance['title']        = strip_tags( $new_instance['title'] );
		$instance['order_by']     = strip_tags( $new_instance['order_by'] );
		$instance['exclude_forum']     = (bool) $new_instance['exclude_forum'] ;
		$instance['excluded_forum']     = sanitize_text_field ($new_instance['excluded_forum'] );
		$instance['parent_forum'] = sanitize_text_field( $new_instance['parent_forum'] );
		$instance['show_freshness']    = (bool) $new_instance['show_freshness'];
		$instance['show_user']    = (bool) $new_instance['show_user'];
		$instance['show_forum']    = (bool) $new_instance['show_forum'];
		$instance['show_count']    = (bool) $new_instance['show_count'];
		$instance['reply_count_label']    = $new_instance['reply_count_label'];
		$instance['max_shown']    = (int) $new_instance['max_shown'];
		$instance['shorten_freshness']    = (int) $new_instance['shorten_freshness'];
		$instance['hide_avatar']    = (int) $new_instance['hide_avatar'];

		
		//strip spaces
		$instance['parent_forum'] = str_replace(' ', '', $instance['parent_forum']);
		//check that parent_forum only contains numbers or numbers separated by commas
		$re = '/^\d+(?:,\d+)*$/';
		if ( !preg_match($re, $instance['parent_forum']) ) {
    	$instance['parent_forum'] = 'any';
		}
		
		$instance['excluded_forum'] = str_replace(' ', '', $instance['excluded_forum']);
		//check that parent_forum only contains numbers or numbers separated by commas
		if ( !preg_match($re, $instance['excluded_forum']) ) {
    	$instance['excluded_forum'] = '';
		}
		
		return $instance;
	}

	/**
	 * Output the topic widget options form
	 *
	 * @since bbPress (r2653)
	 *
	 * @param $instance Instance
	 * @uses BBP_Topics_Widget::get_field_id() To output the field id
	 * @uses BBP_Topics_Widget::get_field_name() To output the field name
	 */
	public function form( $instance = array() ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance ); ?>
		
		<p><label for="<?php echo $this->get_field_id( 'title'     ); ?>"><?php _e( 'Title:',                  'bbp-style-pack' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title'     ); ?>" name="<?php echo $this->get_field_name( 'title'     ); ?>" type="text" value="<?php echo esc_attr( $settings['title']     ); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'max_shown' ); ?>"><?php _e( 'Maximum topics to show:', 'bbp-style-pack' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_shown' ); ?>" name="<?php echo $this->get_field_name( 'max_shown' ); ?>" type="text" value="<?php echo esc_attr( $settings['max_shown'] ); ?>" /></label></p>
		<hr>
		<p>
		<label for="<?php echo $this->get_field_id( 'exclude_forum' ); ?>"><input type="radio" id="<?php echo $this->get_field_id( 'exclude_forum' ); ?>" name="<?php echo $this->get_field_name( 'exclude_forum' ); ?>" <?php checked( false, $settings['exclude_forum'] ); ?> value="0" /></label>
			
			<label for="<?php echo $this->get_field_id( 'parent_forum' ); ?>"><?php _e( 'From Forum ID(s):', 'bbp-style-pack' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'parent_forum' ); ?>" name="<?php echo $this->get_field_name( 'parent_forum' ); ?>" type="text" value="<?php echo esc_attr( $settings['parent_forum'] ); ?>" />
			</label>

			<br />

			<small><?php _e( '"0" to show only root - "any" to show all - ', 'bbp-style-pack' ); ?></small>
			<small><br /><?php _e( 'a single forum eg "2921"  - or forums separated by commas eg "2921,2922"', 'bbp-style-pack' ); ?></small>
			<small><br /><?php _e( 'See dashboard>forums>all forums to find the ID of a forum', 'bbp-style-pack' ); ?></small>
			
		</p>
		<?php _e( 'OR', 'bbp-style-pack' ); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'exclude_forum' ); ?>"><input type="radio" id="<?php echo $this->get_field_id( 'exclude_forum' ); ?>" name="<?php echo $this->get_field_name( 'exclude_forum' ); ?>" <?php checked( true, $settings['exclude_forum'] ); ?> value="1" /></label>
			
			<label for="<?php echo $this->get_field_id( 'excluded_forum' ); ?>"><?php _e( 'Exclude Forum ID(s):', 'bbp-style-pack' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'excluded_forum' ); ?>" name="<?php echo $this->get_field_name( 'excluded_forum' ); ?>" type="text" value="<?php echo esc_attr( $settings['excluded_forum'] ); ?>" />
			</label>

			<br />

			<small><br /><?php _e( 'a single forum eg "2921"  - or forums separated by commas eg "2921,2922"', 'bbp-style-pack' ); ?></small>
						
		</p>
		<hr>
		<p><label for="<?php echo $this->get_field_id( 'show_freshness' ); ?>"><?php _e( 'Show Freshness:',    'bbp-style-pack' ); ?> <input type="checkbox" id="<?php echo $this->get_field_id( 'show_freshness' ); ?>" name="<?php echo $this->get_field_name( 'show_freshness' ); ?>" <?php checked( true, $settings['show_freshness'] ); ?> value="1" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'shorten_freshness' ); ?>"><?php _e( 'Shorten freshness:',    'bbp-style-pack' ); ?> <input type="checkbox" id="<?php echo $this->get_field_id( 'shorten_freshness' ); ?>" name="<?php echo $this->get_field_name( 'shorten_freshness' ); ?>" <?php checked( true, $settings['shorten_freshness'] ); ?> value="1" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'show_user' ); ?>"><?php _e( 'Show topic author:', 'bbp-style-pack' ); ?> <input type="checkbox" id="<?php echo $this->get_field_id( 'show_user' ); ?>" name="<?php echo $this->get_field_name( 'show_user' ); ?>" <?php checked( true, $settings['show_user'] ); ?> value="1" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'hide_avatar' ); ?>"><?php _e( 'Hide Avatar',    'bbp-style-pack' ); ?> <input type="checkbox" id="<?php echo $this->get_field_id( 'hide_avatar' ); ?>" name="<?php echo $this->get_field_name( 'hide_avatar' ); ?>" <?php checked( true, $settings['hide_avatar'] ); ?> value="1" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'show_forum' ); ?>"><?php _e( 'Show Forum:',    'bbp-style-pack' ); ?> <input type="checkbox" id="<?php echo $this->get_field_id( 'show_forum' ); ?>" name="<?php echo $this->get_field_name( 'show_forum' ); ?>" <?php checked( true, $settings['show_forum'] ); ?> value="1" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'show_count' ); ?>"><?php _e( 'Show reply count:',    'bbp-style-pack' ); ?> <input type="checkbox" id="<?php echo $this->get_field_id( 'show_count' ); ?>" name="<?php echo $this->get_field_name( 'show_count' ); ?>" <?php checked( true, $settings['show_count'] ); ?> value="1" /></label></p>
		<label for="<?php echo $this->get_field_id( 'reply_count_label' ); ?>"><?php _e( 'Reply Count Label:', 'bbp-style-pack' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'reply_count_label' ); ?>" name="<?php echo $this->get_field_name( 'reply_count_label' ); ?>" type="text" value="<?php echo $settings['reply_count_label']; ?>" />
			</label>
			<br />

			<small><?php _e( 'eg Replies:, No. Replies - etc', 'bbp-style-pack' ); ?></small>
		<p>
			<label for="<?php echo $this->get_field_id( 'order_by' ); ?>"><?php _e( 'Order By:',        'bbp-style-pack' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'order_by' ); ?>" id="<?php echo $this->get_field_name( 'order_by' ); ?>">
				<option <?php selected( $settings['order_by'], 'freshness' ); ?> value="freshness"><?php _e( 'Topics With Recent Replies', 'bbp-style-pack' ); ?></option>
				<option <?php selected( $settings['order_by'], 'newness' );   ?> value="newness"><?php _e( 'Newest Topics',                'bbp-style-pack' ); ?></option>
				<option <?php selected( $settings['order_by'], 'popular' );   ?> value="popular"><?php _e( 'Popular Topics',               'bbp-style-pack' ); ?></option>
				
			</select>
		</p>

		<?php
	}

	/**
	 * Merge the widget settings into defaults array.
	 *
	 * @since bbPress (r4802)
	 *
	 * @param $instance Instance
	 * @uses bbp_parse_args() To merge widget options into defaults
	 */
	public function parse_settings( $instance = array() ) {
		return bbp_parse_args( $instance, array(
			'title'        => __( 'Latest Activity', 'bbp-style-pack' ),
			'max_shown'    => 5,
			'show_date'    => false,
			'show_user'    => false,
			'exclude_forum' => false,
			'excluded_forum' => '',
			'parent_forum' => 'any',
			'show_freshness' => false,
			'shorten_freshness' => false,
			'hide_avatar' => false,
			'show_forum' => false,
			'show_count' => false,
			'reply_count_label' => false,
			'order_by'     => false
		), 'latest_activity_widget_settings' );
	}
} //end of latest activity widget

/**
 * For the User Dashboard
 */
function bfc_activity_widget_excerpt_length(){
	$excerpt_length = 150;
	return (int) $excerpt_length;
}



/**
 * A filter to simplify the output from bp_activity_action() in the Activty widget on the User Dashboard
 */

function bfc_simplify_activity_action ($action){
	$action = str_replace(' posted an update','<br>', $action );
	$action = 'From ' . $action;
	return $action;
}

add_filter( 'bp_get_activity_action_pre_meta', 'bfc_simplify_activity_action', 10, 1);

// Latest messages widget 
class bfc_messages_widget extends WP_Widget {
 
	// The construct part  
	function __construct() {
		parent::__construct(
			
		// Base ID of your widget
		'bfc_messages_widget', 
			
		// Widget name will appear in UI
		__('(BFC) Latest Messages', 'bfc_messages_widget_domain'), 
			
		// Widget description
		array( 'description' => __( 'A widget to display a user\'s latest private messages', 'bfc_messages_widget_domain' ), ) 
		);
	}
	  
	// Creating widget front-end
	public function widget( $args, $instance ) {
		global $messages_template;

		$is_follow_active = bp_is_active('activity') && function_exists('bp_is_activity_follow_active') && bp_is_activity_follow_active();
		$follow_class = $is_follow_active ? 'follow-active' : '';

		$title = apply_filters( 'widget_title', $instance['title'] );
  
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( ! empty( $title ) )
		echo $args['before_title'] . $title . $args['after_title'];
		
		// This is where you run the code and display the output
		echo '<ul class="bfc-la-ul">';

		$args = array(
			// 'user_id'      => $user_id,
			// 'box'          => $default_box,
			// 'per_page'     => 10,
			'max'          => 6,
			// 'type'         => 'all',
			// 'search_terms' => $search_terms,
			// 'include'      => false,
			// 'page_arg'     => 'mpage', // See https://buddypress.trac.wordpress.org/ticket/3679.
			// 'meta_query'   => array(),
			'after_widget' => '<div class="bfc-after-widget"></div>',
		);

		  
 
		
		if (bp_has_message_threads($args)) {
			while ( bp_message_threads() ) {
				bp_message_thread();
				// echo bp_get_message_thread_id();
				// bp_message_thread_view_link();
				// echo '<br>';
				// bp_message_thread_last_post_date();
				// echo date('M j, Y', bp_get_message_thread_last_post_date_raw() );
				$type = 'widget';
				$source = $messages_template->thread->last_sender_id;
				?>
				<div class="activity-list item-list">
					<div class="activity-update">
						<div class="update-item">
							<span data-toggle="widget-dropdown-<?php echo $source ; ?>">
								<?php bp_message_thread_avatar(array( 'type'   => 'thumb', 'width'  => '40', 'height' => '40' )); ?></span>
								<?php 
								echo bfc_avatar_dropdown ($type,$source,$follow_class);
								?>
							<div class="bp-activity-info">
								<p>From <?php bp_message_thread_from(); ?><br>
								<a href="<?php bp_message_thread_view_link(); ?>">
									<?php echo bfc_nice_date (date('M j, Y', strtotime ($messages_template->thread->last_message_date))); ?>
								</a>
								</p>
							</div>
						</div>
					</div>
					<div class="activity-content ">	
						<div class="activity-inner ">
							<?php echo bp_create_excerpt( stripslashes($messages_template->thread->last_message_content), 150 ); ?>
						</div>
					</div>
				</div>

			<?php }	
				
		}
						echo $args['after_widget']; ?>
		</div>
	<?php }
			  
	// Creating widget Backend 
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'bfc_messages_widget_domain' );
		}
		?>
		<!-- Widget admin form -->
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php			
	}
		  
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
	 
	// Class wpb_widget ends here
	} 

