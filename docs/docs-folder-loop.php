<?php $current_folder_id = isset( $_GET['folder'] ) ? absint( $_GET['folder'] ) : 0; ?>
<table class="doctable" data-folder-id="<?php echo $current_folder_id; ?>">
<tbody>
<?php $has_folders = false; ?>
<?php if ( true ) : ?>
	<?php if ( bp_docs_include_folders_in_loop_view() ) : ?>
		<?php foreach ( bp_docs_get_folders() as $folder ) : ?>
			<?php $has_folders  = true; ?>
			<tr class="folder-row">
				<?php /* Just to keep things even */ ?>
				<?php if ( bp_docs_enable_attachments() ) : ?>
					<td class="attachment-clip-cell">
						<?php bp_docs_attachment_icon() ?>
					</td>
				<?php endif ?>

				<td class="folder-row-name" colspan=10>
					<div class="toggleable <?php bp_docs_toggleable_open_or_closed_class( 'folder-contents-toggle' ); ?>">
						<span class="folder-toggle-link toggle-link-js"><a class="toggle-folder" id="expand-folder-<?php echo $folder->ID; ?>" data-folder-id="<?php echo $folder->ID; ?>" href="<?php echo esc_url( bp_docs_get_folder_url( $folder->ID ) ) ?>"><span class="hide-if-no-js"><?php bp_docs_genericon( 'expand', $folder->ID ); ?></span><?php bp_docs_genericon( 'category', $folder->ID ); ?><?php echo esc_html( $folder->post_title ) ?></a></span>
						<div class="toggle-content folder-loop"></div>
					</div>
				</td>
			</tr>
		<?php endforeach ?>
	<?php endif; ?>
<?php endif; /* bp_docs_enable_folders_for_current_context() */ ?>

<?php $has_docs = false ?>
<?php if ( bp_docs_has_docs() ) : ?>
	<?php $has_docs = true; ?>
		<ul class="docs-list">
		<?php while ( bp_docs_has_docs() ) : bp_docs_the_doc() ?>

			<li <?php bp_docs_doc_row_classes(); ?> data-doc-id="<?php echo get_the_ID() ?>">
				<div class="list-wrap">
					<div class="item">
						<div class="title-block">
							<a href="<?php bp_docs_doc_link() ?>"><?php the_title() ?></a> <?php bp_docs_doc_trash_notice(); ?>
							<div class="row-actions">
								<?php 
									bfc_docs_action_links();
									if ( current_user_can( 'bp_docs_read_comments' ) ) {
										$comment_count = get_comments (array ('post_id' => get_the_ID(), 'count' => true ));
										if ( $comment_count ) { 
											echo '<a href="' . bp_docs_get_doc_link() . '#comments" class="bfc-num-comments" title="Comments"> ' . $comment_count .'<span class="bb-icon-comment bb-icon-l bfc-comment-bubble"></span></a>';
										}
									}
								?>
							</div>
							<div class="folder-block">
								<p><?php echo bfc_docs_location(); ?></p>
							</div>
						</div>
						<div class="meta-block">
							<p>Last edit: <?php echo bfc_nice_date (get_post_modified_time('U', true)) ?></p>
							<p><?php bfc_show_terms(); ?></p>
							<p><?php bfc_show_parent(); ?></p>
						</div>
						<div class="author-block">
							<?php bfc_doc_authors( get_the_ID() ); ?>
						</div>
					</div>
				</div>
			</li>
		<?php endwhile ?>
		</ul>
		
		<tr class="folder-meta-info">
			<?php if ( bp_docs_enable_attachments() ) : ?>
				<td class="attachment-clip-cell">
					<?php bp_docs_attachment_icon() ?>
				</td>
			<?php endif ?>
			<td class="folder-meta-info-statement" colspan=10>
				<?php printf( __( 'Viewing %1$s-%2$s of %3$s docs in this folder.', 'buddypress-docs' ), bp_docs_get_current_docs_start(), bp_docs_get_current_docs_end(), bp_docs_get_total_docs_num() ) ?> <br/>
				<a href="<?php echo esc_url( bp_docs_get_folder_url( $current_folder_id ) ); ?>"><?php printf( __( 'View all docs in <strong>%s</strong>.', 'buddypress-docs' ), get_the_title( $current_folder_id ) ); ?></a>
			</td>
		</tr>
<?php endif; ?>
	<?php // Add the "no docs" message as the last row, for easy toggling. ?>
	<tr class="no-docs-row<?php if ( $has_docs || $has_folders ) { echo ' hide'; } ?>">
		<?php if ( bp_docs_enable_attachments() ) : ?>
			<td class="attachment-clip-cell"></td>
		<?php endif ?>

		<td class="title-cell">
			<?php if ( bp_docs_current_user_can_create_in_context() ) : ?>
				<p class="no-docs"><?php printf( __( 'There are no docs for this view. Why not <a href="%s">create one</a>?', 'buddypress-docs' ), bp_docs_get_create_link() ); ?>
			<?php else : ?>
				<p class="no-docs"><?php _e( 'There are no docs for this view.', 'buddypress-docs' ); ?></p>
			<?php endif; ?>
		</td>

		<?php if ( ! bp_docs_is_started_by() ) : ?>
			<td class="author-cell"></td>
		<?php endif; ?>

		<td class="date-cell created-date-cell"></td>
		<td class="date-cell edited-date-cell"></td>
	</tr>
</tbody>
</table>
