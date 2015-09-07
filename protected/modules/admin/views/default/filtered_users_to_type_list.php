<?php
if ($find) {
    if (count($all_users) > 0) {
        foreach ($all_users as $person) {
            if ($person->user) {
                $type = array_search($person->user->User_Type, $this->userTypes);

                if ($type == 'DBAdmin') {
                    $type = Users::DB_ADMIN;
                }

                echo '<tr id="user' . $person->user->User_ID  . '">';
                echo '<td class="width160">' . CHtml::encode($person->First_Name . ' '  . $person->Last_Name) .  '</td><td class="width160"><span class="ov_hidden width160">' . CHtml::encode($person->Email) .  '</span></td><td class="dropdown_cell"><div class="dropdown_cell_ul"><span class="dropdown_cell_value">' . $type . '</span><ul class="width70"><li>User</li><li>DEC</li><li>Admin</li><li>DB Admin</li></ul></div></td>';
                echo '</tr>';
            }
        }
    } else {
        echo '<tr id="user0">';
        echo '<td clospan="3">Users were not found</td>';
        echo '</tr>';
    }
} else {
    echo '<tr id="user0">';
    echo '<td clospan="3">Enter last name to the text field to populate this grid</td>';
    echo '</tr>';
}
?>