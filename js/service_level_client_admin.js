function ServiceLevelClientAdmin(stripe_publish_key) {
    this.init(stripe_publish_key);
}

ServiceLevelClientAdmin.prototype = {
    /**
     * Service level Settings
     */
    serviceSettings:  null,

    /**
     * Payment type
     */
    paymentType: null,

    /**
     * Amount per month
     */
    amountPerMonth: null,

    /**
     * Amount to pay
     */
    amountToPay: null,

    /**
     * CVV2 code of Credit Card
     */
    cvv2: null,

    form_changed:false,
    levels_checksum : 0,

    /**
     * Initialize method
     */
    init: function (stripe_publish_key) {
        var self = this;

        setTimeout(function() {
            $('#service_levels_sidebar .sidebar_item').effect('highlight', 'slow');
        }, 150);

        $('#service_level_radio_buttons h3').click(function() {
            self.changeServiceLevel($(this));
        });

        $('#add_service').click(function() {
            show_modal_box('#add_service_level');
        });

        $('#add_service_submit').click(function (e){
            console.log("click event");
            //check and validate add_service_level_form
            if ($(this).hasClass('button')) {
                self.validateAddService().then(function(answer) {
                    if (answer=='validated') {
                        self.updateUserSettings();
                        close_modal_box('#add_service_level');

                    }
                });
            }

            //window.location = '/myaccount?tab=service';
        });

        $('#download_invoice').click(function (e) {
            e.preventDefault();
            var input = $('#service_levels_sidebar input[name=service_payment_type]:checked');
            var amount = input.data('amount');
            var url = '/myaccount/invoicetopayment?amount=' + amount;
            window.open(url, 'popup', 'toolbar=no, menubar=no, status=no, location=no, resizable=no, width=640, height=600');

        });



        $("#search_field").keydown(function() {
            return false;
        })

        //check Tier checkbox before doing any action
        $("#add_service_level .tier_level").change (function () {
            var checkbox = $(this);
            self.checkTierBoxBeforeDisable(checkbox);
        });

        //check "End Date" before saving
        $("#active_to_dropdown").change (function () {
            var dropdown = $(this);
            self.checkEndDateBeforeSave(dropdown);

        });

        //users number
        $('#service_add_users_input').keydown(function (e){
            e.preventDefault();
        })

        //additional project number
        $('#service_add_projects_input').keydown(function (e){
            e.preventDefault();
        })


        //additional storage value
        $('#add_service_level').on('keydown','#service_add_storage_input',function (e){
            e.preventDefault();
        });

        $('.spinner-text').keydown(function (e){
            e.preventDefault();
        });



        $('#add_service_level_form').submit(function(event) {
            event.preventDefault();
            self.updateSettingsForm();
        });


/*        $('#service_add_users_input').blur(function() {
            console.log('users blur event');
            var value = $(this).val();
            self.checkIntegerType($(this), value);
            self.checkForChanges($(this).data('value'),value);
        });*/
        $('#service_add_users_input').spinner({
            change: function( event, ui ) {
            },
            spin: function(event, ui) {
                self.checkIntegerType($(this), ui.value);
                self.checkForChanges($(this).data('value'),ui.value);
            }
        });



        /*$('#service_add_projects_input').blur(function() {
            console.log('blur event');
            var value = $(this).val();
            self.checkIntegerType($(this), value);
            self.checkForChanges($(this).data('value'),value);
        });*/
        $('#service_add_projects_input').spinner({
            change: function( event, ui ) {
            },
            spin: function(event, ui) {
                self.checkIntegerType($(this), ui.value);
                self.checkForChanges($(this).data('value'),ui.value);
            }
        });


        //array of available values for storage field
        var dlist = ['1','5','10','15','20','25','30','35','40','45','50','55','60','65','70','75','80','85','90','95','100',
            '105','110','115','120','125','130','135','140','145','150','155','160','165','170','175','180','185','190','195','200',
            '205','210','215','220','225','230','235','240','245','250','255','260','265','270','275','280','285','290','295','300',
            '305','310','315','320','325','330','335','340','345','350','355','360','365','370','375','380','385','390','395','400',
            '405','410','415','420','425','430','435','440','445','450','455','460','465','470','475','480','485','490','495','400',
            '505','510','515','520','525','530','535','540','545','550','555','560','565','570','575','580','585','590','595','500',
            '605','610','615','620','625','630','635','640','645','650','655','660','665','670','675','680','685','690','695','600',
            '705','710','715','720','725','730','735','740','745','750','755','760','765','770','775','780','785','790','795','700',
            '805','810','815','820','825','830','835','840','845','850','855','860','865','870','875','880','885','890','895','900',
            '905','910','915','920','925','930','935','940','945','950','955','960','965','970','975','980','985','990','995','1000',
        ];

        var initial_index = $('#input_for_storage_spin').data('value');
            initial_index = self.storageIndexByValue(initial_index,dlist);

        var min_index = $('#input_for_storage_spin').data('min-value');
            min_index = self.storageIndexByValue(min_index,dlist);

        $('#input_for_storage_spin').val (initial_index);
        $('#input_for_storage_spin').spinner({
            max: 200,
            min:min_index,
            incremental: true,
            change: function( event, ui ) {

            },
            spin: function(event, ui) {

            },
            create: function(){
                $(this).parent().append('<input class="spinner-text ui-spinner-input" name="ClientServiceSettings[Additional_Storage_Hidden]" id="service_add_storage_input" value="'+dlist[$(this).val()]+'">');
            },
            stop: function(event,ui) {

                var calculated_val  = dlist[$(this).val()];
                var initial_value = dlist[initial_index];

                $(this).siblings('.spinner-text').val(calculated_val);
                self.checkForChanges(initial_value,calculated_val);
            }
        });


        self.setServiceSettings();

        $('#account_submit').click(function() {
            var tab = $(this).attr('data');
            if (tab == 'tab7') {
                //$('#company_service_level_form').submit();
                self.applyPendingSettings();
            }
        });

        $('#submit_new_settings').click(function() {
            var amount = $('#service_payment_type1').data('amount');
            var prev_amount = $('#company_service_level_table tr:eq(6)').find('td:eq(4)').text();;

            if (amount < prev_amount) {

                var str = "Warning! Any reduction in service is reflected on the next month's fee that is due to be paid. Do you wand to continue?";

                show_deffered_dialog(str,500,'true').then(function (answer) {
                    if(answer == 'true'){
                        self.submitNewSettings();
                    }
                });
            } else {
                self.submitNewSettings();
            }
        });


        $('#submit_monthly_payment').click(function() {
            self.newMonthlyPayment();
            $('#cancel_monthly_payment').show();
        });

        $('#cancel_monthly_payment').click(function() {
            //monthes to null
            $('#service_levels_sidebar input[name=service_payment_type_mon]:checked').val();
            window.location = 'myaccount?tab=service';
            $('#cancel_monthly_payment').hide();

        });

        if ($('#upload_service_payment').attr('id')) {
            new AjaxUpload('#upload_service_payment', {
                action: '/myaccount/uploadservicepayment',
                onSubmit : function(file, ext) {
                    // check extension of the file
                    if (!(ext && /^(jpg|jpeg|bmp|gif|png|tiff|tif|pdf)$/.test(ext))) {
                        show_alert("Invalid extension of the file!");
                        return false;
                    }

                    show_alert("Uploading...");

                    // lock button
                    this.disable();
                },
                onComplete: function(file, response) {
                    if (response == '1') {
                        show_alert("Invalid extension of the file!");
                        this.enable();
                    } else if (response == '2') {
                        show_alert('The file size exceeds the maximum value!');
                        this.enable();
                    } else if (response == '3') {
                        show_alert('Error loading file! Try again.');
                        this.enable();
                    } else if (response == '4') {
                        show_alert('Invalid filename!');
                        this.enable();
                    } else {
                        show_alert('Payment has been sent to support! You will be notified when payment is confirmed.', 400);
                        this.enable();
                    }
                }
            });
        }

        if ($('#upload_service_payment_m').attr('id')) {
            new AjaxUpload('#upload_service_payment_m', {
                action: '/myaccount/uploadservicepayment',
                onSubmit : function(file, ext) {
                    // check extension of the file
                    if (!(ext && /^(jpg|jpeg|bmp|gif|png|tiff|tif|pdf)$/.test(ext))) {
                        show_alert("Invalid extension of the file!");
                        return false;
                    }

                    show_alert("Uploading...");

                    // lock button
                    this.disable();
                },
                onComplete: function(file, response) {
                    if (response == '1') {
                        show_alert("Invalid extension of the file!");
                        this.enable();
                    } else if (response == '2') {
                        show_alert('The file size exceeds the maximum value!');
                        this.enable();
                    } else if (response == '3') {
                        show_alert('Error loading file! Try again.');
                        this.enable();
                    } else if (response == '4') {
                        show_alert('Invalid filename!');
                        this.enable();
                    } else {
                        show_alert('Payment has been sent to support! You will be notified when payment is confirmed.', 400);
                        this.enable();
                    }
                }
            });
        }

        $('#submit_cvv2').click(function() {
            self.checkCVV2();
        });

        Stripe.setPublishableKey(stripe_publish_key);

        $('#cc_info_form').submit(function(event) {
            var $form = $(this);
            var use_last_cc = $form.find('#use_last_cc').val();


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
            self.logToHistory('102');
            $('#cc_info_form').submit();
        });

        $('#use_new_card').click(function() {
            setTimeout(function() {
                $('#credit_card_info #submit_cc_info_form').val('Pay $' + self.amountToPay);
                show_modal_box('#credit_card_info');
            }, 250);
        });

        $('#company_service_level_table').on('click','.cancel_pending',function (e) {
            e.preventDefault();
            $('.pending').remove();
            $('#account_submit').hide();
        });


    },

    /**
     * Calculates value's index in the values array
     * @param value
     * @param dlist
     */
    storageIndexByValue: function(initial_value,dlist) {
        for (var i = 0;i<dlist.length;i++) {
            if ( initial_value == dlist[i] || (initial_value > dlist[i-1] && initial_value < dlist[i]) ) {
                initial_value = i;
                break;
            }
        }
        return initial_value;
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
            value = 1;
        }

        if (id == 'service_add_storage_input') {
            if (value != 1 ) value = Math.ceil(value/5)*5;
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
     * Get service settings from server and set it to this.serviceSettings
     */
    setServiceSettings: function() {
        var self = this;
        $.ajax({
            url: "/myaccount/getservicelevelsettings",
            dataType: 'json',
            type: "POST",
            success: function(data) {
                self.serviceSettings = data;
            }
        });
    },

    /**
     * Change service level
     */
    changeServiceLevel: function(button) {
        //$('#service_level_radio_buttons h3 .styled_radio_button_opt').css('backgroundColor', '#fff');
        $('#service_level_radio_buttons h3').removeClass('current_service_level');
        //button.find('.styled_radio_button_opt').css('backgroundColor', '#000');
        button.addClass('current_service_level');
        //this.updateSettingsForm();
    },

    /**
     * Update settings form values
     */
    updateSettingsForm: function() {
        var self = this;
        var currentLevel = $('.current_service_level').data('id');
        var addUsers = $('#service_add_users_input').val();
        var addProjects = $('#service_add_projects_input').val();
        var addStorage = $('#service_add_storage_input').val();

        var total = parseFloat(this.serviceSettings[currentLevel].Base_Fee) + parseFloat(addUsers*this.serviceSettings[currentLevel].Additional_User_Fee) +
                    parseFloat(addProjects*this.serviceSettings[currentLevel].Additional_Project_Fee) + parseFloat(parseInt(addStorage)*this.serviceSettings[currentLevel].Additional_Storage_Fee);

       /*$('#set_tire_name').text(this.serviceSettings[currentLevel].Tier_Name);
       $('#set_count_users').text(this.serviceSettings[currentLevel].Users_Count);
       $('#set_count_projects').text(this.serviceSettings[currentLevel].Projects_Count);
       $('#set_count_storage').text(this.serviceSettings[currentLevel].Storage_Count);
       $('#set_base_fee').text(this.serviceSettings[currentLevel].Base_Fee);*/

       $('#set_pen_users').text(addUsers);
       $('#set_pen_projects').text(addProjects);
       $('#set_pen_storage').text(addStorage);
       $('#set_pen_full_fee').text(total.toFixed(2));

       $('#ClientServiceSettings_Service_Level_ID').val(currentLevel);
       $('#ClientServiceSettings_Additional_Users').val(addUsers);
       $('#ClientServiceSettings_Additional_Projects').val(addProjects);
       $('#ClientServiceSettings_Additional_Storage').val(addStorage);

        //we have to show payment form in the right side. After payment new settings will be applied
        //self.showPaymentForm();
    },

    showPaymentForm: function () {
        $.ajax({
            url: "/myaccount/getpaymentform",
            type: "POST",
            data: {
              requested_by: 'admin',  //can also be requested by time (if <10 days before expiration)
              tierlevel:tierlevel

            },
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
     * Submit new settings event
     */
    submitNewSettings: function() {
        var self = this;
        var input = $('#service_levels_sidebar input[name=service_payment_type]:checked');
        var paymentType = input.val();
        var amount = input.data('amount');
        if(amount>0.50){
            if (paymentType == 1) {
                //open window with downloading invoice
                var url = '/myaccount/invoicetopayment?amount=' + amount;
                window.open(url, 'popup', 'toolbar=no, menubar=no, status=no, location=no, resizable=no, width=640, height=600');


                // and update settings setting three additional days and disabling next prolongation
                self.updateUserSettingsManual();

            } else if (paymentType == 2) {
                //online payment
                this.amountToPay = amount;

                //if we use Stripe
                this.checkStripeCustomer();

                //if we use DoDirectPayment
                //self.checkCreditCard();

                //if we use ExpressCheckout
                //this.doExpressCheckoutPayment();

            } else if (paymentType == 3) {
                //re-calculate expiration date
                window.location = '/myaccount/applyservises';
            }
        } else {show_alert("Payment have to be more then 50 cents , you have - "+amount,600);}
    },

    /**
     * New monthly payment
     */
    newMonthlyPayment: function() {
        var self = this;
        //set flag that this is a monthly payment (not new settings)
        $('#credit_card_info input.monthly_payment').val('1');

        var inputMonth = $('#service_levels_sidebar input[name=service_payment_type_mon]:checked');
        var months = inputMonth.val();
        var value = $('#company_service_level_table tr:eq(1)').find('td:eq(4)').text();


        if (months) {
            var amount = (parseInt(months)*self.amountPerMonth).toFixed(2);
            if (self.paymentType == 1) {
                //open window with downloading invoice
                var url = '/myaccount/invoicetopayment?amount=' + amount;
                window.open(url, 'popup', 'toolbar=no, menubar=no, status=no, location=no, resizable=no, width=640, height=600');
            } else if (self.paymentType == 2) {
                //online payment
                self.amountToPay = amount;

                //if we use DoDirectPayment
                //self.checkCreditCard();

                //if we use ExpressCheckout
                //self.doExpressCheckoutPayment();

                //if we use Stripe
                self.checkStripeCustomer();
            }
        }

        var input = $('#service_levels_sidebar input[name=service_payment_type_m]:checked');
        var paymentType = input.val();
        if (paymentType) {
            self.paymentType = paymentType;
            //self.amountPerMonth = parseFloat(input.data('amount'));
            self.amountPerMonth = parseFloat(value);
            var htmlOptions = '<div class="row"><input type="radio" value="1" class="service_payment_type" id="service_payment_type_mon1" name="service_payment_type_mon" data-amount="215.8" checked="checked">'+
                '<label for="service_payment_type_mon1">1 month ($' + (self.amountPerMonth).toFixed(2) + ')</label>'+
                '</div>' +
                '<div class="row"><input type="radio" value="3" class="service_payment_type" id="service_payment_type_mon3" name="service_payment_type_mon">'+
                '<label for="service_payment_type_mon3">3 months ($' + (3*self.amountPerMonth).toFixed(2) + ')</label>'+
                '</div>' +
                '<div class="row"><input type="radio" value="6" class="service_payment_type" id="service_payment_type_mon6" name="service_payment_type_mon">'+
                '<label for="service_payment_type_mon6">6 months ($' + (6*self.amountPerMonth).toFixed(2) + ')</label>'+
                '</div>' +
                '<div class="row"><input type="radio" value="12" class="service_payment_type" id="service_payment_type_mon12" name="service_payment_type_mon">'+
                '<label for="service_payment_type_mon12">1 year ($' + (12*self.amountPerMonth).toFixed(2) + ')</label>' +
                '</div>';
            $('#monthly_payment').html(htmlOptions);
            $('#monthly_payment').parent().find('p').text('Please chose number of months:');
        }
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
        show_alert('Please wait...');
        $.ajax({
            url: "/myaccount/checkstripecustomer",
            type: "POST",
            dataType: 'json',
            success: function(data) {
                close_alert();
                if (data.success == 1) {
                    //if stripe customer exists
                    setTimeout(function() {
                        $('#ask_using_last_card #last_cc_info').html(data.ccInfo);
                        $('#ask_using_last_card #sum_to_pay').text('$' + self.amountToPay);

                        show_modal_box('#ask_using_last_card', 510);
                    }, 210);
                } else {
                    //if stripe customer doesn't exist
                    setTimeout(function() {
                        $('#credit_card_info #submit_cc_info_form').val('Pay $' + self.amountToPay);

                        show_modal_box('#credit_card_info');
                    }, 260);
                }
            }
        });
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


    /**
     * Execute online payment for DoDirectPayment
     */
     doDirectPayment: function() {
        var self = this;
        if (self.cvv2 && self.amountToPay) {
            $.ajax({
                url: "/paypal/directpayment",
                type: "POST",
                dataType: 'json',
                data: {
                    cvv2: self.cvv2,
                    amount: self.amountToPay
                },
                success: function(data) {
                    if (data.success == 1) {
                        window.location = '/myaccount?tab=service';
                    } else {
                        show_alert(data.message, 500);
                    }
                }
            });
        } else {
            show_alert("Invalid payment parameters. Please try again.");
        }
    },


    /**
     * Execute online payment for ExpressCheckout
     */
    doExpressCheckoutPayment: function() {
        var self = this;
        if (self.amountToPay) {
            show_alert("Please Wait...");
            $.ajax({
                url: "/paypal/buy",
                type: "POST",
                dataType: 'json',
                data: {
                    amount: self.amountToPay
                },
                success: function(data) {
                    if (data.success == 1) {
                        window.location = data.message;
                    } else {
                        show_alert(data.message, 500);
                    }
                }
            });
        } else {
            show_alert("Invalid payment parameters. Please try again.");
        }
    },

    checkTierBoxBeforeDisable: function (checkbox) {
        var self = this;
        if (!checkbox.attr('checked')) {
            var tier_level = checkbox.data('id');
            var tier_name = checkbox.attr('data-name');

            if ( checkbox.attr('data-checked')) {
                    $.ajax({
                        url: "/myaccount/checktierlevelusage",
                        data : {
                            tier_level : tier_level
                        },
                        type: "POST",
                        success: function(data) {

                            var str = 'You have '+data+' records in the "'+tier_name+'"  service. Removing this Tier will disable your access to your work. Are you sure you want to remove this Tier?';

                            show_deffered_dialog(str,500,'true').then(function(answer){
                                var ans = self.checkTiersMinimalValues(answer,checkbox);
                                return ans;
                            }).then(function (answer) {
                                if(answer == 'true'){
                                    checkbox.removeAttr('checked');

                                } else {
                                    checkbox.prop('checked', true);
                                }
                                self.updateSummaryString();
                            });
                        }
                    });
            } else {
                //self.addTiersDecValues(checkbox);
                self.updateSummaryString();
            }

        } else {
            //self.addTiersIncValues(checkbox);
            self.updateSummaryString();
        }
    },

    checkTiersMinimalValues: function (prev_answer,checkbox) {
        var self = this;
        var defer1 = $.Deferred();
        var error_string = '';

        if (prev_answer=='true') {

            var users_input = $('#service_add_users_input');
            var storage_input = $('#service_add_projects_input');
            var project_input = $('#service_add_storage_input');

            if (users_input.val()-checkbox.attr('data-users') < users_input.attr('min')) {
                 error_string = '\nCan\'t remove Tier Level: reduce users number first or add additional users ';
            } ;

            if (storage_input.val()-checkbox.attr('data-projects') < storage_input.attr('min')) {
                error_string += ' \n Can\'t remove Tier Level: reduce projects number first or add additional projects';
            };

            /*if (project_input.val()-checkbox.attr('data-storage') < project_input.attr('min')) {
                error_string += '\n Can\'t remove Tier Level: reduce used free space number first or add additional storage';
            };*/

            if (error_string=='') {
                defer1.resolve('true');
                //self.addTiersDecValues(checkbox);//reduce values
            } else {
                //var new_resolve = show_deffered_dialog(error_string,500,'true');
                show_alert2(error_string,500);
                defer1.resolve('false');
            }

        } else {
            defer1.resolve("false");
        }
        return defer1.promise();
    },

    addTiersIncValues: function (checkbox) {

        var users_input = $('#service_add_users_input');
        var storage_input = $('#service_add_projects_input');
        var project_input = $('#service_add_storage_input');
        users_input.val(parseInt(users_input.val())+parseInt(checkbox.attr('data-users')));
        storage_input.val(parseInt(storage_input.val())+parseInt(checkbox.attr('data-projects')));
        project_input.val(parseInt(project_input.val())+parseInt(checkbox.attr('data-storage')));

    },

    addTiersDecValues: function (checkbox) {

        var users_input = $('#service_add_users_input');
        var storage_input = $('#service_add_projects_input');
        var project_input = $('#service_add_storage_input');
        users_input.val(parseInt(users_input.val())-parseInt(checkbox.attr('data-users')));
        storage_input.val(parseInt(storage_input.val())-parseInt(checkbox.attr('data-projects')));
        project_input.val(parseInt(project_input.val())-parseInt(checkbox.attr('data-storage')));

    },

    checkEndDateBeforeSave: function (dropdown) {
        var self = this;
        console.log('selected index', dropdown.prop("selectedIndex"));
        if (dropdown.prop("selectedIndex")==1) {
            $('#add_service_submit').addClass('button').removeClass('not_active_button');
        }
        if (dropdown.prop("selectedIndex") >1) {

            var str = 'You have selected to pay for more than 1 month of service. Services purchased cannot be refunded, if a less time is required, once processed. Are you sure you want to continue?';
            show_deffered_dialog(str,500,'true').then(function (answer) {

                if(answer == 'true'){
                    //we need to write this fact to the history table
                    self.logToHistory('101');
                    $('#add_service_submit').addClass('button').removeClass('not_active_button');

                } else {
                    dropdown.prop("selectedIndex",0);
                }
            });
        }

    },

    updateUserSettingsManual: function () {
        $.ajax({
            url: "/myaccount/updateuserssettingsmanual",
            type: "POST",
            data : {three_days_add : '1'},

            success: function(data) {
                window.location = '/myaccount?tab=service';
            }
        });
    },

    updateUserSettings: function () {
        var selected = new Array();

        $(".tier_level:checked").each(function() {
            selected.push($(this).attr('data-id'));
        });


        $.ajax({
            url: "/myaccount/updateuserssettingsnew",
            type: "POST",
            //data : $('#add_service_level_form').serialize(),
            dataType:'json',
            data: {
                tiers : selected,
                active_to: $('#active_to_dropdown option:selected').text(),
                active_to_index: $('#active_to_dropdown option:selected').index(),
                users: $('#service_add_users_input').val(),
                projects: $('#service_add_projects_input').val(),
                storage: $('#service_add_storage_input').val()
            },

            success: function(data) {
                //$('.pending').remove();
                /*var table = $('#company_service_level_table');
                var str = '<tr class="pending"><td colspan="5">Pending Service (<a href="#" class="cancel_pending">Click to cancel</a>)</td></tr>';
                table.append(str);
                str = '<tr class="pending">' +
                    '<td>'+data.level_desc+'</td>' +
                    '<td>'+data.users+'</td>' +
                    '<td>'+data.projects+'</td>' +
                    '<td>'+data.storage+'</td>' +
                    '<td>'+data.fee_to_upgrade+'</td>' +
                    '</tr>';
                table.append(str);*/
                if (data) {
                    $('#add_tier_name').html(data.level_desc);
                    $('#set_add_users').html(data.users);
                    $('#set_add_projects').html(data.projects+1);
                    $('#set_add_storage').html(data.storage+1);
                    $('#set_full_fee').html(data.monthly_fee);

                    $('#account_submit').show();
                }


            }
        });
    },

    /**
     * checking storage changes and enabling save button
     * @param initial
     * @param final
     * @returns {boolean}
     */
    checkForChanges: function (initial,final) {

        var self = this;
        if (initial!=final) {
            self.form_changed = true;
            $('#add_service_submit').removeClass('not_active_button').addClass('button');
            return true;
        } else {
            $('#add_service_submit').removeClass('button').addClass('not_active_button');
            return false;
        }
    },

    validateAddService: function () {
        var self = this;
        var defer = $.Deferred();
        var total_checksum_before = parseInt($('#sum_level_checksum').val());

        var date_initial = $('#active_to_dropdown').attr('data-initial-value');
        var date_selected = $('#active_to_dropdown option:selected').text();
        var date_changed_flag = 0;
        if (date_selected != date_initial) {
            date_changed_flag = 100;
        }



        var add_checksum = parseInt($('#service_add_users_input').val()) + parseInt($('#service_add_projects_input').val()) + parseInt($('#service_add_storage_input').val()) + parseInt(date_changed_flag) + self.levels_checksum;

        var timeis_up = parseInt($('#timeis_up').val());

        console.log ('old settingschecksum',total_checksum_before);
        console.log ('new settingschecksum',parseInt(add_checksum));

        if (add_checksum < total_checksum_before && date_initial==date_selected && timeis_up==0)  {
            show_alert2("Settings can be reduced only within 10 days before the expiration",500);
            self.returnAddFormValues();
            defer.resolve('not validated');
        } else if (add_checksum < total_checksum_before  && date_initial==date_selected && timeis_up==1){
            show_alert2("Pleace select correct Active_To date for reducing service settings.",500);
            self.returnAddFormValues();
            defer.resolve('not validated');
        } else if (add_checksum == total_checksum_before) {
            defer.resolve('not validated');
        } else {
            defer.resolve('validated');
        }

    return defer;
    },

    returnAddFormValues: function () {
       //1) return tiers checkboxes
        $(".tier_level").each(function() {
            if( $(this).data('checked')=='checked') $(this).prop('checked', true);
        });

        //2) active to dropdown
        $('#active_to_dropdown option:eq(0)').prop('selected', true);

        //3) additional fields
        var input_var =$('#service_add_users_input');
        input_var.val(input_var.data('value'));

        input_var =$('#service_add_projects_input');
        input_var.val(input_var.data('value'));

        input_var =$('#service_add_storage_input');
        input_var.val(input_var.data('value'));
    },

    updateSummaryString: function () {
        var self = this;
        var sum_string = '';
        $(".tier_level").each(function() {

            if( $(this).prop('checked')) {
                if (sum_string.length == 0) {
                    sum_string += $(this).attr('data-name');
                } else {
                    sum_string += ', '+$(this).attr('data-name');
                }
            } else {
            }
        });
        var search_field = $('#search_field');

        search_field.val(sum_string);

        if (search_field.val() != search_field.data('value')) {
            $('#add_service_submit').addClass('button').removeClass('not_active_button');
            self.levels_checksum = 1;
        }

    },

    applyPendingSettings: function () {
        var self = this;
        $.ajax({
            url: "/myaccount/applypendingservice",
            type: "POST",
            success: function(data) {
                window.location = '/myaccount?tab=service';
            }
        });
    },

    logToHistory : function (action) {
        $.ajax({
            url: "/myaccount/logtohistory",
            type: "POST",
            //data : $('#add_service_level_form').serialize(),
            dataType:'json',
            data: {
                action : action
            },
            success: function(data) {
            }
        });
    }


 }