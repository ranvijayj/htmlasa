function PoList() {
    this.init();
    this.initTable();
    this.initPo();
    $('#list_table tbody .cutted_cell').tooltip(this.tooltipOptions);
}

PoList.prototype = $.extend(W9List.prototype, {

    /**
     * Sort parameters
     */
    sortType: 'PO_ID',
    sortDirection: 'DESC',

    /**
     * Items to be approve
     */
    itemsToApprove: [],

    /**
     * Define approval buttons status
     */
    buttonsStatus: false,

    /**
     * Define if list is ready for approve
     */
    readyForApprove: false,

    /**
     * Width of progress bar
     */
    progressBarWidth: 0,

    limit_checkbox_state:false,


    location:'/po',
    limit:50,
    offset:50,
    label_is_checked:null,

    projectsArray:null,

    /**
     * Initialize table head
     */
    initPo: function()
    {
        var self = this;

        self.projectsArray = new Array();

        var apprBtnsClass = $('#approve_all_items').attr('class');
        if (apprBtnsClass == 'button') {
            self.buttonsStatus = true;
        }

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

        $('#number_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == 'PO_Number') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'PO_Number';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });

        $('#mark_as_void').on('click',function(event) {
            event.stopPropagation();

            var poId = $(this).data('id');
            self.markPoAsVoid(poId);
        });

        $('#amount_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == 'PO_Total') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'PO_Total';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });

        $('#due_date_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == 'PO_Date') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'PO_Date';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });

        $('#approve_all_items').click(function(event) {
            event.stopPropagation();
            if (self.buttonsStatus) {
                if (self.readyForApprove) {
                    var i = 0;
                    self.itemsToApprove = [];
                    $('.list_checkbox').each(function() {
                        self.itemsToApprove[i] = $(this).val();
                        i++;
                    });
                    var pb= new ProgressBar("ap_approve");
                    pb.startListen();

                    self.approveItems();
                } else {

                    self.updateList(true);
                    $('#ready_for_approval_all').show();
                    self.readyForApprove = true;
                }
            }
        });

        $('#approve_selected_items').click(function(event) {
            event.stopPropagation();
            if (self.buttonsStatus) {
                var i = 0;
                self.itemsToApprove = [];
                $('.list_checkbox:checked').each(function() {
                    self.itemsToApprove[i] = $(this).val();
                    i++;
                });
                if (self.readyForApprove) {
                    if (self.itemsToApprove.length == 0) {
                        show_alert("You must select at least one item", 350);
                    }
                    var pb= new ProgressBar("ap_approve");
                    pb.startListen();

                    self.approveItems();
                } else {
                    self.sendSelectedItems();
                    self.updateList(true, true);
                    $('#ready_for_approval_selctd').show();
                    self.readyForApprove = true;
                }
            }
        });

        $('body').click(function() {
            if (self.readyForApprove) {
                $('#ready_for_approval_all').hide();
                $('#ready_for_approval_selctd').hide();
                $("#check_all").removeAttr('checked');
                self.updateList();
                self.readyForApprove = false;
            }
        });

        $('#alertmodal button').click(function(event) {
            event.stopPropagation();
        });

        $('#submit_po_form').click(function() {
            if ($(this).hasClass('button')) {
                if ($(".list_checkbox:checked").length > 0) {
                    self.updateList(false, false, '/po/addsearchquerytosession');
                    $('#po_detail_form').submit();
                } else {
                    if ($(".list_checkbox").length == 1) {
                        self.updateList(false, false, '/po/addsearchquerytosession');
                        $(".list_checkbox").attr('checked', true);
                        $('#po_detail_form').submit();
                    } else {
                        show_alert("You must select at least one item", 350);
                    }
                }
            }
        });
        /**
         * After clicking on search criterias this function organize behavior of checkboxex
         *
         */
        $('#search_options label').click(function() {
            var label = $(this);
            var checkbox = label.find('input[type=checkbox]');
            setTimeout(function() {
                if(checkbox.attr('id') == 'search_option_to_be_batched' && checkbox.prop('checked')) {
                    $('#search_option_to_be_approved').attr('checked', false);
                    $('#search_option_batch').attr('checked', false);
                    $('#submit_for_batch').show();      //button "Batch" become visible when checkbox checked
                    $('#submit_po_form').removeClass('button').addClass('not_active_button');


                    //uncheck limit checkbox
                    //self.limit_checkbox_state=$('#limiter_checkbox').prop('checked');
                    //$('#limiter_checkbox').attr('checked', false);

                } else {
                    $('#submit_for_batch').hide();      //button "Batch" become invisible when checkbox unchecked
                    $('#submit_po_form').removeClass('not_active_button').addClass('button');

                    //check limit checkbox
                    //$('#limiter_checkbox').attr('checked', self.limit_checkbox_state);
                }


                if(checkbox.attr('id') == 'search_option_to_be_approved' && checkbox.prop('checked')) {
                    $('#search_option_to_be_batched').attr('checked', false);
                    $('#search_option_batch').attr('checked', false);
                }

                if(checkbox.attr('id') == 'search_option_batch' && checkbox.prop('checked')) {
                    $('#search_option_to_be_batched').attr('checked', false);
                    $('#search_option_to_be_approved').attr('checked', false);
                }
            }, 20);
        });


        self.toggleLimitCheckBoxVisibility();
    },

    /**
     * Table initialize method
     */
    initTable: function() {
        var self = this;

        $(".list_checkbox").click(function (event) {
            event.stopPropagation();

            var checkbox = $(this);
            var projectID = checkbox.data('project-id');

            setTimeout(function() {
                if (!checkbox.attr('checked')) {
                    checkbox.parent().parent().css({"backgroundColor":"#fff"});

                    self.removeFromProjectsArray(projectID);//remove project id from global project array

                    if ($(".list_checkbox:checked").length == 1) {
                        var checked = $(".list_checkbox:checked");
                        checked.each(function() {
                            var row = $(this).parent().parent();
                            var docId = row.attr('id');
                            docId = docId.slice(3);
                            self.getCompanyInfo(docId);
                        });
                    }
                } else {
                    var row = checkbox.parent().parent();
                    row.css({"backgroundColor":"#eee"});

                    self.addToProjectsArray(projectID);//add project id of current item to global project array

                    var docId = row.attr('id');
                    docId = docId.slice(3);
                    if ($(".list_checkbox:checked").length == 1) {
                        self.getCompanyInfo(docId);
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
    updateList: function(onlyForApprove, markSelected, url) {
        var self = this;

        //display or hide limit checkbox according to the mode : to be approved or not
        self.toggleLimitCheckBoxVisibility();


        var pb= new ProgressBar("doc_search");
//        pb.startListen();



        onlyForApprove = onlyForApprove ? onlyForApprove : false;
        markSelected = markSelected ? 1: 0;
        url = url ? url : '/po/getlistbysearchquery';

        var query =  $('#search_field').val();
        var search_option_com_name = $('#search_option_com_name').attr('checked') ? 1 : 0;
        var search_option_to_be_approved = $('#search_option_to_be_approved').attr('checked') ? 1 : 0;
        var search_option_limit = $('#limiter_checkbox').attr('checked') ? 1 : 0;

        if (onlyForApprove) {
            search_option_to_be_approved = 1;
            search_option_limit=0;
        }



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


        self.loadingMask('#tab1 .table_list_scroll_block');
        pb.stepTo(10);
        $.ajax({
            url: url,
            data: {
                query: query,
                search_option_to_be_approved: search_option_to_be_approved,
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
                mark_selected: markSelected
            },
            async: false,
            dataType: 'json',
            type: "POST",
            success: function(data) {
                if (data) {

                    console.log("Result "+data.count);
                    $('#list_table tbody').html(data.html);
                    self.initTable();
                    self.setCheckedCount();

                    //$('#items_to_review').text('Number of Vendors in List: '+data.count);
                    $('#items_to_review').attr('data-id',data.count);

                    $('#approve_all_items').attr('class', data.btnsClass);
                    $('#approve_selected_items').attr('class', data.btnsClass);

                    if (data.btnsClass == 'button') {
                        self.buttonsStatus = true;
                    } else {
                        self.buttonsStatus = false;
                    }

                    //$('#items_to_review').text('Number of PO in List: '+data.count+' items');
                    $('#items_to_review').attr('data-id',data.count);

                    $('#list_table tbody .cutted_cell').tooltip(self.tooltipOptions);


                }
                self.endLoadingMask('#tab1 .table_list_scroll_block');
                pb.doneQuick();
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
            url: "/po/getnextblockbysearchquery",
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


                    //$('#items_to_review').text('Number of PO in List: '+(self.offset+self.lastSelectedCount));
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
        var self = this;
        var checkedCount = $(".list_checkbox:checked").length;
        //$("#number_items").text(checkedCount);
        if (checkedCount != 1) {
            $('#company_info').html('<span style="font-size: 18px;">Details:</span>');
            $('#progress_bar').hide();
        }

        if (checkedCount > 0 && $('#search_option_to_be_batched').prop('checked') && (self.getProjectUniqueItemsCount()==1)) {
            $('#submit_for_batch').removeClass('not_active_button').addClass('button');
        } else {
            $('#submit_for_batch').removeClass('button').addClass('not_active_button');
        }
    },

    /**
     * Gets company info
     */
    getCompanyInfo: function(docId) {
        var self = this;
        $.ajax({
            url: "/po/getpoprogress",
            data: {docId: docId},
            async: false,
            type: "POST",
            success: function(msg) {
                msg = parseInt(msg);
                self.progressBarWidth = msg;
                $('#progress_line').css('width', '0%')
            }
        });

        $.ajax({
            url: "/po/getcompanyinfo",
            data: {docId: docId},
            async: false,
            type: "POST",
            success: function(msg) {
                $('#company_info').html(msg);
                $('#progress_bar').attr('data-id',docId);
                $('#progress_bar').show();
                $('#progress_line').animate({width: parseInt(self.progressBarWidth) +'%'},1000,'easeOutExpo');
            }
        });
    },

    /**
     * Approve items and reload page
     */
    approveItems: function() {
        var self = this;
        if (self.itemsToApprove.length != 0) {
            show_alert("Please wait...", 250);
            $.ajax({
                url: "/po/approvepos",
                data: {docs: self.itemsToApprove},
                type: "POST",
                success: function() {
                    window.location = '/po';
                }
            });
        }
    },

    /**
     * Send marked items
     */
    sendSelectedItems: function() {
        var self = this;
        var clearList = 0;
        if (self.itemsToApprove.length == 0) {
            clearList = 1;
        }
        $.ajax({
            url: "/po/setmarkeditems",
            data: {docs: self.itemsToApprove, clearList:clearList},
            async: false,
            type: "POST",
            success: function() {

            }
        });
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

                // window.location = '/po/create';
            }
        });

    }

})