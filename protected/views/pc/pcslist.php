<?php
if (count($pcsList) > 0) {
    foreach ($pcsList as $pc) {
        $checked = "";
        $color = '';
        /*
        if (in_array($pc->Document_ID, $pcs_to_review)) {
            $checked = "checked='checked'";
            $color = 'style="background-color: #eee"';
        } else {
            $checked = "";
            $color = '';
        }
        */
        ?>
        <tr id="doc<?php echo $pc->Document_ID; ?>">
            <td class="width30">
                <input type="checkbox" class='list_checkbox' name="documents[<?php echo $pc->Document_ID; ?>]" value="<?php echo $pc->Document_ID; ?>"/>
            </td>
            <td class="width235">
                <?php echo isset($pc->Employee_Name) ? Helper::cutText(15, 250, 20, $pc->Employee_Name) : '<span class="not_set">Not set</span>'; ?>
            </td>
            <td class="width100">
                <?php echo $pc->Envelope_Number ? CHtml::encode($pc->Envelope_Number) : '<span class="not_set">Not set</span>'; ?>
            </td>
            <td class="amount_cell width80">
                <?php echo ($pc->Envelope_Total != 0) ? Helper::cutText(15, 85, 11, number_format($pc->Envelope_Total, 2)) : '<span class="not_set">Not set</span>'; ?>
            </td>
            <td>
                <?php echo $pc->Envelope_Date ? CHtml::encode(Helper::convertDate($pc->Envelope_Date)) : '<span class="not_set">Not set</span>'; ?>
            </td>
        </tr>
    <?php
    }
} else {
    echo '<tr>
             <td>
                 PCs were not found.
             </td>
           </tr>';
}
?>
