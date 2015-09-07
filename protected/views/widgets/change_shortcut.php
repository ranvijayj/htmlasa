<div class="modal_box" id="change_shortcut" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <h2>Change Shortcut </h2>
    <label class="required" for="doc_to_user_email">
        Vendor's Shortcut
    </label>
    <input id="shortcut_to_update" class="txtfield" type="text" name="shortcut_to_update">
    <div class="errorMessage" style="display: none;">Shortcut must be unique.</div>
    <div class="center">
        <button id="update_shortcut" class="button" type="submit">Update</button>
        <button class="button hidemodal" type="submit" style="margin-left: 30px;">Close</button>
    </div>
</div>
