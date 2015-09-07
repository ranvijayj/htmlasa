<?php
if ($find) {
    if (count($all_users) > 0) {
        foreach ($all_users as $person) {
            if ($person->user) {
                if ($person->user->Active == 1) {
                    $active = 'Y';
                } else {
                    $active = 'N';
                }

                echo '<tr id="user' . $person->user->User_ID  . '">';
                echo '<td class="width180">' . CHtml::encode($person->First_Name . ' '  . $person->Last_Name) .  '</td><td class="width180"><span class="ov_hidden width180">' . CHtml::encode($person->Email) .  '</span></td><td class="dropdown_cell"><div class="dropdown_cell_ul"><span class="dropdown_cell_value">' . $active . '</span><ul><li>Y</li><li>N</li></ul></div></td>';
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