/**
 * Created by lee on 9/12/14.
 */
function FileUploading(calling_side) {
    var self = this;
    this.init(calling_side);

    //this.initTable();
}
FileUploading.prototype =$.extend(DocCreate.prototype, {


    calling_module:null,

    filetodelete:null,

    apObject:null,

 init: function(calling_module) {

    'use strict';
    var self = this;
    var jqXHR;
    var url = '/uploads/UploadStatus';

     //define which module of system called this js
    self.calling_module = calling_module;
    if(calling_module=='AP' || calling_module=='AP_BU') {
        self.page='ap';
    } else if(calling_module=='PO' || calling_module=='PO_BU'  ) {
        self.page='po';
    }  else if(calling_module=='PAY' || calling_module=='PAY_BU'  ) {
        self.page='ap';
    }

    $('#fileupload').bind('fileuploadadd', function (e, data) {
        $.each(data.files, function (index, file) {
            var cutted_file_name = file.name.substring(0,18)+'...';
            $(".progress-label").text("Uploading : ["+ cutted_file_name +"]");
        });
    });

    $('#fileupload').fileupload({
        url: url,
        dataType: 'json',

        add: function (e, data) {
            jqXHR = data.submit()
                .success(function (result, textStatus, jqXHR) {/* ... */})
                .error(function (jqXHR, textStatus, errorThrown) {/* ... */})
                .complete(function (result, textStatus, jqXHR) {/* ... */});



        },

        start: function () {
            console.log("starting uploading");
            console.log(self.calling_module,"calling module");
            $("#progress_bar_container").fadeIn('fast');
            $( "#progressbar_jui" ).progressbar({value: false});

            //disable other 3 buttons for ap
            $('#create_check_rq').removeClass('button').addClass('not_active_button');


            $('#send_to_approve').removeClass('button').addClass('not_active_button');

            //disable other 3 buttons for po
            $('#create_po').removeClass('button').addClass('not_active_button');
           // $('#save_po').hide();
            //$('#save_po').removeClass('button').addClass('not_active_button');
            $('#send_to_approve').removeClass('button').addClass('not_active_button');
        },

        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            setTimeout(function(){
                $( "#progressbar_jui" ).progressbar({
                    value: progress
                });
            },100);
        },

        done: function (e, data) {
            //console.log(data.result.files);
            $.each(data.result.files, function (index, file) {

                if(file.error) {
                    show_alert (file.error);  $("#progress_bar_container").fadeOut('slow');
                } else if (file.warning) {
                    show_alert (file.warning);$("#progress_bar_container").fadeOut('slow');
                } else {
                    var count = $('#current_upload_grid tr').length;
                    if($("#no_images").length) {
                        count=count-1;
                        $("#no_images").fadeOut();
                    }
                    var jsonString = JSON.stringify(file);



                    /**
                     * sets $_SESSION variables to apropriate values
                     * here begin differences in logic according to the module defined in "calling_module" variables
                     *
                     */
                    if(self.calling_module=='AP' || self.calling_module=='AP_BU') {
                        self.ap_SessionVarsInit(jsonString);
                    }else  if(self.calling_module=='PO' || self.calling_module=='PO_BU') {
                        self.po_SessionVarsInit(jsonString);
                    } else  if(self.calling_module=='PAY' || self.calling_module=='PAY_BU') {
                        self.ap_SessionVarsInit(jsonString);
                    }  else {
                        $.ajax({
                            type:"POST",

                            url: '/uploads/SessionVarsInit',
                            data: {files : jsonString},
                            success: function (html) {
                                self.postrProcess(jsonString);
                            }
                        });
                    }


                    $("#clear_upload_session").show();
                    $("#progress_bar_container").fadeOut('slow');

                    //enable other 3 buttons
                    $('#create_check_rq').removeClass('not_active_button').addClass('button');

                    $('#send_to_approve').removeClass('not_active_button').addClass('button');

                }

            });
        }
    }).prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');

    $('.canceltext').click(function (e) {
        jqXHR.abort();
        $("#progress_bar_container").fadeOut('slow');
    });

 },

    ap_SessionVarsInit : function (jsonString) {
        var self = this;
        $.ajax({
            type:"POST",
            //sets $_SESSION variables to appropriate values
            url: '/uploads/ApSessionVarsInit',
            data: {files : jsonString},
            success: function (html) {
                self.postrProcess(jsonString);
            }
        });


    },

    pay_SessionVarsInit : function (jsonString) {
        var self = this;
        $.ajax({
            type:"POST",
            //sets $_SESSION variables to appropriate values
            url: '/uploads/PoSessionVarsInit',
            data: {files : jsonString},
            success: function (html) {
                self.postrProcess(jsonString);
            }
        });


    },

    po_SessionVarsInit : function (jsonString) {
        var self = this;
        $.ajax({
            type:"POST",
            //sets $_SESSION variables to appropriate values
            url: '/uploads/PoSessionVarsInit',
            data: {files : jsonString},
            success: function (html) {
                self.postrProcess(jsonString);
            }
        });


    },

    ap_FileBlock : function (jsonString) {
        var self = this;
        var url;
        if (self.calling_module=='AP_BU'){ url='/uploads/GetApBuFileBlock';
        } else {url='/uploads/GetApFileBlock';}


        $.ajax({
            type:"POST",
            //sets $_SESSION variables to apropriate values
            url: url,
            data: {files : jsonString},
            success: function (html) {

                self.initUploadBox();

                self.afterApUpload(html);
            }
        });
    },

    pay_FileBlock : function (jsonString) {
        var self = this;
        var url;
        if (self.calling_module=='AP_BU'){ url='/uploads/GetApBuFileBlock';
        } else {url='/uploads/GetApFileBlock';}


        $.ajax({
            type:"POST",
            //sets $_SESSION variables to apropriate values
            url: url,
            data: {files : jsonString},
            success: function (html) {

                self.initUploadBox();

                self.afterApUpload(html);
            }
        });
    },

    po_FileBlock : function (jsonString) {

        var self = this;
        var url;
        if (self.calling_module=='PO_BU'){ url='/uploads/GetPoBuFileBlock';
        } else {url='/uploads/GetPoFileBlock';}

        $.ajax({
            type:"POST",
            //sets $_SESSION variables to apropriate values
            url: url,
            data: {files : jsonString},
            success: function (html) {

                self.initUploadBox();

                self.afterApUpload(html);
            }
        });
    },


    afterApUpload: function(html) {
        self.validFedId =  false;
        self.existingFedId =  false;
        self.existingCompanyName =  '';
        self.lastDocType =  "BU";
        $('#additional_fields_block').html(html);
        close_alert();
        setTimeout(function() {
            show_modal_box('#additional_fields_block', 725, 50);
        }, 250);


    },

    postrProcess : function (jsonString) {
        var self = this;
        //check is such file was loaded before
        if(self.calling_module=='AP' || self.calling_module=='AP_BU') {
            self.ap_FileBlock();
        } else if(self.calling_module=='PO' || self.calling_module=='PO_BU' ) {
            self.po_FileBlock();
        } else if (self.calling_module=='PAY' || self.calling_module=='PAY_BU'){
            self.pay_FileBlock();
        } else  {
            $.ajax({
                type:"POST",
                url: '/uploads/AjaxCheckFileHash',
                data: {files : jsonString},
                success: function (html) {
                    if (html=='files_are_not_similar') {

                    } else {
                        $('#similar_files_block_wrapper').empty();
                        $('#similar_files_block_wrapper').append(html);
                        self.fileToDelete=1;
                        show_modal_box('#comparisonmodal', 910, 20);
                    }
                }
            });
        }
    }


});
