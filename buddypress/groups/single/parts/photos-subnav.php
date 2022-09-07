<?php
/**
 * BFC Single Groups Photos Sub Nav
 *
 */

global $bp;
$bp_nouveau = bp_nouveau();
$group_link = bp_get_group_permalink( $bp->groups->current_group);
$photo_link = trailingslashit( $group_link . 'photos' );
$album_link = trailingslashit( $group_link . 'albums' );
$photo_selected = ' current selected';
$album_selected = '';
if ('albums' == bp_current_action()) {
	$album_selected = ' current selected';
	$photo_selected = '';
}
?>

<nav class="bp-navs bp-subnavs no-ajax user-subnav bb-subnav-plain" id="subnav" role="navigation" aria-label="Sub Menu">
	<ul class="subnav">

		
			<li id="photos-group-li" class="bp-personal-sub-tab<?php echo $photo_selected; ?>" data-bp-user-scope="photos">
				<a href="<?php echo $photo_link; ?>" id="photos" class="">
					Images
									</a>
			</li>

		
			<li id="albums-group-li" class="bp-personal-sub-tab<?php echo $album_selected; ?>" data-bp-user-scope="albums">
				<a href="<?php echo $album_link; ?>" id="albums" class="">
					Albums
									</a>
			</li>

		
	</ul>
</nav>      
