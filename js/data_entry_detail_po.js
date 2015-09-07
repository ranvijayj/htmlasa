
$(document).ready(function() {
    new DataEntryDetail;
    var columns = $("#data_entry_left,#data_entry_right");
    var tallestcolumn = 0;
    var dist_is_active = false;
    columns.each(function() {
            currentHeight = $(this).height();
            if(currentHeight > tallestcolumn)
                tallestcolumn = currentHeight;
        }
    );

    columns.height(tallestcolumn+30);

    var dpSettings = {
        dateFormat: "mm/dd/yy"
    }
    $('#Pos_PO_Date').datepicker({
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

    jQuery(function($){
        $("#Pos_PO_Date").mask("99/99/9999");
    });


    var timeout = false;
    $('#narrow_vendors_list').keydown(function() {
        clearTimeout(timeout);
        var input = $(this);
        timeout = setTimeout(function() {
            var docId = $('#Pos_Document_ID').val();
            var query = input.val();
            $('#Pos_Vendor_ID').attr('disabled', true);
            $.ajax({
                url: "/dataentry/getvendorslist",
                data: {query: query, docId: docId},
                type: "POST",
                success: function(msg){
                    $('#Pos_Vendor_ID').html(msg).attr('disabled', false);
                }
            });
        }, 500);
    });

    $('#remove_invoice').click(function() {
        var countItems = $('#attached_invoices tbody tr').length;
        if (countItems > 4) {
            var item = $('#attached_invoices tbody tr:last-child');
            item.remove();
        }
    });

    $('#Pos_Payment_Type').change(function() {
        var paymentType = $(this).val();
        if (paymentType != 'CC') {
            $('#Pos_PO_Card_Last_4_Digits').val('').attr('disabled', true);
            $('#Pos_PO_Card_Last_4_Digits').parent().find('label span').hide();
        } else {
            $('#Pos_PO_Card_Last_4_Digits').attr('disabled', false);
            $('#Pos_PO_Card_Last_4_Digits').parent().find('label span').show();
        }
    });

    $('#Pos_PO_Subtotal').blur(function() {
        var amount = parseFloat($(this).val());
        if (!isNaN(amount) && amount > 0) {
            $(this).val(amount.toFixed(2));
        } else {
            $(this).val('');
            amount='';
        }
        dist_is_active = $('#dists_enabled').prop('checked');

        if(dist_is_active){
            var invoices_sum = 0;
            $('#attached_invoices tr td input.gl_amount').each(function() {
                var val = parseFloat($(this).val());
                if (!isNaN(val) && val > 0) {
                    invoices_sum += val;
                }
            });

            if (invoices_sum != amount) {
                var currentVal = parseFloat($('#attached_invoices tr:first-child td input.gl_amount').val());
                if (isNaN(currentVal)) {
                    $('#attached_invoices tr:first-child td input.gl_amount').val(amount);
                } else if ((currentVal + (amount - invoices_sum)) < 0) {
                    $('#attached_invoices tr td input.gl_amount').val('');
                    $('#attached_invoices tr:first-child td input.gl_amount').val(amount);
                } else {
                    var temp_result=currentVal + (amount - invoices_sum);
                    if(temp_result==0) {temp_result='';}//in order to not put "zero" in the table
                    $('#attached_invoices tr:first-child td input.gl_amount').val(temp_result);
                }
            }

        }
    });

    $('.dollar_fields').on('blur',function() {
        var total = 0;
        var value = parseFloat($(this).val());

        if (value == 0 || isNaN(value)) {
            $(this).val('');
        }

        $('.dollar_fields.base_fields').each(function() {
            value = parseFloat($(this).val());
            if (!isNaN(value) && value > 0) {
                total += value;

            }
        });

        if (total > 0) {
            $('#Pos_PO_Total').val(total.toFixed(2));
        } else {
            $('#Pos_PO_Total').val('');
        }

        if(dist_is_active){
            var invoices_sum = 0;
            $('#attached_invoices tr td input.gl_amount').each(function() {
                var val = parseFloat($(this).val());
                if (!isNaN(val) && val > 0) {
                    invoices_sum += val;
                }
            });

            if (invoices_sum != total) {
                var currentVal = parseFloat($('#attached_invoices tr:first-child td input.gl_amount').val());
                if (isNaN(currentVal)) {
                    $('#attached_invoices tr:first-child td input.gl_amount').val(total.toFixed(2));
                } else if ((currentVal + (total - invoices_sum)) < 0) {
                    $('#attached_invoices tr td input.gl_amount').val('');
                    $('#attached_invoices tr:first-child td input.gl_amount').val(total.toFixed(2));
                } else {

                    var temp_result=currentVal + (total - invoices_sum);
                    if(temp_result==0) {temp_result='';}//in order to not put "zero" in the table

                    $('#attached_invoices tr:first-child td input.gl_amount').val(temp_result.toFixed(2));
                }
            }
        }

    });


    $('#attached_invoices tr').click(function(){
        if (!$('#dists_enabled').prop('checked')) {$('#dists_enabled').trigger('click');}
    });

    $('#dists_enabled').change(function() {
        if($(this).prop('checked')){
            dist_is_active=true;
            var temp_val=$('#Pos_PO_Total').val();
            if(!isNaN(temp_val) && temp_val!=0) {
                var amount = temp_val;
            } else amount='';

            var invoices_sum = 0;
            $('#attached_invoices tr td input.gl_amount').each(function() {
                var val = parseFloat($(this).val());
                if (!isNaN(val) && val > 0) {
                    invoices_sum += val;
                }
            });
            if (invoices_sum != amount ) {
                var currentVal = parseFloat($('#attached_invoices tr:first-child td input.gl_amount').val());
                if (isNaN(currentVal)) {
                    $('#attached_invoices tr:first-child td input.gl_amount').val(amount);
                } else if ((currentVal + (amount - invoices_sum)) < 0) {
                    $('#attached_invoices tr td input.gl_amount').val('');
                    $('#attached_invoices tr:first-child td input.gl_amount').val(amount);
                } else {
                    $('#attached_invoices tr:first-child td input.gl_amount').val(currentVal + (amount - invoices_sum));
                }
            }
        } else {
            $('#attached_invoices tr td input').each(function() {
                $(this).val('');
            });
            dist_is_active=false;
        }

    });

    $('#save_po_details_form').click(function (event) {
        event.preventDefault();

        $('#data_entry_left').scrollTop(0).prepend("<div class='loadinng_mask' style='width: 330px;'></div>");

        $.ajax({
            type: 'POST',
            url: '/dataentry/AjaxPoFromDetail',
            data: $('#po_data_entry_form').serialize(),
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




});

