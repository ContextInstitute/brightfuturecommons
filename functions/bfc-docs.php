<?php 

add_filter('nav_menu_css_class', 'bp_docs_is_parent', 10 , 2);

function bp_docs_is_parent( $classes, $item) {
	if (bp_docs_is_bp_docs_page() && $item->title == 'Docs' && bp_current_component() != 'groups' && !(bfc_doc_has_tag ('bfcom-help') || 'bfcom-help' == urldecode( $_GET['bpd_tag'] ))) {
		$classes[] = 'current_page_parent';
	}
	return $classes;
}

add_filter('nav_menu_css_class', 'bp_docs_help_is_parent', 10 , 2);

function bp_docs_help_is_parent( $classes, $item) {
	if (bp_docs_is_bp_docs_page() && $item->title == 'Help' && bp_current_component() != 'groups' && (bfc_doc_has_tag ('bfcom-help') || 'bfcom-help' == urldecode( $_GET['bpd_tag'] ))) {
		$classes[] = 'current_page_parent';
	}
	return $classes;
}

add_filter( 'bp_docs_allow_comment_section', '__return_true', 12 ); 
// add_filter( 'bp_docs_do_theme_compat', '__return_false', 12 );

add_filter('bp_docs_wp_editor_args', 'bfc_docs_editor_args',10,1);

function bfc_docs_editor_args( $args ) {
		$wp_editor_args = array($args);

		$wp_editor_args['quicktags'] = false;
		$wp_editor_args['media_buttons'] = true;

	return $wp_editor_args;
}

// add_filter('bp_docs_groups_enable_nav_item', '__return_true', 12 );

add_filter( 'bp_docs_get_access_options', 'bfc_remove_anyone_access',12,1 );

function bfc_remove_anyone_access ($options) {
	unset($options[10]);
	if (isset ($options[20])) {$options[20]['default'] = 1;}
	return $options;
}

/*
From https://wordpress.stackexchange.com/questions/380612/how-to-list-the-authors-of-all-revisions
 */

function bfc_docs_get_authors($post_id) {
	$revision = wp_get_post_revisions($post_id);

	$post_author_id = array();

	foreach ($revision as $key => $value) {
		

		// Check Id Already Exists in Array 
		
		if ( ! in_array($value->post_author, $post_author_id)) {

			// Store Author Id 
			$post_author_id[] = $value->post_author;

			// Do other stuff, like ...

			// $get_author['author_link'] = get_author_posts_url($value->post_author); // Author Link
			// $get_author['author_name'] = get_the_author_meta( 'display_name', $value->post_author ); // Author Display Name


		}
	}
	return $post_author_id;
}

function bfc_doc_has_tag ($tag_name) {
	global $bp;

    $taxonomies = (array)$bp->bp_docs->docs_tag_tax_name;
	$has_tag = false;
    foreach ( $taxonomies as $tax_name ) {
        $html    = '';
        $tagtext = array();
        $tags 	 = wp_get_post_terms( get_the_ID(), $tax_name );

		foreach ( $tags as $tag ) {
            if ( $tag_name == $tag->name ) {$has_tag = true;}
        }
	}
	return $has_tag;
}

function bfc_show_terms() {
    global $bp;

    $taxonomies = (array)$bp->bp_docs->docs_tag_tax_name;

    foreach ( $taxonomies as $tax_name ) {
        $html    = '';
        $tagtext = array();
        $tags 	 = wp_get_post_terms( get_the_ID(), $tax_name );

        foreach ( $tags as $tag ) {
            $tagtext[] = bp_docs_get_tag_link( array( 'tag' => $tag->name ) );
        }

        if ( ! empty( $tagtext ) ) {
            $html = '<p>' . sprintf( __( 'Tags: %s', 'buddypress-docs' ), implode( ', ', $tagtext ) ) . '</p>';
        }

        echo apply_filters( 'bp_docs_taxonomy_show_terms', $html, $tagtext );
    }
}

function bfc_show_parent() {
    global $post, $wp_query;

    $html = '';
    $parent = false;

    if ( ! empty( $post->post_parent ) ) {
        $parent = get_post( $post->post_parent );
        if ( !empty( $parent->ID ) ) {
            $parent_url = bp_docs_get_doc_link( $parent->ID );
            $parent_title = $parent->post_title;

            $html = "<p>" . __( 'Parent: ', 'buddypress-docs' ) . "<a href=\"$parent_url\" title=\"$parent_title\">$parent_title</a></p>";
        }
    }

    echo apply_filters( 'bp_docs_hierarchy_show_parent', $html, $parent );
}


