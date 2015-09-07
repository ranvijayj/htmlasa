<span class="sidebar_block_header">Details:</span>
<ul class="sidebar_list">
    <li><h2 class="sidebar_block_list_header"><?php echo wordwrap(CHtml::encode($company->Company_Name), 20, "\n", true); ?></h2></li>
    <li>Fed ID: <span class="details_page_value"><?php echo $company->Company_Fed_ID; ?></span></li>
    <li>Addre. 1: <span class="details_page_value"><?php echo $adress->Address1 ? CHtml::encode($adress->Address1) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>Addre. 2: <span class="details_page_value"><?php echo $adress->Address2 ? CHtml::encode($adress->Address2) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>City: <span class="details_page_value"><?php echo $adress->City ? CHtml::encode($adress->City) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>State: <span class="details_page_value"><?php echo $adress->State ? CHtml::encode($adress->State) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>Zip: <span class="details_page_value"><?php echo $adress->ZIP ? CHtml::encode($adress->ZIP) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>Country: <span class="details_page_value"><?php echo $adress->Country ? CHtml::encode($adress->Country) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>Contact - Phone: <span class="details_page_value"><?php echo (isset($adminPerson->First_Name) ? (CHtml::encode($adminPerson->First_Name . ' ' . $adminPerson->Last_Name)) : ''); if (isset($adminPerson->Direct_Phone) && $adminPerson->Direct_Phone) echo ', ' . CHtml::encode($adminPerson->Direct_Phone);?></span></li>
    <li>Created on: <span class="created_on_date"><?php echo Helper::convertDateString($lastDocument->Created); ?></span></li>
</ul>