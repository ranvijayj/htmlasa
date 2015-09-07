/**
 *
 * @param mode
 * @constructor
 */
function VendorReUpload(mode) {
    var self = this;
    this.init(mode);

}

VendorReUpload.prototype = {
    mode: '',
    validFedId:null,
    existingFedId:null,
    fileName:null,
    fileID:null,
    fed_id:null,
    doc_id : null,/**
     * Initialize method
     */

    init: function(mode) {
        var self = this;

        self.mode = mode;



        $('a.change_vendor').click(function (){

            self.fed_id = $(this).data('fed_id');//links from vendor details got it
            self.doc_id = $(this).data('doc_id');//links from vendor details got it
            self.mode = $(this).data('mode');
            self.showUploadDataEntry();
        });

    },
    can_be_temp:null,



    /**
     * This function is used for showing Upload Dataentry page from Dataentry and create views
     */
    showUploadDataEntry: function () {
        var self = this;
        $.ajax({
            url: "/uploads/getadditionfieldsblock",
            data: {
                imgId: 555,
                mode:'dataentry',
                fed_id : self.fed_id,
                doc_id : self.doc_id
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

        $('#add_fields_remove_button').hide();



        $('#add_fed_id').blur(function() {
            self.can_be_temp = $('#add_fed_id').data('can-be-temp');
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
                        $('#comp_name_status').html('FedID is unique').css('color', '#41B50B').css('text-align','left').css('width','180px');
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

            self.testFedID();

            if (self.validFedId) {

                    $('#fed_id_status').text('new company').css('color', '#41B50B');
                    $('#add_com_name').attr('disabled', false);
                    if (comp_name == '') {
                        $('#com_name_status').text('required').css('color', '#f00');
                    } else {
                        $('#com_name_status').text('');
                    }

                    //self.fileName = $('#current_file_name').val();
                    self.updateVendor(fed_id, comp_name,bus_name,tax_name,street_adr,city,state,add_zip);

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

                var iframe = $('#additional_fields_block_conteiner iframe');
                iframe.attr('src','/documents/PreviewFile?file_id='+file_id+'&approved=0');
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

    updateVendor: function(fed_id, com_name,bus_name,tax_name,street_adr,city,state,add_zip) {
        var self = this;
        $.ajax({
            url: "/vendor/updatevendor",
            dataType: 'json',
            data: {
                filename: self.fileName,
                file_id : self.fileID,
                old_fed_id: $('#add_fed_id').data('fed_id'),
                new_fed_id: fed_id,
                w9_doc_id: $('#w9_doc_id').val(),
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

                window.location = '/vendor/detail';
            }
        });
    },

    testFedID : function () {
        var self = this;

        var fed_id_pattern = /^(\d{2}\-\d{7})|(\d{3}\-\d{2}\-\d{4})$/;
        var add_fed_id = $('#add_fed_id').val();

        if (add_fed_id.length>0 ) {
            // Check Fed ID

            if (fed_id_pattern.test(add_fed_id) ) {
                self.validFedId = true;
                self.checkFedId(add_fed_id);//check already exists or not

                if (self.existingFedId && self.mode!='no_image_mode') {
                    self.validFedId = false;
                    $('#fed_id_status').html('Please assign unique Fed_ID').css('color', '#f00').css('text-align','right').css('width','180px');
                } else {
                    $('#fed_id_status').html('FedID is unique').css('color', '#41B50B').css('text-align','left').css('width','180px');
                    self.validFedId = true;

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
    }



}