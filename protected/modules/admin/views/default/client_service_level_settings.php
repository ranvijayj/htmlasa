<?
/**
 * $settings  - $client->service_settings
 */
?>

<div id="service_levels_company_settings">

    <form method="post" action="/admin/default/updatecompanyservicelevel" id="company_service_level_form">
        <table class="items mbot0" id="company_service_level_table">
            <thead>
            <tr>
                <th class="width120"><span>Level</span></th>
                <th class="width50"><span>Users</span></th>
                <th class="width50"><span>Projects</span></th>
                <th class="width50"><span>Storage, GB</span></th>
                <th class=""><span>Fee, $/M</span></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?php

                    $level_ids =  explode(',',$summary_sl_settings['Tiers_Str']);//array of servise levels for current user

                    $this->renderPartial('application.views.widgets.dropdown_search_style', array(
                        'items'=> $items,
                        'level_ids'=>$level_ids,
                        'summary_sl_settings'=>$summary_sl_settings
                    ));
                ?></td>
                <td id="set_count_users"><?php echo $summary_sl_settings['Users_Count'];?></td>
                <td id="set_count_projects"><?php echo $summary_sl_settings['Projects_Count'];?></td>
                <td id="set_count_storage"><?php echo $summary_sl_settings['Storage_Count'];?></td>
                <td id="set_base_fee"><?php echo number_format($summary_sl_settings['Base_Fee'], 2);?></td>
            </tr>

            <tr>
                <td>Added</td>
                <?php
                    $tiers_str = $summary_sl_settings['Tiers_Str'];
                    $base_level_settings = ClientServiceSettings::getBaseTierValues(explode(',',$tiers_str));
                ?>
                <td><?php echo CHtml::activeTextField($settings, "Additional_Users", array(
                        'class' => 'input_in_grid qty_cell',
                        'disabled' =>(Yii::app()->user->id=='db_admin') ? false : true,
                        'style'=>'background:'.(Yii::app()->user->id=='db_admin' ? 'white' : ''),
                        'data-fee' => $base_level_settings['Max_Add_User_Fee'],
                        'maxlength'=>3
                    ));?></td>
                <td><?php echo CHtml::activeTextField($settings, "Additional_Projects", array(
                        'class' => 'input_in_grid qty_cell',
                        'disabled' =>(Yii::app()->user->id=='db_admin') ? false : true,
                        'style'=>'background:'.(Yii::app()->user->id=='db_admin' ? 'white' : ''),
                        'data-fee' => $base_level_settings['Max_Add_Project_Fee'],
                        'maxlength'=>3
                    ));?></td>
                <td><?php echo CHtml::activeTextField($settings, "Additional_Storage", array(
                        'class' => 'input_in_grid qty_cell',
                        'value'=> $settings->Additional_Storage+1,
                        'disabled' =>(Yii::app()->user->id=='db_admin') ? false : true,
                        'style'=>'background:'.(Yii::app()->user->id=='db_admin' ? 'white' : ''),
                        'data-fee' => $base_level_settings['Max_Add_Storage_Fee'],
                        'maxlength'=>4
                    ));?>


                </td>

                <td id="set_full_fee"><?php echo number_format($summary_sl_settings['Additional_Fee'],2);?></td>
            </tr>



            <?if ($pending_client_service_settings) {?>
                <tr class="pending">
                    <td colspan="5">User's pending settings</td>
                </tr>
                <tr class="pending">
                    <td>Waiting for payments <br/>(Tiers <?=$pending_client_service_settings->Service_Level_ID?>)<br/>active to:
                        <?=date('m/d/Y',strtotime($pending_client_service_settings->Pending_Active_To))?></td>
                    <td id="set_pen_users"><?php echo $pending_client_service_settings->Additional_Users?></td>
                    <td id="set_pen_projects"><?php echo $pending_client_service_settings->Additional_Projects?></td>
                    <td id="set_pen_storage"><?php echo $pending_client_service_settings->Additional_Storage+1?></td>
                    <td id="set_pen_full_fee"><?php echo number_format($pending_client_service_settings->Fee_To_Upgrade,2)?></td>
                </tr>
            <? } ?>


            </tbody>
        </table>
        <div class="group">
            <?php
            if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $settings->Active_To)) {
                $settings->Active_To = Helper::convertDate($settings->Active_To);
            }
            ?>
            <?php echo CHtml::activeLabel($settings, "Active_To");?>
            <?php echo CHtml::activeTextField($settings, "Active_To",array(
                'disabled'=>(Yii::app()->user->id=='db_admin') ? false : true
            ));?>
        </div>
        <?php echo CHtml::activeHiddenField($settings, "Client_ID");?>

        <input type="hidden" name="ClientServiceSettings[Service_Level_ID]" id="ClientServiceSettings_Sevice_Level_ID" value="<?=$summary_sl_settings['Tiers_Str'];?>">


        <div id="service_levels_company_payments">
            <div id="service_levels_company_add_payment" class="left">
                <p class="cl_adm_company_name_row">Add Payment:</p>
                <div class="group">
                    <label for="add_payment_amount">Amount</label>
                    <input type="text" value="" id="add_payment_amount" name="add_payment_amount" class="dollar_fields add_payment">
                </div>
                <div class="group">
                    <label for="add_payment_date">Date</label>
                    <input type="text" value="" id="add_payment_date" name="add_payment_date" class="add_payment">
                </div>
                <div class="group">
                    <label for="add_payment_date">Number</label>
                    <input type="text" value="" id="add_payment_number" name="add_payment_number" class="add_payment">
                </div>
                <div class="center"><button id="submit_new_payment" class="button" data-id="<?=(Yii::app()->user->id=='db_admin') ? 1 : 0?>">Add Payment</button></div>
            </div>
        </div>
        </form>
</div>

            <div id="service_levels_company_payments_list" class="right">
                <table class="items mbot0">
                    <thead>
                    <tr>
                        <th class="width100"><span>Date</span></th>
                        <th><span>Amount</span></th>
                    </tr>
                    </thead>
                </table>
                <div style="height: 235px; overflow: auto">
                    <table class="items" id="company-payments-list-table">
                        <tbody>
                        <?php
                        if (count($payments) > 0) {
                            foreach ($payments as $payment) {
                                echo '<tr>
                                         <td class="width100">' .$payment->Payment_Date . '</td>
                                         <td>' . number_format($payment->Payment_Amount, 2) . '</td>
                                      </tr>';
                            }
                        } else {
                            echo '<tr><td>No Payments</td></tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="clear"></div>
