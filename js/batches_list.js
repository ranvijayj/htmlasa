function BatchesList() {
    this.init();
    this.initTable();
    this.initBatches();
    $('#list_table tbody .cutted_cell').tooltip(this.tooltipOptions);
}

BatchesList.prototype = {

    /**
     * Sort parameters
     */
    sortType: 't.Batch_Creation_Date',
    sortDirection: 'DESC',


    init: function() {
        var self = this;

        //$('#search_field').hide();
        //$('#batch_search_field').show();

        $('#view_section_docs').hide();
        $('#view_section_docs_batches').show();

        $('#search_field').val(self.batchesQuery);
        $('#cabinets_actions').hide();
        $('#shelves_actions').hide();


        $('#batch_search_field').keydown(function() {
            clearTimeout(self.timeoutClick);
            self.timeoutClick = setTimeout(function() {
                self.updateList();
            }, 800);
        });

        $("#check_all").click(function () {
            setTimeout(function() {
                self.setCheckedCount();
            }, 10);
        });



    $('#view_section_docs_batches').click(function() {
            self.viewSectionBatches();
        });

    },

    /**
     *
     */

    initBatchesFlags: function () {


    },

    /**
     * Initialize table head
     */
    initBatches: function()
    {
        var self = this;



        $('#date_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == 'Batch_Creation_Date') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'Batch_Creation_Date';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });
        $('#source_cell_header').click(function() {
            self.clearArrows($(this));
             if (self.sortType == 'Batch_Source') {
                 if (self.sortDirection == 'ASC') {
                     self.sortDirection = 'DESC';
                 } else {
                     self.sortDirection = 'ASC';
                 }
             } else {
                 self.sortType = 'Batch_Source';
             }
             self.addSortingArrow($(this));
             self.updateList();
        });
        $('#posted_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == 'Batch_Posted') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'Batch_Posted';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });

        $('#uploaded_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == 'Batch_Uploaded') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'Batch_Uploaded';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });



        $('#amount_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == 'Batch_Total') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'Batch_Total';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });

        $('#type_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == 'Batch_Export_Type') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'Batch_Export_Type';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });

        $('#number_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == 'Batch_ID') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'Batch_ID';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });
        $('#user_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == 'User_ID') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'User_ID';
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
                            self.getBatchesInfo(docId);
                        });
                    }
                } else {
                    var row = checkbox.parent().parent();
                    row.css({"backgroundColor":"#eee"});
                    var docId = row.attr('id');
                    docId = docId.slice(3);
                    if ($(".list_checkbox:checked").length == 1) {
                        self.getBatchesInfo(docId);
                    }
                }
                self.setCheckedCount();





            }, 10);
        });

        $('#list_table tbody tr').click(function (event) {
            event.stopPropagation();
            $(this).find(".list_checkbox").click();
        });

        $(".checkbox_uploaded").click(function (event) {
            event.stopPropagation();
            var checkbox = $(this);

            if (checkbox.attr('checked')) {

//                    checkbox.parent().parent().find(".list_checkbox").attr("checked","checked");



            } else {
                //check if "checkbox_posted" is checked then do nothing
                if (checkbox.parent().find(".checkbox_posted").attr("checked")){
                    //  alert("Do nothing");


                } else {
                    //check if "checkbox_posted" is unchecked then uncheck all the row
                    //checkbox.parent().parent().find(".list_checkbox").removeAttr("checked");

                }

            }
            self.updateFlags(checkbox.parent().parent());
        })


        $(".checkbox_posted").click(function (event) {
            event.stopPropagation();
            var checkbox = $(this);
            setTimeout(function() {

                if (checkbox.attr('checked')) {
                    //checkbox.parent().parent().find(".list_checkbox").click();
                    //  checkbox.parent().parent().find(".list_checkbox").attr("checked","checked");
                    checkbox.parent().find(".checkbox_uploaded").attr("checked","checked");
                } else {
                    //checkbox.parent().find(".checkbox_uploaded").removeAttr("checked");
                    //checkbox.parent().parent().find(".list_checkbox").removeAttr("checked");
                }

                self.updateFlags(checkbox.parent().parent());
            })



        })
    },

    /**
     * Updates batches list
     */
    updateList: function() {

            var self = this;
            var query =  $('#batch_search_field').val();

            $.ajax({
                url: "/batches/getlistbysearchquery",
                data: {
                    query: query,
                    sort_type: self.sortType,
                    sort_direction: self.sortDirection
                },
                type: "POST",
                success: function(msg) {
                    $('#list_table tbody').html(msg);

                    self.initTable();
                    self.setCheckedCount();
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
            $('#storage_info').html('<span style="font-size: 18px;">Details:</span>');
        }
    },

    /**
     * Gets payment info
     */
    getBatchesInfo: function(docId) {
        $.ajax({
            url: "/batches/GetBatchesInfo",
            data: {docId: docId},
            async: false,
            type: "POST",
            success: function(msg) {
                $('#storage_info').html(msg);
            }
        });
    },

    /**
     * Clear sorting arrows
     * @param cell
     */
    clearArrows: function(cell) {
        var row = cell.parent();
        var ths = row.find('th');
        ths.each(function() {
            $(this).removeClass('sorting_arrow_down');
            $(this).removeClass('sorting_arrow_up');
        });
    },

    /**
     * Add sort direction arrow
     * @param cell
     */
    addSortingArrow: function(cell) {

        if (this.sortDirection == 'ASC') {
            cell.addClass('sorting_arrow_up');
        } else {
            cell.addClass('sorting_arrow_down');

        }
    },

    updateFlags: function (row) {
        var checkbox_posted=row.find(".checkbox_posted").attr("checked") ? 1 :0;
        var checkbox_uploaded=row.find(".checkbox_uploaded").attr("checked") ? 1 :0;
        var batch_id= row.find(".list_checkbox").val();

        $.ajax({
            url: "/batches/UpdateBatchFlags",
            data: {
                checkbox_uploaded: checkbox_uploaded,
                checkbox_posted: checkbox_posted,
                batch_id: batch_id
            },
            type: "POST",
            success: function(msg) {
            }
        });


},
    viewSectionBatches: function(){
        if ($(".list_checkbox:checked").length > 0) {
            $( "#bathes_form_for_view" ).submit();
        } else {
            show_alert("You must select at least one item", 350);
        }

        //window.location = '/batches/viewstorage';

    }

}