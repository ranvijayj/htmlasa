function VendorManage() {
    this.init();
    this.initTable('list_table');
    this.initTable('vendor_list_table');
    this.initVendors();
    $('#list_table tbody .cutted_cell').tooltip(this.tooltipOptions);
    $('#vendor_list_table tbody .cutted_cell').tooltip(this.tooltipOptions);
}

VendorManage.prototype = $.extend(W9List.prototype, {
    /**
     * Sort parameters
     */
    sortType: 'com.Company_Name',
    sortDirection: 'ASC',

    /**
     * Sort parameters of external vendors
     */
    sortTypeExt: 'com.Company_Name',
    sortDirectionExt: 'ASC',

    /**
     * Client id to change Shortcut
     */
    clientToChangeShortcut: 0,
    limit:50,
    offset:50,

    /**
     * scroll dimension
     */
    visibleHeight : 0,

    tableHeight :0,

    endReached:0,

    lastSelectedCount:0,

    table_object:null,
    tableSelected:null,
    /**
     * Init vendors
     */
    initVendors: function() {
        var self = this;

        //if checkbox Limit 50 is unchecked initially - we dont need handle scroll
        if (!$('#limiter_checkbox_left').is(":checked")) {self.endReached=1;}


        $('p.table_select').click(function(){
            self.tableSelected = $(this).data('id');
            $('p.table_select').removeClass('selected');
            $(this).addClass('selected');
            self.updateList();
        });

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
            self.updateList('list_table');
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
            self.updateList('list_table');
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
            self.updateList('list_table');
        });

        $('#vendor_name_cell_header_ext').click(function() {
            self.clearArrows($(this));
            if (self.sortTypeExt == 'com.Company_Name') {
                if (self.sortDirectionExt == 'ASC') {
                    self.sortDirectionExt = 'DESC';
                } else {
                    self.sortDirectionExt = 'ASC';
                }
            } else {
                self.sortTypeExt = 'com.Company_Name';
            }
            self.addSortingArrow($(this), true);
            self.updateList('vendor_list_table');
        });

        /*
        $('#shortcut_cell_header_ext').click(function() {
            self.clearArrows($(this));
            if (self.sortTypeExt == 'Vendor_ID_Shortcut') {
                if (self.sortDirectionExt == 'ASC') {
                    self.sortDirectionExt = 'DESC';
                } else {
                    self.sortDirectionExt = 'ASC';
                }
            } else {
                self.sortTypeExt = 'Vendor_ID_Shortcut';
            }
            self.addSortingArrow($(this), true);
            self.updateList('vendor_list_table');
        });
        */

        $('#number_cell_header_ext').click(function() {
            self.clearArrows($(this));
            if (self.sortTypeExt == 'com.Company_Fed_ID') {
                if (self.sortDirectionExt == 'ASC') {
                    self.sortDirectionExt = 'DESC';
                } else {
                    self.sortDirectionExt = 'ASC';
                }
            } else {
                self.sortTypeExt = 'com.Company_Fed_ID';
            }
            self.addSortingArrow($(this), true);
            self.updateList('vendor_list_table');
        });

        $('#add_users_to_list').click(function() {
            if ($("#ext_vendor_list .list_checkbox:checked").length > 0) {
                $('#ext_vendor_list').submit();
            } else {
                show_alert("You must select at least one item from the right panel", 350);
            }
        });

        $('#remove_users_to_list').click(function() {
            if ($("#vendor_detail_form .list_checkbox:checked").length > 0) {
                show_dialog('Are you sure you want to delete these Vendor(s)?');
            } else {
                show_alert("You must select at least one item", 350);
            }
        });

        $('#dialogmodal a').click(function(event) {
            event.preventDefault();
            $('#vendor_detail_form').submit();
        });

        /**
        $('#update_shortcut').click(function() {
            var shortcut = $('#shortcut_to_update').val();
            $.ajax({
                url: "/vendor/updateshortcut",
                data: {
                    shortcut: shortcut,
                    client_id: self.clientToChangeShortcut
                },
                dataType: 'json',
                type: "POST",
                success: function(data) {
                    if (data.changed == '1') {
                        $('#client' + self.clientToChangeShortcut + " .shortcut_cell").text(data.message);
                        close_modal_box('#change_shortcut');
                    } else {
                        $('#change_shortcut .errorMessage').text(data.message).show();
                    }
                }
            });
        });
         */

        $('#import_vendors_list').click(function() {
            self.openCopyVendorsListBox('import');
        });

        $('#export_vendors_list').click(function() {
            self.openCopyVendorsListBox('export');
        });

        $('#copy_vendors_form').submit(function(event) {
            event.preventDefault();
            var companyId = $('#copy_vendors_company').val();
            var copyType = $('#copy_vendors_type').val();//0 -copy fro 1 - copy to
            if (companyId > 0) {
                self.copyVendorsList(companyId, copyType);
            } else {
                $('#copy_vendors_company_error').show();
            }
        });

        $('#copy_vendors_company').change(function() {
            var companyId = $(this).val();
            if (companyId > 0) {
                $('#copy_vendors_company_error').hide();
            }
        });

        $('#limiter_checkbox_right').change(function(){

            var label = $(this);
            var label_checked= $(this).is(":checked");

            self.table_object = $('#vendor_list_table tbody')
            self.tableSelected = 'vendor_list_table';
            $.ajax({
                url: '/site/changevendorsrightdisplaylimit',
                data: {
                    limiter_checkbox_right: label_checked
                },
                async: true,
                type: "POST",
                success: function(data) {

                    if(!label_checked) {
                        $('#loading_mask_left').show();
                        setTimeout(function(){
                            self.appendNextBlockToGrid('vendor_list_table');
                        },1000);
                    } else {
                        //    window.location='/vendor/manage';
                        self.limit = label.data('value');
                        self.offset= self.limit;
                        self.lastSelectedCount = 0;

                        self.updateList(self.tableSelected);
                    }
                }

            });

        });

        $('#limiter_checkbox_left').change(function(){

            var label = $(this);
            var label_checked= $(this).is(":checked");

            self.table_object = $('#list_table tbody')
            self.tableSelected = 'list_table';
            $.ajax({
                url: '/site/changevendorsleftdisplaylimit',
                data: {
                    limiter_checkbox_left: label_checked
                },
                async: true,
                type: "POST",
                success: function(data) {

                    if(!label_checked) {
                        $('#loading_mask_left').show();
                          setTimeout(function(){
                            self.appendNextBlockToGrid('list_table');
                          },1000);
                    } else {
                    //    window.location='/vendor/manage';
                        self.limit = label.data('value');
                        self.offset= self.limit;
                        self.lastSelectedCount = 0;

                        self.updateList(self.tableSelected);
                    }
                }

            });

        });

        //scroll routines
        self.recalculateScroll();


        $('.table_list_scroll_block').on('scroll',function(){
            //console.log($(this).scrollTop());
            var label_checked= $('#limiter_checkbox_left').is(":checked");
            //console.log("Label checked"+label_checked);
            if(!label_checked){
               if(self.endReached!=1){
                    if((self.tableHeight-self.visibleHeight-$(this).scrollTop())<20) {
                        setTimeout(function(){
                        //console.log("End reached");


                           $('#loading_mask_left').show();

                            setTimeout(self.appendNextBlockToGrid('list_table'),500);

                        },1000);
                    }
               }

            }

        });

        //end of scroll routines

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
     * Table initialize method
     */
    initTable: function(tableId) {
        var self = this;

        $("#" + tableId + " .list_checkbox").click(function (event) {
            event.stopPropagation();
            var checkbox = $(this);
            setTimeout(function() {
                if (!checkbox.attr('checked')) {
                    checkbox.parent().parent().css({"backgroundColor":"#fff"});
                } else {
                    var row = checkbox.parent().parent();
                    row.css({"backgroundColor":"#eee"});
                }
            }, 10);
        });

        $('#' + tableId + ' tbody tr').click(function (event) {
            event.stopPropagation();
            $(this).find(".list_checkbox").click();
        });

        $('#' + tableId + ' .shortcut_cell').click(function(event) {
            event.stopPropagation();
            var vendorID = $(this).data('vendorId');
            var editing = $(this).attr('data-editing');
            if (editing == 0) {
                self.getInPlaceInput(vendorID, $(this));
            }
        });

        /*
        $('.shortcut_cell').click(function(event) {
             event.stopPropagation();
             self.clientToChangeShortcut = $(this).parent().find('.list_checkbox').val();
             if ($(this).html() != '<span class="not_set">Not set</span>') {
             $('#shortcut_to_update').val($(this).text());
             } else {
              $('#shortcut_to_update').val('');
             }
             $('#change_shortcut .errorMessage').hide();
             show_modal_box('#change_shortcut');
        });
        */
    },

    getInPlaceInput: function(vendorID, cell) {
        var self = this;
        $.ajax({
            url: "/vendor/getinplaceinput",
            data: {
                vendorID: vendorID
            },
            async: false,
            type: "POST",
            success: function(msg) {
                if (msg != '') {
                    cell.attr('data-editing', '1');
                    cell.html(msg);
                    input = cell.find('input');
                    input.focus();
                    input.blur(function() {
                        var value = $(this).val();
                        self.updateCellValue(vendorID, value, cell);
                    });
                }
            }
        });
    },

    updateCellValue: function(vendorID, value, cell) {
        var self = this;
        $.ajax({
            url: "/vendor/updatecellvalue",
            data: {
                vendorID: vendorID,
                value: value
            },
            async: false,
            type: "POST",
            success: function(msg) {
                if (msg != '') {
                    cell.html(msg);
                    cell.attr('data-editing', '0');
                }
            }
        });
    },

    /**
     * Updates vendors list
     */
    updateList: function(tableId) {
        var self = this;
        //tableId = tableId ? tableId : 'vendor_list_table';
        tableId = tableId ? tableId : self.tableSelected;
        tableId = tableId ? tableId : 'vendor_list_table';

        self.offset=self.offset+parseInt(self.lastSelectedCount);


        var query =  $('#search_field').val();
        console.log ('current query',query);
        var search_option_com_name = $('#search_option_com_name').attr('checked') ? 1 : 0;
        var search_option_international = $('#search_option_international').attr('checked') ? 1 : 0;
        var search_option_temporary = $('#search_option_temporary').attr('checked') ? 1 : 0;
        var search_option_fed_id = $('#search_option_fed_id').attr('checked') ? 1 : 0;
        var search_option_addr1 = $('#search_option_addr1').attr('checked') ? 1 : 0;
        var search_option_addr2 = $('#search_option_addr2').attr('checked') ? 1 : 0;
        var search_option_city = $('#search_option_city').attr('checked') ? 1 : 0;
        var search_option_state = $('#search_option_state').attr('checked') ? 1 : 0;
        var search_option_zip = $('#search_option_zip').attr('checked') ? 1 : 0;
        var search_option_country = $('#search_option_country').attr('checked') ? 1 : 0;
        var search_option_phone = $('#search_option_phone').attr('checked') ? 1 : 0;
        var search_option_vendorlimit_right = $('#limiter_checkbox_right').attr('checked') ? 1 : 0;
        var search_option_vendorlimit_left = $('#limiter_checkbox_left').attr('checked') ? 1 : 0;

        var sort_type = self.sortTypeExt;
        var sort_direction = self.sortDirectionExt;
        var list = 'external';

        if (tableId == 'list_table') {

            sort_type = self.sortType;
            sort_direction = self.sortDirection;
            list = 'own';
            self.loadingMask('#left_column .table_list_scroll_block');
        } else {
            self.loadingMask('#right_column .table_list_scroll_block');
        }

        $.ajax({
            url: "/vendor/getmanagelistbysearchquery",
            data: {
                query: query,
                search_option_com_name: search_option_com_name,
                search_option_international: search_option_international,
                search_option_temporary: search_option_temporary,
                search_option_fed_id: search_option_fed_id,
                search_option_addr1: search_option_addr1,
                search_option_addr2: search_option_addr2,
                search_option_city: search_option_city,
                search_option_state: search_option_state,
                search_option_zip: search_option_zip,
                search_option_country: search_option_country,
                search_option_phone: search_option_phone,
                search_option_vendorlimit_right:search_option_vendorlimit_right,
                search_option_vendorlimit_left:search_option_vendorlimit_left,
                limit:self.limit,
                offset:self.offset,
                sort_type: sort_type,
                sort_direction: sort_direction,
                list: list
            },
            type: "POST",
            success: function(msg) {


                //$('#items_to_review').text('Number of Vendors in List: '+(self.offset+self.lastSelectedCount));

                $('#' + tableId + ' tbody').html(msg);
                self.initTable(tableId);
                $('#' + tableId + ' tbody .cutted_cell').tooltip(self.tooltipOptions);
                self.setCheckedCount();
                self.endLoadingMask('.table_list_scroll_block');
                var rows_count = $('#' + tableId + ' tbody tr').length;
                $('#items_to_review').text('Number of Vendors in List: '+rows_count);
            }
        });
    },

    /**
     * Open copy Vendors box
     * @param type
     */
    openCopyVendorsListBox: function(type) {
        var box = $('#copy_vendors_box');
        if (type == 'export') {
            box.find('h2').text('Copy List to Company:');
            box.find('#copy_vendors_type').val(1);
            box.find('#copy_vendors_submit').val('Export');
        } else {
            box.find('h2').text('Copy List from Company:');
            box.find('#copy_vendors_type').val(0);
            box.find('#copy_vendors_submit').val('Import');
        }

        $('#copy_vendors_company_error').hide();
        $('#copy_vendors_company').val(0);

        show_modal_box('#copy_vendors_box');
    },

    /**
     * Copy Vendors List
     */
    copyVendorsList: function(companyId, copyType) {
        var self = this;

        close_modal_box('#copy_vendors_box');
        setTimeout(function() {
            show_alert("Please wait...");
        }, 210);

        $.ajax({
            url: "/vendor/copyvendorslist",
            data: {
                companyId: companyId,
                copyType: copyType
            },
            type: "POST",
            success: function() {
                close_alert();
                if (copyType == 1) {
                  self.updateList();
                }
                setTimeout(function() {
                    show_alert("Vendors list has been " + (copyType == 0 ? 'imported' : 'exported') + "!", 400);
                }, 210);

               window.location='/vendor/manage';
            }
        });
    },

    /**
     * Add sort direction arrow
     * @param cell
     */
    addSortingArrow: function(cell, extList) {
        extList = extList ? extList : false;
        if (extList) {
            if (this.sortDirectionExt == 'ASC') {
                cell.addClass('sorting_arrow_up');
            } else {
                cell.addClass('sorting_arrow_down');
            }
        } else {
            if (this.sortDirection == 'ASC') {
                cell.addClass('sorting_arrow_up');
            } else {
                cell.addClass('sorting_arrow_down');
            }
        }
    },

    appendNextBlockToGrid: function(tableId){
            var self = this;
            self.table_object = $('#list_table tbody');





            self.limit=self.limit+parseInt(self.lastSelectedCount);
            self.offset=self.offset+parseInt(self.lastSelectedCount);



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
              //  self.loadingMask('#left_column .table_list_scroll_block');
            } else {
                //self.loadingMask('#right_column .table_list_scroll_block');
            }
            console.log(self.offset);
            $.ajax({
                url: "/vendor/getmanagelistbysearchquerynextblock",
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
                    //search_option_vendorlimit_right:search_option_vendorlimit_right,
                    //search_option_vendorlimit_left:search_option_vendorlimit_left,
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

                        $('#items_to_review').text('Number of Vendors in List: '+(self.offset+self.lastSelectedCount));

                        self.recalculateScroll();

                        self.initTable('list_table');
                        $('#list_table tbody .cutted_cell').tooltip(self.tooltipOptions);

                        self.setCheckedCount();

                        // self.endLoadingMask('.table_list_scroll_block');
                    } else {
                        self.endReached=1;
                    }

                    $('#loading_mask_left').hide();

                }
            });


        //self.endLoadingMaskSmall('#loading_mask_left');
     //  setTimeout($('#loading_mask_left').hide(),5000);

    },

    recalculateScroll:function(){
        var self = this;
         self.visibleHeight = $('.table_list_scroll_block').height();
        //console.log("Visible height "+self.visibleHeight);
        self.tableHeight = $('#scroll_wrapper').height();
        //console.log("Table height "+self.tableHeight);
    }

});