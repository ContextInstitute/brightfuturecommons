<?php

add_action( 'bp_nouveau_return_groups_buttons', 'bfc_group_buttons', 10, 3);

function bfc_group_buttons (&$return, $group, $type) {
	if ($group->id ==1 && $type == 'loop'){
		if (str_contains($return['group_membership'], 'Member')) {
			$return['group_membership'] = '<div class="group-button public generic-button" id="groupbutton-' . $group->id . '"><button data-title="For Everyone" data-title-displayed="Member" class="everyone-group-button">Member</button></div>';
		} elseif (str_contains($return['group_membership'], 'Moderator')) {
			$return['group_membership'] = '<div class="group-button public generic-button" id="groupbutton-' . $group->id . '"><button data-title="For Everyone" data-title-displayed="Moderator" class="everyone-group-button">Moderator</button></div>';
		} elseif (str_contains($return['group_membership'], 'Steward')) {
			$return['group_membership'] = '<div class="group-button public generic-button" id="groupbutton-' . $group->id . '"><button data-title="For Everyone" data-title-displayed="Steward" class="everyone-group-button">Steward</button></div>';
		}
	}
}
?>
