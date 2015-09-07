function PaymentsList() {
    this.init();
    this.initTable();
    this.initPayments();
    $('#list_table tbody .cutted_cell').tooltip(this.tooltipOptions);
}

PaymentsList.prototype = $.extend(W9List.prototype, {

    /**
     * Sort parameters
     */
    sortType: 't.Payment_ID',
    sortDirection: 'DESC',

    limit_checkbox_state:false,


    limit:50,
    offset:50,
    label_is_checked:null,

    location:'/payments',
    /**
     * Initialize table head
     */
    initPayments: function()
    {
        var self = this;

        $('#vendor_name_cell_header').click(function() {
            self.clearArrows($(this));
             if (self.sortType == 'Company_Name') {
                 if (self.sortDirection == 'ASC') {
                     self.sortDirection = 'DESC';
                 } else {
                     self.sortDirection = 'ASC';
                 }
             } else {
                 self.sortType = 'Company_Name';
             }
             self.addSortingArrow($(this));
             self.updateList();
        });

        $('#amount_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == 'Payment_Amount') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'Payment_Amount';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });

        $('#check_date_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == 'Payment_Check_Date') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'Payment_Check_Date';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });

        $('#check_number_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == 'Payment_Check_Number') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'Payment_Check_Number';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });
    },

    /**
     * Table initialize method
     */
    initTable: function() {
        var self = this;

        $(".list_checkbox").click(function (event) {
            event.stopPropagation();
            var checkbox = $(this);
            setTimeout(function() {
                if (!checkbox.attr('checked')) {
                    checkbox.parent().parent().css({"backgroundColor":"#fff"});
                    if ($(".list_checkbox:checked").length == 1) {
                        var checked = $(".list_checkbox:checked");
                        checked.each(function() {
                            var row = $(this).parent().parent();
                            var docId = row.attr('id');
                            docId = docId.slice(3);
                            self.getPaymentInfo(docId);
                        });
                    }
                } else {
                    var row = checkbox.parent().parent();
                    row.css({"backgroundColor":"#eee"});
                    var docId = row.attr('id');
                    docId = docId.slice(3);
                    if ($(".list_checkbox:checked").length == 1) {
                        self.getPaymentInfo(docId);
                    }
                }
                self.setCheckedCount();
            }, 10);
        });

        $('#list_table tbody tr').click(function (event) {
            event.stopPropagation();
            $(this).find(".list_checkbox").click();
        });
    },

    /**
     * Updates vendors list
     */
    updateList: function() {


        var self = this;
        var query =  $('#search_field').val();
        var search_option_payment_check_date = $('#search_option_payment_check_date').attr('checked') ? 1 : 0;
        var search_option_payment_check_number = $('#search_option_payment_check_number').attr('checked') ? 1 : 0;
        var search_option_payment_amount = $('#search_option_payment_amount').attr('checked') ? 1 : 0;
        var search_option_invoice_number = $('#search_option_invoice_number').attr('checked') ? 1 : 0;
        var search_option_invoice_amount = $('#search_option_invoice_amount').attr('checked') ? 1 : 0;
        var search_option_invoice_date = $('#search_option_invoice_date').attr('checked') ? 1 : 0;
        var search_option_com_name = $('#search_option_com_name').attr('checked') ? 1 : 0;
        var search_option_fed_id = $('#search_option_fed_id').attr('checked') ? 1 : 0;
        var search_option_addr1 = $('#search_option_addr1').attr('checked') ? 1 : 0;
        var search_option_city = $('#search_option_city').attr('checked') ? 1 : 0;
        var search_option_state = $('#search_option_state').attr('checked') ? 1 : 0;
        var search_option_zip = $('#search_option_zip').attr('checked') ? 1 : 0;

        var search_option_limit = $('#limiter_checkbox').attr('checked') ? 1 : 0;

        var bankAccounts = [];

        var i = 0;
        $('.account_nums_chbx:checked').each(function(){
            bankAccounts[i] = $(this).val();
            i++
        });
        self.loadingMask('#tab1 .table_list_scroll_block');
        $.ajax({
            url: "/payments/getlistbysearchquery",
            data: {
                query: query,
                search_option_payment_check_date: search_option_payment_check_date,
                search_option_payment_check_number: search_option_payment_check_number,
                search_option_payment_amount: search_option_payment_amount,
                search_option_invoice_number: search_option_invoice_number,
                search_option_invoice_amount: search_option_invoice_amount,
                search_option_invoice_date: search_option_invoice_date,
                search_option_com_name: search_option_com_name,
                search_option_fed_id: search_option_fed_id,
                search_option_addr1: search_option_addr1,
                search_option_zip: search_option_zip,
                search_option_city: search_option_city,
                search_option_state: search_option_state,
                search_option_limit:search_option_limit,
                sort_type: self.sortType,
                sort_direction: self.sortDirection,
                bankAccounts: bankAccounts
            },
            type: "POST",
            success: function(msg) {
                $('#list_table tbody').html(msg);
                self.initTable();
                self.setCheckedCount();
                $('#list_table tbody .cutted_cell').tooltip(self.tooltipOptions);
                self.endLoadingMask('#tab1 .table_list_scroll_block');
            }
        });
    },


    appendNextBlockToGrid: function(tableId){
        var self = this;
        self.table_object = $('#list_table tbody');

        self.limit=self.limit+parseInt(self.lastSelectedCount);
        self.offset=self.offset+parseInt(self.lastSelectedCount);
        // self.offset=   $('#items_to_review').data('id')+0;



        var query =  $('#search_field').val();
        var search_option_com_name = $('#search_option_com_name').attr('checked') ? 1 : 0;
        var search_option_to_be_approved = $('#search_option_to_be_approved').attr('checked') ? 1 : 0;
        var search_option_limit = $('#limiter_checkbox').attr('checked') ? 1 : 0;





        var search_option_fed_id = $('#search_option_fed_id').attr('checked') ? 1 : 0;
        var search_option_po_number = $('#search_option_po_number').attr('checked') ? 1 : 0;
        var search_option_po_date = $('#search_option_po_date').attr('checked') ? 1 : 0;
        var search_option_po_total = $('#search_option_po_total').attr('checked') ? 1 : 0;
        var search_option_po_acct_number = $('#search_option_po_acct_number').attr('checked') ? 1 : 0;
        var search_option_payment_type = $('#search_option_payment_type').attr('checked') ? 1 : 0;
        var search_option_last_digits = $('#search_option_last_digits').attr('checked') ? 1 : 0;
        var search_option_addr1 = $('#search_option_addr1').attr('checked') ? 1 : 0;
        var search_option_addr2 = $('#search_option_addr2').attr('checked') ? 1 : 0;
        var search_option_city = $('#search_option_city').attr('checked') ? 1 : 0;
        var search_option_state = $('#search_option_state').attr('checked') ? 1 : 0;
        var search_option_zip = $('#search_option_zip').attr('checked') ? 1 : 0;
        var search_option_country = $('#search_option_country').attr('checked') ? 1 : 0;
        var search_option_phone = $('#search_option_phone').attr('checked') ? 1 : 0;
        var search_option_batch = $('#search_option_batch').attr('checked') ? 1 : 0;
        var search_option_to_be_batched = $('#search_option_to_be_batched').attr('checked') ? 1 : 0;




        console.log(self.offset);
        $.ajax({
            url: "/payments/getnextblockbysearchquery",
            data: {
                query: query,

                search_option_com_name: search_option_com_name,
                search_option_fed_id: search_option_fed_id,
                search_option_po_number: search_option_po_number,
                search_option_po_date: search_option_po_date,
                search_option_po_total: search_option_po_total,
                search_option_po_acct_number: search_option_po_acct_number,
                search_option_payment_type: search_option_payment_type,
                search_option_last_digits: search_option_last_digits,
                search_option_addr1: search_option_addr1,
                search_option_addr2: search_option_addr2,
                search_option_city: search_option_city,
                search_option_state: search_option_state,
                search_option_zip: search_option_zip,
                search_option_country: search_option_country,
                search_option_phone: search_option_phone,
                search_option_batch: search_option_batch,
                search_option_to_be_batched: search_option_to_be_batched,
                search_option_limit:search_option_limit,
                sort_type: self.sortType,
                sort_direction: self.sortDirection,
                limit:50,
                offset:self.offset

            },
            type: "POST",
            dataType: 'json',
            success: function(msg) {

                if(msg['count']>0){
                    self.lastSelectedCount=msg['count'];


                    self.table_object.append(msg['html']);


                    $('#items_to_review').text('Payments: '+(self.offset+self.lastSelectedCount)+" items");
                    $('#items_to_review').attr('data-id',self.offset+self.lastSelectedCount);

                    self.recalculateScroll();

                    $(".list_checkbox").unbind('click');
                    self.initTable('list_table');
                    $('#list_table tbody .cutted_cell').tooltip(self.tooltipOptions);
                    self.setCheckedCount();


                } else {self.endReached=1;}

                setTimeout( function(){
                    $('#loading_mask_left').hide();
                },1000);

            }
        });



    },


    /**
     * Sets count of checked items
     */
    setCheckedCount: function() {
        var checkedCount = $(".list_checkbox:checked").length;
        $("#number_items").text(checkedCount);
        if (checkedCount != 1) {
            $('#sidebar').html('<div class="sidebar_item details_sidebar_block" id="company_info"><span style="font-size: 18px;">Details:</span></div>');
        }
    },

    /**
     * Gets payment info
     */
    getPaymentInfo: function(docId) {
        $.ajax({
            url: "/payments/getpaymentinfo",
            data: {docId: docId},
            async: false,
            type: "POST",
            success: function(msg) {
                $('#sidebar').html(msg);
            }
        });
    }
})