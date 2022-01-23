<?php

/**
 * Replies Loop
 *
 * @package    bbPress
 * @subpackage Theme
 */

?>

<?php do_action( 'bbp_template_before_replies_loop' ); ?>

<ul id="topic-<?php bbp_topic_id(); ?>-replies"
    class="bs-item-list bs-forums-items bs-single-forum-list bb-single-reply-list list-view"><!-- bfc-marker loop-replies.php -->

	<?php
	if ( ! empty( bbp_get_topic_id() ) ) {
		?>
        <li class="bs-item-wrap bs-header-item align-items-center no-hover-effect">

            <div class="item flex-1">
                <div class="item-title">
                    <h1 class="bb-reply-topic-title"><?php echo bbp_get_reply_topic_title( bbp_get_reply_id() ); ?></h1>

					<?php if ( ! bbp_show_lead_topic() && is_user_logged_in() ) : ?>
                        <div class="bb-topic-states push-right">
							<?php
							/**
							 * Checked bbp_get_topic_close_link() is empty or not
							 */
							if ( ! empty( bbp_get_topic_close_link() ) ) { ?>
								<?php if ( bbp_is_topic_open() ) { ?>
                                    <span data-balloon-pos="up"
                                          data-balloon="<?php _e( 'Close', 'buddyboss-theme' ); ?>"><i
                                                class="bb-topic-status open"><?php echo bbp_get_topic_close_link(); ?></i></span>
								<?php } else { ?>
                                    <span data-balloon-pos="up"
                                          data-balloon="<?php _e( 'Open', 'buddyboss-theme' ); ?>"><i
                                                class="bb-topic-status closed"><?php echo bbp_get_topic_close_link(); ?></i></span>
								<?php } ?>
							<?php } ?>
							<?php
							/**
							 * Checked bbp_get_topic_stick_link() is empty or not
							 */
							if ( ! bbp_is_topic_super_sticky( bbp_get_topic_id() ) && ! empty( bbp_get_topic_stick_link() ) ) {
								if ( bbp_is_topic_sticky() ) { ?>
                                <span data-balloon-pos="up" data-balloon="<?php _e( 'Unstick', 'buddyboss-theme' ); ?>">
                                    <i class="bb-topic-status bb-sticky sticky"><?php echo bbp_get_topic_stick_link(); ?></i>
                                    </span><?php
								} else { ?>
                                <span data-balloon-pos="up" data-balloon="<?php _e( 'Sticky', 'buddyboss-theme' ); ?>">
                                    <i class="bb-topic-status bb-sticky unsticky"><?php echo bbp_get_topic_stick_link(); ?></i>
                                    </span><?php
								}
							}

							/**
							 * Checked bbp_get_topic_stick_link() is empty or not
							 */

							if ( ! empty( bbp_get_topic_stick_link() ) ) {
								if ( bbp_is_topic_super_sticky( bbp_get_topic_id() ) ) { ?>
                                <span data-balloon-pos="up" data-balloon="<?php _e( 'Unstick', 'buddyboss-theme' ); ?>">
                                    <i
                                            class="bb-topic-status bb-super-sticky super-sticky"><?php echo bbp_get_topic_stick_link(); ?></i>
                                    </span><?php
								} elseif ( ( ! bp_is_group() && ! bp_is_group_forum_topic() ) && ! bbp_is_topic_sticky() ) { ?>
                                <span data-balloon-pos="up"
                                      data-balloon="<?php _e( 'Super Sticky', 'buddyboss-theme' ); ?>"><i
                                            class="bb-topic-status bb-super-sticky super-sticky unsticky"><?php echo bbp_get_topic_stick_link(); ?></i>
                                    </span><?php
								}
							}
							?>

							<?php
							if ( bbp_is_favorites_active() ) {
								$is_fav = bbp_is_user_favorite( get_current_user_id(), bbp_get_topic_id() );
								if ( $is_fav ) { ?>
                                <span class="bb-favorite-wrap" data-balloon-pos="up"
                                      data-balloon="<?php _e( 'Unfavorite', 'buddyboss-theme' ); ?>"
                                      data-unfav="<?php _e( 'Unfavorite', 'buddyboss-theme' ); ?>"
                                      data-fav="<?php _e( 'Favorite', 'buddyboss-theme' ); ?>"><i
                                            class="bb-topic-status bb-favorite-status favorited"><?php bbp_user_favorites_link(); ?></i>
                                    </span><?php
								} else { ?>
                                <span class="bb-favorite-wrap" data-balloon-pos="up"
                                      data-balloon="<?php _e( 'Favorite', 'buddyboss-theme' ); ?>"
                                      data-unfav="<?php _e( 'Unfavorite', 'buddyboss-theme' ); ?>"
                                      data-fav="<?php _e( 'Favorite', 'buddyboss-theme' ); ?>"><i
                                            class="bb-topic-status bb-favorite-status unfavorited"><?php bbp_user_favorites_link(); ?></i>
                                    </span><?php
								}
							}
							?>

	                        <?php if ( function_exists( 'bp_is_active' ) && bp_is_active( 'moderation' ) && function_exists( 'bbp_get_topic_report_link' ) && bbp_get_topic_report_link( array( 'id' => get_the_ID() ) ) ) { ?>
                                <div class="forum_single_action_wrap">
									<span class="forum_single_action_more-wrap" data-balloon-pos="up"
                                          data-balloon="<?php _e( 'More Options', 'buddyboss-theme' ); ?>">
										<i class="bb-icon bb-icon-menu-dots-v"></i>
									</span>
                                    <div class="forum_single_action_options">
				                        <?php
				                        if ( bp_is_active( 'moderation' ) && function_exists( 'bbp_get_topic_report_link' ) ) {
					                        ?>
                                            <p class="bb-topic-report-link-wrap">
						                        <?php echo bbp_get_topic_report_link( array( 'id' => get_the_ID() ) ); ?>
                                            </p>
					                        <?php
				                        }
				                        ?>
                                    </div>
                                </div><!-- .forum_single_action_wrap -->
	                        <?php } ?>
                        </div>
					<?php endif; ?>
                </div>

                <!-- BB Theme code removed - item-meta block -->
				<?php
				$terms = bbp_get_form_topic_tags();
				if ( $terms && bbp_allow_topic_tags() ) {
					$tags_arr = explode( ', ', $terms );
					$html     = '';
					foreach ( $tags_arr as $tag ) {
						$html .= '<li><a href="' . bbp_get_topic_tag_link( $tag ) . '">' . $tag . '</a></li>';
					}
					?>
                    <div class="item-tags">
                        <i class="bb-icon-tag"></i>
                        <ul>
							<?php echo rtrim( $html, ',' ); ?>
                        </ul>
                    </div>
					<?php
				} else {
					?>
                    <div class="item-tags" style="display: none;">
                        <i class="bb-icon-tag"></i>
                    </div>
					<?php
				}
				remove_filter( 'bbp_get_reply_content', 'bbp_reply_content_append_revisions', 99, 2 );
				?>
				<input type="hidden" name="bbp_topic_excerpt" id="bbp_topic_excerpt" value="<?php echo bbp_reply_excerpt( bbp_get_topic_id(), 50 ); ?>"/>
				<?php
				add_filter( 'bbp_get_reply_content', 'bbp_reply_content_append_revisions', 99, 2 );
				?>
            </div>
        </li><!-- .bbp-header -->
		<?php
	}

	if ( bbp_thread_replies() ) : ?>
		<?php bbp_list_replies(); ?>
	<?php else : ?>
		<?php while ( bbp_replies() ) : bbp_the_reply(); ?>
            <li><?php bbp_get_template_part( 'loop', 'single-reply' ); ?></li>
		<?php endwhile; ?>
	<?php endif; ?>

</ul><!-- #topic-<?php bbp_topic_id(); ?>-replies -->

<script> 
	jQuery(document).foundation();
</script>
 
<?php do_action( 'bbp_template_after_replies_loop' ); ?>
