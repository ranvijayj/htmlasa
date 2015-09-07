<div class="sidebar_item details_sidebar_block" id="company_info">
    <span class="sidebar_block_header">Details:</span>
    <ul class="sidebar_list">
        <li><h2 class="sidebar_block_list_header"><?php echo isset($company->Company_Name) ? wordwrap(CHtml::encode($company->Company_Name), 20, "\n", true) : '<span class="not_set">Vendor not attached</span>'; ?></h2></li>
        <li>Amount: <span class="details_page_value"><?php echo ($payment->Payment_Amount != 0) ? CHtml::encode(number_format($payment->Payment_Amount, 2)) : '<span class="not_set">Not set</span>'; ?></span></li>
        <li>Check # / Date: <span class="details_page_value"><?php echo ($payment->Payment_Check_Number && $payment->Payment_Check_Date) ? CHtml::encode($payment->Payment_Check_Number)  . ' / ' .  CHtml::encode(Helper::convertDate($payment->Payment_Check_Date)) : '<span class="not_set">Not set</span>'; ?></span></li>
        <li>Account Num: <span class="details_page_value"><?php echo $bankAccount ? CHtml::encode($bankAccount) : '<span class="not_set">Not set</span>'; ?></span></li>
        <li>Address: <span class="details_page_value"><?php echo isset($address->Address1) ? CHtml::encode($address->Address1) : '<span class="not_set">Not set</span>'; ?></span></li>
        <li><span class="details_page_value" style="margin-left: 50px;"><?php echo isset($address->Address2) ? CHtml::encode($address->Address2) : '<span class="not_set">Not set</span>'; ?></span></li>
        <li>City: <span class="details_page_value"><?php echo isset($address->City) ? CHtml::encode($address->City) : '<span class="not_set">Not set</span>'; ?></span></li>
        <li>State, Zip: <span class="details_page_value"><?php echo isset($address->State) ? (CHtml::encode($address->State) . ', ' . CHtml::encode($address->ZIP)) : '<span class="not_set">Not set</span>'; ?></span></li>
        <li>Check Number: <span class="details_page_value"><?php echo $payment->Payment_Check_Number ? CHtml::encode($payment->Payment_Check_Number) : '<span class="not_set">Not set</span>'; ?></span></li>
        <li>Country: <span class="details_page_value"><?php echo isset($address->Country) ? CHtml::encode($address->Country) : '<span class="not_set">Not set</span>'; ?></span></li>
    </ul>
    <span class="sidebar_block_header">Invoices Attached:</span>
    <div class="sidebar_attd_inv_list">
        <?php
            if (is_array($aps)) {
                foreach ($aps as $ap) {
                    echo '<span class="details_page_value">' . CHtml::encode($ap->Check_Invoice_Number) . ' / ' . CHtml::encode(number_format($ap->Check_Invoice_Amount, 2)) . '</span><br />';
                }
            }
        ?>
    </div>
</div>
<span class="sidebar_block_header">Created on:</span><br/>
<span class="created_on_date"><?php echo Helper::convertDateString($document->Created) . ' ' . CHtml::encode($user->person->First_Name) . ' ' . CHtml::encode($user->person->Last_Name); ?></span>