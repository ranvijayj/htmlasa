<?php
if (count($COAs) > 0) {
    $dropDownCellList = '<li>AP</li><li>W9</li><li>PO</li><li>BU</li><li>PM</li><li>LB</li><li>GF</li><li>PR</li><li>PC</li><li>JE</li><li>AR</li>';
    $dropDownCellList = '';
    foreach ($coaClasses as $coaClass) {
        $dropDownCellList .= '<li data-class-id="' . $coaClass->COA_Class_ID . '" data-class="' . $coaClass->Class_Shortcut . '">' . CHtml::encode($coaClass->Class_Shortcut . ' - ' . $coaClass->Class_Name) . '</li>';
    }
    foreach ($COAs as $COA) {
        ?>
        <tr>
            <td class="width20">
                <input type="checkbox" class='list_checkbox' name="coa[<?php echo $COA->COA_ID; ?>]" value="<?php echo $COA->COA_ID; ?>"/>
            </td>
            <td class="width40 dropdown_cell_upload">
                <div class="dropdown_cell_ul"><ul><?php echo $dropDownCellList; ?></ul><span class="dropdown_cell_value"><?php echo CHtml::encode($COA->class->Class_Shortcut); ?></span></div>
            </td>
            <td class="width110 in_place_edit" data-cell-type="COA_Name" data-editing="0" data-already-used="0">
                <?php echo Helper::cutText(14, 130, 10, $COA->COA_Name); ?>
            </td>
            <?if ($COA->COA_Used==0) {?>
                <td class="width130 in_place_edit" data-cell-type="COA_Acct_Number" data-editing="0" data-already-used="<?=$COA->COA_Used?>">
            <?} else {?>
                <td class="width130" data-cell-type="COA_Acct_Number" data-already-used="<?=$COA->COA_Used?>">
            <?}?>
                <?php echo Helper::cutText(14, 170, 20, $COA->COA_Acct_Number); ?>
            </td>
            <td class="amount_cell in_place_edit"  data-cell-type="COA_Budget" data-editing="0" data-already-used="0">
                <span class="left">$</span><span><?php echo Helper::cutText(14, 90, 13, number_format($COA->COA_Budget,2)); ?></span>
            </td>
        </tr>
    <?php
    }
} else {

    echo '<tr>
             <td>
                 COA not found.
             </td>
           </tr>';
}
?>