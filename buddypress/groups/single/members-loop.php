<?php
/**
 * Group Members Loop template
 *
 * @since 3.0.0
 * @version 3.0.0
 */
?>

<?php
$message_button_args = array(
	'link_text'         => '<i class="bb-icon-mail-small"></i>',
	'button_attr' => array(
		'data-balloon-pos' => 'down',
		'data-balloon' => __( 'Message', 'buddyboss-theme' ),
	)
);

$footer_buttons_class = ( bp_is_active('friends') && bp_is_active('messages') ) ? 'footer-buttons-on' : '';

$is_follow_active = bp_is_active('activity') && function_exists('bp_is_activity_follow_active') && bp_is_activity_follow_active();
$follow_class = $is_follow_active ? 'follow-active' : '';
?>


<?php if ( bp_group_has_members( bp_ajax_querystring( 'group_members' ) . '&type=group_role' ) ) : ?>

	<?php bp_nouveau_group_hook( 'before', 'members_content' ); ?>

	<?php bp_nouveau_pagination( 'top' ); ?>

	<?php bp_nouveau_group_hook( 'before', 'members_list' ); ?>

	<script> 
		jQuery(document).foundation();
	</script>

	<?php
	$count = groups_get_current_group()->total_member_count;
	If ($count < 22) : ?>
	<ul id="members-intros" class="bp-list">
	<?php
		while ( bp_group_members() ) :
			bp_group_the_member();
			$member_id           = bp_get_member_user_id();
			$show_message_button = buddyboss_theme()->buddypress_helper()->buddyboss_theme_show_private_message_button( $member_id, bp_loggedin_user_id() );
			//Check if members_list_item has content
			ob_start();
			bp_nouveau_member_hook( '', 'members_list_item' );
			$members_list_item_content = ob_get_contents();
			ob_end_clean();
			$member_loop_has_content = empty($members_list_item_content) ? false : true;
			?>
			<li <?php bp_member_class( array( 'item-entry' ) ); ?> data-bp-item-id="<?php echo esc_attr( bp_get_group_member_id() ); ?>" data-bp-item-component="members">
				<div class="list-wrap <?php echo $footer_buttons_class; ?> <?php echo $follow_class; ?> <?php echo $member_loop_has_content ? ' has_hook_content' : ''; ?>">
					<div class="item-avatar">
						<span data-toggle="group-member-dropdown-<?php bp_member_user_id() ; ?>"><?php 
							bb_user_status( bp_get_member_user_id() );
							bp_member_avatar( bp_nouveau_avatar_args() );?></span>
						<?php 
						$type = 'group-member';
						$source = bp_get_member_user_id();
						echo bfc_avatar_dropdown ($type,$source,$follow_class);
						?>
						<div class="list-title member-name"><?php bp_member_name(); ?></div>
					</div>
				</div>
				<div class="item-intro">
					<?php
					$args = array('field' => 8, 'user_id' => bp_get_group_member_id());
					$intro = bp_get_profile_field_data($args);
					if ($intro) {
						echo wpautop($intro);
					} else {
						echo "<p>&nbsp;</p>";
					}
					?>
				</div>
			</li>

		<?php 

		endwhile; 
		else :?>
		<ul id="members-list" class="<?php bp_nouveau_loop_classes(); ?>">
		<?php
		while ( bp_group_members() ) :
			bp_group_the_member();
			$member_id           = bp_get_member_user_id();
			$show_message_button = buddyboss_theme()->buddypress_helper()->buddyboss_theme_show_private_message_button( $member_id, bp_loggedin_user_id() );
			//Check if members_list_item has content
			ob_start();
			bp_nouveau_member_hook( '', 'members_list_item' );
			$members_list_item_content = ob_get_contents();
			ob_end_clean();
			$member_loop_has_content = empty($members_list_item_content) ? false : true;
			?>
			<li <?php bp_member_class( array( 'item-entry' ) ); ?> data-bp-item-id="<?php echo esc_attr( bp_get_group_member_id() ); ?>" data-bp-item-component="members">
				<div class="list-wrap <?php echo $footer_buttons_class; ?> <?php echo $follow_class; ?> <?php echo $member_loop_has_content ? ' has_hook_content' : ''; ?>">
					<div class="item-avatar">
						<span data-toggle="group-member-dropdown-<?php bp_member_user_id() ; ?>"><?php 
							bb_user_status( bp_get_member_user_id() );
							bp_member_avatar( bp_nouveau_avatar_args() );?></span>
						<span class="list-title member-name"><?php bp_member_name(); ?></span>
						<?php 
						$type = 'group-member';
						$source = bp_get_member_user_id();
						echo bfc_avatar_dropdown ($type,$source,$follow_class);
						?>
					</div>

					<div class="bp-members-list-hook">
					<?php 
						if($member_loop_has_content){ ?>
							<a class="more-action-button" href="#"><i class="bb-icon-menu-dots-h"></i></a>
						<?php } ?>
						<div class="bp-members-list-hook-inner">
							<?php bp_nouveau_member_hook( '', 'members_list_item' ); ?>
						</div>
					</div>
				</div>
			</li>

		<?php 
			endwhile;
		endif;
		?>

	</ul>

	<?php bp_nouveau_group_hook( 'after', 'members_list' ); ?>

	<?php bp_nouveau_pagination( 'bottom' ); ?>

	<?php bp_nouveau_group_hook( 'after', 'members_content' ); ?>

<?php else :

	bp_nouveau_user_feedback( 'group-members-none' );

endif;