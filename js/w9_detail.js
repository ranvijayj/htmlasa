function W9Detail() {
    this.init();
    this.initDeleteDocumentButton();
}

W9Detail.prototype = $.extend(DetailPage.prototype, {
    /**
     * Client Id
     */
    clientId: 0,

    /*
     * Initialize method
     */
    init: function() {

        var self = this;
        self.page = 'w9';

        $('#add_note_button').click(function() {
            self.addNote();
        });

        $('#print_document').click(function() {
            var fed_id = $(this).attr('data');
            $.ajax({
                url: "/w9/setfedidtoprintdocument",
                data: {fed_id: fed_id},
                type: "POST",
                async: false,
                success: function(msg) {

                }
            });
            var url = '/w9/printdocument';
            window.open(url, 'popup', 'toolbar=no, menubar=no, status=no, location=no, resizable=no, width=640, height=600');
        });

        $('#send_document_by_email').click(function() {
            show_modal_box('#askemailbox');
            var client_id = $(this).attr('data');
            self.clientId = client_id;
        });

        $('#doc_to_user_email').blur(function() {
            self.checkEmail();
        });

        $('#send_document_by_fax').click(function() {
            show_modal_box('#askfaxbox');
            var client_id = $(this).attr('data');
            self.clientId = client_id;
        });

        $('#doc_to_user_fax').blur(function() {
            self.checkFax();
        });

        // Send document by email action
        $('#send_doc_by_email').click(function() {
            if (self.validMail) {
                close_modal_box('#askemailbox');
                var email = $('#doc_to_user_email').val();
                $.ajax({
                    url: "/w9/senddocumentbyemail",
                    data: {email: email, client_id: self.clientId},
                    type: "POST",
                    success: function(msg) {
                        if (msg == 1) {
                            setTimeout(function() {
                                show_alert("Email was sent!", 250);
                            }, 200);
                        } else {
                            setTimeout(function() {
                                show_alert("Email was not sent!", 250);
                            }, 200);
                        }
                    }
                });
            } else {
                $('#doc_to_user_email').focus();
            }
        });

        // Send document by fax action
        $('#send_doc_by_fax').click(function() {
            if (self.validFax) {
                close_modal_box('#askfaxbox');
                var fax = $('#doc_to_user_fax').val();
                $.ajax({
                    url: "/w9/senddocumentbyfax",
                    data: {fax: fax, client_id: self.clientId},
                    type: "POST",
                    success: function(msg) {
                        if (msg == 1) {
                            setTimeout(function() {
                                show_alert("Fax was sent!", 250);
                            }, 200);
                        } else {
                            setTimeout(function() {
                                show_alert("Fax was not sent!", 250);
                            }, 200);
                        }
                    }
                });
            }
        });

        $('#share_document').click(function() {
            show_modal_box('#share_document_box');
        });

        $('#share_document_form').submit(function(event) {
            event.preventDefault();
            var companyId = $('#share_document_company').val();
            var accessType = $('#w9_access_type').val();
            var w9Id = $('#share_document_id').val();
            if (companyId > 0) {
                self.shareW9(companyId, w9Id, accessType);
            } else {
                $('#share_document_company_error').show();
            }
        });

        $('#share_document_company').change(function() {
            var companyId = $(this).val();
            if (companyId > 0) {
                $('#share_document_company_error').hide();
            }
        });

        var w9_view = new DocumentView('#tab1', '#tab1_block', '#w9_detail_block1', 735, 45, 10);
        var w9_view2 = new DocumentView('#tab2', '#tab2_block', '#w9_detail_block2', 735, 45, 10);
        var w9_view3 = new DocumentView('#tab3', '#tab3_block', '#w9_detail_block3', 735, 45, 10);
    },


    /**
     * Gets company info
     */
    addNote: function() {
        var comment = $('#notes_blok .note_textarea').val();
        var company_id = $('#notes_blok #note_to_company').val();
        $('#notes_blok .note_textarea').val('');
        if (comment != '') {
            $.ajax({
                url: "/w9/addnote",
                data: {comment: comment, company_id: company_id},
                type: "POST",
                success: function(msg) {
                    $('#notes_list').scrollTop(0).prepend(msg);
                    $('#notes_list .note_item:first-child').effect('highlight');
                }
            });
        } else {
            $('#notes_blok .note_textarea').focus();
        }
    },

    /**
     * Share W9
     * @param companyId
     * @param w9Id
     * @param accessType
     */
    shareW9: function(companyId, w9Id, accessType) {
        $.ajax({
            url: "/w9/sharew9",
            data: {
                companyId: companyId,
                accessType: accessType,
                w9Id: w9Id
            },
            type: "POST",
            success: function() {
                close_modal_box('#share_document_box');
                setTimeout(function() {
                    show_alert("W9 has been shared!");
                }, 210);
            },
            error: function() {
                close_modal_box('#share_document_box');
                setTimeout(function() {
                    show_alert("W9 was not shared!");
                }, 210);
            }
        });
    }
});