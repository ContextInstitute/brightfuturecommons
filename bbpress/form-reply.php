<?php

/**
 * New/Edit Reply
 *
 * @package BuddyBoss
 * @subpackage Theme
 */

?>

<?php if ( bbp_is_reply_edit() ) : ?>

<div id="bbpress-forums">

	<?php bbp_breadcrumb(); ?>

<?php endif; ?>

<?php if ( bbp_current_user_can_access_create_reply_form() ) : ?>

	<?php do_action( 'bbp_theme_before_reply_form_notices' ); ?>

	<?php if ( ! bbp_is_reply_edit() && ! bbp_is_topic_open() ) : ?>

		<div class="bp-feedback info">
			<span class="bp-icon" aria-hidden="true"></span>
			<p><?php esc_html_e( 'This discussion is marked as closed to new replies, however your posting capabilities still allow you to do so.', 'bfcommons-theme' ); ?></p>
		</div>

	<?php endif; 
	$forum_link = esc_url( bbp_get_forum_permalink( bbp_get_topic_forum_id( bbp_get_topic_id() ) ));
	$group_link = substr($forum_link, 0, strpos($forum_link, "forum/")) . "forum/" ;
	$action = bbp_is_reply_edit() ? bbp_get_reply_edit_url() : $group_link ;
	?>

	<div id="new-reply-<?php bbp_topic_id(); ?>" class="bbp-reply-form"> <!-- <?php echo ( bbp_is_single_topic() ? 'bb-modal bb-modal-box' : '' ); ?> -->

		<form id="new-post" name="new-post" method="post" action="<?php echo $action ; ?>">

			<?php do_action( 'bbp_theme_before_reply_form' ); ?>

			<fieldset class="bbp-form">
				<!-- <legend>
					<?php esc_html_e( 'Reply to:', 'bfcommons-theme' ); ?> <span id="bbp-reply-to-user"><?php printf( '%s', bbp_get_topic_title() ); ?></span>
					<div id="bbp-reply-exerpt"></div>
				</legend> -->

				<div id="bbp-template-notices">
					<?php do_action( 'bbp_template_notices' ); ?>
				</div>

				<div>

					<?php bbp_get_template_part( 'form', 'anonymous' ); ?>

					<?php do_action( 'bbp_theme_before_reply_form_content' ); ?>

					<?php
						$before = current_user_can( 'delete_others_posts' ) ? '<div class="bbp-the-content-wrapper">' : '<div class="bbp-the-content-wrapper bfc-not-editor">';
						echo bfc_get_the_content( array( 'context' => 'reply', 'before' => $before ) ); 
					?>

					<?php do_action( 'bbp_theme_after_reply_form_content' ); ?>

					<?php if ( ! ( bbp_use_wp_editor() || current_user_can( 'unfiltered_html' ) ) ) : ?>

						<p class="form-allowed-tags">
							<label><?php _e( 'You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes:', 'bfcommons-theme' ); ?></label><br />
							<code><?php bbp_allowed_tags(); ?></code>
						</p>

					<?php endif; ?>

					<?php 
					// bbp_get_template_part( 'form', 'attachments' ); 
					?>

					<?php if ( bbp_allow_topic_tags() && current_user_can( 'assign_topic_tags' ) ) : ?>

						<?php do_action( 'bbp_theme_before_reply_form_tags' ); ?>

						<?php
						$get_topic_id = bbp_get_topic_id();
						$get_the_tags = isset( $get_topic_id ) && ! empty( $get_topic_id ) ? bbp_get_topic_tag_names( $get_topic_id ) : array();
						?>

						<p class="bbp_topic_tags_wrapper">
							<input type="hidden" value="" name="bbp_topic_tags" class="bbp_topic_tags" id="bbp_topic_tags" >
							<select name="bbp_topic_tags_dropdown[]" class="bbp_topic_tags_dropdown" id="bbp_topic_tags_dropdown" placeholder="<?php esc_html_e( 'Type one or more tag, comma separated', 'bfcommons-theme' ); ?>" autocomplete="off" multiple="multiple" style="width: 100%" tabindex="<?php bbp_tab_index(); ?>">
								<?php
								if ( ! empty( $get_the_tags ) ) {
									$get_the_tags = explode( ',', $get_the_tags );
									foreach ( $get_the_tags as $single_tag ) {
										$single_tag = trim( $single_tag );
										?>
										<option selected="selected" value="<?php echo esc_attr( $single_tag ); ?>"><?php echo esc_html( $single_tag ); ?></option>
										<?php
									}
								}
								?>
							</select>
						</p>

						<?php do_action( 'bbp_theme_after_reply_form_tags' ); ?>

					<?php endif; ?>

					<?php if ( bbp_allow_revisions() && bbp_is_reply_edit() ) : ?>

						<div class="bb-form_rev_wrapper flex">

							<?php do_action( 'bbp_theme_before_reply_form_revisions' ); ?>

							<!-- <fieldset class="bbp-form">
								<div class="flex">
									<legend>
										<input name="bbp_log_reply_edit" id="bbp_log_reply_edit" class="bs-styled-checkbox" type="checkbox" value="0" <?php bbp_form_reply_log_edit(); ?> tabindex="<?php bbp_tab_index(); ?>" />
										<label for="bbp_log_reply_edit"><?php esc_html_e( 'Keep a log of this edit:', 'bfcommons-theme' ); ?></label>
									</legend>

									<div>
										<label for="bbp_reply_edit_reason"><?php printf( __( 'Optional reason for editing:', 'bfcommons-theme' ), bbp_get_current_user_name() ); ?></label>
										<input type="text" value="<?php bbp_form_reply_edit_reason(); ?>" tabindex="<?php bbp_tab_index(); ?>" size="40" name="bbp_reply_edit_reason" id="bbp_reply_edit_reason" placeholder="<?php esc_html_e( 'Optional reason for editing', 'bfcommons-theme' ); ?>" />
									</div>
								</div>
							</fieldset> -->

							<?php do_action( 'bbp_theme_after_reply_form_revisions' ); ?>

						</div>

					<?php endif; ?>

					<div class="bb-form-select-fields flex">

						<?php if ( false && bbp_is_subscriptions_active() && ! bbp_is_anonymous() && ( ! bbp_is_reply_edit() || ( bbp_is_reply_edit() && ! bbp_is_reply_anonymous() ) ) ) : ?>

							<?php
							if (
								! function_exists( 'bb_enabled_legacy_email_preference' ) ||
								(
									function_exists( 'bb_enabled_legacy_email_preference' ) &&
									(
										bb_enabled_legacy_email_preference() ||
										( ! bb_enabled_legacy_email_preference() && bb_get_modern_notification_admin_settings_is_enabled( 'bb_forums_subscribed_reply' ) )
									)
								)
							) {
								?>

								<?php do_action( 'bbp_theme_before_reply_form_subscription' ); ?>

								<div class="bb_subscriptions_active">

									<input name="bbp_topic_subscription" id="bbp_topic_subscription" class="bs-styled-checkbox" type="checkbox" value="bbp_subscribe"<?php bbp_form_topic_subscribed(); ?> tabindex="<?php bbp_tab_index(); ?>" />

									<?php if ( bbp_is_reply_edit() && ( bbp_get_reply_author_id() !== bbp_get_current_user_id() ) ) : ?>

										<label for="bbp_topic_subscription"><?php esc_html_e( 'Notify the author of replies via email', 'bfcommons-theme' ); ?></label>

									<?php else : ?>

										<label for="bbp_topic_subscription"><?php esc_html_e( 'Notify me of replies via email', 'bfcommons-theme' ); ?></label>

									<?php endif; ?>

								</div>

								<?php do_action( 'bbp_theme_after_reply_form_subscription' ); ?>

						<?php } ?>

						<?php endif; ?>

						<?php do_action( 'bbp_theme_before_reply_form_submit_wrapper' ); ?>

						<div class="bbp-submit-wrapper">

							<?php do_action( 'bbp_theme_before_reply_form_submit_button' ); ?>

							<?php bbp_cancel_reply_to_link(); ?>

							<button type="submit" id="bbp_reply_submit" name="bbp_reply_submit" class="button submit"><?php esc_html_e( 'Post', 'bfcommons-theme' ); ?></button>

							<?php do_action( 'bbp_theme_after_reply_form_submit_button' ); ?>

						</div>

						<?php do_action( 'bbp_theme_after_reply_form_submit_wrapper' ); ?>

					</div>

				</div>

				<?php bbp_reply_form_fields(); ?>

			</fieldset>

			<?php do_action( 'bbp_theme_after_reply_form' ); ?>

		</form>
	</div>

