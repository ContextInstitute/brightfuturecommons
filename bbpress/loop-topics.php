<?php

/**
 * Topics Loop
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<?php do_action( 'bbp_template_before_topics_loop' ); ?>

<ul id="bbp-forum-<?php bbp_forum_id(); ?>" class="bbp-topics1 bs-item-list bs-forums-items list-view">



	<?php
	while ( bbp_topics() ) :
		bbp_the_topic();

		bbp_get_template_part( 'loop', 'topic-list' );
	endwhile;
	?>

</ul><!-- #bbp-forum-<?php bbp_forum_id(); ?> -->

<script>
	jQuery(document).foundation();
</script>

<?php do_action( 'bbp_template_after_topics_loop' ); ?>
