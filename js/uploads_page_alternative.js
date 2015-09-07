function UploadsPageAlternative() {
    var self = this;
    this.init();
    this.initAdditionalFieldsPointer();
    var lastUpload = $('#last_upload_files').text();
    self.lastUploadFiles = lastUpload.split('%');
    var currentUpload = $('#current_upload_files').text();
    self.currentUploadFiles = currentUpload.split('%');
}

 UploadsPageAlternative.prototype = {
    /*
     * Last uploaded files
     */
    lastUploadFiles: [],

    /**
     * Current upload files
     */
    currentUploadFiles: [],

    /**
     * Spec. if is valid Fed ID
     */
    validFedId: true,

    /**
     * Spec. if Fed ID exists
     */
    existingFedId: false,

    /**
     * Company name and other dataentry values if Fed ID exists
     */
    existingCompanyName: '',
    existingCompanyName:'',
    exstBusiness:'',
    exstTax :'',
    exstAddress1 :'',
    exstCity :'',
    exstState :'',
    exstZip:'',



    /**
     * Spec. if is valid upload form
     */
    validForm: false,

    /**
     * File to delete
     */
    fileToDelete: 0,

    /**
     * File to review with additional fields
     */
    fileToReview: 0,

     /**
      * Dialog box action
      */
    dialogAction: null,


    uploaded_files_count:null,
    uploaded_files_size:null,


    /**
     * Initialize method
     */
    init: function() {
        var self = this;
        $('#file_upload_button').click(function () {
            $('#fileupload').trigger('click');
        });

        self.initUploadingRoutines();
        self.reinitClickEvents();

        $('.disable_uploading').live('click',function(event) {
            var message = $(this).data('message');
            var type = $(this).data('type');
            if (type == 2) {
                show_alert(message, 400);
            } else if (type == 1) {
                $('#dialogmodal a').attr('href', '/myaccount?tab=service');
                self.dialogAction = 'full_storage';
                show_dialog(message, 540);
            }

        });

        $('#current_upload_grid').on('click','td.uploaded_file_name',function(event) {
            var row = $(this).parent();
            var addPointer = row.find('.additional_field_pointer').attr('data');

            console.log('addPointer',addPointer);

            var imgId = row.attr('id');

            imgId = imgId.slice(5);
            if (addPointer>=0) {
                self.fileToReview =  imgId;
                self.showAdditionalFieldsBlock();
            } else {
                self.displayFile(imgId, 'current_uploads');
            }
        });

        $('#current_upload_grid').on('click','.additional_field_pointer',function(event) {
            var row = $(this).parent().parent();
            var addPointer = row.find('.additional_field_pointer').attr('data');

            var imgId = row.attr('id');

            imgId = imgId.slice(5);
            if (addPointer>=0) {
                self.fileToReview =  imgId;
                self.showAdditionalFieldsBlock();
            } else {
                self.displayFile(imgId, 'current_uploads');
            }
        });

        $('#last_upload_grid td').live('click',function() {
            var docId = $(this).data('id');
            self.displayFile(docId, 'last_uploads');
        });

        $('#submit_uploaded_file').click(function() {
                if ($(this).hasClass('button')) {
                    var pb=new ProgressBar('savefile');
                    pb.setCaption("Saving...");
                    pb.indeterminate();
                    $("#progress_bar_container").show();

                    self.checkForm();

                }
        });

        $("#current_upload_grid").on('click','td.dropdown_cell_upload',function(event) {
            event.stopPropagation();
            $("#users-to-approve-grid td.dropdown_cell_upload ul").slideUp(150);
            $(this).find('ul').slideDown(150);
        });

        $("#current_upload_grid").on ('mouseout','td.dropdown_cell_upload ul',function(event) {
            var ul = $(this);
            self.timeout = setTimeout(function() {
                ul.slideUp(150);
            }, 100);
        });

        $("#current_upload_grid").on('mouseover','td.dropdown_cell_upload ul',function(event) {
            if (self.timeout) {
                clearTimeout(self.timeout);
                self.timeout = false;
            }
        });

        $("#current_upload_grid").on('click','td.dropdown_cell_upload ul li',function(event) {
            event.stopPropagation();
            var text = $(this).data('docType');
            var cell = $(this).parent().parent().parent();
            var dropdown_cell_value = cell.find('.dropdown_cell_value');
            dropdown_cell_value.text(text);
            var row = cell.parent();
            var imgId = row.attr('id');
            imgId = imgId.slice(5);

            self.sendDocumentType(imgId, text);

            var add_td = row.find('td.additions_cell');
            // Check document file and open additional fields
            if (text == 'W9') {
                add_td.html('<span class="additional_field_pointer" style="color: #f00; cursor: pointer;" data="' + imgId + '">REQUIRED</span>');
                self.initAdditionalFieldsPointer('#' + row.attr('id'));
            } else {
                add_td.html('');
            }

            $("#current_upload_grid td.dropdown_cell_upload ul").slideUp(150);
        });

        $('body').live('click',function(event) {
            $("#current_upload_grid td.dropdown_cell_upload ul").slideUp(150);
        });


        $('#current_upload_grid').on('click','.delete_file',function() {
            var id = $(this).attr('id');
            id = id.slice(12);
            self.fileToDelete = id;
            self.dialogAction = 'deletion';
            show_dialog('Are you sure you want to delete this file?', 400);
        });

        $('#dialogmodal a').click(function(event) {
            if (self.dialogAction == 'deletion') {
                self.deleteFile(event);
            }
        });

        $('#delete_uploaded_file').click(function(event) {
                self.dialogAction == 'deletion';
                self.deleteFile(event);

        });

        $('#clear_upload_session').click(function() {
            self.fileToDelete = 'clear';
            self.dialogAction = 'deletion';
            show_dialog('Are you sure you want to clear upload session?', 450);
        });
    },


    /**
     * Initialize method for additional fields pointer
     */
    initAdditionalFieldsPointer: function(rowId) {
        var self = this;

        if (rowId) {
            $(rowId + ' .additions_cell .additional_field_pointer').click(function() {
                var imgId = $(this).attr('data');
                self.fileToReview =  imgId;
                self.showAdditionalFieldsBlock();
            });
        } else {
            $('.additions_cell .additional_field_pointer').click(function() {
                var imgId = $(this).attr('data');
                self.fileToReview =  imgId;
                self.showAdditionalFieldsBlock();
            });
        }
    },

    /**
     * Initialize details block
     */
    initDetailsBlock: function() {
        var image_view = new DocumentView('#image_view_block', '#file_detail_block_conteiner', '#file_detail_block', 525, 76, 1000);
        /*
         var details_block_view = new DocumentView('#preview_block', '#file_detail_block_conteiner', '#file_detail_block', 525, 76, 20);
        $('#close_detail_block_conteiner').click(function() {
            if (details_block_view.fullSize) {
                var block = $('.fullsize_button').parent().parent().parent();
                block.appendTo('#preview_block');
                block.css('position', 'relative');
                block.css('z-index', '20');
                block.css('height', '100%');
                $('#file_detail_block').css('height', '525px');
                $("html, body").css('overflow', 'auto');
                details_block_view.fullSize = false;
                setTimeout(function() {
                    $('#preview_block').hide('fold', {}, 700);
                }, 200);
            } else {
                $('#preview_block').hide('fold', {}, 700);
            }
        });
        */
    },

    /**
     * Initialize comparison modal block
     */
    initComparisonBlocks: function() {
        var self = this;

        $('#comparisonmodal a').click(function(event) {
            self.deleteFile(event);
        });
        var comparison_file1_view = new DocumentView('#similar_file_one', '#similar_file_one_conteiner', '#similar_file_one_block', 425, 76, 1000);
        var comparison_file2_view = new DocumentView('#similar_file_two', '#similar_file_two_conteiner', '#similar_file_two_block', 425, 76, 1000);
    },

    /**
     * Initialize additional fields' block
     */
    initAdditionalFieldsBlock: function() {
        var self = this;

        $('#add_fed_id').blur(function() {
            self.testFedID();
        });

        $('#add_com_name').unbind('blur');//to avoid double event trigger
        $('#add_com_name').blur(function() {
            self.testComName($(this).val()).then(function (result_array) {
                var table = $("#companies_view_block_details table");
                table.html('');
                var str = '';
                var comp_str_value;

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

                    $('#comp_name_status').html('<a href="#" id="show_companies_list">already exists ('+result_array.length+')</a>').css('color', '#f00').css('text-align','right').css('width','180px');

                } else {

                    $('#comp_name_status').html('new company').css('color', '#41B50B').css('text-align','left').css('width','180px');
                    if ( $('#add_com_name').val().length <= 0 ) {
                        $('#comp_name_status').html('Company name required').css('color', '#f00').css('text-align','left').css('width','180px');
                    }
                }
            });


        });

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
            var add_contact = $('#add_contact').val();
            var add_phone = $('#add_phone').val();

            self.validateFields();

            if (self.validFedId) {


                if (self.existingFedId) {

                    $('#fed_id_status').html('<span style="text-align: right;width:180px;">already exists</span>').css('color', '#f00');

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

            if (self.validFedId && (self.existingFedId || (!self.existingFedId && comp_name != ''))) {

                self.sendAdditionalFields(fed_id, comp_name,bus_name,tax_name,street_adr,city,state,add_zip, add_contact, add_phone);

                close_modal_box('#additional_fields_block');
                $('#image' + self.fileToReview + ' .additions_cell').html('<span class="additional_field_pointer" style="color: #41B50B; cursor: pointer;" data="' + self.fileToReview + '">Complete</span>');
                self.initAdditionalFieldsPointer('#image' + self.fileToReview);
            }
        });

        $('#add_fields_remove_button').click(function(event) {
            self.fileToDelete = self.fileToReview;
            self.deleteFile(event);
        });

        var additional_fields_view_image = new DocumentView('#additional_fields_block_conteiner', '#additional_fields_block_file_conteiner', '#additional_fields_block_file', 360, 45, 1000);

    },

     /**
      * progress bar animation
      * leaved here for example
      */
     pb_step: function(progress) {
            //alert (progress);
         setTimeout(function(){$('#progressbar').val(progress);}, 1000);
     },

     /**
      * progress bar animation
      */
     prepend_last_upload_grid: function(answer) {

         $.each(answer, function(i, item){
             //alert(item.string);
             $("#last_upload_grid tbody").prepend(item.string);
             //alert("after prepend");

         })


     },


     /**
     * Check the form before send
     */
    checkForm: function() {
        var self = this;

         //pb.stepTo(50);


        if($("#current_upload_grid tr").length>=2) {
            self.sendCheckFormRequest();
        } else { show_alert("You must select at least one file", 350);
                 $("#progress_bar_container").fadeOut();
                 return false;
        }
        if (!self.validForm) {
            show_alert("Please complete additional fields!", 350);
            $("#progress_bar_container").fadeOut();
            return false;
        } else {
            $('#left_column').prepend("<div class='loadinng_mask'></div>");

            $.ajax({
                url: "/uploads/upload",
                data: {upload: true},
                async: true,
                type: "POST",
                success: function(msg) {
                    if (msg != '1') {
                        var answer=JSON.parse(msg);
                        $('#current_upload_grid tbody').empty();
                        self.prepend_last_upload_grid(answer);
                    } else if (msg == '1') {
                        show_alert("Invalid request!", 350);
                    } else if (msg == '2') {
                        show_alert("Invalid Fed ID!", 350);
                    }
                },

                error: function(msg) {
                    $('.loadinng_mask').remove();
                }

            });

            $('.loadinng_mask').remove();
            $('#submit_uploaded_file').removeClass('button');
            $('#submit_uploaded_file').addClass('not_active_button');


            $("#progress_bar_container").fadeOut(500);


        }
    },

    sendCheckFormRequest: function() {
        var self = this;
        $.ajax({
            url: "/uploads/checkformrequest",
            async: false,
            type: "POST",
            success: function(msg) {
                if (msg == '1') {
                    self.validForm = true;
                } else {
                    self.validForm = false;
                }
            }
        });
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

     /**
      *
      * @param fed_id
      * @param com_name
      * @param bus_name
      * @param tax_name
      * @param street_adr
      * @param city
      * @param state
      */
    sendAdditionalFields: function(fed_id, com_name,bus_name,tax_name,street_adr,city,state,add_zip,add_contact,add_phone) {
        var self = this;
        $.ajax({
            url: "/uploads/changeadditionalfields",
            data: {imgId: self.fileToReview,
                   fed_id: fed_id,
                   com_name: com_name,
                   bus_name: bus_name,
                   tax_name: tax_name,
                   street_adr: street_adr,
                   city: city,
                   state: state,
                   zip:add_zip,
                   contact:add_contact,
                   phone:add_phone
            },
            async: false,
            type: "POST",
            success: function(){

            }
        });
    },

    /**
     * Send document type to server
     * @param imgId
     * @param docType
     */
    sendDocumentType: function(imgId, docType) {
        var self = this;
        $.ajax({
            url: "/uploads/changedocumenttype",
            data: {imgId: imgId, docType: docType},
            async: false,
            type: "POST",
            success: function() {

            }
        });
    },

     /**
      * Displays block with file in right column
      * @param docId
      * @param fileType
      */
    displayFile: function(docId, fileType) {
        var self = this;
        $.ajax({
            url: "/uploads/getfilesblock",
            data: {imgId: docId, fileType: fileType},
            async: false,
            type: "POST",
            success: function(msg) {
                if (msg) {

                    $('#image_view_block').html(msg);
                    show_modal_box('#image_view_block', 725, 20);
                    self.initDetailsBlock();
                }
            }
        });
    },

    deleteFile: function(event) {
        var self = this;
        event.preventDefault();
        if (self.fileToDelete != 0 && self.fileToDelete != 'clear') {
            $.ajax({
                url: "/uploads/deletefile",
                data: {imgId: self.fileToDelete},
                async: false,
                type: "POST",
                success: function() {
                    window.location = '/uploads';
                }
            });
        } else if (self.fileToDelete == 'clear') {
            $.ajax({
                url: "/uploads/clearuploadsession",
                data: {clear: true},
                async: false,
                type: "POST",
                success: function() {
                    window.location = '/uploads';
                }
            });
        }
    },

    /**
     * Show block with file and additional fields
     */
    showAdditionalFieldsBlock: function() {
        var self = this;
        if (self.fileToReview != 0) {
            $.ajax({
                url: "/uploads/getadditionfieldsblock",
                data: {imgId: self.fileToReview},
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
        }
    },

   showDublicates: function () {
    $.ajax({
        type:"POST",
        url: '/uploads/AjaxCheckFileHash',
        success: function (html) {
            if (html=='files_are_not_similar') {
            } else {
                $('#similar_files_block_wrapper').empty();
                $('#similar_files_block_wrapper').append(html);
                up.fileToDelete=1;
                show_modal_box('#comparisonmodal', 910, 20);
            }
        }
    });
    },


     initUploadingRoutines: function () {
        var self = this;


            'use strict';

         self.uploaded_files_count= 0;
         self.uploaded_files_size= 0;

         var jqXHR,previous_file_name;
            var url = '/uploads/UploadStatus';


        var count = 0;
          var total_uploaded = 0;
           var file_names_list = [];
            var jqXHR;
               var count_files_accepted = 0;
                var show_alert_at_the_end = 0;

        $('#fileupload').fileupload({
                url: url,
                dataType: 'json',
                acceptFileTypes: /(pdf)|(jpg)|(jpeg)|(gif)|(png)|(tif)|(tiff)|(bmp)$/i,

            start: function (data) {


                $("#progress_bar_container").fadeIn('fast');
                $( "#progressbar_jui" ).progressbar({value: false});

                count = self.calculateexistingUploads();
            },

            add: function (e, data) {

                total_uploaded += data.files[0].size;
                if (data.originalFiles.length >= 2 && total_uploaded>4194304) {
                    console.log("Script should be stopped");
                    jqXHR.abort();
                    show_alert_at_the_end = 1;
                    //alert("For multiple file uploads only 4MB aggregate allowed.Other files will be skipped.")
                } else {
                    count_files_accepted++;
                }
                jqXHR = data.submit()
                    .success(function (result, textStatus, jqXHR) {/* ... */})
                    .error(function (jqXHR, textStatus, errorThrown) {/* ... */})
                    .complete(function (result, textStatus, jqXHR) {/* ... */});
            },

            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $( "#progressbar_jui" ).progressbar({ value: progress});
            },


            progress: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);

                var cutted_file_name = data.files[0].name.substring(0,20)+'...';
                $(".progress-label").text("Uploading : ["+ cutted_file_name +"]");

            },

            done: function (e, data) {

                $.each(data.result.files, function (index, file) {

                    if (!file.error) {
                        file_names_list.push(file.name) ;//array of names needed below

                        var jsonString = JSON.stringify(file);

                        if (count_files_accepted>=0) {
                            $.ajax({
                                type:"POST",
                                url: '/uploads/SaveUploadedFiles',
                                async:false,
                                data: {files : jsonString},
                                success: function (html) {
                                    if (html!='error'){
                                        $("#current_upload_grid").append(html);
                                        self.reinitClickEvents();
                                    }

                                }
                            });

                        }

                        count_files_accepted--;

                    } else {
                        $("#progress_bar_container").fadeOut(1000);
                        show_alert(file.error);
                    }


                });

                if (file_names_list.length == data.originalFiles.length || count_files_accepted==0 ) {
                    console.log("This is the code section that well be exequted last");
                    if (show_alert_at_the_end) {
                        show_alert("For multiple file uploads only 4MB aggregate allowed. Other files were skipped.",500);
                    }

                    $("#clear_upload_session").show();
                    $("#progress_bar_container").fadeOut(1000);
                    $('#submit_uploaded_file').removeClass('not_active_button');
                    $('#submit_uploaded_file').addClass('button');

                }
            },

            processalways: function(e,data){
                if (data.files.error) alert(data.files[0].error);
            }
        });

         $('button.cancel').click(function (e) {
             jqXHR.abort();
         });


            if ($('#project_id').data('id')=='2') {
                $('#fileupload').prop('disabled','disabled');
            }

            $('.canceltext').click(function (e) {
                jqXHR.abort();
                $("#progress_bar_container").fadeOut('slow');
            });




     },

     reinitClickEvents: function () {

         var self = this;

         var count=$('#current_upload_grid tbody tr').length;

         if($(".left_column #no_images").length) {
             count=count-1;
             $("#no_images").fadeOut();
         }


       if (count>0) {$('#submit_uploaded_file').removeClass('not_active_button').addClass('button');}


         $('span.dublicate_field_pointer').bind('click',function () {
             self.showDublicates();
         });

         /*$('.additions_cell .additional_field_pointer').click(function() {
             var imgId = $(this).attr('data');
             self.fileToReview =  imgId;
             self.showAdditionalFieldsBlock();
         });*/



     },

     refreshUploadsFileTable : function () {
         var self = this;
         $.ajax({
             type:"POST",
             url: '/uploads/RefreshUploadsTable',

             success: function (html) {
                 $("#current_upload_grid").html(html);
                 self.reinitClickEvents();
             }
         });


     },

     calculateexistingUploads: function () {
         var count = $('#current_upload_grid tbody tr').length;

         if($("#no_images").length) {
             count=count-1;
             $("#no_images").fadeOut();
         }
         //console.log ("Count tr",count);
         return count;
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

     },

     validateFields : function () { //without updating text fields
         var self = this;
         //validate fed_id
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




     testFedID : function () {
         var self = this;
         var fed_id_pattern = /^(\d{2}\-\d{7})|(\d{3}\-\d{2}\-\d{4})$/;
         var fed_id_temp_pattern = /^(IN[-]\d{6})|(T0[-]\d{7})$/;


             var add_fed_id = $('#add_fed_id').val();

             // Check Fed ID
             if (fed_id_pattern.test(add_fed_id) || fed_id_temp_pattern.test(add_fed_id)) {
                 self.validFedId = true;
                 self.checkFedId(add_fed_id);

                 if (self.existingFedId) {
                     $('#fed_id_status').html('already exists').css('color', '#f00').css('text-align','right').css('width','180px');
                     //$('#comp_name_status').html('already exists').css('color', '#f00').css('text-align','right').css('width','180px');
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
                 if(html) {
                     resolve(html);
                 } else {
                     if ( $('#add_com_name').val().length <= 0 ) {
                         $('#comp_name_status').html('Company name required').css('color', '#f00').css('text-align','left').css('width','180px');
                     }
                 }
             },

             error : function () {
                 reject('error');
             }

         });

       });
     }




}