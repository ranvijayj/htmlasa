<h2>My Profile</h2>

<?php $form=$this->beginWidget('CActiveForm', array (
    'id'=>'registerform',
    'action'=>Yii::app()->createUrl('/myaccount?tab=profile'),
    //'enableClientValidation'=>true,
    'clientOptions'=>array(
        'validateOnSubmit'=>true,
    ),
));

?>
<fieldset>
    <div class="group">
        <?php echo $form->labelEx($person,'First_Name'); ?>
        <?php echo $form->textField($person,'First_Name'); ?>
        <?php echo $form->error($person,'First_Name'); ?>
    </div>
    <div class="group">
        <?php echo $form->labelEx($person,'Last_Name'); ?>
        <?php echo $form->textField($person,'Last_Name'); ?>
        <?php echo $form->error($person,'Last_Name'); ?>
    </div>
    <div class="group">
        <?php echo $form->labelEx($person,'Email'); ?>
        <?php echo $form->textField($person,'Email'); ?>
        <?php echo $form->error($person,'Email'); ?>
    </div>
    <div class="group">
        <?php echo $form->labelEx($person,'Email_Confirmation'); ?>
        <?php echo $form->textField($person,'Email_Confirmation'); ?>
        <?php echo $form->error($person,'Email_Confirmation'); ?>
    </div>
    <div class="group">
        <?php echo $form->labelEx($person,'Direct_Phone'); ?>
        <?php echo $form->textField($person,'Direct_Phone'); ?>
        <?php echo $form->error($person,'Direct_Phone'); ?>
    </div>
    <div class="group">
        <?php echo $form->labelEx($person,'Mobile_Phone'); ?>
        <?php echo $form->textField($person,'Mobile_Phone'); ?>
        <?php echo $form->error($person,'Mobile_Phone'); ?>
    </div>
    <div class="group">
        <?php echo $form->labelEx($person,'Direct_Fax'); ?>
        <?php echo $form->textField($person,'Direct_Fax'); ?>
        <?php echo $form->error($person,'Direct_Fax'); ?>
    </div>
    <div class="group">
        <?php echo $form->labelEx($person_adress,'Address1'); ?>
        <?php echo $form->textField($person_adress,'Address1'); ?>
        <?php echo $form->error($person_adress,'Address1'); ?>
    </div>
    <div class="group">
        <?php echo $form->labelEx($person_adress,'Address2'); ?>
        <?php echo $form->textField($person_adress,'Address2'); ?>
        <?php echo $form->error($person_adress,'Address2'); ?>
    </div>
    <div class="group">
        <?php echo $form->labelEx($person_adress,'City'); ?>
        <?php echo $form->textField($person_adress,'City'); ?>
        <?php echo $form->error($person_adress,'City'); ?>
    </div>
    <div class="group">
        <?php echo $form->labelEx($person_adress,'State'); ?>
        <?php echo $form->textField($person_adress,'State'); ?>
        <?php echo $form->error($person_adress,'State'); ?>
    </div>
    <div class="group">
        <?php echo $form->labelEx($person_adress,'ZIP'); ?>
        <?php echo $form->textField($person_adress,'ZIP'); ?>
        <?php echo $form->error($person_adress,'ZIP'); ?>
    </div>
    <div class="group">
        <?php echo $form->labelEx($person_adress,'Country'); ?>
        <?php echo $form->textField($person_adress,'Country'); ?>
        <?php echo $form->error($person_adress,'Country'); ?>
    </div>
    <br><br><br>
    <div class="group">
        <?php echo $form->labelEx($password_form,'oldPass'); ?>
        <?php echo $form->passwordField($password_form,'oldPass'); ?>
        <?php echo $form->error($password_form,'oldPass'); ?>
    </div>
    <div class="group">
        <?php echo $form->labelEx($password_form,'newPass'); ?>
        <?php echo $form->passwordField($password_form,'newPass'); ?>
        <?php echo $form->error($password_form,'newPass'); ?>
    </div>
    <div class="group">
        <?php echo $form->labelEx($password_form,'newPass2'); ?>
        <?php echo $form->passwordField($password_form,'newPass2'); ?>
        <?php echo $form->error($password_form,'newPass2'); ?>
    </div>
    <input type="hidden" value="true" name="profile_form">
    <input name="submit" type="submit" value="submit" class="hide">
</fieldset>
<?php $this->endWidget(); ?>