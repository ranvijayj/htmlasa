<div class="sidebar_item">
                <p class="fs15">
                    Service (<?php echo $summary_sl_settings['Tier_Name']; ?>) expires on <?php echo Helper::convertDate($client_service_settings->Active_To); ?>.
                    Total renewal cost $<?php echo number_format($summary_sl_settings['Base_Fee'] + $summary_sl_settings['Additional_Fee'], 2);?></td>.
                    Please renew by choosing a payment method:
                </p>
<div id="monthly_payment">
    <div class="row">
        <input type="radio" checked="checked" data-amount="<?php echo $client_service_settings->Fee;?>" name="service_payment_type_m" id="service_payment_type_m1" class="service_payment_type" value="1"/>
        <label for="service_payment_type_m1">Manual Payment</label>
        <div class="service_payment_type_descr">
            Download Invoice, pay it and upload payment document trought the system.
            If You have already had a payment doc. you can upload it  <a href="javascript:void(0);" id="upload_service_payment_m">here</a>.
        </div>
    </div>
    <div class="row">
        <input type="radio" name="service_payment_type_m" data-amount="<?php echo $client_service_settings->Fee;?>" id="service_payment_type_m2" class="service_payment_type" value="2" />
        <label for="service_payment_type_m2">Online Payment</label>
        <div class="service_payment_type_descr">
            Pay now using credit card info.
        </div>
    </div>
</div>
<div class="center">

    <button id="submit_monthly_payment" class="button">Continue</button>
    <button id="cancel_monthly_payment" class="button" style="display: none;">Cancel</button>

</div>
<div class="center">

</div>
</div>