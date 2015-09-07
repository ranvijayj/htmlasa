<div class="modal_box" id="edit_vendor_info" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <h1>Edit Vendor</h1>
    <?php
        $url = '/vendor/detail';
        if ($page > 1) {
            $url .= '?page=' . $page;
        }

        $form=$this->beginWidget('CActiveForm', array (
        'id'=>'edit_vendor_info_form',
        'action'=>Yii::app()->createUrl($url),
        'clientOptions'=>array(
            'validateOnSubmit'=>true,
        ),
    )); ?>
<div style="float: left;">
    <div class="row">
        <?php echo $form->labelEx($model,'Vendor_ID_Shortcut'); ?>
        <?php echo $form->textField($model,'Vendor_ID_Shortcut',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Vendor_ID_Shortcut'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Vendor_Contact'); ?>
        <?php echo $form->textField($model,'Vendor_Contact',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Vendor_Contact'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Vendor_Phone'); ?>
        <?php echo $form->textField($model,'Vendor_Phone',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Vendor_Phone'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Vendor_Name_Checkprint'); ?>
        <?php echo $form->textField($model,'Vendor_Name_Checkprint',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Vendor_Name_Checkprint'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Vendor_Checkprint_Add1'); ?>
        <?php echo $form->textField($model,'Vendor_Checkprint_Add1',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Vendor_Checkprint_Add1'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Vendor_Checkprint_Add2'); ?>
        <?php echo $form->textField($model,'Vendor_Checkprint_Add2',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Vendor_Checkprint_Add2'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Vendor_Checkprint_City'); ?>
        <?php echo $form->textField($model,'Vendor_Checkprint_City',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Vendor_Checkprint_City'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Vendor_Checkprint_ST'); ?>
        <?php echo $form->textField($model,'Vendor_Checkprint_ST',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Vendor_Checkprint_ST'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Vendor_Checkprint_Zip'); ?>
        <?php echo $form->textField($model,'Vendor_Checkprint_Zip',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Vendor_Checkprint_Zip'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Vendor_Checkprint_Country'); ?>
        <?php echo $form->textField($model,'Vendor_Checkprint_Country',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Vendor_Checkprint_Country'); ?>
    </div>


    <div class="row">
        <?php echo $form->labelEx($model,'Vendor_1099'); ?>
        <?php echo $form->dropDownList($model,'Vendor_1099', array('0' => 'No', '1' => 'Yes'), array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Vendor_1099'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Vendor_Default_GL'); ?>
        <?php echo $form->textField($model,'Vendor_Default_GL',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Vendor_Default_GL'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Vendor_Default_GL_Note'); ?>
        <?php echo $form->textField($model,'Vendor_Default_GL_Note',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Vendor_Default_GL_Note'); ?>
    </div>
    <?php echo $form->hiddenField($model,'Vendor_ID'); ?>
</div>
    <div style="clear: both; height: 10px;"></div>
    <div class="center">
        <?php echo CHtml::submitButton('Save',array('id'=>'editvendorbtn','class'=>'button hidemodal')); ?>
    </div>
    <?php $this->endWidget(); ?>
</div>