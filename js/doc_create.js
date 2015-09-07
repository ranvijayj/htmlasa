function DocCreate() {

}

DocCreate.prototype = {
    /**
     * Drop down ul timeout
     */
    timeout: false,

    /**
     * Upload file flag
     */
    isUploaded: false,

    /**
     * Valid Fed ID
     */
    validFedId: false,

    /**
     * Existing Fed ID
     */
    existingFedId: false,

    /**
     * Existing company name
     */
    existingCompanyName: '',

    /**
     * lastDocType
     */
    lastDocType: "",

    /**
     * Current page
     */
    page: '',

    /**
     * Initialize Back Up
     */
    initBackUp: function() {
        var back_up_view = new DocumentView('#tab2', '#tab2_block', '#detail_block2', 735, 45, 10);
    },

    /**
     * Initialize upload box
     */
    initUploadBox: function() {
        var self = this;

        $("body").on('click','td.dropdown_cell_upload',function(event) {
            event.stopPropagation();
            $("td.dropdown_cell_upload ul").slideUp(150);
            $(this).find('ul').slideDown(150);
        });

        $("td.dropdown_cell_upload ul").live('mouseout',function() {
            var ul = $(this);
            self.timeout = setTimeout(function() {
                ul.slideUp(150);
            }, 100);
        });

        $("td.dropdown_cell_upload ul").live('mouseover',function() {
            if (self.timeout) {
                clearTimeout(self.timeout);
                self.timeout = false;
            }
        });

        $("body").on('click','.dropdown_cell_ul ul li',function(event) {
            event.stopPropagation();

            var text = $(this).text();
            var cell = $(this).parent().parent().parent();
            var dropdown_cell_value = cell.find('.dropdown_cell_value');
            dropdown_cell_value.text(text);

            self.sendDocumentType(text);
            self.lastDocType = text;
            var add_pointer = $('#additional_fields_pointer');
            // Check document file and open additional fields
            if (text == 'W9') {
                add_pointer.css('color', '#f00').html('REQUIRED');
                $('#add_fed_id').val('');
                $('#add_com_name').val('').attr('disabled', true);
                $('#fed_id_status').text('');
                $('#com_name_status').text('');
                $('.additional_fields_form').show();
            } else {
                add_pointer.css('color', '#41B50B').text('NOT REQUIRED');
                $('.additional_fields_form').hide();
            }

            $("td.dropdown_cell_upload ul").slideUp(150);
        });

        $('body').click(function() {
            $("td.dropdown_cell_upload ul").slideUp(150);
        });

        var fed_id_pattern = /^(\d{2}\-\d{7})|(\d{3}\-\d{2}\-\d{4})$/;

        $('body').on('blur','#add_fed_id',function() {
            var add_fed_id = $(this).val();

            // Check Fed ID
            if (fed_id_pattern.test(add_fed_id)) {
                self.validFedId = true;
                self.checkFedId(add_fed_id);
                if (self.existingFedId) {
                    $('#fed_id_status').text('already exists').css('color', '#f00');
                    $('#add_com_name').val(self.existingCompanyName).attr('disabled', true);
                    $('#com_name_status').text('');
                    $('#additional_fields_pointer').css('color', '#41B50B').text('Complete');
                } else {
                    $('#fed_id_status').text('new company').css('color', '#41B50B');
                    $('#add_com_name').val('').attr('disabled', false);
                    $('#add_com_name').focus();
                    $('#additional_fields_pointer').css('color', '#f00').html('REQUIRED');
                }
            } else {
                $('#fed_id_status').text('invalid value, correct: xx-xxxxxxx').css('color', '#f00');
                $('#add_com_name').val('').attr('disabled', true);
                $('#com_name_status').text('');
                self.validFedId = false;
                $('#additional_fields_pointer').css('color', '#f00').html('REQUIRED');
            }
        });

        $('#add_com_name').blur(function() {
            var add_com_name = $(this).val();
            if (add_com_name == '') {
                $('#additional_fields_pointer').css('color', '#f00').html('REQUIRED');
            } else {
                $('#additional_fields_pointer').css('color', '#41B50B').text('Complete');
            }
        });



        //$('#upload_file').('click',function() {
        $('#additional_fields_block').on('click','#upload_file',function() {

            self.reinitVars();
            var fed_id = $('#add_fed_id').val(); //exists only for W9

            var comp_name = $('#add_com_name').val();//exists only for W9
            var docId = $('#save_' + self.page).attr('data'); // po or ap ID
            if (self.lastDocType == 'W9') {
                if (fed_id_pattern.test(fed_id)) {
                    self.validFedId = true;
                    self.checkFedId(fed_id);
                    if (self.existingFedId) {
                        $('#fed_id_status').text('already exists').css('color', '#f00');
                        $('#add_com_name').val('').attr('disabled', true);
                        $('#com_name_status').text('');
                        $('#additional_fields_pointer').css('color', '#41B50B').text('Complete');
                    } else {
                        $('#fed_id_status').text('new company').css('color', '#41B50B');
                        $('#add_com_name').attr('disabled', false);
                        if (comp_name == '') {
                            $('#additional_fields_pointer').css('color', '#f00').html('REQUIRED');
                            $('#com_name_status').text('required').css('color', '#f00');
                        } else {
                            $('#com_name_status').text('');
                            $('#additional_fields_pointer').css('color', '#41B50B').text('Complete');
                        }
                    }
                } else {
                    $('#fed_id_status').text('invalid value, correct: xx-xxxxxxx').css('color', '#f00');
                    $('#add_com_name').val('').attr('disabled', true);
                    $('#com_name_status').text('');
                    $('#additional_fields_pointer').css('color', '#f00').html('REQUIRED');
                    self.validFedId = false;
                }

                if (self.validFedId && (self.existingFedId || (!self.existingFedId && comp_name != ''))) {
                    self.uploadFile(docId, self.lastDocType, fed_id, comp_name);
                    close_modal_box('#additional_fields_block');
                }
            } else {
                self.uploadFile(docId, self.lastDocType, fed_id, comp_name);
                close_modal_box('#additional_fields_block');
            }
            $('#additional_fields_block').off('click','#upload_file');
        });

        var uploaded_file_view = new DocumentView('#additional_fields_block_conteiner', '#additional_fields_block_file_conteiner', '#additional_fields_block_file', 360, 45, 1000);
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
     * Send document type to server
     * @param docType
     */
    sendDocumentType: function(docType) {
        var self = this;
        $.ajax({
            url: "/" + self.page + "/changedocumenttype",
            data: {docType: docType},
            async: false,
            type: "POST",
            success: function() {

            }
        });
    },

    /**
     * Update Vendor info block
     * @param vendorId
     */
    updateVendorInfoBlock: function(vendorId) {
        var self = this;
        $.ajax({
            url: "/" + self.page + "/getvendorinfoblock",
            data: {vendorId: vendorId},
            type: "POST",
            success: function(msg) {
                $('#po_vendor_info_block').html(msg);
            }
        });
    },

    /**
     * Check integer Type of value
     */
    checkIntegerType: function(elem, value) {
        value = parseInt(value);
        if (value == 0 || isNaN(value)) {
            value = '';
        }

        elem.val(value);
    },

    /**
     * Check float Type of value
     */
    checkFloatType: function(elem, value) {
        value = parseFloat(value);
        if (value == 0 || isNaN(value)) {
            value = '';
        } else {
            value = value.toFixed(2);
        }

        elem.val(value);
    },

    /**
     * Upload file to DB
     * @param docId
     * @param lastDocType
     * @param fed_id
     * @param comp_name
     */
    uploadFile: function(docId, lastDocType, fed_id, comp_name) {
        var self = this;
        var currentVendor = $('#' + self.page.slice(0, 1).toUpperCase() + self.page.slice(1) + 's_Vendor_ID').val();
        $.ajax({
            url: "/" + self.page + "/uploadfile",
            data: {docId: docId, docType: lastDocType, fed_id: fed_id, comp_name: comp_name},
            type: "POST",
            dataType: 'json',
            success: function(data) {
                if (lastDocType == 'BU' && data.back_up_block != '') {
                    $('#tab2').html(data.back_up_block);
                    self.initBackUp();
                }

                if (lastDocType == 'W9' && data.vendors_list != '') {
                    $('#' + self.page.slice(0, 1).toUpperCase() + self.page.slice(1) + 's_Vendor_ID').html(data.vendors_list);
                    $('#' + self.page.slice(0, 1).toUpperCase() + self.page.slice(1) + 's_Vendor_ID option[value=' + currentVendor + ']').attr('selected', 'selected');
                }
            }
        });
    },

    reinitVars: function()
    {
        var self = this;
        var fed_id = $('#add_fed_id').val();
        var comp_name = $('#add_com_name').val();
        var docId = $('#save_' + self.page).attr('data'); // po or ap ID
        self.lastDocType= $("td.dropdown_cell_upload span.dropdown_cell_value").text();


    }
}