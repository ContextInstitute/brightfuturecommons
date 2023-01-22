<?php
/**
 * Template for displaying the search results of the reply
 *
 * This template can be overridden by copying it to yourtheme/buddypress/search/loop/reply.php.
 *
 * @package BuddyBoss\Core
 * @since   BuddyBoss 1.0.0
 * @version 1.0.0
 */

$doc_id= get_the_ID();
$doc = get_post( $doc_id);
?>
<li class="bp-search-item bp-search-item_reply">
	<div class="list-wrap">
		<div class="item-avatar">
			<a href="<?php bbp_reply_url( $doc_id); ?>" class="bp-search-item_reply_link">
				<?php
				$args   = array(
					'type'    => 'avatar',
					'post_id' => $doc_id,
				);
				$avatar = bbp_get_reply_author_link( $args );

				if ( $avatar ) {
					echo wp_kses_post( $avatar );
				} else {
					?>
					<i class="bb-icon-f <?php echo esc_attr( bp_search_get_post_thumbnail_default( get_post_type(), 'icon' ) ); ?>"></i>
					<?php
				}
				?>
			</a>
		</div>

		<div class="item">
			<div class="entry-title item-title">
				<a href="<?php bbp_topic_permalink( $doc_id ); ?>"><?php echo $doc->post_title; ?></a>
				<!-- <?php esc_html_e( 'replied to a discussion', 'buddyboss' ); ?> -->
			</div>
			<div class="entry-content entry-summary">
				<?php echo wp_kses_post( wp_trim_words( bbp_get_reply_content( $doc_id), 30, '...' ) ); ?>
			</div>
			<div class="entry-meta">
				<span><?php echo esc_html__( 'Started by ', 'buddyboss' ) . esc_html( bp_core_get_user_displayname( bbp_get_topic_author_id( $doc_id ) ) ); ?></span>
				<span class="middot">&middot;</span>				
				<span class="datetime">
					<?php echo esc_html__( 'Latest edit ', 'buddyboss' ); ?>
					<?php bbp_reply_post_date( $doc_id, true ); ?>
				</span>
			</div>
		</div>
	</div>
</li>
