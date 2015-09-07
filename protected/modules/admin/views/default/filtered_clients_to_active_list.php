<?php
if ($find) {
    if (count($all_clients) > 0) {
        foreach ($all_clients as $client) {
            if ($client->Client_Status == 1) {
                $active = 'Y';
            } else {
                $active = 'N';
            }

            echo '<tr id="client' . $client->Client_ID  . '" data-id="' . $client->Client_ID  . '">';
            echo '<td class="width260">' . CHtml::encode($client->company->Company_Name) .  '</td><td class="width100"><span class="ov_hidden width100">' . CHtml::encode($client->company->Company_Fed_ID) .  '</span></td><td class="dropdown_cell"><div class="dropdown_cell_ul"><span class="dropdown_cell_value">' . $active . '</span><ul><li>Y</li><li>N</li></ul></div></td>';
            echo '</tr>';
        }
    } else {
        echo '<tr id="client0" data-id="0">';
        echo '<td clospan="3">Clients were not found</td>';
        echo '</tr>';
    }
} else {
    echo '<tr id="client0" data-id="0">';
    echo '<td clospan="3">Enter Company Name to populate this grid</td>';
    echo '</tr>';
}
?>