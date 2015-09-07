<img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton" style="right: -93px;"/>

<div id="data_entry_left" style="background-color: #ffffff;overflow: auto;z-index: 120;">
    <h2><?php echo isset($company->Company_Name) ? CHtml::encode($company->Company_Name) : 'Company Name';?></h2>
    <span class="de_company_id">
        Company ID: <span class="details_page_value"><?php echo isset($vendor->Vendor_ID_Shortcut) ? CHtml::encode($vendor->Vendor_ID_Shortcut) : ''; ?></span>
    </span>

    <?php $form=$this->beginWidget('CActiveForm', array (
        'id'=>'payment_data_entry_form',
        'htmlOptions'=>array(
            'class'=>'data_entry_form',
        ),
    )); ?>

    <fieldset>
        <!--<div class="group">
            <label style="color: #fff">
                *
            </label>
            <input id="narrow_vendors_list" type="text" value="" maxlength="100" name="narrow_vendors_list" placeholder="Narrow Vend. by Name or Shortcut">
        </div>-->
        <div class="group">
            <label for="Payments_Vendor_ID"><span class="red">*</span> Vendor</label>
            <?php  echo $form->dropDownList($payment,'Vendor_ID', array('0' => 'Unknown Vendor') + $vendorsCP,array('disabled'=>'disabled')); ?>
            <?php echo $form->error($payment,'Vendor_ID'); ?>
        </div>
        <div class="group">
            <?php
            if ($payment->Payment_Check_Number == '0') {
                $payment->Payment_Check_Number = '';
            }
            ?>
            <label for="Payments_Payment_Check_Number"><span class="red">*</span> Pmt. Number</label>
            <?php echo $form->textField($payment,'Payment_Check_Number',array('disabled'=>'disabled')); ?>
            <?php echo $form->error($payment,'Payment_Check_Number'); ?>
            <div id="payment_number_warning" class="errorMessage" style="color: blue"></div>
        </div>
        <div class="group">
            <label for="Payments_Payment_Amount"><span class="red">*</span> Pmt. Amount</label>
            <?php echo $form->textField($payment,'Payment_Amount',array(
                'readonly'=> $payment->Void,
                'class' => 'gl_amount dollar_fields'
            )); ?>
            <?php echo $form->error($payment,'Payment_Amount'); ?>
            <?php  echo '<div class="warningMessage"></div>';?>
        </div>
        <div class="group">
            <label for="Payments_Void">Void payment</label>
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
                                      <input type="text" maxlength="15" class="gl_amount dollar_fields" value="' . $invoice['Invoice_Amount'] . '" name="Invoice[' . $key . '][Invoice_Amount]" '.$readonly .' >
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

        <input type="hidden" value="<?=$return_url;?>" name="return_url" id="return_url">

        <div class="center">
            <?php //echo CHtml::submitButton('Save',array('class'=>'button')); ?>
            <button class="button" id="submit_ajax_pay_form">Save</button>
        </div>
    </fieldset>
    <?php $this->endWidget(); ?>

</div>


