<span class="sidebar_block_header">Details:</span>
<ul class="sidebar_list">
    <li><h2 class="sidebar_block_list_header"><?php
                                                    //echo CHtml::encode( Helper::truncLongWords($company->Company_Name,20) );
                                                    echo  wordwrap(CHtml::encode($company->Company_Name), 20, "\n", true); ;
                                              ?>
        </h2></li>
    <li>Vendor ID: <span class="details_page_value"><?php echo CHtml::encode($vendor->Vendor_ID); ?></span></li>
    <li>Shortcut: <span class="details_page_value"><?php echo $vendor->Vendor_ID_Shortcut ? CHtml::encode($vendor->Vendor_ID_Shortcut) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>Ck Print: <span class="details_page_value"><?php echo $vendor->Vendor_Name_Checkprint ? CHtml::encode($vendor->Vendor_Name_Checkprint) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>Address: <span class="details_page_value"><?php echo $address->Address1 ? CHtml::encode($address->Address1) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li><span class="details_page_value" style="margin-left: 50px;"><?php echo $address->Address2 ? CHtml::encode($address->Address2) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>City: <span class="details_page_value"><?php echo $address->City ? CHtml::encode($address->City) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>Township: <span class="details_page_value"><?php echo CHtml::encode(''); ?></span></li>
    <li>State / Postal Code: <span class="details_page_value"><?php echo CHtml::encode($address->State . ', ' . $address->ZIP) ? CHtml::encode($address->State . ', ' . $address->ZIP) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>Country: <span class="details_page_value"><?php echo $address->Country ? CHtml::encode($address->Country) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>Phone: <span class="details_page_value"><?php echo $address->Phone ? CHtml::encode($address->Phone) : '<span class="not_set">Not set</span>'; ?></span></span></li>
    <li>Fed ID: <span class="details_page_value"><?php echo $company->Company_Fed_ID ? CHtml::encode($company->Company_Fed_ID) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>Contact: <span class="details_page_value"><?php echo isset($adminPerson->First_Name) ? (CHtml::encode($adminPerson->First_Name . ' ' . $adminPerson->Last_Name)) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>Contact Email: <span class="details_page_value"><?php echo isset($adminPerson->Email) ? CHtml::encode($adminPerson->Email) : '<span class="not_set">Not set</span>'; ?></span></li>

    <li>Created on: <br/><span class="created_on_date"><?php if ($lastDocument) { echo Helper::convertDateString($lastDocument->Created) . '  ' . CHtml::encode($lastDocument->user->person->First_Name) . ' ' . CHtml::encode($lastDocument->user->person->Last_Name); } else {echo '<span class="not_set">Not set</span>';}?></span></li>
</ul>