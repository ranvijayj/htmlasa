<?php
if (count($vendorsList) > 0) {
    if ($externalVendors) {
        foreach ($vendorsList as $client) {
            $addresses = $client->company->adreses;
            $address = $addresses[0];
            ?>
            <tr id="client<?php echo $client->Client_ID; ?>">
                <td class="width12" style="padding-left: 0px; padding-right: 0px;">
                    <input type="checkbox" class='list_checkbox' name="clients[<?php echo $client->Client_ID; ?>]" value="<?php echo $client->Client_ID; ?>"/>
                </td>
                <td class="width120">
                    <?php echo Helper::cutText(15, 130, 10, $client->company->Company_Name); ?>
                </td>
                <!--
                <td width="50"><?php //echo CHtml::encode($vendor->Vendor_ID_Shortcut); ?></td>
                -->
                <td class="width75">
                    <?php echo CHtml::encode($client->company->Company_Fed_ID); ?>
                </td>
                <td>
                    <span class="cutted_cell">
                    <?php
                         echo Helper::createAddressLine($address->Address1, $address->City, $address->State, $address->ZIP, true, 11, 200, 22);
                    ?>
                    </span>
                </td>
            </tr>
        <?php
        }
    } else {
        foreach ($vendorsList as $vendor) {
            $addresses = $vendor->client->company->adreses;
            $address = $addresses[0];
            ?>
            <tr id="client<?php echo $vendor->Vendor_Client_ID; ?>">
                <td class="width12" style="padding-left: 0px; padding-right: 0px;">
                    <input type="checkbox" class='list_checkbox' name="clients[<?php echo $vendor->Vendor_Client_ID; ?>]" value="<?php echo $vendor->Vendor_Client_ID; ?>"/>
                </td>
                <td class="width120">
                    <?php echo Helper::cutText(15, 130, 10, $vendor->client->company->Company_Name); ?>
                </td>
                <td class="shortcut_cell width55" data-vendor-id="<?php echo $vendor->Vendor_ID; ?>" data-editing="0"><?php echo  $vendor->Vendor_ID_Shortcut ? CHtml::encode($vendor->Vendor_ID_Shortcut) : '<span class="not_set">Not set</span>'; ?></td>
                <td class="width75">
                    <?php echo CHtml::encode($vendor->client->company->Company_Fed_ID); ?>
                </td>
                <td>
                <span class="cutted_cell">
                    <?php
                    echo Helper::createAddressLine($address->Address1, $address->City, $address->State, $address->ZIP, true, 11, 130, 14);
                    ?>
                </span>
                </td>
            </tr>
        <?php
        }
    }
} else {
    echo '<tr>
             <td>
                 ' . ((isset($queryString) && $queryString == '') ? 'Use search panel to find other companies which W9s are available for you.' : 'Companies were not found.') . '
             </td>
           </tr>';
}
?>
