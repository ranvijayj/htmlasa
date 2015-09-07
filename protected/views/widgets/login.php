<div class="modal_box" id="loginmodal" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <h1>User Login</h1>
    <?php
        if (isset($this->login_model) && $this->login_model !== false) {
            $model = $this->login_model;
        } else {
            $model = new LoginForm();
        }
    ?>
    <?php $form=$this->beginWidget('CActiveForm', array (
        'id'=>'loginform',
        'action'=>Yii::app()->createUrl('/site/login'),
        'enableClientValidation'=>true,
        'clientOptions'=>array(
            'validateOnSubmit'=>true,
        ),
    )); ?>

    <div class="row">
        <?php echo $form->labelEx($model,'username'); ?>
        <?php echo $form->textField($model,'username',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'username'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'password'); ?>
        <?php echo $form->passwordField($model,'password',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'password'); ?>
        <?php echo $form->hiddenField($model,'timezoneOffset',array('id'=>'user_timezone_offset')); ?>
        <input type="hidden" id="user_resolution" name=LoginForm[resolution]>
    </div>
    <div class="row rememberMe">
        <?php echo $form->checkBox($model,'rememberMe'); ?>
        <?php echo $form->label($model,'rememberMe'); ?>
        <?php echo $form->error($model,'rememberMe'); ?>
    </div>

    <div class="center">
        <?php //echo CHtml::submitButton('Login',array('id'=>'loginbtn','class'=>'flatbtn-blu hidemodal')); ?>
        <button class="flatbtn-blu" id="loginbtn">Login</button>
    </div>

    <div class="row">
        <?php echo CHtml::link('Register',array('/site/register')); ?> <span style='float: right;'><?php echo CHtml::link('Forgot password?',array('/site/forgotpassword')); ?></span>
    </div>

    <?php $this->endWidget(); ?>

</div>