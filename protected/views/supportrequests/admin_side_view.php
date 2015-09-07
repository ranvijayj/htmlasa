<?php
?>
<div class="request_side_view">
    <b>Created :</b> <?=$sup_req->Created?><br/>
    <b>Category:</b> <?=$sup_req->Problem_Category?><br/>
    <b>User message:</b> <?=$sup_req->User_Message?><br/><br/><br/>


    <b>User device OS: </b><?=$device->OS?><br/>
    <b>User device browser: </b><?=$device->Browser?><br/>
    <b>User device hash: </b> <div style="font-size: 12px;"> <?=$device->MOB_Hash?><br/></div>
    <b>Device last logged: </b><?=Helper::convertDateFromIntClient($device->Last_Logged)?><br/>
    <b>Device added: </b><?=$device->Created;?><br/>

    <br/><br/><br/>
    <b>User's questions:</b><br/><br/>
    <?
    $i=1;
    foreach ($users_questions as $question) {
    echo $i.') '.$question['Text'];//echo '('.$question['Hint'].')<br/>';
    echo '<div class="row" style="padding-left: 10px;margin-bottom:15px;margin-top: 9px; ">';
        echo '<input type="text" class="answer" name=answers['.$question['Question_ID'].'] autocomplete="off"
            data-uid="'.$user_id.'"
            data-question="'.$question['Question_ID'].'"
        >';
        echo '<br/>';
        $i++;
        echo '</div>';
    }
    ?>
    <br/><br/>
    <input type="checkbox" name="add_device_check_box" id="add_device_check_box"
           data-hash="<?=$device->MOB_Hash?>"
           data-uid="<?=$user_id?>"
            <?=$device->Super_Login ? 'checked' : ''; ?>
            <?=$device->Super_Login ? 'disabled' : ''; ?>
        > Allow login from this device<br/><br/>

    <input type="checkbox" name="UsersSettings_Use_Device_Checking" id="UsersSettings_Use_Device_Checking"
        <?=$users_settings->Use_Device_Checking ? 'checked' : ''; ?>
        data-login=<?=$sup_req->User_Login;?>
    > Use device checking for this user<br/><br/>

    <input type="checkbox" name="mark_request_as_solved" id="mark_request_as_solved" data-id="<?=$sup_req->Request_ID?>" > Mark current request as solved

</div>