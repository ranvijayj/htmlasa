<?php
if (count($vendorsList) > 0) {
    foreach ($vendorsList as $vendor) {
        $addresses = $vendor->client->company->adreses;
        $address = $addresses[0];
        ?>
        <tr id="vendor<?php echo $vendor->Vendor_ID; ?>">
            <td class="width30">
                <input type="checkbox" class='list_checkbox' name="vendors[<?php echo $vendor->Vendor_ID; ?>]" value="<?php echo $vendor->Vendor_ID; ?>"/>
            </td>
            <td class="width140">
                <?php echo Helper::cutText(15, 160, 14, $vendor->client->company->Company_Name); ?>
            </td>
            <td class="width70"><?php echo $vendor->Vendor_ID_Shortcut ? CHtml::encode($vendor->Vendor_ID_Shortcut) : '<span class="not_set">Not set</span>'; ?></td>
            <td class="width75">
                <?php echo CHtml::encode($vendor->client->company->Company_Fed_ID); ?>
            </td>
            <td>
                <span class="cutted_cell">
                     <?php
                          echo Helper::createAddressLine($address->Address1, $address->City, $address->State, $address->ZIP, true, 11, 250, 26);
                     ?>
                </span>
            </td>
        </tr>
    <?php
    }
} else {
    echo '<tr>
             <td>
                 Vendors were not found.
             </td>
           </tr>';
}
?>
