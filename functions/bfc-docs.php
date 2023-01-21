<?php 

add_filter('nav_menu_css_class', 'bp_docs_is_parent', 10 , 2);

function bp_docs_is_parent( $classes, $item) {
	global $post;
	$docs_page = bp_docs_is_bp_docs_page();
	$item_title = $item->title;
	$is_user_page = bp_is_user();
	$cur_comp = bp_current_component();
	if ((!isset($_GET['bp_search'])) && $item->title == 'Docs' && !bp_is_user() && bp_current_component() != 'groups' && !(bfc_doc_has_tag ('bfcom-help') || 'bfcom-help' == urldecode( isset($_GET['bpd_tag'] ) ? $_GET['bpd_tag'] : ''))) {
		$classes[] = 'current_page_parent';
	}
	return $classes;
}

add_filter('nav_menu_css_class', 'bp_docs_help_is_parent', 10 , 2);

function bp_docs_help_is_parent( $classes, $item) {
	if ($item->title == 'Help' && bp_current_component() != 'groups' && (bfc_doc_has_tag ('bfcom-help') || 'bfcom-help' == urldecode( isset($_GET['bpd_tag'] ) ? $_GET['bpd_tag'] : ''))) {
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

add_filter( 'bp_docs_get_access_options', 'bfc_set_default_access_to_creator',20,1 );

function bfc_set_default_access_to_creator ($options) {
	unset($options[10]);
	if (isset ($options[20])) {$options[20]['default'] = 0;}
	if (isset ($options[90])) {$options[90]['default'] = 1;}
	else {
		$options[90] = array(
			'name'  => 'creator',
			'label' => __( 'The Doc author only', 'buddypress-docs' ),
			'default' => 1 // default to 'creator' as a starting point.
		);
	}

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
            $html = '<p>' . sprintf( __( 'Tags: %s', 'bfcommons-theme' ), implode( ', ', $tagtext ) ) . '</p>';
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

            $html = "<p>" . __( 'Parent: ', 'bfcommons-theme' ) . "<a href=\"$parent_url\" title=\"$parent_title\">$parent_title</a></p>";
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

	$post_author_ids = bfc_doc_author_ids($post_id);

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
			<div class="bfc-tooltip" data-bp-item-id="<?php echo $person; ?>" data-bp-item-component="members">
				<span class="bfc-dropdown-span" data-toggle="doc-dropdown-<?php echo esc_attr( $author_id ); ?>"><img src="<?php echo $avatar; ?>" alt="<?php echo $uname; ?>" class=".bfc-rounded"/></span>
				<?php
				if (bp_docs_is_single_doc()) {
				echo bfc_member_dropdown( $type, $instance_id, $person, $follow_class );
				} ?>
				<span class="bfc-tooltiptext"><a href="/members/<?php echo bp_core_get_username( $author_id );?>"><?php echo $uname; ?></a></span>
			</div>
		<?php }
	}
}

function bfc_doc_author_ids ( $post_id = false ) {

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
	return $post_author_ids;
}

add_filter( 'bp_docs_enable_folders_for_current_context', 'bfc_docs_enable_folders' );

