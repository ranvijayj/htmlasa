function APCreate() {
    this.init();
    this.initBackUp();
}

//APCreate.prototype = $.extend(DocCreate.prototype, {
APCreate.prototype = {
    /**
     * Date settings
     */
    dpSettings: {
        dateFormat: "mm/dd/yy"
    },
    description:null,
    /*
     * Initialize method
     */
    init: function() {
        var self = this;

        self.page = 'ap';

        $('#Aps_Vendor_ID').change(function() {
            var vendorId = $(this).val();
            self.updateVendorInfoBlock(vendorId);
            self.checkVendorW9(vendorId);
        });

        $('input.float_type').blur(function() {
            var value = $(this).val();
            self.checkFloatType($(this), value);
            self.updateTotals();
        });

        $('#Aps_Invoice_Reference').blur(function() {
            var value = $(this).val();
            if (value != '' ) {
                self.description=value;
                $('#po_dists tr td input.dist_descriptions:first').val(self.description);
                self.updateTotals();
            }

        });

        $('#file_upload_button').click(function () {
            $('#fileupload').trigger('click');
        });

        /*$('#add_dist').click(function() {
            var countItems = $('#po_dists tbody tr').length;
            var newItem = countItems +1;
            var newLine = $('<tr><td class="width120">' +
                                    '<span>' +
                                        '<input type="text" class="GL_Code" data-short-hand="" name="GlDistDetails['+ newItem +'][GL_Dist_Detail_COA_Acct_Number]" value="">' +
                                    '</span>' +
                '                 </td>' +
                '                 <td class="width65">' +
                '                   <span>' +
                '                       <input type="text" class="float_type dists_amounts" name="GlDistDetails['+ newItem +'][GL_Dist_Detail_Amt]" value="">' +
                '                   </span>' +
                '                 </td>' +
                '                 <td>' +
                '                   <span>' +
                '                       <input type="text" class="dist_descriptions" name="GlDistDetails['+ newItem +'][GL_Dist_Detail_Desc]" value="">' +
                '                   </span>' +
                '                 </td>' +
                '           </tr>');
            newLine.find('input.float_type').blur(function() {
                var value = $(this).val();
                self.checkFloatType($(this), value);
                self.updateTotals();
            });

            newLine.appendTo('#po_dists tbody');
            $('#ap_dists_block').scrollTop(9999);
            newLine.effect('highlight');
            newLine.find('td').effect('highlight');
            newLine.find('span').effect('highlight');
            newLine.find('input').effect('highlight');

        });*/

        $('#remove_dist').click(function() {
            var countItems = $('#po_dists tbody tr').length;
            if (countItems > 6) {
                var item = $('#po_dists tbody tr:last-child');
                item.remove();
            }
        });

        $('#save_ap').click(function() {
                if($(this).hasClass('button')){
                    pb=new ProgressBar();
                    pb.setValue(50);
                    //pb.done();

                    //disable other 3 buttons
                    $('#create_check_rq').removeClass('button').addClass('not_active_button');
                    $('#send_to_approve').removeClass('button').addClass('not_active_button');
                    $('#fileupload').prop('disabled','disabled');
                    $('.fileinput-button').prop('disabled','disabled');

                    $('#po_creating_form').submit();
                }
        });

        $('#create_check_rq').click(function() {
                if($(this).hasClass('button')){
                        $('#save_ap').removeClass('button').addClass('not_active_button');
                        $('#send_to_approve').removeClass('button').addClass('not_active_button');
                        $('#fileupload').prop('disabled','disabled');
                        $('#fileinput-button').prop('disabled','disabled');
                        window.location= '/ap/create';

                }
        });


        $('#send_to_approve').click(function() {
            if($(this).hasClass('button')){
                    //disable other 3 buttons
                    $('#create_check_rq').removeClass('button').addClass('not_active_button');
                    $('#save_ap').removeClass('button').addClass('not_active_button');
                    $('#fileupload').prop('disabled','disabled');
                    $('#fileinput-button').prop('disabled','disabled');

                    pb=new ProgressBar();
                    pb.setCaption("Sending AP to approval");
                    pb.indeterminate();
                    //pb.stepTo(10);
                    var apId = $('#save_ap').attr('data');
                    //pb.stepTo(50);
                    $.ajax({
                        url: "/ap/addsessionpointertoapprove",
                        data: {apId: apId},
                        type: "POST",
                        async: false,
                        success: function() {

                        }
                    });

                    $('#po_creating_form').submit();
                    //pb.done();

            }
        });

        $('#purchase_rental_p').click(function() {
            $('#CkReqDetails_Rental_Begin').attr('disabled', true).val('');
            $('#CkReqDetails_Rental_End').attr('disabled', true).val('');
        });

        $('#purchase_rental_r').click(function() {
            $('#CkReqDetails_Rental_Begin').attr('disabled', false);
            $('#CkReqDetails_Rental_End').attr('disabled', false);
        });

        $('#CkReqDetails_PO_Number').blur(function() {
            self.checkIntegerType($(this), $(this).val());
        });

        $('#Aps_Invoice_Due_Date').datepicker({
            dateFormat: "mm/dd/yy",
            onClose: function(selectedDate){
                //check if the year part of the date correct
                var arr = selectedDate.split('/');
                if(isNaN(arr[2])){
                    //if not correct changing it 2xxx format
                    arr[2]=arr[2].replace(/_/g, '');
                    var res =parseInt(arr[2],10);
                    if( res<2000 ) { res=res+2000; arr[2]=res;}
                    var new_date=arr[0]+'/'+arr[1]+'/'+arr[2];

                    $(this).datepicker( "setDate", new_date );
                }
            }
        });


        $("#Aps_Invoice_Due_Date").mask("99/99/9999");


        $('#CkReqDetails_Rental_Begin').datepicker(self.dpSettings);
        $('#CkReqDetails_Rental_End').datepicker(self.dpSettings);

        $('.disable_uploading').click(function() {
            var message = $(this).data('message');
            $('#dialogmodal a').attr('href', '/myaccount?tab=service');
            show_dialog(message, 540);
        });

        $('.mark_as_void').on('click',function(event) {
            event.stopPropagation();

            var id = $(this).data('id');

            $.ajax({
                url: '/ap/markasvoid',
                data: {
                    ap_id: id
                },
                async: true,
                type: "POST",
                success: function() {

                    window.location = '/ap/create';
                }
            });

        });

        $('.preview_pdf').click(function() {
            self.previewAp();
        });

    },


    afterUpload: function(html) {
        self.validFedId =  false;
        self.existingFedId =  false;
        self.existingCompanyName =  '';
        self.lastDocType =  "BU";
        $('#additional_fields_block').html(html);
        close_alert();
        setTimeout(function() {
            show_modal_box('#additional_fields_block', 725, 50);
        }, 250);
        //self.initUploadBox();
        //this.enable();

    },

    /**
     * Update total amounts
     */
    updateTotals: function() {
        var self = this;

        var total = parseFloat($('#Aps_Invoice_Amount').val());

        if (isNaN(total)) {
            $('#po_dists tr td input.dists_amounts').val('');
        }

       var dists_sum = 0;
        $('#po_dists tr td input.dists_amounts').each(function() {
            var val = parseFloat($(this).val());
            if (!isNaN(val) && val > 0) {
                dists_sum += val;
            }
        });

        if (!isNaN(total) && dists_sum != total) {
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
     * Check Vendor W9 and show or hide 1099 box number drop down
     * @param vendorId
     */
    checkVendorW9: function(vendorId) {
        var self = this;
        $.ajax({
            url: "/" + self.page + "/checkvendorw9",
            data: {vendorId: vendorId},
            type: "POST",
            success: function(msg) {
                if (msg == '1') {
                    $('#1099type').show();
                    $('#Aps_Detail_1099_Box_Number').val(7);
                } else {
                    $('#1099type').hide();
                    $('#Aps_Detail_1099_Box_Number').val(0);
                }
            }
        });
    },

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

    previewAp: function () {

        //1) generate pdf according form data
        $.ajax({
            url: '/ap/createforpreview',

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