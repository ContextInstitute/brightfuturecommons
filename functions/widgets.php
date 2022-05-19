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


function bfc_register_latest_activities_widget() {
	register_widget( 'bfc_latest_activities' );
}
add_action('widgets_init', 'bfc_register_latest_activities_widget');

//latest activity forum widget
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
		$hide_own_posts = false;
		if ($hide_own_posts) {
			$shown_topics = (int) $settings['max_shown'];
			$topics_query['posts_per_page'] = 3*$shown_topics;
		} else {
			$topics_query['posts_per_page'] = (int) $settings['max_shown'];
		}
		
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
		echo "<div class='bfc-widget-head'>";
		
		echo '<span class="bsp-la-title">' . $args['before_title'] . $settings['title'] . $args['after_title'] . '</span>' ;

		If (bp_current_component() == 'groups') {
			$forum_id = bbp_get_group_forum_ids (bp_get_current_group_id());
			$compose_link = esc_url( bbp_get_forum_permalink ($forum_id[0])); 
		?>
		<div class='bfc-write-new'><a href="<?php echo $compose_link ?>" class= ><span class= 'bb-icon-edit' ></span></a></div>
		<?php }
		
		$helptip = "<div class='bfc-helptip'><span class= 'bb-icon-help-circle' ></span><span class='bfc-helptiptext'>";
		If (bp_current_component() == 'groups') {
			if($hide_own_posts){
				$helptip .= "<p>These are the <strong>most recent forum posts by others</strong> from this group.</p>
				<p>Threads where your post is the most recent aren't included since you've seen them.</p>";
			} else {
				$helptip .= "<p>These are the <strong>most recent forum posts</strong> from this group.</p>";
			}
			$helptip .= "<p>To see the full post in its thread, click the <span class = 'bfc-widget-actions bb-icon-arrow-up-right'></span> symbol to the right of the sender's name.</p>			
			<p>Once you've gone to the full post, you can also <em>like</em> it and/or add a reply.</p>			
			<p>Clicking on the thread subject takes you to the start of the thread.</p>			
			<p>You can create a new thread by clicking on the <span class= 'bb-icon-edit' ></span> symbol to the left of the <span class= 'bb-icon-help-circle' ></span> symbol. This takes to the page that lists all of threads. Click the <em>New discussion</em> button to create your new thread.</p>			
			<p>Hover over the sender's picture for quick access to sending them a new message, following them or going to their profile.</p>			
			<p>Clicking on the sender's name also takes you to their profile.</p>";
		} else {
			if($hide_own_posts){
				$helptip .= "<p>These are the <strong>most recent forum posts by others</strong> from the <strong>groups</strong> you are part of.</p>
				<p>Threads where your post is the most recent aren't included since you've seen them.</p>";
			} else {
				$helptip .= "<p>These are the <strong>most recent forum posts</strong> from the <strong>groups</strong> you are part of.</p>";
			}
			$helptip .= "<p>To see the full post in its thread, click the <span class = 'bfc-widget-actions bb-icon-arrow-up-right'></span> symbol to the right of the sender's name.</p>
			<p>Once you've gone to the full post, you can also <em>like</em> it and/or add a reply.</p>			
			<p>Clicking on the thread subject takes you to the start of the thread.</p>			
			<p>Clicking on the group name in the lower right takes you to the group dashboard/homepage.</p>			
			<p>Hover over the sender's picture for quick access to sending them a new message, following them or going to their profile.</p>			
			<p>Clicking on the sender's name also takes you to their profile.</p>";
		}
		$helptip .= "</span></div></div>";
		echo $helptip;
		
		
		?>
		
		<ul class="bfc-la-ul">

			<?php 
			$visible_topics = 0;
			while ( $widget_query->have_posts() ) :
				

				$widget_query->the_post();
				$topic_id    = bbp_get_topic_id( $widget_query->post->ID );
				// $author_link = '';
				
				//check if this topic has a reply
				$reply = get_post_meta( $topic_id, '_bbp_last_reply_id',true);
				
				//if no reply the author
				if (empty ($reply)) {
					// $author_avatar = bbp_get_topic_author_link( array( 'post_id' => $topic_id, 'type' => 'avatar', 'size' => $avatar_size ) );
					$author_name = bbp_get_topic_author_link( array( 'post_id' => $topic_id, 'type' => 'name' ) );
					$author_id = bbp_get_topic_author_id ($topic_id);
				//if has a reply then get the author of the reply
				} else { 
					// $author_avatar = bbp_get_reply_author_link( array( 'post_id' => $reply, 'type' => 'avatar', 'size' => $avatar_size) );
					$author_name = bbp_get_reply_author_link( array( 'post_id' => $reply, 'type' => 'name') );
					$author_id = bbp_get_reply_author_id( $reply );
				} 
				
				// Create excerpt
				$post_id = empty ($reply)? $topic_id : $reply;
				$bfc_excerpt = wp_trim_words(bbp_get_reply_content($post_id), 20);
				?>
				<?php 
				global $bfc_dropdown_prefix;
				$type = $bfc_dropdown_prefix . '-forum';
				$person = bbp_get_reply_author_id($post_id);
				if($hide_own_posts && $person == bp_loggedin_user_id()) {continue;}
				?>

				<li class="bfc-la-li" data-bp-item-id="<?php echo $author_id; ?>" data-bp-item-component="members">
				<div class = "update-item">
					<span class="bfc-la-topic-author-avatar topic-author bfc-dropdown-span" data-toggle="<?php echo $type . '-dropdown-' . esc_attr( $post_id ); ?>"><?php bbp_reply_author_avatar( $post_id,  $size = 40 ); ?></span>
					<?php echo bfc_member_dropdown( $type, $post_id, $person, $follow_class );?>
					<div class="bfc-forum-links">
						<div class="bsp-la-reply-topic-title">
							<a href="<?php bbp_topic_permalink( $topic_id ); ?>"><?php bbp_topic_title( $topic_id ); ?></a><br>
							<?php 					
							echo '<span class="bfc-la-topic-author-name topic-author">' . $author_name . '</span>';
							
							if ( ! empty( $settings['show_freshness'] ) ) : ?>
							<?php 
							// $output = bbp_get_topic_last_active_time( $topic_id ) ; 
							$last_active = get_post_field( 'post_date_gmt', $post_id );
							$output = bfc_nice_date( strtotime( $last_active ) );
							echo '<span class="bs-separator">&middot;&nbsp;</span>';
							echo '<span class="bsp-activity-freshness bsp-la-freshness">'.$output. '</span>';
							endif; 
							?>
						</div>
						
					</div>
					
					<div class = "bfc-widget-actions">
						<a href="<?php echo esc_url( bbp_get_reply_url( $reply ) ); ?>" class = "bb-icon-arrow-up-right"></a>
					</div>


					<?php
					echo '</div><div><div class="bfc-la-topic-excerpt">' . $bfc_excerpt . '</div>';
					bfc_widget_like_state($post_id);
					?>
					
					<?php if ( ! bp_is_group()) : ?>
					<div class = "bsp-activity-forum">
						<?php
						$forum = bbp_get_topic_forum_id($topic_id);
						$forum1 = bfc_get_forum_title($forum) ;
						$forum2 = esc_url( bbp_get_forum_permalink( $forum )) ;
						$forum3 = substr($forum2, 0, strpos($forum2, "forum/"));
					?>
						<a class="bsp-la-forum-title bbp-forum-title" href="<?php echo $forum3; ?>"><?php echo $forum1 ; ?></a>

					</div>
					<?php endif; ?>
					</div>
				</li>

			<?php 
			if($hide_own_posts) {
				$visible_topics++;
				if ($visible_topics == $shown_topics) {break;}
			}
			endwhile; ?>

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
	global $activities_template;
	$action = str_replace(' posted an update','<br>', $action );
	$action = str_replace(' the group','', $action );

	if ($activities_template->activity->type == 'activity_update') {
		$action = 'From ' . $action;
	}
	If (bp_current_component() == 'groups') {
		$target = 'in <a href="' . bp_get_group_permalink() . '">' . bp_get_group_name() . '</a>';
		$action = str_replace($target,' ', $action );
	}
	return $action;
}

