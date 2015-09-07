<label>Vendor Name:</label><p class="underlined_field"><?php echo isset($currentVendor->client->company) ? $currentVendor->client->company->Company_Name : ''; ?> </p>
<div class="clear"></div>
<label>Address:</label><p class="underlined_field"><?php echo isset($currentVendor->client->company) ? $currentVendor->client->company->adreses[0]->Address1 : '';?> </p>
<div class="clear"></div>
<label>City/State/Zip:</label><p class="underlined_field"><?php echo isset($currentVendor->client->company) ? Helper::createFullAddressLine('', $currentVendor->client->company->adreses[0]->City, $currentVendor->client->company->adreses[0]->State, $currentVendor->client->company->adreses[0]->ZIP) : '';?> </p>
<div class="clear"></div>
<label>Federal ID #:</label><p class="underlined_field"><?php echo isset($currentVendor->client->company) ? CHtml::encode($currentVendor->client->company->Company_Fed_ID) : '';?> </p>
<div class="clear"></div>
<label>Contact:</label><p class="underlined_field"><?php echo isset($vendorAdmin) ? CHtml::encode($vendorAdmin->user->person->First_Name . ' ' . $vendorAdmin->user->person->Last_Name . ' ' . $vendorAdmin->user->person->Email) : '';?> </p>
<div class="clear"></div>
<label>Phone:</label><p class="underlined_field"><?php echo isset($currentVendor->client->company) ? $currentVendor->client->company->adreses[0]->Phone : '';?> </p>
<div class="clear"></div>
<label>Fax:</label><p class="underlined_field"><?php echo isset($currentVendor->client->company) ? $currentVendor->client->company->adreses[0]->Fax : '';?> </p>