function bfc_docs_display_folder_meta() {
	$doc_id    = get_the_ID();
	$folder_id = bp_docs_get_doc_folder( $doc_id );

	if ( ! $folder_id ) {
		return;
	}

	$folder = get_post( $folder_id );

	if ( ! is_a( $folder, 'WP_Post' ) || 'bp_docs_folder' !== $folder->post_type ) {
		return;
	}

	echo sprintf(
		'<p class="folder-meta" data-folder-id="%d">%s<a href="%s">%s</a>',
		esc_attr( $folder_id ),
		bfc_docs_folder_icon (), // bp_docs_get_genericon( 'category', $folder_id ),
		esc_url( bp_docs_get_folder_url( $folder_id ) ),
		esc_attr( $folder->post_title )
	);
}

function bfc_doc_authors( $post_id = false ) {

	if ( ! $post_id ) {
		return '';
	}

	$post_author_ids = array();

    $revision = wp_get_post_revisions($post_id);

	foreach ($revision as $key => $value) {
		// Check Id Already Exists in Array 
		if ( ! in_array($value->post_author, $post_author_ids)) {
			// Store Author Id 
			$post_author_ids[] = $value->post_author;
		}
	}

	$post = get_post($post_id);

	if ( ! in_array($post->post_author, $post_author_ids)) {
		// Store Author Id 
		$post_author_ids[] = $post->post_author;
	}

	// $post_author_ids = array_reverse($post_author_ids);

    if ( ! empty( $post_author_ids ) ) {
		$is_follow_active = bp_is_active('activity') && function_exists('bp_is_activity_follow_active') && bp_is_activity_follow_active();
		$follow_class = $is_follow_active ? 'follow-active' : '';		
		foreach ( $post_author_ids as $author_id ) {
			$avatar = bp_core_fetch_avatar(
				array(
					'item_id'    => $author_id,
					'avatar_dir' => 'avatars',
					'object'     => 'user',
					'type'       => 'thumb',
					'html'       => false,
				)
			);
			$uname = esc_attr( bp_core_get_user_displayname( $author_id ) );
			$type = 'doc';
			$instance_id = $author_id;
			$person = $instance_id;
			?>
			<span data-bp-item-id="<?php echo $person; ?>" data-bp-item-component="members">
				<div class="bfc-tooltip">
					<span class="bfc-dropdown-span" data-toggle="doc-dropdown-<?php echo esc_attr( $author_id ); ?>"><img src="<?php echo $avatar; ?>" alt="<?php echo $uname; ?>" class=".bfc-rounded"/></span>
				<?php
				if (bp_docs_is_single_doc()) {
				echo bfc_member_dropdown( $type, $instance_id, $person, $follow_class );
				} ?>
				<span class="bfc-tooltiptext"><a href="/members/<?php echo bp_core_get_username( $author_id );?>"><?php echo $uname; ?></a></span>

				</div>
			</span>
		<?php }
	}
}


add_filter( 'bp_docs_enable_folders_for_current_context', 'bfc_docs_enable_folders' );

function bfc_docs_enable_folders () {
	return (function_exists( 'bp_is_group' ) && bp_is_group() && isset( $_GET['folder'] )) ;
}

add_filter( 'bp_docs_get_container_classes', 'bfc_docs_add_single_doc_class' );

function bfc_docs_add_single_doc_class ($classes) {
	if (bp_docs_is_single_doc() || bp_docs_is_doc_create()) {
		$classes[] = 'bp-docs-single';
	}
	return $classes;
}

add_filter('bp_docs_tax_query','bfc_remove_help_docs');

function bfc_remove_help_docs ($query) {
	if (bp_docs_is_global_directory() && 'bfcom-help' != urldecode( $_GET['bpd_tag'] )) {
		$query[] = array ('taxonomy' => 'bp_docs_tag', 'field'    => 'slug', 'terms'    => 'bfcom-help', 'operator' => 'NOT IN');
	}
	return $query;
}

