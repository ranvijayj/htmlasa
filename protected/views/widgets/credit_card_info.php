<div class="modal_box" id="credit_card_info" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <h2>Credit Card Info:</h2>
    <form action="<?php echo Yii::app()->createUrl('stripe/executepayment'); ?>" method="post" id="cc_info_form">
        <input type="hidden" name="monthly_payment" class="monthly_payment" value="0">
        <div class="row">
            <label for="cc_num">
                Card Number  <span class="required">*</span>
            </label>
            <input type="text" id="cc_num" size="20" data-stripe="number" class="txtfield"/>
            <div class="errorMessage hidden" id="error_cc_num">Invalid Card Number</div>
        </div>
        <div class="row">
            <label for="cc_cvc">
                CVC  <span class="required">*</span>
            </label>
            <input type="text" id="cc_cvc" size="4" data-stripe="cvc" class="txtfield"/>
            <div class="errorMessage hidden" id="error_cc_cvc">Invalid CVC</div>
        </div>
        <div class="row">
            <div style="width: 115px; float: left;">
                <label class="required" for="cc_exp_month">Exp. Month (MM) <span class="required">*</span></label>
                <input type="text" size="2" id="cc_exp_month" data-stripe="exp-month" style="width: 100px;" class="txtfield">
                <div class="errorMessage hidden" id="error_cc_exp_date">Invalid Expiration Date</div>
            </div>
            <div style="width: 140px; float: right;">
                <label class="required" for="cc_exp_year">Exp. Year (YYYY) <span class="required">*</span></label>
                <input type="text" size="4" id="cc_exp_year" data-stripe="exp-year" style="width: 130px;" class="txtfield">
            </div>
            <div style="clear: both;"></div>
        </div>
        <div class="row">
            <input type="checkbox" name="Automatic_CC_Charge" checked="checked">
            <label for="Automatic_CC_Charge" "> use this credit card for monthly automatic payments  </label>
        </div>
        <div class="row">
            <div class="errorMessage hidden" id="payment_error">  </div>
        </div>
        <input type="hidden" id="use_last_cc" name="use_last_cc" value="0">
        <input type="hidden" id="amount_to_pay" name="amount_to_pay" value="">
        <br/>
        <div class="center" >
            <input class="button" id="submit_cc_info_form" type="submit" value="Pay">
        </div>
    </form>
</div>