function VendorW9Upload(mode) {
    var self = this;
    this.init(mode);

}

VendorW9Upload.prototype = {

    mode: '',
    validFedId:null,
    existingFedId:null,
    fileName:'W9-Temporary.pdf',
    fileID:null,
    /**
     * Initialize method
     */

    init: function(mode) {
      var self = this;

      self.mode = mode;
        $('a.add_new_vendor').click(function (){
           self.showUploadDataEntry();
        });

    },

    /**
     * This function is used for showing Upload Dataentry page from Dataentry and create views
     */
    showUploadDataEntry: function () {
        var self = this;
        $.ajax({
            url: "/uploads/getadditionfieldsblock",
            data: {
                imgId: 555,
                mode:'dataentry'
            },
            async: false,
            type: "POST",
            success: function(msg) {
                if (msg) {
                    $('#additional_fields_block').html(msg);
                    show_modal_box('#additional_fields_block', 725, 50);
                    self.initAdditionalFieldsBlock();
                }
            }
        });
    },


    initAdditionalFieldsBlock: function() {
        var self = this;

        $('#add_fed_id').blur(function() {
            self.testFedID();
        });



        $('#add_com_name').blur(function() {
            var val = $(this).val();
            if (val.length>0) {

                self.testComName(val).then(function (result_array) {

                    var table = $("#companies_view_block_details table");
                    var str = '';
                    var comp_str_value;

                    table.html('');

                    if (result_array.length > 0) {
                        for (var i=0;i<result_array.length;i++){

                            comp_str_value = '<span class="company_name">'+result_array[i].Company_Name+'</span>'+'<br/>'+
                                '<span class="address_name">'+result_array[i].Address1+'</span> '+
                                '<span class="city_name">'+result_array[i].City+'</span> '+
                                '<span class="state_name">'+result_array[i].State+'</span> '+
                                '<span class="zip_name">'+result_array[i].ZIP+'</span> '+'<br/>'+
                                '<span class="fedid_name">Fed_ID: '+result_array[i].Company_Fed_ID+'</span> ';

                            str = '<tr data-id="'+i+'"><td>'+comp_str_value+'</td><td><button class="copy_company_data" data-id="'+i+'">Copy</button></td></tr>';
                            table.append(str);
                        }

                        //we are showing text if company with such fed_id already exists and we are showing link with possibility to choose instead

                        if ($("#fed_id_status").html() =='already exists') {
                            $('#comp_name_status').html('already exists').css('text-align','right').css('width','180px').css('color','#FF0000');
                        } else {
                            $('#comp_name_status').html('<a href="#" id="show_companies_list" style="color: #FF0000;">already exists('+result_array.length+')</a>').css('text-align','right').css('width','180px');
                        }



                    } else {
                        $('#comp_name_status').html();
                        $('#comp_name_status').html('new company').css('color', '#41B50B').css('text-align','left').css('width','180px');
                    }
                });

            }


        });

        $('#add_bussiness_name').blur(function () {
            self.validateBusName();
        })

        $('#add_street_adr').blur(function () {
            self.validateStreet();
        })

        $('#add_city').blur(function () {
            self.validateCity();
        })

        $('#add_street').blur(function () {
            self.validateState();
        })

        $('#add_zip').blur(function () {
            self.validateZip();
        })

        $('#additional_fields_block').on('click','#show_companies_list',function() {
            $('#companies_view_block').show();
        });

        $('#companies_view_block_details').on('click','.copy_company_data', function() {

            var tr = $(this).parent().parent();

            $('#add_com_name').val(tr.find('td:eq(0) span.company_name').text());
            $('#add_street_adr').val(tr.find('td:eq(0) span.address_name').text());
            $('#add_city').val(tr.find('td:eq(0) span.city_name').text());
            $('#add_street').val(tr.find('td:eq(0) span.state_name').text());
            $('#add_zip').val(tr.find('td:eq(0) span.zip_name').text());
        });


        $('#assign_fedid a').click(function () {
            $('#assign_fedid_popup').fadeIn();
        });

        $('.add_intern_number').click(function () {
            self.getTempNumber(2).then(function (number){
                $('#add_fed_id').val(number);
                $('#assign_fedid_popup').hide();
                self.testFedID();
            });
        });

        $('.add_temp_number').click(function () {
            self.getTempNumber(1).then(function (number){
                $('#add_fed_id').val(number);
                $('#assign_fedid_popup').hide();
                self.testFedID();
            });
        });

        $('#add_fields_ok_button').click(function() {

            var fed_id = $('#add_fed_id').val();
            var comp_name = $('#add_com_name').val();
            var bus_name = $('#add_bussiness_name').val();
            var tax_name = $('#listname').val();
            var street_adr = $('#add_street_adr').val();
            var city = $('#add_city').val();
            var state = $('#add_street').val();
            var add_zip = $('#add_zip').val();

            self.testFedID_simple();




            if (self.validFedId) {

                //self.checkFedId(fed_id);
                if (self.existingFedId) {
                    //$('#fed_id_status').text('already exists').css('color', '#f00');
                    $('#fed_id_status').html('<span style="text-align: right;width:180px;">already exists</span>').css('color', '#FF0000');

                    //$('#add_com_name').val('').attr('disabled', true);
                    $('#com_name_status').text('');
                } else {
                    $('#fed_id_status').text('new company').css('color', '#41B50B');
                    $('#add_com_name').attr('disabled', false);
                    if (comp_name == '') {
                        $('#com_name_status').text('required').css('color', '#f00');
                    } else {
                        $('#com_name_status').text('');
                    }
                }
            } else {
                $('#fed_id_status').text('invalid value, correct: xx-xxxxxxx').css('color', '#f00');
                $('#add_com_name').val('').attr('disabled', true);
                $('#com_name_status').text('');
                self.validFedId = false;
            }

            if (self.validFedId && (self.existingFedId || (!self.existingFedId && comp_name != ''))
                && self.validateBusName() && self.validateStreet() && self.validateCity() && self.validateState() && self.validateZip()
                )
                {

                self.handleFile(fed_id, comp_name,bus_name,tax_name,street_adr,city,state,add_zip);

            }
        });

        $('#add_fields_remove_button').click(function(event) {
            self.fileToDelete = self.fileToReview;
            self.deleteFile(event);
        });


        $('#w9_vendor_upload_link').click(function(event){
            event.stopPropagation();
            $('#fileupload_add_block').click();

        });


        $('#fileupload_add_block').fileupload({
            url: '/uploads/UploadStatus',
            dataType: 'json',

            done: function (e, data) {


                var filename = data.result.files[0].name;
                var file_id = data.result.files[0].path;
                self.fileName = filename;
                self.fileID = file_id;

                var iframe = $('#additional_fields_block_conteiner iframe').attr('src','/documents/PreviewFile?file_id='+file_id);
            }
        });



    },


    validateBusName : function () {
        var val = $('#add_bussiness_name').val();
        if (val.length > 45) {
            $('#add_bussiness_name').css('border','1px solid red');
            return false;
        } else {
            $('#add_bussiness_name').css('border','1px solid #8e8e8e');
            return true;
        }
    },

    validateStreet : function () {
        var val = $('#add_street_adr').val();
        if (val.length > 45) {
            $('#add_street_adr').css('border','1px solid red');
            return false;
        } else {
            $('#add_street_adr').css('border','1px solid #8e8e8e');
            return true;
        }
    },

     validateCity : function () {
            var val = $('#add_city').val();
            if (val.length > 45) {
                $('#add_city').css('border','1px solid red');
                return false;
            } else {
                $('#add_city').css('border','1px solid #8e8e8e');
                return true;
            }
     },

    validateState : function () {
        var val = $('#add_street').val();

        if (val.length > 4) {
            $('#add_street').css('border','1px solid red');
            return false;
        } else {
            $('#add_street').css('border','1px solid #8e8e8e');
            return true;
        }
    },

    validateZip : function () {
        var val = $('#add_zip').val();

        if (val.length > 15) {
            $('#add_zip').css('border','1px solid red');
            return false;
        } else {
            $('#add_zip').css('border','1px solid #8e8e8e');
            return true;
        }
    },

    handleFile: function(fed_id, com_name,bus_name,tax_name,street_adr,city,state,add_zip) {
        var self = this;
        $.ajax({
            url: "/uploads/HandleUploadedFile",
            dataType: 'json',
            data: {
                filename: self.fileName,
                file_id : self.fileID,
                fed_id: fed_id,
                com_name: com_name,
                bus_name: bus_name,
                tax_name: tax_name,
                street_adr: street_adr,
                city: city,
                state: state,
                zip:add_zip
            },
            type: "POST",
            success: function(data){

                //emulate click() to close popup
                $('#additional_fields_block img.hidemodal').click();
                //append uploaded vendor to PO vendors list
                $('#Pos_Vendor_ID').append('<option value="'+data.id+'">'+data.name+'</option>');
                //select appended
                $("#Pos_Vendor_ID option[value="+data.id+"]").attr("selected", "selected");
                //emulate change event
                $("#Pos_Vendor_ID").trigger("change");

                //append uploaded vendor to AP vendors list
                $('#Aps_Vendor_ID').append('<option value="'+data.id+'">'+data.name+'</option>');
                //select appended
                $("#Aps_Vendor_ID option[value="+data.id+"]").attr("selected", "selected");
                //emulate change event
                $("#Aps_Vendor_ID").trigger("change");


                //append uploaded vendor to Payment vendors list
                $('#Payments_Vendor_ID').append('<option value="'+data.id+'">'+data.name+'</option>');
                //select appended
                $("#Payments_Vendor_ID option[value="+data.id+"]").attr("selected", "selected");
                //emulate change event
                $("#Payments_Vendor_ID").trigger("change");

                //if it is from vendor mode
                if (self.mode == 'vendor_list_mode' ) {
                    window.location = '/vendor';
                }


            }
        });
    },

    testFedID : function () {
        var self = this;
        var fed_id_pattern = /^(\d{2}\-\d{7})|(\d{3}\-\d{2}\-\d{4})$/;
        var fed_id_temp_pattern = /^(IN[-]\d{6})|(T0[-]\d{7})$/;


        var add_fed_id = $('#add_fed_id').val();
        if (add_fed_id.length>0) {
            // Check Fed ID
            if (fed_id_pattern.test(add_fed_id) || fed_id_temp_pattern.test(add_fed_id)) {
                self.validFedId = true;
                self.checkFedId(add_fed_id);

                if (self.existingFedId) {
                    $('#fed_id_status').html('already exists').css('color', '#FF0000').css('text-align','right').css('width','180px');
                    $('#add_com_name').val(self.existingCompanyName);
                    $('#add_street_adr').val(self.exstAddress1);
                    $('#add_city').val(self.exstCity);
                    $('#add_street').val(self.exstState);
                    $('#add_zip').val(self.exstZip);
                    $('#com_name_status').text('');
                } else {
                    $('#fed_id_status').html('new company').css('color', '#41B50B').css('text-align','left').css('width','180px');
                    //$('#comp_name_status').html('new company').css('color', '#41B50B').css('text-align','left').css('width','180px');
                    $('#add_com_name').val('').attr('disabled', false);
                    $('#add_com_name').focus();
                    $('#add_com_name').val('');
                    $('#add_street_adr').val('');
                    $('#add_city').val('');
                    $('#add_street').val('');
                    $('#add_zip').val('');
                }

            } else {
                $('#fed_id_status').text('invalid value, correct: xx-xxxxxxx').css('color', '#f00');
                //$('#add_com_name').val('').attr('disabled', true);
                $('#com_name_status').text('');
                self.validFedId = false;
            }
        }


    },

    /**
     * Check Fed Id
     * @param add_fed_id
     */
    checkFedId: function(add_fed_id) {
        var self = this;
        $.ajax({
            url: "/uploads/checkfedid",
            data: {add_fed_id: add_fed_id},
            dataType:'json',
            async: false,
            type: "POST",
            success: function(msg) {
                if (msg != 0) {
                    self.existingFedId = true;
                    self.existingCompanyName = msg.Company_Name;
                    self.exstBusiness = msg.Business_Name;
                    self.exstTax = msg.Tax_Class;
                    self.exstAddress1 = msg.Address1;
                    self.exstCity = msg.City;
                    self.exstState = msg.State;
                    self.exstZip = msg.Zip;

                } else {
                    self.existingFedId = false;
                    self.existingCompanyName = '';
                }
            }
        });
    },

    testFedID_simple : function () { //without updating text fields
        var self = this;
        var fed_id_pattern = /^(\d{2}\-\d{7})|(\d{3}\-\d{2}\-\d{4})$/;
        var fed_id_temp_pattern = /^(IN[-]\d{7})|(T0[-]\d{7})$/;


        var add_fed_id = $('#add_fed_id').val();

        // Check Fed ID
        if (fed_id_pattern.test(add_fed_id) || fed_id_temp_pattern.test(add_fed_id)) {
            self.validFedId = true;
        } else {
            self.validFedId = false;
        }
    },

    testComName : function (name) {
        return new Promise (function (resolve,reject){
            var self = this;
            $.ajax({
                type:"POST",
                url: '/vendor/GetCompanyDataByName',
                data: {com_name:name},
                dataType: 'json',

                success: function (html) {
                    resolve(html);
                },

                error : function () {
                    reject();
                }

            });

        });
    },

    getTempNumber : function (type) {
        return new Promise (function (resolve,reject){
            var number = 0;
            $.ajax({
                type:"POST",
                url: '/w9/GetNextTempFedID',
                data: {fed_id_type:type},
                success: function (html) {
                    number = html;
                    console.log("afer ajax number is",number);
                    resolve(number);
                },

                error : function () {
                    reject();
                }
            });
        });

    }





}