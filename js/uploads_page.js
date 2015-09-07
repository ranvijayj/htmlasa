function UploadsPage() {
    var self = this;
    this.init();
    this.initAdditionalFieldsPointer();
    var lastUpload = $('#last_upload_files').text();
    self.lastUploadFiles = lastUpload.split('%');
    var currentUpload = $('#current_upload_files').text();
    self.currentUploadFiles = currentUpload.split('%');
}

 UploadsPage.prototype = {
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
     * Company name if Fed ID exists
     */
    existingCompanyName: '',

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

    /**
     * Initialize method
     */
    init: function() {
        var self = this;

        new AjaxUpload('#select_uploaded_file', {
            action: '/uploads/addfiletouploadsession',
            onSubmit : function(file, ext) {
                // Check count of uploads
                if (self.currentUploadFiles.length > 10) {
                    show_alert("Maximum 10 upload!");
                    return false;
                }

                // Compare with files of this session
                for(var i = 0; i < self.currentUploadFiles.length; i++) {
                    if (file == self.currentUploadFiles[i] || file == "C:\\fakepath\\" + self.lastUploadFiles[i] ) {
                        show_alert("File with this name is already in this upload session!", 500);
                        return false;
                    }
                }

                // Compare with files uploaded before
                for(var i = 0; i < self.lastUploadFiles.length; i++) {
                    if (file == self.lastUploadFiles[i] || file == "C:\\fakepath\\" + self.lastUploadFiles[i]) {
                        show_alert("File with this name was uploaded before. Change filename and try again please.", 400);
                        return false;
                    }
                }

                // check extension of the file
                if (!(ext && /^(jpg|jpeg|bmp|gif|png|tiff|tif|pdf)$/.test(ext))) {
                    show_alert("Invalid extension of the file (graphical formats only) !");
                    return false;
                }

                //show_alert("Uploading...");
                $('#left_column').prepend("<div class='loadinng_mask'></div>");

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
                    window.location = '/uploads';
                }
            }
        });

        $('.disable_uploading').click(function() {
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

        $('td.uploaded_file_name').click(function() {
            var row = $(this).parent();
            var addPointer = row.find('.additional_field_pointer').attr('data');
            var imgId = row.attr('id');
            imgId = imgId.slice(5);
            if (addPointer) {
                self.fileToReview =  imgId;
                self.showAdditionalFieldsBlock();
            } else {
                self.displayFile(imgId, 'current_uploads');
            }
        });

        $('#last_upload_grid td').click(function() {
            var docId = $(this).data('id');
            self.displayFile(docId, 'last_uploads');
        });

        $('#submit_uploaded_file').click(function() {
            self.checkForm();
        });

        $("#current_upload_grid td.dropdown_cell_upload").click(function(event) {
            event.stopPropagation();
            $("#users-to-approve-grid td.dropdown_cell_upload ul").slideUp(150);
            $(this).find('ul').slideDown(150);
        });

        $("#current_upload_grid td.dropdown_cell_upload ul").mouseout(function() {
            var ul = $(this);
            self.timeout = setTimeout(function() {
                ul.slideUp(150);
            }, 100);
        });

        $("#current_upload_grid td.dropdown_cell_upload ul").mouseover(function() {
            if (self.timeout) {
                clearTimeout(self.timeout);
                self.timeout = false;
            }
        });

        $("#current_upload_grid td.dropdown_cell_upload ul li").click(function(event) {
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

        $('body').click(function() {
            $("#current_upload_grid td.dropdown_cell_upload ul").slideUp(150);
        });

        $('.delete_file').click(function() {
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
        var fed_id_pattern = /^(\d{2}\-\d{7})|(\d{3}\-\d{2}\-\d{4})$/;

        $('#add_fed_id').blur(function() {
            var add_fed_id = $(this).val();

            // Check Fed ID
            if (fed_id_pattern.test(add_fed_id)) {
                self.validFedId = true;
                self.checkFedId(add_fed_id);
                if (self.existingFedId) {
                    $('#fed_id_status').text('already exists').css('color', '#f00');
                    $('#add_com_name').val(self.existingCompanyName).attr('disabled', true);
                    $('#com_name_status').text('');
                } else {
                    $('#fed_id_status').text('new company').css('color', '#41B50B');
                    $('#add_com_name').val('').attr('disabled', false);
                    $('#add_com_name').focus();
                }
            } else {
                $('#fed_id_status').text('invalid value, correct: xx-xxxxxxx').css('color', '#f00');
                $('#add_com_name').val('').attr('disabled', true);
                $('#com_name_status').text('');
                self.validFedId = false;
            }
        });

        $('#add_fields_ok_button').click(function() {
            var fed_id = $('#add_fed_id').val();
            var comp_name = $('#add_com_name').val();

            if (fed_id_pattern.test(fed_id)) {
                self.validFedId = true;
                self.checkFedId(fed_id);
                if (self.existingFedId) {
                    $('#fed_id_status').text('already exists').css('color', '#f00');
                    $('#add_com_name').val('').attr('disabled', true);
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

            if (self.validFedId ) {
                self.sendAdditionalFields(fed_id, comp_name);
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
     * Check the form before send
     */
    checkForm: function() {
        var self = this;

        if (self.currentUploadFiles.length == 1) {
            show_alert("You must select at least one file", 350);
            return false;
        }

        self.sendCheckFormRequest();

        if (!self.validForm) {
            show_alert("Please complete additional fields!", 350);
            return false;
        } else {
            //show_alert("Please wait", 250);
            $('#left_column').prepend("<div class='loadinng_mask'></div>");
            $.ajax({
                url: "/uploads/upload",
                data: {upload: true},
                async: false,
                type: "POST",
                success: function(msg) {
                    if (msg == '') {
                        window.location = '/uploads';
                    } else if (msg == '1') {
                        show_alert("Invalid request!", 350);
                    } else if (msg == '2') {
                        show_alert("Invalid Fed ID!", 350);
                    }
                }
            });
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
            async: false,
            type: "POST",
            success: function(msg) {
                if (msg != 0) {
                    self.existingFedId = true;
                    self.existingCompanyName = msg
                } else {
                    self.existingFedId = false;
                    self.existingCompanyName = '';
                }
            }
        });
    },

    /**
     * Send Fed Id
     * @param fed_id
     * @param com_name
     */
    sendAdditionalFields: function(fed_id, com_name) {
        var self = this;
        $.ajax({
            url: "/uploads/changeadditionalfields",
            data: {imgId: self.fileToReview, fed_id: fed_id, com_name: com_name},
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
    }
}