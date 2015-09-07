<div class="modal_box" id="newcompanymodal" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <h1>New Company</h1>
    <?php
        if (isset($this->new_company) && $this->new_company !== false) {
            $model = $this->new_company;
        } else {
            $model = new NewCompanyForm();
        }
    ?>
    <?php $form=$this->beginWidget('CActiveForm', array (
        'id'=>'newcompanyform',
        'action'=>Yii::app()->createUrl('/myaccount'),
        'clientOptions'=>array(
            'validateOnSubmit'=>true,
        ),
    )); ?>
<div style="float: left;" id="userInfo">
    <div class="row">
        <?php echo $form->labelEx($model,'Company_Name'); ?>
        <?php echo $form->textField($model,'Company_Name',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Company_Name'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Fed_ID'); ?>
        <?php echo $form->textField($model,'Fed_ID',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Fed_ID'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Address1'); ?>
        <?php echo $form->textField($model,'Address1',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Address1'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'City'); ?>
        <?php echo $form->textField($model,'City',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'City'); ?>
    </div>
    <div class="row">
        <div style="width: 110px; float: left;">
            <?php echo $form->labelEx($model,'State'); ?>
            <?php echo $form->textField($model,'State',array('class'=>'txtfield', 'style' => 'width: 100px;')); ?>
            <?php echo $form->error($model,'State'); ?>
        </div>
        <div style="width: 140px; float: right;">
            <?php echo $form->labelEx($model,'ZIP'); ?>
            <?php echo $form->textField($model,'ZIP',array('class'=>'txtfield', 'style' => 'width: 130px;')); ?>
            <?php echo $form->error($model,'ZIP'); ?>
        </div>
        <div style="clear: both;"></div>
    </div>
</div>
    <div style="clear: both; height: 10px;"></div>
    <div class="center">
        <?php echo CHtml::submitButton('Create',array('id'=>'newcompanybtn','class'=>'button hidemodal')); ?>
    </div>
    <?php $this->endWidget(); ?>
</div>