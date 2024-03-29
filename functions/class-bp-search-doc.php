<?php
/**
 * @todo add description
 *
 * @package BuddyBoss\Search
 * @since BuddyBoss 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BP_Search_Doc' ) ) :

	/**
	 * BuddyPress Global Search  - search posts class
	 */
	class BP_Search_Doc extends Bp_Search_Type {
		private $cpt_name;
		private $search_type;

		/**
		 * A real constructor. Since we do want multiple copies of this class.
		 * The idea is to have one object for each searchable custom post type.
		 *
		 * @since BuddyBoss 1.0.0
		 */
		public function __construct( $cpt_name, $search_type ) {
			$this->cpt_name    = $cpt_name;
			$this->search_type = $search_type;

			add_action( "bp_search_settings_item_{$this->cpt_name}", array( $this, 'print_search_options' ) );
		}

		public function sql( $search_term, $only_totalrow_count = false ) {

			global $wpdb;

			$bp_prefix = bp_core_get_table_prefix();

			$query_placeholder = array();

			$sql = ' SELECT ';

			if ( $only_totalrow_count ) {
				$sql .= ' COUNT( DISTINCT id ) ';
			} else {
				$sql                .= " DISTINCT id , %s as type, post_title LIKE '%%%s%%' AS relevance, post_date as entry_date  ";
				$query_placeholder[] = $this->search_type;
				$query_placeholder[] = $search_term;
			}

			$sql .= " FROM {$wpdb->prefix}posts p";

			$tax        = array();
			$taxonomies = get_object_taxonomies( $this->cpt_name );
			foreach ( $taxonomies as $taxonomy ) {
				if ( bp_is_search_post_type_taxonomy_enable( $taxonomy, $this->cpt_name ) ) {
					$tax[] = $taxonomy;
				}
			}

			// Tax query left join.
			if ( ! empty( $tax ) ) {
				$sql .= " LEFT JOIN {$wpdb->term_relationships} r ON p.ID = r.object_id ";
			}

			$sql                .= " WHERE 1=1 AND ( p.post_title LIKE %s OR ExtractValue(p.post_content, '//text()') LIKE %s ";
			$query_placeholder[] = '%' . $search_term . '%';
			$query_placeholder[] = '%' . $search_term . '%';

			// Tax query.
			if ( ! empty( $tax ) ) {

				$tax_in_arr = array_map(
					function( $t_name ) {
							return "'" . $t_name . "'";
					},
					$tax
				);

				$tax_in = implode( ', ', $tax_in_arr );

				$sql                .= " OR  r.term_taxonomy_id IN (SELECT tt.term_taxonomy_id FROM {$wpdb->term_taxonomy} tt INNER JOIN {$wpdb->terms} t ON
					  t.term_id = tt.term_id WHERE ( t.slug LIKE %s OR t.name LIKE %s ) AND  tt.taxonomy IN ({$tax_in}) )";
				$query_placeholder[] = '%' . $search_term . '%';
				$query_placeholder[] = '%' . $search_term . '%';
			}

			// If post is not attachment Post should be publish &
			// else attachment should be inherit and that not include media and document as we have separate search for that.
			$sql .= ' ) AND p.post_type = %s';
			if ( 'attachment' === $this->cpt_name ) {
				$sql .= " AND p.post_status = 'inherit' AND p.ID NOT IN ( SELECT post_id FROM {$wpdb->postmeta} pm WHERE pm.`meta_key` IN ( 'bp_media_upload', 'bp_document_upload', 'bp_video_upload' ) )";
			} else {
				$sql .= " AND p.post_status = 'publish'";
			}

			$query_placeholder[] = $this->cpt_name;

			$sql = $wpdb->prepare( $sql, $query_placeholder );

			return apply_filters(
				'BP_Search_Doc_sql',
				$sql,
				array(
					'post_type'           => $this->cpt_name,
					'search_term'         => $search_term,
					'only_totalrow_count' => $only_totalrow_count,
				)
			);
		}

		protected function generate_html( $template_type = '' ) {
			$post_ids = array();
			foreach ( $this->search_results['items'] as $item_id => $item_html ) {
				$post_ids[] = $item_id;
			}

			// now we have all the posts
			// lets do a wp_query and generate html for all posts.
			$qry      = new WP_Query(
				array(
					'post_type'      => $this->cpt_name,
					'post__in'       => $post_ids,
					'post_status'    => ( 'attachment' === $this->cpt_name ) ? 'inherit' : 'publish',
					'posts_per_page' => 20,
				)
			);
			$template = bp_locate_template( "search/loop/{$this->cpt_name}.php" ) ? "loop/{$this->cpt_name}" : 'loop/post';

			if ( $qry->have_posts() ) {
				while ( $qry->have_posts() ) {
					$qry->the_post();
					$result = array(
						'id'    => get_the_ID(),
						'type'  => $this->search_type,
						'title' => get_the_title(),
						'html'  => bp_search_buffer_template_part( $template, $template_type, false ),
					);

					$this->search_results['items'][ get_the_ID() ] = $result;
				}
			}
			wp_reset_postdata();
		}


		/**
		 * What taxonomy  should be searched on?
		 *
		 * Prints options to search through all registered taxonomies with give
		 * post type e.g $this->cpt_name
		 */
		public function print_search_options( $items_to_search ) {
			echo "<div class='wp-{$this->cpt_name}-fields' style='margin: 10px 0 10px 30px'>";

			$cpt_taxonomy = get_object_taxonomies( $this->cpt_name );

			foreach ( $cpt_taxonomy as $tax ) {

				$label   = ucwords( str_replace( '_', ' ', $tax ) );
				$value   = $this->search_type . '-tax-' . $tax;
				$checked = ! empty( $items_to_search ) && in_array( $value, $items_to_search ) ? ' checked' : '';

				echo "<label><input type='checkbox' value='{$value}' name='bp_search_plugin_options[items-to-search][]' {$checked}>{$label}</label><br>";
			}

			echo '</div><!-- .wp-user-fields -->';
		}

		/**
		 * Get total count for the matched result.
		 *
		 * @param string $search_term Search term will be like whatever enter for search.
		 * @param string $search_type
		 *
		 * @return mixed|string|null
		 */
		public function get_total_match_count( $search_term, $search_type = '' ) {
			$this->search_term = $search_term;// save it for future reference may be.

			global $wpdb;
			static $bbp_search_term = array();
			$cache_key = 'bb_search_term_total_match_count_' . sanitize_title( $search_term );
			if ( ! empty( $search_type ) ) {
				$cache_key .= sanitize_title( $search_type );
			}
			if ( ! isset( $bbp_search_term[ $cache_key ] ) ) {
				$sql    = $this->sql( $search_term, false );
				$full_results = $wpdb->get_results( $sql );
				$trimmed_results = apply_filters('bfc_docs_get_total_match_count', $full_results );
				$result = count($trimmed_results);
				$bbp_search_term[ $cache_key ] = $result;
			} else {
				$result = $bbp_search_term[ $cache_key ];
			}

			return $result;
		}

	}


	// End class BP_Search_Doc.

endif;

