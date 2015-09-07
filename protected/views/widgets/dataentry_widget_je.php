<img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton" style="right: -93px;"/>

<div id="data_entry_left" style="background-color: #ffffff;overflow: auto;z-index: 120;">

    <?php $form=$this->beginWidget('CActiveForm', array (
        'id'=>'je_data_entry_form',
        'htmlOptions'=>array(
            'class'=>'data_entry_form',
        ),
    )); ?>

    <fieldset>
        <div class="group">
            <?php
            // convert date string to view format
            if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $je->JE_Date)) {
                $je->JE_Date = Helper::convertDateSimple($je->JE_Date);
            }
            ?>
            <label for="Journals_JE_Date"><span class="red">*</span> Journal Date</label>
            <?php echo $form->textField($je,'JE_Date'); ?>
            <?php echo $form->error($je,'JE_Date'); ?>
            <?php
            if (!isset($je['_errors']["JE_Date"])) {
                echo '<div class="errorMessage grey">Correct date format: mm/dd/yyyy!</div>';
            }
            ?>
        </div>
        <div class="group">
            <?php
            if ($je->JE_Number == '0') {
                $je->JE_Number = '';
            }
            ?>
            <label for="Journals_JE_Number"><span class="red">*</span> Journal #</label>
            <?php echo $form->textField($je,'JE_Number'); ?>
            <?php echo $form->error($je,'JE_Number'); ?>
        </div>
        <div class="group">
            <label for="Journals_JE_Transaction_ID">Transaction #</label>
            <?php echo $form->textField($je,'JE_Transaction_ID'); ?>
            <?php echo $form->error($je,'JE_Transaction_ID'); ?>
        </div>
        <div class="group">
            <label for="Journals_JE_Desc">Desc</label>
            <?php echo $form->textField($je,'JE_Desc'); ?>
            <?php echo $form->error($je,'JE_Desc'); ?>
        </div>
        <?php echo $form->hiddenField($je,'JE_ID'); ?>
        <?php echo $form->hiddenField($je,'Document_ID'); ?>
        <input type="hidden" value="true" name="je_data_entry_form_values">
        <div class="center">
            <button class="button" id="submit_ajax_je_form">Save</button>
        </div>
    </fieldset>

    <?php $this->endWidget(); ?>

</div>