function bfc_docs_action_links () {
    
    $can_edit = current_user_can( 'bp_docs_edit', get_the_ID() );
    $can_view_history = current_user_can( 'bp_docs_view_history', get_the_ID() ) && defined( 'WP_POST_REVISIONS' ) && WP_POST_REVISIONS && boolval( wp_get_post_revisions( get_the_ID() ));
    $can_manage_trash = current_user_can( 'manage', get_the_ID() ) && bp_docs_is_doc_trashed( get_the_ID() );

    // if ( $can_edit || $can_view_history || $can_manage_trash) {
        $links = array();

        $links[] = '<a href="' . bp_docs_get_doc_link() . '" class="bb-icon-book-open bb-icon-l" title="' . __( "Read", "buddypress-docs" ) . '"></a>';
    
        if ( $can_edit ) {
            $links[] = '<a href="' . bp_docs_get_doc_edit_link() . '" class="bb-icon-edit-square bb-icon-l" title="' . __( 'Edit', 'buddypress-docs' ) . '"></a>';
        }
    
        if ( $can_view_history ) {
            $links[] = '<a href="' . bp_docs_get_doc_link() . BP_DOCS_HISTORY_SLUG . '" class="bb-icon-clock bb-icon-l" title="' . __( 'History', 'buddypress-docs' ) . '"></a>';
        }
    
        if ( $can_manage_trash ) {
            $links[] = '<a href="' . bp_docs_get_remove_from_trash_link( get_the_ID() ) . '" class="bb-icon-trash-restore bb-icon-l delete confirm" title="' . __( 'Untrash', 'buddypress-docs' ) . '"></a>';
        }
    
        $links = apply_filters( 'bfc_docs_action_links', $links, get_the_ID() );
    
        echo implode( '  ', $links );

    // }
}

