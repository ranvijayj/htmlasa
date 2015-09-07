<img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton" style="right: -93px;"/>
<div id="data_entry_left" style="background-color: #ffffff;overflow: auto;z-index: 120;">

    <?php $form=$this->beginWidget('CActiveForm', array (
        'id'=>'ar_data_entry_form',
        'htmlOptions'=>array(
            'class'=>'data_entry_form',
        ),
    ));
    ?>
    <fieldset>
        <?php
        /*
        <div class="group">
            <label style="color: #fff">
                *
            </label>
            <input id="narrow_customers_list" type="text" value="" maxlength="100" name="narrow_customers_list" placeholder="Narrow Cust. by Name or Shortcut">
        </div>
        <div class="group">
            <label for="Ars_Customer_ID"><span class="red">*</span> Customer</label>
            <?php  echo $form->dropDownList($ar,'Customer_ID', array('0' => 'Unknown Customer') + $customers); ?>
            <?php echo $form->error($ar,'Customer_ID'); ?>
        </div>
        */
        ?>
        <div class="group">
            <label for="Ars_Company_Name"><span class="red">*</span> Comp. Name</label>
            <?php echo $form->textField($ar,'Company_Name'); ?>
            <?php echo $form->error($ar,'Company_Name'); ?>
        </div>
        <div class="group">
            <?php
            if ($ar->Invoice_Number == '0') {
                $ar->Invoice_Number = '';
            }
            ?>
            <label for="Ars_Invoice_Number"><span class="red">*</span> Invoice #</label>
            <?php echo $form->textField($ar,'Invoice_Number'); ?>
            <?php echo $form->error($ar,'Invoice_Number'); ?>
        </div>
        <div class="group">
            <?php
            // convert date string to view format
            if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $ar->Invoice_Date)) {
                $ar->Invoice_Date = Helper::convertDateSimple($ar->Invoice_Date);
            }
            ?>
            <label for="Ars_Invoice_Date"><span class="red">*</span> Invoice Date</label>
            <?php echo $form->textField($ar,'Invoice_Date'); ?>
            <?php echo $form->error($ar,'Invoice_Date'); ?>
            <?php
            if (!isset($ar['_errors']["Invoice_Date"])) {
                echo '<div class="errorMessage grey">Correct date format: mm/dd/yyyy!</div>';
            }
            ?>
        </div>
        <div class="group">
            <?php
            if ($ar->Invoice_Amount == '0.00') {
                $ar->Invoice_Amount = '';
            }
            ?>
            <label for="Ars_Invoice_Amount"><span class="red">*</span> Inv. Amount</label>
            <?php echo $form->textField($ar,'Invoice_Amount',array(
                'class'=>'dollar_fields'
            )); ?>
            <?php echo $form->error($ar,'Invoice_Amount'); ?>
        </div>
        <div class="group">
            <label for="Ars_Description">Description</label>
            <?php echo $form->textField($ar,'Description'); ?>
            <?php echo $form->error($ar,'Description'); ?>
        </div>
        <div class="group">
            <label for="Ars_Terms">Terms</label>
            <?php echo $form->textField($ar,'Terms'); ?>
            <?php echo $form->error($ar,'Terms'); ?>
        </div>
        <?php echo $form->hiddenField($ar,'AR_ID'); ?>
        <?php echo $form->hiddenField($ar,'Document_ID'); ?>
        <input type="hidden" value="true" name="ar_data_entry_form_values">
        <div class="center">
            <button class="button" id="submit_ajax_ar_form">Save</button>
        </div>
    </fieldset>
    <?php $this->endWidget(); ?>
</div>


