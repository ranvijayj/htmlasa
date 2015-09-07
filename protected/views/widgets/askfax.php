<div class="modal_box" id="askfaxbox" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <h2>Send Fax</h2>
    <label class="required" for="doc_to_user_fax">
        Enter fax number of the user
    </label>
    <input id="doc_to_user_fax" class="txtfield" type="text" name="doc_to_user_fax">
    <div class="errorMessage" style="display: none;">Fax is not a valid fax number.</div>
    <div class="center">
        <button id="send_doc_by_fax" class="button" type="submit">Send</button>
    </div>
</div>