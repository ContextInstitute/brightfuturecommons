<?php
/**
 * Template Name: Home
 *
 * @package BFCom
 */

get_header(); ?>

	<div class="content">

		<div class="inner-content grid-x grid-margin-x grid-padding-x">

			<main class="main small-12 medium-12 large-12 cell" role="main">

			<div class="user-home-page">
				<h2 class="user-home-welcome">
				<span class="user-home-welcome-welcome">Welcome, </span>
				<span class="user-home-welcome-name"><?php echo esc_html( bp_get_user_meta( bp_loggedin_user_id(), 'first_name', true ) ); ?></span></h2>
				<div class="user-home-welcome-tagline"><span>to your personal Bright Future Commons dashboard</span></div>

			<div id="bfc-user-panels" class="bfc-user-panels">
				<?php
				global $bfc_dropdown_prefix;
				$bfc_dropdown_prefix = 'dt';
				?>
				<?php if ( is_active_sidebar( 'user_left_panel' ) ) : ?>
					<div id="bfc-user-panel-left" class="bfc-user-panel widget-area">
						<?php dynamic_sidebar( 'user_left_panel' ); ?>
					</div><!-- #bfc-user-panel-left -->

				<?php endif; ?>
				<?php if ( is_active_sidebar( 'user_center_panel' ) ) : ?>
					<div id="bfc-user-panel-center" class="bfc-user-panel widget-area">
						<?php dynamic_sidebar( 'user_center_panel' ); ?>
					</div><!-- #bfc-user-panel-center -->
				<?php endif; ?>

				<?php if ( is_active_sidebar( 'user_right_panel' ) ) : ?>
					<div id="bfc-user-panel-right" class="bfc-user-panel widget-area">
						<?php dynamic_sidebar( 'user_right_panel' ); ?>
					</div><!-- #bfc-user-panel-right -->
				<?php endif; ?>
			</div>

			<?php $bfc_dropdown_prefix = 'ac';?>
			<ul id="bfc-user-accordion" class="bfc-user-accordion accordion" data-accordion data-allow-all-closed="true">
				<?php if ( is_active_sidebar( 'user_left_panel' ) ) : ?>
					<li class="accordion-item bfc-user-panel-top widget-area" data-accordion-item >
					<a href="#" class="accordion-title">Latest Updates</a>
					<div class="accordion-content" data-tab-content>
						<?php dynamic_sidebar( 'user_left_panel' ); ?>
					</div></li><!-- #bfc-user-panel-le-top -->

				<?php endif; ?>
				<?php if ( is_active_sidebar( 'user_center_panel' ) ) : ?>
					<li class="accordion-item bfc-user-panel-middle widget-area" data-accordion-item >
					<a href="#" class="accordion-title">Forum Posts</a>
					<div class="accordion-content" data-tab-content>
						<?php dynamic_sidebar( 'user_center_panel' ); ?>
					</div></li><!-- #bfc-user-panel-middle -->
				<?php endif; ?>

				<?php if ( is_active_sidebar( 'user_right_panel' ) ) : ?>
					<li class="accordion-item bfc-user-panel-bottom widget-area" data-accordion-item >
					<a href="#" class="accordion-title">Blog Posts</a>
					<div class="accordion-content" data-tab-content>
						<?php dynamic_sidebar( 'user_right_panel' ); ?>
					</div></li><!-- #bfc-user-panel-bottom -->
				<?php endif; ?>
				</ul>

			</div>

			<script>
				jQuery(document).foundation();
			</script>

			</main> <!-- end #main -->

		</div> <!-- end #inner-content -->

	</div> <!-- end #content -->

<?php get_footer(); ?>
