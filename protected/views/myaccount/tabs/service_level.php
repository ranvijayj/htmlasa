<h2>Service Level</h2>
<?php
$expirationDate = $settings->Active_To;
if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $expirationDate)) {
    $expirationDate = Helper::convertDate($expirationDate);
}

?>

<div class="group">

    <p><?php echo 'Expiration date: ' . $expirationDate;?> (<?=$summary_sl_settings['Tier_Name']?>)</p>
</div>

<?php
    if ($client_admin) {
        echo '<button class="button right" id="add_service">+/- Service</button>';
    }
$restricted_users_array=array('user','data_entry_clerk');
$alowed_users_array=array('client_admin','db_admin','admin','approver','processor');
if(in_array($user_role,$alowed_users_array)){
echo '<div id="user_allowed" data-id="allowed"></div>';

?>
<br/><br/>

<div class="grid-view">
    <form method="post" action="/myaccount/updatecompanyservicelevel" id="company_service_level_form">
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
                <td id="set_tire_name">Current Service (<?php echo $summary_sl_settings['Tier_Name'];?>)</td>
                <td id="set_count_users"><?php echo $summary_sl_settings['Users_Count']+$summary_sl_settings['Additional_Users'];//echo $settings->service_level->Users_Count;?></td>
                <td id="set_count_projects"><?php echo $summary_sl_settings['Projects_Count']+$summary_sl_settings['Additional_Projects'];//echo $settings->service_level->Projects_Count;?></td>
                <td id="set_count_storage"><?php echo $summary_sl_settings['Storage_Count']+$summary_sl_settings['Additional_Storage'];//echo $settings->service_level->Storage_Count;?></td>
                <td id="set_base_fee"><?php echo number_format($summary_sl_settings['Base_Fee'] + $summary_sl_settings['Additional_Fee'], 2);?></td>
            </tr>

            <tr>

                <td id="add_tier_name">Change after add <br/>
                    <?php
                        if($pending_client_service_settings) echo '('.$pending_client_service_settings->Service_Level_ID.' '.date('m/d/Y',strtotime($pending_client_service_settings->Pending_Active_To)).')';
                    ?></td>
                <td id="set_add_users"><?php echo $pending_client_service_settings->Additional_Users ? $pending_client_service_settings->Additional_Users : 0; ?></td>
                <td id="set_add_projects"><?php echo isset($pending_client_service_settings->Additional_Projects) ? $pending_client_service_settings->Additional_Projects+1 : 0; ?></td>
                <td id="set_add_storage"><?php echo isset($pending_client_service_settings->Additional_Storage) ? $pending_client_service_settings->Additional_Storage+1 : 0;?></td>
                <td id="set_full_fee"><?php echo number_format($pending_client_service_settings->Fee,2);?></td>
            </tr>

            </tbody>
        </table>
        <?if ($delayed_client_service_settings) {?>
            <br/>
            <a href="#" id="delayed_settings_link">Be aware: you have delayed settings, that will be applied <?=$delayed_client_service_settings->Active_From?> </a>
        <?}?>
        <input type="hidden" id="sum_level_checksum" value="<?=$summary_sl_settings['Users_Count']+$summary_sl_settings['Additional_Users']+$summary_sl_settings['Additional_Projects']+$summary_sl_settings['Projects_Count']+$summary_sl_settings['Storage_Count']+$summary_sl_settings['Additional_Storage'];?>">
        <input type="hidden" id="sum_pending_level_cheksum" value="<?=$pending_client_service_settings->Additional_Users + $pending_client_service_settings->Additional_Projects + $pending_client_service_settings->Additional_Storage;?>">
        <?php echo CHtml::activeHiddenField($settings, "Service_Level_ID");?>
        <?php echo CHtml::activeHiddenField($settings, "Additional_Users");?>
        <?php echo CHtml::activeHiddenField($settings, "Additional_Projects");?>
        <?php echo CHtml::activeHiddenField($settings, "Additional_Storage");?>
    </form>
</div>
<div class="styled_radio_buttons_list" id="service_level_radio_buttons">
    <?php
        $bgColors = array('#00a33d', '#ff7f00', '#d00', '#0078c1');

        foreach ($serviceLevels as $serviceLevel) {
            $match = (in_array($serviceLevel->Service_Level_ID, $level_ids )) ? 1 : 0;

            echo '
            <h3 class="' . (($match) ? 'current_service_level' : '') . '" data-id="' . $serviceLevel->Service_Level_ID . '">' . $serviceLevel->Tier_Name . '
            <span class="styled_radio_button_right">
                    <span class="styled_radio_button_opt" ' . (($match) ? 'style="background-color: #000;"' : '') . '></span>
            </span></h3>
            <div>
            ' . $serviceLevel->Description . '
            </div>';
        }
    ?>
</div>
<?php }?>

<script type="text/javascript">
    $(document).ready(function() {
        $( "#service_level_radio_buttons" ).accordion({
            collapsible: true,
            active: false

            //show "Save" button on change of service level
            /*activate: function(event, ui) {
                $('#account_submit').show();
            }*/
        });

        //$('#account_submit').hide();
        //disabling the PC (4th) Tier level
        $('#service_level_radio_buttons h3.ui-accordion-header:eq( 3 )').unbind('click');


    });
</script>