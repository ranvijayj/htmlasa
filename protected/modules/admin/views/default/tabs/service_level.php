<div id="company_name_row_service_level" class="cl_adm_company_name_row">
    <?if ($client) {?>
        <div id="sl_loaded_client_id" data-id="<?=$client->Client_ID?>" ><?='Client '.$client->Client_ID.' / '.$client->company->Company_Name?></div>
    <?} else {?>
        Client: Select a client
    <?}?>



</div>
<div id="session_client_id" data-id="<?=$client_id_to_rewiev?>"></div>
<div id="service_levels_company_info" class="grid-view">
<?if ($auto_loaded_data) {
    echo $auto_loaded_data;
}?>
</div>