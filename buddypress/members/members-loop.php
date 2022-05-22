<?php
/**
 * BuddyPress - Members Loop
 *
 * @since 3.0.0
 * @version 3.0.0
 */

bp_nouveau_before_loop(); ?>

<?php
$message_button_args = array(
	'link_text'         => '<i class="bb-icon-mail-small"></i>',
	'button_attr' => array(
		'data-balloon-pos' => 'down',
		'data-balloon' => __( 'Message', 'bfcommons-theme' ),
	)
);

$footer_buttons_class = ( bp_is_active('friends') && bp_is_active('messages') ) ? 'footer-buttons-on' : '';

$is_follow_active = bp_is_active('activity') && function_exists('bp_is_activity_follow_active') && bp_is_activity_follow_active();
$follow_class = $is_follow_active ? 'follow-active' : '';
?>

<?php if ( bp_get_current_member_type() ) : ?>
	<div class="bp-feedback info">
		<span class="bp-icon" aria-hidden="true"></span>
		<p><?php bp_current_member_type_message(); ?></p>
	</div>
<?php endif; ?>

<?php if ( bp_has_members( bp_ajax_querystring( 'members' ) ) ) : ?>

	<ul id="members-list" class="<?php bp_nouveau_loop_classes(); ?>">

	<?php while ( bp_members() ) : bp_the_member(); ?>
		<?php
		$member_id           = bp_get_member_user_id();
		$show_message_button = buddyboss_theme()->buddypress_helper()->buddyboss_theme_show_private_message_button( $member_id, bp_loggedin_user_id() );
		
		//Check if members_list_item has content
		ob_start();
		bp_nouveau_member_hook( '', 'members_list_item' );
		$members_list_item_content = ob_get_contents();
		ob_end_clean();
		$member_loop_has_content = empty($members_list_item_content) ? false : true;
		?>
		<li <?php bp_member_class( array( 'item-entry' ) ); ?> data-bp-item-id="<?php bp_member_user_id(); ?>" data-bp-item-component="members">
			<div class="list-wrap <?php echo $footer_buttons_class; ?> <?php echo $follow_class; ?> <?php echo $member_loop_has_content ? ' has_hook_content' : ''; ?>">
			
				<!-- <div class="list-wrap-inner"> -->
					<div class="item-avatar">
						<span data-toggle="member-dropdown-<?php bp_member_user_id() ; ?>"><?php 
							bb_user_status( bp_get_member_user_id() );
							bp_member_avatar( bp_nouveau_avatar_args() );?></span>
						<div class="list-title member-name"><?php bp_member_name(); ?></div>
						<?php 
						$type = 'member';
						$source = bp_get_member_user_id();
						$person = $source;
						echo bfc_member_dropdown( $type, $source, $person, $follow_class )
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

	<?php endwhile; ?>

	</ul>

	<?php bp_nouveau_pagination( 'bottom' ); ?>
	<script> 
		jQuery(document).foundation();
	</script>


<?php

else :

	bp_nouveau_user_feedback( 'members-loop-none' );

endif;
?>

<?php bp_nouveau_after_loop(); ?>
