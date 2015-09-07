<div class="modal_box" id="po_formatting_block" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <h1>Edit PO Formatting for Project</h1>
    <?php
        if (isset($this->po_formatting) && $this->po_formatting !== false) {
            $model = $this->po_formatting;
        } else {
            $model = new PoFormatting();
        }
    ?>
    <?php $form=$this->beginWidget('CActiveForm', array (
        'id'=>'po_formatting_form',
        'action'=>Yii::app()->createUrl('/myaccount?tab=com_info'),
    )); ?>
<div style="float: left;" id="userInfo">
    <div class="row">
        <?php echo $form->labelEx($model,'PO_Format_Client_Name'); ?>
        <?php echo $form->textField($model,'PO_Format_Client_Name',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'PO_Format_Client_Name'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'PO_Format_Project_Name'); ?>
        <?php echo $form->textField($model,'PO_Format_Project_Name',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'PO_Format_Project_Name'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'PO_Format_Job_Name'); ?>
        <?php echo $form->textField($model,'PO_Format_Job_Name',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'PO_Format_Job_Name'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'PO_Format_Starting_Num'); ?>
        <?php echo $form->textField($model,'PO_Format_Starting_Num',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'PO_Format_Starting_Num'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'PO_Format_Address'); ?>
        <?php echo $form->textField($model,'PO_Format_Address',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'PO_Format_Address'); ?>
    </div>
</div>
    <div style="float: right; padding-left: 13px; border-left: 1px dotted #a2aEF8;" id="companyInfo">
        <div class="row">
            <?php echo $form->labelEx($model,'PO_Format_City_St_ZIP'); ?>
            <?php echo $form->textField($model,'PO_Format_City_St_ZIP',array('class'=>'txtfield')); ?>
            <?php echo $form->error($model,'PO_Format_City_St_ZIP'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model,'PO_Format_Phone'); ?>
            <?php echo $form->textField($model,'PO_Format_Phone',array('class'=>'txtfield')); ?>
            <?php echo $form->error($model,'PO_Format_Phone'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model,'PO_Format_Sig_Req'); ?>
            <?php echo $form->dropDownList($model,'PO_Format_Sig_Req',array('No', 'Yes'), array('class'=>'txtfield')); ?>
            <?php echo $form->error($model,'PO_Format_Sig_Req'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model,'PO_Format_Addl_Language'); ?>
            <?php echo $form->textArea($model,'PO_Format_Addl_Language',array('class'=>'txtfield')); ?>
            <?php echo $form->error($model,'PO_Format_Addl_Language'); ?>
        </div>
    </div>
    <div style="clear: both; height: 10px;"></div>
    <?php echo CHtml::hiddenField('current_po_formatting_id', $poFormattingId, array('id' => 'current_po_formatting_id')); ?>
    <div class="center">
        <?php echo CHtml::submitButton('Save',array('class'=>'button hidemodal')); ?>
    </div>
    <?php $this->endWidget(); ?>
</div>