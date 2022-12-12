<?php
/**
 * BuddyPress Single Groups item Navigation
 *
 * @since BuddyPress 3.0.0
 * @version 3.0.0
 */
?>

<nav class="<?php bp_nouveau_single_item_nav_classes(); ?>" id="object-nav" role="navigation" aria-label="<?php esc_attr_e( 'Group menu', 'bfcommons-theme' ); ?>">

	<?php if ( bp_nouveau_has_nav( array( 'object' => 'groups' ) ) ) : ?>

		<ul>

			<?php
			$activity_feed_status = bp_group_get_activity_feed_status();
			$user_can_see_timeline = false;
			if ($activity_feed_status == 'members'){
				$user_can_see_timeline = true;
			} elseif ($activity_feed_status == 'mods' && (groups_is_user_mod(bp_loggedin_user_id(), bp_get_current_group_id()) || groups_is_user_admin(bp_loggedin_user_id(), bp_get_current_group_id()))){
				$user_can_see_timeline = true;
			} elseif ($activity_feed_status == 'admins' && groups_is_user_admin(bp_loggedin_user_id(), bp_get_current_group_id())){
				$user_can_see_timeline = true;
			}
			while ( bp_nouveau_nav_items() ) :
				bp_nouveau_nav_item();
				if (bp_nouveau_get_nav_link_text() == 'Timeline' && !(is_super_admin( bp_loggedin_user_id()) || $user_can_see_timeline) ) {
					continue;
				} elseif (bp_nouveau_get_nav_link_text() == 'Images') {
					continue;
				} elseif (bp_nouveau_get_nav_link_text() == 'Members' && bp_get_group_id()<5 ) {
					continue;
				}
			?>

				<li id="<?php bp_nouveau_nav_id(); ?>" class="<?php bp_nouveau_nav_classes(); ?>">
					<a href="<?php bp_nouveau_nav_link(); ?>" id="<?php bp_nouveau_nav_link_id(); ?>">
						<?php bp_nouveau_nav_link_text(); ?>

						<?php if ( bp_nouveau_nav_has_count() ) : ?>
							<span class="count"><?php bp_nouveau_nav_count(); ?></span>
						<?php endif; ?>
					</a>
				</li>

			<?php endwhile; ?>

			<?php bp_nouveau_group_hook( '', 'options_nav' ); ?>

		</ul>

	<?php endif; ?>

</nav>
