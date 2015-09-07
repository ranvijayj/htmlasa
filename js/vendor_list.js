function VendorList() {
    this.init();
    this.initTable();
    this.initVendors();
    $('#list_table tbody .cutted_cell').tooltip(this.tooltipOptions);
}

VendorList.prototype = $.extend(W9List.prototype, {
    /**
     * Sort parameters
     */
    sortType: 'com.Company_Name',
    sortDirection: 'ASC',



    limit:50,
    offset:50,
    label_is_checked:null,

    location:'/vendor',
    /**
     * scroll dimension
     */
    visibleHeight : 0,

    tableHeight :0,

    endReached:0,

    lastSelectedCount:0,

    table_object:null,

    /**
     * Init vendors
     */
    initVendors: function() {
        var self = this;

        //if checkbox Limit 50 is unchecked initially - we dont need handle scroll
        if (!$('#limiter_checkbox').is(":checked")) {self.endReached=1;}


        $('#vendor_name_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == 'com.Company_Name') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'com.Company_Name';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });

        $('#shortcut_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == 'Vendor_ID_Shortcut') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'Vendor_ID_Shortcut';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });

        $('#number_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == 'com.Company_Fed_ID') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'com.Company_Fed_ID';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });

        new AjaxUpload('#import_vendors_list', {
            action: '/vendor/import',
            onSubmit : function(file, ext) {
                // check extension of the file
                if (!(ext && /^(xls|xlsx|csv)$/.test(ext))) {
                    show_alert("Invalid extension of the file (xls/xlsx/csv allowed)!");
                    return false;
                }

                self.loadingMask('#tab1 .table_list_scroll_block');

                // lock button
                this.disable();
            },
            onComplete: function(file, response) {
                self.endLoadingMask('#tab1 .table_list_scroll_block');
                if (response == '1') {
                    show_alert("Invalid extension of the file!");
                    this.enable();
                } else if (response == '2') {
                    show_alert('The file size exceeds the maximum value!');
                    this.enable();
                } else if (response == '3') {
                    show_alert('Error loading file! Try again.');
                    this.enable();
                } else if (response == '4') {
                    show_alert('Invalid filename!');
                    this.enable();
                } else {
                    $('#additional_fields_block').html(response);
                    show_modal_box('#additional_fields_block', 725, 130);
                    self.initImportBox();
                }
                this.enable();
            }
        });


        $('#search_option_temporary').change(function () {
            if ($(this).prop('checked')) {
                $('#search_option_international').attr('checked',false);
            }
            self.updateList();
        })
        $('#search_option_international').change(function () {
            if ($(this).prop('checked')) {
                $('#search_option_temporary').attr('checked',false);
            }
            self.updateList();
        })


    },

    /**
     * initialize import box
     */
    initImportBox: function() {
        $('#submit_import').click(function() {
            $('#import_vendors_form').submit();
            close_modal_box('#additional_fields_block');
            var pb= new ProgressBar("vendors_import");
            pb.startListen();

            setTimeout(function() {
                show_alert('Please wait...');
            }, 210);
        });
    },

    /**
     * Table initialize method
     */
    initTable: function() {
        var self = this;

        $(".list_checkbox").on('click',function (event) {
            event.stopPropagation();
            var checkbox = $(this);
            setTimeout(function() {
                if (!checkbox.attr('checked')) {
                    checkbox.parent().parent().css({"backgroundColor":"#fff"});
                    if ($(".list_checkbox:checked").length == 1) {
                        var checked = $(".list_checkbox:checked");
                        checked.each(function() {
                            var vendorId = $(this).val();
                            self.getCompanyInfo(vendorId);
                            //$('#print_document').attr('data', fed_id).show();
                        });
                    }
                } else {
                    var row = checkbox.parent().parent();
                    row.css({"backgroundColor":"#eee"});
                    var vendorId = checkbox.val();
                    if ($(".list_checkbox:checked").length == 1) {
                        self.getCompanyInfo(vendorId);
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
    updateList: function() {
        var self = this;

        self.offset=self.offset+parseInt(self.lastSelectedCount);

        var query =  $('#search_field').val();
        var search_option_com_name = $('#search_option_com_name').attr('checked') ? 1 : 0;
        var search_option_international = $('#search_option_international').attr('checked') ? 1 : 0;
        var search_option_temporary = $('#search_option_temporary').attr('checked') ? 1 : 0;
        var search_option_fed_id = $('#search_option_fed_id').attr('checked') ? 1 : 0;
        var search_option_shortcut = $('#search_option_shortcut').attr('checked') ? 1 : 0;
        var search_option_addr1 = $('#search_option_addr1').attr('checked') ? 1 : 0;
        var search_option_addr2 = $('#search_option_addr2').attr('checked') ? 1 : 0;
        var search_option_city = $('#search_option_city').attr('checked') ? 1 : 0;
        var search_option_state = $('#search_option_state').attr('checked') ? 1 : 0;
        var search_option_zip = $('#search_option_zip').attr('checked') ? 1 : 0;
        var search_option_country = $('#search_option_country').attr('checked') ? 1 : 0;
        var search_option_phone = $('#search_option_phone').attr('checked') ? 1 : 0;
        var search_option_limit = $('#limiter_checkbox').attr('checked') ? 1 : 0;
        //self.loadingMask('#tab1 .table_list_scroll_block');
        $('#loading_mask_left').show();

        $.ajax({
            url: "/vendor/getlistbysearchquery",
            data: {
                query: query,
                search_option_com_name: search_option_com_name,
                search_option_international: search_option_international,
                search_option_temporary: search_option_temporary,
                search_option_fed_id: search_option_fed_id,
                search_option_shortcut: search_option_shortcut,
                search_option_addr1: search_option_addr1,
                search_option_addr2: search_option_addr2,
                search_option_city: search_option_city,
                search_option_state: search_option_state,
                search_option_zip: search_option_zip,
                search_option_country: search_option_country,
                search_option_phone: search_option_phone,
                search_option_limit:search_option_limit,
                sort_type: self.sortType,
                sort_direction: self.sortDirection
            },
            type: "POST",
            dataType: 'json',
            success: function(msg) {
                $('#list_table tbody').html(msg['html']);
                $('#list_table tbody .cutted_cell').tooltip(self.tooltipOptions);
                self.initTable();
                self.setCheckedCount();
                //self.endLoadingMask('#tab1 .table_list_scroll_block');
                self.lastSelectedCount = msg['count'];
                $('#items_to_review').text('Number of Vendors in List: '+self.lastSelectedCount);
                $('#items_to_review').attr('data-id',self.lastSelectedCount);
                $('#loading_mask_left').hide();
            }
        });
    },

    appendNextBlockToGrid: function(tableId){
        var self = this;
        self.table_object = $('#list_table tbody');

        self.limit=self.limit+parseInt(self.lastSelectedCount);
        self.offset=self.offset+parseInt(self.lastSelectedCount);
       // self.offset=   $('#items_to_review').data('id')+0;

        tableId = tableId ? tableId : 'vendor_list_table';

        var self = this;
        var query =  $('#search_field').val();
        var search_option_com_name = $('#search_option_com_name').attr('checked') ? 1 : 0;
        var search_option_fed_id = $('#search_option_fed_id').attr('checked') ? 1 : 0;
        var search_option_addr1 = $('#search_option_addr1').attr('checked') ? 1 : 0;
        var search_option_addr2 = $('#search_option_addr2').attr('checked') ? 1 : 0;
        var search_option_city = $('#search_option_city').attr('checked') ? 1 : 0;
        var search_option_state = $('#search_option_state').attr('checked') ? 1 : 0;
        var search_option_zip = $('#search_option_zip').attr('checked') ? 1 : 0;
        var search_option_country = $('#search_option_country').attr('checked') ? 1 : 0;
        var search_option_phone = $('#search_option_phone').attr('checked') ? 1 : 0;
        //var search_option_vendorlimit_right = $('#limiter_checkbox_right').attr('checked') ? 1 : 0;
        //var search_option_vendorlimit_left = $('#limiter_checkbox_left').attr('checked') ? 1 : 0;

        var sort_type = self.sortTypeExt;
        var sort_direction = self.sortDirectionExt;
        var list = 'external';

        if (tableId == 'list_table') {
            //query = '';
            sort_type = self.sortType;
            sort_direction = self.sortDirection;
            list = 'own';

        } else {
            //self.loadingMask('#right_column .table_list_scroll_block');
        }
        console.log(self.offset);
        $.ajax({
            url: "/vendor/GetVendorsListBySearchQueryNextBlock",
            data: {
                query: query,
                search_option_com_name: search_option_com_name,
                search_option_fed_id: search_option_fed_id,
                search_option_addr1: search_option_addr1,
                search_option_addr2: search_option_addr2,
                search_option_city: search_option_city,
                search_option_state: search_option_state,
                search_option_zip: search_option_zip,
                search_option_country: search_option_country,
                search_option_phone: search_option_phone,
                sort_type: sort_type,
                sort_direction: sort_direction,
                limit:50,
                offset:self.offset,
                list: list
            },
            type: "POST",
            dataType: 'json',
            success: function(msg) {

                if(msg['count']>0){
                    self.lastSelectedCount=msg['count'];


                    self.table_object.append(msg['html']);


                    $('#items_to_review').text('Vendors in List: '+(self.offset+self.lastSelectedCount));
                    $('#items_to_review').attr('data-id',self.offset+self.lastSelectedCount);

                    self.recalculateScroll();

                    $(".list_checkbox").unbind('click');
                    self.initTable('list_table');
                    $('#list_table tbody .cutted_cell').tooltip(self.tooltipOptions);
                    self.setCheckedCount();
                    setTimeout($('#loading_mask_left').hide(),1000);
                    // self.endLoadingMask('.table_list_scroll_block');
                } else {self.endReached=1;}


                setTimeout( function(){
                    $('#loading_mask_left').hide();
                },1000);
            }
        });



    },

    /**
     * Gets company info
     */
    getCompanyInfo: function(vendorId) {
        $.ajax({
            url: "/vendor/getcompanyinfo",
            data: {vendorId: vendorId},
            type: "POST",
            success: function(msg) {
                $('#company_info').html(msg);
            }
        });
    },

    recalculateScroll:function(){
        var self = this;
        self.visibleHeight = $('.table_list_scroll_block').height();
        self.tableHeight = $('#scroll_wrapper').height();
    }


});