add_filter( 'bp_get_activity_action_pre_meta', 'bfc_simplify_activity_action', 10, 1);

function bfc_activity_info(){
	global $activities_template;
	
	$author = $activities_template->activity->action;
	$author = str_replace(' posted an update','<br>', $author );
	$author = str_replace(' the group','', $author );
	
	if ($activities_template->activity->type == 'activity_update') {
		$author = 'From ' . $author;
	}
	if (bp_current_component() == 'groups') {
		$target = 'in <a href="' . bp_get_group_permalink() . '">' . bp_get_group_name() . '</a>';
		$author = str_replace($target,' ', $author );
	// 	if(str_contains($author, 'joined')) {$author .= ' ';}
	// } elseif (str_contains($author, 'in <a href="')) {
	// 	$author .= ' ';
	}

	if(str_contains($author, '<br>')) {
		$author .= ' ';
	}else {
		$author .= '<br>';
	}

	if(str_contains($author, '<br> in ')) {
		$author .= '<span class="bs-separator">&middot;&nbsp;</span>';
	} 
	// Get the time since this activity was recorded.
	$date_recorded = strtotime( $activities_template->activity->date_recorded );

	$time_since = '';
	// Remove time since from single activity page.

	// Set up 'time-since' <span>.
	// $time_since = sprintf(
	// 	'<span class="time-since" data-livestamp="%1$s">%2$s</span>',
	// 	bp_core_get_iso8601_date( $activities_template->activity->date_recorded ),
	// 	$date_recorded
	// );

	$time_since = '<span class="time-since">' . bfc_nice_date($date_recorded) . '</span>';

	echo '<p>' . $author . $time_since . '</p>';
}

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
		echo "<div class='bfc-widget-head'>";

		if ( ! empty( $title ) )
		echo $args['before_title'] . $title . $args['after_title'];

		$compose_link = bp_loggedin_user_domain() . bp_get_messages_slug() . '/compose/';
		?>
		<div class='bfc-write-new'><a href="<?php echo $compose_link ?>" class= ><span class= 'bb-icon-edit' ></span></a></div>
		<?php

		$helptip = "<div class='bfc-helptip'><span class= 'bb-icon-help-circle' ></span><span class='bfc-helptiptext'>";
		$helptip .= "<p>These are your <strong>unread private messages</strong>.</p><p>To see the full message thread and reply to it, click the <span class = 'bfc-widget-actions bb-icon-arrow-up-right'></span> symbol to the right of the sender's name. This will also mark the message as read and take you to your full inbox where you can work with other messages and create new ones.</p>
		<p>You can create a new message by clicking on the <span class= 'bb-icon-edit' ></span> symbol to the left of the <span class= 'bb-icon-help-circle' ></span> symbol.</p>
		<p>You can also access your messages via the <span class = 'bb-icon-inbox-small' style='font-size:16px;'></span> symbol at the right of the top menu.</p>
		<p>Hover over the sender's picture for quick access to sending them a new message, following them or going to their profile.</p>
		<p>Clicking on the sender's name also takes you to their profile.</p>";
		$helptip .= "</span></div></div>";
		echo $helptip;
		
		// This is where you run the code and display the output
		echo '<ul class="bfc-la-ul">';

		$args = array(
			// 'user_id'      => $user_id,
			// 'box'          => $default_box,
			// 'per_page'     => 10,
			'max'          => 6,
			'type'         => 'all', // Values: 'all', 'read', 'unread'
			// 'search_terms' => $search_terms,
			// 'include'      => false,
			// 'page_arg'     => 'mpage', // See https://buddypress.trac.wordpress.org/ticket/3679.
			// 'meta_query'   => array(),
			'after_widget' => '<div class="bfc-after-widget"></div>',
		);

		$message_count = 0;
		
		if (bp_has_message_threads($args)) {
			while ( bp_message_threads() ) {
				bp_message_thread();
				if (bp_message_thread_has_unread()) {

					$message_count++;
					global $bfc_dropdown_prefix;
					$type = $bfc_dropdown_prefix . '-message';
					$instance_id = $messages_template->thread->thread_id;
					$person = $messages_template->thread->last_sender_id;
					?>
					<div class="activity-list item-list">
						<div class="activity-update">
							<div class="update-item" data-bp-item-id="<?php echo $person; ?>" data-bp-item-component="members">
								<span class="bfc-dropdown-span" data-toggle="<?php echo $type . '-dropdown-' . $instance_id ; ?>">
									<?php bp_message_thread_avatar(array( 'type'   => 'thumb', 'width'  => '40', 'height' => '40' )); ?>
								</span>
								<?php echo bfc_member_dropdown( $type, $instance_id, $person, $follow_class ); ?>
								
								<div class="bp-activity-info">
									<p>From <?php bp_message_thread_from(); ?><br>
									<?php echo bfc_nice_date (strtotime (bp_get_message_thread_last_post_date_raw()));?>
									</p>
								</div>
								<div class = "bfc-widget-actions">
									<p>
										<a href="<?php bp_message_thread_view_link(); ?>" class = "bb-icon-arrow-up-right"></a>
									</p>
								</div>
							</div>
						</div>
						<div class="activity-content ">	
							<div class="activity-inner ">
								<?php echo wp_trim_words(stripslashes($messages_template->thread->last_message_content), 20);?>
							</div>
						</div>
					</div>
				<?php }
			}					
		}
		if ($message_count == 0) {
			echo "<p class='bfc-no-unread'>You have no unread messages.</p>";
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
	 
	
} // Class bfc_messages_widget ends here