function bfc_docs_enable_folders () {
	return true;
	// return (function_exists( 'bp_is_group' ) && bp_is_group()) ;
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
	if (bp_docs_is_global_directory() && 'bfcom-help' != urldecode( isset($_GET['bpd_tag'] ) ? $_GET['bpd_tag'] : '')) {
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

        $links[] = '<a href="' . bp_docs_get_doc_link() . '" class="bb-icon-book-open bb-icon-l" title="' . __( "Read", "bfcommons-theme" ) . '"></a>';
    
        if ( $can_edit ) {
            $links[] = '<a href="' . bp_docs_get_doc_edit_link() . '" class="bb-icon-edit-square bb-icon-l" title="' . __( 'Edit', 'bfcommons-theme' ) . '"></a>';
        }
    
        if ( $can_view_history ) {
            $links[] = '<a href="' . bp_docs_get_doc_link() . BP_DOCS_HISTORY_SLUG . '" class="bb-icon-clock bb-icon-l" title="' . __( 'History', 'bfcommons-theme' ) . '"></a>';
        }
    
        if ( $can_manage_trash ) {
            $links[] = '<a href="' . bp_docs_get_remove_from_trash_link( get_the_ID() ) . '" class="bb-icon-trash-restore bb-icon-l delete confirm" title="' . __( 'Untrash', 'bfcommons-theme' ) . '"></a>';
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
					sprintf( esc_html__( '%s Docs', 'bfcommons-theme' ), esc_html( $group->name ) )
				),
			);
		} else {
			$group_crumbs = array(sprintf( esc_html__( '%s Docs', 'bfcommons-theme' ), esc_html( $group->name ) ));    
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
			$message = __( 'You are viewing the unfiltered list.', 'bfcommons-theme' );
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

			$message .= '<br>' . sprintf( __( '<strong><a href="%s" title="View All Docs">View the unfiltered list</a></strong>', 'bfcommons-theme' ), $view_all_url );
		}

		?>

		<p class="currently-viewing"><?php echo $message ?></p>

		<?php if ( $filter_titles = bp_docs_filter_titles() ) : ?>
			<div class="docs-filters">
				<p id="docs-filter-meta">
					<?php printf( __( 'Filter by: %s', 'bfcommons-theme' ), $filter_titles ) ?>
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

	switch ( $type ) {
		case 'autosave' :
			if ( !$autosave = wp_get_post_autosave( $post->ID ) )
				return;
			$revisions = array( $autosave );
			break;
		case 'revision' : // just revisions - remove autosave later
		case 'all' :
		default :
			if ( !$revisions = wp_get_post_revisions( $post->ID ) )
				return;
			break;
	}

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

		if ( $post->post_modified_gmt == $revision->post_modified_gmt ) {
			$date = bfc_nice_date( strtotime( $post->post_modified_gmt ) );
			// $hide_left = " style='display: none;'";
		} else {
			$date = '<a href="' . add_query_arg( 'revision', $revision->ID ) . '">' . bfc_nice_date( strtotime( $revision->post_date_gmt ) ) . '</a>';
			// $hide_left = '';
		}
		// $date = '<a href="' . add_query_arg( 'revision', $revision->ID ) . '">' . bfc_nice_date( strtotime( $revision->post_date_gmt ) ) . '</a>';
		$name = bp_core_get_userlink( $revision->post_author );

		if ( 'form-table' == $format ) {
			if ( $left && $post->ID != $revision->ID )
				$left_checked = $left == $revision->ID ? ' checked="checked"' : '';
			else
				$left_checked = $right_checked ? ' checked="checked"' : ''; // [sic] (the next one)
			$right_checked = $right == $revision->ID ? ' checked="checked"' : '';

			if ('diff' != bp_docs_history_action() && !bp_docs_history_is_latest()) {
				$left_checked = $right_checked = '';
			}	

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
	}

?>

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

if (class_exists('Simple_Comment_Editing')) {
	add_filter( 'sce_unlimited_editing', '__return_true', 12 );

	add_filter( 'sce_can_edit_cookie_bypass', '__return_true', 12 );

	add_filter( 'sce_text_edit', 'bfc_sce_text_edit', 12 );

	function bfc_sce_text_edit() {
		global $comment;
		if ($comment->user_id == bp_loggedin_user_id()) {
			return __( 'Edit', 'bfcommons-theme');
		} else {
			return __( 'Resolve', 'bfcommons-theme');
		}
	}
	
	add_filter( 'sce_buttons', 'bfc_sce_allow_edit', 12, 2 );

	function bfc_sce_allow_edit($textarea_buttons, $comment_id) {
		$comment = get_comment ($comment_id);
		if ($comment->user_id != bp_loggedin_user_id()) {
			$textarea_buttons = str_replace('class="sce-comment-save"', 'class="sce-comment-save" style= "display: none;"', $textarea_buttons);
		}
		return $textarea_buttons;
	}

	add_filter( 'sce_can_edit', 'bfc_docs_sce_can_edit', 12, 2 );

	function bfc_docs_sce_can_edit ($value, $comment) {
		$value = bfc_docs_can_edit_comment ($comment);
		return $value;
		// return true;
	}

	function bfc_docs_sce_add_reply_link ( $comment_text, $comment, $args){
		$args['reply_text'] = __( 'Reply to this comment' );
		$post_id = absint( $comment->comment_post_ID );
		$reply_link = '</a>' . get_comment_reply_link( $args, $comment, $post_id) . '</div>';
		$retval = str_replace('</a></div>', $reply_link, $comment_text );
		return $retval;
	}

	add_filter( 'sce_content', 'bfc_sce_widen_textarea');

	function bfc_sce_widen_textarea ($sce_content) {
		return str_replace('cols="45"', 'cols="59"', $sce_content );
	}

}

