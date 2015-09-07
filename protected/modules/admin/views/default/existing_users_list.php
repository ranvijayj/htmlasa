<?php

    if (count($all_users) > 0) {
        if (count($all_users) > 0) {
            foreach ($all_users as $item) {?>
                <tr data-id="<?=$item['User_ID']?>">
                    <td class="width200"><?=$item['First_Name']." ".$item['Last_Name']?></td>
                    <td><? if($item['Client_Admin']!=1) { echo "<a href='#' class='delete_exsting' data-uid='".$item['User_ID']."' data-client='".$item['Client_ID']."' data-project='".$item['Project_ID']."' > Remove from client </a>"; }?> </td>
                </tr>
            <? }
            }
    } else {
        echo '<tr>';
        echo '<td clospan="2">Users were not found</td>';
        echo '</tr>';
    }

?>