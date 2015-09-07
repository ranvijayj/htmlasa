<span class="sidebar_block_header">Details:</span>

<ul class="sidebar_list">
    <li><h2 class="sidebar_block_list_header"><?php echo isset($company->Company_Name) ? CHtml::encode(Helper::truncLongWords($company->Company_Name,17)) : '<span class="not_set">Vendor not attached</span>'; ?></h2></li>
    <li>Amount: <span class="details_page_value"><?php echo CHtml::encode($ap->Invoice_Amount) ? CHtml::encode(number_format($ap->Invoice_Amount, 2,'.', ',')) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>Date: <span class="details_page_value"><?php echo CHtml::encode($ap->Invoice_Date) ? CHtml::encode(Helper::convertDate($ap->Invoice_Date)) : '<span class="not_set">Not Set</span>'; ?></span></li>
    <li>Due Date: <span class="details_page_value"><?php echo CHtml::encode($ap->Invoice_Due_Date) ? CHtml::encode(Helper::convertDate($ap->Invoice_Due_Date)) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>Number: <span class="details_page_value"><?php echo (($ap->Invoice_Number != '0') ? CHtml::encode($ap->Invoice_Number) : '<span class="not_set">Not set</span>'); ?></span></li>
    <li>Vendor ID:  <span class="details_page_value"><?php echo (($ap->Vendor_ID != 0) ? CHtml::encode($ap->Vendor_ID) : '<span class="not_set">Not set</span>'); ?></span></li>
    <li>Check Number: <span class="details_page_value"><?php echo $paymentCheckNumber ? CHtml::encode($paymentCheckNumber) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>Created on: <br/><span class="created_on_date"><?php echo $document->Created ? Helper::convertDateString($document->Created) . ' ' . CHtml::encode($user->person->First_Name) . ' ' . CHtml::encode($user->person->Last_Name) : '<span class="not_set">Not set</span>'; ?></span></li>

            <?php
/*
            echo '<br/>AP ID :';
            echo $ap->AP_ID;

            echo '<br/>AP approval value :';
            echo $ap->AP_Approval_Value;

            echo '<br/>AP Approved :';
            echo $ap->Approved;


            echo '<br/>AP prev appr value :';
            echo $ap->Previous_AP_A_Val;


            echo '<br/>AP ClientID :';
                //var_dump($user);
                echo $document->Client_ID;
            echo '<br/>Doc ProjectID :';
            echo $document->Project_ID;


*/


            ?>
    </span></li>
</ul>