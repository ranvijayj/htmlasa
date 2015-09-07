<?php

$this->breadcrumbs=array(
    'Payroll Data Entry'
);
?>
<h1>Payroll Data Entry
    <div id="filename_section" style="float: right;margin-right:10px;font-size:17.7px;font-style: normal;font-weight: normal; " ><?=$file->File_Name; ?> </div>
</h1>

<div class="account_manage">
  <div class="de_nav_wrapper">
    <?php if ($num_pages > 0) { ?>
        <div class="data_entry_nav_wrapper">
            <?php $this->renderPartial('application.views.widgets.pages_navigation_de', array(
                'page' => $page,
                'num_pages' => $num_pages,
                'url' => '/dataentry/payroll',
                'position' => 'left'
            )); ?>
        </div>
    <?php } ?>

    <form method="post" action="/dataentry/<?=$this->action->id?>">
        <div class="left" style="margin-left: <?php echo ($num_pages > 0) ? '65' : '10'; ?>px;">
            <span class="left search_block_label">Search: &nbsp;</span>
            <div class="search_block">
                <input type="text" name="search_field" id="search_field" maxlength="250" value="<?php echo $_SESSION['last_payr_to_entry_search']['query'];?>" autocomplete="off">
                <div id="search_options">
                    <span class="search_options_header">Search in the fields:</span><br/>
                    <?php
                    echo Helper::getSearchOptionsHtml(array(
                        'session_name' => 'last_payr_to_entry_search',
                        'options' => array(
                            'search_option_week_end' => array('Date (yyyy-mm-dd) ', 1),
                            'search_option_type' => array('Payroll type #', 1),
                            'search_option_submitted' => array('Submitted(0 or 1)', 0),
                            'search_option_version' => array('Version', 0),
                        ),
                    ));
                    ?>
                </div>
            </div>
        </div>
    </form>

    <span class="de_count_items" style="margin-left: <?php echo ($num_pages > 0) ? '65' : '10'; ?>px;">Payrolls to Process: <?php echo $num_pages; ?> items</span>
  </div>
</div>
<?php if(Yii::app()->user->hasFlash('success')):?>
    <div class="info">
        <button class="close-alert">&times;</button>
        <?php echo Yii::app()->user->getFlash('success'); ?>
    </div>
<?php endif; ?>
<div id="data_entry_left_right_wrapper">

<div id="data_entry_left">
<?php if ($num_pages > 0) {?>
    <?php $form=$this->beginWidget('CActiveForm', array (
        'id'=>'payroll_data_entry_form',
        'htmlOptions'=>array(
            'class'=>'data_entry_form',
        ),
    )); ?>
    <fieldset>
        <div class="group">
            <?php
            // convert date string to view format
            if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $payroll->Week_Ending)) {
                $payroll->Week_Ending = Helper::convertDateSimple($payroll->Week_Ending);
            }
            ?>
            <label for="Payrolls_Week_Ending"><span class="red">*</span> Week Ending</label>
            <?php echo $form->textField($payroll,'Week_Ending'); ?>
            <?php echo $form->error($payroll,'Week_Ending'); ?>
            <?php
            if (!isset($payroll['_errors']["Week_Ending"])) {
                echo '<div class="errorMessage grey">Correct format: mm/dd/yyyy!</div>';
            }
            ?>
        </div>
        <div class="group">
            <label for="Payrolls_Payroll_Type_ID"><span class="red">*</span> Type</label>
            <?php  echo $form->dropDownList($payroll,'Payroll_Type_ID', array('0' => 'Unknown Payroll Type') + $payrollTypes); ?>
            <?php echo $form->error($payroll,'Payroll_Type_ID'); ?>
        </div>
        <div class="group">
            <label for="Payrolls_Submitted"> Submitted</label>
            <?php echo $form->checkBox($payroll,'Submitted'); ?>
            <?php echo $form->error($payroll,'Submitted'); ?>
        </div>
        <div class="group">
            <label for="Payrolls_Version">Version</label>
            <?php echo $form->textField($payroll,'Version'); ?>
            <?php echo $form->error($payroll,'Version'); ?>
        </div>
        <?php echo $form->hiddenField($payroll,'Payroll_ID'); ?>
        <?php echo $form->hiddenField($payroll,'Document_ID'); ?>
        <input type="hidden" value="true" name="payroll_data_entry_form_values">
        <div class="center">
            <?php echo CHtml::submitButton('Save',array('class'=>'button')); ?>
        </div>
    </fieldset>
    <?php $this->endWidget(); ?>

    <div class="delimiter" style="height: 30px;"></div>
    <?if ( in_array(Yii::app()->user->id,array('admin','db_admin')) ) {
        $this->renderPartial("application.views.widgets.document_details",array('document'=> $document)); //document details widget
    }?>

