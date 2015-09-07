<div class="modal_box" id="find_user_docs" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <h2>Select User</h2>
    <label class="required" for="user_login_docs">
        Enter Login of the user you want to show
    </label>
    <input id="user_login_docs" class="txtfield" type="text" name="user_login_docs">
    <div class="center">
        <input id="find_user_for_docs" class="button" type="submit" value="Search">
    </div>
    <div id="users_searsh_res_box_for_docs" style="margin-top: 30px; display: none;">
    </div>
</div>