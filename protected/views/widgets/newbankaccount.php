<div class="modal_box" id="new_bank_account" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <h1>New Bank Account</h1>
    <?php
        if (isset($this->new_bank_account) && $this->new_bank_account !== false) {
            $model = $this->new_bank_account;
        } else {
            $model = new BankAcctNums();
        }
    ?>
    <?php $form=$this->beginWidget('CActiveForm', array (
        'id'=>'new_bank_account_form',
        'action'=>Yii::app()->createUrl('/myaccount?tab=com_info'),
        'clientOptions'=>array(
            'validateOnSubmit'=>true,
        ),
    )); ?>
<div style="float: left;">
    <div class="row">
        <?php echo $form->labelEx($model,'Account_Number'); ?>
        <?php echo $form->textField($model,'Account_Number',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Account_Number'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Account_Name'); ?>
        <?php echo $form->textField($model,'Account_Name',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Account_Name'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Bank_Name'); ?>
        <?php echo $form->textField($model,'Bank_Name',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Bank_Name'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Bank_Routing'); ?>
        <?php echo $form->textField($model,'Bank_Routing',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Bank_Routing'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Bank_SWIFT'); ?>
        <?php echo $form->textField($model,'Bank_SWIFT',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Bank_SWIFT'); ?>
    </div>
    <?php echo CHtml::hiddenField('acct_id', $acctId, array('id' => 'acct_id')); ?>
</div>
    <div style="clear: both; height: 10px;"></div>
    <div class="center">
        <?php echo CHtml::submitButton('Save',array('id'=>'newbankacctbtn','class'=>'button hidemodal')); ?>
        <?php echo CHtml::resetButton('Cancel',array('id'=>'cancelbankacctbtn','class'=>'button hidemodal')); ?>
    </div>
    <?php $this->endWidget(); ?>
</div>