<h2>Credit Card Information</h2>

<?php
$restricted_users_array=array('user','data_entry_clerk');
$alowed_users_array=array('client_admin','db_admin','admin','approver','processor');
if(in_array($user_role,$alowed_users_array)){


$form=$this->beginWidget('CActiveForm', array (
    'id'=>'ccform',
    'action'=>Yii::app()->createUrl('myaccount?tab=ccard'),
));
?>
<fieldset>
    <div class="group">
        <?php echo $form->labelEx($cCard,'CC_Name'); ?>
        <?php echo $form->textField($cCard,'CC_Name'); ?>
        <?php echo $form->error($cCard,'CC_Name'); ?>
    </div>
    <div class="group">
        <?php echo $form->labelEx($cCard,'CC_Number'); ?>
        <?php echo $form->textField($cCard,'CC_Number'); ?>
        <?php echo $form->error($cCard,'CC_Number'); ?>
    </div>
    <div class="group">
        <?php echo $form->labelEx($cCard,'CC_Type_ID'); ?>
        <?php echo $form->dropDownList($cCard,'CC_Type_ID', array(0 => 'Chose Card Type') + $ccTypes); ?>
        <?php echo $form->error($cCard,'CC_Type_ID'); ?>
    </div>
    <div class="group">
        <?php echo $form->labelEx($cCard,'Exp_Month'); ?>
        <?php echo $form->dropDownList($cCard,'Exp_Month', array(
            'Chose Exp. Month',
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        )); ?>
        <?php echo $form->error($cCard,'Exp_Month'); ?>
    </div>
    <div class="group">
        <?php echo $form->labelEx($cCard,'Exp_Year'); ?>
        <?php echo $form->textField($cCard,'Exp_Year'); ?>
        <?php echo $form->error($cCard,'Exp_Year'); ?>
    </div>
    <input type="hidden" value="true" name="ccform">
    <input name="submit" type="submit" value="submit" class="hide">
</fieldset>
<?php $this->endWidget(); } ?>
