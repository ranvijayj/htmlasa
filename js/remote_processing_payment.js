function RemoteProcessingPayment(stripe_publish_key) {
    this.init(stripe_publish_key);
}

RemoteProcessingPayment.prototype = {


    /**
     * Payment type
     */
    paymentType: null,


    /**
     * Amount to pay
     */
    amountToPay: null,

    rp_id :null,
    /**
     * CVV2 code of Credit Card
     */
    cvv2: null,

    /**
     * Initialize method
     */
    init: function (stripe_publish_key) {
        var self = this;

        /**
         *if we was redirected from procces page
         *pay dialog should be shown
         */

        self.checkForAutoPayDialog();

        $('.recountable').on('change',function(){

            var param0 =$('#analog_book_prize').data('analogprise');

            var param1 = $('#book_copies_count').val();
            var param2 = $('#book_pages_on_sheet option:selected').text();
            var param3 = $('#book_quality option:selected').text();

            var result =  param0 *param1 / param2;
            if (param3 == 'double sided') {

                result= result*0.75;
                //result.toFixed(2); not working
                result = Math.round(result*100)/100;
            } else {
                result= param0 *param1 / param2;
                result = Math.round(result*100)/100;
            }
            $('#analog_book_prize').attr('data-analogprise',result);
            $('#analog_book_prize').val('Pay $'+result);

        });

        $('#existing_exports').on('click','.rp_item', function (e) {
            e.preventDefault();
            self.amountToPay = $(this).data('prise');

            self.rp_id = $(this).attr('id');
            $('#rp_id').val(self.rp_id);
            self.checkStripeCustomer();
        });

        $('#analog_book_prize').on('click',function (e) {

            self.amountToPay = $(this).attr('data-analogprise');
            self.rp_id = $(this).attr('data-id');
            $('#rp_id').val(self.rp_id);



            //changing form action
            $("#cc_info_form").attr('action', 'stripe/executerpanalogpayment');
            $("#copies_count").val($('#book_copies_count').val());
            $("#pages_on_sheet").val($('#book_pages_on_sheet option:selected').text());
            $("#quality").val($('#book_quality option:selected').text());

            close_modal_box('#book_calulation_widget');

            self.checkStripeCustomer();
        });



        $('#submit_cvv2').click(function() {
            self.checkCVV2();
        });

        Stripe.setPublishableKey(stripe_publish_key);

        $('#cc_info_form').submit(function(event) {
            console.log ("inside submit");


            var $form = $(this);
            var use_last_cc = $form.find('#use_last_cc').val();
            console.log (use_last_cc);
            //self.amountToPay = $(this).data('price');
            console.log ('self.amountToPay',self.amountToPay);
            if (self.amountToPay) {
                $form.find('#amount_to_pay').val(self.amountToPay);
            } else {
                return false;
            }

            if (use_last_cc == 0) {
                // Disable the submit button to prevent repeated clicks
                var hasErrors = false;
                if (Stripe.card.validateCardNumber($form.find('#cc_num').val())) {
                    $form.find('#error_cc_num').hide();
                } else {
                    $form.find('#error_cc_num').show();
                    hasErrors = true;
                    console.log ('errors in form');
                }

                if (Stripe.card.validateCVC($form.find('#cc_cvc').val())) {
                    $form.find('#error_cc_cvc').hide();
                } else {
                    $form.find('#error_cc_cvc').show();
                    hasErrors = true;
                }

                if (Stripe.card.validateExpiry($form.find('#cc_exp_month').val(), $form.find('#cc_exp_year').val())) {
                    $form.find('#error_cc_exp_date').hide();
                } else {
                    $form.find('#error_cc_exp_date').show();
                    hasErrors = true;
                }

                if (!hasErrors) {
                    $form.find('#submit_cc_info_form').prop('disabled', true);
                    Stripe.card.createToken($form, self.stripeResponseHandler);
                }

                // Prevent the form from submitting with the default action
                return false;
            }
        });


        $('#use_last_card').click(function() {
            $('#cc_info_form #use_last_cc').val(1);
            $('#cc_info_form #rp_id').val(self.rp_id);


            console.log($('#use_last_cc').val());
            $('#cc_info_form').submit();
        });

        $('#use_new_card').click(function() {
            setTimeout(function() {
                $('#credit_card_info #submit_cc_info_form').val('Pay $' + self.amountToPay);
                console.log($('#use_last_cc').val());
                show_modal_box('#credit_card_info');
            }, 250);
        });
    },

    /**
     * Handle stripe response and check token
     * @param status
     * @param response
     */
    stripeResponseHandler: function(status, response) {
        var $form = $('#cc_info_form');

        if (response.error) {
            // Show the errors on the form
            $form.find('#payment_error').text(response.error.message).show();
            $form.find('#submit_cc_info_form').prop('disabled', true);
        } else {
            // response contains id and card, which contains additional card details
            var token = response.id;
            // Insert the token into the form so it gets submitted to the server
            $form.append($('<input type="hidden" name="stripeToken" />').val(token));
            // and submit
            $form.get(0).submit();
            close_modal_box('#credit_card_info');
        }
    },

    /**
     * Check integer Type of value
     */
    checkIntegerType: function(elem, value) {
        var id = elem.attr('id');

        value = parseInt(value);
        if (isNaN(value)) {
            value = 0;
        }

        if (id == 'service_add_storage_input') {
            value = Math.ceil(value/5)*5
        }

        elem.val(value);
    },

    /**
     * Check float Type of value
     */
    checkFloatType: function(elem, value) {
        value = parseFloat(value);
        if (isNaN(value)) {
            value = 0;
        }

        elem.val(value.toFixed(2));
    },




    /**
     * Execute online payment
     */
    checkCreditCard: function() {
        var self = this;
        $.ajax({
            url: "/myaccount/checkcreditcard",
            type: "POST",
            success: function(msg) {
                if (msg == 1) {
                    //if we use DoDirectPayment
                    show_modal_box('#askcvv2');
                } else {
                    show_alert("Please fill in and save Credit Card Information on Credit Card tab.", 600);
                }
            }
        });
    },

    /**
     * Execute online payment
     */
    checkStripeCustomer: function() {
        var self = this;
        console.log("!!!!!!!",self.amountToPay);
        if (self.amountToPay>0) {

            $.ajax({
                url: "/myaccount/checkstripecustomer",
                type: "POST",
                dataType: 'json',
                async:true,
                success: function(data) {

                    if (data.success == 1) {
                        //if stripe customer exists
            //            setTimeout(function() {

                            $('#ask_using_last_card #last_cc_info').html(data.ccInfo);
                            $('#ask_using_last_card #sum_to_pay').text('$' + self.amountToPay);
                            show_modal_box('#ask_using_last_card', 510);
                            $('#ask_using_last_card #rp_id').val(self.rp_id);
              //          }, 210);
                    } else {
                        //if stripe customer doesn't exist
                //        setTimeout(function() {
                            $('#credit_card_info #submit_cc_info_form').val('Pay $' + self.amountToPay);
                            show_modal_box('#credit_card_info');
                  //      }, 260);
                    }
                }
            });
        }
    },

    /**
     * Check CVV2 code
     */
    checkCVV2: function() {
        var self = this;
        var cvv2 = parseInt($('#askcvv2_input').val());
        if (isNaN(cvv2) || cvv2 == 0 || cvv2 == '' || cvv2 < 100 || cvv2 > 999) {
            $('#askcvv2 .errorMessage').show();
        } else {
            $('#askcvv2 .errorMessage').hide();
            this.cvv2 = cvv2;
            close_modal_box('#askcvv2');
            setTimeout(function() {
                show_alert("Please Wait...");
                self.doDirectPayment();
            }, 210);
        }
    },


    checkForAutoPayDialog: function() {
        var self = this;

        var show_or_not = $('#show_pay_dialog').data('prid');
        if (show_or_not !=0) {
            self.rp_id =show_or_not;

            //PROMISSES

            self.calculateDigitalPaymentSum(self.rp_id).then(function (response){
                self.checkStripeCustomer();
            });

        }
    },

    calculateDigitalPaymentSum:function (pr_id) {
        var self = this;
        var result = false;
        return new Promise (function (resolve,reject){
                $.ajax({
                    url: "/remoteprocessing/getbookpaysums",
                    type: "POST",
                    dataType: 'json',
                    data : {rp_id : pr_id},

                    success: function(data) {
                        if (data) {
                            self.amountToPay = data['pdf_prise'];
                            console.log("Inside ajax get book paysum",self.amountToPay);
                            resolve(self.amountToPay);

                        } else {
                            reject(Error("Error"));
                        }

                    },
                    error: function () {
                        reject(Error("Error"));
                    }
                });
        });

    }





 }