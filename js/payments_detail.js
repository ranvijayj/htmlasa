function PaymentsDetail(currentAP) {
    this.init(currentAP);
    this.initDeleteDocumentButton();
}

PaymentsDetail.prototype = $.extend(DetailPage.prototype, {

    /**
     * Document ID
     */
    docId: 0,

    /**
     * Current AP
     */
    currentAP: 0,

    /*
     * Initialize method
     */
    init: function(currentAP) {

        var self = this;
        self.currentAP = currentAP;
        self.page = 'payments';
        $('#print_document').click(function() {
            var docId = $(this).attr('data');
            self.docId = docId;
            $.ajax({
                url: "/payments/setdocidtoprintdocument",
                data: {docId: docId},
                type: "POST",
                success: function(msg) {
                    var url = '/payments/printdocument';
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
                    url: "/payments/senddocumentbyemail",
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

        var payment_view = new DocumentView('#tab3', '#tab3_block', '#w9_detail_block3', 735, 45, 10);
        var ap_view = new DocumentView('#tab1', '#tab1_block', '#detail_block1', 735, 45, 10);
        var ap_backup_view = new DocumentView('#tab2', '#tab2_block', '#detail_block2', 735, 45, 10);

        setTimeout(function() {
            $('#progress_line').animate({width: parseInt($('#progress_line').data('width')) +'%'},1000,'easeOutExpo');
        },200);

        $('.payment_invoice').click(function() {
            var apId = $(this).data('apId');
            if (apId !=  self.currentAP) {
                $('.current_payment_invoice').removeClass('current_payment_invoice');
                $(this).addClass('current_payment_invoice');
                $('a[href=#tab1]').click();
                self.currentAP = apId;
                self.switchAP(apId);
            }
        });

        $("#doc_to_user_email" ).autocomplete({
            source: function( request, response ) {
                $.ajax({
                    url: "/usersAnswers/GetEmails",
                    dataType: "json",
                    type:"POST",
                    data: {
                        search_string: request.term
                    },

                    success: function( data ){

                        response(data);
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        console.log(xhr.status);
                        console.log(thrownError);
                    }

                });
            },
            minLength:1,

            select: function( event, ui ) {

                //console.log("selected "+ui.item.value);
                var arr = ui.item.value.split(' - ');
                //console.log("array "+arr);
                $(this).val( arr[0] );
                self.temp_val = arr[0];
                $(this).parent().parent().find('td:last-child input').val(arr[1]);
                return false;
            },

            open: function() {
                $( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
            },

            close: function() {
                $( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
            }

        }).bind('focus', function () {
            $(this).autocomplete("search");
        });

        $('#edit_dataentry').click(function() {

            var doc_id = $(this).attr("data");
            var paym_id = $(this).data("paym-id");
            if (doc_id != 0) {
                $.ajax({
                    url: '/dataentry/AjaxPaymDataEntry',
                    data: {paym_id: paym_id },
                    type: 'POST',
                    success: function(html){
                        $('#dataentry_block').html(html);
                        show_modal_box('#dataentry_block', 260, 50);
                    }
                });
            }

        });




    },

    switchAP: function(apId) {
        $.ajax({
            url: "/payments/getapview",
            data: {apId: apId},
            type: "POST",
            dataType: 'json',
            success: function(data) {
                $('#tab1').html(data.htmlAP);
                $('#tab2').html(data.htmlAPBackup);
                $('#progress_line').animate({width: parseInt(data.appval) +'%'},1000,'easeOutExpo');
                var ap_view = new DocumentView('#tab1', '#tab1_block', '#detail_block1', 735, 45, 10);
                var ap_backup_view = new DocumentView('#tab2', '#tab2_block', '#detail_block2', 735, 45, 10);
                $('#payment_dists_block #ap_dists_cont').remove();
                $('#payment_dists_block').append(data.distsHtml);
            }
        });
    }
})