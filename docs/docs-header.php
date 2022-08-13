<?php do_action( 'bp_docs_before_doc_header' ) ?>
<!-- header start-->
<!-- <?php /* Subnavigation on user pages is handled by BP's core functions */ ?> -->
<?php if ( ! (bp_is_user() || bp_docs_is_single_doc()) && current_user_can( 'bp_docs_create' )): ?>
	<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
		<?php bp_docs_create_button(); ?>
	 </div> <!-- .item-list-tabs -->
<?php endif ?>

<?php do_action( 'bp_docs_before_doc_header_content' ) ?>

<?php if ( bp_docs_is_existing_doc() ) : ?>

	<h1 class="entry-title"><?php the_title()?></h1>

	
	<?php if ( ! bp_docs_is_theme_compat_active() && bp_docs_is_doc_trashed()) : ?>
		<h2 class="bp-docs-trashed-doc-notice" title="<?php esc_html_e( 'This Doc is in the Trash', 'buddypress-docs' ) ?>"><?php esc_html_e( 'Trash', 'buddypress-docs' ); ?></h2>
	<?php endif ?>

	<?php 
	// do_action( 'bp_docs_single_doc_header_fields' ) 
	?>
	<div id="bp-docs-single-doc-header">

		<div class="author-block">
			<?php bfc_doc_authors( get_the_ID() ); ?>
		</div>

		<?php
			$can_edit = current_user_can( 'bp_docs_edit', get_the_ID() );
			$can_view_history = current_user_can( 'bp_docs_view_history', get_the_ID() ) && defined( 'WP_POST_REVISIONS' ) && WP_POST_REVISIONS && boolval( wp_get_post_revisions( get_the_ID() ));
			$can_manage_trash = current_user_can( 'manage', get_the_ID() ) && bp_docs_is_doc_trashed( get_the_ID() );
		?>
		<?php if ( $can_edit || $can_view_history || $can_manage_trash) : ?>

			<div class="meta-block">
				Last edit: <?php echo get_the_modified_date() ?>
			</div>

			<div class="doc-tabs">
				<ul>
					<li<?php if ( bp_docs_is_doc_read() ) : ?> class="current"<?php endif ?>>
						<a href="<?php bp_docs_doc_link() ?>" class="bb-icon-book-open bb-icon-l" title="<?php _e( 'Read', 'buddypress-docs' ) ?>"></a>
					</li>

					<?php if ( $can_edit ) : ?>
						<li<?php if ( bp_docs_is_doc_edit() ) : ?> class="current"<?php endif ?>>
							<a href="<?php bp_docs_doc_edit_link() ?>" class="bb-icon-edit-square bb-icon-l" title="<?php _e( 'Edit', 'buddypress-docs' ) ?>"></a>
						</li>
					<?php endif ?>

					<?php if ( $can_view_history ) : ?>
						<li<?php if ( bp_docs_is_doc_history() ) : ?> class="current"<?php endif ?>>
							<a href="<?php echo bp_docs_get_doc_link() . BP_DOCS_HISTORY_SLUG ?>" class="bb-icon-clock bb-icon-l" title="<?php _e( 'History', 'buddypress-docs' ) ?>"></a>
						</li>
					<?php endif ?>

					<?php if ( $can_manage_trash ) : ?>
						<li<?php if ( bp_docs_is_doc_history() ) : ?> class="current"<?php endif ?>>
							<a href="<?php echo bp_docs_get_remove_from_trash_link( get_the_ID() ) ?>" class="bb-icon-trash-restore bb-icon-l delete confirm" title="<?php _e( 'Untrash', 'buddypress-docs' ) ?>"></a>
						</li>
					<?php endif ?>
				</ul>
			</div>
		<?php else : ?>
			<div class="meta-block-right">
				Last edit: <?php echo get_the_modified_date() ?>
			</div>
		<?php endif ?>
	</div>
<?php elseif ( bp_docs_is_doc_create() ) : ?>

	<h2><?php _e( 'Create a Doc', 'buddypress-docs' ); ?></h2>

<?php endif ?>
<!-- header end-->
<?php do_action( 'bp_docs_after_doc_header_content' ) ?>
<!-- after header end-->
