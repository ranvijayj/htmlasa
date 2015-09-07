<div class="modal_box" id="usr_approval_block" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>



    <div id="client_admin_appr_value" class="grid-view">

        <h2>At least one Approver must have an Approval value of 100.</h2>
        <div id="ua_loaded_client_id" data-id="<?=$client->Client_ID?>" ><?='Client '.$client->Client_ID.' / '.$client->company->Company_Name?></div>
        <form method="post" action="/myaccount/updateUsersApprovalValues" id="appr_value_form" autocomplete="off">
            <input type="hidden" name="clientID" value="" id="client_id_input"/>
            <table class="items mbot0">
                <thead>
                <tr>
                    <th class="width160"><span>Name</span></th><th class="width160"><span>Email</span></th><th><span>Approval Value</span></th>
                </tr>
                </thead>
            </table>
            <div style="max-height: 400px; overflow: auto">
                <table class="items" id="client_admin_appr_value_table">
                    <?     $approvers_array = UsersClientList::getApproversArray(Yii::app()->user->clientID);
                    if (count($approvers_array)) {
                        foreach ($approvers_array as $item) {
                            $user = $item['user'];
                            $apr_value = $item['approval_value'];
                            echo '<tr id="user' . $user->User_ID  . '">';
                            echo '<td class="width160">' . CHtml::encode($user->person->First_Name . ' '  . $user->person->Last_Name) .  '</td><td class="width160"><span class="ov_hidden width160">' . CHtml::encode($user->person->Email) .  '</span></td><td><input class="input_in_grid appr_value_input" type="text" size="5" maxlength="3" name="users[' . $user->User_ID . ']" value="' . $apr_value .'"/></td>';
                            echo '</tr>';

                        }
                    } else {
                        echo '<tr id="user0">';
                        echo '<td clospan="3">Users were not found</td>';
                        echo '</tr>';
                    } ?>
                </table>
            </div>


            <button class="button" id="submit_usr_appr_form" style="float: right">Save</button>
        </form>


    </div>

</div>