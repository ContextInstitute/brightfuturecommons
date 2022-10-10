<?php
$has_revision = wp_get_post_revisions( get_the_ID() );
$bp_docs_do_theme_compat = is_buddypress() && bp_docs_do_theme_compat( 'single/history.php' );
if ( ! $bp_docs_do_theme_compat ) : ?>
<div id="buddypress">
<?php endif; ?>

<div class="<?php bp_docs_container_class(); ?>">

	<?php include( apply_filters( 'bp_docs_header_template', bp_docs_locate_template( 'docs-header.php' ) ) ) ?>

	<div class="doc-content">

	<?php if($has_revision)  : ?>

		<?php if ( bp_docs_history_is_latest() ) : ?>

			<p><?php _e( "The first item on the list below is the current version, included so it can be compared to.", 'buddypress-docs' ) ?></p>

			<p><?php _e( "You can compare two revisions by selecting them in the 'Old' and 'New' columns, and clicking 'Compare Revisions'.", 'buddypress-docs' ) ?></p>

			<p><?php _e( "Alternatively, you can click on the 'Date Created' for any earlier item to view that revision by itself.", 'buddypress-docs' ) ?></p>

		<?php endif;

		 
		if ( 'diff' == bp_docs_history_action() ) {
			$bfc_history_class = ' bfc-diff';
		}elseif ( !bp_docs_history_is_latest() ) {
			$bfc_history_class = ' bfc-revision';
		}
		?>

		<table class="form-table ie-fixed<?php echo $bfc_history_class; ?>">
			<col class="th" />

			<?php if ( 'diff' == bp_docs_history_action() ) : ?>
				<tr id="revision">
					<th scope="row"></th>
					<th scope="col" class="th-full">
						<span class="alignleft"><?php printf( __( 'Older: %s', 'buddypress-docs' ), bp_docs_history_post_revision_field( 'left', 'post_title' ) ); ?></span>
						<span class="alignright"><?php printf( __( 'Newer: %s', 'buddypress-docs' ), bp_docs_history_post_revision_field( 'right', 'post_title' ) ); ?></span>
					</th>
				</tr>
			<?php elseif ( !bp_docs_history_is_latest() ) : ?>
				<tr id="revision">
					<th scope="row"></th>
					<th scope="col" class="th-full">
						<span class="alignleft"><?php printf( __( 'You are currently viewing a revision saved %1$s by %2$s', 'buddypress-docs' ), bfc_nice_date( strtotime( bp_docs_history_post_revision_field( false, 'post_date_gmt' ) ) ), bp_core_get_userlink( bp_docs_history_post_revision_field( false, 'post_author' ) ) ); ?></span>
					</th>
				</tr>
			<?php endif ?>

			<?php foreach ( _wp_post_revision_fields() as $field => $field_title ) : ?>
				<?php if ( 'diff' == bp_docs_history_action() ) : ?>
					<tr id="revision-field-<?php echo $field; ?>">
						<th scope="row"><?php echo esc_html( $field_title ); ?></th>
						<?php if (!bp_docs_history_revisions_are_identical()) :
						$diff_text = wp_text_diff( bp_docs_history_post_revision_field( 'left', $field ), bp_docs_history_post_revision_field( 'right', $field ) );
						$diff_text = ($diff_text) ? $diff_text : '(These revisions are identical in ' . strtolower ($field_title) . '.)';
						?>
						<td><div class="pre"><?php echo $diff_text ?></div></td>
						<?php endif ?>
					</tr>
				<?php elseif ( !bp_docs_history_is_latest() ) : ?>
					<?php if ( $field == 'post_content' ) : ?>
						<tr id="revision-field-<?php echo $field; ?>">
							<th scope="row"><?php echo 'Revision ' . esc_html( $field_title ); ?></th>
							<td><div class="pre"><?php echo bfc_docs_get_the_content( $_GET["revision"]); ?></div></td>
						</tr>
					<?php else : ?>
						<tr id="revision-field-<?php echo $field; ?>">
							<th scope="row"><?php echo 'Revision ' . esc_html( $field_title ); ?></th>
							<td><div class="pre"><?php echo bp_docs_history_post_revision_field( false, $field ) ?></div></td>
						</tr>
					<?php endif ?>

				<?php endif ?>

			<?php endforeach ?>

			<?php do_action( 'bp_docs_revisions_comparisons' ) ?>

			<?php if ( 'diff' == bp_docs_history_action() && bp_docs_history_revisions_are_identical() ) : ?>
				<tr><td colspan="2"><div class="updated"><p><?php _e( 'These revisions are identical.', 'buddypress-docs' ); ?></p></div></td></tr>
			<?php endif ?>

		</table>

		<br class="clear" />

		<?php bfc_docs_list_post_revisions( get_the_ID()) ?>

		</div>
	<?php else : ?>
		<p><?php _e( "This doc doesn't have any revisions yet to compare.", 'buddypress-docs' ) ?></p>
	<?php endif; ?>
</div><!-- .bp-docs -->

<?php if ( ! $bp_docs_do_theme_compat ) : ?>
</div><!-- /#buddypress -->
<?php endif; ?>
