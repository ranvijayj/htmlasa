function BuUpload(page) {
    this.init(page);
}

BuUpload.prototype = {

    /**
     * Current page
     */
    page: '',


    /**
     * Initialize method
     */
    init: function(page)
    {
        var self = this;
        self.page = page;

       /* new AjaxUpload('#show_upload_modal', {
            action: '/' + self.page + '/addfiletouploadsession?page=detail_page',
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
                    $('#additional_fields_block').html(response);
                    close_alert();
                    setTimeout(function() {
                        show_modal_box('#additional_fields_block', 725, 50);
                    }, 250);
                    self.initUploadBox();
                    this.enable();
                }
            }
        });*/
    },

    /**
     * Initialize upload box
     */
    initUploadBox: function() {
        var self = this;

        $('#upload_file').click(function() {
            var docId = $('#doc_id').data('id');
            self.uploadFile(docId);
            close_modal_box('#additional_fields_block');
        });

        var uploaded_file_view = new DocumentView('#additional_fields_block_conteiner', '#additional_fields_block_file_conteiner', '#additional_fields_block_file', 360, 45, 1000);
    },

    /**
     * Upload file to DB
     * @param docId
     */
    uploadFile: function(docId) {
        var self = this;
        $.ajax({
            url: "/" + self.page + "/uploadfile?page=detail_page",
            data: {docId: docId, docType: 'BU', fed_id: '', comp_name: ''},
            type: "POST",
            dataType: 'json',
            success: function(data) {
                if (data.back_up_block != '') {
                    $('#tab2').html(data.back_up_block);
                    self.initBackUp();
                }
            }
        });
    },

    /**
     * Initialize Back Up
     */
    initBackUp: function() {
        var back_up_vew = new DocumentView('#tab2', '#tab2_block', '#detail_block2', 735, 45, 10);
    }
}