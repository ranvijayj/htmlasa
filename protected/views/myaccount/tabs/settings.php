<h2>Settings</h2>

<?php


$restricted_users_array=array('user','data_entry_clerk');
$alowed_users_array=array('client_admin','db_admin','admin','approver','processor');
if(in_array($user_role,$alowed_users_array)){


$form=$this->beginWidget('CActiveForm', array (
    'id'=>'settingsform',
    'action'=>Yii::app()->createUrl('/myaccount?tab=settings'),
));

?>

<fieldset>
    <div class="group radio_group">
        <h4 class="settings_option_header">Email notifications for Documents awaiting Approval:</h4>
        <?php echo $form->dropDownList($user_settings,'Notification', array('1' => 'Send right after I have updates', '0' => 'Send once a day')); ?>
        <?php echo $form->error($user_settings,'Notification'); ?>
    </div>
    <div class="group radio_group">
        <h4 class="settings_option_header">Defaut Document Type on Upload:</h4>
        <?php echo $form->dropDownList($user_settings,'Default_Doc_Type',
            array(
                Documents::W9 =>'W9',
                Documents::PO =>'Purchase Order',
                Documents::AP =>'Accounts Payable',
                Documents::BU =>'Backup',
                Documents::PM =>'Payment',
                Documents::LB =>'Library',
                Documents::GF =>'General',
                Documents::PR =>'Payroll',
                Documents::PC =>'Petty Cash (Expense)',
                Documents::JE =>'Journal Entry',
                Documents::AR =>'Accounts Receivable',
            )); ?>
        <?php echo $form->error($user_settings,'Default_Doc_Type'); ?>
    </div>
    <div class="group radio_group">
        <h4 class="settings_option_header">Defaut Project on Login:</h4>
        <?php echo $form->dropDownList($user_settings,'Default_Project', $all_user_projects); ?>
        <?php echo $form->error($user_settings,'Default_Project'); ?>
    </div>
    <div class="group radio_group">
        <h4 class="settings_option_header">Defaut Bank Account on Pmt:</h4>
        <?php echo $form->dropDownList($user_settings,'Default_Bank_Acct', $all_user_bank_accounts); ?>
        <?php echo $form->error($user_settings,'Default_Bank_Acct'); ?>
    </div>
    <div class="group radio_group">
        <h4 class="settings_option_header">Default Batch Export Type:</h4>
        <?php echo $form->dropDownList($user_settings,'Default_Export_Type', Batches::$exportTypes); ?>
        <?php echo $form->error($user_settings,'Default_Export_Type'); ?>
    </div>
    <div class="group radio_group">
        <h4 class="settings_option_header">Default Batch Export Format:</h4>
        <?php echo $form->dropDownList($user_settings,'Default_Export_Format', Batches::$exportFormats); ?>
        <?php echo $form->error($user_settings,'Default_Export_Format'); ?>
    </div>

    <div class="group radio_group">
        <h4 class="settings_option_header">Do automatic monthly payment</h4>
        <?php echo $form->dropDownList($user_settings,'Automatic_CC_Charge', array('No','Yes')); ?>
        <?php echo $form->error($user_settings,'Automatic_CC_Charge'); ?>
    </div>
    <div class="group radio_group">
        <h4 class="settings_option_header">Due Date Terms</h4>
        <?php echo $form->textField($user_settings,'Due_Date_Terms'); ?>
        <?php echo $form->error($user_settings,'Due_Date_Terms'); ?>
    </div>

    <div class="group radio_group">
        <h4 class="settings_option_header">Defaut Share W9 Access Type:</h4>
        <?php echo $form->dropDownList($user_settings,'Default_W9_Share_Type', UsersSettings::$w9ShareTypes); ?>
        <?php echo $form->error($user_settings,'Default_W9_Share_Type'); ?>
    </div>

</fieldset>
<input type="hidden" value="true" name="settings_form">
<input name="submit" type="submit" value="submit" class="hide">
<?php
    $this->endWidget();
}
?>