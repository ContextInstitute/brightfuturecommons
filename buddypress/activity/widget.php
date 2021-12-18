<?php
/**
 * BFC Activity Widget template.
 * Adapted from BP Nouveau Activity Widget template.
 *
 * @since BuddyPress 3.0.0
 * @version 3.0.0
 */
?>

<?php if ( bp_has_activities( bp_nouveau_activity_widget_query() ) ) : ?>

	<div class="activity-list item-list">

		<?php
		while ( bp_activities() ) :
			bp_the_activity();
		?>
		<?php if ( bp_activity_has_content() ) : ?>
		<div class="activity-update">

			<div class="update-item">

				<cite>
					<a href="<?php bp_activity_user_link(); ?>" class="bp-tooltip" data-bp-tooltip-pos="up" data-bp-tooltip="<?php echo esc_attr( bp_activity_member_display_name() ); ?>">
						<?php
						bp_activity_avatar(
							array(
								'type'   => 'thumb',
								'width'  => '40',
								'height' => '40',
							)
						);
						?>
					</a>
				</cite>

				<div class="bp-activity-info">
					<?php bp_activity_action(); ?>
				</div>
			</div>

		</div>
		<div class="activity-content <?php ( function_exists('bp_activity_entry_css_class') ) ? bp_activity_entry_css_class(): ''; ?>">
		
			<div class="activity-inner <?php echo ( function_exists( 'bp_activity_has_content' ) && empty( bp_activity_has_content() ) ) ? esc_attr( 'bb-empty-content' ) : esc_attr( '' ); ?>">
				<?php
					add_filter('bp_activity_excerpt_length','bfc_activity_widget_excerpt_length',900);
					bp_nouveau_activity_content();
					remove_filter('bp_activity_excerpt_length','bfc_activity_widget_excerpt_length',900);
				?>
			</div>

		</div>

		<?php endif; ?>

		<?php endwhile; ?>

	</div>

<?php else : ?>

	<div class="widget-error">
		<?php bp_nouveau_user_feedback( 'activity-loop-none' ); ?>
	</div>

<?php endif; ?>
