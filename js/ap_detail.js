function APDetail(enableEditing) {
    this.init(enableEditing);
    this.initDeleteDocumentButton();
}

APDetail.prototype = $.extend(DetailPage.prototype, {

    /**
     * Document ID
     */
    docId: 0,

    /**
     * If enable editing data of AP
     */
    enableEditing: 'disable',

    /*
     * Initialize method
     */
    init: function(enableEditing) {

        var self = this;
        self.page = 'ap';
        self.enableEditing = enableEditing;

        $('#add_note_button').click(function() {
            self.addNote();
        });

        $('#approve_ap').click(function() {
            self.docId = $(this).attr('data');
            self.approveAp();
        });

        $('#hard_approve').click(function() {
            self.docId = $(this).attr('data');
            self.hardApprove();
        });


        $('#return_document').click(function() {
            self.docId = $(this).attr('data');
            self.returnAp();
        });

        $('#print_document').click(function() {
            var docId = $(this).attr('data');
            self.docId = docId;
            $.ajax({
                url: "/ap/setdocidtoprintdocument",
                data: {docId: docId},
                type: "POST",
                success: function(msg) {
                    var url = '/ap/printdocument';
                    window.open(url, 'popup', 'toolbar=no, menubar=no, status=no, location=no, resizable=no, width=640, height=600');
                }
            });
        });

        var ap_view = new DocumentView('#tab1', '#tab1_block', '#w9_detail_block1', 735, 45, 10);
        var check_view = new DocumentView('#tab3', '#tab3_block', '#detail_block3', 735, 45, 10);
        var back_up_vew = new DocumentView('#tab2', '#tab2_block', '#detail_block2', 735, 45, 10);

        $('#open_ap_details_box').click(function() {
            $('.w9_details').click();
        });

        $('.w9_details').click(function() {
            if ($(this).css('cursor') =='pointer') {

                var ap_id = $("#doc_id").data('id');
                $.ajax({
                    url: '/dataentry/AjaxApDataEntry',
                    data: {ap_id: ap_id },
                    type: 'POST',
                    success: function(html){
                        $('#dataentry_block').html(html);
                        show_modal_box('#dataentry_block', 260, 50);
                    }
                });
            }

        });


        var dpSettings = {
            dateFormat: "mm/dd/yy"
        }
        $('#Aps_Invoice_Due_Date').datepicker(dpSettings);
        $('#Aps_Invoice_Date').datepicker(dpSettings);

        $('#Aps_Invoice_Date').blur(function() {
            var pattern = /(\d{2}\/\d{2}\/\d{4})|(\d{6})/;
            if (pattern.test($(this).val())) {
                $('#Aps_Invoice_Due_Date').val($(this).val());
            }
        });

        setTimeout(function() {
            $('#progress_line').animate({width: parseInt($('#progress_line').data('width')) +'%'},1000,'easeOutExpo');
        },200);

        $('#sidebar_dists_block').click(function() {
            if (self.enableEditing == 'enable') {
                show_modal_box('#ap_dists_modal', 295);
            }
        });

        $('#add_invoice_add').click(function() {
            var countItems = $('#attached_invoices_add tbody tr').length;
            var newItem = countItems +1;
            var newLine = $("<tr><td><input type='text' name='Dist[" + newItem + "][GL_Dist_Detail_COA_Acct_Number]' value=''></td><td><input class='gl_amount dollar_fields' type='text' name='Dist[" + newItem + "][GL_Dist_Detail_Amt]' value=''></td><td><input type='text' name='Dist[" + newItem + "][GL_Dist_Detail_Desc]' value='' maxlenght='125'></td></tr>");
            newLine.appendTo('#attached_invoices_add tbody');
            $('#attached_dists_block_add').scrollTop(9999);
            newLine.effect('highlight');
            newLine.find('td').effect('highlight');
            newLine.find('input').effect('highlight');
        });

        $('#remove_invoice_add').click(function() {
            var countItems = $('#attached_invoices_add tbody tr').length;
            if (countItems > 4) {
                var item = $('#attached_invoices_add tbody tr:last-child');
                item.remove();
            }
        });

        $('.gl_amount').blur(function() {
            var amount = parseFloat($(this).val());
            if (!isNaN(amount) && amount > 0) {
                $(this).val(amount.toFixed(2));
            } else {
                $(this).val('');
            }
        });

        $('#recreate_document').click(function () {
            var doc_id = $(this).data('id');
            $.ajax({
                url: "/ap/recreate",
                data: {doc_id: doc_id},
                async: false,
                type: "POST",
                success: function() {
                    window.location = '/ap/detail';
                }
            });
        });
    },

    /**
     * Approve AP and reload page
     */
    approveAp: function() {
        var self = this;
        if (self.docId != 0) {
            $.ajax({
                url: "/ap/approveaps",
                data: {docs: self.docId},
                async: false,
                type: "POST",
                success: function() {
                    window.location = '/ap/detail';
                }
            });
        }
    },

    /**
     * Approve AP skipping the queue
     */
    hardApprove: function() {
        var self = this;
        if (self.docId != 0) {
            $.ajax({
                url: "/ap/HardApsApprove",
                data: {docs: self.docId},
                async: false,
                type: "POST",
                success: function() {
                    window.location = '/documents/approvalcue';
                }
            });
        }
    },


    /**
     * Return AP to previous approver
     */
    returnAp: function() {
        var self = this;
        if (self.docId != 0) {
            $.ajax({
                url: "/ap/returnap",
                data: {doc: self.docId},
                async: false,
                type: "POST",
                success: function() {
                    window.location = '/ap/detail';
                }
            });
        }
    }
})