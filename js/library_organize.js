function LibraryOrganize(activeTab,projectId,userType) {
    this.init(activeTab,projectId,userType);

}

LibraryOrganize.prototype = $.extend(LibraryTree.prototype, {
    /**
     * If email is valid
     */
    validMail: false,

    /**
     * Initialize library
     */
    init: function(activeTab,projectId,userType) {
        var self = this;

        self.projectId = projectId;
        self.userType = userType;

        self.initTree('cabinets_table');
        self.initTree('shelves_table');

        $('#send_email').hide();
        $('#print_doc').hide();

        $('.tabs li a').click(function() {

            var tabNumber = $(this).attr('href');
            if (tabNumber == '#tab1') {
                self.activeTab = 'cabinets_table';
                $('#search_field').val(self.cabinetsQuery);
                self.resetDropdownMenu();
            } else if (tabNumber == '#tab2') {
                self.activeTab = 'shelves_table';
                $('#search_field').val(self.shelvesQuery);
                self.resetDropdownMenu();
            }

        });

        $('#dialogmodal a').click(function() {
            self.showForm($(this));
        });

        $('#search_field').keydown(function() {
            clearTimeout(self.timeoutSearch);
            self.timeoutSearch = setTimeout(function() {
                self.updateTree(1);
            }, 800);
        });

        $('#shelves_names_header, #cabinets_names_header').click(function() {
            if (self.sortDirection == 'ASC') {
                self.sortDirection = 'DESC';
            } else {
                self.sortDirection = 'ASC';
            }
            self.addSortingArrow($(this));
            self.updateTree(1);
        });

        $('#view_section_docs').click(function() {
            self.viewSectionDocs();
        });

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
                self.setCheckedCount();
            }, 10);
        });

        $('#unassigned_documents_table tbody tr').click(function (event) {
            event.stopPropagation();
            $(this).find(".list_checkbox").click();
        });

        $('.unassigned_documents_select, .unassigned_documents_select option').click(function(event) {
            event.stopPropagation();
        });

        $('#print_doc').click(function() {
            var doc_id = $(this).attr('data-id');
            if (doc_id > 0) {
                $.ajax({
                    url: "/documents/setdocumentidtoprint",
                    data: {doc_id: doc_id},
                    type: "POST",
                    async: false,
                    success: function() {
                        var url = '/documents/printdocument';
                        window.open(url, 'popup', 'toolbar=no, menubar=no, status=no, location=no, resizable=no, width=640, height=600');
                    }
                });
            }
        });

        $('#send_email').click(function() {
            show_modal_box('#askemailbox');
        });

        $('#doc_to_user_email').blur(function() {
            self.checkEmail();
        });

        // Send document by email action
        $('#send_doc_by_email').click(function() {
            var doc_id = $('#send_email').attr('data-id');

            if (self.validMail && doc_id > 0) {
                close_modal_box('#askemailbox');
                var email = $('#doc_to_user_email').val();
                $.ajax({
                    url: "/documents/senddocumentbyemail",
                    data: {email: email, doc_id: doc_id},
                    type: "POST",
                    success: function(msg) {
                        if (msg == 1) {
                            setTimeout(function() {
                                show_alert("Email was sent!", 250);
                            }, 200);
                        } else {
                            setTimeout(function() {
                                show_alert("Email was not sent!", 250);
                            }, 200);
                        }
                        $('#doc_to_user_email').val('');
                    }
                });
            } else {
                $('#doc_to_user_email').focus();
            }
        });

        $('#move_docs').click(function() {
            $('#unassigned_documents').submit();
        });

        $('#unassigned_documents').submit(function(event) {
            if ($(".list_checkbox:checked").length == 0) {
                event.preventDefault();
                show_alert("Please chose documents to move from right column!", 500);
            } else if ($('#organize_left_column .selected_item').length != 1) {
                event.preventDefault();
                show_alert("Please chose panel or tab in left column!", 400);
            } else {
                var row = $('#organize_left_column .selected_item');
                var rowType = row.attr('data-row-type');
                var storage = row.attr('data-storage');
                var id = row.attr('data-id');

                var cur_section = row.parent().parent().find("[data-row-type='section']");
                var sec_id = cur_section.data('id');

                if (rowType == 'subsection') {
                    $('#section_id_to_move').val(sec_id);
                    $('#subsection_id_to_move').val(id);
                    $('#subsection_type_to_move').val(storage);
                } else {
                    event.preventDefault();
                    show_alert("You must choose panel or tab in left column!", 400);
                }
            }
        });

        $('#library_years').change(function() {
            var year = $(this).val();
            self.changeYear(year, '/library/organize');
        });

        $('#unassigned_documents_table .list_checkbox').change(function () {

            var checked = $(this).attr('checked');
            var doc_id = $(this).val();
            var year = $(this).data('year');
            $.ajax({
                url:'/library/setUnassignedToSession',
                type: "POST",
                data: {
                    checked:checked,
                    year:year,
                    doc_id:doc_id
                },
                success: function () {
                    console.log ('done');
                }
            });

        });

        $('#menu_wrapper #files').change(function() {

            console.log($(this)[0].selectedIndex);

            if ($(this)[0].selectedIndex==1) $('#move_docs').click();
            if ($(this)[0].selectedIndex>=4) self.showFormOrg($(this));
        });


    },

    /**
     * Initialize tree
     * @param tree_type
     */
    initTree: function(tree_type) {
        var self = this;

        $("#" + tree_type + " .table_row").click(function() {
            $("#" + tree_type + " .table_row").removeClass('selected_item');
            $(this).addClass('selected_item');
            self.setTreeActions($(this));
            self.getItemInfo($(this));
        });

        $("#" + tree_type + " .tree_nav").click(function() {
            self.processTree($(this).parent().parent());
        });
    },


    /**
     * Set tree actions
     * @param row
     */
    setTreeActions: function(row) {

        var rowType = row.data('rowType');
        var storage = row.data('storage');
        var id = row.data('id');
        var deletable = row.data('deletable');
        var subsectionsCount = row.data('subsections');
        var msg = '';

        var select_menu = $('#menu_wrapper #files option');

        // todo: create ajax and php-generation
        if (this.activeTab == 'cabinets_table') {
            if (rowType == 'storage') {
                if (this.projectId != 'all' && self.userType != 'User') {
                    select_menu.eq(4).prop('disabled',false).attr('data-id',id).attr('data-row-type',rowType).attr('data-storage',storage);    //"Add cabinet" functionality
                }
                if (self.userType != 'User') {
                    select_menu.eq(5).prop('disabled',false).attr('data-id',id).attr('data-row-type',rowType).attr('data-storage',storage); //adding "Edit cabinet" functionality
                }

                if (deletable == '1') {
                    if (self.userType != 'User') {
                        select_menu.eq(6).prop('disabled',false).attr('data-id',id).attr('data-row-type',rowType).attr('data-storage',storage);  //adding "Delete func"
                    }
                }
                if (self.userType != 'User') {
                    select_menu.eq(7).prop('disabled',false).attr('data-id',id).attr('data-row-type',rowType).attr('data-storage',storage);  //adding "Add folder"
                }
            } else if (rowType == 'drawer') {
                //dropping what needed
                select_menu.eq(7).prop('disabled',true);

                var folderCategory = row.data('category');
                rowType = row.parent().parent().find('.table_row[data-row-type=storage]').data('rowType');
                storage = row.parent().parent().find('.table_row[data-row-type=storage]').data('storage');
                id = row.parent().parent().find('.table_row[data-row-type=storage]').data('id');

                if (folderCategory == 6) {
                    if (self.userType != 'User') {
                        select_menu.eq(7).prop('disabled',false).attr('data-id',id).attr('data-row-type',rowType).attr('data-storage',storage);  //adding "Add folder"
                    }
                }
            } else if (rowType == 'section') {
                console.log ('Section selected');

                //dropping what needed
                select_menu.eq(7).prop('disabled',true);

                var folderCategory = row.data('category');


                if (folderCategory == 6) {
                    if (self.userType != 'User') {
                        select_menu.eq(7).prop('disabled',false).attr('data-id',id).attr('data-row-type',rowType).attr('data-storage',storage);  //adding "Add folder"
                    }
                }

                if (self.userType != 'User') {
                    select_menu.eq(8).prop('disabled',false).attr('data-id',id).attr('data-row-type',rowType).attr('data-storage',storage);  //adding "Edit folder" func
                }

                if (deletable == '1') {
                    if (self.userType != 'User') {

                        select_menu.eq(9).prop('disabled',false).attr('data-id',id).attr('data-row-type',rowType).attr('data-storage',storage);  //adding "Del. folder" func
                    }
                }
                if (subsectionsCount < 6) {
                    if (self.userType != 'User') {
                        select_menu.eq(10).prop('disabled',false).attr('data-id',id).attr('data-row-type',rowType).attr('data-storage',storage);  //adding "Add panel" func
                    }
                }
            } else if (rowType == 'subsection') {
                //dropping what needed
                select_menu.eq(10).prop('disabled',true);
                select_menu.eq(10).attr('data-action','add');

                if (subsectionsCount < 6) {
                    if (self.userType != 'User') {
                        select_menu.eq(10).prop('disabled',false).attr('data-id',id).attr('data-row-type',rowType).attr('data-storage',storage);  //adding "Add panel" func
                    }
                }
                if (self.userType != 'User') {
                    select_menu.eq(11).prop('disabled',false).attr('data-id',id).attr('data-row-type',rowType).attr('data-storage',storage);  //adding "Edit panel" func
                }
                if (deletable == '1') {
                    if (self.userType != 'User') {
                        select_menu.eq(12).prop('disabled',false).attr('data-id',id).attr('data-row-type',rowType).attr('data-storage',storage);  //adding "Del panel" func
                    }
                }
            }
        } else if (this.activeTab == 'shelves_table') {
            if (rowType == 'storage') {
                if (this.projectId != 'all' && self.userType != 'User') {
                    //msg += '<button class="button left library_action" data-row-type="storage" data-storage="1" data-action="add">Add Shelf</button>';
                    select_menu.eq(13).prop('disabled',false).attr('data-id',id).attr('data-row-type','storage').attr('data-storage',1);  //adding "Add Shelf" func
                }
                if (self.userType != 'User') {
                    //msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="edit">Edit Shelf</button>';
                    select_menu.eq(14).prop('disabled',false).attr('data-id',id).attr('data-row-type',rowType).attr('data-storage',storage);  //adding "Edit Shelf" func
                }
                if (deletable == '1') {
                    if (self.userType != 'User') {
                        //msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="del">Del. Shelf</button>';
                        select_menu.eq(15).prop('disabled',false).attr('data-id',id).attr('data-row-type',rowType).attr('data-storage',storage);  //adding "Delete Shelf" func
                    }
                }
                if (self.userType != 'User') {
                    //msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="add_sub">Add Binder</button>';
                    select_menu.eq(16).prop('disabled',false).attr('data-id',id).attr('data-row-type',rowType).attr('data-storage',storage);  //adding "Add Binder" func
                }
            } else if (rowType == 'section') {
                select_menu.eq(16).prop('disabled',true);
                if (self.userType != 'User') {
                    select_menu.eq(16).prop('disabled',false).attr('data-id',id).attr('data-row-type',rowType).attr('data-storage',storage);  //adding "Add Binder" func
                    select_menu.eq(17).prop('disabled',false).attr('data-id',id).attr('data-row-type',rowType).attr('data-storage',storage);  //adding "Edit Binder" func
                    //msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="add">Add Binder</button>';
                    //msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="edit">Edit Binder</button>';
                }
                if (deletable == '1') {
                    if (self.userType != 'User') {
                        select_menu.eq(18).prop('disabled',false).attr('data-id',id).attr('data-row-type',rowType).attr('data-storage',storage);  //adding "Del Binder" func
                        //msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="del">Del. Binder</button>';

                    }
                }
                if (subsectionsCount < 6) {
                    if (self.userType != 'User') {
                        //msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="add_sub">Add Tab</button>';
                        select_menu.eq(19).prop('disabled',false).attr('data-id',id).attr('data-row-type',rowType).attr('data-storage',storage);  //adding "Add tab" func
                    }
                }
            } else if (rowType == 'subsection') {
                if (subsectionsCount < 6) {
                    if (self.userType != 'User') {
                        select_menu.eq(19).prop('disabled',false).attr('data-id',id).attr('data-row-type',rowType).attr('data-storage',storage);  //adding "Add tab" func
                    }
                }
                if (self.userType != 'User') {
                    select_menu.eq(20).prop('disabled',false).attr('data-id',id).attr('data-row-type',rowType).attr('data-storage',storage);  //adding "Edit tab" func
              //      msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="edit">Edit Tab</button>';
                }
                if (deletable == '1') {
                    if (self.userType != 'User') {
                        select_menu.eq(21).prop('disabled',false).attr('data-id',id).attr('data-row-type',rowType).attr('data-storage',storage);  //adding "Del tab" func
                        //msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="del">Del. Tab</button>';
                    }
                }
            }
        }


    },



    /**
     * Show modal box with necessary form
     */
    showFormOrg: function(select) {
        var self = this;

        var element =select.find(':selected');


        var rowType = element.data('rowType');
        var storage = element.data('storage');
        var id = element.data('id');
        var action = element.data('action');

        //reset select to first option
        select.val($("select option:first").val());

        if (action == 'del') {
            var button = $('#dialogmodal a');
            button.attr('href', '#').attr('data-action', 'delete').attr('data-storage', storage).attr('data-row-type', rowType).attr('data-id', id).attr('data-backurl','/library/organize');
            show_dialog('Are you sure you want to delete this item?', 450);
        } else {
            $.ajax({
                url: "/library/getlibraryform",
                data: {
                    rowType: rowType,
                    storage: storage,
                    id: id,
                    action: action,
                    back_url: '/library/organize'
                },

                type: "POST",
                success: function(msg) {
                    if (action == 'delete') {
                        //window.location = '/library/org';
                    } else if (msg != '') {
                        $('#library_form_modal').html(msg);
                        show_modal_box('#library_form_modal');
                    }

                }
            });
        }

    },

    markUrlForRedirect : function () {

    },

    resetDropdownMenu: function () {
        console.log('inside reset');
        var select_menu = $('#menu_wrapper #files option');
        select_menu.eq(4).prop('disabled',true);
        select_menu.eq(5).prop('disabled',true);
        select_menu.eq(6).prop('disabled',true);
        select_menu.eq(7).prop('disabled',true);
        select_menu.eq(8).prop('disabled',true);
        select_menu.eq(9).prop('disabled',true);
        select_menu.eq(10).prop('disabled',true);
        select_menu.eq(11).prop('disabled',true);
        select_menu.eq(12).prop('disabled',true);

    },

    /**
     * Sets count of checked items
     */
    setCheckedCount: function() {
        var checkedCount = $(".list_checkbox:checked").length;
        $("#number_items").text(checkedCount);
        if (checkedCount == 1) {
            var docId = $(".list_checkbox:checked").val();
            $('#send_email').attr('data-id', docId).show();
            $('#print_doc').attr('data-id', docId).show();
        } else {
            $('#send_email').hide();
            $('#print_doc').hide();
        }
    },

    /**
     * Check email
     */
    checkEmail: function () {
        var email = $('#doc_to_user_email').val();
        var pattern = /^([0-9a-zA-Z]([\-\.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][\-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})$/;
        if (!pattern.test(email)) {
            this.validMail = false;
            if (email != '') {
                $('#askemailbox .errorMessage').show();
            } else {
                $('#askemailbox .errorMessage').hide();
            }
        } else {
            this.validMail = true;
            $('#askemailbox .errorMessage').hide();
        }
    }
});