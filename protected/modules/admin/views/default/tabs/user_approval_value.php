<div id="company_name_row_appr_value" style="color: #000000; font-size: 15px; margin-top: -20px;">
    <?if ($client) {?>
        <div id="ua_loaded_client_id" data-id="<?=$client->Client_ID?>" ><?='Client '.$client->Client_ID.' / '.$client->company->Company_Name?></div>
    <?} else {?>
        Client: Select a client
    <?}?>
</div>
<div id="client_admin_appr_value" class="grid-view">
    <form method="post" action="/admin/default/updateusersapprovalvalues" id="appr_value_form" autocomplete="off">
    <input type="hidden" name="clientID" value="" id="client_id_input"/>
    <table class="items mbot0">
        <thead>
        <tr>
            <th class="width160"><span>Name</span></th><th class="width160"><span>Email</span></th><th><span>Approval Value</span></th>
        </tr>
        </thead>
    </table>
    <div style="height: 400px; overflow: auto">
        <table class="items" id="client_admin_appr_value_table">
            <?if ($auto_loaded_data) {
                echo $auto_loaded_data;
            } else {?>

            <tbody>
                <tr id="user0"><td colspan="3">Select a client to populate this grid</td></tr>
            </tbody>
            <?}?>
        </table>
    </div>
    </form>
</div>
