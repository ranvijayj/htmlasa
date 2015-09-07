<?php
/* @var $this CoaController */

$this->breadcrumbs=array('Chart of Accounts');
?>
<h1>Chart of Accounts: <?=@CHtml::encode(Yii::app()->user->userLogin);?></h1>

<div class="account_manage">
    <div class="account_header_left left">
        <button class="button" id="copy_coa">Copy</button>
        <button class="button" id="import_coa">Import</button>
        <a href="/coa/export" target="_blank" class="button">Export</a>
    </div>
    <button class="button right" id="submit_form">Save</button>
</div>
<?php if(Yii::app()->user->hasFlash('success')):?>
    <div class="info">
        <button class="close-alert">&times;</button>
        <?php echo Yii::app()->user->getFlash('success'); ?>
    </div>
<?php endif; ?>
<div class="left_column" id="left_column">
    <h2>Management:</h2>

    <div class="row_control_buttons" style="float: right;">
        <span class="add_row" id="add_coa">+add item</span>
        <span class="remove_row not_active" id="remove_coa" >-remove item</span>
    </div>

    <table id="list_table_head">
        <thead>
        <tr class="table_head">
            <th class="width2">
                <input type="checkbox" id="check_all" name="check_all"/>
            </th>
            <th class="width50" id="coa_class_header">
                Class
            </th>
            <th class="width110" id="coa_desc_header">
                Description
            </th>
            <th class="width130" id="coa_numb_header">
                Number
            </th>
            <th id="coa_budget_header">
                Budget
            </th>
        </tr>
        </thead>
    </table>
    <div class="table_list_scroll_block">
        <table id="list_table" class="coa_list_table">
            <tbody>
            <?php $this->renderPartial('application.views.coa.coa_list', array(
                'COAs' => $COAs,
                'coaClasses' => $coaClasses,
            )); ?>
            </tbody>
        </table>
    </div>
