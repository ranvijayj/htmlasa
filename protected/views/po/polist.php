<?php
if (count($poList) > 0) {
    $count = count($poList);
    $i = 0;
    for ($i=0;$i<$count; $i++){
        $checked = "";

        $color = '';
        $tr_class = '';

        $po = $poList[$i];
        $prId = $po->document->Project_ID; //project ID
        $clientID = $po->document->Client_ID; //project ID
        if ($batchedMode) {


            $prevId = $poList[$i-1]->document->Project_ID;
            if ($prId == $prevId) {
                $thisIsFirstItem = false;
            } else {
                $thisIsFirstItem = true;
            }
        }

        if ($po->Vendor_ID == 0 && $po->PO_Approval_Value == 100) {
            $color = '#E6F6FB';
            $tr_class = 'voided_po';
        }
        if ($markSelctd && in_array($po->Document_ID, $_SESSION['marked_pos'])) {
            $checked = "checked='checked'";
            $color = '#eee';
        }
        $style = "background-color: ".$color ? $color : '#fff'.";font-color: ".$font_color ? $font_color : '#000'.";";

        ?>


        <?if ($thisIsFirstItem && $batchedMode) {?>
            <tr id="doc<?php echo $po->Document_ID; ?>" <?php echo $color; ?>>

                <td colspan="5" style="text-align: left;color: #808080;text-decoration: underline;">
                    Project : "<?=Projects::getProjectNameByID($prId);?>"
                </td>
            </tr>
        <?}?>


        <tr id="doc<?php echo $po->Document_ID; ?>" class="<?=$tr_class?>" <?php echo $style; ?>>
            <td class="width30" >
                <input type="checkbox" class='list_checkbox' name="documents[<?php echo $po->Document_ID; ?>]" data-project-id="<?=intval($prId)?>" data-client-id="<?=intval($clientID)?>" value="<?php echo $po->Document_ID; ?>" <?php echo $checked; ?>/>
            </td>
            <td class="width140">
                <?php echo isset($po->vendor->client->company->Company_Name) ? Helper::cutText(15, 160, 14, $po->vendor->client->company->Company_Name) : '<span class="not_set">Vendor not attached</span>'; ?>
            </td>
            <td class="width40">
                 <?php echo $po->PO_Number; ?>
            </td>
            <td class="amount_cell width95"><?php echo CHtml::encode($po->PO_Total) ? CHtml::encode(number_format($po->PO_Total, 2)) : '<span class="not_set">Not set</span>'; ?></td>
            <td class="width70">
                <?php
                    echo isset($budgets[$po->PO_ID]) ? Helper::cutText(15, 90, 10, implode('; ' ,$budgets[$po->PO_ID])) : '<span class="not_set">Not set</span>';
                ?>
            </td>
            <td>
                <span class="cutted_cell">
                      <?php
                           echo CHtml::encode($notes[$po->PO_ID]) ? Helper::cutText(11, 240, 28, $notes[$po->PO_ID]) : '<span class="not_set">No comments</span>';
                      ?>
                 </span>
            </td>
        </tr>
    <?php
    }
} else {
    echo '<tr>
             <td>
                 POs were not found.
             </td>
           </tr>';
}
?>
