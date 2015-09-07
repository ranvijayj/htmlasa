<div class="modal_box" id="support_request_div" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <div style="margin: 0px auto;text-align: center;"><h3> Support request form </h3> </div>

    <?php $form=$this->beginWidget('CActiveForm', array (
        'id'=>'support_requests_form',
        'action'=>Yii::app()->createUrl('/supportrequests/add'),
        'enableClientValidation'=>true,
        'clientOptions'=>array(
            'validateOnSubmit'=>true,
        ),
    )); ?>

        <?php
            $model = new SupportRequests();

        ?>


        <div class="row">
            Select category :
            <?echo CHtml::dropDownList('request_category','',SupportRequests::getProblemCategories());?>
        </div>
    <br/><br/>
    <div class="row">
        <?php echo $form->labelEx($model,'User_Login'); ?>
        <?php echo $form->textField($model,'User_Login',array('size'=>30,'maxlength'=>30)); ?>
        <?php echo $form->error($model,'User_Login'); ?>
    </div>
    <br/>
    <div class="row">
        <?php echo $form->labelEx($model,'User_Email'); ?>
        <?php echo $form->textField($model,'User_Email',array('size'=>30,'maxlength'=>80)); ?>
        <?php echo $form->error($model,'User_Email'); ?>
    </div>
    <br/>
    <div class="row">
        <?php echo $form->labelEx($model,'User_Phone'); ?>
        <?php echo $form->textField($model,'User_Phone',array('size'=>30,'maxlength'=>30)); ?>
        <?php echo $form->error($model,'User_Phone'); ?>
    </div>
    <br/>
    <div class="row">
        <?php echo $form->labelEx($model,'User_Message'); ?><br/>
        <?php echo $form->textArea($model,'User_Message',array(
            'maxlength' => 300,
            'rows' => 6,
            'cols' => 70
        )); ?>
        <?php echo $form->error($model,'User_Message'); ?>
    </div>
    <br/><br/>
    <div class="row buttons">
        <?php echo CHtml::submitButton($model->isNewRecord ? 'Request' : 'Save'); ?>
    </div>



    <?php $this->endWidget(); ?>



</div>
<script>
    $(document).ready(function() {

    });

</script>