function bfc_docs_location ( $args = array() ){
    $d_doc_id = 0;
    if ( bp_docs_is_existing_doc() ) {
        $d_doc_id = get_queried_object_id();
    }

    $r = wp_parse_args( $args, array(
        'include_doc' => false,
        'doc_id'      => $d_doc_id,
    ) );

    $crumbs = array();

	$group_id = bp_docs_get_associated_group_id( $r['doc_id'] );
	$user_has_access = current_user_can( 'bp_moderate' ) || groups_is_user_member( bp_loggedin_user_id(), $group_id );

    $doc = get_post( $r['doc_id'] );

    if ( $r['include_doc'] ) {
        $crumbs[] = sprintf(
            '<span class="breadcrumb-current">%s%s</span>',
            bp_docs_get_genericon( 'document', $r['doc_id'] ),
            $doc->post_title
        );
    }

    $folder_id = 0;

	if ( is_a( $doc, 'WP_Post' ) ) {
		$folder_id = bp_docs_get_doc_folder( $doc->ID );
	} else if ( bp_docs_is_existing_doc() ) {
		$folder_id = bp_docs_get_doc_folder( get_queried_object_id() );
	} else if ( isset( $_GET['folder'] ) ) {
		$folder_id = intval( $_GET['folder'] );
	}

	$folder_id = intval( $folder_id );

	$descendants    = array();
	$this_folder_id = $folder_id;

	// Recurse up the tree
	while ( 0 !== $this_folder_id ) {
		$folder = get_post( $this_folder_id );
		$descendants[] = array(
			'id'     => $folder->ID,
			'parent' => $folder->post_parent,
			'name'   => $folder->post_title,
		);

		$this_folder_id = intval( $folder->post_parent );
	}

	// Sort from top to bottom
	$descendants = array_reverse( $descendants );

	$breadcrumb_items = array();
	foreach ( $descendants as $d ) {
		if ( $user_has_access ) {
			$crumbs[] = sprintf(
				'<span class="bp-docs-folder-breadcrumb" id="bp-docs-folder-breadcrumb-%s">%s<a href="%s">%s</a></span>',
				$d['id'],
				bfc_docs_folder_icon (), // bp_docs_get_genericon( 'category', $d['id'] ),
				esc_url( bp_docs_get_folder_url( $d['id'] ) ),
				esc_html( $d['name'] )
			);
		} else {
			$crumbs[] = sprintf(
				'<span class="bp-docs-folder-breadcrumb" id="bp-docs-folder-breadcrumb-%s">%s%s</span>',
				$d['id'],
				bfc_docs_folder_icon (), // bp_docs_get_genericon( 'category', $d['id'] ),
				esc_html( $d['name'] )
			);

		}
	}

	$sep = bp_docs_get_breadcrumb_separator( 'doc' );

	if( bp_current_component() != 'groups') {
		$group_id = null;

		if ( is_a( $doc, 'WP_Post' ) ) {
			$group_id = bp_docs_get_associated_group_id( $doc->ID );
		} else if ( bp_docs_is_existing_doc() ) {
			$group_id = bp_docs_get_associated_group_id( get_queried_object_id() );
		}

		if ( $group_id ) {
			$group = groups_get_group( array(
				'group_id' => $group_id,
			) );
		}



		if ( empty( $group->name ) ) {
			$location =  '<span class="directory-breadcrumb-separator" style="padding-left:0px;" >' . $sep . '</span> Sitewide';
			return $location;
		}

		if ( $user_has_access ) {
			$group_crumbs = array(
				sprintf(
					'<a href="%s">%s</a>',
					bp_get_group_permalink( $group ) . bp_docs_get_slug() . '/',
					/* translators: group name */
					sprintf( esc_html__( '%s&#8217;s Docs', 'buddypress-docs' ), esc_html( $group->name ) )
				),
			);
		} else {
			$group_crumbs = array(sprintf( esc_html__( '%s&#8217;s Docs', 'buddypress-docs' ), esc_html( $group->name ) ));    
		}
	
		$crumbs = array_merge( $group_crumbs, $crumbs );
	}

	if(count($crumbs)) {
		$location =  '<span class="directory-breadcrumb-separator" style="padding-left:0px;" >' . $sep . '</span> ';

		$location .=  implode( ' <span class="directory-breadcrumb-separator">' . $sep . '</span> ', $crumbs );

		return $location;
	}
}

	/**
	 * Get the info header for a list of docs 
	 * 
	 * Adapted from buddypress-docs/includes/templatetags.php > bp_docs_get_info_header()
	 *
	 * Contains things like tag filters
	 *
	 * @since 1.0-beta
	 *
	 * @param int $doc_id optional The post_id of the doc
	 * @return str Permalink for the group doc
	 */
	function bfc_docs_get_info_header() {
		do_action( 'bp_docs_before_info_header' );

		$filters = bp_docs_get_current_filters();

		// Set the message based on the current filters
		if ( empty( $filters ) ) {
			$message = __( 'You are viewing the unfiltered list.', 'buddypress-docs' );
		} else {
			$message = array();

			$message = apply_filters( 'bp_docs_info_header_message', $message, $filters );

			$message = implode( "<br />", $message );

			// We are viewing a subset of docs, so we'll add a link to clear filters
			// Figure out what the possible filter query args are.
			$filter_args = apply_filters( 'bp_docs_filter_types', array() );
			$filter_args = wp_list_pluck( $filter_args, 'query_arg' );
			$filter_args = array_merge( $filter_args, array( 'search_submit', 'folder' ) );

			$view_all_url = remove_query_arg( $filter_args );

			// Try to remove any pagination arguments.
			$view_all_url = remove_query_arg( 'p', $view_all_url );
			$view_all_url = preg_replace( '|page/[0-9]+/|', '', $view_all_url );

			$message .= '<br>' . sprintf( __( '<strong><a href="%s" title="View All Docs">View the unfiltered list</a></strong>', 'buddypress-docs' ), $view_all_url );
		}

		?>

		<p class="currently-viewing"><?php echo $message ?></p>

		<?php if ( $filter_titles = bp_docs_filter_titles() ) : ?>
			<div class="docs-filters">
				<p id="docs-filter-meta">
					<?php printf( __( 'Filter by: %s', 'buddypress-docs' ), $filter_titles ) ?>
				</p>

				<div id="docs-filter-sections">
					<?php do_action( 'bp_docs_filter_sections' ) ?>
				</div>
			</div>

			<div class="clear"> </div>
		<?php endif ?>
		<?php
	}

/**
 * Display list of a Docs's revisions. Borrowed heavily from WP's wp_list_post_revisions()
 *
 * @since 1.1
 *
 * @uses wp_get_post_revisions()
 * @uses wp_post_revision_title()
 * @uses get_edit_post_link()
 * @uses get_the_author_meta()
 *
 * @param int|object $post_id Post ID or post object.
 * @param string|array $args See description {@link wp_parse_args()}.
 * @return null
 */
