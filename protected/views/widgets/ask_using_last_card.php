<div class="modal_box" id="ask_using_last_card" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <h2><h2><?=CustomMessages::getMessage(1);?> <span id="sum_to_pay"></span> ?</h2>
    <div id="last_cc_info">
        <input type="hidden" name="monthly_payment" class="monthly_payment" value="0">
    </div>
    <div class="center">

        <button class="button hidemodal" id="use_last_card" style="display: inline-block;">Use This Card</button>
        <button class="button hidemodal" id="use_new_card" style="margin-left: 30px;">Use Other Card</button>
    </div>
</div>