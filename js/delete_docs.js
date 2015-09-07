function DeleteDocs() {
    var self = this;
    this.init();
    this.initSortingOptions();
    //this.initTable();
}

DeleteDocs.prototype =  {

    /**
     * Initialize method
     */
    init: function() {
        var self = this;

        /**
         *
         */

        $(".check_uncheck_all").click(function (event) {
            event.stopPropagation();
            if (!$(this).attr('checked')) {
                $('#files_for_delete tbody tr').each(function() {
                    $(this).animate({"backgroundColor":"#fff"},200);
                });
                $(".delete_checkbox").each(function() {
                    $(this).removeAttr('checked');
                });
            } else {
                $('#files_for_delete tbody tr').each(function() {
                    $(this).animate({"backgroundColor":"#eee"},200);
                });
                $(".delete_checkbox").each(function() {
                    $(this).attr('checked', 'checked');
                });
            }
        });

        $('.pointer_file').live('click',function() {
            var docId = $(this).data('id');
            self.displayFile(docId, 'last_uploads');
        });

        $('#delete_document').click(function() {
            var checkedCount = $(".delete_checkbox:checked").length;
            if (checkedCount > 0) {
                show_dialog("Are you sure you want to delete these documents!", 500);
            } else {
                show_alert("Are you sure you want to delete these documents!", 500);
            }
        });

        $('#dialogmodal a').click(function(event) {
            event.preventDefault();
            $('#delete_doc_form').submit();
        });

        $('.delete_checkbox').click(function(event) {
            event.stopPropagation();
            var checkbox = $(this);
            setTimeout(function() {
                if (!checkbox.attr('checked')) {
                    checkbox.parent().parent().css({"backgroundColor":"#fff"});
                } else {
                    var row = checkbox.parent().parent();
                    row.css({"backgroundColor":"#eee"});
                }
                self.setCheckedCount();
            }, 10);
        });


        $('#search_field').keydown(function() {
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

        $('#search_field').focus(function() {
            clearTimeout(self.timeoutSearch);
            $('#search_options').fadeIn(200);
        });

        $('#search_field').blur(function() {
            self.timeoutSearch = setTimeout(function() {
                $('#search_options').fadeOut(200);
            }, 200);
        });

        $('#search_options').click(function() {
            $('#search_field').focus();
        });

        $('#search_options label').click(function() {
            clearTimeout(self.timeoutClick);
            self.timeoutClick = setTimeout(function() {
                self.updateList();
            }, 200);
        });

        $('#submit_list_form').click(function() {
            if ($(".list_checkbox:checked").length > 0) {
                $('#detail_form').submit();
            } else {
                if ($(".list_checkbox").length == 1) {
                    $(".list_checkbox").attr('checked', true);
                    $('#detail_form').submit();
                } else {
                    show_alert("You must select at least one item", 350);
                }
            }
        });
    },

    /**
     * Initialize table head
     */
    initSortingOptions: function()
    {
        var self = this;

        var apprBtnsClass = $('#approve_all_items').attr('class');
        if (apprBtnsClass == 'button') {
            self.buttonsStatus = true;
        }

        $('#type_cell_header').click(function() {
            event.stopPropagation();
            self.clearArrows($(this));
            if (self.sortType == 'd.Document_Type') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'd.Document_Type';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });

        $('#name_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == 'i.File_Name') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'i.File_Name';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });

        $('#date_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == 'd.Created') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'd.Created';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });

        $('#user_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == 'u.User_Login') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = 'u.User_Login';
            }
            self.addSortingArrow($(this));
            self.updateList();
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






    /**
     * Updates documents list
     */
    updateList: function(markSelected, url) {
        var self = this;


        markSelected = markSelected ? 1: 0;
        url = '/documents/GetDocListForDeleteBySearchQuery';

        var query =  $('#search_field').val();


        var search_option_filename = $('#search_option_filename').attr('checked') ? 1 : 0;
        var search_option_doctype = $('#search_option_doctype').attr('checked') ? 1 : 0;
        var search_option_date = $('#search_option_date').attr('checked') ? 1 : 0;
        var search_option_createdby = $('#search_option_createdby').attr('checked') ? 1 : 0;
        var search_option_modified = $('#search_option_modified').attr('checked') ? 1 : 0;


        //self.loadingMask('#tab1 .table_list_scroll_block');
        $.ajax({
            url: url,
            data: {
                query: query,
                search_option_filename: search_option_filename,
                search_option_doctype: search_option_doctype,
                search_option_date: search_option_date,
                search_option_createdby: search_option_createdby,
                search_option_modified: search_option_modified,
                sort_type: self.sortType,
                sort_direction: self.sortDirection,
                mark_selected: markSelected
            },
            async: false,
            dataType: 'json',
            type: "POST",
            success: function(data) {
                if (data) {
                    $('#files_for_delete tbody').html(data.html);

                    $('#files_for_delete tbody .cutted_cell').tooltip(self.tooltipOptions);
                }
                //self.endLoadingMask('#tab1 .table_list_scroll_block');
            }
        });
    },


    /**
      * Displays block with file in right column
      * @param docId
      * @param fileType
      */
    displayFile: function(docId, fileType) {
        var self = this;
        $.ajax({
            url: "/uploads/getfilesblock",
            data: {imgId: docId, fileType: fileType},
            async: false,
            type: "POST",
            success: function(msg) {
                if (msg) {
                    $('#image_view_block').html(msg);
                    show_modal_box('#image_view_block', 725, 20);
                    self.initDetailsBlock();
                }
            }
        });
    },

    /**
     * Sets count of checked items
     */
    setCheckedCount: function() {
        var checkedCount = $(".delete_checkbox:checked").length;
        $("#number_items").text(checkedCount);
    },

    /**
     * Initialize details block
     */
    initDetailsBlock: function() {
        var image_view = new DocumentView('#image_view_block', '#file_detail_block_conteiner', '#file_detail_block', 525, 76, 1000);
    }
}