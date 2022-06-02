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

	<li class="bs-item-wrap bs-header-item align-items-center no-hover-effect">
		<div class="flex-1">
			<h2 class="bs-section-title">
			<?php
			if ( bbp_is_topic_tag() ) {
				$bbp_topic_tag = get_query_var( 'bbp_topic_tag' );

				if ( function_exists( 'bbp_is_shortcode' ) && bbp_is_shortcode() && bbp_is_query_name( 'bbp_topic_tag' ) && ! empty( bbpress()->current_topic_tag_id ) ) {
					$bbp_tag_term = get_term( bbpress()->current_topic_tag_id );
					if ( ! empty( $bbp_tag_term->name ) ) {
						$bbp_topic_tag = $bbp_tag_term->name;
					}
				}

				echo sprintf(
					/* translators: Discussions tags. */
					wp_kses_post( __( "Discussions tagged with '%s' ", 'bfcommons-theme' ) ),
					wp_kses_post( $bbp_topic_tag )
				);
			} else {
				if ( function_exists( 'bbp_is_shortcode' ) && bbp_is_shortcode() && bbp_is_query_name( 'bbp_view' ) && 'popular' === bbpress()->current_view_id ) {
					esc_html_e( 'Popular Discussions', 'bfcommons-theme' );
				} elseif ( function_exists( 'bbp_is_shortcode' ) && bbp_is_shortcode() && bbp_is_query_name( 'bbp_view' ) && 'no-replies' === bbpress()->current_view_id ) {
					esc_html_e( 'Unanswered Discussions', 'bfcommons-theme' );
				} else {
					esc_html_e( 'All Discussions', 'bfcommons-theme' );
				}
			}
			?>
			</h2>
		</div>
	</li>

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
