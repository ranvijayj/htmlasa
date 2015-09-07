<?php
if (count($approvers_array)) {
    foreach ($approvers_array as $item) {
        $user = $item['user'];
        $apr_value = $item['approval_value'];
        echo '<tr id="user' . $user->User_ID  . '">';
        echo '<td class="width160">' . CHtml::encode($user->person->First_Name . ' '  . $user->person->Last_Name) .  '</td><td class="width160"><span class="ov_hidden width160">' . CHtml::encode($user->person->Email) .  '</span></td><td><input class="input_in_grid appr_value_input" type="text" size="5" maxlength="3" name="users[' . $user->User_ID . ']" value="' . $apr_value .'"/></td>';
        echo '</tr>';

}
} else {
    echo '<tr id="user0">';
    echo '<td clospan="3">Users were not found</td>';
    echo '</tr>';
}


?>