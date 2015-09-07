<h1>
    <?php
        if ($action == 'add') {
            echo 'New ';
        } else {
            echo 'Edit ';
        }

        if ($storageType == 1) {
            echo ' Shelf';
        } else {
            echo '  Cabinet';
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
    <label for="Storages_Storage_Name"><span class="red">*</span> Title</label>
    <?php echo $form->textField($storage,'Storage_Name' ,array('class'=>'txtfield')); ?>
    <?php echo $form->error($storage,'Storage_Name'); ?>
</div>
<?php
    if (($storage->Created_By == Yii::app()->user->userID && $action == 'edit') || $action == 'add') {
        ?>
        <div class="row">
            <label for="Storages_Access_Type"><span class="red">*</span> Access</label>
            <?php echo $form->dropDownList($storage,'Access_Type', array('0' => 'Only for me', '1' => 'For all users in Project'), array('class'=>'txtfield')); ?>
            <?php echo $form->error($storage,'Access_Type'); ?>
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