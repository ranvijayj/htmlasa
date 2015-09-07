<h2>Service Level Settings</h2>
<div id="service_level_settings_block" class="grid-view">
    <?if (strtolower(Yii::app()->user->userLogin) =="admin") {?>
    <form method="post" action="/admin/default/updateservicelevelsettings" id="service_level_settings_form">
        <table class="items mbot0" id="service_level_settings_table">
            <thead>
            <tr>
                <th class="width80"><span>Tier Name</span></th>
                <th class="width40"><span># of Users</span></th>
                <th class="width40"><span># of Projects</span></th>
                <th class="width40"><span>Storage, GB</span></th>
                <th class="width40"><span>Base Fee, $/M</span></th>
                <th class="width40"><span>Add. User Fee, $/M</span></th>
                <th class="width40"><span>Add. Pr. Fee, $/M</span></th>
                <th class="width40"><span>Add. 5GB St. Fee, $/M</span></th>
                <th><span>Trial Period, days</span></th>
            </tr>
            </thead>
            <tbody>
            <?php
            for($i=0; $i<count($serviceLevelSettings); $i++) {
                ?>
                <tr>
                    <td><?php echo CHtml::activeTextField($serviceLevelSettings[$i], "[$i]Tier_Name", array('class' => 'input_in_grid'));?></td>
                    <td><?php echo CHtml::activeTextField($serviceLevelSettings[$i], "[$i]Users_Count", array('class' => 'input_in_grid qty_cell'));?></td>
                    <td><?php echo CHtml::activeTextField($serviceLevelSettings[$i], "[$i]Projects_Count", array('class' => 'input_in_grid qty_cell'));?></td>
                    <td><?php echo CHtml::activeTextField($serviceLevelSettings[$i], "[$i]Storage_Count", array('class' => 'input_in_grid qty_cell'));?></td>
                    <td><?php echo CHtml::activeTextField($serviceLevelSettings[$i], "[$i]Base_Fee", array('class' => 'input_in_grid dollar_fields'));?></td>
                    <td><?php echo CHtml::activeTextField($serviceLevelSettings[$i], "[$i]Additional_User_Fee", array('class' => 'input_in_grid dollar_fields'));?></td>
                    <td><?php echo CHtml::activeTextField($serviceLevelSettings[$i], "[$i]Additional_Project_Fee", array('class' => 'input_in_grid dollar_fields'));?></td>
                    <td><?php echo CHtml::activeTextField($serviceLevelSettings[$i], "[$i]Additional_Storage_Fee", array('class' => 'input_in_grid dollar_fields'));?></td>
                    <td><?php echo CHtml::activeTextField($serviceLevelSettings[$i], "[$i]Trial_Period", array('class' => 'input_in_grid qty_cell')) .
                                   CHtml::activeHiddenField($serviceLevelSettings[$i], "[$i]Service_Level_ID");?></td>
                </tr>
            <?php
            }
            ?>
            </tbody>
        </table>
    </form>
    <?}?>
</div>
