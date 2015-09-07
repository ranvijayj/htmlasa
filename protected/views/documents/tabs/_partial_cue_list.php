<?php
if (count($cueApprList) > 0) {
    foreach ($cueApprList as $cueAppr) {
        $checked = "";
        $color = '';
        if ($markSelctd && in_array($cueAppr->Document_ID, $_SESSION['marked_aps'])) {
            $checked = "checked='checked'";
            $color = 'style="background-color: #eee"';
        }
        ?>
        <tr id="doc<?php echo  $cueAppr->DocID; ?>" <?php echo $color; ?> >
            <td class="width10">
                <input type="checkbox" class='list_checkbox' name="documents[<?php echo $cueAppr->DocID; ?>]" value="<?php echo $cueAppr->DocID; ?>" <?php echo $checked; ?>/>
                <input type="hidden" class="document_type" value="<?=$cueAppr->DocType; ?>">
                <input type="hidden" class="control_id" value="<?=$cueAppr->NextApproverUID; ?>">
            </td>
            <td class="width130">
                <?php echo isset($cueAppr->CompanyName) ? Helper::cutText(15, 160, 14, $cueAppr->CompanyName) : '<span class="not_set">Vendor not attached</span>'; ?>
            </td>
            <td class="width30 doc_type">
                 <?php
                     echo $cueAppr->DocType;
                 ?>
            </td>

            <td class="amount_cell width30"><?php echo CHtml::encode($cueAppr->Amount) ? Helper::cutText(15, 75, 10, number_format($cueAppr->Amount, 2, '.', ',')) : '<span class="not_set">Not set</span>'; ?></td>
            <td class="width90 control_name">
                <?php
                if($cueAppr->Approval_Value<1) {
                    echo "DEC";
                } else { echo $cueAppr->ApprName; }
                ?>
            </td>
            <td>
                <span class="cutted_cell">
                    <?php
                        $notes = Documents::getLastNoteById($cueAppr->DocID);
                        echo CHtml::encode($notes) ? Helper::cutText(11, 240, 29, $notes) : '<span class="not_set">No comments</span>';
                    ?>
                </span>
            </td>
        </tr>
    <?php
    }
} else {
    echo '<tr>
             <td>
                 Documents  not found.

             </td>
           </tr>';
}
?>
