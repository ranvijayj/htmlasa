<?php
$this->breadcrumbs=array(
    'Payment Data Entry'
);
?>
<h1>Payment Data Entry
    <div id="filename_section" style="float: right;margin-right:10px;font-size:17.7px;font-style: normal;font-weight: normal; " ><?=$file->File_Name; ?> </div>
</h1>

<div class="account_manage">
 <div class="de_nav_wrapper">
    <?php if ($num_pages > 0) { ?>
        <div class="data_entry_nav_wrapper">
            <?php $this->renderPartial('application.views.widgets.pages_navigation_de', array(
                'page' => $page,
                'num_pages' => $num_pages,
                'url' => '/dataentry/payments',
                'position' => 'left'
            )); ?>
        </div>
    <?php } ?>

    <form method="post" action="/dataentry/<?=$this->action->id?>">
        <div class="left" style="margin-left: <?php echo ($num_pages > 0) ? '65' : '10'; ?>px;">
            <span class="left search_block_label">Search: &nbsp;</span>
            <div class="search_block">
                <input type="text" name="search_field" id="search_field" maxlength="250" value="<?php echo $_SESSION['last_paym_to_entry_search']['query'];?>" autocomplete="off">
                <div id="search_options">
                    <span class="search_options_header">Search in the fields:</span><br/>
                    <?php
                    echo Helper::getSearchOptionsHtml(array(
                        'session_name' => 'last_paym_to_entry_search',
                        'options' => array(
                            'search_option_com_name' => array('Company name', 1),
                            'search_option_fed_id' => array('Company Fed ID', 1),
                            'search_option_pmt_num' => array('Pmt. Number', 0),
                            'search_option_pmt_amount' => array('Pmt. Amount', 1),
                            'search_option_pmt_date' => array('Pmt. Date', 1),
                        ),
                    ));
                    ?>
                </div>
            </div>
        </div>
    </form>

    <span class="de_count_items" style="margin-left: <?php echo ($num_pages > 0) ? '65' : '10'; ?>px;">Payments to Process: <?php echo $num_pages; ?> items</span>
 </div>
</div>
<?php if(Yii::app()->user->hasFlash('success')):?>
    <div class="info">
        <button class="close-alert">&times;</button>
        <?php echo Yii::app()->user->getFlash('success'); ?>
    </div>
