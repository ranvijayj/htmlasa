function POCreate() {
    this.init();
    this.initBackUp();
}

//POCreate.prototype = $.extend(DocCreate.prototype, {
POCreate.prototype = {
    /*
     * Initialize method
     */
    pb:null,

    init: function() {

        var self = this;

        self.page = 'po';

        $('#Pos_Vendor_ID').change(function() {
            var vendorId = $(this).val();
            self.updateVendorInfoBlock(vendorId);
        });


        $('.mark_as_void').on('click',function(event) {

            var poId = $(this).data('id');
            self.markPoAsVoid(poId);
        });

        $('#file_upload_button').click(function () {
            $('#fileupload').trigger('click');
        });

        $('#Pos_Payment_Type').change(function() {
            var paymentType = $(this).val();
            if (paymentType != 'CC') {
                $('#Pos_PO_Card_Last_4_Digits').val('').attr('disabled', true);
            } else {
                $('#Pos_PO_Card_Last_4_Digits').attr('disabled', false);
            }
        });

        $('input.qty_cell').blur(function() {
            var value = $(this).val();
            self.checkIntegerType($(this), value);
            self.updateTotals();
        });

        $('#po_dists').on('blur','input.dollar_fields',function() {
            var value = $(this).val();
            self.checkFloatType($(this), value);
            self.updateTotals();
        });

        $('#po_dists').on('blur','input.float_type',function() {
            var value = $(this).val();
            self.checkFloatType($(this), value);
            self.updateTotals();
        });

        $('#total_fields').on('blur','input',function() {
            var value = $(this).val();
            self.checkFloatType($(this), value);
            self.updateTotals();
        });


        $('#po_descr_details').on('blur','input.dollar_fields',function() {
            var value = $(this).val();
            self.checkFloatType($(this), value);
            self.updateTotals();
        });

        $('#add_desc_det').click(function() {
            var countItems = $('#po_descr_details tbody tr').length;
            if (countItems <= 50) {
                var newItem = countItems +1;
                var newLine = $('<tr><td class="width60"><input class="qty_cell" type="text" name="PoDescDetail['+ newItem +'][PO_Desc_Qty]" value=""></td><td class="width240"><input type="text" maxlength="255" name="PoDescDetail['+ newItem +'][PO_Desc_Desc]" value=""></td><td class="width60"><input type="radio" checked="checked" name="PoDescDetail['+ newItem +'][PO_Desc_Purchase_Rental]" value="0"></td><td class="width55"><input type="radio" name="PoDescDetail['+ newItem +'][PO_Desc_Purchase_Rental]" value="1"></td><td class="width55"><input type="text" maxlength="20" name="PoDescDetail['+ newItem +'][PO_Desc_Budget_Line_Num]" value=""></td><td><input class="dollar_fields" type="text" name="PoDescDetail['+ newItem +'][PO_Desc_Amount]" value=""></td></tr>');

                // bind events
                newLine.find('input.qty_cell').blur(function() {
                    var value = $(this).val();
                    self.checkIntegerType($(this), value);
                    self.updateTotals();
                });
                newLine.find('input.dollar_fields').blur(function() {
                    var value = $(this).val();
                    self.checkFloatType($(this), value);
                    self.updateTotals();
                });

                newLine.appendTo('#po_descr_details tbody');
                $('#po_descr_details_block').scrollTop(9999);
                newLine.effect('highlight');
                newLine.find('td').effect('highlight');
                newLine.find('input').effect('highlight');
            }

        });

        $('#remove_desc_det').click(function() {
            var countItems = $('#po_descr_details tbody tr').length;
            if (countItems > 8) {
                var item = $('#po_descr_details tbody tr:last-child');
                item.remove();
                self.updateTotals();
            }
        });

        /*$('#add_dist').click(function() {
            var countItems = $('#po_dists tbody tr').length;
            var newItem = countItems +1;
            var newLine = $('<tr><td class="width120"><span><input type="text" name="PoDists['+ newItem +'][PO_Dists_GL_Code]" value=""></span></td><td class="width50"><span><input type="text" class="float_type dists_amounts" name="PoDists['+ newItem +'][PO_Dists_Amount]" value=""></span></td><td><span><input class="dist_descriptions" type="text" name="PoDists['+ newItem +'][PO_Dists_Description]" value=""></span></td></tr>');

            newLine.find('input.float_type').blur(function() {
                var value = $(this).val();
                self.checkFloatType($(this), value);
                self.updateTotals();
            });

            newLine.appendTo('#po_dists tbody');
            $('#po_dists_block').scrollTop(9999);
            newLine.effect('highlight');
            newLine.find('td').effect('highlight');
            newLine.find('input').effect('highlight');
        });*/

        $('#remove_dist').click(function() {
            var countItems = $('#po_dists tbody tr').length;
            if (countItems > 6) {
                var item = $('#po_dists tbody tr:last-child');
                item.remove();
            }
        });




        $('#save_po').click(function() {
            if($(this).hasClass('button')){
                pb=new ProgressBar();
                pb.setCaption("PO creation");
                pb.setValue(50);
                pb.done();

                //disable other 3 buttons
                $('#create_po').removeClass('button').addClass('not_active_button');
                $('#send_to_approve').removeClass('button').addClass('not_active_button');
                $('#fileupload').prop('disabled','disabled');
                $('.fileinput-button').prop('disabled','disabled');

                $('#po_creating_form').submit();
            }
        });


        $('#create_po').click(function() {
           if($(this).hasClass('button')){
                $('#save_po').removeClass('button').addClass('not_active_button');
                $('#send_to_approve').removeClass('button').addClass('not_active_button');
                $('#fileupload').prop('disabled','disabled');
                $('#fileinput-button').prop('disabled','disabled');
               window.location= '/po/create';
           }

        });

        $('#send_to_approve').click(function() {

            if($(this).hasClass('button')){
                //disable other 3 buttons
                $('#create_po').removeClass('button').addClass('not_active_button');
                $('#save_po').removeClass('button').addClass('not_active_button');
                $('#fileupload').prop('disabled','disabled');
                $('#fileinput-button').prop('disabled','disabled');

                pb=new ProgressBar();
                pb.setCaption("PO approval");
                pb.indeterminate();
                //pb.stepTo(10);
                var poId = $('#save_po').attr('data');
                //pb.stepTo(30);
                $.ajax({
                    url: "/po/addsessionpointertoapprove",
                    data: {poId: poId},
                    type: "POST",
                    async: false,
                    success: function() {
                        //pb.stepTo(50);

                    }
                });
                //pb.done();
                $('#po_creating_form').submit();
            }
        });

        $('#po_descr_details tr:first-child td input.po_det_descriptions').blur(function() {
            var detDesc = $(this).val();
            if (detDesc != '') {
                $('#po_dists tr:first-child td input.dist_descriptions').val(detDesc);
            }
        });

        $('.disable_uploading').click(function() {
            var message = $(this).data('message');
            $('#dialogmodal a').attr('href', '/myaccount?tab=service');
            show_dialog(message, 540);
        });

        $('.preview_pdf').click(function() {
            self.previewPo();
        });

        $('#save_po_create_form').click(function (event) {
            event.preventDefault();

            $('#data_entry_left').scrollTop(0).prepend("<div class='loadinng_mask' style='width: 330px;'></div>");

            $.ajax({
                type: 'POST',
                url: '/dataentry/AjaxPoFromDetailFull',
                data: $('#po_creating_form').serialize(),
                dataType: 'json',
                success: function(data){
                    $('#dataentry_block').html(data.html);
                    $('.cancelbutton').click(function(){
                        window.location =$('#return_url').val();
                    });

                    if(data.saved) {
                        $('.cancelbutton').trigger('click');
                    }
                }
            });

            event.preventDefault();
        });





    },

    /**
     * Update total amounts
     */
    updateTotals: function() {
        var subtotal = 0;
        var total = 0;
        var value = 0;
        var qty = 0;
        $('#po_descr_details tr').each(function() {
            qty = parseInt($(this).find('.qty_cell').val());
            value = parseFloat($(this).find('.dollar_fields').val());
            if (!isNaN(value) && value > 0 && !isNaN(qty) && qty > 0) {
                subtotal += value*qty;
                total += value*qty;
            }
        });

        $('#total_fields .dollar_fields').each(function() {
            value = parseFloat($(this).val());
            if (!isNaN(value) && value > 0) {
                total += value;
            }
        });

        $('#Pos_PO_Subtotal').val(subtotal.toFixed(2));
        $('#Pos_PO_Total').val(total.toFixed(2));

        var dists_sum = 0;
        $('#po_dists tr td input.dists_amounts').each(function() {
            var val = parseFloat($(this).val());
            if (!isNaN(val) && val > 0) {
                dists_sum += val;
            }
        });

        if (dists_sum != total) {
            var currentVal = parseFloat($('#po_dists tr:first-child td input.dists_amounts').val());
            if (isNaN(currentVal)) {
                $('#po_dists tr:first-child td input.dists_amounts').val(total.toFixed(2));
            } else if ((currentVal + (total - dists_sum)) < 0) {
                $('#po_dists tr td input.dists_amounts').val('');
                $('#po_dists tr:first-child td input.dists_amounts').val(total.toFixed(2));
            } else {
                $('#po_dists tr:first-child td input.dists_amounts').val((currentVal + (total - dists_sum)).toFixed(2));
            }
        }
    },

    /**
     * these functions came from doc_create
     */

    /**
     * Initialize Back Up
     */
    initBackUp: function() {
        var back_up_view = new DocumentView('#tab2', '#tab2_block', '#detail_block2', 735, 45, 10);
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



    markPoAsVoid:function(poId){

        $.ajax({
            url: '/po/markasvoid',
            data: {
                po_id: poId
            },
            async: true,
            type: "POST",
            success: function() {

               window.location = '/po/create';
            }
        });

    },

    previewPo: function () {

        //1) generate pdf according form data
        $.ajax({
            url: '/po/createforpreview',

            data: $('#po_creating_form').serialize(),

            async: true,
            type: "POST",
            success: function(cache_file_id) {

                var url=  '/documents/PreviewFile?file_id='+cache_file_id;
                window.open(url,'_blank');

            }
        });
    }

}
//);