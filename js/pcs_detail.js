function PCDetail() {
    this.init();
}

PCDetail.prototype = $.extend(DetailPage.prototype, {

    /**
     * Document ID
     */
    docId: 0,

    /*
     * Initialize method
     */
    init: function() {
        var self = this;
        self.page = 'pc';

        $('#print_document').click(function() {
            var docId = $(this).attr('data');
            self.docId = docId;
            $.ajax({
                url: "/pc/setdocidtoprintdocument",
                data: {docId: docId},
                type: "POST",
                success: function(msg) {
                    var url = '/pc/printdocument';
                    window.open(url, 'popup', 'toolbar=no, menubar=no, status=no, location=no, resizable=no, width=640, height=600');
                }
            });
        });

        $('#send_document_by_email').click(function() {
            show_modal_box('#askemailbox');
            var docId = $(this).attr('data');
            self.docId = docId;
        });

        $('#doc_to_user_email').blur(function() {
            self.checkEmail();
        });

        // Send document by email action
        $('#send_doc_by_email').click(function() {
            if (self.validMail) {
                close_modal_box('#askemailbox');
                var email = $('#doc_to_user_email').val();
                $.ajax({
                    url: "/pc/senddocumentbyemail",
                    data: {email: email, docId: self.docId},
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

        var pc_view = new DocumentView('#tab1', '#tab1_block', '#detail_block1', 735, 45, 10);
    }
})