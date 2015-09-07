/**
 * Created by lee on 4/14/15.
 */
$(document).ready(function() {


    var dpSettings = {
        dateFormat: "mm/dd/yy"
    }

        $('#dataentry_block').on ('focus','#Payments_Payment_Check_Date',function() {
            $(this).datepicker({
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
            $(this).mask("99/99/9999");
        });




    $('#dataentry_block').on('blur','#Payments_Payment_Check_Date',function() {
        $("#Payments_Account_Num_ID").focus();
    });


    $('#dataentry_block').on('blur',"#Payments_Vendor_ID",function() {
        $("#Payments_Payment_Check_Number").focus();
    });

    $('#dataentry_block').on('click','#add_invoice',function() {
        var countItems = $('#attached_invoices tbody tr').length;
        var newItem = countItems +1;
        var newLine = $("<tr><td><input type='text' class='invoice_number' name='Invoice[" + newItem + "][Invoice_Number]' value=''></td><td><input type='text' class='dollar_fields' name='Invoice[" + newItem + "][Invoice_Amount]' value=''></td></tr>");
        newLine.appendTo('#attached_invoices tbody');
        $('#attached_invoices_block').scrollTop(9999);
        newLine.effect('highlight');
        newLine.find('td').effect('highlight');
        newLine.find('input').effect('highlight');
        reinitInvoicesTable();
    });

    $('#dataentry_block').on('click','#remove_invoice',function() {
        var countItems = $('#attached_invoices tbody tr').length;
        if (countItems > 6) {
            var item = $('#attached_invoices tbody tr:last-child');
            item.remove();
        }
    });


    $('#dataentry_block').on('blur','#Payments_Payment_Amount',function() {
        recalcInvPaym();
    });

    $('#dataentry_block').on('blur','#attached_invoices tr input',function() {
        recalcInvPaym();
    });

    function recalcInvPaym () {
        var total_field = $('#Payments_Payment_Amount');
        var amount = parseFloat(total_field.val());

        var invoices_sum = 0;
        $('#attached_invoices tr td:last-child input').each(function() {
            var val = parseFloat($(this).val());
            if (!isNaN(val) && val > 0) {
                invoices_sum += val;

            }
        });

        invoices_sum = invoices_sum.toFixed(2);


        if (invoices_sum != amount && invoices_sum!=0 ) {
            total_field.css("background",'#F3F6FA');

            total_field.parent().find(".warningMessage").html("Invoices sum: "+invoices_sum);
        } else {
            total_field.css("background",'white');
            total_field.parent().find(".warningMessage").html('');
        }
    }

    var timeout = false;

    $('#dataentry_block').on('keydown','#narrow_vendors_list',function() {
        clearTimeout(timeout);
        var input = $(this);
        timeout = setTimeout(function() {
            var docId = $('#Payments_Document_ID').val();
            var query = input.val();
            $('#Payments_Vendor_ID').attr('disabled', true);
            $.ajax({
                url: "/dataentry/getvendorslist",
                data: {query: query, docId: docId},
                type: "POST",
                success: function(msg){
                    $('#Payments_Vendor_ID').html(msg).attr('disabled', false);
                }
            });
        }, 500);
    });

    $('#dataentry_block').on('click','#invalidInvoices',function() {
        $('#invalidInvoicesTopMess').show();
    });

    var doc_id = $('#Payments_Document_ID').val();
    var vendor_id = $('#Payments_Vendor_ID option:selected').val();

    function reinitInvoicesTable(){

        $(".invoice_number").unbind('autocomplete');
        $(".invoice_number").unbind('focus');

        $(".invoice_number" ).autocomplete({

            source: function( request, response ) {
                $.ajax({
                    url: "/payments/getFreeAps",
                    dataType: "json",
                    type:"POST",
                    data: {
                        inv_number: request.term,
                        doc_id: doc_id,
                        vendor_id: vendor_id
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
            minLength:0,

            select: function( event, ui ) {


                var arr = ui.item.value.split(' : $');
                $(this).val( arr[0] );
                $(this).parent().parent().find('td:last-child input').val(arr[1]);

                recalcInvPaym();

                return false;
            },

            open: function() {
                $('.ui-menu').width(250);
                $( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
            },

            close: function() {
                $( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
            }

        }).bind('focus', function () {
            doc_id = $('#Payments_Document_ID').val();
            vendor_id = $('#Payments_Vendor_ID option:selected').val();
            $(this).autocomplete("search");
        });

    }
    reinitInvoicesTable();

    $('#dataentry_block').on('blur','#Payments_Payment_Check_Number',function() {
        $("#payment_number_warning").html('');
        var pattern = /[^0-9]+/g;
        var num_val = $(this).val();
        if (pattern.test(num_val)) {
            $(this).val('');
            $(this).parent().find(".errorMessage").html("Only digits");
            $(this).parent().find(".errorMessage").show();
            $(this).parent().find(".errorMessage").fadeOut(1500);
            $(this).focus();
        } else {
            $.ajax({
                url: "/payments/checkPaymentNumber",
                data:{
                    num: $(this).val()
                },
                dataType: "json",
                type:"POST",


                success: function( data ){

                    if (data > 0 ){
                        $("#payment_number_warning").html('This payment number has already been entered.');
                        $("#payment_number_warning").show();
                    }

                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log(xhr.status);
                    console.log(thrownError);
                }

            });
        }

    });


    $('#dialogmodal a.button').click(function(event){
        //to differentiate this action from others dialogs show we will check data-id
        if ($(this).data('id')=='confirm') {
            $("#payment_number_warning").html('');
            $('#payment_data_entry_form').submit();
        }

    });

    $('#dataentry_block').on('click','#submit_payment_form',function (event){
        event.preventDefault();
        if ($("#payment_number_warning").html()===''){
            $('#payment_data_entry_form').submit();
        } else {
            $('#dialogmodal a.button').attr('data-id','confirm');
            show_dialog('This payment number has already been entered. Do you want to continue?', 500);
        }
    });

    $('#dataentry_block').on('click','#submit_ajax_pay_form',function (event){

            event.preventDefault();

            $('#data_entry_left').scrollTop(0).prepend("<div class='loadinng_mask' style='width: 330px;'></div>");

            $.ajax({
                type: 'POST',
                url: '/dataentry/AjaxPaymFromDetail',
                data: $('#payment_data_entry_form').serialize(),
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
    });

    $('#dataentry_block').on('change','#Payments_Void',function () {
        if ($(this).attr('checked')) {

            $('#Payments_Payment_Amount').val('0').prop('readonly',true);
            $('#attached_invoices input').each(function () {
                $(this).prop('readonly',true);
            });
        } else {
            $('#Payments_Payment_Amount').val('').prop('readonly',false);
            $('#attached_invoices input').each(function () {
                $(this).prop('readonly',false);
            });
        }
    });


});