function bfc_docs_can_edit_comment ($comment) {

	$retval = false;
	if (bp_docs_is_single_doc()) {
		$doc_id = get_the_ID();
		$user_id = bp_loggedin_user_id();
		$associated_group_id = bp_is_active( 'groups' ) ? bp_docs_get_associated_group_id( $doc_id ) : 0;
		$is_comment_author = $comment->user_id == $user_id;
		$is_doc_author = in_array( $user_id, bfc_doc_author_ids ( $doc_id ));
		$is_steward = groups_is_user_admin( $user_id, $associated_group_id );
		$retval = ( $is_comment_author || $is_doc_author || $is_steward );
	} else {
		$post_author_id = get_post_field ('post_author', $comment->comment_post_ID);
		$comment_user_id = $comment->user_id;
		$loggin_user = bp_loggedin_user_id();
		if ($post_author_id == $loggin_user || $comment_user_id == $loggin_user) {
			$retval = true;
		}
	}
	return $retval;
}

add_action('wp_head', 'bfc_setup_doc_folders');

function bfc_setup_doc_folders () {
	remove_action ( 'bp_docs_before_tags_meta_box', 'bp_docs_folders_meta_box' );
	add_action( 'bp_docs_before_tags_meta_box', 'bfc_docs_folders_meta_box', 12 );
}

/**
 * Add the meta box to the edit page.
 *
 * @since 1.9
 */
function bfc_docs_folders_meta_box() {

	$doc_id = get_the_ID();
	$associated_group_id = bp_is_active( 'groups' ) ? bp_docs_get_associated_group_id( $doc_id ) : 0;

	if ( ! $associated_group_id && isset( $_GET['group'] ) ) {
		$group_id = BP_Groups_Group::get_id_from_slug( urldecode( $_GET['group'] ) );
		if ( current_user_can( 'bp_docs_associate_with_group', $group_id ) ) {
			$associated_group_id = $group_id;
		}
	}

	if (! $associated_group_id && bp_docs_is_doc_create()) {
		$associated_group_id = bp_get_current_group_id();
	}

	// On the Create screen, respect the 'folder' $_GET param
	if ( bp_docs_is_doc_create() ) {
		$folder_id = bp_docs_get_current_folder_id();
	} else {
		$folder_id = bp_docs_get_doc_folder( $doc_id );
	}
	if ($folder_id) {
		$folder = get_post ($folder_id);
		$current_folder = ' – ' . $folder->post_title;
	} else {
		$current_folder = ' – None';
	}
	?>

	<div id="doc-folders" class="doc-meta-box bfc-folders">
		<div class="toggleable <?php bp_docs_toggleable_open_or_closed_class( 'folders-meta-box' ) ?>">
			<p id="folders-toggle-edit" class="toggle-switch">
				<span class="hide-if-js toggle-link-no-js"><?php _e( 'Folders', 'buddypress-docs' ) ?></span>
				<a class="hide-if-no-js toggle-link" id="folders-toggle-link" href="#"><span class="show-pane plus-or-minus"></span><span class="toggle-title"><?php _e( 'Folders', 'buddypress-docs' )?></span> <?php echo '<span class="bfc-current-access">' . $current_folder . '</span>' ?></span></a>
			</p>

			<div class="toggle-content">
				<table class="toggle-table" id="toggle-table-folders">
					<tr>
						<td class="desc-column">
							<label for="bp_docs_tag"><?php _e( 'Select a folder for this Doc.', 'buddypress-docs' ) ?></label>
						</td>

						<td>
							<div class="existing-or-new-selector">
								<input type="radio" name="existing-or-new-folder" id="use-existing-folder" value="existing" checked="checked" />
								<label for="use-existing-folder" class="radio-label"><?php _e( 'Use an existing folder', 'buddypress-docs' ) ?></label><br />
								<div class="selector-content">
									<?php bp_docs_folder_selector( array(
										'name'     => 'bp-docs-folder',
										'id'       => 'bp-docs-folder',
										'group_id' => $associated_group_id,
										'selected' => $folder_id,
									) ) ?>
								</div>
							</div>

							<div class="existing-or-new-selector" id="new-folder-block">
								<input type="radio" name="existing-or-new-folder" id="create-new-folder" value="new" />
								<label for="create-new-folder" class="radio-label"><?php _e( 'Create a new folder', 'buddypress-docs' ) ?></label>
								<div class="selector-content">

									<?php bp_docs_create_new_folder_markup( array(
										'group_id' => $associated_group_id,
										'selected' => $associated_group_id,
									) ) ?>
								</div><!-- .selector-content -->
							</div>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>

	<?php
}

