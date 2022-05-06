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
		$is_follow_active = bp_is_active('activity') && function_exists('bp_is_activity_follow_active') && bp_is_activity_follow_active();
		$follow_class = $is_follow_active ? 'follow-active' : '';
		
		while ( bp_activities() ) :
			bp_the_activity();
			global $bfc_dropdown_prefix;
			$type = $bfc_dropdown_prefix . '-update';
			$person = bp_get_activity_user_id();
			$activity_id = bp_get_activity_id();

		?>
		<div class="activity-update">

			<div class="update-item">

					<span class="bfc-dropdown-span" data-toggle="<?php echo $type . '-dropdown-' . esc_attr( $activity_id ); ?>">
						<?php
						bp_activity_avatar(
							array(
								'type'   => 'thumb',
								'width'  => '40',
								'height' => '40',
							)
						);
						echo bfc_member_dropdown( $type, $activity_id, $person, $follow_class );
						?>
					</span>

				<div class="bp-activity-info">
					<?php bfc_activity_info(); ?>
					<!-- <?php bp_activity_action();?> -->
				</div>
				<div class = "bfc-widget-actions">
					<!-- <p> -->
						<a href="<?php echo bp_activity_get_permalink( $activity_id ); ?>" class = "bb-icon-arrow-up-right"></a>
					<!-- </p> -->
				</div>

			</div>

		</div>
		<div class="activity-content <?php ( function_exists('bp_activity_entry_css_class') ) ? bp_activity_entry_css_class(): ''; ?>">
		
			<div class="activity-inner <?php echo ( function_exists( 'bp_activity_has_content' ) && empty( bp_activity_has_content() ) ) ? esc_attr( 'bb-empty-content' ) : esc_attr( '' ); ?>">
				<?php
					add_filter('bp_activity_excerpt_length','bfc_activity_widget_excerpt_length',900);
					bp_nouveau_activity_content();
					remove_filter('bp_activity_excerpt_length','bfc_activity_widget_excerpt_length',900);
					bfc_widget_activity_state();
				?>
			</div>

		</div>

		<?php endwhile; ?>

	</div>

<?php else : ?>

	<p class="widget-error">You have no updates yet.</p>

<?php endif; ?>
