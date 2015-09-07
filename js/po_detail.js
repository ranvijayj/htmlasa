function PODetail() {
    this.init();
    this.initDeleteDocumentButton();
}

PODetail.prototype = $.extend(DetailPage.prototype, {

    /**
     * Document ID
     */
    docId: 0,

    /*
     * Initialize method
     */
    init: function() {

        var self = this;
        self.page = 'po';

        $('#add_note_button').click(function() {
            self.addNote();
        });

        $('#approve_po').click(function() {
            self.docId = $(this).attr('data');
            self.approvePo();
        });

        $('#hard_po_approve').click(function() {
            self.docId = $(this).attr('data');
            self.hardPoApprove();
        });


        $('#return_document').click(function() {
            self.docId = $(this).attr('data');
            self.returnPo();
        });

        $('#print_document').click(function() {
            var docId = $(this).attr('data');
            self.docId = docId;
            $.ajax({
                url: "/po/setdocidtoprintdocument",
                data: {docId: docId},
                type: "POST",
                success: function(msg) {
                    var url = '/po/printdocument';
                    window.open(url, 'popup', 'toolbar=no, menubar=no, status=no, location=no, resizable=no, width=640, height=600');
                }
            });
        });

        $('#PO_Trkng_Pmt_Amt').blur(function() {
            var value = $(this).val();
            self.checkFloatType($(this), value);
        });

        var dpSettings = {
            dateFormat: "mm/dd/yy"
        }
        $('#PO_Trkng_Inv_Date').datepicker(dpSettings);

        $('#add_po_trak').click(function() {
            $('#po_tracking_form').submit();
        });

        setTimeout(function() {
            $('#progress_line').animate({width: parseInt($('#progress_line').data('width')) +'%'},1000,'easeOutExpo');
        },200);

        $('.po_track_note, .po_track_note_content').click(function() {
            $('#po_track_note_content').hide();
            $('#po_track_note_input_block').show();
            $('#po_track_note_input').focus();
        });

        $('#po_track_note_input').blur(function() {
            self.updatePOTrackingNote($(this));
        });

        var po_view = new DocumentView('#tab1', '#tab1_block', '#w9_detail_block1', 735, 45, 10);
        var back_up_vew = new DocumentView('#tab2', '#tab2_block', '#detail_block2', 735, 45, 10);

        $('#open_ap_details_box').click(function() {
            $('.w9_details').click();
        });

        $('.w9_details').click(function() {

            if ($(this).css('cursor') =='pointer') {

                var po_id = $("#doc_id").data('id');
                $.ajax({
                    //url: '/dataentry/AjaxPoDataEntry',
                    url: '/po/GetPOCreationForm',
                    data: {po_id: po_id },
                    type: 'POST',
                    success: function(html){
                        $('#dataentry_block').html(html);
                        show_modal_box('#dataentry_block', 690, 50);
                    }
                });
            }

        });

        $('#send_document_by_email').click(function() {
             if($(this).hasClass('button')){
                show_modal_box('#askemailbox');
                var doc_id = $(this).attr('data');
                self.doc_id = doc_id;
             }
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
                    url: "/documents/SendDocumentByEmail",
                    data: {email: email,
                           doc_id: self.doc_id
                          },
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


        $("#doc_to_user_email" ).autocomplete({
            source: function( request, response ) {
                $.ajax({
                    url: "/usersAnswers/GetEmails",
                    timeout:700,
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

        $('#po_tracking_list_block .in_place_edit').dblclick(function () {
            self.revertPreviousEditing();
            var po_trk_id = $(this).data('id');
            self.prepareInPlaceInput(po_trk_id, $(this));
        });

        $('#po_tracking_list_block').on('click','.save_track_button',function () {
            var item_id =  $(this).data('id');
            var row = $(this).parent().parent();
            self.savePoTrackItem(item_id,row);
        });


        $('#recreate_document').click(function () {
            var doc_id = $(this).data('id');
            $.ajax({
                url: "/po/recreate",
                data: {doc_id: doc_id},
                async: false,
                type: "POST",
                success: function() {
                    window.location = '/po/detail';
                }
            });
        });



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
     * Approve PO and reload page
     */
    approvePo: function() {
        var self = this;
        if (self.docId != 0) {
            $.ajax({
                url: "/po/approvepos",
                data: {docs: self.docId},
                async: false,
                type: "POST",
                success: function() {
                    window.location = '/po/detail';
                }
            });
        }
    },
    /**
     * Approve PO skipping the queue
     */
    hardPoApprove: function() {
        var self = this;
        if (self.docId != 0) {
            $.ajax({
                url: "/po/HardPosApprove",
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
     * Return PO to previous approver
     */
    returnPo: function() {
        var self = this;
        if (self.docId != 0) {
            $.ajax({
                url: "/po/returnpo",
                data: {doc: self.docId},
                async: false,
                type: "POST",
                success: function() {
                    window.location = '/po/detail';
                }
            });
        }
    },

    /**
     * Update PO tracking Note
     * @param noteInput
     */
    updatePOTrackingNote: function(noteInput) {
        var note = noteInput.val();
        var poId = noteInput.data('id');
        $.ajax({
            url: "/po/updatePOTrackingNote",
            data: {note: note, poId: poId},
            type: "POST",
            success: function() {
                note = note.replace(/\</g, "&lt;");
                note = note.replace(/\>/g, "&gt;");
                note = note.replace(/\n/g, "<br />");
                $('#po_track_note_input_block').hide();
                $('#po_track_note_content').html(note).show();
            }
        });
    },

    prepareInPlaceInput: function(po_trk_id, row) {
        var self = this;
        var order_num = parseInt(row.find("td:eq(0)").html());

        $.ajax({
            url: "/po/getinplaceinput",
            data: {
                po_trk_id: po_trk_id
            },
            type: "POST",
            success: function(msg) {
                if (msg != '') {
                    var obj = JSON.parse(msg);
                    console.log('result',obj);
                    row.find("td:eq(0)").html('<a href="#" class="button_small save_track_button" id="save_po_trak" data-order-num="'+order_num+'" data-id="'+po_trk_id+'">Save</a>');
                    row.find("td:eq(1)").html(obj[0].PO_Trkng_Desc);
                    row.find("td:eq(2)").html(obj[1].PO_Trkng_Inv_Date);
                    row.find("td:eq(3)").html(obj[2].PO_Trkng_Inv_Number);
                    row.find("td:eq(4)").html(obj[3].PO_Trkng_Pmt_Amt);
                    //cell.find("td:eq(1)").html("bla bla");
                    /*cell.attr('data-editing', '1');
                    cell.html(msg);
                    input = cell.find('input');
                    input.focus();
                    input.blur(function() {
                        var value = $(this).val();
                        self.updateCellValue(coaID, cellType, value, cell);
                    });*/
                }
            }
        });
    },

    revertPreviousEditing: function () {
        var self = this;
        var rows = $('tr.in_place_edit');
        var row = null;
        var temp_value = null;
        console.log(rows);
        rows.each(function () {
            row = $(this);
            var order_num = row.find('td:eq(0)').data('initial-value');
            row.find('td:eq(0)').html(order_num);
            temp_value = row.find('td:eq(1)').data('initial-value');
            row.find('td:eq(1)').html(temp_value);
            temp_value = row.find('td:eq(2)').data('initial-value');
            row.find('td:eq(2)').html(temp_value);
            temp_value = row.find('td:eq(1)').data('initial-value');
            row.find('td:eq(1)').html(temp_value);


        });
    },

    savePoTrackItem : function (po_trk_id, row) {

        var self = this;

        var PO_Trkng_Desc = row.find("td:eq(1) input").val();
        var PO_Trkng_Inv_Date = row.find("td:eq(2) input").val();
        var PO_Trkng_Inv_Number = row.find("td:eq(3) input").val();
        var PO_Trkng_Pmt_Amt = row.find("td:eq(4) input").val();

        $.ajax({
            url: "/po/savepotrack",
            data: {
                po_trk_id: po_trk_id,
                PO_Trkng_Pmt_Amt:PO_Trkng_Pmt_Amt,
                PO_Trkng_Inv_Number:PO_Trkng_Inv_Number,
                PO_Trkng_Inv_Date:PO_Trkng_Inv_Date,
                PO_Trkng_Desc:PO_Trkng_Desc

            },
            type: "POST",
            success: function(msg) {
                window.location = '/po/detail';
               // self.revertPreviousEditing();
            }
        });
    }

});