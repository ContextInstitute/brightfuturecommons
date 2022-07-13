<?php
/**
 * The template for displaying activity post case privacy
 *
 * This template can be overridden by copying it to yourtheme/buddypress/common/js-templates/activity/parts/bp-activity-post-case-privacy.php.
 *
 * @since   BuddyBoss 1.8.6
 * @version 1.8.6
 */

?>
<script type="text/html" id="tmpl-activity-post-case-privacy">
	
	<?php 
		$visibility_levels = bp_activity_get_visibility_levels();
		if (!isset ($visibility_levels['public'])) : ?>
			<# if ( 'public' == data.privacy ) {  data.privacy = 'loggedin';  } #>
			<?php if (bp_loggedin_user_id() == bp_displayed_user_id()) : ?>
			<div id="bp-activity-privacy-point" class="{{data.privacy}}" data-bp-tooltip-pos="up" data-bp-tooltip="<?php esc_html_e( 'Set by album privacy', 'bfcommons-theme' ); ?>">
				<span class="privacy-point-icon"></span>
				<span class="bp-activity-privacy-status">
					<# if ( data.privacy === 'loggedin' ) { #>
						<?php esc_html_e( 'Anyone In The Network', 'bfcommons-theme' ); ?>
					<# } else if ( data.privacy === 'friends' ) { #>
						<?php esc_html_e( 'My Connections', 'bfcommons-theme' ); ?>
					<# } else if ( data.privacy === 'onlyme' ) { #>
						<?php esc_html_e( 'Only Me', 'bfcommons-theme' ); ?>
					<# } else { #>
						<?php esc_html_e( 'Group', 'bfcommons-theme' ); ?>
					<# } #>
				</span>
				<i class="bb-icon-chevron-down"></i>
			</div>
			<?php endif; 
		else: ?>
			<div id="bp-activity-privacy-point" class="{{data.privacy}}" data-bp-tooltip-pos="up" data-bp-tooltip="<?php esc_html_e( 'Set by album privacy', 'bfcommons-theme' ); ?>">
				<span class="privacy-point-icon"></span>
				<span class="bp-activity-privacy-status">
					<# if ( data.privacy === 'public' ) {  #>
						<?php esc_html_e( 'Public', 'bfcommons-theme' ); ?>
					<# } else if ( data.privacy === 'loggedin' ) { #>
						<?php esc_html_e( 'Anyone In The Network', 'bfcommons-theme' ); ?>
					<# } else if ( data.privacy === 'friends' ) { #>
						<?php esc_html_e( 'My Connections', 'bfcommons-theme' ); ?>
					<# } else if ( data.privacy === 'onlyme' ) { #>
						<?php esc_html_e( 'Only Me', 'bfcommons-theme' ); ?>
					<# } else { #>
						<?php esc_html_e( 'Group', 'bfcommons-theme' ); ?>
					<# } #>
				</span>
				<i class="bb-icon-chevron-down"></i>
			</div>
		<?php endif;
	?>
</script>
