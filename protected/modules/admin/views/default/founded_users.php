<table class="font16 width100p">
<?php
if ($user) {
   echo "<tr><td>" . CHtml::encode($user->person->First_Name . ' ' . $user->person->Last_Name) . "</td><td class='width20'><span id='show_users_doc' class='button_small' data='" . $user->User_ID . "'>Show</span></td></tr>";
} else {
    echo "<tr><td>$message</td></tr>";
}
?>
</table>