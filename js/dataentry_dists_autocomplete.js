/**
 * Created by lee on 11/11/14.
 */

function DistsHandling(){
    var self = this;
    var enable_manual_coding = $('#COA_Allow_Manual_Coding').data('id');
    console.log("Manual coding value is "+enable_manual_coding);

    //common functions initialisation
    this.init();

    if(enable_manual_coding==1){
        this.initReadWrite();
    } else {
        this.initReadOnly();
    }

}

DistsHandling.prototype = {

 temp_val:null,

init:function (){
    var self = this;

    //adding deleting invoices to table
    //for dataentry page
    $('#add_invoice').click(function() {
        var countItems = $('#attached_invoices tbody tr').length;
        var newItem = countItems +1;
        var newLine = $("<tr><td><input type='text' class='GL_Code' name='Dist[" + newItem + "][PO_Dists_GL_Code]' value=''></td><td><input type='text' class='gl_amount float_type dollar_fields' name='Dist[" + newItem + "][PO_Dists_Amount]' value=''></td><td><input type='text' name='Dist[" + newItem + "][PO_Dists_Description]' value='' maxlenght='125'></td></tr>");
        newLine.appendTo('#attached_invoices tbody');
        $('#attached_dists_block').scrollTop(9999);
        newLine.effect('highlight');
        newLine.find('td').effect('highlight');
        newLine.find('input').effect('highlight');
        self.reinitDistTable();
    });

    //for ap create page
    $('#add_dist').click(function() {
        var countItems = $('#po_dists tbody tr').length;
        var newItem = countItems;
        var newLine = $("<tr><td><input type='text' class='GL_Code' name='GlDistDetails[" + newItem + "][GL_Dist_Detail_COA_Acct_Number]' value=''></td><td><input type='text' class='gl_amount float_type' name='GlDistDetails[" + newItem + "][GL_Dist_Detail_Amt]' value=''></td><td><input type='text' name='GlDistDetails[" + newItem + "][GL_Dist_Detail_Desc]' value='' maxlenght='125' ></td></tr>");
        newLine.appendTo('#po_dists tbody');
        $('#attached_dists_block').scrollTop(9999);
        newLine.effect('highlight');
        newLine.find('td').effect('highlight');
        newLine.find('input').effect('highlight');
        self.reinitDistTable();
    });

    //for po create page
    $('#add_dist_po').click(function() {
        var countItems = $('#po_dists tbody tr').length;
        var newItem = countItems;
        var newLine = $("<tr><td><input type='text' class='GL_Code' name='PoDists[" + newItem + "][PO_Dists_GL_Code]' value=''></td><td><input type='text' class='dists_amounts float_type' name='PoDists[" + newItem + "][PO_Dists_Amount]' value=''></td><td><input type='text' name='PoDists[" + newItem + "][PO_Dists_Description]' value='' maxlenght='125'></td></tr>");
        newLine.appendTo('#po_dists tbody');
        $('#attached_dists_block').scrollTop(9999);
        newLine.effect('highlight');
        newLine.find('td').effect('highlight');
        newLine.find('input').effect('highlight');
        self.reinitDistTable();
    });


    //for dataentry page
    $('#remove_invoice').click(function() {
        var countItems = $('#attached_invoices tbody tr').length;
        if (countItems > 4) {
            var item = $('#attached_invoices tbody tr:last-child');
            item.remove();
        }
    });

    $('.GL_Code').focus(function () {
        var short_hand=$(this).attr('data-short-hand');
        self.temp_val = $(this).val();
        if (short_hand!='') {$(this).val(short_hand);}
    });

    $('.GL_Code').blur(function () {
        self.temp_val = $(this).val();
    });

    $('input.float_type').blur(function() {
        var value = $(this).val();
        self.checkFloatType($(this), value);
        self.updateTotals();
    });

    /*for popup data entry form from detail view PO and AP*/

    $('#data_entry_left').on('blur','.gl_amount.dollar_fields',function() {
        var value = $(this).val();
        self.checkFloatType($(this), value);
        self.updateTotals();
    });

    //$('$data_entry_left').on('blur',)
},


initReadWrite: function () {
    var self = this;
    $(".GL_Code" ).autocomplete({
        source: function( request, response ) {
            $.ajax({
                url: "/coa/getCoaCodes",
                //timeout:700,
                dataType: "json",
                type:"POST",
                data: {
                    search_string: request.term,
                    doc_id: doc_id,
                    vendor_id: vendor_id
                   // allow_manual:allow_manual
                },

                success: function( data ){

                    /*var i=0;
                     $.each(data,function(key, value){

                     console.log (value.description);
                     result = value.description;
                     //result[i]['description']=value.description;
                     //result[i]['value']=value.value;
                     i++;
                     });
                     console.log (result);*/
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
        doc_id = $('#Payments_Document_ID').val();
        vendor_id = $('#Payments_Vendor_ID option:selected').val();
        $(this).autocomplete("search");
    });


},

initReadOnly:function (){
    var self = this;
    console.log ('Read only initiated');
    $(".GL_Code" ).autocomplete({
        source: function( request, response ) {
            $.ajax({
                url: "/coa/getCoaCodes",
                timeout:700,
                dataType: "json",
                type:"POST",
                data: {
                    search_string: request.term,
                    doc_id: doc_id,
                    vendor_id: vendor_id
                    // allow_manual:allow_manual
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

        change: function( event, ui ) {


            if ( !ui.item ) {
                $(this).val("");
                $(this).parent().parent().find('td:last-child input').val("");

            }
        },

        close: function() {
            $( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
        }

    }).bind('focus', function () {
        doc_id = $('#Payments_Document_ID').val();
        vendor_id = $('#Payments_Vendor_ID option:selected').val();
        $(this).autocomplete("search");
    });


},

reinitDistTable: function (){

    $(".GL_Code").unbind('autocomplete');
    $(".GL_Code").unbind('focus');

    var enable_manual_coding = $('#COA_Allow_Manual_Coding').data('id');


    if(enable_manual_coding){
        this.initReadWrite();
    } else {
        this.initReadOnly();
    }

},

    /**
     * Update total amounts
     */
    updateTotals: function() {
        var self = this;

        //here we calculating total for AP dataentry page and it not defined then fro PO dataentry page;
        var total = parseFloat($('#Aps_Invoice_Amount').val());
        if (isNaN(total)) {
            total = parseFloat($('#Pos_PO_Total').val());
        }


        if (isNaN(total)) {
            $('#attached_invoices tr td input.gl_amount').val('');
        }

        var dists_sum = 0;
        $('#attached_invoices tr td input.gl_amount').each(function() {
            var val = parseFloat($(this).val());
            if (!isNaN(val) && val > 0) {
                dists_sum += val;
            }
        });

        if (!isNaN(total) && dists_sum != total) {
            var currentVal = parseFloat($('#attached_invoices tr:first-child td input.gl_amount').val());
            if (isNaN(currentVal)) {
                $('#attached_invoices tr:first-child td input.gl_amount').val(total.toFixed(2));
            } else if ((currentVal + (total - dists_sum)) < 0) {
                $('#attached_invoices tr td input.gl_amount').val('');
                $('#attached_invoices tr:first-child td input.gl_amount').val(total.toFixed(2));
            } else {
                $('#attached_invoices tr:first-child td input.gl_amount').val((currentVal + (total - dists_sum)).toFixed(2));
            }
        }
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
    }


}