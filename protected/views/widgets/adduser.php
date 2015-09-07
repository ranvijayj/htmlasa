<div class="modal_box" id="adduserbox" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <h2>Add User</h2>
    <label class="required">
        Enter email of the user you want to add
    </label>
    <input id="adduser_email" class="txtfield" type="text" name="email">
    <div class="errorMessage" style="display: none;">Email is not a valid email address.</div>
    <div class="center">
        <input id="find_user" class="button" type="submit" value="Search" name="yt0">
    </div>
    <div id="users_searsh_res_box" style="margin-top: 30px; display: none;"></div>
</div>