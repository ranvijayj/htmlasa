<div class="modal_box" id="askemailbox" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <h2>Send Email</h2>
    <label class="required" for="doc_to_user_email">
        Enter email of the user
    </label>
    <input id="doc_to_user_email" class="txtfield" type="text" name="doc_to_user_email">
    <div class="errorMessage" style="display: none;">Email is not a valid email address.</div>
    <div class="center">
        <button id="send_doc_by_email" class="button" type="submit">Send</button>
    </div>
</div>