/**
 * BFC Activity widget based on
 * BP Nouveau Activity widgets
 *
 * @since BuddyPress 3.0.0
 * @version 3.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * A widget to display the latest activities of your community!
 *
 * @since BuddyPress 3.0.0
 */
class bfc_latest_activities extends WP_Widget {
	/**
	 * Construct the widget.
	 *
	 * @since BuddyPress 3.0.0
	 */
	public function __construct() {

		/**
		 * Filters the widget options for the bfc_latest_activities widget.
		 *
		 * @since BuddyPress 3.0.0
		 *
		 * @param array $value Array of widget options.
		 */
		$widget_ops = apply_filters(
			'bfc_latest_activities', array(
				'classname'                   => 'bfc-latest-activities buddypress',
				'description'                 => __( 'Select to display the latest activity updates, by type, posted within your community.', 'buddyboss' ),
				'customize_selective_refresh' => true,
			)
		);

		parent::__construct( false, __( '(BFC) Latest Activities', 'buddyboss' ), $widget_ops );
	}


	/**
	 * Display the widget content.
	 *
	 * @since BuddyPress 3.0.0
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Widget settings, as saved by the user.
	 */
	public function widget( $args, $instance ) {
		// Default values
		$title      = __( 'Updates', 'buddyboss' );
		$type       = 'activity_update';
		$max        = 15;
		$bp_nouveau = bp_nouveau();

		// Check instance for a custom title
		if ( ! empty( $instance['title'] ) ) {
			$title = $instance['title'];
		}

		/**
		 * Filters the bfc_latest_activities widget title.
		 *
		 * @since BuddyPress 3.0.0
		 *
		 * @param string $title    The widget title.
		 * @param array  $instance The settings for the particular instance of the widget.
		 * @param string $id_base  Root ID for all widgets of this type.
		 */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		// Check instance for custom max number of activities to display
		if ( ! empty( $instance['max'] ) ) {
			$max = (int) $instance['max'];
		}

		// Check instance for custom activity types
		if ( ! empty( $instance['type'] ) ) {
			$type    = maybe_unserialize( $instance['type'] );
			if ( ! is_array( $type ) ) {
				$type = (array) maybe_unserialize( $type );
			}
			$classes = array_map( 'sanitize_html_class', array_merge( $type, array( 'bfc-latest-activities' ) ) );

			// Add classes to the container
			$args['before_widget'] = str_replace( 'bfc-latest-activities', join( ' ', $classes ), $args['before_widget'] );
		}

		echo $args['before_widget'];
		echo "<div class='bfc-widget-head'>";

		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		If (bp_current_component() == 'groups') {
			$compose_link = bp_get_group_permalink() . 'activity/';
		} else {
			$compose_link = bp_loggedin_user_domain() . 'activity/';
		}
		
		?>
		<div class='bfc-write-new'><a href="<?php echo $compose_link ?>" ><span class= 'bb-icon-edit' ></span></a></div>
		<?php
		
		$helptip = "<div class='bfc-helptip'><span class= 'bb-icon-help-circle' ></span><span class='bfc-helptiptext'>";
		If (bp_current_component() == 'groups') {
			$helptip .= "<p>These are <strong>update messages</strong> from and for this group.</strong></p>
			<p>To see the full message plus any comments, click the <span class = 'bfc-widget-actions bb-icon-arrow-up-right'></span> symbol to the right of the sender's name.</p>		
			<p>Once you've gone to the full message, you can also <em>like</em> it and/or add your comment.</p>
			<p>You can create a new update by clicking on the <span class= 'bb-icon-edit' ></span> symbol to the left of the <span class= 'bb-icon-help-circle' ></span> symbol.</p>					
			<p>You can access the group's full activity feed in its <a href='";
			$helptip .= bp_get_group_permalink();
			$helptip .= "activity/'>Timeline section</a>.</p>		
			<p>Hover over the person's picture for quick access to sending them a new message, following them or going to their profile.</p>		
			<p>Clicking on the person's name also takes you to their profile.</p>";
		} else {
			$helptip .= "<p>These are <strong>update messages</strong> from <strong>people you are following, groups you are part of and the one's you've sent.</strong></p>
			<p>To see the full message plus any comments, click the <span class = 'bfc-widget-actions bb-icon-arrow-up-right'></span> symbol to the right of the sender's name.</p>		
			<p>Once you've gone to the full message, you can also <em>like</em> it and/or add your comment.</p>			
			<p>You can create a new update by clicking on the <span class= 'bb-icon-edit' ></span> symbol to the left of the <span class= 'bb-icon-help-circle' ></span> symbol.</p>
			<p>If it's an update from a group, clicking on the group name take you to the group's home page.</p>		
			<p>You can access your full activity feed in the <a href='/members/";
			$helptip .= bp_core_get_username( bp_loggedin_user_id() );
			$helptip .= "/activity'>Timeline section</a> of your Profile.</p>		
			<p>Hover over the sender's picture for quick access to sending them a new message, following them or going to their profile.</p>		
			<p>Clicking on the sender's name also takes you to their profile.</p>";
		}
		$helptip .= "</span></div></div>";
		echo $helptip;

		$reset_activities_template = null;
		if ( ! empty( $GLOBALS['activities_template'] ) ) {
			$reset_activities_template = $GLOBALS['activities_template'];
		}

		/**
		 * Globalize the activity widget arguments.
		 * @see bp_nouveau_activity_widget_query() to override
		 */

		$followers = bp_get_following( array ('user_id' => bp_loggedin_user_id()) );
		$followers[] = bp_loggedin_user_id();
		$followers_and_me = implode(',', (array)$followers); 

		$bp_nouveau->activity->widget_args = array(
			'max'          => 10,
			'scope'        => 'all',
			'user_id'      => $followers_and_me,
			'object'       => false,
			'action'       => 'activity_update', //join( ',', $type ),
			'primary_id'   =>  0, //bp_get_current_group_id(),
			'secondary_id' => 0,
		);

		If (bp_current_component() == 'groups') {
			$group_members_result = groups_get_group_members( array( 'group_id' => bp_get_group_id(), 'exclude_admins_mods' => false) );
			$group_members = array();

			foreach(  $group_members_result['members'] as $member ) {
				$group_members[] = $member->ID;
			}
			
			$bp_nouveau->activity->widget_args = array(
				'max'          => $max,
				'scope'        => 'groups',
				'user_id'      => implode(',', $group_members),
				'object'       => 'groups',
				'action'       => 'activity_update,joined_group',//join( ',', $type ),
				'primary_id'   => bp_get_current_group_id(),
				'secondary_id' => 0,
			);
		}

		bp_get_template_part( 'activity/widget' );

		// Reset the globals
		$GLOBALS['activities_template']    = $reset_activities_template;
		$bp_nouveau->activity->widget_args = array();

		echo $args['after_widget'];
	}

