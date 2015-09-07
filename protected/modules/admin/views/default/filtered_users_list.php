<?php
if ($find) {
    if (count($all_users) > 0) {
        if (count($all_users) > 0) {
            foreach ($all_users as $person) {
                if ($person->user) {
                    echo '<tr id="user' . $person->user->User_ID . '">';
                    echo '<td class="width200">' . CHtml::encode($person->First_Name . ' '  . $person->Last_Name ) .  '</td><td>' . CHtml::encode($person->Email) . '</td>';
                    echo '</tr>';
                }
            }
        }
    } else {
        echo '<tr id="user0">';
        echo '<td clospan="2">Users were not found</td>';
        echo '</tr>';
    }
} else {
    echo '<tr id="user0">';
    echo '<td clospan="2">Enter last name to the text field to populate this grid</td>';
    echo '</tr>';
}
?>