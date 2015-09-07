<img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton" style="right: -93px;"/>

<div id="data_entry_left" style="background-color: #ffffff;overflow: auto;z-index: 120;">


    <?php $form=$this->beginWidget('CActiveForm', array (
        'id'=>'payroll_data_entry_form',
        'htmlOptions'=>array(
            'class'=>'data_entry_form',
        ),
    )); ?>

    <fieldset>
        <fieldset>
            <div class="group">
                <?php
                // convert date string to view format
                if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $payroll->Week_Ending)) {
                    $payroll->Week_Ending = Helper::convertDateSimple($payroll->Week_Ending);
                }
                ?>
                <label for="Payrolls_Week_Ending"><span class="red">*</span> Week Ending</label>
                <?php echo $form->textField($payroll,'Week_Ending'); ?>
                <?php echo $form->error($payroll,'Week_Ending'); ?>
                <?php
                if (!isset($payroll['_errors']["Week_Ending"])) {
                    echo '<div class="errorMessage grey">Correct format: mm/dd/yyyy!</div>';
                }
                ?>
            </div>
            <div class="group">
                <label for="Payrolls_Payroll_Type_ID"><span class="red">*</span> Type</label>
                <?php  echo $form->dropDownList($payroll,'Payroll_Type_ID', array('0' => 'Unknown Payroll Type') + $payrollTypes); ?>
                <?php echo $form->error($payroll,'Payroll_Type_ID'); ?>
            </div>
            <div class="group">
                <label for="Payrolls_Submitted"> Submitted</label>
                <?php echo $form->checkBox($payroll,'Submitted'); ?>
                <?php echo $form->error($payroll,'Submitted'); ?>
            </div>
            <div class="group">
                <label for="Payrolls_Version">Version</label>
                <?php echo $form->textField($payroll,'Version'); ?>
                <?php echo $form->error($payroll,'Version'); ?>
            </div>
            <?php echo $form->hiddenField($payroll,'Payroll_ID'); ?>
            <?php echo $form->hiddenField($payroll,'Document_ID'); ?>
            <input type="hidden" value="true" name="payroll_data_entry_form_values">
            <div class="center">
                <button class="button" id="submit_ajax_payr_form">Save</button>
            </div>
        </fieldset>
    </fieldset>
    <?php $this->endWidget(); ?>

</div>