	/**
	 * Update the widget settings.
	 *
	 * @since BuddyPress 3.0.0
	 *
	 * @param array $new_instance The new instance settings.
	 * @param array $old_instance The old instance settings.
	 *
	 * @return array The widget settings.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['max']   = 5;
		if ( ! empty( $new_instance['max'] ) ) {
			$instance['max'] = $new_instance['max'];
		}

		$instance['type'] = maybe_serialize( array( 'activity_update' ) );
		if ( ! empty( $new_instance['type'] ) ) {
			$instance['type'] = maybe_serialize( $new_instance['type'] );
		}

		return $instance;
	}

	/**
	 * Display the form to set the widget settings.
	 *
	 * @since BuddyPress 3.0.0
	 *
	 * @param array $instance Settings for this widget.
	 *
	 * @return string HTML output.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
			'title' => __( 'Updates', 'buddyboss' ),
			'max'   => 5,
			'type'  => '',
		) );

		$title = esc_attr( $instance['title'] );
		$max   = (int) $instance['max'];

		$type = array( 'activity_update' );
		if ( ! empty( $instance['type'] ) ) {
			$type = maybe_unserialize( $instance['type'] );
			if ( ! is_array( $type ) ) {
				$type = (array) maybe_unserialize( $type );
			}
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'buddyboss' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'max' ); ?>"><?php _e( 'Maximum amount to display:', 'buddyboss' ); ?></label>
			<input type="number" class="widefat" id="<?php echo $this->get_field_id( 'max' ); ?>" name="<?php echo $this->get_field_name( 'max' ); ?>" value="<?php echo intval( $max ); ?>" step="1" min="1" max="20" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'type' ); ?>"><?php esc_html_e( 'Activity Type:', 'buddyboss' ); ?></label>
			<select class="widefat" multiple="multiple" id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>[]">
				<?php foreach ( bp_nouveau_get_activity_filters() as $key => $name ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( in_array( $key, $type, true ) ); ?>><?php echo esc_html( $name ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}
} // Class bfc_latest_activities ends here

