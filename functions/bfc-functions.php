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

?>