<?php elseif ( bbp_is_topic_closed() ) : ?>

	<div id="no-reply-<?php bbp_topic_id(); ?>" class="bbp-no-reply">
		<div class="bp-feedback info">
			<span class="bp-icon" aria-hidden="true"></span>
			<p><?php printf( __( 'The discussion &#8216;%s&#8217; is closed to new replies.', 'bfcommons-theme' ), bbp_get_topic_title() ); ?></p>
		</div>
	</div>

<?php elseif ( bbp_is_forum_closed( bbp_get_topic_forum_id() ) ) : ?>

	<div id="no-reply-<?php bbp_topic_id(); ?>" class="bbp-no-reply">
		<div class="bp-feedback info">
			<span class="bp-icon" aria-hidden="true"></span>
			<p><?php printf( __( 'The forum &#8216;%s&#8217; is closed to new discussions and replies.', 'bfcommons-theme' ), bbp_get_forum_title( bbp_get_topic_forum_id() ) ); ?></p>
		</div>
	</div>

<?php else : ?>

<?php if ( is_user_logged_in() ) : ?>
		<div id="no-reply-<?php bbp_topic_id(); ?>" class="bbp-no-reply">
			<div class="bp-feedback info">
				<span class="bp-icon" aria-hidden="true"></span>
				<p><?php esc_html_e( 'You cannot reply to this discussion.', 'bfcommons-theme' ); ?></p>
			</div>
		</div>
	<?php endif; ?>
<?php endif; ?>

<?php if ( bbp_is_reply_edit() ) : ?>

</div>

<?php endif; ?>

<script>
var bfcCount = 0;
jQuery('.bbp-reply-form form button#bbp_reply_submit').click(function(event){
	bfcCount++;
	if (bfcCount>1) {
		event.preventDefault();
		event.stopImmediatePropagation();
	}
	jQuery('#bbp_reply_submit')
		.css({backgroundColor:'#fff', borderColor:'#c8cbcf', color: '#c8cbcf', cursor:'default'})
		.text('Please wait ...');
});
</script>
