function VendorDetail() {
    this.init();
    this.initVendor();
}

VendorDetail.prototype = $.extend(W9Detail.prototype, {
    /**
     * Vendor initialize method
     */
    initVendor: function() {
        this.page = 'vendor';
        $('#print_vendor_document').click(function() {
            var client_id = $(this).attr('data');
            $.ajax({
                url: "/vendor/setvendortoprintdocument",
                data: {client_id: client_id},
                type: "POST",
                success: function(msg) {

                }
            });
            var url = '/vendor/printdocument';
            window.open(url, 'popup', 'toolbar=no, menubar=no, status=no, location=no, resizable=no, width=640, height=600');
        });

        $('#vendor_info_block li').click(function() {
            show_modal_box('#edit_vendor_info', 260, 50);
        });

        $('.w9_details').click(function() {
            if ($(this).css('cursor') =='pointer') {

                var w9_doc_id = $("#w9_doc_id").val();
                var company_id = $("#company_id").val();
                var vendor_id = $("#vendor_id").val();
                var cli_adm_user_id = $('#cli_adm_user_id').val()
                $.ajax({
                    url: '/vendor/GetAdditionFieldsBlock',
                    data: {
                        w9_doc_id: w9_doc_id,
                        company_id: company_id,
                        vendor_id: vendor_id,
                        cli_adm_user_id:cli_adm_user_id
                    },
                    type: 'POST',
                    success: function(html){
                        $('#dataentry_block_ext').html(html);
                        show_modal_box('#dataentry_block_ext', 750, 50);
                        $('#editw9vendorbtn').click(function (){
                        $('#updatew9detail_form').submit();
                        })
                    }
                });
            }

        });

    },

    /**
     * Get or add note
     */
    addNote: function(comId) {
        var comment = $('#notes_blok .note_textarea').val();
        var vendor_id = $('#notes_blok #note_to_vendor').val();
        if (comment != '') {
            $.ajax({
                url: "/vendor/updatenote",
                data: {comment: comment, vendor_id: vendor_id},
                type: "POST",
                success: function(msg) {
                    $('#notes_list').html(msg);
                    $('#notes_blok .note_textarea').val('');
                }
            });
        } else {
            $.ajax({
                url: "/vendor/getnote",
                data: {vendor_id: vendor_id},
                type: "POST",
                success: function(msg) {
                    $('#notes_blok .note_textarea').val(msg).focus();
                }
            });
        }
    },

    saveW9Details: function() {

    }
})