<span class="sidebar_block_header">Details:</span>
<ul class="sidebar_list">
    <li><h2 class="sidebar_block_list_header"><?php echo isset($pc->Employee_Name) ? CHtml::encode($pc->Employee_Name) : '<span class="not_set">Not set</span>'; ?></h2></li>
    <li>Total: <span class="details_page_value"><?php echo ($pc->Envelope_Total != 0) ? CHtml::encode(number_format($pc->Envelope_Total, 2)) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>Number: <span class="details_page_value"><?php echo ($pc->Envelope_Number) ? CHtml::encode($pc->Envelope_Number) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>Date: <span class="details_page_value"><?php echo ($pc->Envelope_Date) ? CHtml::encode(Helper::convertDate($pc->Envelope_Date)) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>Created on: <br/><span class="created_on_date"><?php echo $document->Created ? Helper::convertDateString($document->Created) . ' ' . CHtml::encode($user->person->First_Name) . ' ' . CHtml::encode($user->person->Last_Name) : '<span class="not_set">Not set</span>'; ?></span></li>
</ul>