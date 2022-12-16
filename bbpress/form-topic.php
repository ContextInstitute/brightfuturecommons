<?php

/**
 * New/Edit Topic
 *
 * @package BuddyBoss\Theme
 */

?>

<?php if ( ! bbp_is_single_forum() ) : ?>

<div id="bbpress-forums">

	<?php bbp_breadcrumb(); ?>

<?php endif; ?>

<?php if ( bbp_is_topic_edit() ) : ?>

	<?php bbp_topic_tag_list( bbp_get_topic_id() ); ?>

<?php endif; ?>

<?php if ( bbp_current_user_can_access_create_topic_form() ) : 
	$forum_link = esc_url( bbp_get_forum_permalink( bbp_get_topic_forum_id( bbp_get_topic_id() ) ));
	$group_link = substr($forum_link, 0, strpos($forum_link, "forum/")) . "forum/" ;
	$action = bbp_is_topic_edit() ? bbp_get_topic_edit_url() : $group_link ;
?>

	<div id="new-topic-<?php bbp_topic_id(); ?>" class="bbp-topic-form">

		<form id="new-post" name="new-post" method="post" action="<?php $action; ?>">

			<?php do_action( 'bbp_theme_before_topic_form' ); ?>

			<fieldset class="bbp-form">
				<legend>

					<?php
					if ( bbp_is_topic_edit() ) {
						printf( __( 'Now Editing &ldquo;%s&rdquo;', 'bfcommons-theme' ), bbp_get_topic_title() );
					} else {
						 _e( 'Start A New Thread', 'bfcommons-theme' );
					}
					?>

				</legend>

				<?php do_action( 'bbp_theme_before_topic_form_notices' ); ?>

				<?php if ( ! bbp_is_topic_edit() && bbp_is_forum_closed() ) : ?>

					<div class="bp-feedback info">
						<span class="bp-icon" aria-hidden="true"></span>
						<p><?php _e( 'This forum is marked as closed to new threads, however your posting capabilities still allow you to do so.', 'bfcommons-theme' ); ?></p>
					</div>

				<?php endif; ?>

				<?php do_action( 'bbp_template_notices' ); ?>

				<div>

					<?php bbp_get_template_part( 'form', 'anonymous' ); ?>

					<?php do_action( 'bbp_theme_before_topic_form_title' ); ?>

					<p>
						<label for="bbp_topic_title"><?php _e( 'Thread Title', 'bfcommons-theme' ); ?></label><br />
						<input type="text" id="bbp_topic_title" value="<?php bbp_form_topic_title(); ?>" tabindex="<?php bbp_tab_index(); ?>" size="40" name="bbp_topic_title" maxlength="<?php bbp_title_max_length(); ?>" />
					</p>

					<?php do_action( 'bbp_theme_after_topic_form_title' ); ?>

					<?php do_action( 'bbp_theme_before_topic_form_content' ); ?>

					<?php 
						$before = current_user_can( 'delete_others_posts' ) ? '<div class="bbp-the-content-wrapper">' : '<div class="bbp-the-content-wrapper bfc-not-editor">';
						echo bfc_get_the_content( array( 'context' => 'topic', 'before' => $before ) ); 
					?>

					<?php do_action( 'bbp_theme_after_topic_form_content' ); ?>

					<?php 
					// bbp_get_template_part( 'form', 'attachments' ); 
					?>

					<?php if ( ! ( bbp_use_wp_editor() || current_user_can( 'unfiltered_html' ) ) ) : ?>

						<p class="form-allowed-tags">
							<label><?php _e( 'You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes:', 'bfcommons-theme' ); ?></label><br />
							<code><?php bbp_allowed_tags(); ?></code>
						</p>

					<?php endif; ?>

					<?php if ( bbp_allow_topic_tags() && current_user_can( 'assign_topic_tags' ) ) : ?>

						<?php do_action( 'bbp_theme_before_topic_form_tags' ); ?>

						<?php
						$get_topic_id = bbp_get_topic_id();
						$get_the_tags = isset( $get_topic_id ) && ! empty( $get_topic_id ) ? bbp_get_topic_tag_names( $get_topic_id ) : array();
						?>

						<p>
							<input type="hidden" value="<?php echo ( ! empty( $get_the_tags ) ) ? esc_attr( $get_the_tags ) : ''; ?>" name="bbp_topic_tags" class="bbp_topic_tags" id="bbp_topic_tags" >
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

						<?php do_action( 'bbp_theme_after_topic_form_tags' ); ?>

					<?php endif; ?>

					<?php if ( ! bbp_is_single_forum() ) : ?>

						<?php do_action( 'bbp_theme_before_topic_form_forum' ); ?>

						<p>
							<label for="bbp_forum_id"><?php _e( 'Forum:', 'bfcommons-theme' ); ?></label><br />
							<?php
								bbp_dropdown(
									array(
										'show_none' => __( '(No Forum)', 'bfcommons-theme' ),
										'selected'  => bbp_get_form_topic_forum(),
									)
								);
							?>
						</p>

						<?php do_action( 'bbp_theme_after_topic_form_forum' ); ?>

					<?php endif; ?>

					<?php if ( current_user_can( 'moderate' ) ) : ?>

						<?php do_action( 'bbp_theme_before_topic_form_type' ); ?>

						<p>

							<label for="bbp_stick_topic"><?php _e( 'Type:', 'bfcommons-theme' ); ?></label><br />

							<?php bbp_form_topic_type_dropdown(); ?>

						</p>

						<?php do_action( 'bbp_theme_after_topic_form_type' ); ?>

					<?php endif; ?>

					<?php if ( false && bbp_is_subscriptions_active() && ! bbp_is_anonymous() && ( ! bbp_is_topic_edit() || ( bbp_is_topic_edit() && ! bbp_is_topic_anonymous() ) ) ) : ?>

						<?php
						if (
							bb_enabled_legacy_email_preference() ||
							( ! bb_enabled_legacy_email_preference() && bb_get_modern_notification_admin_settings_is_enabled( 'bb_forums_subscribed_discussion' ) )
						) {
							?>

							<?php do_action( 'bbp_theme_before_topic_form_subscriptions' ); ?>

							<p class="checkbox bp-checkbox-wrap">
								<input name="bbp_topic_subscription" id="bbp_topic_subscription" class="bs-styled-checkbox" type="checkbox" value="bbp_subscribe" <?php bbp_form_topic_subscribed(); ?> tabindex="<?php bbp_tab_index(); ?>" />

								<?php if ( bbp_is_topic_edit() && ( bbp_get_topic_author_id() !== bbp_get_current_user_id() ) ) : ?>

									<label for="bbp_topic_subscription"><?php esc_html_e( 'Notify the author of follow-up replies via email', 'bfcommons-theme' ); ?></label>

								<?php else : ?>

									<label for="bbp_topic_subscription"><?php esc_html_e( 'Notify me of follow-up replies via email', 'bfcommons-theme' ); ?></label>

								<?php endif; ?>
							</p>

							<?php do_action( 'bbp_theme_after_topic_form_subscriptions' ); ?>

						<?php } ?>

					<?php endif; ?>

					<?php if ( bbp_allow_revisions() && bbp_is_topic_edit() ) : ?>

						<?php do_action( 'bbp_theme_before_topic_form_revisions' ); ?>

						<!-- <fieldset class="bbp-form">
							<div class="bp-checkbox-wrap">
								<input name="bbp_log_topic_edit" id="bbp_log_topic_edit" class="bs-styled-checkbox" type="checkbox" value="1" <?php bbp_form_topic_log_edit(); ?> tabindex="<?php bbp_tab_index(); ?>" />
								<label for="bbp_log_topic_edit"><?php _e( 'Keep a log of this edit:', 'bfcommons-theme' ); ?></label><br />
							</div>

							<div>
								<label for="bbp_topic_edit_reason"><?php printf( __( 'Optional reason for editing:', 'bfcommons-theme' ), bbp_get_current_user_name() ); ?></label><br />
								<input type="text" value="<?php bbp_form_topic_edit_reason(); ?>" tabindex="<?php bbp_tab_index(); ?>" size="40" name="bbp_topic_edit_reason" id="bbp_topic_edit_reason" />
							</div>
						</fieldset> -->

						<?php do_action( 'bbp_theme_after_topic_form_revisions' ); ?>

					<?php endif; ?>

					<?php do_action( 'bbp_theme_before_topic_form_submit_wrapper' ); ?>

					<div class="bbp-submit-wrapper">

						<?php do_action( 'bbp_theme_before_topic_form_submit_button' ); ?>

						<button type="submit" tabindex="<?php bbp_tab_index(); ?>" id="bbp_topic_submit" name="bbp_topic_submit" class="button submit"><?php _e( 'Post', 'bfcommons-theme' ); ?></button>

						<?php do_action( 'bbp_theme_after_topic_form_submit_button' ); ?>

					</div>

					<?php do_action( 'bbp_theme_after_topic_form_submit_wrapper' ); ?>

				</div>

				<?php bbp_topic_form_fields(); ?>

			</fieldset>

			<?php do_action( 'bbp_theme_after_topic_form' ); ?>

		</form>
	</div>

