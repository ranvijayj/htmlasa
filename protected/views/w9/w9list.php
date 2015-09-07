<?php
 if (count($vendorsList) > 0) {
     foreach ($vendorsList as $vendor) {
         ?>
         <tr id="com<?php echo $vendor['Company_ID']; ?>">
             <td class="width30">
                 <input type="checkbox" class='list_checkbox' name="companies[<?php echo $vendor['Company_ID']; ?>]" value="<?php echo $vendor['Company_ID']; ?>"/>
             </td>
             <td class="width140">
                 <?php echo Helper::cutText(15, 170, 14,  $vendor['Company_Name']); ?>
             </td>
             <td class="fed_id_cell width75"><?php echo $vendor['Company_Fed_ID']; ?></td>
             <td>
                 <?php echo Helper::createAddressLine($vendor['Address1'], $vendor['City'], $vendor['State'], $vendor['ZIP'], true, 15, 340, 30); ?>
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