<?php } else {?>
    Payrolls weren't found.
<?php } ?>
</div>
<div id="data_entry_right">
    <!-- for reuploading functionality -->
    <input id="fileupload" type="file" name="files[]" style="display: none;" >
    <script src="<?php echo Yii::app()->request->baseUrl.'/js/jupload/vendor/jquery.ui.widget.js'?>" > </script>
    <script src="<?php echo Yii::app()->request->baseUrl.'/js/jupload/jquery.fileupload.js'?>" > </script>
    <!-- end -->
    <?php if ($num_pages > 0) {?>
        <div id="tab1_block">
            <div class="w9_detail_block_bar">
                <div class="image_buttons left">
                    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/fullsize.jpg" alt=""  class="w9_detail_block_bar_button fullsize_button"/>
                    <?php
                    if (strpos($file->Mime_Type, 'pdf') === false) {
                        echo '<img src="' . Yii::app()->request->baseUrl . '/images/minlup.jpg" alt=""  class="w9_detail_block_bar_button minlup_button"/>
                              <img src="' . Yii::app()->request->baseUrl . '/images/pluslup.jpg" alt="" class="w9_detail_block_bar_button pluslup_button"/>';
                    }
                    ?>
                </div>
                <?php
                //Buttons modification widget
                $this->renderPartial("application.views.filemodification.buttons",array(
                    'buttons' => array('rotate_cw','rotate_ccw','delete','reupload'),
                    'docId'  => $document->Document_ID,
                ));
                //end of widget
                ?>
            </div>
            <div id="embeded_pdf">
                <?php $this->widget('application.components.ShowPdfWidget', array(
                    'params' => array(
                        'doc_id'=>intval($document->Document_ID),
                        'mime_type'=>$file->Mime_Type,
                        'mode'=>Helper::isMobileComplexCheck()? 5 : 3,
                        'approved'=>1
                    ),
                )); ?>
            </div>

        </div>
    <?php } ?>
</div>

    </div>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/main.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/data_entry_detail.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/document_view.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/asa_datepicker.js"></script>
<?php
Yii::app()->clientScript->registerScriptFile('/js/mousewheel.js', CClientScript::POS_HEAD);
?>
<script type="text/javascript">
    $(document).ready(function() {
        new DataEntryDetail;

        var dpSettings = {
            dateFormat: "mm/dd/yy"
        }
        $('#Payrolls_Week_Ending').datepicker({
            dateFormat: "mm/dd/yy",
            onClose: function(selectedDate){
                //check if the year part of the date correct
                var arr = selectedDate.split('/');
                if(isNaN(arr[2])){
                    //if not correct changing it 2xxx format
                    arr[2]=arr[2].replace(/_/g, '');
                    var res =parseInt(arr[2],10);
                    if( res<2000 ) { res=res+2000; arr[2]=res;}
                    var new_date=arr[0]+'/'+arr[1]+'/'+arr[2];
                    $(this).datepicker( "setDate", new_date );
                }
            }
        });
        jQuery(function($){
            $("#Payrolls_Week_Ending").mask("99/99/9999");
        });

    });
</script>