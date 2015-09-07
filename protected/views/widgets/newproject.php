<div class="modal_box" id="new_project_box" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <h1>New Project</h1>
    <?php
        if (isset($this->new_project) && $this->new_project !== false) {
            $model = $this->new_project;
        } else {
            $model = new Projects();
        }
    ?>
    <?php $form=$this->beginWidget('CActiveForm', array (
        'id'=>'new_project_form',
        'action'=>Yii::app()->createUrl('/myaccount?tab=com_info'),
        'clientOptions'=>array(
            'validateOnSubmit'=>true,
        ),
    )); ?>
<div style="float: left;">
    <div class="row">
        <?php echo $form->labelEx($model,'Project_Name'); ?>
        <?php echo $form->textField($model,'Project_Name',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Project_Name'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Project_Description'); ?>
        <?php echo $form->textField($model,'Project_Description',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Project_Description'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Project_Prod_Number'); ?>
        <?php echo $form->textField($model,'Project_Prod_Number',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Project_Prod_Number'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'PO_Starting_Number'); ?>
        <?php echo $form->textField($model,'PO_Starting_Number',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'PO_Starting_Number'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Ck_Req_Starting_Numb'); ?>
        <?php echo $form->textField($model,'Ck_Req_Starting_Numb',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Ck_Req_Starting_Numb'); ?>
    </div>
    <?php echo CHtml::hiddenField('current_project_id', $projectId, array('id' => 'current_project_id')); ?>
</div>
    <div style="clear: both; height: 10px;"></div>
    <div class="center">
        <?php echo CHtml::submitButton('Save',array('id'=>'newprojectbtn','class'=>'button hidemodal')); ?>
        <?php echo CHtml::resetButton('Cancel',array('id'=>'cancelprojectbtn','class'=>'button hidemodal')); ?>
    </div>
    <?php $this->endWidget(); ?>
</div>