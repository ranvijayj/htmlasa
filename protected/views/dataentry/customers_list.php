<option selected="selected" value="0">Unknown Customer</option>
<?php
    if (count($customers) > 0) {
        foreach ($customers as $customer) {
            ?>
                <option value="<?php echo $customer->Customer_ID ?>"><?php echo ($customer->Cust_ID_Shortcut ? $customer->Cust_ID_Shortcut . ' - ' : '') . $customer->client->company->Company_Name; ?></option>
            <?php
        }
    }
?>
