<?php
if (count($client_users)) {
    foreach ($client_users as $key => $user) {
        echo '<tr id="user' . $user->User_ID  . '">';
        echo '<td class="width160">' . CHtml::encode($user->person->First_Name . ' '  . $user->person->Last_Name) .  '</td><td class="width160"><span class="ov_hidden width160">' . CHtml::encode($user->person->Email) .  '</span></td><td class="dropdown_cell"><div class="dropdown_cell_ul"><span class="dropdown_cell_value">' . $userTypes[$user->User_ID] . '</span><ul class="width70"><li>User</li><li>Processor</li><li>Approver</li><li>Cl. Admin</li></ul></div></td>';
        echo '</tr>';
    }
} else {
    echo '<tr id="user0">';
    echo '<td clospan="3">Users were not found</td>';
    echo '</tr>';
}
?>