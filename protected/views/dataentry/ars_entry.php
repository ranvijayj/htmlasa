<?php

$this->breadcrumbs=array(
    'AR Data Entry'
);
?>
<h1>AR Data Entry
    <div id="filename_section" style="float: right;margin-right:10px;font-size:17.7px;font-style: normal;font-weight: normal; " ><?=$file->File_Name; ?> </div>
</h1>

<div class="account_manage">
 <div class="de_nav_wrapper">
    <?php if ($num_pages > 0) { ?>
        <div class="data_entry_nav_wrapper">
            <?php $this->renderPartial('application.views.widgets.pages_navigation_de', array(
                'page' => $page,
                'num_pages' => $num_pages,
                'url' => '/dataentry/ar',
                'position' => 'left'
            )); ?>
        </div>
    <?php } ?>

    <form method="post" action="/dataentry/<?=$this->action->id?>">
        <div class="left" style="margin-left: <?php echo ($num_pages > 0) ? '65' : '10'; ?>px;">
            <span class="left search_block_label">Search: &nbsp;</span>
            <div class="search_block">
                <input type="text" name="search_field" id="search_field" maxlength="250" value="<?php echo $_SESSION['last_ar_to_entry_search']['query'];?>" autocomplete="off">
                <div id="search_options">
                    <span class="search_options_header">Search in the fields:</span><br/>
                    <?php
                    echo Helper::getSearchOptionsHtml(array(
                        'session_name' => 'last_ar_to_entry_search',
                        'options' => array(
                            'search_option_com_name' => array('Company name', 1),
                            'search_option_inv_num' => array('Invoice #', 0),
                            'search_option_inv_date' => array('Inv. Date', 0),
                            'search_option_inv_amount' => array('Inv. Amount', 0),
                            'search_option_descr' => array('Description', 1),
                            'search_option_terms' => array('Terms', 0),
                        ),
                    ));
                    ?>
                </div>
            </div>
        </div>
    </form>

    <span class="de_count_items" style="margin-left: <?php echo ($num_pages > 0) ? '65' : '10'; ?>px;">ARs to Process: <?php echo $num_pages; ?> items</span>
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
        'id'=>'ar_data_entry_form',
        'htmlOptions'=>array(
            'class'=>'data_entry_form',
        ),
    ));
    ?>
    <fieldset>
        <?php
        /*
        <div class="group">
            <label style="color: #fff">
                *
            </label>
            <input id="narrow_customers_list" type="text" value="" maxlength="100" name="narrow_customers_list" placeholder="Narrow Cust. by Name or Shortcut">
        </div>
        <div class="group">
            <label for="Ars_Customer_ID"><span class="red">*</span> Customer</label>
            <?php  echo $form->dropDownList($ar,'Customer_ID', array('0' => 'Unknown Customer') + $customers); ?>
            <?php echo $form->error($ar,'Customer_ID'); ?>
        </div>
        */
        ?>
        <div class="group">
            <label for="Ars_Company_Name"><span class="red">*</span> Comp. Name</label>
            <?php echo $form->textField($ar,'Company_Name'); ?>
            <?php echo $form->error($ar,'Company_Name'); ?>
        </div>
        <div class="group">
            <?php
            if ($ar->Invoice_Number == '0') {
                $ar->Invoice_Number = '';
            }
            ?>
            <label for="Ars_Invoice_Number"><span class="red">*</span> Invoice #</label>
            <?php echo $form->textField($ar,'Invoice_Number'); ?>
            <?php echo $form->error($ar,'Invoice_Number'); ?>
        </div>
        <div class="group">
            <?php
            // convert date string to view format
            if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $ar->Invoice_Date)) {
                $ar->Invoice_Date = Helper::convertDateSimple($ar->Invoice_Date);
            }
            ?>
            <label for="Ars_Invoice_Date"><span class="red">*</span> Invoice Date</label>
            <?php echo $form->textField($ar,'Invoice_Date'); ?>
            <?php echo $form->error($ar,'Invoice_Date'); ?>
            <?php
            if (!isset($ar['_errors']["Invoice_Date"])) {
                echo '<div class="errorMessage grey">Correct date format: mm/dd/yyyy!</div>';
            }
            ?>
        </div>
        <div class="group">
            <?php
            if ($ar->Invoice_Amount == '0.00') {
                $ar->Invoice_Amount = '';
            }
            ?>
            <label for="Ars_Invoice_Amount"><span class="red">*</span> Inv. Amount</label>
            <?php echo $form->textField($ar,'Invoice_Amount',array(
                'class'=>'dollar_fields'
            )); ?>
            <?php echo $form->error($ar,'Invoice_Amount'); ?>
        </div>
        <div class="group">
            <label for="Ars_Description">Description</label>
            <?php echo $form->textField($ar,'Description'); ?>
            <?php echo $form->error($ar,'Description'); ?>
        </div>
        <div class="group">
            <label for="Ars_Terms">Terms</label>
            <?php echo $form->textField($ar,'Terms'); ?>
            <?php echo $form->error($ar,'Terms'); ?>
        </div>
        <?php echo $form->hiddenField($ar,'AR_ID'); ?>
        <?php echo $form->hiddenField($ar,'Document_ID'); ?>
        <input type="hidden" value="true" name="ar_data_entry_form_values">
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
    ARs weren't found.
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
        $('#Ars_Invoice_Date').datepicker({
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
            $("#Ars_Invoice_Date").mask("99/99/9999");
        });


        $('#Ars_Invoice_Amount').blur(function() {
            var amount = parseFloat($(this).val());
            if (!isNaN(amount)) {
                $(this).val(amount.toFixed(2));
            } else {
                $(this).val('');
            }
        });

        var timeout = false;
        $('#narrow_customers_list').keydown(function() {
            clearTimeout(timeout);
            var input = $(this);
            timeout = setTimeout(function() {
                var docId = $('#Ars_Document_ID').val();
                var query = input.val();
                $('#Ars_Customer_ID').attr('disabled', true);
                $.ajax({
                    url: "/dataentry/getcustomerslist",
                    data: {query: query, docId: docId},
                    type: "POST",
                    success: function(msg){
                        $('#Ars_Customer_ID').html(msg).attr('disabled', false);
                    }
                });
            }, 500);
        });

       var availableNames = [<?php echo is_array($compNames) ? '"' . Helper::multiImplode('","', $compNames) . '"' : ''; ?>];
        $( "#Ars_Company_Name" ).autocomplete({
            source: availableNames,
            minLength:0
        });
        //$( "#Ars_Company_Name" ).on('focus', function() { $(this).keydown();});
        $( "#Ars_Company_Name" ).on('focus', function() { $(this).autocomplete("search", "");});

        var availableTags = [<?php echo is_array($terms) ? '"' . Helper::multiImplode('","', $terms) . '"' : ''; ?>];
        $( "#Ars_Terms" ).autocomplete({
            source: availableTags,
            minLength:0
        });
        $( "#Ars_Terms" ).on('focus', function() { $(this).autocomplete("search", "");});
    });
</script>