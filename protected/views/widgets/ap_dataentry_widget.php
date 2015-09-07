<img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton" style="right: -93px;"/>

<div id="data_entry_left" style="background-color: #ffffff;overflow: auto;height: 640px;">
    <span style="font-family: Arial, sans-serif; text-align: center;font-size: 1.5em;font-weight: bolder"> <?=$company->Company_Name;?> </span><br>
    <span style="font-family: Arial, sans-serif; text-align: center;font-size: 1.3em;font-stretch: condensed ; "> Company ID : <?=$company->Company_ID;?> </span>

    <?php $form=$this->beginWidget('CActiveForm', array (
        'id'=>'ap_data_entry_form',
        'action'=>Yii::app()->createUrl('/dataentry/AjaxApFromDetail'),
        'htmlOptions'=>array(
            'class'=>'data_entry_form',
        ),

        'enableClientValidation'=>true,
        'enableAjaxValidation'=>true,
        'clientOptions' => array(
         'validateOnSubmit'=>true,
         'validateOnChange' => true,
        ),
    )); ?>


    <fieldset>
        <?php echo $form->errorSummary($ap); ?>
        <div class="group">
            <label for="Aps_Vendor_ID"><span class="red">*</span> Vendor</label>
            <?php echo $form->dropDownList($ap,'Vendor_ID', array('0' => 'Unknown Vendor') + $vendorsCP); ?>
            <?php echo $form->error($ap,'Vendor_ID'); ?>
        </div>
        <div class="group">
            <?php
            if ($ap->Invoice_Number == '0') {
                $ap->Invoice_Number = '';
            }
            ?>
            <label for="Aps_Invoice_Number"><span class="red">*</span> Inv. Number</label>
            <?php echo $form->textField($ap,'Invoice_Number'); ?>
            <?php echo $form->error($ap,'Invoice_Number'); ?>
        </div>
        <div class="group">
            <?php
            // convert date string to view format
            if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $ap->Invoice_Date)) {
                $ap->Invoice_Date = Helper::convertDateSimple($ap->Invoice_Date);
            }
            ?>
            <label for="Aps_Invoice_Date"><span class="red">*</span> Inv. Date</label>
            <?php echo $form->textField($ap,'Invoice_Date'); ?>
            <?php echo $form->error($ap,'Invoice_Date'); ?>
            <?php
            if (!isset($ap['_errors']["Invoice_Date"])) {
                echo '<div class="errorMessage grey">Correct date format: mm/dd/yyyy!</div>';
            }
            ?>
        </div>
        <div class="group">
            <?php
            // convert date string to view format
            if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $ap->Invoice_Due_Date)) {
                $ap->Invoice_Due_Date = Helper::convertDateSimple($ap->Invoice_Due_Date);
            }
            ?>
            <label for="Aps_Invoice_Due_Date">Inv. Due Date</label>
            <?php echo $form->textField($ap,'Invoice_Due_Date',array('data-term'=>$due_date_term)); ?>
            <?php echo $form->error($ap,'Invoice_Due_Date'); ?>
            <?php
            if (!isset($ap['_errors']["Invoice_Due_Date"])) {
                echo '<div class="errorMessage grey">Correct date format: mm/dd/yyyy!</div>';
            }
            ?>
        </div>
        <div class="group">
            <label for="Aps_Invoice_Amount"><span class="red">*</span> Inv. Amount</label>
            <?php echo $form->textField($ap,'Invoice_Amount'); ?>
            <?php echo $form->error($ap,'Invoice_Amount'); ?>
        </div>
        <div class="group">
            <label for="Aps_Invoice_Reference"><span class="red">*</span> Description</label>
            <?php echo $form->textField($ap,'Invoice_Reference'); ?>
            <?php echo $form->error($ap,'Invoice_Reference'); ?>
        </div>
        <div class="group">
            <label for="Aps_Detail_1099">1099</label>
            <?php echo $form->checkBox($ap,'Detail_1099'); ?>
            <?php echo $form->error($ap,'Detail_1099'); ?>
        </div>
        <div class="group">
            <label for="Aps_Detail_1099_Box_Number">1099 Type</label>
            <?php echo $form->dropDownList($ap,'Detail_1099_Box_Number', array('0' => 'Unknown 1099 Type') + array_combine(range(1,18), range(1,18))); ?>
            <?php echo $form->error($ap,'Detail_1099_Box_Number'); ?>

        </div>

        <div class="group">
            <label for="PO_Number">
                PO Number
            </label>
            <input id="PO_Number" type="text" value="<?php echo $poNum; ?>" maxlength="45" name="PO_Number">
            <?php
            if ($poError) {
                echo '<div class="errorMessage">' . $poError . '</div>';
            }
            ?>
        </div>
        <div style="font-family: Arial, sans-serif; text-align: center;font-size: 1.5em;font-weight: bolder;width:300px; "> AP Distributions </div>
        <div class="group de_invoices">

            <label>
                <input type="checkbox" id="dists_enabled" name="dists_enabled"
                    <?if(!$dists_empty) echo ' checked ';?>  >

                GL Dists</label><br/><br/>
            <div class="de_dists_fields">

                <table id="attached_invoices_head" class="width280">
                    <thead>
                    <tr>
                        <th class="width90">
                            GL Code
                        </th>
                        <th class="width70">
                            Amount
                        </th>
                        <th>
                            Desc.
                        </th>
                    </tr>
                    </thead>
                </table>
                <div id="attached_dists_block">
                    <table id="attached_invoices">
                        <tbody>
                        <?php
                        foreach($dists as $key => $dist) {
                            echo '<tr>
                                  <td class="width100">
                                      <input type="text" class="GL_Code" data-short-hand="'.$dist['Short_Hand'].'" maxlength="63" title="' . $dist['GL_Dist_Detail_COA_Acct_Number'] . '" value="' . $dist['GL_Dist_Detail_COA_Acct_Number'] . '" name="Dist[' . $key . '][GL_Dist_Detail_COA_Acct_Number]">
                                      <input type="hidden" class="short_hand"  value="' . $dist['Short_Hand'] . '" name="Dist[' . $key . '][Short_Hand]">
                                  </td>
                                  <td class="width80">
                                      <input type="text" class="gl_amount dollar_fields" value="' . $dist['GL_Dist_Detail_Amt'] . '" name="Dist[' . $key . '][GL_Dist_Detail_Amt]">
                                  </td>
                                  <td>
                                      <input type="text" maxlength="40" class="gl_descript" value="' . $dist['GL_Dist_Detail_Desc'] . '" name="Dist[' . $key . '][GL_Dist_Detail_Desc]" maxlength="125">
                                  </td>
                               </tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>

                <span id="add_invoice">+add item</span>
                <span id="remove_invoice">-remove item</span>
            </div>

            <div class="coa_row" id="COA_Allow_Manual_Coding" style="display: none" data-id="<?=$coaStructure->COA_Allow_Manual_Coding?>">
            </div>

            <?php echo $form->error($ap,'Dists');?>
            <?php
            if ($invalidDistsSum != '') {
                echo '<div class="errorMessage mleft20">' . $invalidDistsSum . '</div>';
            }
            ?>
        </div>
        <?php echo $form->hiddenField($ap,'AP_ID'); ?>
        <?php echo $form->hiddenField($ap,'Document_ID'); ?>

        <input type="hidden" value="true" name="ap_data_entry_form_values">

        <input type="hidden" value="<?=$return_url;?>" name="return_url" id="return_url">

        <div class="center">
            <?php //echo CHtml::submitButton('Save',array('class'=>'button')); ?>
            <button class="button" id="save_ap_details_form" >Save</button>
          
        </div>
    </fieldset>
    <?php $this->endWidget(); ?>

    <br/> <br/>
</div>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/main.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/data_entry_detail.js"></script>

<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/asa_datepicker.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/dataentry_dists_autocomplete.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/data_entry_detail_ap.js"></script>

