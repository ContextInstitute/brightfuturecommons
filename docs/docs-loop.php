<?php

$help_group_id = BP_Groups_Group::get_id_from_slug( 'evolving-the-commons' );

$bp_docs_do_theme_compat = is_buddypress() && bp_docs_do_theme_compat( 'docs-loop.php' );
if ( ! $bp_docs_do_theme_compat ) : ?>
<div id="buddypress">
<?php endif; 
$docs_view_class = bp_docs_is_folder_manage_view() ? ' bp-docs-manage-folders' : ' bp-docs-directory';
$has_docs = false;
?>
<!-- docs-loop start -->
<div class="<?php bp_docs_container_class(); echo $docs_view_class ?> ">

<?php if ('bfcom-help' == urldecode( isset($_GET['bpd_tag'] ) ? $_GET['bpd_tag'] : '') ) : ?>

	<h1 class="directory-title">
		<?php echo 'Commons Help'; ?>
	</h1>

	<?php $has_docs = false ?>
	<?php if ( bp_docs_has_docs( array( 'orderby' => 'menu_order', 'order' => 'ASC', 'group_id' =>  $help_group_id) ) ) : ?>
		<?php $has_docs = true ?>
		<ul class="docs-list">
		<?php while ( bp_docs_has_docs() ) : bp_docs_the_doc() ?>
			<li <?php bp_docs_doc_row_classes(); ?> data-doc-id="<?php echo get_the_ID() ?>">
				<div class="list-wrap">
					<div class="item">
						<div class="title-block">
							<a href="<?php bp_docs_doc_link() ?>"><?php the_title() ?></a> <?php bp_docs_doc_trash_notice(); ?>
						</div>
						<div class="meta-block">
							<p>Latest edit: <?php echo get_the_modified_date() ?></p>
						</div>
						<div class="author-block">
							<?php bfc_doc_authors( get_the_ID() ); ?>
						</div>
					</div>
				</div>
			</li>
		<?php endwhile ?>
		</ul>
	<?php endif ?>
