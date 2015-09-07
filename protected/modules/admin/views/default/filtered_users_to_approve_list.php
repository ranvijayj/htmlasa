<?php
if (count($usersToApprove)) {
    foreach ($usersToApprove as $userToApprove) {
        echo '<tr id="user' . $userToApprove->id  . '">';
        echo '<td class="width150">' . CHtml::encode($userToApprove->user->person->First_Name . ' '  . $userToApprove->user->person->Last_Name) .  '</td><td class="width130"><span class="ov_hidden width130">' . $userToApprove->user->person->Email .  '</span></td><td class="dropdown_cell width70"><div class="dropdown_cell_ul"><span class="dropdown_cell_value user_type_value">' . ($userToApprove->New_Client ? 'Cl. Admin' : 'User') . '</span><ul class="width70"><li>User</li><li>Processor</li><li>Approver</li><li>Cl. Admin</li></ul></div></td><td class="dropdown_cell"><div class="dropdown_cell_ul"><span class="dropdown_cell_value user_approve_value">-</span><ul><li>Y</li><li>N</li><li>-</li></ul></div></td>';
        echo '</tr>';
    }
} else {
    echo '<tr id="user0">';
    echo '<td clospan="3">Users were not found</td>';
    echo '</tr>';
}
?>