</div>
<div class="right_column coa_right" id="right_column">
    <div id="coa_setup_block">
        <h2>Setup:</h2>
        <?php $form=$this->beginWidget('CActiveForm', array (
            'id'=>'coa_settings_form',
            'action'=>Yii::app()->createUrl('/coa'),
        )); ?>

        <div class="coa_row">
            <label>Default Class Select:</label>
            <select id="default_coa_class" name="default_coa_class">
                <?php
                echo '<option value="0" >' . 'All classes' . '</option>';
                foreach ($coaClasses as $coaClass) {
                    echo '<option value="' . $coaClass->COA_Class_ID .'" ' . ($coaClass->COA_Class_ID == $coaDefaultClass->COA_Class_ID  ? 'selected="selected"' : '') . '>' . CHtml::encode($coaClass->Class_Shortcut . ' - ' . $coaClass->Class_Name) . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="coa_row">
            <?php echo $form->checkBox($coaStructure,'COA_Allow_Manual_Coding'); ?>
            <label for="CoaStructure_COA_Allow_Manual_Coding">Allow Manual Coding</label>
            <?php echo $form->error($coaStructure,'COA_Allow_Manual_Coding'); ?>
        </div>
        <div class="coa_row">
            <div class="left break_c">
                <?php echo $form->labelEx($coaStructure,'COA_Break_Character'); ?>
                <?php echo $form->textField($coaStructure,'COA_Break_Character'); ?>
                <?php echo $form->error($coaStructure,'COA_Break_Character'); ?>
            </div>
            <div class="left break_n">
                <?php echo $form->labelEx($coaStructure,'COA_Break_Number'); ?>
                <?php echo $form->dropDownList($coaStructure,'COA_Break_Number', array(0=>0,1=>1,2=>2)); ?>
                <?php echo $form->error($coaStructure,'COA_Break_Number'); ?>
            </div>
            <div class="clear"></div>
        </div>
        <div class="coa_row">
            <label>Account Structure</label>
            <table class="scroll_table_head center position_center">
                <thead>
                <tr>
                    <th class="width54">
                        Prefix
                    </th>
                    <th class="width54">
                        Head
                    </th>
                    <th class="width54">
                        Modifier
                    </th>
                    <th class="width54">
                        Root
                    </th>
                    <th class="width54">
                        Conj.
                    </th>
                    <th class="width54">
                        Tail
                    </th>
                    <th class="width54">
                        Suffix
                    </th>
                </tr>
                </thead>
            </table>
            <div class="account_structure_block">
                <?php echo $form->dropDownList($coaStructure,'COA_Prefix', array(1=>'On',0=>'Off')); ?>
                <?php echo $form->dropDownList($coaStructure,'COA_Head', array(1=>'On',0=>'Off')); ?>
                <?php echo $form->dropDownList($coaStructure,'COA_Modifier', array(1=>'On',0=>'Off')); ?>
                <?php echo $form->dropDownList($coaStructure,'COA_Root', array(1=>'On',0=>'Off')); ?>
                <?php echo $form->dropDownList($coaStructure,'COA_Conjun', array(1=>'On',0=>'Off')); ?>
                <?php echo $form->dropDownList($coaStructure,'COA_Tail', array(1=>'On',0=>'Off')); ?>
                <?php echo $form->dropDownList($coaStructure,'COA_Suffix', array(1=>'On',0=>'Off')); ?>
            </div>
        </div>
        <div class="coa_row">
            <label>Account Structure Entry</label>
            <div class="account_structure_block">
                <?php echo $form->textField($coaStructure,'COA_Prefix_Val'); ?>
                <input type="text" value="" maxlength="9" disabled="disabled" name="head">
                <?php echo $form->textField($coaStructure,'COA_Modifier_Val'); ?>
                <input type="text" value="" maxlength="9" disabled="disabled" name="root">
                <?php echo $form->textField($coaStructure,'COA_Conjun_Val'); ?>
                <input type="text" value="" maxlength="9" disabled="disabled" name="tail">
                <?php echo $form->textField($coaStructure,'COA_Suffix_Val'); ?>
            </div>
            <?php echo $form->error($coaStructure,'COA_Prefix_Val'); ?>
            <?php echo $form->error($coaStructure,'COA_Modifier_Val'); ?>
            <?php echo $form->error($coaStructure,'COA_Conjun_Val'); ?>
            <?php echo $form->error($coaStructure,'COA_Suffix_Val'); ?>
        </div>
        <div class="coa_row">
            <label>Class Entry</label>
            <table class="scroll_table_head coa_classes_scroll_table_head">
                <thead>
                <tr>
                    <th class="width40">
                        Order
                    </th>
                    <th class="width50">
                        Class
                    </th>
                    <th class="width235">
                        Description
                    </th>
                </tr>
                </thead>
            </table>
            <div id="coa_classes_block">
                <table id="coa_classes">
                    <tbody>
                    <?php
                    foreach($coaClasses as $key => $coaClass) {
                        echo '<tr>
                                  <td class="width40">
                                      <span><input type="text" value="' . $coaClass->Class_Sort_Order . '" name="CoaClass[' . ($key + 1) . '][Class_Sort_Order]" class="int_type"></span>
                                  </td>
                                  <td class="width50">
                                       <span><input type="text" maxlength="3" value="' . $coaClass->Class_Shortcut . '" name="CoaClass[' . ($key + 1) . '][Class_Shortcut]"></span>
                                  </td>
                                  <td>
                                       <span><input type="text" maxlength="50" value="' . $coaClass->Class_Name . '" name="CoaClass[' . ($key + 1) . '][Class_Name]"></span>
                                       <input type="hidden" value="' . $coaClass->COA_Class_ID . '" name="CoaClass[' . ($key + 1) . '][COA_Class_ID]">
                                  </td>
                             </tr>';
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <div class="row_control_buttons">
                <span class="add_row" id="add_coa_class">+add item</span>
                <span class="remove_row" id="remove_coa_class">-remove item</span>
            </div>
            <input type="hidden" value="1" name="coa_settings_form">
        </div>
        <?php $this->endWidget(); ?>
    </div>
</div>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/ajaxupload_xls.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/coa.js"></script>
<script>
    $(document).ready(function() {
        new CoaPage;
    });
</script>
<div class="modal_box" id="copy_coas" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <h2>Chart of Accounts Copy</h2>
    <form action="/coa" method="post">
        <div class="row">
            <label for="client_to_copy">
                Company:
            </label>
            <select id="client_to_copy" class="txtfield" name="client_to_copy">
                <option value="0">Select a company</option>
            </select>
        </div>
        <div class="row">
            <label for="project_to_copy">
                Project:
            </label>
            <select id="project_to_copy" class="txtfield" name="project_to_copy">
                <option value="0">Select a project</option>
            </select>
        </div>
        <input type="hidden" name="coa_copy_form" value="1">
        <div class="center">
            <input class="button hidemodal" type="submit" value="Copy">
        </div>
    </form>

    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/progress_bar.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            $("#copy_coas form").submit(function(event) {
                if ($('#client_to_copy').val() == 0 || $('#project_to_copy').val() == 0) {
                    event.preventDefault();
                    close_modal_box('#copy_coas');
                }
            });


        });
    </script>
</div>