<li>
	<?php $class = bbp_is_topic_open() ? '' : 'closed'; ?>
	<div class="bs-item-wrap <?php echo esc_attr( $class ); ?>">
		<div class="flex flex-1">
			<div class="item-avatar bb-item-avatar-wrap">
				<?php 
				$is_follow_active = bp_is_active('activity') && function_exists('bp_is_activity_follow_active') && bp_is_activity_follow_active();
				$follow_class = $is_follow_active ? 'follow-active' : '';
				$type = 'forumlist';
				$person = bbp_get_topic_author_id();
				$topic_id = bbp_get_topic_id();
				?>
				<div class="bfc-la-topic-author-avatar topic-author">
					<span class="bfc-dropdown-span" data-toggle="<?php echo $type . '-dropdown-' . esc_attr( $topic_id ); ?>"><?php bbp_topic_author_avatar( $topic_id,  $size = 40 ); ?></span><br>
					<?php echo bfc_member_dropdown( $type, $topic_id, $person, $follow_class );?>
				</div>


				<?php if ( ! bbp_is_topic_open() ) { ?>
					<i data-balloon-pos="up" data-balloon="<?php esc_attr_e( 'Closed', 'buddyboss-theme' ); ?>" class="bb-topic-status closed"></i>
					<?php
				}

				if ( bbp_is_topic_super_sticky() ) {
					?>
					<i data-balloon-pos="up" data-balloon="<?php esc_attr_e( 'Super Sticky', 'buddyboss-theme' ); ?>" class="bb-topic-status super-sticky"></i>
				<?php } elseif ( bbp_is_topic_sticky() ) { ?>
					<i data-balloon-pos="up" data-balloon="<?php esc_attr_e( 'Sticky', 'buddyboss-theme' ); ?>" class="bb-topic-status sticky"></i>
					<?php
				}

				if ( is_user_logged_in() ) {
					$is_subscribed = bbp_is_user_subscribed_to_topic( get_current_user_id(), bbp_get_topic_id() );
					if ( $is_subscribed ) {
						?>
						<i data-balloon-pos="up" data-balloon="<?php esc_attr_e( 'Subscribed', 'buddyboss-theme' ); ?>" class="bb-topic-status subscribed"></i>
						<?php
					}
				}
				?>
			</div>

			<div class="item">
				<div class="item-title">
					<a class="bbp-topic-permalink bfc-item-title" href="<?php bbp_topic_permalink(); ?>"><?php bbp_topic_title(); ?></a>
					<span class="bs-voices-wrap bfc-item-meta bb-reply-meta">
						<?php
							$voice_count = bbp_get_topic_voice_count( bbp_get_topic_id() );
							$voice_text  = $voice_count > 1 ? __( 'Members', 'buddyboss-theme' ) : __( 'Member', 'buddyboss-theme' );

							$topic_reply_count = bbp_get_topic_reply_count( bbp_get_topic_id() );
							$topic_post_count  = bbp_get_topic_post_count( bbp_get_topic_id() );
							$topic_reply_text  = '';
						?>
						<span class="bs-voices"><?php bbp_topic_voice_count(); ?> <?php echo wp_kses_post( $voice_text ); ?></span>
						<span class="bs-separator">&middot;</span>
						<span class="bs-replies">
						<?php
						if ( bbp_show_lead_topic() ) {
							bbp_topic_reply_count();
							$topic_reply_text = $topic_reply_count > 1 ? __( 'Replies', 'buddyboss-theme' ) : __( 'Reply', 'buddyboss-theme' );
						} else {
							bbp_topic_post_count();
							$topic_reply_text = $topic_post_count > 1 ? __( 'Posts', 'buddyboss-theme' ) : __( 'Post', 'buddyboss-theme' );
						}
						echo ' ' . wp_kses_post( $topic_reply_text );
						?>
						</span>
					</span>
				</div>

				<div class="item-meta bb-reply-meta">
					<!-- <i class="bb-icon-reply"></i> -->
					<div>
						<span class="bs-voices-wrap bs-replied">
							<?php 
							esc_html_e( 'Started by ', 'buddyboss-theme' );
							bbp_topic_author_link(array('type' => 'name'));
							?>
						</span>

						<?php if( $topic_post_count > 1 ) : ?>
							<span class="bs-replied">
								<span class="bbp-topic-freshness-author">
								<?php
								echo  ' - Latest reply by';
								bbp_author_link( array( 'post_id' => bbp_get_topic_last_active_id(), 'type'    => 'name', ) );
								?>
								</span> 
							</span>
						<?php endif; ?>
						<span class="bs-voices-wrap bs-replied">
						<a href="<?php echo esc_url( bbp_get_reply_url(bbp_get_topic_last_active_id())); ?>">
						<?php echo bbp_get_topic_last_active_time( $topic_id );?>
						<span class = "bfc-forumlist bb-icon-arrow-up-right"></span></a></span>

					</div>
				</div>
			</div>
		</div>

		<?php
		if ( ! empty( bbp_get_topic_forum_title() ) ) {

			$group_ids   = bbp_get_forum_group_ids( bbp_get_topic_forum_id() );
			$group_id    = ( ! empty( $group_ids ) ? current( $group_ids ) : 0 );
			$topic_title = ( function_exists( 'bp_is_active' ) && bp_is_active( 'groups' ) && $group_id ) ? bp_get_group_name( groups_get_group( $group_id ) ) : bbp_get_topic_forum_title();

			?>
			<div class="action bs-forums-meta flex align-items-center">
				<span class="color bs-meta-item forum-label <?php echo bbp_is_single_forum() ? esc_attr( 'no-action' ) : ''; ?>" style="background: <?php echo esc_attr( color2rgba( textToColor( bbp_get_topic_forum_title() ), 0.6 ) ); ?>">
					<?php
					if ( bbp_is_single_forum() ) {
						?>
						<span class="no-links forum-label__is-single"><?php echo esc_html( $topic_title ); ?></span>
						<?php
					} else {
						?>
						<a href="<?php echo esc_url( bbp_get_forum_permalink( bbp_get_topic_forum_id() ) ); ?>"><?php echo esc_html( $topic_title ); ?></a>
						<?php
					}
					?>
				</span>
			</div>
		<?php } ?>
	</div>
</li>
