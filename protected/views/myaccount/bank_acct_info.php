<?php
    if ($userClientRow->hasClientAdminPrivileges()) {
        $style = 'style="cursor: pointer;"';
    } else {
        $style = '';
    }
?>
<h2>Account Detail:</h2>
<ul class="sidebar_active_list">
    <li><span class="acct_det_first_col">Acct. Num:</span><span class="details_page_value" <?php echo $style; ?>><?php echo CHtml::encode($bankAcct->Account_Number); ?></span></li>
    <li><span class="acct_det_first_col">Acct. Name:</span><span class="details_page_value" <?php echo $style; ?>><?php echo CHtml::encode($bankAcct->Account_Name); ?></span></li>
    <li><span class="acct_det_first_col">Bank Name:</span><span class="details_page_value" <?php echo $style; ?>><?php echo  CHtml::encode($bankAcct->Bank_Name); ?></span></li>
    <li><span class="acct_det_first_col">Bank Routine:</span><span class="details_page_value" <?php echo $style; ?>><?php echo CHtml::encode($bankAcct->Bank_Routing); ?></span></li>
    <li><span class="acct_det_first_col">SWIFT:</span><span class="details_page_value" <?php echo $style; ?>><?php echo CHtml::encode($bankAcct->Bank_SWIFT); ?></span></li>
</ul>