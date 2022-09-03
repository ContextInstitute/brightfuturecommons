<?php

/**
 * Mostly borrowed from BuddyPress Default
 *
 */

// If this is a history or edit page, bail
if ( ! bp_docs_is_doc_read() ) {
	return;
}

if (class_exists('Simple_Comment_Editing')) {
	add_filter( 'comment_excerpt', 'bfc_docs_sce_add_reply_link' , 1002, 3 );
	add_filter( 'comment_text', 'bfc_docs_sce_add_reply_link' , 1002, 3 );
}

$num_comments = 0;
$num_trackbacks = 0;
foreach ( (array)$comments as $comment ) {
	if ( 'comment' != get_comment_type() )
		$num_trackbacks++;
	else
		$num_comments++;
}

?>

<?php if ( current_user_can( 'bp_docs_read_comments' ) ) : ?>
	<div id="comments" class="comments-area">
		<h3>
			<?php printf( __( 'Discussion (%d)', 'bfcommons-theme' ), $num_comments ) ?>
		</h3>

		<?php do_action( 'bp_before_blog_comment_list' ) ?>

		<?php
		$user_link = function_exists( 'bp_core_get_user_domain' ) ? bp_core_get_user_domain( get_current_user_id() ) : get_author_posts_url( get_current_user_id() );

		// You can start editing here -- including this comment!
		$args = array(
			'comment_field'      => '<p class="comment-form-comment"><textarea id="comment" name="comment" cols="45" rows="8" aria-required="true" placeholder="' . __( 'Comment on this doc...', 'buddyboss-theme' ) . '"></textarea></p>',
			'title_reply'        => '',

			/*
			* translators:
			* %1$s - user avatar html
			* %3$s - User Name
			*/
			'logged_in_as'       => '<p class="logged-in-as">' . sprintf( __( '<a class="comment-author" href="%1$s"><span class="vcard">%2$s</span><span class="name">%3$s</span></a>', 'buddyboss-theme' ), $user_link, get_avatar( get_current_user_id(), 80 ), $user_identity ) . '</p>',
			'class_submit'       => 'submit button outline small',
			'title_reply_before' => '',
			'title_reply_after'  => '',
			'label_submit'       => __( 'Publish', 'buddyboss-theme' ),
		);

		// comment_form( $args );

		if ( have_comments() ) : ?>

			<ol class="comment-list commentlist">
				<?php
				wp_list_comments(
					array(
						'callback'    => 'bfc_comment',
						'style'       => 'ol',
						'short_ping'  => true,
						'avatar_size' => 80,
					)
				);
				?>
			</ol><!-- .comment-list -->

			<?php do_action( 'bp_after_blog_comment_list' ) ?>

			<?php if ( get_option( 'page_comments' ) ) : ?>
				<div class="comment-navigation paged-navigation">
					<?php paginate_comments_links() ?>
				</div>
			<?php endif; ?>

		<?php else : ?>

			<p class="comments-closed comments-empty">
				<?php _e( 'There are no comments for this doc yet.', 'bfcommons-theme' ) ?>
			</p>

		<?php endif ?>

		<?php if ( current_user_can( 'bp_docs_post_comments' ) ) : ?>
			<?php comment_form( $args ) ?>
		<?php else : ?>
			<p class="comments-closed comment-posting-disabled">
				<?php _e( 'Comment posting has been disabled on this doc.', 'bfcommons-theme' ) ?>
			</p>
		<?php endif; ?>

		<script>
			// Disable 'submit comment' until we have something in the field
			if ( jQuery( '#submit' ).length ){
				jQuery( '#submit' ).prop( 'disabled', true );

				jQuery( '#comment' ).keyup( function() {
					if ( jQuery.trim( jQuery( '#comment' ).val().length ) > 0 ) {
						jQuery( '#submit' ).prop( 'disabled', false );
					} else {
						jQuery( '#submit' ).prop( 'disabled', true );
					}
				});
			}
		</script>

	</div><!-- #comments -->

<?php else : ?>
	<p class="comments-closed comment-display-disabled">
		<?php _e( 'Comment display has been disabled on this doc.', 'bfcommons-theme' ) ?>
	</p>

<?php endif; 

if (class_exists('Simple_Comment_Editing')) {
	remove_filter( 'comment_excerpt', 'bfc_docs_sce_add_reply_link' , 1002 );
	remove_filter( 'comment_text', 'bfc_docs_sce_add_reply_link' , 1002 );
}

?>
