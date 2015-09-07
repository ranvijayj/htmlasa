<?php
/* @var $this VendorController */

$this->breadcrumbs=array('Vendor Management');
?>
<h1>Vendor Management: <?=@CHtml::encode(Yii::app()->user->userLogin);?><span class="right items_to_review" id="items_to_review" data-id="<?php echo count($vendorsList); ?>" ">Number of Vendors in List: <?php echo count($vendorsList); ?></span></h1>

<div class="account_manage">
    <? //if (Yii::app()->user->userType != UsersClientList::USER) {?>
    <? if ( in_array(Yii::app()->user->userType,UsersClientList::$clientAdmins)) {?>
    <div class="account_header_left left">
        <button class="button" id="add_users_to_list">Include</button>
        <button class="button" id="remove_users_to_list">Remove</button>
        <button class="button" id="import_vendors_list">Copy From</button>
        <button class="button" id="export_vendors_list">Copy To</button>
    </div>
    <?}?>

    <div class="right">
        <span class="left search_block_label">Search: &nbsp;</span>
        <div class="search_block">
            <input type="text" name="search" id="search_field" maxlength="250">
            <div id="search_options">
                <span class="search_options_header">Search in the fields:</span><br/>
                <label><input type="checkbox" id="search_option_temporary" value="0" name="search_option_com_name" />Temporary Vendors</label><br/>
                <label><input type="checkbox" id="search_option_international" value="0" name="search_option_com_name" />International Vendors</label><br/>
                <div class="delimiter" style="border-top: 1px solid;"></div>
                <label><input type="checkbox" id="search_option_com_name" value="1" name="search_option_com_name" checked="checked" />Vendor Name</label><br/>
                <label><input type="checkbox" id="search_option_fed_id" value="1" name="search_option_fed_id" checked="checked"/>Fed ID</label><br/>
                <label><input type="checkbox" id="search_option_addr1" value="1" name="search_option_addr1"/>Addre. 1</label><br/>
                <label><input type="checkbox" id="search_option_addr2" value="1" name="search_option_addr2"/>Addre. 2</label><br/>
                <label><input type="checkbox" id="search_option_city" value="1" name="search_option_city"/>City</label><br/>
                <label><input type="checkbox" id="search_option_state" value="1" name="search_option_state"/>State</label><br/>
                <label><input type="checkbox" id="search_option_zip" value="1" name="search_option_zip"/>Zip</label><br/>
                <label><input type="checkbox" id="search_option_country" value="1" name="search_option_country"/>Country</label><br/>
                <label><input type="checkbox" id="search_option_phone" value="1" name="search_option_phone"/>Phone</label>
            </div>
        </div>
    </div>
</div>
<?php if(Yii::app()->user->hasFlash('success')):?>
    <div class="info">
        <button class="close-alert">&times;</button>
        <?php echo Yii::app()->user->getFlash('success'); ?>
    </div>
<?php endif; ?>
<div class="left_column vendors_left_column" id="left_column" style="position: relative;">

    <?php
    //if(!$checkedTBABox) {

    if (!isset($_SESSION['limiter_vendor_left'])) {
        $_SESSION['limiter_vendor_left']=Aps::DISPLAY_LIMIT;
        echo '<div id="limiter_div_left" style="position:absolute;top:2px;right:5px"><label><input type="checkbox" id="limiter_checkbox_left" data-value="'.Aps::DISPLAY_LIMIT.'" checked="true"> Limit '.Aps::DISPLAY_LIMIT.'</label></div>';
    } else {
        if ($_SESSION['limiter_vendor_left']==Aps::DISPLAY_LIMIT) {
            echo '<div id="limiter_div_left" style="position:absolute;top:2px;right:5px"><label><input type="checkbox" id="limiter_checkbox_left" data-value="'.Aps::DISPLAY_LIMIT.'" checked="true"> Limit '.Aps::DISPLAY_LIMIT.'</label></div>';
        } else {
            echo '<div id="limiter_div_left" style="position:absolute;top:2px;right:5px"><label><input type="checkbox" id="limiter_checkbox_left" data-value="'.Aps::DISPLAY_LIMIT.'" > Limit '.Aps::DISPLAY_LIMIT.'</label></div>';
        }
    }
    ?>

    <p class="table_select" data-id="list_table">Company Vendor List:</p>
    <table id="list_table_head">
        <thead>
        <tr class="table_head">
            <th class="width2">

            </th>
            <th class="width120" id="vendor_name_cell_header">
                Vendor Name
            </th>
            <th class="width55" id="shortcut_cell_header">
                Shortcut
            </th>
            <th class="width65"" id="number_cell_header">
                Number
            </th>
            <th>
                <span class="cutted_cell">[Address + City + St + Zip]</span>
            </th>
        </tr>
        </thead>
    </table>
    <div class="table_list_scroll_block" style="position: relative;">

        <form action="/vendor/removevendorsfromlist" id="vendor_detail_form" method="post">
            <div id="scroll_wrapper" >
            <table id="list_table">
                <tbody>
                <?php
                if (count($vendorsList) > 0) {
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
                } else {
                    echo '<tr>
             <td>
                 Vendors were not found.
             </td>
           </tr>';
                }
                ?>
                </tbody>
            </table>


            </div>
        </form>


