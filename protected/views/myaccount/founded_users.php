<table class="width100p font16">
<?php

if ($exist) {
    echo "<tr><td>$message</td></tr>";
} else if (count($users) > 0) {
    foreach ($users as $user) {
        if (isset($user->user->User_ID)) {
            echo "<tr><td>" . CHtml::encode($user->Last_Name) . ' ' . CHtml::encode($user->First_Name) . "</td><td class='width20'><a href='/myaccount/adduser?id=" . $user->user->User_ID ."' class='button_small'>Add</a></td></tr>";
        }
    }
} else {
    echo "<tr><td>$message</td></tr>";
}
?>
</table>