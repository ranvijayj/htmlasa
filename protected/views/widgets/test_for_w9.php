<div class="modal_box" id="test_for_w9" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <div style="margin-bottom: 20px; height: 60px; line-height: 60px;">
        <h2 style="display: inline">W9 On File:</h2>
        <span id="test_for_w9_result"></span>
    </div>
    <div style="clear: both;"></div>
    <?php if(!Yii::app()->user->id): ?>
    <div class="row">
        <label for="login_test_for_w9">
            Username <span class="red">*</span>:
        </label>
        <input id="login_test_for_w9" class="txtfield" type="text" name="login_test_for_w9">
    </div>
    <div class="row">
        <label for="password_test_for_w9">
            Password <span class="red">*</span>:
        </label>
        <input id="password_test_for_w9" class="txtfield" type="password" maxlength="45" name="password_test_for_w9">
        <div class="errorMessage hidden" id="password_test_for_w9_error">Incorrect username or password.</div>
    </div>
    <?php endif; ?>
    <div class="row">
        <label for="fed_id_test_for_w9">
            Fed ID (EIN):
        </label>
        <input id="fed_id_test_for_w9" class="txtfield" type="text" maxlength="45" name="fed_if_test_for_w9">
    </div>
    <div class="row">
        <label for="company_name_test_for_w9">
            Company Name:
        </label>
        <input id="company_name_test_for_w9" class="txtfield" type="text" maxlength="45" name="fed_if_test_for_w9">
    </div>
    <div class="row">
        <label for="address_test_for_w9">
            Address:
        </label>
        <input id="address_test_for_w9" class="txtfield" type="text" maxlength="45" name="fed_if_test_for_w9">
    </div>
    <input type="hidden" id="auth_user" value="<?php echo (Yii::app()->user->id) ? '1' : '0'; ?>">
    <div class="center">
        <button class="button" id="test_for_w9_submit">Test</button>
        <button class="button hidemodal" style="margin-left: 30px;">Cancel</button>
    </div>
</div>



