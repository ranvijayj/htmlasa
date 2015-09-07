<?php
echo "<p>Dear " . $user->person->First_Name . ' ' . $user->person->Last_Name . "!</p>\n"
    . "<p>There are new AP items that require your approval for the following companies:</p>\n";

foreach ($clientsToApprove as $key => $clientToApprove) {
    echo  "<p>&nbsp;&nbsp;" . ($key+1) .") " . CHtml::encode($clientToApprove) . "<p>\n";
}

echo "</br><p>In order to see more details please follow this link <a style='color: #0066CC' href='http://" . $_SERVER['HTTP_HOST'] . "/ap' target='_blank'>" . $_SERVER['HTTP_HOST'] . "/ap</a></p>";
?>