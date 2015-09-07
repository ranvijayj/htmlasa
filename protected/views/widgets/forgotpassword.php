<div class="modal_box" id="forgotpasswordbox" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <h1>Reset Password</h1>
    <?php
    if (isset($this->forgot_password_model) && $this->forgot_password_model !== false) {
        $model = $this->forgot_password_model;
    } else {
        $model = new ForgotPasswordForm();
    }
    ?>
    <?php $form=$this->beginWidget('CActiveForm', array (
        'id'=>'forgotpasswordform',
        'action'=>Yii::app()->createUrl('/site/forgotpassword'),
        'enableClientValidation'=>true,
        'clientOptions'=>array(
            'validateOnSubmit'=>true,
        ),
    )); ?>

    <div class="row">
        <?php //commented out 24.03.2015 according bug tracking file #39 ?>
        <?php //echo $form->labelEx($model,'username'); ?>
        <?php //echo $form->textField($model,'username',array('class'=>'txtfield')); ?>
        <?php //echo $form->error($model,'username'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'email'); ?>
        <?php echo $form->textField($model,'email',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'email'); ?>
    </div>

    <div class="center">
        <?php echo CHtml::submitButton('Send password',array('id'=>'sendpwdbtn','class'=>'flatbtn-blu hidemodal', 'style' => 'width: 200px;')); ?>
    </div>
    <?php $this->endWidget(); ?>
</div>