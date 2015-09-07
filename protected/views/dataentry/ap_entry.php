<?php
/* @var $this VendorController */

$this->breadcrumbs=array(
    'AP Data Entry'
);
?>
<h1>AP Data Entry
    <div id="filename_section" style="float: right;margin-right:10px;font-size:17.7px;font-style: normal;font-weight: normal; " ><?=$file->File_Name; ?> </div>
</h1>

<div class="account_manage">
  <div class="de_nav_wrapper">
    <?php if ($num_pages > 0) { ?>
        <div class="data_entry_nav_wrapper">
            <?php $this->renderPartial('application.views.widgets.pages_navigation_de', array(
                'page' => $page,
                'num_pages' => $num_pages,
                'url' => '/dataentry/ap',
                'position' => 'left'
            )); ?>
        </div>
    <?php } ?>

    <form method="post" action="/dataentry/<?=$this->action->id?>">
        <div class="left" style="margin-left: <?php echo ($num_pages > 0) ? '65' : '10'; ?>px;">
            <span class="left search_block_label">Search: &nbsp;</span>
            <div class="search_block">
                <input type="text" name="search_field" id="search_field" maxlength="250" maxlength="250" value="<?php echo $_SESSION['last_ap_to_entry_search']['query'];?>" autocomplete="off">
                <div id="search_options">
                    <span class="search_options_header">Search in the fields:</span><br/>
                    <?php
                    echo Helper::getSearchOptionsHtml(array(
                        'session_name' => 'last_ap_to_entry_search',
                        'options' => array(
                            'search_option_com_name' => array('Company name', 1),
                            'search_option_fed_id' => array('Company Fed ID', 1),
                            'search_option_invoice_num' => array('Inv. Number', 0),
                            'search_option_inv_date' => array('Inv. Date', 0),
                            'search_option_inv_due_date' => array('Inv. Due Date', 0),
                            'search_option_amount' => array('Inv. Amount', 1),
                            'search_option_description' => array('Description', 1),
                            'search_option_1099_type' => array('1099 Type', 0),
                            'search_option_po_number' => array('PO Number', 0),
                        ),
                    ));
                    ?>
                </div>
            </div>
        </div>
    </form>


    <span class="de_count_items" style="margin-left: <?php echo ($num_pages > 0) ? '65' : '10'; ?>px;">AP to Process: <?php echo $num_pages; ?> items</span>
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
        'id'=>'ap_data_entry_form',
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
            <label for="Aps_Vendor_ID"><span class="red">*</span> Vendor</label>
            <?php echo $form->dropDownList($ap,'Vendor_ID', array('0' => 'Unknown Vendor') + $vendorsCP); ?>
            <?php echo $form->error($ap,'Vendor_ID'); ?>
        </div>
        <div class="group">
            <?php
            if ($ap->Invoice_Number == '0') {
                $ap->Invoice_Number = '';
            }
            ?>
            <label for="Aps_Invoice_Number"><span class="red">*</span> Inv. Number</label>
            <?php echo $form->textField($ap,'Invoice_Number'); ?>
            <?php echo $form->error($ap,'Invoice_Number'); ?>
        </div>
        <div class="group">
            <?php
            // convert date string to view format
            if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $ap->Invoice_Date)) {
                $ap->Invoice_Date = Helper::convertDateSimple($ap->Invoice_Date);
            }
            ?>
            <label for="Aps_Invoice_Date"><span class="red">*</span> Inv. Date</label>
            <?php echo $form->textField($ap,'Invoice_Date'); ?>
            <?php echo $form->error($ap,'Invoice_Date'); ?>
            <?php
            if (!isset($ap['_errors']["Invoice_Date"])) {
                echo '<div class="errorMessage grey">Correct date format: mm/dd/yyyy!</div>';
            }
            ?>
        </div>
        <div class="group">
            <?php
            // convert date string to view format
            if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $ap->Invoice_Due_Date)) {
                $ap->Invoice_Due_Date = Helper::convertDateSimple($ap->Invoice_Due_Date);
            }
            ?>
            <label for="Aps_Invoice_Due_Date">Inv. Due Date</label>
            <?php echo $form->textField($ap,'Invoice_Due_Date',array('data-term'=>$due_date_term)); ?>
            <?php echo $form->error($ap,'Invoice_Due_Date'); ?>
            <?php
            if (!isset($ap['_errors']["Invoice_Due_Date"])) {
                echo '<div class="errorMessage grey">Correct date format: mm/dd/yyyy!</div>';
            }
            ?>
        </div>
        <div class="group">
            <label for="Aps_Invoice_Amount"><span class="red">*</span> Inv. Amount</label>
            <?php echo $form->textField($ap,'Invoice_Amount'); ?>
            <?php echo $form->error($ap,'Invoice_Amount'); ?>
        </div>
        <div class="group">
            <label for="Aps_Invoice_Reference"><span class="red">*</span> Description</label>
            <?php echo $form->textField($ap,'Invoice_Reference'); ?>
            <?php echo $form->error($ap,'Invoice_Reference'); ?>
        </div>
        <div class="group">
            <label for="Aps_Detail_1099">1099</label>
            <?php echo $form->checkBox($ap,'Detail_1099'); ?>
            <?php echo $form->error($ap,'Detail_1099'); ?>
        </div>
        <div class="group">
            <label for="Aps_Detail_1099_Box_Number">1099 Type</label>
            <?php echo $form->dropDownList($ap,'Detail_1099_Box_Number', array('0' => 'Unknown 1099 Type') + array_combine(range(1,18), range(1,18))); ?>
            <?php echo $form->error($ap,'Detail_1099_Box_Number'); ?>

        </div>

        <div class="group">
            <label for="PO_Number">
                PO Number
            </label>
            <input id="PO_Number" type="text" value="<?php echo $poNum; ?>" maxlength="45" name="PO_Number">
            <?php
               if ($poError) {
                   echo '<div class="errorMessage">' . $poError . '</div>';
               }
            ?>
        </div>
        <div class="group de_invoices">

            <label>
                <input type="checkbox" id="dists_enabled" name="dists_enabled"
                    <?if(!$dists_empty) echo ' checked ';?>
                >
                GL Dists
            </label>
            <br/><br/>
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
                                      <input type="text" class="GL_Code" data-short-hand="'.$dist['Short_Hand'].'" maxlength="63" title="' . $dist['GL_Dist_Detail_COA_Acct_Number'] . '" value="' . $dist['GL_Dist_Detail_COA_Acct_Number'] . '" name="Dist[' . $key . '][GL_Dist_Detail_COA_Acct_Number]">
                                      <input type="hidden" class="short_hand"  value="' . $dist['Short_Hand'] . '" name="Dist[' . $key . '][Short_Hand]">
                                  </td>
                                  <td class="width80">
                                      <input type="text" class="gl_amount dollar_fields float_type" maxlenght="13" value="' . $dist['GL_Dist_Detail_Amt'] . '" name="Dist[' . $key . '][GL_Dist_Detail_Amt]">
                                  </td>
                                  <td>
                                      <input type="text" maxlength="125" class="gl_descript" value="' . $dist['GL_Dist_Detail_Desc'] . '" name="Dist[' . $key . '][GL_Dist_Detail_Desc]" maxlength="125">
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
        <?php echo $form->hiddenField($ap,'AP_ID'); ?>
        <?php echo $form->hiddenField($ap,'Document_ID'); ?>

        <input type="hidden" value="true" name="ap_data_entry_form_values">

        <div class="coa_row" id="COA_Allow_Manual_Coding" style="display: none" data-id="<?=$coaStructure->COA_Allow_Manual_Coding?>">
        </div>

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
    APs weren't found.
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
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/vendor_w9_upload.js"></script>

<?php
Yii::app()->clientScript->registerScriptFile('/js/mousewheel.js', CClientScript::POS_HEAD);
?>

<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/data_entry_detail_ap.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/dataentry_dists_autocomplete.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        new VendorW9Upload('temp');
    });
</script>