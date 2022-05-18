<?php

/**
 * Single Topic Content Part
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<div id="bbpress-forums" class="bb-content-area bs-replies-wrapper"><!-- bfc-marker content-single-topic.php -->
	<div class="bb-grid">

		<div class="replies-content"><!-- bfc-marker content-single-topic.php -->
			<?php bbp_breadcrumb(); ?>

			<?php do_action( 'bbp_template_before_single_topic' ); ?>

			<?php if ( post_password_required() ) : ?>

				<?php bbp_get_template_part( 'form', 'protected' ); ?>

			<?php else : ?>

				<?php bbp_topic_tag_list(); ?>

				<?php if ( bbp_show_lead_topic() ) : ?>

					<?php bbp_get_template_part( 'content', 'single-topic-lead' ); ?>

				<?php endif; ?>

				<?php if ( bbp_has_replies() ) : ?>

					<?php bbp_get_template_part( 'pagination', 'replies' ); ?>

					<?php bbp_get_template_part( 'loop', 'replies' ); ?>

					<?php bbp_get_template_part( 'pagination', 'replies' ); ?>

				<?php endif; ?>

				<p class="bb-topic-reply-link-wrap mobile-only">
					<?php
					bbp_topic_reply_link();
					if ( ! bbp_current_user_can_access_create_reply_form() && ! bbp_is_topic_closed() && ! bbp_is_forum_closed( bbp_get_topic_forum_id() ) && ! is_user_logged_in() ) {
						?>
						<a href="<?php echo esc_url( wp_login_url() ); ?>" class="bbp-topic-login-link bb-style-primary-bgr-color bb-style-border-radius"><?php esc_html_e( 'Log In to Reply', 'buddyboss-theme' ); ?></a>
					<?php } ?>
				</p>
				<p class="bb-topic-subscription-link-wrap mobile-only">
					<?php
					$args = array( 'before' => '' );
					echo bbp_get_topic_subscription_link( $args );
					?>
				</p>

				<?php bbp_get_template_part( 'form', 'reply' ); ?>

			<?php endif; ?>

			<?php do_action( 'bbp_template_after_single_topic' ); ?>

		</div>
	</div>
		<!-- <div class="bb-sm-grid bs-single-topic-sidebar">
            <div class="bs-topic-sidebar-inner"> -->
				<div class="single-topic-sidebar-links">
					<!--<p class="bb-topic-reply-link-wrap">
						<?php
						bbp_topic_reply_link();
						if ( ! bbp_current_user_can_access_create_reply_form() && ! bbp_is_topic_closed() && ! bbp_is_forum_closed( bbp_get_topic_forum_id() ) && ! is_user_logged_in() ) {
							?>
							<a href="<?php echo esc_url( wp_login_url() ); ?>" class="bbp-topic-login-link bb-style-primary-bgr-color bb-style-border-radius"><?php esc_html_e( 'Log In to Reply', 'buddyboss-theme' ); ?></a>
						<?php } ?>
					</p> -->
					<p class="bb-topic-subscription-link-wrap">
					<?php
					// $args = array( 'before' => '' );
					// echo bbp_get_topic_subscription_link( $args );
					?>
					</p>
				</div>

				<?php
				$bbp       = bbpress();
				$start_num = intval( ( $bbp->reply_query->paged - 1 ) * $bbp->reply_query->posts_per_page ) + 1;
				$from_num  = bbp_number_format( $start_num );
				$to_num    = bbp_number_format( ( $start_num + ( $bbp->reply_query->posts_per_page - 1 ) > $bbp->reply_query->found_posts ) ? $bbp->reply_query->found_posts : $start_num + ( $bbp->reply_query->posts_per_page - 1 ) );
				?>

               <!--  BB code removed - scrubber -->
           <!--   </div>
		</div>  -->
</div>
