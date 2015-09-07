function ApList() {
    this.init();
    this.initTable();
    this.initAp();
    $('#list_table tbody .cutted_cell').tooltip(this.tooltipOptions);
}

ApList.prototype = $.extend(W9List.prototype, {

    /**
     * Sort parameters
     */
    sortType: 'ID',
    sortDirection: 'DESC',

    /**
     * Items to be notified
     */
    itemsToNotyfy: [],

    itemsToOpen: [],
    typesToOpen: [],
    usersToNotyfy: [],

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

    /**
     * Initialize table head
     */
    initAp: function()
    {
        var self = this;

        var apprBtnsClass = $('#approve_all_items').attr('class');
        if (apprBtnsClass == 'button') {
            self.buttonsStatus = true;
        }

        $('#vendor_name_cell_header').click(function() {
             self.clearArrows($(this));
             if (self.sortType == 'CompanyName') {
                 if (self.sortDirection == 'ASC') {
                     self.sortDirection = 'DESC';
                 } else {
                     self.sortDirection = 'ASC';
                 }
             } else {
                 self.sortType = 'CompanyName';
             }
             self.addSortingArrow($(this));
             self.updateList();
        });

        $('#type_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == 'DocType') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'DocType';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });

        $('#amount_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == 'Amount') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'Amount';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });

        $('#control_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == 'ApprName') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'ApprName';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });

        $('#notify_selected_items').click(function(event) {
            event.stopPropagation();
            if ($(this).hasClass('button')) {
                var i=0;
                self.itemsToNotyfy = [];
                $('.list_checkbox:checked').each(function() {
                    self.itemsToNotyfy[i] = $(this).val();
                    self.usersToNotyfy[i]=$(this).parent().find(".control_id").val();
                    i++;
                });
                self.showEmail(self.itemsToNotyfy,self.usersToNotyfy);
            }
        }),

        $('#notify_all_items').click(function(event) {

            event.stopPropagation();
            
            self.itemsToNotyfy = [];
            self.usersToNotyfy = [];
            var i=0;
            $('.list_checkbox').each(function() {
                self.itemsToNotyfy[i] = $(this).val();
                self.usersToNotyfy[i]=$(this).parent().find(".control_id").val();
                i++;
            });

            //$('input:checkbox').removeAttr('checked');
            self.showEmail(self.itemsToNotyfy,self.usersToNotyfy);
            }),



        $('#send_mail').click(function(event) {
            event.stopPropagation();
            self.sendEmail(self.itemsToNotyfy,self.usersToNotyfy);

        }),

        $('#open_selected_items').click(function(event) {
                event.stopPropagation();
                if ($(this).hasClass('button')) {
                    var i=0;

                    $('.list_checkbox:checked').each(function() {

                        var  control =$(this).parent().parent().find('.control_name').text();
                        control = $.trim(control);
                        if (control !='DEC') {
                            var type = $.trim( $(this).parent().parent().find('.doc_type').text());
                            self.itemsToOpen[i] = $(this).val();
                            self.typesToOpen[i] = type;
                            i++;
                        }

                    });
                    self.itemsToOpen = self.uniqueIDArray(self.itemsToOpen);
                    self.typesToOpen = self.uniqueDoctypeArray(self.typesToOpen);

                    self.openDialog(self.itemsToOpen,self.typesToOpen);

                }
            }),

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

        $('#submit_ap_form').click(function() {
            if ($(this).hasClass('button')) {
                if ($(".list_checkbox:checked").length > 0) {
                    self.updateList(false, false, '/ap/addsearchquerytosession');
                    $('#ap_detail_form').submit();
                } else {
                    if ($(".list_checkbox").length == 1) {
                        self.updateList(false, false, '/ap/addsearchquerytosession');
                        $(".list_checkbox").attr('checked', true);
                        $('#ap_detail_form').submit();
                    } else {
                        show_alert("You must select at least one item", 350);
                    }
                }
            }
        });

        $('#search_options label').click(function() {
            var label = $(this);
            var checkbox = label.find('input[type=checkbox]');
            setTimeout(function() {
                if(checkbox.attr('id') == 'search_option_to_be_batched' && checkbox.prop('checked')) {
                     $('#search_option_to_be_approved').attr('checked', false);
                     $('#search_option_batch').attr('checked', false);
                     $('#submit_for_batch').show();      //button "Batch" become visible when checkbox checked
                     $('#submit_ap_form').removeClass('button').addClass('not_active_button');
                } else {
                     $('#submit_for_batch').hide();      //button "Batch" become invisible when checkbox unchecked
                     $('#submit_ap_form').removeClass('not_active_button').addClass('button');
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

                            var is_dec = $.trim(checkbox.parent().parent().find(".control_name").text());

                            var doc_type=checkbox.parent().find(".document_type").val();

                            var row = $(this).parent().parent();
                            var docId = row.attr('id');
                            docId = docId.slice(3);

                            self.getCompanyInfo(docId,doc_type,is_dec);
                            //$('#print_document').attr('data', fed_id).show();

                        });
                    }
                } else {
                    var is_dec = $.trim(checkbox.parent().parent().find(".control_name").text());
                    var doc_type=checkbox.parent().find(".document_type").val();
                    var row = checkbox.parent().parent();
                    row.css({"backgroundColor":"#eee"});
                    var docId = row.attr('id');
                    docId = docId.slice(3);
                    if ($(".list_checkbox:checked").length == 1) {
                        self.getCompanyInfo(docId,doc_type,is_dec);
                        //$('#print_document').attr('data', fed_id).show();
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

        onlyForApprove = onlyForApprove ? onlyForApprove : false;
        markSelected = markSelected ? 1: 0;
        url = url ? url : '/documents/GetCueListBySearchQuery';

        var query =  $('#search_field').val();
        var search_option_com_name = $('#search_option_com_name').attr('checked') ? 1 : 0;
        var search_option_to_be_approved = $('#search_option_to_be_approved').attr('checked') ? 1 : 0;


        if (onlyForApprove) {
            search_option_to_be_approved = 1;
        }

        var search_option_fed_id = $('#search_option_fed_id').attr('checked') ? 1 : 0;
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
        $.ajax({
            url: url,
            data: {
                query: query,
                search_option_to_be_approved: search_option_to_be_approved,
                search_option_com_name: search_option_com_name,
                search_option_fed_id: search_option_fed_id,
                search_option_addr1: search_option_addr1,
                search_option_addr2: search_option_addr2,
                search_option_city: search_option_city,
                search_option_state: search_option_state,
                search_option_zip: search_option_zip,
                search_option_country: search_option_country,
                search_option_phone: search_option_phone,
                search_option_batch: search_option_batch,
                search_option_to_be_batched: search_option_to_be_batched,
                sort_type: self.sortType,
                sort_direction: self.sortDirection,
                mark_selected: markSelected
            },
            async: false,
            dataType: 'json',
            type: "POST",
            success: function(data) {
                if (data) {
                    $('#list_table tbody').html(data.html);
                    self.initTable();
                    self.setCheckedCount();

                    $('#approve_all_items').attr('class', data.btnsClass);
                    $('#approve_selected_items').attr('class', data.btnsClass);

                    if (data.btnsClass == 'button') {
                        self.buttonsStatus = true;
                    } else {
                        self.buttonsStatus = false;
                    }
                    $('#list_table tbody .cutted_cell').tooltip(self.tooltipOptions);
                }
                self.endLoadingMask('#tab1 .table_list_scroll_block');
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
            $('#company_info').html('<span style="font-size: 18px;">Details:</span>');
            $('#progress_bar').hide();
        }
        if (checkedCount > 0)  {
            $('#notify_selected_items').removeClass('not_active_button').addClass('button');
            $('#open_selected_items').removeClass('not_active_button').addClass('button');
            //$('#notify_all_items').removeClass('not_active_button').addClass('button');
        } else {
            $('#notify_selected_items').removeClass('button').addClass('not_active_button');
            //$('#notify_all_items').removeClass('button').addClass('not_active_button');
            $('#open_selected_items').removeClass('button').addClass('not_active_button');
        }
    },

    /**
     * Gets company info
     */
    getCompanyInfo: function(docId,doc_type,is_dec) {

        var url1,url2;
        var self = this;
        if (doc_type=='AP') {url1 = "/ap/getapprogress"; url2="/documents/GetApprCompanyInfo"; }
        if (doc_type=='PO') {url1 = "/po/getpoprogress"; url2="/documents/GetApprCompanyInfo"; }

        $.ajax({
            url: url1,
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
            url: url2,
            data: {docId: docId,is_dec: is_dec},
            async: false,
            type: "POST",
            success: function(msg) {
                $('#company_info').html(msg);
                $('#progress_bar').show().attr('data-id',docId);
                $('#progress_line').animate({width: parseInt(self.progressBarWidth) +'%'},1000,'easeOutExpo');
            }
        });
    },


    /**
     * Send marked items
     */
    sendSelectedItems: function() {
        var self = this;
        var clearList = 0;
        if (self.itemsToNotyfy.length == 0) {
            clearList = 1;
        }
        $.ajax({
            url: "/ap/setmarkeditems",
            data: {docs: self.itemsToNotyfy, clearList:clearList},
            async: false,
            type: "POST",
            success: function() {

            }
        });
    },

    /**
     * Approve items and reload page
     */
    showEmail: function(documents,control) {

        
        $.ajax({
            url: '/documents/ShowSendDialog',
            data: {documents: documents,control:control},
            async: false,
            type: "POST",
            dataType: 'json',
            success: function(msg) {

            show_modal_box('#send_mail_dialog', 725, 50);

            $('#email_block').html(msg.html_result);

            }
        });

    },

    sendEmail: function(documents,control) {


            $.ajax({
                url: '/documents/SendApprCueNotification',
                data: {documents: documents,control:control},
                async: false,
                type: "POST",
                dataType: 'json',
                success: function(msg) {

                    $('#email_block').html(msg.html_result);
                    $('#send_mail_dialog').fadeOut(800);
                    close_dialog();


                }
            });

    },

    openDialog: function (id_arr, type_arr) {
        var url='';
        var type='';
        if(type_arr.length ==1) {

            if(type_arr[0]=='AP'){
                url = '/ap/AddSessionPointerToReview';
                type='ap';
            }
            if(type_arr[0]=='PO'){
                url = '/po/AddSessionPointerToReview';
                type='po';
            }


            $.ajax({
                url: url,
                data: {apr_ids_arr:id_arr},
                async: true,
                type: "POST",
                dataType: 'json',
                success: function(msg) {
                    window.location='/'+type+'/detail';
                }
            });

        } else {

            show_modal_box('#open_dialog', 725, 50);
        }
    },


    /**
     * Makes array unique by third field (doc_id)
     * @param arr
     * @returns {*}
     */
    uniqueIDArray : function(arr){
        console.log("Starting uniq id");
        console.log(arr);
            var a=arr[0], k=1;
            for (var i=1; i<arr.length; i++)
            {
                if (arr[i]!=a){
                    arr[k]=arr[i]; a=arr[i]; k++;
                }
            }

            arr.length = k;



        return arr;

    },

    /***
     * Makes array unique by second field (doctype)
     * @param arr
     * @returns {*}
     */
    uniqueDoctypeArray : function(arr){

        var a=arr[0], k=1;
        for (var i=1; i<arr.length; i++)
        {
            if (arr[i]!=a){
                arr[k]=arr[i]; a=arr[i]; k++;
            }
        }
        arr.length = k;
        return arr;
    }


})