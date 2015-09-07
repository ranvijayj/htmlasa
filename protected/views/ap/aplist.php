<?php
if (count($apList) > 0) {
    $count = count($apList);
    $i = 0;
    for ($i=0;$i<$count; $i++){
    //foreach ($apList as $ap) {
        $ap = $apList[$i];
        $prId = $ap->document->Project_ID; //project ID
        $clientID = $ap->document->Client_ID; //project ID
        if ($batchedMode) {


            $prevId = $apList[$i-1]->document->Project_ID;
            if ($prId == $prevId) {
                $thisIsFirstItem = false;
            } else {
                $thisIsFirstItem = true;
            }
        }

        $checked = "";
        $color = '';
        if ($markSelctd && in_array($ap->Document_ID, $_SESSION['marked_aps'])) {
            $checked = "checked='checked'";
            $color = 'style="background-color: #eee"';
        }
        ?>

        <?if ($thisIsFirstItem && $batchedMode) {?>
            <tr id="doc<?php echo $ap->Document_ID; ?>" <?php echo $color; ?>>

                <td colspan="5" style="text-align: left;color: #808080;text-decoration: underline;">
                    Project : "<?=Projects::getProjectNameByID($prId);?>"
                </td>
            </tr>
        <?}?>

        <tr id="doc<?php echo $ap->Document_ID; ?>" <?php echo $color; ?>>
            <td class="width30">
                <input type="checkbox" class='list_checkbox' name="documents[<?php echo $ap->Document_ID; ?>]"  data-project-id="<?=intval($prId)?>" data-client-id="<?=intval($clientID)?>" value="<?php echo $ap->Document_ID; ?>" <?php echo $checked; ?>/>
            </td>
            <td class="width140">
                <?php echo isset($ap->vendor->client->company->Company_Name) ? Helper::cutText(15, 160, 14, $ap->vendor->client->company->Company_Name) : '<span class="not_set">Vendor not attached</span>'; ?>
            </td>
            <td class="amount_cell width70"><?php echo CHtml::encode($ap->Invoice_Amount) ? Helper::cutText(15, 75, 10, number_format($ap->Invoice_Amount, 2, '.', ',')) : '<span class="not_set">Not set</span>'; ?></td>
            <td class="width70">
                <?php echo CHtml::encode($ap->Invoice_Due_Date) ? CHtml::encode(Helper::convertDate($ap->Invoice_Due_Date)) : '<span class="not_set">Not set</span>'; ?>
            </td>
            <td>
                <span class="cutted_cell">
                    <?php
                        echo CHtml::encode($notes[$ap->AP_ID]) ? Helper::cutText(11, 240, 29, $notes[$ap->AP_ID]) : '<span class="not_set">No comments</span>';
                    ?>
                </span>
            </td>
        </tr>



    <?php
    }
} else {
    echo '<tr>
             <td>
                 Aps were not found.
             </td>
           </tr>';
}
?>


