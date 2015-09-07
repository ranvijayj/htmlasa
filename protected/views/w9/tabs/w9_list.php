<div class='list_block'>
    <table id="list_table_head">
        <thead>
        <tr class="table_head">
            <th class="width30">
                <input type="checkbox" id="check_all" name="check_all"/>
            </th>
            <th class="width140" id="vendor_name_cell_header">
                Vendor Name
            </th>
            <th class="width75" id="fed_id_cell_header">
                Fed ID
            </th>
            <th class="width85" id="address_cell_header">
                Address
            </th>
            <th>
                <span class="cutted_cell"></span>
            </th>
        </tr>
        </thead>
    </table>
    <div class="table_list_scroll_block">
    <form action="/w9/addw9itemstosession" id="detail_form" method="post">

        <?//displaying w9 of the company user logged in?>
        <?if ($current_client_w9) {?>
            <table id="first_row">
                <tr id="com<?php echo $current_client_w9['Company_ID']; ?>" <?php echo $color; ?>>
                    <td class="width30">
                        <input type="checkbox" class='list_checkbox' name="companies[<?php echo $current_client_w9['Company_ID']; ?>]" value="<?php echo $current_client_w9['Company_ID']; ?>" <?php echo $checked; ?>/>
                    </td>
                    <td class="width140">
                        <?php echo Helper::cutText(15, 170, 14,  $current_client_w9['Company_Name']); ?>
                    </td>

                    <td class="fed_id_cell width75"><?php echo $current_client_w9['Company_Fed_ID']; ?></td>
                    <td>
                        <?php echo Helper::createAddressLine($current_client_w9['Address1'], $current_client_w9['City'], $current_client_w9['State'], $current_client_w9['ZIP'], true, 15, 340, 30);; ?>
                    </td>
                </tr>
            </table>
        <?}?>

        <table id="list_table">
            <tbody>
            <?php
            if (count($vendorsList) > 0) {
                foreach ($vendorsList as $vendor) {
                    $checked = "";
                    $color = '';
            ?>
                    <tr id="com<?php echo $vendor['Company_ID']; ?>" <?php echo $color; ?>>
                        <td class="width30">
                            <input type="checkbox" class='list_checkbox' name="companies[<?php echo $vendor['Company_ID']; ?>]" value="<?php echo $vendor['Company_ID']; ?>" <?php echo $checked; ?>/>
                        </td>
                        <td class="width140">
                            <?php echo Helper::cutText(15, 170, 14,  $vendor['Company_Name']); ?>
                        </td>
                        <td class="fed_id_cell width75"><?php echo $vendor['Company_Fed_ID']; ?></td>
                        <td>
                            <?php echo Helper::createAddressLine($vendor['Address1'], $vendor['City'], $vendor['State'], $vendor['ZIP'], true, 15, 340, 30);; ?>
                        </td>
                    </tr>
                <?php
                }
            } else {
                echo '<tr>
             <td>
                 Use search panel to populate this grid.
             </td>
           </tr>';
            }
            ?>
            </tbody>
        </table>
    </form>
    </div>
</div>