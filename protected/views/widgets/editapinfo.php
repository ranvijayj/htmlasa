<div class="modal_box" id="edit_ap_info" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt='' class="hidemodal cancelbutton"/>
    <h1>Edit AP Info</h1>
    <?php
        $form=$this->beginWidget('CActiveForm', array (
        'id'=>'edit_ap_info_form',
    )); ?>
<div style="float: left;">
    <div class="row">
        <label for="Aps_Vendor_ID"><span class="red">*</span> Vendor</label>
        <?php echo $form->dropDownList($ap,'Vendor_ID', array('0' => 'Unknown Vendor') + $vendorsCP,array('class'=>'txtfield')); ?>
        <?php echo $form->error($ap,'Vendor_ID'); ?>
    </div>
    <div class="row">
        <?php
        if ($ap->Invoice_Number == '0') {
            $ap->Invoice_Number = '';
        }
        ?>
        <label for="Aps_Invoice_Number"><span class="red">*</span> Inv. Number</label>
        <?php echo $form->textField($ap,'Invoice_Number',array('class'=>'txtfield')); ?>
        <?php echo $form->error($ap,'Invoice_Number'); ?>
    </div>
    <div class="row">
        <label for="Aps_Invoice_Reference"><span class="red">*</span> Description</label>
        <?php echo $form->textField($ap,'Invoice_Reference',array('class'=>'txtfield')); ?>
        <?php echo $form->error($ap,'Invoice_Reference'); ?>
    </div>
    <div class="row">
        <div style="width: 125px; float: left;">
            <?php
            // convert date string to view format
            if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $ap->Invoice_Date)) {
                $ap->Invoice_Date = Helper::convertDate($ap->Invoice_Date);
            }
            ?>
            <label for="Aps_Invoice_Date"><span class="red">*</span> Inv. Date</label>
            <?php echo $form->textField($ap,'Invoice_Date',array('class'=>'txtfield', 'style' => 'width: 115px;')); ?>
            <?php echo $form->error($ap,'Invoice_Date'); ?>
        </div>
        <div style="width: 125px; float: right;">
            <?php
            // convert date string to view format
            if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $ap->Invoice_Due_Date)) {
                $ap->Invoice_Due_Date = Helper::convertDate($ap->Invoice_Due_Date);
            }
            ?>
            <label for="Aps_Invoice_Due_Date">Inv. Due Date</label>
            <?php echo $form->textField($ap,'Invoice_Due_Date',array('class'=>'txtfield','style' => 'width: 115px;')); ?>
            <?php echo $form->error($ap,'Invoice_Due_Date'); ?>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div class="row">
        <div style="width: 125px; float: left;">
            <label for="Aps_Invoice_Amount"><span class="red">*</span> Inv. Amount</label>
            <?php echo $form->textField($ap,'Invoice_Amount',array('class'=>'txtfield', 'style' => 'width: 115px;')); ?>
            <?php echo $form->error($ap,'Invoice_Amount'); ?>
        </div>
        <div style="width: 125px; float: right;">
            <label for="po_number">PO Number</label>
            <input type="text" value="<?php echo $relatedPONumber; ?>" id="po_number" name="po_number" style="width: 115px;" class="txtfield">
            <?php
                if ($relatedPOError != '') {
                    echo '<div class="errorMessage">' . $relatedPOError . '</div>';
                }
            ?>

        </div>
        <div style="clear: both;"></div>
    </div>
    <?php echo $form->hiddenField($ap,'AP_ID'); ?>
</div>
    <div style="clear: both; height: 10px;"></div>
    <div class="center">
        <?php echo CHtml::submitButton('Save',array('id'=>'editvendorbtn','class'=>'button hidemodal')); ?>
    </div>
    <?php $this->endWidget(); ?>
</div>