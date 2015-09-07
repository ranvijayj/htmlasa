
$(document).ready(function() {
    new DataEntryDetail;

    //variables for date converting
    var inv_date = $('#Aps_Invoice_Date').val();
    var inv_due_date = $('#Aps_Invoice_Due_Date').val();
    var offset = $('#Aps_Invoice_Due_Date').data('term');


    if (inv_date && !inv_due_date) {
          calculateDueDate(inv_date,offset);
    }

    var columns = $("#data_entry_left,#data_entry_right");
    var tallestcolumn = 0;

    var dist_is_active = false;
    if($('#dists_enabled').prop('checked')){
        dist_is_active = true;
    }

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
        $("#Aps_Invoice_Amount").focus();
    }

});

$('#Aps_Invoice_Date').datepicker({
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
        //we have to calculate due date value
        var pattern = /(\d{2}\/\d{2}\/\d{4})|(\d{6})/;
        if (pattern.test($(this).val())) {
            calculateDueDate($(this).val(),offset);
        }
        $("#Aps_Invoice_Due_Date").focus();
    }
});




$("#Aps_Invoice_Due_Date").mask("99/99/9999");


$("#Aps_Invoice_Date").mask("99/99/9999");

function calculateDueDate(date,offset) {
    var initial_date = new Date(date);
    console.log(initial_date);
    console.log("Begin date adding");
    initial_date.setDate(initial_date.getDate()+offset);
    console.log(initial_date);
    var dd = addZero(initial_date.getDate());
    var mm = addZero( initial_date.getMonth()+1);
    var y = initial_date.getFullYear();
    var someFormattedDate = mm + '/'+ dd + '/'+ y;
    console.log(someFormattedDate);
    $("#Aps_Invoice_Due_Date").val(someFormattedDate);
}

 function addZero(i) {
        return (i < 10)? "0" + i: i;
 }

 function StringToDate(str){

        var arr = str.split('/');
        var new_date = new Date();

        new_date.setDate(addZero(arr[1]));
        new_date.setMonth(addZero(arr[0]));
        new_date.setFullYear(addZero(arr[2]));

        return new_date;
 }

var timeout = false;
$('#narrow_vendors_list').keydown(function() {
    clearTimeout(timeout);
    var input = $(this);
    timeout = setTimeout(function() {
    var docId = $('#Aps_Document_ID').val();
    var query = input.val();
    $('#Aps_Vendor_ID').attr('disabled', true);
    $.ajax({
        url: "/dataentry/getvendorslist",
        data: {query: query, docId: docId},
        type: "POST",
        success: function(msg){
            $('#Aps_Vendor_ID').html(msg).attr('disabled', false);
        }
    });
    }, 500);
});



$('#Aps_Invoice_Amount').blur(function() {
    var amount = parseFloat($(this).val());

    amount = amount.toFixed(2);

    if (!isNaN(amount) && amount > 0) {
        $(this).val(amount);
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
                        console.log("Invoises sum <> amount");
                        var currentVal = parseFloat($('#attached_invoices tr:first-child td input.gl_amount').val());
                        if (isNaN(currentVal)) {
                            $('#attached_invoices tr:first-child td input.gl_amount').val(amount);
                        } else if ((currentVal + (amount - invoices_sum)) < 0) {
                            $('#attached_invoices tr td input.gl_amount').val('');
                            $('#attached_invoices tr:first-child td input.gl_amount').val(amount);
                        } else {
                            var temp_result=currentVal + (amount - invoices_sum);
                            if(temp_result==0) {temp_result='';}//in order to not put "zero" in the table
                            $('#attached_invoices tr:first-child td input.gl_amount').val(temp_result.toFixed((2)));
                        }
            }

    }
    });

            $('#Aps_Invoice_Reference').blur(function() {
                var text = $(this).val();
                dist_is_active = $('#dists_enabled').prop('checked');
                if(dist_is_active){
                $('#attached_invoices tr:first-child td input.gl_descript').val(text);
                }
            });



        /**/
        $('#attached_invoices tr').click(function(){
            if (!$('#dists_enabled').prop('checked')) {$('#dists_enabled').trigger('click');}

        });


        $('#dists_enabled').change(function() {
            if($(this).prop('checked')){
                dist_is_active=true;
                var amount;

                var temp_val=parseFloat($('#Aps_Invoice_Amount').val());
                if(!isNaN(temp_val) && temp_val!=0) {
                     amount = temp_val;
                } else amount='';

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
                        $('#attached_invoices tr:first-child td input.gl_amount').val(currentVal + (amount - invoices_sum));
                    }
                }

                var text = $('#Aps_Invoice_Reference').val();
                $('#attached_invoices tr:first-child td input.gl_descript').val(text);
            } else {
                $('#attached_invoices tr td input').each(function() {
                    $(this).val('');
                });
                dist_is_active=false;
            }
        });


        $('#save_ap_details_form').click(function (event) {
            event.preventDefault();
            $('#data_entry_left').scrollTop(0).prepend("<div class='loadinng_mask' style='width: 330px;'></div>");
            $.ajax({
                type: 'POST',
                url: '/dataentry/AjaxApFromDetail',
                data: $('#ap_data_entry_form').serialize(),
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

    new DistsHandling;

});

