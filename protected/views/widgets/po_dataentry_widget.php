<img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton" style="right: -93px;"/>

<div id="data_entry_left" style="background-color: #ffffff;overflow: auto;height: 705px;z-index: 120;">
    <span style="font-family: Arial, sans-serif; text-align: center;font-size: 1.5em;font-weight: bolder"> <?=$company->Company_Name;?> </span><br>
    <span style="font-family: Arial, sans-serif; text-align: center;font-size: 1.3em;font-stretch: condensed ; "> Company ID : <?=$company->Company_ID;?> </span>


    <?php $form=$this->beginWidget('CActiveForm', array (
        'id'=>'po_data_entry_form',
        'htmlOptions'=>array(
            'class'=>'data_entry_form',
        ),
    )); ?>
    <fieldset>
        <div class="group">
            <label style="color: #fff">
                *
            </label>
            <input id="narrow_vendors_list" type="text" value="" maxlength="100" name="narrow_vendors_list" placeholder="Narrow Vend. by Name or Shortcut">
        </div>
        <div class="group">
            <label for="Pos_Vendor_ID"><span class="red">*</span> Vendor</label>
            <?php  echo $form->dropDownList($po,'Vendor_ID', array('0' => 'Unknown Vendor') + $vendorsCP); ?>
            <?php echo $form->error($po,'Vendor_ID'); ?>
        </div>
        <div class="group">
            <label for="Pos_PO_Account_Number">Account Num</label>
            <?php echo $form->textField($po,'PO_Account_Number'); ?>
            <?php echo $form->error($po,'PO_Account_Number'); ?>
        </div>
        <div class="group">
            <?php
            // convert date string to view format
            if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $po->PO_Date)) {
                $po->PO_Date = Helper::convertDateSimple($po->PO_Date);
            }
            ?>
            <label for="Pos_PO_Date"><span class="red">*</span> Date</label>
            <?php echo $form->textField($po,'PO_Date'); ?>
            <?php echo $form->error($po,'PO_Date'); ?>
            <?php
            if (!isset($ap['_errors']["PO_Date"])) {
                echo '<div class="errorMessage grey">Correct date format: mm/dd/yyyy!</div>';
            }
            ?>
        </div>
        <div class="group">
            <label for="Pos_PO_Subtotal"><span class="red">*</span> Subtotal</label>
            <?php echo $form->textField($po,'PO_Subtotal', array('class' => 'dollar_fields base_fields')); ?>
            <?php echo $form->error($po,'PO_Subtotal'); ?>
        </div>
        <div class="group">
            <label for="Pos_PO_Tax">Tax</label>
            <?php echo $form->textField($po,'PO_Tax', array('class' => 'dollar_fields base_fields')); ?>
            <?php echo $form->error($po,'PO_Tax'); ?>
        </div>
        <div class="group">
            <label for="Pos_PO_Delivery_Chg">Delivery Chg</label>
            <?php echo $form->textField($po,'PO_Delivery_Chg', array('class' => 'dollar_fields base_fields')); ?>
            <?php echo $form->error($po,'PO_Delivery_Chg'); ?>
        </div>
        <div class="group">
            <label for="Pos_PO_Other_Chg">Other Chg</label>
            <?php echo $form->textField($po,'PO_Other_Chg', array('class' => 'dollar_fields base_fields')); ?>
            <?php echo $form->error($po,'PO_Other_Chg'); ?>
        </div>
        <div class="group">
            <label for="Pos_PO_Total"><span class="red">*</span> Total</label>
            <?php echo $form->textField($po,'PO_Total',array('readonly'=>true)); ?>
            <?php echo $form->error($po,'PO_Total'); ?>
        </div>
        <div class="group">
            <label for="Pos_Payment_Type"><span class="red">*</span> Payment Type</label>
            <?php echo $form->dropDownList($po,'Payment_Type', array('0' => 'Unknown Payment Type') + $this->paymentTypes); ?>
            <?php echo $form->error($po,'Payment_Type'); ?>
        </div>
        <div class="group">
            <?php
            $htmlOptions = array();
            $hidden = '';
            if ($po->Payment_Type != 'CC') {
                $htmlOptions['disabled'] = 'disabled';
                $hidden = 'style="display:none;"';
            }
            ?>
            <label for="Pos_PO_Card_Last_4_Digits"><span class="red" <?php echo $hidden; ?>>*</span> Last 4 Digits</label>
            <?php echo $form->textField($po,'PO_Card_Last_4_Digits', $htmlOptions); ?>
            <?php echo $form->error($po,'PO_Card_Last_4_Digits'); ?>
        </div>
        <div style="font-family: Arial, sans-serif; text-align: center;font-size: 1.5em;font-weight: bolder;width:300px; "> PO Distributions </div>
        <div class="group de_invoices">
            <label style="width: 100px;">
                <input type="checkbox" id="dists_enabled" name="dists_enabled"
                    <?if(!$dists_empty) echo ' checked ';?>  >


                Distributions</label><br/><br/>
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
                                      <input type="text" class="GL_Code"  data-short-hand="'.$dist['Short_Hand'].'" maxlength="63"  value="' . $dist['PO_Dists_GL_Code'] . '" title="' . $dist['PO_Dists_GL_Code'] . '" name="Dist[' . $key . '][PO_Dists_GL_Code]">
                                  </td>
                                  <td class="width80">
                                      <input type="text" class="gl_amount dollar_fields" value="' . $dist['PO_Dists_Amount'] . '" name="Dist[' . $key . '][PO_Dists_Amount]">
                                  </td>
                                  <td>
                                      <input type="text" maxlength="125" value="' . $dist['PO_Dists_Description'] . '" name="Dist[' . $key . '][PO_Dists_Description]">
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


            <?php echo $form->error($po,'Dists');?>




        </div>
        <?php echo $form->hiddenField($po,'PO_ID'); ?>
        <?php echo $form->hiddenField($po,'Document_ID'); ?>
        <input type="hidden" value="true" name="po_data_entry_form_values">
        <input type="hidden" value="<?=$return_url;?>" name="return_url" id="return_url">

        <div class="center">
            <?php // echo CHtml::submitButton('Save',array('class'=>'button')); ?>
            <button class="button" id="save_po_details_form" >Save</button>
        </div>
    </fieldset>
    <?php $this->endWidget(); ?>
    <br/> <br/>
</div>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/main.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/data_entry_detail.js"></script>

<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/asa_datepicker.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/data_entry_detail_po.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/dataentry_dists_autocomplete.js"></script>

<script type="text/javascript">
    new DistsHandling;
</script>

