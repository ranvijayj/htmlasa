<option selected="selected" value="0">Unknown Vendor</option>
<?php
    if (count($vendors) > 0) {
        foreach ($vendors as $vendor) {
            ?>
                <option value="<?php echo $vendor->Vendor_ID ?>"><?php echo ($vendor->Vendor_ID_Shortcut ? $vendor->Vendor_ID_Shortcut . ' - ' : '') . $vendor->client->company->Company_Name; ?></option>
            <?php
        }
    }
?>