function bfc_docs_get_the_content($post_id) {
	if ( function_exists( 'bp_restore_all_filters' ) ) {
		bp_restore_all_filters( 'the_content' );
	}

	$content = apply_filters( 'the_content', get_post_field('post_content', $post_id ) );

	if ( function_exists( 'bp_remove_all_filters' ) ) {
		bp_remove_all_filters( 'the_content' );
	}

	return $content;
}

add_filter('bp_docs_current_user_can_create_in_context', 'bfc_docs_current_user_can_create_in_context');

function bfc_docs_current_user_can_create_in_context($can_create) {

	if ( function_exists( 'bp_is_group' ) && bp_is_group() ) {
		$can_create = current_user_can( 'bp_docs_associate_with_group', bp_get_current_group_id() );
	} elseif (bp_is_my_profile() || !bp_is_user()) {
		$can_create = current_user_can( 'delete_others_posts' );
	} else {
		$can_create = false;
	}
	
	if(bp_docs_is_folder_manage_view() || bp_docs_is_single_doc()) {$can_create = false;}

	return $can_create;
}

add_filter('bp_docs_create_button', 'bfc_docs_create_button');

function bfc_docs_create_button ($create_button) {
	$can_create = bfc_docs_current_user_can_create_in_context('false');
	if (!$can_create) {$create_button = '';}
	return $create_button;
}

add_filter('document_title', 'bfc_docs_single_title', 999);

function bfc_docs_single_title($title) {
	global $wp;
	if(bp_current_component() == 'groups' && bp_docs_is_existing_doc()) {
		$page_slug = substr($wp->request, strrpos($wp->request,'/docs/'));
		$page_slug = str_replace('/edit','',$page_slug);
		$current_url = home_url() . $page_slug;
		$post_id = url_to_postid($current_url);
		$title = get_the_title($post_id) . ' - ' . get_bloginfo('name');
		return $title;
	} elseif (isset($_GET['bpd_tag'] ) && 'bfcom-help' == urldecode( $_GET['bpd_tag'] )){
		$title = 'Commons Help - ' . get_bloginfo('name');
		return $title;
	}
	return $title;
}

add_filter( 'bp_docs_directory_breadcrumb', 'bfc_docs_group_directory_remove_s', 99 );

function bfc_docs_group_directory_remove_s($crumbs){
	if($crumbs) {
		$crumbs = str_replace("s&#8217;s", "s&#8217;", $crumbs);
	}
	return $crumbs;
}

function bfc_docs_user_can_access_folder($folder_id) {
	$levels = array( 'none' => 0, 'anyone' => 1, 'loggedin' => 2, 'group-members' => 3, 'admins-mods' => 4, 'creator' => 5);
	$min_access = get_post_meta( $folder_id, 'bfc_contents_min_access', true );
	$min_access = array_search($min_access, $levels);
	$creators = get_post_meta( $folder_id, 'bfc_contents_creators', true);
	$user_id = bp_loggedin_user_id();
	if ( $min_access == 'loggedin' ) {return true;}
	if ( is_array($creators) && in_array ($user_id, $creators) ) {return true;}
	elseif ( $creators && $user_id == intval($creators) ) {return true;}
	if ( bp_is_active( 'groups' ) && bp_is_group() ) {
		$group_id = bp_get_current_group_id();
		if ( $min_access == 'group-members' && groups_is_user_member( $user_id, $group_id )){return true;}
		if ( $min_access == 'admins-mods' && (groups_is_user_admin( $user_id, $group_id ) || groups_is_user_mod( $user_id, $group_id )) ) {return true;}
	}
	return false;
}
add_action( 'bp_docs_after_save', 'bfc_docs_update_folder_access_on_edit',99 );
add_action( 'bp_docs_doc_deleted', 'bfc_docs_update_folder_access_on_trash', 99 );
add_action( 'bp_docs_doc_untrashed', 'bfc_docs_update_folder_access_on_untrash', 99 );
add_action( 'untrashed_post', 'bfc_docs_update_folder_access_on_untrash', 99 );


