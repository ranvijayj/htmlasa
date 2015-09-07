function PCList() {
    this.init();
    this.initTable();
    this.initPCs();
    $('#list_table tbody .cutted_cell').tooltip(this.tooltipOptions);
}

PCList.prototype = $.extend(W9List.prototype, {

    /**
     * Sort parameters
     */
    sortType: 't.Employee_Name',
    sortDirection: 'ASC',

    /**
     * Initialize table head
     */
    initPCs: function()
    {
        var self = this;

        $('#name_cell_header').click(function() {
            self.clearArrows($(this));
             if (self.sortType == 'Employee_Name') {
                 if (self.sortDirection == 'ASC') {
                     self.sortDirection = 'DESC';
                 } else {
                     self.sortDirection = 'ASC';
                 }
             } else {
                 self.sortType = 'Employee_Name';
             }
             self.addSortingArrow($(this));
             self.updateList();
        });

        $('#amount_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == 'Envelope_Total') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'Envelope_Total';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });

        $('#number_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == 'Envelope_Number') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'Envelope_Number';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });

        $('#date_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == 'Envelope_Date') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'Envelope_Date';
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
                            self.getPCInfo(docId);
                        });
                    }
                } else {
                    var row = checkbox.parent().parent();
                    row.css({"backgroundColor":"#eee"});
                    var docId = row.attr('id');
                    docId = docId.slice(3);
                    if ($(".list_checkbox:checked").length == 1) {
                        self.getPCInfo(docId);
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
        var search_option_employee_name = $('#search_option_employee_name').attr('checked') ? 1 : 0;
        var search_option_envelope_number = $('#search_option_envelope_number').attr('checked') ? 1 : 0;
        var search_option_envelope_total = $('#search_option_envelope_total').attr('checked') ? 1 : 0;
        var search_option_envelope_date = $('#search_option_envelope_date').attr('checked') ? 1 : 0;

        self.loadingMask('#tab1 .table_list_scroll_block');
        $.ajax({
            url: "/pc/getlistbysearchquery",
            data: {
                query: query,
                search_option_employee_name: search_option_employee_name,
                search_option_envelope_number: search_option_envelope_number,
                search_option_envelope_total: search_option_envelope_total,
                search_option_envelope_date: search_option_envelope_date,
                sort_type: self.sortType,
                sort_direction: self.sortDirection
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

    /**
     * Gets payment info
     */
    getPCInfo: function(docId) {
        $.ajax({
            url: "/pc/getpcinfo",
            data: {docId: docId},
            async: false,
            type: "POST",
            success: function(msg) {
                $('#company_info').html(msg);
            }
        });
    }
})