function bfc_docs_list_post_revisions( $post_id = 0, $args = null ) {
	global $bp;

	if ( !$post = get_post( $post_id ) )
		return;

	$defaults = array(
		'parent' => false,
		'right'  => $bp->bp_docs->history->right,
		'left'   => $bp->bp_docs->history->left,
		'format' => 'form-table',
		'type'   => 'all'
	);

	extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

	$revisions = array();

	switch ( $type ) {
		case 'autosave' :
			if ( !$autosave = wp_get_post_autosave( $post->ID ) )
				return;
			$revisions = array( $autosave );
			break;
		case 'revision' : // just revisions - remove autosave later
		case 'all' :
		default :
			$revisions = wp_get_post_revisions( $post->ID );
			break;
	}

	$revisions[] = $post;

	/* translators: post revision: 1: when, 2: author name */
	$titlef = _x( '%1$s by %2$s', 'post revision', 'buddypress-docs' );

	if ( $parent )
		array_unshift( $revisions, $post );

	$rows = $right_checked = '';
	$class = false;
	$can_edit_post = current_user_can( 'bp_docs_edit' );
	foreach ( $revisions as $revision ) {
		if ( 'revision' === $type && wp_is_post_autosave( $revision ) )
			continue;

		$base_url = trailingslashit( get_permalink() . BP_DOCS_HISTORY_SLUG );

		$date = '<a href="' . add_query_arg( 'revision', $revision->ID ) . '">' . bp_format_time( strtotime( $revision->post_date ), false, false /* don't double localize time */ ) . '</a>';
		$name = bp_core_get_userlink( $revision->post_author );

		if ( 'form-table' == $format ) {
			if ( $left )
				$left_checked = $left == $revision->ID ? ' checked="checked"' : '';
			else
				$left_checked = $right_checked ? ' checked="checked"' : ''; // [sic] (the next one)
			$right_checked = $right == $revision->ID ? ' checked="checked"' : '';

			$class = $class ? '' : " class='alternate'";

			if ( $post->ID != $revision->ID && $can_edit_post )
				$actions = '<a class="confirm" href="' . wp_nonce_url( add_query_arg( array( 'revision' => $revision->ID, 'action' => 'restore' ), $base_url ), "restore-post_$post->ID|$revision->ID" ) . '">' . __( 'Restore', 'buddypress-docs' ) . '</a>';
			else
				$actions = '';

			$rows .= "<tr$class>\n";
			$rows .= "\t<th style='white-space:nowrap;text-align:center' scope='row'><input type='radio' name='left' value='$revision->ID'$left_checked id='left-$revision->ID' /><label class='screen-reader-text' for='left-$revision->ID'>" . __( 'Old', 'buddypress-docs' ) . "</label></th>\n";
			$rows .= "\t<th style='white-space:nowrap;text-align:center' scope='row'><input type='radio' name='right' value='$revision->ID'$right_checked id='right-$revision->ID' /><label class='screen-reader-text' for='right-$revision->ID'>" . __( 'New', 'buddypress-docs' ) . "</label></th>\n";
			$rows .= "\t<td>$date</td>\n";
			$rows .= "\t<td>$name</td>\n";
			$rows .= "\t<td class='action-links'>$actions</td>\n";
			$rows .= "</tr>\n";
		} else {
			$title = sprintf( $titlef, $date, $name );
			$rows .= "\t<li>$title</li>\n";
		}
	} ?>

	<form action="" method="get">

	<div class="tablenav">
		<div class="alignleft">
			<input type="submit" class="button-secondary" value="<?php esc_attr_e( 'Compare Revisions', 'buddypress-docs' ); ?>" />
			<input type="hidden" name="action" value="diff" />
			<input type="hidden" name="post_type" value="<?php echo esc_attr($post->post_type); ?>" />
		</div>
	</div>

	<br class="clear" />

	<table class="widefat post-revisions" cellspacing="0" id="post-revisions">
		<col />
		<col />
		<col style="width: 33%" />
		<col style="width: 33%" />
		<col style="width: 33%" />
	<thead>
	<tr>
		<th scope="col"><?php /* translators: column name in revisons */ _e( 'Old', 'buddypress-docs' ); ?></th>
		<th scope="col"><?php /* translators: column name in revisons */ _e( 'New', 'buddypress-docs' ); ?></th>
		<th scope="col"><?php /* translators: column name in revisons */ _e( 'Date Created', 'buddypress-docs' ); ?></th>
		<th scope="col"><?php _e( 'Author', 'buddypress-docs' ); ?></th>
		<th scope="col" class="action-links"><?php _e( 'Actions', 'buddypress-docs' ); ?></th>
	</tr>
	</thead>
	<tbody>

	<?php echo $rows; ?>

	</tbody>
	</table>

	</form>

<?php

}

function bfc_docs_folder_icon (){
	$icon_markup = '<i class="bb-icon-folder-open bb-icon-l" style="font-size: 20px; line-height: 20px;" ></i>';
	return $icon_markup;
}
?>
