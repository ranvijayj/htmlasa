<h1>
    <?php
        if ($action == 'add') {
            echo 'New ';
        } else {
            echo 'Edit ';
        }

        if ($storageType == 1) {
            echo ' Binder';
        } else {
            echo '  Folder';
        }
    ?>
</h1>
<img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt='' class="hidemodal cancelbutton"/>
<?php
$form=$this->beginWidget('CActiveForm', array (
    'id'=>'library_form',
    'action' => '/library',
)); ?>
<div class="row">
    <label for="Sections_Section_Name"><span class="red">*</span> Title</label>
    <?php echo $form->textField($section,'Section_Name' ,array('class'=>'txtfield')); ?>
    <?php echo $form->error($section,'Section_Name'); ?>
</div>
<?php
if ($action == 'add') {
    ?>
    <div class="row">
        <label for="count_of_subsections"><span class="red">*</span> Count of <?php echo ($storageType == 1) ? 'Tabs' : 'Panels'; ?></label>
        <select id="count_of_subsections" name="count_of_subsections" class="txtfield">
            <?php
            $subsType = ($storageType == 1) ? 'Tab' : 'Panel';
            for ($i = 1; $i <=6; $i++) {
                echo '<option value="' . $i . '">' . $i . ' ' . $subsType . ($i > 1 ? 's' : '') . '</option>';
            }
            ?>
        </select>
    </div>
<?php
}
?>
<?php
if ($storageType == 1 && $action == 'add') {
    ?>
    <div class="row">
        <label for="Sections_Folder_Cat_ID"><span class="red">*</span> Category</label>
        <?php echo $form->dropDownList($section,'Folder_Cat_ID', array('7' => 'Purchase Order Log', '8' => 'Check Log'), array('class'=>'txtfield')); ?>
        <?php echo $form->error($section,'Folder_Cat_ID'); ?>
    </div>
<?php
}

    if (($section->Created_By == Yii::app()->user->userID && $action == 'edit') || $action == 'add') {
        ?>
        <div class="row">
            <label for="Sections_Access_Type"><span class="red">*</span> Access</label>
            <?php echo $form->dropDownList($section,'Access_Type', array('0' => 'Only for me', '1' => 'For all users in Project'), array('class'=>'txtfield')); ?>
            <?php echo $form->error($section,'Access_Type'); ?>
        </div>
        <?php
    }
?>
<?php echo CHtml::hiddenField('library_form', true); ?>
<?php echo CHtml::hiddenField('rowType', $rowType); ?>
<?php echo CHtml::hiddenField('action', $action); ?>
<?php echo CHtml::hiddenField('storage', $storageType); ?>
<?php echo CHtml::hiddenField('id', $id); ?>
<?php echo CHtml::hiddenField('back_url', $back_url); ?>
<div style="clear: both; height: 10px;"></div>
<div class="center">
    <?php echo CHtml::submitButton('Save',array('id'=>'sendlibformbtn','class'=>'button hidemodal')); ?>
</div>
<?php $this->endWidget(); ?>