<?php endif; ?>
<div id="data_entry_left_right_wrapper">
<div id="dataentry_block">
    <div id="data_entry_left">
        <?php if ($num_pages > 0) {?>
            <h2><?php echo isset($company->Company_Name) ? CHtml::encode($company->Company_Name) : 'Company Name';?></h2>
            <span class="de_company_id">
                Company ID: <span class="details_page_value"><?php echo isset($vendor->Vendor_ID_Shortcut) ? CHtml::encode($vendor->Vendor_ID_Shortcut) : ''; ?></span>
            </span>

            <?if (Yii::app()->user->id != 'data_entry_clerk' && $document->Client_ID == Yii::app()->user->clientID ) {?>
                <div style="position: relative;left: 235px;">
                    <a href="#" class="add_new_vendor"> Add new vendor </a>
                </div>
            <?}?>

            <?php $form=$this->beginWidget('CActiveForm', array (
                'id'=>'payment_data_entry_form',
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
                    <label for="Payments_Vendor_ID"><span class="red">*</span> Vendor</label>
                    <?php  echo $form->dropDownList($payment,'Vendor_ID', array('0' => 'Unknown Vendor') + $vendorsCP); ?>
                    <?php echo $form->error($payment,'Vendor_ID'); ?>
                </div>
                <div class="group">
                    <?php
                    if ($payment->Payment_Check_Number == '0') {
                        $payment->Payment_Check_Number = '';
                    }
                    ?>
                    <label for="Payments_Payment_Check_Number"><span class="red">*</span> Pmt. Number</label>
                    <?php echo $form->textField($payment,'Payment_Check_Number'); ?>
                    <?php echo $form->error($payment,'Payment_Check_Number'); ?>
                    <div id="payment_number_warning" class="errorMessage" style="color: blue"></div>
                </div>
                <div class="group">
                    <label for="Payments_Payment_Amount"><span class="red">*</span> Pmt. Amount</label>
                    <?php echo $form->textField($payment,'Payment_Amount',array(
                        'readonly'=> $payment->Void,
                        'class'=>'dollar_fields'
                    )); ?>
                    <?php echo $form->error($payment,'Payment_Amount'); ?>
                    <?php  echo '<div class="warningMessage"></div>';?>
                </div>
                <div class="group">
                    <label for="Void">Void payment</label>
                    <?php echo $form->checkBox($payment,'Void'); ?>
                    <?php echo $form->error($payment,'Void'); ?>
                </div>
                <div class="group">
                    <?php
                    // convert date string to view format
                    if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $payment->Payment_Check_Date)) {
                        $payment->Payment_Check_Date = Helper::convertDateSimple($payment->Payment_Check_Date);
                    }
                    ?>
                    <label for="Payments_Payment_Check_Date"><span class="red">*</span> Pmt. Date</label>
                    <?php echo $form->textField($payment,'Payment_Check_Date'); ?>
                    <?php echo $form->error($payment,'Payment_Check_Date'); ?>
                    <?php
                    if (!isset($payment['_errors']["Payment_Check_Date"])) {
                        echo '<div class="errorMessage grey">Correct date format: mm/dd/yyyy!</div>';
                    }
                    ?>
                </div>
                <div class="group">
                    <label for="Acct_Number">Acct. Number</label>
                    <?php  echo $form->dropDownList($payment,'Account_Num_ID', array('0' => 'Unknown Acct. Number') + $acctNumbs); ?>
                    <?php echo $form->error($payment,'Account_Num_ID'); ?>
                </div>
                <div class="group de_invoices">
                    <label><span class="red">*</span> Invoices</label>
                    <?php
                    if ($invalidInvoicesTopMess != '') {
                        echo '<div class="errorMessage" id="invalidInvoicesTopMess">' . $invalidInvoicesTopMess . '</div>';
                    }
                    ?>
                    <div class="de_invoices_fields">
                        <table id="attached_invoices_head">
                            <thead>
                            <tr>
                                <th class="width80">
                                    Invoice #
                                </th>
                                <th>
                                    Amount
                                </th>
                            </tr>
                            </thead>
                        </table>
                        <div id="attached_invoices_block">
                            <table id="attached_invoices">
                                <tbody>
                                <?php

                                $readonly = ($payment->Void==1) ? 'readonly' : '';
                                foreach($invoices as $key => $invoice) {

                                    echo '<tr><td>';
                                    echo '<input type="text" maxlength="45" class="invoice_number" value="' . $invoice['Invoice_Number'] . '" name="Invoice[' . $key . '][Invoice_Number]" '.$readonly .'>';
                                    echo  '</td>
                                  <td>
                                      <input type="text" maxlength="15" class="dollar_fields" value="' . $invoice['Invoice_Amount'] . '" name="Invoice[' . $key . '][Invoice_Amount]" '.$readonly .' >
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
                    if ($invalidInvoices != '') {
                        echo '<div class="errorMessage" ' . (($invalidInvoicesTopMess != '') ? 'id="invalidInvoices"' : '') . '>' . $invalidInvoices . '</div>';
                    }
                    ?>
                </div>
                <?php echo $form->hiddenField($payment,'Payment_ID'); ?>
                <?php echo $form->hiddenField($payment,'Document_ID'); ?>
                <input type="hidden" value="true" name="payment_data_entry_form_values">

                <div class="center">
                    <?php //echo CHtml::submitButton('Save',array('class'=>'button')); ?>
                    <button class="button" id="submit_payment_form">Save</button>
                </div>
            </fieldset>
            <?php $this->endWidget(); ?>

            <div class="delimiter" style="height: 30px;"></div>
            <?if ( in_array(Yii::app()->user->id,array('admin','db_admin')) ) {
                $this->renderPartial("application.views.widgets.document_details",array('document'=> $document)); //document details widget
            }?>

        <?php } else {?>
            Payments weren't found.
        <?php } ?>
    </div>

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
    <?php $this->renderPartial('application.views.widgets.payment_dialog'); ?>
</div>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/main.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/data_entry_detail.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/document_view.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/asa_datepicker.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/payments_dataentry.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/vendor_w9_upload.js"></script>
<?php
Yii::app()->clientScript->registerScriptFile('/js/mousewheel.js', CClientScript::POS_HEAD);
?>
<script type="text/javascript">
    $(document).ready(function() {
        new DataEntryDetail;
        new VendorW9Upload('temp');
    });
</script>