</div>
    <div id="loading_mask_left" style="position: absolute;bottom: 0px; height: 50px; width:455px;background: url(/images/rotate.gif) center no-repeat #fff; display: none;">  </div>
</div>
<div class="right_column vendors_right_column" id="right_column" style="position: relative;">

    <?php
    //if(!$checkedTBABox) {
    if (!isset($_SESSION['limiter_vendor_right'])) {
        $_SESSION['limiter_vendor_right']=Aps::DISPLAY_LIMIT;
        echo '<div id="limiter_div_add" style="position:absolute;top:2px;right:5px"><label><input type="checkbox" id="limiter_checkbox_right" data-value="'.Aps::DISPLAY_LIMIT.'"  checked="true"> Limit '.Aps::DISPLAY_LIMIT.'</label></div>';

    } else {

        if ($_SESSION['limiter_vendor_right']==Aps::DISPLAY_LIMIT) {
            echo '<div id="limiter_div_right" style="position:absolute;top:2px;right:5px"><label><input type="checkbox" id="limiter_checkbox_right" data-value="'.Aps::DISPLAY_LIMIT.'" checked="true"> Limit '.Aps::DISPLAY_LIMIT.'</label></div>';

        } else {
            echo '<div id="limiter_div_right" style="position:absolute;top:2px;right:5px"><label><input type="checkbox" id="limiter_checkbox_right" data-value="'.Aps::DISPLAY_LIMIT.'" > Limit '.Aps::DISPLAY_LIMIT.'</label></div>';
        }
    }


    ?>
    <p class="table_select" data-id="vendor_list_table">Master Vendor List:</p>
    <table class="list_table_head">
        <thead>
        <tr class="table_head">
            <th class="width2">

            </th>
            <th class="width120" id="vendor_name_cell_header_ext">
                Vendor Name
            </th>
            <!--
            <th width="50" id="shortcut_cell_header_ext">
                Shortcut
            </th>
            -->
            <th class="width75" id="number_cell_header_ext">
                Number
            </th>
            <th>
                <span class="cutted_cell">[Address + City + St + Zip]</span>
            </th>
        </tr>
        </thead>
    </table>
    <div class="table_list_scroll_block">

        <form action="/vendor/addvendorstolist" id="ext_vendor_list" method="post">
            <table id="vendor_list_table" class='list_table'>
                <tbody>

                <?php

                if (count($externalClients) > 0) {
                    foreach ($externalClients as $client) {
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
                    echo '<tr>
             <td>
                 Clients were not found.
             </td>
           </tr>';
                }

                ?>
                </tbody>
            </table>
        </form>

    </div>

</div>

<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/w9_list.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/vendor_manage.js"></script>

<?php $this->renderPartial('application.views.widgets.copy_vendors_list', array(
    'companiesToCopyList' => $companiesToCopyList,
)); ?>

<script>

    $(document).ready(function() {
        new VendorManage;

        });
</script>