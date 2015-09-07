<?php
if (count($paymentsList) > 0) {
    foreach ($paymentsList as $payment) {
        $checked = "";
        $color = '';
        /*
        if (in_array($payment->Document_ID, $payments_to_review)) {
            $checked = "checked='checked'";
            $color = 'style="background-color: #eee"';
        } else {
            $checked = "";
            $color = '';
        }
        */
        ?>
        <tr id="doc<?php echo $payment->Document_ID; ?>">
            <td class="width30">
                <input type="checkbox" class='list_checkbox' name="documents[<?php echo $payment->Document_ID; ?>]" value="<?php echo $payment->Document_ID; ?>"/>
            </td>
            <td class="width180">
                <?php echo isset($payment->vendor->client->company->Company_Name) ? Helper::cutText(15, 230, 20, $payment->vendor->client->company->Company_Name) : '<span class="not_set">Vendor not attached</span>'; ?>
            </td>
            <td class="amount_cell width80">
                <?php echo ($payment->Payment_Amount != 0) ? Helper::cutText(15, 85, 11, number_format($payment->Payment_Amount, 2)) : '<span class="not_set">Not set</span>'; ?>
            </td>
            <td class="width100">
                <?php echo $payment->Payment_Check_Number ? CHtml::encode($payment->Payment_Check_Number) : '<span class="not_set">Not set</span>'; ?>
            </td>
            <td class="width80">
                <?php echo $payment->Payment_Check_Date ? CHtml::encode(Helper::convertDate($payment->Payment_Check_Date)) : '<span class="not_set">Not set</span>'; ?>
            </td>
            <td>
                <span class="cutted_cell">
                    <?php echo isset($payment->bank_account->Account_Number) ? CHtml::encode(Helper::prepareAcctNum($payment->bank_account->Account_Number, 4)) : '<span class="not_set">Not set</span>'; ?>
                </span>
            </td>
        </tr>
    <?php
    }
} else {
    echo '<tr>
             <td>
                 Payments were not found.
             </td>
           </tr>';
}
?>
