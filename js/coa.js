function CoaPage() {
    this.init();
    this.initSorting();
    this.initTable();
}

CoaPage.prototype = {
    /**
     * Sort parameters
     */
    sortType: '`class`.`Class_Name`',
    sortDirection: 'ASC',
    classID: 0,
    selected_coas: new Array(),

    /**
     * Tooltip options
     * @type {{position: {my: string, at: string, using: Function}, show: {effect: string, delay: number}, hide: {effect: string, delay: number}}}
     */
    tooltipOptions : {
        position: {
            my: "left top",
            at: "left bottom",
            using: function( position, feedback ) {
                $( this ).css( position );
                $( "<div>" )
                    .addClass( "tooltip" )
                    .addClass( feedback.vertical )
                    .addClass( feedback.horizontal )
                    .appendTo( this );
            }
        },
        show: {
            effect: "slideDown",
            delay: 400
        },
        hide: {
            effect: "hide",
            delay: 0
        }
    },

    /**
     * If import is enable
     */
    enableImport: false,

    /**
     * Click timeout on drop down list
     */
    timeout: false,

    /**
     * Initialize method
     */
    init: function() {
        var self = this;

        new AjaxUpload('#import_coa', {
            action: '/coa/importcoa',
            onSubmit : function(file, ext) {
                // check extension of the file
                if (!(ext && /^(xls|xlsx|csv)$/.test(ext))) {
                    show_alert("Invalid extension of the file (xls,xlsx,csv allowed)!");
                    return false;
                }


                //show_alert("Uploading...");
                $('#left_column .table_list_scroll_block').prepend("<div class='loadinng_mask'></div>");

                // lock button
                this.disable();
            },
            onComplete: function(file, response) {
                $('#left_column .table_list_scroll_block .loadinng_mask').remove();
                if (response == '1') {
                    show_alert("Invalid extension of the file (xls,xlsx,csv allowed)!");
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
                    if (response) { $('#additional_fields_block').html(response);} else {$('#additional_fields_block').html("Something wrong with file");}
                    show_modal_box('#additional_fields_block', 725, 130);
                    self.initImportBox();
                }
                this.enable();
            }
        });

        $('#add_coa_class').click(function() {
            var countItems = $('#coa_classes tbody tr').length;
            var newItem = countItems +1;
            var newLine = $('<tr><td class="width40"><span><input type="text" value="' + newItem + '" name="CoaClass[' + newItem + '][Class_Sort_Order]" class="int_type"></span></td><td class="width50"><span><input type="text" maxlength="3" value="" name="CoaClass[' + newItem + '][Class_Shortcut]"></span></td><td><span><input type="text" maxlength="50" value="" name="CoaClass[' + newItem + '][Class_Name]"></span><input type="hidden" value="new_item" name="CoaClass[' + newItem + '][COA_Class_ID]"></td></tr>');

            newLine.find('input.int_type').blur(function() {
                var value = $(this).val();
                self.checkIntType($(this), value);
            });

            newLine.appendTo('#coa_classes tbody');
            $('#coa_classes_block').scrollTop(9999);
            newLine.effect('highlight');
            newLine.find('td').effect('highlight');
            newLine.find('input').effect('highlight');
        });

        $('#remove_coa_class').click(function() {
            var countItems = $('#coa_classes tbody tr').length;
            if (countItems > 1) {
                var item = $('#coa_classes tbody tr:last-child');
                item.remove();
            }
        });


        $('#add_coa').click(function(e) {
            e.stopPropagation();
            self.prependCoaCreationBlock();

        });
        $('#remove_coa').click(function(e) {
            e.stopPropagation();
            if ( !$(this).hasClass('not_active') ) {
                var str = "Do you really want to delete "+ self.selected_coas.length +" item(s) ?";

                show_def_dialog(str,500,'true').then(function (answer) {
                    if(answer == 'true'){
                        $.ajax({
                            url: "/coa/deletecoas",
                            data: {
                                coas: self.selected_coas
                            },
                            type: "POST",
                            dataType: 'json',
                            success: function(data) {

                                setTimeout( function () {
                                    if (data == '0') {
                                        show_alert("No Coa's were deleted", 540);
                                    } else if (data == '1') {
                                        show_alert(data + " COA was deleted", 540);
                                    } else {
                                        show_alert(data + " COA's were deleted", 540);
                                    }


                                    $('#alertmodal .hidemodal').click(function () {
                                        window.location = '/coa';
                                    });
                                    $('#alertmodal .cancelbutton').click(function () {
                                        window.location = '/coa';
                                    });
                                    $('#lean_overlay').click(function () {
                                        window.location = '/coa';
                                    });

                                },500);

                            }
                        });
                    }
                });
            }

        });



        $('.table_list_scroll_block').on('click','#confirm_coa_adding',function () {
            self.addCoaRecord();
        });

        $('.table_list_scroll_block').on('click','#cancel_coa_adding',function () {
            $('.table_list_scroll_block .popup_row').fadeOut('slow');
        });

        $('.coa_list_table').on('change','.list_checkbox',function () {
                    self.updateSelectedArray();
        });


        $('input.int_type').blur(function() {
            var value = $(this).val();
            self.checkIntType($(this), value);
        });

        $('#submit_form').click(function() {
            $('#coa_settings_form').submit();
        });

        $('#list_table tbody .cutted_cell').tooltip(self.tooltipOptions);

        $('#default_coa_class').change(function() {
            var classID = $(this).val();
            self.changeDefaultClass(classID);
        });

        $('#copy_coa').click(function() {
            var modal_width = modal_width ? modal_width : 260;
            $.ajax({
                url: "/site/getuserclientslist",
                data: {
                    do_not_show_all_projects: true
                },
                type: "POST",
                dataType: 'json',
                success: function(data) {
                    $('#client_to_copy').html(data.clients);
                    $('#project_to_copy').html(data.projects);
                    show_modal_box('#copy_coas');
                }
            });
        });

        // get user client projects
        $('#client_to_copy').change(function() {
            var client_id = $(this).val();
            $.ajax({
                url: "/site/getuserclientslist",
                type: "POST",
                data: {
                    client_id: client_id,
                    do_not_show_all_projects: true
                },
                dataType: 'json',
                success: function(data) {
                    $('#project_to_copy').html(data.projects);
                }
            });
        });

        $('#CoaStructure_COA_Break_Character').bind('keyup blur',function(){
                var node = $(this);
                node.val(node.val().replace(/[a-zA-Z0-9\s]/g,'') ); }
        );

        $('body').click(function() {
            $('.coa_list_table').find(".list_checkbox:checked").each(function() {
                $(this).click();
            });
        });
    },

    /**
     * initialize impot box
     */
    initImportBox: function() {
        var self = this;
        var rowsToImport = $('.row_for_import');
        var countRows = rowsToImport.length;
        var invalidBudget = false;

        rowsToImport.each(function() {
            var validBudg = $(this).data('budget');
            if (validBudg == 0) {
                invalidBudget = true;
            }
        });

        if (countRows == 0 || invalidBudget) {
            self.enableImport = false;
            $('#submit_import').removeClass('button').addClass('not_active_button');
        } else {
            self.enableImport = true;
        }

        $('#submit_import.button').click(function() {

            var pb= new ProgressBar("coa_import");
            pb.startListen();
            $('#import_coa_form').submit();
        });

        $('#import_coa_form').submit(function(event) {
            if (!self.enableImport) {
                event.preventDefault();
            } else {
                close_modal_box('#additional_fields_block');
                setTimeout(function() {
                    show_alert('Please wait...');
                }, 210);
            }
        });
    },

    /**
     * Sorting initialize
     */
    initSorting: function() {
        var self = this;

        $('#coa_class_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == '`class`.`Class_Name`') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = '`class`.`Class_Name`';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });

        $('#coa_desc_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == '`t`.`COA_Name`') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = '`t`.`COA_Name`';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });

        $('#coa_numb_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == '`t`.`COA_Acct_Number`') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = '`t`.`COA_Acct_Number`';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });

        $('#coa_budget_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == '`t`.`COA_Budget`') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = '`t`.`COA_Budget`';
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
                } else {
                    var row = checkbox.parent().parent();
                    row.css({"backgroundColor":"#eee"});
                }
            }, 10);
        });

        $(".coa_list_table td.dropdown_cell_upload").click(function(event) {
            event.stopPropagation();
            $(this).parent().find(".list_checkbox").removeAttr('checked').click();
            $(".coa_list_table td.dropdown_cell_upload ul").slideUp(150);
            $(this).find('ul').slideDown(150);
        });

        $(".coa_list_table td.dropdown_cell_upload ul").mouseout(function() {
            var ul = $(this);
            self.timeout = setTimeout(function() {
                ul.slideUp(150);
            }, 100);
        });

        $(".coa_list_table td.dropdown_cell_upload ul").mouseover(function() {
            if (self.timeout) {
                clearTimeout(self.timeout);
                self.timeout = false;
            }
        });

        $(".coa_list_table td.dropdown_cell_upload ul li").click(function(event) {
            event.stopPropagation();
            var classID = $(this).data('classId');
            var className = $(this).data('class');
            var cell = $(this).parent().parent().parent();
            var dropdown_cell_value = cell.find('.dropdown_cell_value');
            dropdown_cell_value.text(className);
            $(this).parent().slideUp(150);
            var checkedCOAs = [];

            $(".list_checkbox:checked").each(function() {
                var id = $(this).val();
                checkedCOAs.push(id);
            });

            self.updateCOAClasses(classID, checkedCOAs);
        });

        $('.in_place_edit').click(function() {
            var cellType = $(this).data('cellType');
            var coaID = $(this).parent().find('.list_checkbox').val();
            var editing = $(this).attr('data-editing');
            var already_used = $(this).attr('data-already-used');

            if (editing == 0 && already_used==0) {
                self.getInPlaceInput(coaID, cellType, $(this));
            }
        });
    },

    getInPlaceInput: function(coaID, cellType, cell) {
        var self = this;
        $.ajax({
            url: "/coa/getinplaceinput",
            data: {
                coaID: coaID,
                cellType: cellType
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
                        self.updateCellValue(coaID, cellType, value, cell);
                    });
                }
            }
        });
    },

    updateCellValue: function(coaID, cellType, value, cell) {
        var self = this;
        $.ajax({
            url: "/coa/updatecellvalue",
            data: {
                coaID: coaID,
                cellType: cellType,
                value: value
            },
            async: false,
            type: "POST",
            success: function(msg) {
                if (msg != '') {
                    cell.html(msg);
                    cell.attr('data-editing', '0');
                    cell.find('.cutted_cell').tooltip(self.tooltipOptions);
                }
            }
        });
    },

    changeDefaultClass: function(classID) {
        var self = this;
        $.ajax({
            url: "/coa/changedefaultclass",
            data: {
                classID: classID
            },
            type: "POST",
            success: function(msg) {
                self.classID = classID;
                self.updateList();
            }
        });
    },

    updateCOAClasses: function(classID, coas) {
        var self = this;
        $.ajax({
            url: "/coa/updatecoaclasses",
            data: {
                classID: classID,
                coas: coas
            },
            type: "POST",
            success: function(msg) {
                self.updateList();
            },
            error: function () {
                self.updateList()
            }
        });
    },

    /**
     * Check integer Type of value
     */
    checkIntType: function(elem, value) {
        value = parseInt(value);
        if (value == 0 || isNaN(value)) {
            value = 0;
        }

        elem.val(value);
    },

    /**
     * Updates vendors list
     */
    updateList: function() {
        var self = this;
        self.loadingMask('#left_column .table_list_scroll_block');
        $.ajax({
            url: "/coa/getcoalist",
            data: {
                sort_type: self.sortType,
                sort_direction: self.sortDirection,
                coa_class: self.classID
            },
            type: "POST",
            success: function(msg) {
                if (msg == '') {
                    $('#list_table tbody').html('<tr><td>COA not found.</td></tr>');
                } else {
                    $('#list_table tbody').html(msg);
                    self.initTable();
                    $('#list_table tbody .cutted_cell').tooltip(self.tooltipOptions);
                }
                self.endLoadingMask('#left_column .table_list_scroll_block');
            },
            error: function () {
                $('#list_table tbody').html('<tr><td>COA not found.</td></tr>');
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

    /**
     * Add loading mask
     */
    loadingMask: function(item) {
        $(item).scrollTop(0).prepend("<div class='loadinng_mask'></div>");
    },

    /**
     * Remove loading mask
     */
    endLoadingMask: function(item) {
        $(item + ' .loadinng_mask').remove();
    },

    /**
     * Updates self.selected_coas array
     */
    updateSelectedArray: function () {
        var self = this;
        self.selected_coas = [];
        $('.coa_list_table .list_checkbox').each(function (){

            if ($(this).attr("checked")) {
                self.selected_coas.push($(this).val());
            }
        });

        if (self.selected_coas.length > 0) {
            $('#remove_coa').removeClass('not_active');
        } else {
            $('#remove_coa').addClass('not_active');
        }

    },

    prependCoaCreationBlock : function () {
        var div = $('.table_list_scroll_block');

        //delete previous
        var prev = $('#coa_adding_form');
        prev.remove();

        $.ajax({
            url: "/coa/getcoacreateform",

            type: "POST",
            success: function(html) {

                //$(html).hide().prependTo(table).fadeIn("slow");
                div.prepend(html).fadeIn('slow');
            }
        });
    },

    addCoaRecord : function () {
        var table = $('.coa_list_table');

        //1 get form for coa creation
        $.ajax({
            url: "/coa/CreateCoaEntry",
            data: $('#coa_adding_form').serialize(),
            type: "POST",
            success: function(html) {
                if (html === 'success') {
                    window.location = '/coa';
                } else {

                }
            }
        });
    }



}