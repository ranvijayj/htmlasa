<?php

if (count($clients) > 0) {
    foreach($clients as $client) {
        if ($currentClient == $client->Client_ID) {
            echo '<option value="' . $client->Client_ID . '" selected="selected">' . CHtml::encode($client->company->Company_Name) . '</option>';
        } else {
            echo '<option value="' . $client->Client_ID . '">' . CHtml::encode($client->company->Company_Name) . '</option>';
        }
    }
} else {
    echo '<option value="0" selected="selected">You are not linked to any company</option>';
}
?>