<?php elseif ( bbp_is_forum_closed() ) : ?>

	<div id="no-topic-<?php bbp_topic_id(); ?>" class="bbp-no-topic">
		<div class="bp-feedback info">
			<span class="bp-icon" aria-hidden="true"></span>
			<p><?php printf( __( 'The forum "%s" is closed to new threads and replies.', 'bfcommons-theme' ), bbp_get_forum_title() ); ?></p>
		</div>
	</div>

<?php else : ?>

	<div id="no-topic-<?php bbp_topic_id(); ?>" class="bbp-no-topic">
		<div class="bp-feedback info">
			<span class="bp-icon" aria-hidden="true"></span>
			<p><?php is_user_logged_in() ? _e( 'You cannot create new threads.', 'bfcommons-theme' ) : _e( 'You must be logged in to create new threads.', 'bfcommons-theme' ); ?></p>
		</div>
	</div>

<?php endif; ?>

<?php if ( ! bbp_is_single_forum() ) : ?>

</div>

<?php endif; ?>

<script>
var bfcCount = 0;
jQuery('.bbp-topic-form form button#bbp_topic_submit').click(function(event){
	bfcCount++;
	if (bfcCount>1) {
		event.preventDefault();
		event.stopImmediatePropagation();
	}
	jQuery('#bbp_topic_submit')
		.css({backgroundColor:'#fff', borderColor:'#c8cbcf', color: '#c8cbcf', cursor:'default'})
		.text('Please wait ...');
});
</script>
