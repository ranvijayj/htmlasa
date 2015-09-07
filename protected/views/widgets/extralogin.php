<div class="modal_box" id="extraloginmodal" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <div style="margin: 0px auto;text-align: center;"><h3> You are trying to log in from unrecognized device </h3> </div>

    <?php $form=$this->beginWidget('CActiveForm', array (
        'id'=>'extraloginform',
        'action'=>Yii::app()->createUrl('/site/login'),
        'enableClientValidation'=>true,
        'clientOptions'=>array(
            'validateOnSubmit'=>true,
        ),
    )); ?>

    <?
    if (count($users_questions)>0) {

        echo '<h4> Please answer next questions: </h4>';
        $i=1;
        foreach ($users_questions as $question) {
            echo $i.') '.$question['Text'];//echo '('.$question['Hint'].')<br/>';
            echo '<div class="row" style="padding-left: 10px;margin-bottom:15px;margin-top: 9px; ">';
            echo '<input type="text" class="answer" name=answers['.$question['Question_ID'].'] autocomplete="off">';
            echo '<br/>';
            $i++;
            echo '</div>';
        }
        echo '<div style="color:red;font-size:10px;">'.$answers_errors.'</div>';
    }
    ?>

    <?php
        if (isset($this->login_model) && $this->login_model !== false) {
            $model = $this->login_model;
        } else {
            $model = new LoginForm();
        }
    ?>


    <div class="row">
        <?php echo $form->labelEx($model,'username'); ?>
        <?php echo $form->textField($model,'username',array('class'=>'txtfield','id'=>'extra_user_login')); ?>
        <?php echo $form->error($model,'username'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'password'); ?>
        <?php echo $form->passwordField($model,'password',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'password'); ?>
        <?php echo $form->hiddenField($model,'timezoneOffset',array('id'=>'extra_user_timezone_offset')); ?>
        <input type="hidden" id="extra_user_resolution" name=LoginForm[resolution]>
    </div>
    <div class="row rememberMe">
        <input type="checkbox" checked="true" id="remember_device" >remember this device
    </div>

    <div class="center">
        <?php //echo CHtml::submitButton('Login',array('id'=>'loginbtn','class'=>'flatbtn-blu hidemodal')); ?>
        <button class="flatbtn-blu" id="loginbtn">Login</button>
    </div>

    <div class="row">
        <?php //echo CHtml::link('Register',array('/site/register')); ?> <span style='float: right;'><?php //echo CHtml::link('Forgot password?',array('/site/forgotpassword')); ?></span>
    </div>

    <?php $this->endWidget(); ?>
    <br/>
    <div style="margin: 0px auto;text-align: center;"> <a href="#" class="support_request">Can't login? Get help from support!</a></div>



</div>
<script>
    $(document).ready(function() {
        var nowdate=new Date();
        var tz=-nowdate.getTimezoneOffset()*60;
        $('#extra_user_timezone_offset').val(tz);
        $('#extra_user_resolution').val(screen.width+'x'+screen.height+'x'+screen.colorDepth);

    });

</script>