function bfc_docs_update_folder_access_on_edit ( $doc_id ) {
	$folder_id = bp_docs_get_doc_folder($doc_id);
	$prev_folder_id = get_post_meta( $doc_id, 'bfc_docs_prev_folder', true );
	if($folder_id) {
		bfc_docs_update_folder_access( $folder_id );		
	} 
	if($prev_folder_id && $prev_folder_id != $folder_id) {
		bfc_docs_update_folder_access( $prev_folder_id );
	}
	if($prev_folder_id != $folder_id) {
		update_post_meta($doc_id, 'bfc_docs_prev_folder', $folder_id);
	}
}

function bfc_docs_update_folder_access_on_trash ( $delete_args ) {
	$doc_id = $delete_args['ID'];
	$folder_id = get_post_meta( $doc_id, 'bfc_docs_prev_folder', true );
	if($folder_id) {
		bfc_docs_update_folder_access( $folder_id );		
	} 
}	

function bfc_docs_update_folder_access_on_untrash ( $doc_id ) {
	$untrashed = wp_update_post( array(
		'ID' => $doc_id,
		'post_status' => 'publish',
	) );
	$folder_id = get_post_meta( $doc_id, 'bfc_docs_prev_folder', true );
	if($folder_id && $untrashed) {
		bp_docs_add_doc_to_folder( $doc_id, $folder_id, $append = false );
		bfc_docs_update_folder_access( $folder_id );		
	} 
}	

function bfc_docs_update_folder_access( $folder_id ) {

	$levels = array( 'none' => 0, 'anyone' => 1, 'loggedin' => 2, 'group-members' => 3, 'admins-mods' => 4, 'creator' => 5);
	if ($folder_id) {
		// Get the docs belonging to this folder
		$folder_term = bp_docs_get_folder_term( $folder_id );

		$folder_docs = get_posts( array(
			'post_type' => bp_docs_get_post_type_name(),
			'tax_query' => array(
				array(
					'taxonomy' => 'bp_docs_doc_in_folder',
					'field' => 'term_id',
					'terms' => $folder_term,
				),
			),
		) );		
		$folder_access = array();
		$folder_creators = array();
		$fldr_id = $folder_id;
		if (!$folder_docs) {
			$fldr_min_access = 0;
		} else {
			foreach ( $folder_docs as $doc ) {
				$settings = bp_docs_get_doc_settings( $doc->ID );
				$doc_read_level = $levels[$settings['read']];
				$folder_access[] = $doc_read_level;
				if($doc_read_level == 5) { $folder_creators[] = $doc->post_author; }
			}
			$fldr_min_access = min($folder_access);
			if( $fldr_min_access < 5) { $folder_creators = array();}
			// $folder_min_access = array_search($fldr_min_access, $levels);
		}
		update_post_meta($fldr_id, 'bfc_contents_min_access', $fldr_min_access);
		update_post_meta($fldr_id, 'bfc_contents_creators', $folder_creators);

		$fldr = get_post( $fldr_id );
		$fldr_id = $fldr->post_parent;
		while($fldr_id) {
			$folder_children = get_posts( array(
				'numberposts' => -1,
				'post_type' => 'bp_docs_folder',
				'post_parent' => $fldr_id,
				));
			$folder_access = array();
			$folder_creators = array();
			foreach ( $folder_children as $child ) {
				$min_access = get_post_meta( $child->ID, 'bfc_contents_min_access', true );
				if($min_access) {$folder_access[] = $min_access;}
				$creators = get_post_meta( $child->ID, 'bfc_contents_creators', true);
				if($min_access == 5 && is_array($creators)) {$folder_creators =  array_merge ($folder_creators, $creators); }
			}
			if($folder_access) {$fldr_min_access = min($folder_access);}
			else {$fldr_min_access = 0;}
			if( $fldr_min_access < 5) { $folder_creators = array();}
			// $folder_min_access = array_search($fldr_min_access, $levels);
			update_post_meta($fldr_id, 'bfc_contents_min_access', $fldr_min_access);
			update_post_meta($fldr_id, 'bfc_contents_creators', $folder_creators);
	
			$fldr = get_post( $fldr_id );
			$fldr_id = $fldr->post_parent;		
		}
	}
}

?>
