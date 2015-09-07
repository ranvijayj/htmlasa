function ServiceLevelAdmin() {
    this.init();
    this.initCompaniesList();

    $('#admin_submit').click(function() {
        if ($(this).attr('data') == 'tab10' && $(this).hasClass('button') ) {
            $('#company_service_level_form').submit();
        } else if ( $(this).attr('data') == 'tab11' ) {
            $('#service_level_settings_form').submit();
        }
    });
}

ServiceLevelAdmin.prototype = {
    /**
     * Timeout ul
     */
    timeout: false,

    /**
     * Click timeout
     */
    timeoutClick: false,


    /**
     * Client Id
     */
    clientID: null,

    /**
     * Company name
     */
    companyName: null,

    /**
     * Service level Settings
     */
    serviceSettings:  null,

    /**
     * DatePicker Settings
     */
    dpSettings: {
        dateFormat: "mm/dd/yy"
    },

    /**
     * Initialize method
     */
    init: function () {
        var self = this;
        console.log('mode ',$('#user_mode').val());
        console.log('tab ',$('li a.active').attr('href'));
        if($('#user_mode').val()!='1' && $('li a.active').attr('href')!='#tab10' ) {
            $('#admin_submit').addClass('not_active_button').removeClass('button');
        }

        $('#ClientServiceSettings_Additional_Users, #ClientServiceSettings_Additional_Projects, #ClientServiceSettings_Additional_Storage').bind('keyup blur',function(){
                var node = $(this);
                node.val(node.val().replace(/[^1-9\s]/g,'') ); }
        );

        $("#ClientServiceSettings_Active_To").mask("99/99/9999");

        $('input.qty_cell').blur(function() {
            var value = $(this).val();
            self.checkIntegerType($(this), value);
        });

        $('input.dollar_fields').blur(function() {
            var value = $(this).val();
            self.checkFloatType($(this), value);
        });

        $('#company_name_input_service_levels').keydown(function() {
            clearTimeout(self.timeoutClick);
            self.timeoutClick = setTimeout(function() {
                self.updateClientsList();
                self.clientID = 0;
                self.companyName = '';
            }, 800);
        });

        self.setServiceSettings();
        self.initTabsClick();
        self.initInputChanges();

        $('#service_levels_company_info').on('change','.tier_level', function () {
            console.log('change');
            var checkbox = $(this);
            self.updateSummaryString();
        });


        self.initClientInfoBlock();

    },

    /**
     * Init companies list
     */
    initCompaniesList: function() {
        var self = this;

        $('#service_levels_sidebar #clients-grid-table-service-levels tbody tr').click(function() {
            $('#service_levels_sidebar #clients-grid-table-service-levels tbody tr').css('background', 'none');
            $(this).css('background-color', '#dFdDdD');
            var clientId = $(this).attr('id');
            clientId = clientId.slice(6);
            var companyName = $(this).find('td').first().text();
            if (clientId == 0) {
                self.clientID = 0;
                self.companyName = '';
                $('#company_name_row_service_level').text('Client: Select a client');
                $('#service_levels_company_info').html('');
            } else {
                self.showClientInfo(clientId, 'Client: ' + clientId + ' / ' + companyName);
                self.clientID = clientId;
                self.companyName = companyName;
            }
        });
    },

    /**
     * Init client info block
     */
    initClientInfoBlock: function() {
        var self = this;

        $('#service_levels_company_info input.qty_cell').blur(function() {
            var value = $(this).val();
            self.checkIntegerType($(this), value);
        });

        $('#service_levels_company_info input.dollar_fields').blur(function() {
            var value = $(this).val();
            self.checkFloatType($(this), value);
        });

        $('#company_service_level_form select').change(function() {
            //setTimeout(self.updateSettingsForm(), 30);
        });

        $('#company_service_level_form input').blur(function() {
         //   setTimeout(self.updateSettingsForm(), 30);
        });

        $('#ClientServiceSettings_Active_To').datepicker(self.dpSettings);

        $('#add_payment_date').datepicker(self.dpSettings);

        $('#submit_new_payment.button').click(function(e) {
            e.preventDefault();
            if ( $(this).hasClass('button') && self.checkAccess($(this).data('id') )) {
                self.validatePaymentFields().then(function (answer) {
                    if (answer=='true'){
                        self.allowEditing();
                    }
                });
            }
        });

        $("#search_field").keydown(function() {
            return false;
        })
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

        if (id == 'ClientServiceSettings_Additional_Storage') {
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
     * Updates clients list
     */
    updateClientsList: function () {
        var self = this;
        var companyName = $('#company_name_input_service_levels').val();
        $('#clients-grid-service-levels > div').scrollTop(0).prepend("<div class='loadinng_mask'></div>");
        $.ajax({
            url: "/admin/default/getfilteredclientslist",
            data: {companyName: companyName},
            type: "POST",
            success: function(msg) {
                $('#clients-grid-table-service-levels tbody').html(msg);
                $('#company_name_row_service_level').text('Client: Select a client');
                self.clientID = 0;
                self.companyName = '';
                $('#service_levels_company_info').html('');
                self.initCompaniesList();
                $('#clients-grid-service-levels > div .loadinng_mask').remove();
            }
        });
    },

    /**
     * Show client info and settings
     */
    showClientInfo: function(clientId, companyName) {
        var self = this;
        $('#service_levels_company_info').scrollTop(0).prepend("<div class='loadinng_mask_dark'></div>");
        $.ajax({
            url: "/admin/default/getcompanyservicelevelsettings",
            data: {clientId: clientId},
            type: "POST",
            success: function(msg) {
                $('#service_levels_company_info').html(msg);
                $('#company_name_row_service_level').text(companyName);
                self.initClientInfoBlock();
                $('#service_levels_company_info .loadinng_mask_dark').remove();
            }
        });
    },

    /**
     * Get service settings from server and set it to this.serviceSettings
     */
    setServiceSettings: function() {
        var self = this;
        $.ajax({
            url: "/admin/default/getservicelevelsettings",
            dataType: 'json',
            type: "POST",
            success: function(data) {
                self.serviceSettings = data;
            }
        });
    },

    /**
     * Update settings form values
     */
    updateSettingsForm: function() {
        var self = this;

        var base_fee = parseFloat($('#set_base_fee').html());

        var add_users = $('#ClientServiceSettings_Additional_Users');
        var add_users_price = parseFloat(add_users.val()*add_users.attr('data-fee'));
        console.log ("price 1",add_users_price);

        var add_projects = $('#ClientServiceSettings_Additional_Projects');
        var add_projects_price = parseFloat((add_projects.val()-1)*add_projects.attr('data-fee'));
        console.log ("price 2",add_projects_price);

        var add_storage = $('#ClientServiceSettings_Additional_Storage');
        var storage_index = self.calculateStorageIndex(add_storage.val());
        var add_storage_price = parseFloat(storage_index * add_storage.attr('data-fee'));
        console.log ("price 3",add_storage_price);

        /*var total = parseFloat(this.serviceSettings[currentLevel].Base_Fee) + parseFloat(addUsers*this.serviceSettings[currentLevel].Additional_User_Fee) +
            parseFloat(addProjects*this.serviceSettings[currentLevel].Additional_Project_Fee) + parseFloat(parseInt(addStorage/5)*this.serviceSettings[currentLevel].Additional_Storage_Fee);*/
        var add_total =  parseFloat(add_projects_price + add_storage_price + add_users_price);


        var total =  base_fee + add_total;



     /*  $('#set_count_users').text(this.serviceSettings[currentLevel].Users_Count);
       $('#set_count_projects').text(this.serviceSettings[currentLevel].Projects_Count);
       $('#set_count_storage').text(this.serviceSettings[currentLevel].Storage_Count);
       $('#set_base_fee').text(this.serviceSettings[currentLevel].Base_Fee);*/
       $('#set_full_fee').text(add_total.toFixed(2));
       $('#sum_sum_fee').text(total.toFixed(2));
    },


    /**
     * Allows editing servise settings for admin (DB Admin can do it by default)
     */
    allowEditing : function () {
        $('#admin_submit').addClass('button').removeClass('not_active_button');
        $('#search_field').removeAttr('disabled');
        $('#ClientServiceSettings_Additional_Users').removeAttr('disabled');
        $('#ClientServiceSettings_Additional_Projects').removeAttr('disabled');
        $('#ClientServiceSettings_Additional_Storage').removeAttr('disabled');
        $('#ClientServiceSettings_Active_To').removeAttr('disabled');
        $('.input_in_grid').css('background','white');
        $('.add_payment').css('background','yellow');
        $('#submit_new_payment').addClass('not_active_button').removeClass('button');

    },

    /**
     * Add new payment and update 'Active To' date
     * not used
     */
    addNewPayment: function() {
        var self = this;
        var amount = $('#add_payment_amount').val();
        var date = $('#add_payment_date').val();
        var number = $('#add_payment_number').val();

        if (amount != '' && amount > 0 && date != '') {
            $.ajax({
                url: "/admin/default/addcompanypayment",
                data: {
                    amount: amount,
                    date: date,
                    number: number,
                    clientID: self.clientID
                },
                type: "POST",
                dataType: 'json',
                success: function(msg) {

                    if (msg.Payment_Amount == amount) {
                        $('#admin_submit').addClass('button').removeClass('not_active_button');
                        $('#search_field').removeAttr('disabled');
                        $('#ClientServiceSettings_Additional_Users').removeAttr('disabled');
                        $('#ClientServiceSettings_Additional_Projects').removeAttr('disabled');
                        $('#ClientServiceSettings_Additional_Storage').removeAttr('disabled');
                        $('#ClientServiceSettings_Active_To').removeAttr('disabled');
                        $('.input_in_grid').css('background','white');
                        $('.add_payment').css('background','yellow');
                        $('#submit_new_payment').addClass('not_active_button').removeClass('button');

                        $('#service_levels_company_payments_list table.items tr:first').after('<tr><td class="width100">'+msg.Payment_Date+'</td><td>'+msg.Payment_Amount.toFixed(2)+'</td> </tr>');

                    }

                    //self.showClientInfo(self.clientID, 'Client: ' + self.clientID + ' / ' + self.companyName);
                }
            });
        }
    },

    initTabsClick: function () {
      $('.wrapper ul.tabs li a').click(function () {
          var href = $(this).attr('href');

          if (href=='#tab10' && $('#user_mode').val()!=1) {
              $('#admin_submit').addClass('not_active_button').removeClass('button');
          } else {
              $('#admin_submit').addClass('button').removeClass('not_active_button');
          }


      });
    },


    updateSummaryString: function () {
        var w9_exist =0;
        var w9_price = 0;

        var sum_users =0;
        var sum_projects=0;
        var sum_storage=0;
        var sum_fee = 0;
        var sum_string = '';
        var id_string = '';
        console.log('inside update');

        $(".tier_level").each(function() {
            if( $(this).prop('checked')) {

                if($(this).attr('data-id') == 1) {
                    w9_exist = 1;
                    w9_price = $(this).attr('data-fee');
                }

                if (sum_string.length == 0) {
                    sum_string += $(this).attr('data-name');
                    id_string += $(this).attr('data-id');
                } else {
                    sum_string += ', '+$(this).attr('data-name');
                    id_string += ','+$(this).attr('data-id');
                }

                sum_users = parseInt($(this).attr('data-users'));
                sum_projects = parseInt($(this).attr('data-projects'));
                sum_storage = parseInt($(this).attr('data-storage'));
                sum_fee += parseFloat($(this).attr('data-fee'));
            } else {
            }
        });

        console.log ('exist',w9_exist);
        console.log ('w9 price',w9_price);
        console.log ('all price',sum_fee);

        if (w9_exist == 1 && sum_fee > w9_price ) {sum_fee = sum_fee - w9_price;} //reduce price as w9 already present in every tier

        $('#search_field').val(sum_string);

        $('#search_field').attr('data-id',id_string);
        $('#ClientServiceSettings_Sevice_Level_ID').val(id_string);


        $('#set_count_users').html(sum_users);
        $('#set_count_projects').html(sum_projects);
        $('#set_count_storage').html(sum_storage);
        $('#set_base_fee').html(sum_fee.toFixed(2));

        //var full_fee = parseFloat($('#set_full_fee').html());
        //$('#set_full_fee').html(parseFloat(sum_fee.toFixed(2))+parseFloat(full_fee.toFixed(2)));

    },

    validatePaymentFields : function () {
        var self = this;

        var defer = $.Deferred();

        var amount = parseFloat($('#add_payment_amount').val());
        var date = $('#add_payment_date').val();

        var pen_amount = $('#set_pen_full_fee').html();
        if (pen_amount) {
            pen_amount = pen_amount.replace(/\,/g,''); //removing commas from number
            pen_amount = parseFloat(pen_amount);
        }



        if (amount==0 || isNaN(amount)) {
        //    $('#add_payment_amount').css('color','red');
            defer.resolve('false');
        }
        if (date=='' || isNaN(amount)) {
            //$('#add_payment_date').css('color','red');
            defer.resolve('false');
        } else {
            //$('#add_payment_date').css('color','auto');
        }

        if (amount != pen_amount && pen_amount>0 && amount!=0 && date) {
            //var str = "Payment amount does not match to client's pending settings amount! Pending client's settings will be removed and current settings will be prolongated on the sum "+ amount.toFixed(2) +" Are you sure to continue ? ";
            var str = "Payment amount does not match to client's pending settings amount! Are you sure to continue ?";
            show_def_dialog(str,500,'true').then(function (answer) {
                if(answer == 'true'){
                    $('#add_payment_amount').css('color','red');
                    $('#set_pen_full_fee').css('text-color','red');

                    defer.resolve('true')
                } else {
                    defer.resolve('false')
                }
            });
        } else { defer.resolve('true'); }

        return defer.promise();
    },

    initInputChanges: function () {
        var self = this;

        $('#service_levels_company_info').on('change','#ClientServiceSettings_Additional_Users',function () {
            var base = parseInt($('#set_count_users').html());
            var total = base + parseInt($(this).val());
            $('#sum_users_count').html(total);
            self.updateSettingsForm();

        });

        $('#service_levels_company_info').on('change','#ClientServiceSettings_Additional_Projects',function () {
            var base = parseInt($('#set_count_projects').html());
            var total = base + parseInt($(this).val());
            $('#sum_projects_count').html(total);
            self.updateSettingsForm();
        });

        $('#service_levels_company_info').on('change','#ClientServiceSettings_Additional_Storage',function () {
            var base = parseInt($('#set_count_storage').html());
            var total = base + parseInt($(this).val());
            $('#sum_storage_count').html(total);
            self.updateSettingsForm();
        });
    },

    /**
     * function allows to add payment only for dbadmin
     * other admins can add payment only if pending settings present in the system
     * @param user_mode
     * @returns {boolean}
     */
    checkAccess: function (user_mode) {

        var result= false;
        if (user_mode==1) {result = true;}
        if (user_mode==0 && $('.pending').length>0) {
            result = true;
        }
        return result;
    },

    calculateStorageIndex : function (value) {
        var index =0;
        if (value == 1) {
            index =0;
        } else {
            index = value/5;
        }
        return index;

    }



 }