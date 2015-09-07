<h2>Company Information</h2>

<?php $form=$this->beginWidget('CActiveForm', array (
    'id'=>'registerform',
    'action'=>Yii::app()->createUrl('/myaccount?tab=com_info'),
    //'enableClientValidation'=>true,
    'clientOptions'=>array(
        'validateOnSubmit'=>true,
    ),
));

$additionParams = array();
if (!$client_admin ) {
    $additionParams['disabled'] = 'disabled';
    $read_only='disabled';
} else $read_only='';


$restricted_users_array=array('user','data_entry_clerk');
$alowed_users_array=array('client_admin','db_admin','admin','approver','processor');




?>
<?if ($company) {?>
    <fieldset>
        <div class="group">
            <?php echo $form->labelEx($company,'Company_Name'); ?>
            <?php echo $form->textField($company,'Company_Name', $additionParams); ?>
            <?php echo $form->error($company,'Company_Name'); ?>
        </div>

        <div class="group">
            <?php echo $form->labelEx($client,'Client_Logo_Name'); ?>
            <?php echo $form->textField($client,'Client_Logo_Name', $additionParams); ?>
            <?php echo $form->error($company,'Client_Logo_Name'); ?>
        </div>


        <div class="group">
            <?php if(in_array($user_role,$alowed_users_array)){ ?>
            <?php echo $form->labelEx($company,'Company_Fed_ID'); ?>
            <?php echo $form->textField($company,'Company_Fed_ID', $additionParams); ?>
            <?php echo $form->error($company,'Company_Fed_ID'); ?>
            <?php } ?>
        </div>
        <div class="group">
            <?php echo $form->labelEx($company_adress,'Address1'); ?>
            <?php echo $form->textField($company_adress,'Address1', $additionParams); ?>
            <?php echo $form->error($company_adress,'Address1'); ?>
        </div>
        <div class="group">
            <?php echo $form->labelEx($company_adress,'Address2'); ?>
            <?php echo $form->textField($company_adress,'Address2', $additionParams); ?>
            <?php echo $form->error($company_adress,'Address2'); ?>
        </div>
        <div class="group">
            <?php echo $form->labelEx($company_adress,'City'); ?>
            <?php echo $form->textField($company_adress,'City', $additionParams); ?>
            <?php echo $form->error($company_adress,'City'); ?>
        </div>
        <div class="group">
            <?php echo $form->labelEx($company_adress,'State'); ?>
            <?php echo $form->textField($company_adress,'State', $additionParams); ?>
            <?php echo $form->error($company_adress,'State'); ?>
        </div>
        <div class="group">
            <?php echo $form->labelEx($company_adress,'ZIP'); ?>
            <?php echo $form->textField($company_adress,'ZIP', $additionParams); ?>
            <?php echo $form->error($company_adress,'ZIP'); ?>
        </div>
        <input type="hidden" value="true" name="company_form">
        <input name="submit" type="submit" value="submit" class="hide">
    </fieldset>
<?}?>
<?php $this->endWidget(); ?>

    <div class="group">
        <?php if(in_array($user_role,$alowed_users_array)){ ?>
        <label for="bank_account_select">Bank Account</label>
        <?php echo CHtml::dropDownList('bank_account_select', 0, $bankAccountNums, array('id' => 'bank_account_select','disabled'=>$read_only)); ?>
        <?php } ?>
    </div>

    <div class="group">
        <?php if(in_array($user_role,$alowed_users_array)){ ?>
        <label for="project_select">Projects</label>
        <?php echo CHtml::dropDownList('project_select', 0, $projects, array('id' => 'project_select','disabled'=>$read_only)); ?>
        <?php } ?>
    </div>
