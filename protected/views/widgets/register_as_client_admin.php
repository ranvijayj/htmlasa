<div class="modal_box" id="registerasclientadminmodal" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <h1>User Register</h1>
    <?php
        if (isset($this->register_model_as_client_admin) && $this->register_model_as_client_admin !== false) {
            $model =$this->register_model_as_client_admin;
        } else {
            $model = new RegisterAsClientAdminForm();
        }
    ?>
    <?php $form=$this->beginWidget('CActiveForm', array (
        'id'=>'registerasclientadminform',
        'clientOptions'=>array(
            'validateOnSubmit'=>true,
        ),
    )); ?>
    <?php if (isset($this->company)) { ?>
    <div class="row">
        <h3>For Company: <?php echo $this->company; ?></h3>
    </div>
    <?php } ?>
    <div class="row">
        <?php echo $form->labelEx($model,'User_Login'); ?>
        <?php echo $form->textField($model,'User_Login',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'User_Login'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'First_Name'); ?>
        <?php echo $form->textField($model,'First_Name',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'First_Name'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Last_Name'); ?>
        <?php echo $form->textField($model,'Last_Name',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Last_Name'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Email'); ?>
        <?php echo $form->textField($model,'Email',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Email'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Auth_Code'); ?>
        <?php echo $form->textField($model, 'Auth_Code', array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Auth_Code'); ?>
    </div>
    <div class="center">
        <?php echo CHtml::submitButton('Register',array('class'=>'flatbtn-blu hidemodal')); ?>
    </div>
    <?php $this->endWidget(); ?>
</div>