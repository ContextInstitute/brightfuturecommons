<?php

function bfc_avatar_dropdown ($type,$source,$follow_class){
  $user = bp_loggedin_user_id();
  $person = $source;
  if ($type == 'reply-author') {
    $person = bbp_get_reply_author_id($source);
  }
  $output = '<div class="dropdown-pane '. $follow_class . '" id="' . $type . '-dropdown-' . esc_attr( $source ) . '" data-dropdown data-hover="true" data-hover-pane="true" data-auto-focus="false">';
  if($person != $user) {
    $output .= '<a href="/members/' . bp_core_get_username($user) . '/messages/compose/?r=' . bp_core_get_username($person) . '">Send a message</a><br>';
    if($follow_class == 'follow-active') {
      $output .= bp_get_add_follow_button ( $person, $user );
    }
  }
  $output .= '<a href="/members/' . bp_core_get_username($person) . '">Visit profile</a><br>Plus info from profile</div>';
  return $output;
}

function bfc_reply_post_date() {
	/*
	 Replaces bbp_reply_post_date() in loop-single-reply.php and loop-search-reply.php 
	 Based on bbp_get_reply_post_date()
	*/
	$post_date = get_post_time( 'M j, Y' );
	echo apply_filters( 'bfc_reply_post_date', bfc_nice_date ($post_date) );
  }

function bfc_nice_date ($post_date){
	$today = current_time('M j, Y');
	$yestertime = current_time('timestamp') - 86400;
	$yesterday = date('M j, Y', $yestertime );
	// $post_date = get_post_time( 'M j, Y' );
	if ($post_date==$today) {
		$result = "Today at " . get_post_time( 'g:i A' );
	} elseif ($post_date==$yesterday) {
		$result =  "Yesterday at " . get_post_time( 'g:i A' );
	} else {
		$result = get_the_date('M j, Y');
	}
	return $result;
}
?>