<?php else : ?>


	<?php include( apply_filters( 'bp_docs_header_template', bp_docs_locate_template( 'docs-header.php' ) ) ) ?>

	<?php if ( current_user_can( 'bp_docs_associate_with_group', bp_get_current_group_id() ) && bp_docs_is_folder_manage_view() ) : ?>
		<?php bp_docs_locate_template( 'manage-folders.php', true ); ?>
	<?php else : ?>

		<?php $directory_title = (bp_docs_get_directory_breadcrumb()) ? bp_docs_get_directory_breadcrumb() : 'Docs Directory'; ?>
		<h1 class="directory-title">
			<?php echo $directory_title; ?>
		</h1>

		<div class="docs-info-header">
			<?php echo bfc_docs_get_info_header() ?>

			<?php if ( bp_current_component() == 'groups' && current_user_can( 'bp_docs_associate_with_group', bp_get_current_group_id() ) ) : ?>
				<div class="folder-action-links manage-folders-link">
					<a href="<?php bp_docs_manage_folders_url() ?>"><?php _e( 'Manage Folders', 'buddypress-docs' ) ?></a>
				</div>
			<?php endif ?>
		</div>

		<?php if ( bp_docs_enable_folders_for_current_context() ) : ?>
			<div class="folder-action-links">
				<?php if ( current_user_can( 'bp_docs_associate_with_group', bp_get_current_group_id() ) ) : ?>
					<div class="manage-folders-link">
						<a href="<?php bp_docs_manage_folders_url() ?>"><?php _e( 'Manage Folders', 'buddypress-docs' ) ?></a>
					</div>
				<?php endif ?>

				<div class="toggle-folders-link hide-if-no-js">
					<a href="#" class="toggle-folders" id="toggle-folders-hide"><?php _e( 'Hide Folders', 'buddypress-docs' ) ?></a>
					<a href="#" class="toggle-folders" id="toggle-folders-show"><?php _e( 'Show Folders', 'buddypress-docs' ) ?></a>
				</div>
			</div>
		<?php endif; ?>

		<div class="doctable" data-folder-id="0">
		<?php $has_folders = false; ?>
		<?php if ( bp_docs_enable_folders_for_current_context() ) : ?>
			<?php /* The '..' row */ ?>
			<?php if ( ! empty( $_GET['folder'] ) ) : ?>
				<tr class="folder-row">
					<?php /* Just to keep things even */ ?>
					<?php if ( bp_docs_enable_attachments() ) : ?>
						<td class="attachment-clip-cell">
							<?php bp_docs_attachment_icon() ?>
						</td>
					<?php endif ?>
				</tr>
			<?php endif ?>

			<?php if ( bp_docs_include_folders_in_loop_view() ) : ?>
				<?php foreach ( bp_docs_get_folders() as $folder ) : ?>
					<?php if ( bfc_docs_user_can_access_folder($folder->ID) ) : ?>
						<?php 
							$has_folders = true; 
						?>
						<tr class="folder-row">
							<?php /* Just to keep things even */ ?>
							<?php if ( bp_docs_enable_attachments() ) : ?>
								<td class="attachment-clip-cell">
									<?php bp_docs_attachment_icon() ?>
								</td>
							<?php endif ?>
							<td class="folder-row-name" colspan=10>
								<div class="toggleable <?php bp_docs_toggleable_open_or_closed_class( 'folder-contents-toggle' ); ?>">
									<span class="folder-toggle-link toggle-link-js"><a class="toggle-folder" id="expand-folder-<?php echo $folder->ID; ?>" data-folder-id="<?php echo $folder->ID; ?>" href="<?php echo esc_url( bp_docs_get_folder_url( $folder->ID ) ) ?>"><span class="hide-if-no-js"><?php bp_docs_genericon( 'expand', $folder->ID ); ?></span><?php echo bfc_docs_folder_icon (); ?><?php echo esc_html( $folder->post_title ) ?></a></span>
									<div class="toggle-content folder-loop"></div>
								</div>
							</td>
						</tr>
					<?php endif ?>
				<?php endforeach ?>
			<?php endif; ?>
		<?php endif; /* bp_docs_enable_folders_for_current_context() */ ?>

		<?php $has_docs = false ?>
		<?php if ( bp_docs_has_docs( array( 'update_attachment_cache' => true ) ) ) : ?>
			<?php $has_docs = true ?>
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
		<?php endif; ?>
	<?php endif; ?>
<?php endif; ?>

<?php // Add the "no docs" message as the last row, for easy toggling. ?>
	<div class="no-docs-row<?php if ( $has_docs || $has_folders || bp_docs_is_folder_manage_view()) { echo ' hide'; } ?>">
		<?php if ( bp_docs_current_user_can_create_in_context() ) : ?>
			<p class="no-docs"><?php printf( __( 'There are no docs for this view. Why not <a href="%s">create one</a>?', 'buddypress-docs' ), bp_docs_get_create_link() ); ?>
		<?php else : ?>
			<p class="no-docs"><?php _e( 'There are no docs for this view.', 'buddypress-docs' ); ?></p>
		<?php endif; ?>
	</div>
</div> <!-- end of bp-docs-directory, line 8 -->

<?php if ( $has_docs ) : ?>
	<div id="bp-docs-pagination">
		<div id="bp-docs-pagination-count">
			<?php printf( __( 'Viewing %1$s-%2$s of %3$s docs', 'buddypress-docs' ), bp_docs_get_current_docs_start(), bp_docs_get_current_docs_end(), bp_docs_get_total_docs_num() ); ?>
		</div>

		<div id="bp-docs-paginate-links">
			<?php bp_docs_paginate_links(); ?>
		</div>
	</div>
<?php endif; ?>

<?php bp_docs_ajax_value_inputs(); ?>
</div><!-- /.bp-docs -->

<?php if ( ! $bp_docs_do_theme_compat ) : ?>
</div><!-- /#buddypress -->
<?php endif; ?>
<!-- docs-loop end -->
