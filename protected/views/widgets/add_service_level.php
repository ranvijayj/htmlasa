<div class="modal_box" id="add_service_level" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt='' class="hidemodal cancelbutton"/>
    <h1>+/- Service</h1>
    <?php
        $form=$this->beginWidget('CActiveForm', array (
        'id'=>'add_service_level_form',
    )); ?>
<div style="float: left;">
    <div class="row">
        <label for="service_names_dropdown"><span class="red">*</span> Tiers Selected</label><br/><br/>

        <?php
        $items =  ServiceLevelSettings::getServiceLevelsOptionsList();
        $this->renderPartial('application.views.widgets.dropdown_search_style_txtfield', array(
            'items'=> $items,
            'level_ids'=>$level_ids,
            'summary_sl_settings'=>$summary_sl_settings
        ));
        ?>

    </div>

    <div class="row">
        <label for="active_to_dropdown"><span class="red">*</span>End Date (mm/dd/YYYY)</label>
        <?php echo $form->dropDownList($client_service_settings, 'Active_To', ServiceLevelSettings::getNextActiveToList( $client_service_settings->Active_To),
            array(
                'id'=>'active_to_dropdown',
                'class'=>'txtfield',
                'data-initial-value'=>date('m/d/Y',strtotime($client_service_settings->Active_To))
            ));?>
    </div>
    <div class="row">
        <label for="service_add_users_input"><span class="red">*</span> Users count (used <?=$summary_sl_settings['Used_Users']?> of <?=$client_service_settings->Additional_Users+$summary_sl_settings['Users_Count']?> allowed )
            $<?=$serviceLevels[0]->Additional_User_Fee;?>
        </label>
        <?php
        $total_users = $client_service_settings->Additional_Users+$summary_sl_settings['Users_Count'];
        $total_users = ($total_users > $summary_sl_settings['Used_Users']) ?  $total_users : $summary_sl_settings['Used_Users']; //value should not be less than minimum
        echo $form->textField($client_service_settings,'Additional_Users', array(
            'id'=>'service_add_users_input',
            //'class'=>'txtfield',
            'min'=>$summary_sl_settings['Used_Users'],
            'max'=>ServiceLevelSettings::MAX_USERS,
            'value'=>$total_users,
            'data-value'=>$total_users

        )); ?>
    </div>
    <div class="row">
        <label for="service_add_projects_input">
            <span class="red">*</span> Projects (used <?=$summary_sl_settings['Used_Projects']?> of <?=$client_service_settings->Additional_Projects+$summary_sl_settings['Projects_Count']?> allowed )
            $<?=$serviceLevels[0]->Additional_Project_Fee;?>
        </label>
        <?php
        $total_projects = $client_service_settings->Additional_Projects+$summary_sl_settings['Projects_Count'];
        $total_projects = ($total_projects > $summary_sl_settings['Used_Projects'] ) ? $total_projects : $summary_sl_settings['Used_Projects'];
        echo $form->textField($client_service_settings,'Additional_Projects', array(
            'id'=>'service_add_projects_input',
            //'class'=>'txtfield',
            'min'=>$summary_sl_settings['Used_Projects'],
            'max'=>ServiceLevelSettings::MAX_PROJECTS,
            'value'=>$total_projects,
            'data-value'=>$total_projects
        )); ?>
    </div>
    <div class="row">
        <? $used_space = number_format(Images::getUsedStorage(Yii::app()->user->clientID),2);?>
        <label for="service_add_storage_input">
            <span class="red">*</span> Additional Storage <br/> (used <?=$used_space; ?> of <?=$client_service_settings->Additional_Storage + $summary_sl_settings['Storage_Count']?> GB allowed )
            $<?=$serviceLevels[0]->Additional_Storage_Fee;?>
        </label>
        <?php
        $total_storage = $client_service_settings->Additional_Storage + $summary_sl_settings['Storage_Count'];
        //$total_storage = 10;
        //min value calculation
        $min_storage = ceil($used_space);
        //$min_storage = ceil(4);

        ?>

        <input type="text" id="input_for_storage_spin" style="display: none;" data-min-value="<?=$min_storage?>" data-value="<?=$total_storage?>" data-usedstorage="<?=number_format(Images::getUsedStorage(Yii::app()->user->clientID),2)?>" value="0" >
    </div>
</div>
    <div class="center">
        <br/><br/>
        <button id="add_service_submit" class="not_active_button" style="margin: 10px;">Submit</button>
    </div>
    <?php $this->endWidget();?>
</div>