<?php
/* @var $this VendorController */

$this->breadcrumbs=array(
    'PO Data Entry'
);

?>
<h1>PO Data Entry
    <div id="filename_section" style="float: right;margin-right:10px;font-size:17.7px;font-style: normal;font-weight: normal; " ><?=$file->File_Name; ?> </div>
</h1>

<div class="account_manage">
  <div class="de_nav_wrapper">
    <?php if ($num_pages > 0) { ?>
        <div class="data_entry_nav_wrapper">
            <?php $this->renderPartial('application.views.widgets.pages_navigation_de', array(
                'page' => $page,
                'num_pages' => $num_pages,
                'url' => '/dataentry/po',
                'position' => 'left'
            )); ?>
        </div>
    <?php } ?>


    <form method="post" action="/dataentry/<?=$this->action->id?>">
        <div class="left" style="margin-left: <?php echo ($num_pages > 0) ? '65' : '10'; ?>px;">
            <span class="left search_block_label">Search: &nbsp;</span>
            <div class="search_block">
                <input type="text" name="search_field" id="search_field" maxlength="250" value="<?php echo $_SESSION['last_po_to_entry_search']['query'];?>" autocomplete="off">
                <div id="search_options">
                    <span class="search_options_header">Search in the fields:</span><br/>
                    <?php
                    echo Helper::getSearchOptionsHtml(array(
                        'session_name' => 'last_po_to_entry_search',
                        'options' => array(
                            'search_option_com_name' => array('Company name', 1),
                            'search_option_fed_id' => array('Company Fed ID', 1),
                            'search_option_account_num' => array('Account Num', 0),
                            'search_option_subtotal' => array('Subtotal', 0),
                            'search_option_tax' => array('Tax', 0),
                            'search_option_deliv_chg' => array('Delivery Chg', 0),
                            'search_option_other_chg' => array('Other Chg', 0),
                            'search_option_total' => array('Total', 0),
                            'search_option_paym_type' => array('Payment type', 0),
                            'search_option_4digits' => array('Last 4 Digits', 0),
                        ),
                    ));
                    ?>
                </div>
            </div>
        </div>
    </form>


    <span class="de_count_items" style="margin-left: <?php echo ($num_pages > 0) ? '65' : '10'; ?>px;">PO for Process: <?php echo $num_pages; ?> items</span>
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
    <h2><?php echo isset($company->Company_Name) ? CHtml::encode($company->Company_Name) : 'Company Name';?></h2>
    <span class="de_company_id">
        Company ID: <span class="details_page_value"><?php echo isset($vendor->Vendor_ID_Shortcut) ? CHtml::encode($vendor->Vendor_ID_Shortcut) : ''; ?></span>
    </span>
    <?if (Yii::app()->user->id != 'data_entry_clerk' && $document->Client_ID == Yii::app()->user->clientID ) {?>
        <div style="position: relative;left: 235px;"> <a href="#" class="add_new_vendor"> Add new vendor </a> </div>
    <?}?>

    <?php $form=$this->beginWidget('CActiveForm', array (
        'id'=>'po_data_entry_form',
        'htmlOptions'=>array(
            'class'=>'data_entry_form',
        ),
    )); ?>
    <fieldset>
        <div class="group">
            <label style="color: #fff">
                *
            </label>
            <input id="narrow_vendors_list" type="text" value="" maxlength="100" name="narrow_vendors_list" placeholder="Narrow Vend. by Name or Shortcut">
        </div>
        <div class="group">
            <label for="Pos_Vendor_ID"><span class="red">*</span> Vendor</label>
            <?php  echo $form->dropDownList($po,'Vendor_ID', array('0' => 'Unknown Vendor') + $vendorsCP); ?>
            <?php echo $form->error($po,'Vendor_ID'); ?>
        </div>
        <div class="group">
            <label for="Pos_PO_Account_Number">Account Num</label>
            <?php echo $form->textField($po,'PO_Account_Number'); ?>
            <?php echo $form->error($po,'PO_Account_Number'); ?>
        </div>
        <div class="group">
            <?php
            // convert date string to view format
            if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $po->PO_Date)) {
                $po->PO_Date = Helper::convertDateSimple($po->PO_Date);
            }
            ?>
            <label for="Pos_PO_Date"><span class="red">*</span> Date</label>
            <?php echo $form->textField($po,'PO_Date'); ?>
            <?php echo $form->error($po,'PO_Date'); ?>
            <?php
            if (!isset($ap['_errors']["PO_Date"])) {
                echo '<div class="errorMessage grey">Correct date format: mm/dd/yyyy!</div>';
            }
            ?>
        </div>
        <div class="group">
            <label for="Pos_PO_Subtotal"><span class="red">*</span> Subtotal</label>
            <?php echo $form->textField($po,'PO_Subtotal', array('class' => 'dollar_fields base_fields')); ?>
            <?php echo $form->error($po,'PO_Subtotal'); ?>
        </div>
        <div class="group">
            <label for="Pos_PO_Tax">Tax</label>
            <?php echo $form->textField($po,'PO_Tax', array('class' => 'dollar_fields base_fields')); ?>
            <?php echo $form->error($po,'PO_Tax'); ?>
        </div>
        <div class="group">
            <label for="Pos_PO_Delivery_Chg">Delivery Chg</label>
            <?php echo $form->textField($po,'PO_Delivery_Chg', array('class' => 'dollar_fields base_fields')); ?>
            <?php echo $form->error($po,'PO_Delivery_Chg'); ?>
        </div>
        <div class="group">
            <label for="Pos_PO_Other_Chg">Other Chg</label>
            <?php echo $form->textField($po,'PO_Other_Chg', array('class' => 'dollar_fields base_fields')); ?>
            <?php echo $form->error($po,'PO_Other_Chg'); ?>
        </div>
        <div class="group">
            <label for="Pos_PO_Total"><span class="red">*</span> Total</label>
            <?php echo $form->textField($po,'PO_Total',array('readonly'=>true)); ?>
            <?php echo $form->error($po,'PO_Total'); ?>
        </div>
        <div class="group">
            <label for="Pos_Payment_Type"><span class="red">*</span> Payment Type</label>
            <?php echo $form->dropDownList($po,'Payment_Type', array('0' => 'Unknown Payment Type') + $this->paymentTypes); ?>
            <?php echo $form->error($po,'Payment_Type'); ?>
        </div>
        <div class="group">
            <?php
            $htmlOptions = array();
            $hidden = '';
            if ($po->Payment_Type != 'CC') {
                $htmlOptions['disabled'] = 'disabled';
                $hidden = 'style="display:none;"';
            }
            ?>
            <label for="Pos_PO_Card_Last_4_Digits"><span class="red" <?php echo $hidden; ?>>*</span> Last 4 Digits</label>
            <?php echo $form->textField($po,'PO_Card_Last_4_Digits', $htmlOptions); ?>
            <?php echo $form->error($po,'PO_Card_Last_4_Digits'); ?>
        </div>
        <div class="group de_invoices">
            <label>
                <input type="checkbox" id="dists_enabled" name="dists_enabled"
                    <?if(!$dists_empty) echo ' checked ';?>  >


                Distributions</label><br/><br/>
            <div class="de_dists_fields">
                <table id="attached_invoices_head" class="width280">
                    <thead>
                    <tr>
                        <th class="width90">
                            GL Code
                        </th>
                        <th class="width70">
                            Amount
                        </th>
                        <th>
                            Desc.
                        </th>
                    </tr>
                    </thead>
                </table>
                <div id="attached_dists_block">
                    <table id="attached_invoices">
                        <tbody>
                        <?php
                        foreach($dists as $key => $dist) {
                            echo '<tr>
                                  <td class="width100">
                                      <input type="text" class="GL_Code" data-short-hand="'.$dist['Short_Hand'].'" maxlength="63"  title="' . $dist['PO_Dists_GL_Code'] . '"  value="' . $dist['PO_Dists_GL_Code'] . '" name="Dist[' . $key . '][PO_Dists_GL_Code]">
                                       <input type="hidden" class="short_hand"  value="' . $dist['Short_Hand'] . '" name="Dist[' . $key . '][Short_Hand]">
                                  </td>
                                  <td class="width80">
                                      <input type="text" class="gl_amount dollar_fields float_type" maxlenght="13" value="' . $dist['PO_Dists_Amount'] . '" name="Dist[' . $key . '][PO_Dists_Amount]">
                                  </td>
                                  <td>
                                      <input type="text" maxlength="125" value="' . $dist['PO_Dists_Description'] . '" name="Dist[' . $key . '][PO_Dists_Description]" maxlenght="125">
                                  </td>
                               </tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
                <span id="add_invoice">+add item</span>
                <span id="remove_invoice">-remove item</span>
            </div>
            <?php
            if ($invalidDistsSum != '') {
                echo '<div class="errorMessage mleft20">' . $invalidDistsSum . '</div>';
            }
            ?>
        </div>

        <div class="coa_row" id="COA_Allow_Manual_Coding" style="display: none" data-id="<?=$coaStructure->COA_Allow_Manual_Coding?>">
        </div>

        <?php echo $form->hiddenField($po,'PO_ID'); ?>
        <?php echo $form->hiddenField($po,'Document_ID'); ?>
        <input type="hidden" value="true" name="po_data_entry_form_values">
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
    POs weren't found.
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
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/dataentry_dists_autocomplete.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/vendor_w9_upload.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/document_view.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/data_entry_detail_po.js"></script>
<?php
Yii::app()->clientScript->registerScriptFile('/js/mousewheel.js', CClientScript::POS_HEAD);
?>
<script type="text/javascript">
    $(document).ready(function() {
        new DataEntryDetail;
       new VendorW9Upload('temp');

        var columns = $("#data_entry_left,#data_entry_right");
        var tallestcolumn = 0;

        columns.each(function() {
                currentHeight = $(this).height();
                if(currentHeight > tallestcolumn)
                    tallestcolumn = currentHeight;
            }
        );


        new DistsHandling;


    });
</script>