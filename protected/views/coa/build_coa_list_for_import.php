<img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>

<div class="left uploads_block_left">
    <p>Import Format Preview:</p>
    <div id="additional_fields_block_conteiner">
        <table id="list_table_head">
            <thead>
            <tr class="table_head">
                <th class="width50">
                    Class
                </th>
                <th class="width180">
                    Description
                </th>
                <th class="width110">
                    Number
                </th>
                <th class="amount_cell">
                    Budget
                </th>
            </tr>
            </thead>
        </table>
        <div id="import_coa_scroll_block">
                <table id="list_table">
                    <tbody>
                    <?php
                    $invalidBudgets = array();
                    $newClasses = array();
                    if (count($COAs) > 0) {
                        foreach ($COAs as $row => $COA) {
                            if (!$COA['validBudget']) {
                                $invalidBudgets[] = $row;
                            }
                            if ($COA['newClass']) {
                                if (!in_array($COA['class'], $newClasses)) {
                                    $countClasses++;
                                    $newClasses[$countClasses] = $COA['class'];
                                }
                            }
                            ?>
                            <tr class="row_for_import" data-budget="<?php echo ($COA['validBudget']) ? '1' : '0'; ?>" data-class="<?php echo ($COA['newClass']) ? '1' : '0'; ?>">
                                <td class="width50">
                                    <?php echo Helper::cutText(14, 60, 5, $COA['class']); ?>
                                </td>
                                <td class="width150">
                                    <?php echo Helper::cutText(14, 185, 15, $COA['name']); ?>
                                </td>
                                <td class="width140">
                                    <?php echo Helper::cutText(14, 175, 15, $COA['acctNumber']); ?>
                                </td>
                                <td class="amount_cell" <?php echo (!$COA['validBudget']) ? 'style="background-color: #fdd;"' : ''; ?>>
                                    <span class="left">$</span><span><?php echo Helper::cutText(14, 85, 8, number_format($COA['budget'], 2)); ?></span>
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
                    </tbody>
                </table>
        </div>
    </div>
</div>
<div class="right uploads_block_right">
    <button class="button" id="submit_import">Import</button> <button class="button hidemodal">Cancel</button>
    <br/>
    <div class="additional_fields_form">
        <?php
            if (count($invalidBudgets) > 0) {
                /*echo 'The value' . ((count($invalidBudgets) > 1) ? 's' : '') . ' in line' . ((count($invalidBudgets) > 1) ? 's' : '') . ' ' . implode(', ', $invalidBudgets) . ' in Excel ' . ((count($invalidBudgets) > 1) ? 'are' : 'is') . ' not a proper dollar value' . ((count($invalidBudgets) > 1) ? 's' : '') . '. The value' . ((count($invalidBudgets) > 1) ? 's' : '') . ' must be a number. Please
                      correct and import again. Thank you.';*/
                echo "Some of the data set for upload could not be processed so the entire upload has been abandoned. Please re-configure the data before continuing.";
            } else {
               ?>
                <form action="/coa" method="post" id="import_coa_form">
                    <?php
                        if (count($newClasses) > 0) {
                            echo "New Classes: <br/><br/>";
                            ?>
                            <div id="coa_classes_import_block">
                                <table id="coa_classes_import">
                                    <tbody>
                                    <?php
                                    foreach($newClasses as $key => $coaClass) {
                                        echo '<tr>
                                  <td class="width30">
                                      <span><input type="text" value="' . $key . '" name="CoaClass[' . $key . '][Class_Sort_Order]" class="int_type"></span>
                                  </td>
                                  <td class="width40">
                                       <span><input type="text" maxlength="3" value="' . $coaClass . '" name="CoaClass[' . $key . '][Class_Shortcut]"></span>
                                  </td>
                                  <td>
                                       <span><input type="text" maxlength="50" value="" name="CoaClass[' . $key . '][Class_Name]"></span>
                                  </td>
                             </tr>';
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php
                        }
                    ?>
                    <input type="hidden" value="1" name="import_coa_form">
                </form>
               <?php
            }
        ?>
    </div>
</div>
<div class="clear"></div>
