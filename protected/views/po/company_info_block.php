<span class="sidebar_block_header">Details:</span>
<ul class="sidebar_list">
    <li><h2 class="sidebar_block_list_header"><?php echo isset($company->Company_Name) ? CHtml::encode(Helper::truncLongWords($company->Company_Name,17)) : '<span class="not_set">Vendor not attached</span>'; ?></h2></li>
    <li>Number: <span class="details_page_value"><?php echo (($po->PO_Number != 0) ? CHtml::encode($po->PO_Number) : '<span class="not_set">Not set</span>'); ?></span></li>
    <li>Date: <span class="details_page_value"><?php echo CHtml::encode($po->PO_Date) ? CHtml::encode(Helper::convertDate($po->PO_Date)) : '<span class="not_set">Not Set</span>'; ?></span></li>
    <li>Job Name: <span class="details_page_value"><?php echo (isset($poFormatting->PO_Format_Job_Name) && $poFormatting->PO_Format_Job_Name) ? CHtml::encode($poFormatting->PO_Format_Job_Name) : '<span class="not_set">Not Set</span>'; ?></span></li>
    <li>Acct. Num: <span class="details_page_value"><?php echo CHtml::encode($po->PO_Account_Number) ? CHtml::encode($po->PO_Account_Number) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>Total: <span class="details_page_value"><?php echo CHtml::encode($po->PO_Total) ? CHtml::encode(number_format($po->PO_Total, 2)) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>Payment Type:  <span class="details_page_value"><?php echo isset($this->paymentTypes[$po->Payment_Type]) ? $this->paymentTypes[$po->Payment_Type] . (($po->Payment_Type == 'CC') ? ' (' . $po->PO_Card_Last_4_Digits . ')' : '') : '<span class="not_set">Not set</span>'; ?></span></li>

    <li>Created on: <br/><span class="created_on_date"><?php echo $document->Created ? Helper::convertDateString($document->Created) . ' ' . CHtml::encode($user->person->First_Name) . ' ' . CHtml::encode($user->person->Last_Name) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li><br/></li>
<?php if ($userApprovalRange['user_appr_val']> $po->PO_Approval_Value) {
    echo '<br/>Management : <a href=# id="mark_as_void" data-id="'.$po->PO_ID.'">Delete</a>';
}?>
</ul>


<span class="sidebar_block_header">Budget Comparison:</span>
<div class="sidebar_attd_inv_list">
    <?php
    if (is_array($budgets[$po->PO_ID])) {
        foreach ($budgets[$po->PO_ID] as $code => $budget) {
            echo '<span class="details_page_value">' . CHtml::encode($code) . ' / ' . $budget . '</span><br />';
        }
    }
    ?>
</div>
<script>

    $(document).ready(function() {

        $('#mark_as_void').on('click',function(event) {
            event.stopPropagation();

            var poId = $(this).data('id');

            $.ajax({
                url: '/po/markasvoid',
                data: {
                    po_id: poId
                },
                async: true,
                type: "POST",
                success: function() {

                window.location = '/po';
                }
            });

        });

    });



</script>





    </script>