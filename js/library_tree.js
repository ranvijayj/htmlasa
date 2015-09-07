function LibraryTree(activeTab,projectId,userType) {
    this.init(activeTab,projectId,userType);
    this.initMainActionButtons();
}

LibraryTree.prototype = {

    /**
     * Tab to review
     */
    activeTab: 'cabinets_table',

    /**
     * Cabinets and shelves query string
     */
    cabinetsQuery: '',
    shelvesQuery: '',
    batchesQuery: '',

    /**
     * Sort directions
     */
    sortDirection: 'ASC',

    /**
     * Search timeout
     */
    timeoutSearch:false,

    /**
     * If folder or binder has documents
     */
    hasDocuments: false,

    /**
     * Current project id
     */
    projectId: 'all',
    userType: 'all',

    /**
     * Initialize method
     */
    init: function(activeTab,projectId,userType) {

        console.log ("active tab", activeTab);

        var self = this;

        self.projectId = projectId;
        self.userType = userType;



        self.initTree('cabinets_table');
        self.initTree('shelves_table');
        if (self.userType != 'User') {
            $('#cabinets_actions').show();
        } else {
            $('#cabinets_actions').hide();
        }

        if (activeTab != 'tab3') {
            $('#view_section_docs_batches').hide();
            $('#view_section_docs').show();
        }


        $('.tabs li a').click(function() {
            var tabNumber = $(this).attr('href');
            if (tabNumber == '#tab1') {
                $('#search_field').show();
                $('#batch_search_field').hide();

                $('#view_section_docs_batches').hide();
                $('#view_section_docs').show();

                self.activeTab = 'cabinets_table';
                $('#search_field').val(self.cabinetsQuery);
                if (self.userType != 'User') {
                    $('#cabinets_actions').show();
                } else {
                    $('#cabinets_actions').hide();
                }
                $('#shelves_actions').hide();
            } else if (tabNumber == '#tab2') {
                $('#search_field').show();
                $('#batch_search_field').hide();

                $('#view_section_docs_batches').hide();
                $('#view_section_docs').show();

                self.activeTab = 'shelves_table';
                $('#search_field').val(self.shelvesQuery);
                $('#cabinets_actions').hide();
                if (self.userType != 'User') {
                $('#shelves_actions').show();
                }
            } else if (tabNumber == '#tab3'){
                self.activeTab = 'batches';
                $('#search_field').hide();
                $('#batch_search_field').show();

                $('#view_section_docs').hide();
                $('#view_section_docs_batches').show();

                $('#search_field').val(self.batchesQuery);
                $('#cabinets_actions').hide();
                $('#shelves_actions').hide();

            }

            if ($('#' + self.activeTab + ' .selected_item').length == 1) {
                self.getItemInfo($('#' + self.activeTab + ' .selected_item'));
            } else {
                $('#storage_info').html('<span class="sidebar_block_header">Details:</span>');
            }
        });

        if (activeTab == 'tab1') {
            self.activeTab = 'cabinets_table';
        } else if (activeTab == 'tab2') {
            self.activeTab = 'shelves_table';
        } else if (activeTab == 'tab3') {
            self.activeTab = 'batches_table';
        }

        $('#search_field').keydown(function() {
            clearTimeout(self.timeoutSearch);
            self.timeoutSearch = setTimeout(function() {
                self.updateTree();
            }, 800);
        });

        $('#shelves_names_header, #cabinets_names_header').click(function() {
            if (self.sortDirection == 'ASC') {
                self.sortDirection = 'DESC';
            } else {
                self.sortDirection = 'ASC';
            }
            self.addSortingArrow($(this));
            self.updateTree();
        });

        $('#view_section_docs').click(function() {
            self.viewSectionDocs();
        });

        $('#dialogmodal a').click(function() {
            self.showForm($(this));
        });

        self.resetTreeActions();

        if ($('#' + self.activeTab + ' .selected_item').length == 1) {
            self.scrollToSelectedItem($('#' + self.activeTab + ' .selected_item'));
            self.getItemInfo($('#' + self.activeTab + ' .selected_item'));
        }

        $('.library_year_link').click(function() {
            var year = $(this).data('year');
            self.changeYear(year, '/library');
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
     * Initialize tree actions
     */
    initActionsButtons: function() {
        var self = this;
        if (this.activeTab == 'cabinets_table') {
            $('#cabinets_actions .library_action').click(function() {
                self.showForm($(this));
            });
        } else if (this.activeTab == 'shelves_table') {
            $('#shelves_actions .library_action').click(function() {
                self.showForm($(this));
            });
        }
    },

    /**
     * Initialize main actions
     */
    initMainActionButtons: function() {
        var self = this;
        $('#cabinets_actions .library_action').click(function() {
            self.showForm($(this));
        });
        $('#shelves_actions .library_action').click(function() {
            self.showForm($(this));
        });
    },

    /**
     * Process tree
     * @param row
     */
    processTree: function(row) {
        var rowType = row.data('rowType');
        var status = row.find('.tree_nav').attr('data-status');
        var storageType = row.data('storage');
        if (status == 'closed') {
            if (rowType == 'storage') {
                this.showStorageSections(row, storageType);
            } else if (rowType == 'drawer') {
                this.showDrawerSections(row);
            } else if (rowType == 'section') {
                this.showSectionSubsections(row);
            }
        } else {
            if (rowType == 'storage') {
                this.hideStorageSections(row, storageType);
            } else if (rowType == 'drawer') {
                this.hideDrawerSections(row);
            } else if (rowType == 'section') {
                this.hideSectionSubsections(row);
            }
        }
    },

    /**
     * Show storage sections
     * @param row
     * @param storageType
     */
    showStorageSections: function(row, storageType) {
        if (row.parent().find('.drawer_block').length > 0) {
            row.parent().find('.drawer_block').show();
        } else {
            row.parent().find('.section_block').show();
        }
        row.find('.tree_nav').attr('data-status', 'opened');
        row.find('.tree_nav .plus_icon').removeClass('plus_icon').addClass('minus_icon');
        if (storageType == '0') {
            row.find('.tree_nav .cabinet_closed').removeClass('cabinet_closed').addClass('cabinet_opened');
        }
    },

    /**
     * Show drawer sections
     * @param row
     * @param storageType
     */
    showDrawerSections: function(row, storageType) {
        row.parent().find('.section_block').show();
        row.find('.tree_nav').attr('data-status', 'opened');
        row.find('.tree_nav .plus_icon').removeClass('plus_icon').addClass('minus_icon');
    },

    /**
     * Show section subsections
     * @param row
     */
    showSectionSubsections: function(row) {
        row.parent().find('.subsection_block').show();
        row.find('.tree_nav').attr('data-status', 'opened');
        row.find('.tree_nav .plus_icon').removeClass('plus_icon').addClass('minus_icon');
        row.find('.tree_nav .folder_closed').removeClass('folder_closed').addClass('folder_opened');
    },

    /**
     * Hide drawer sections
     * @param row
     * @param storageType
     */
    hideDrawerSections: function(row, storageType) {
        var self = this;

        row.parent().find('.section_block').hide();

        row.parent().find('.section_block .table_row').each(function() {
            self.hideSectionSubsections($(this));
        });

        row.find('.tree_nav').attr('data-status', 'closed');
        row.find('.tree_nav .minus_icon').removeClass('minus_icon').addClass('plus_icon');
    },

    /**
     * Hide storage sections
     * @param row
     * @param storageType
     */
    hideStorageSections: function(row, storageType) {
        var self = this;

        if (row.parent().find('.drawer_block').length > 0) {
            row.parent().find('.drawer_block').hide();

            row.parent().find('.drawer_block .table_row').each(function() {
                self.hideDrawerSections($(this));
            });
        } else {
            row.parent().find('.section_block').hide();

            row.parent().find('.section_block .table_row').each(function() {
                self.hideSectionSubsections($(this));
            });
        }

        row.find('.tree_nav').attr('data-status', 'closed');
        row.find('.tree_nav .minus_icon').removeClass('minus_icon').addClass('plus_icon');
        if (storageType == '0') {
            row.find('.tree_nav .cabinet_opened').removeClass('cabinet_opened').addClass('cabinet_closed');
        }
    },


    /**
     * Hide section subsections
     * @param row
     */
    hideSectionSubsections: function(row) {
        row.parent().find('.subsection_block').hide();
        row.find('.tree_nav').attr('data-status', 'closed');
        row.find('.tree_nav .minus_icon').removeClass('minus_icon').addClass('plus_icon');
        row.find('.tree_nav .folder_opened').removeClass('folder_opened').addClass('folder_closed');
    },

    /**
     * Update storages tree
     */
    updateTree: function(organizePage) {
        organizePage = organizePage ? organizePage : 0;
        var self = this;
        var query =  $('#search_field').val();

        if (self.activeTab == 'cabinets_table') {
            self.cabinetsQuery = query;
        } else if (self.activeTab == 'shelves_table') {
            self.shelvesQuery = query;
        } else if (self.activeTab == 'batches_table') {
            self.batchesQuery = query;
        }

        $('#' + self.activeTab).parent().scrollTop(0).prepend("<div class='loadinng_mask'></div>");
        $.ajax({
            url: "/library/gettreebysearchquery",
            data: {
                query: query,
                type: self.activeTab,
                sortDirection: self.sortDirection,
                organizePage: organizePage
            },
            type: "POST",
            success: function(msg) {
                if (msg == '') {
                    $('#' + self.activeTab).html('<div>Storages were not found.</div>');
                } else {
                    $('#' + self.activeTab).html(msg);
                }

                self.initTree(self.activeTab);
                $('#' + self.activeTab).parent().find( '.loadinng_mask').remove();

                if (!organizePage) {
                    self.resetTreeActions();
                    if ($('#' + self.activeTab + ' .selected_item').length == 1) {
                        self.getItemInfo($('#' + self.activeTab + ' .selected_item'));
                    } else {
                        $('#storage_info').html('<span class="sidebar_block_header">Details:</span>');
                    }
                }
            }
        });
    },

    /**
     * Add sort direction arrow
     * @param cell
     */
    addSortingArrow: function(cell) {
        if (this.sortDirection == 'ASC') {
            cell.removeClass('sorting_arrow_down').addClass('sorting_arrow_up');
        } else {
            cell.removeClass('sorting_arrow_up').addClass('sorting_arrow_down');
        }
    },

    /**
     * Process click on View Docs button
     */
    viewSectionDocs: function() {
        if ($('#' + this.activeTab + ' .selected_item').length != 1) {
            show_alert("You must select one item to view documents!", 450);
        } else {
            var row = $('#' + this.activeTab + ' .selected_item');
            var rowType = row.data('rowType');
            var storage = row.data('storage');
            var id = row.data('id');
            if (rowType == 'storage' || rowType == 'drawer') {
                if (this.activeTab == 'cabinets_table') {
                    show_alert("You must select folder or panel to view documents!", 470);
                } else {
                    show_alert("You must select binder or tab to view documents!", 450);
                }
            } else {
                this.checkDocuments(rowType, storage, id);
                if (this.hasDocuments) {
                    window.location = '/library/viewstorage';
                } else {
                    if (this.activeTab == 'cabinets_table') {
                        if (rowType == 'section') {
                            show_alert("This folder is empty!", 350);
                        } else {
                            show_alert("This panel is empty!", 350);
                        }
                    } else {
                        if (rowType == 'section') {
                            show_alert("This binder is empty!", 350);
                        } else {
                            show_alert("This tab is empty!", 350);
                        }
                    }
                }
            }
        }
    },

    checkDocuments: function(rowType, storage, id) {
        var self = this;

        $.ajax({
            url: "/library/checkstoragedocuments",
            data: {
                rowType: rowType,
                storage: storage,
                id: id
            },
            async: false,
            type: "POST",
            success: function(msg) {
                if (msg == '1') {
                    self.hasDocuments = true;
                } else {
                    self.hasDocuments = false;
                }
            }
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

        // todo: create ajax and php-generation
        if (this.activeTab == 'cabinets_table') {
            if (rowType == 'storage') {
                if (this.projectId != 'all' && self.userType != 'User') {
                    msg += '<button class="button left library_action" data-row-type="storage" data-storage="0" data-action="add">Add Cabinet</button>';
                }
               if (self.userType != 'User') {
                msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="edit">Edit Cabinet</button>';
               }
               if (deletable == '1') {
                   if (self.userType != 'User') {
                    msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="del">Del. Cabinet</button>';
                   }
               }
                if (self.userType != 'User') {
                    msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="add_sub">Add Folder</button>';
                }
            } else if (rowType == 'drawer') {
                var folderCategory = row.data('category');
                rowType = row.parent().parent().find('.table_row[data-row-type=storage]').data('rowType');
                storage = row.parent().parent().find('.table_row[data-row-type=storage]').data('storage');
                id = row.parent().parent().find('.table_row[data-row-type=storage]').data('id');

                if (folderCategory == 6) {
                    if (self.userType != 'User') {
                        msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="add_sub">Add Folder</button>';
                    }
                }
            } else if (rowType == 'section') {
               var folderCategory = row.data('category');

               if (folderCategory == 6) {
                   if (self.userType != 'User') {
                    msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="add">Add Folder</button>';
                   }
               }

                if (self.userType != 'User') {
                    msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="edit">Edit Folder</button>';
                }
                if (deletable == '1') {
                    if (self.userType != 'User') {
                        msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="del">Del. Folder</button>';
                    }
                }
                if (subsectionsCount < 6) {
                    if (self.userType != 'User') {
                        msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="add_sub">Add Panel</button>';
                    }
                }
            } else if (rowType == 'subsection') {
                if (subsectionsCount < 6) {
                    if (self.userType != 'User') {
                        msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="add">Add Panel</button>';
                    }
                }
                if (self.userType != 'User') {
                    msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="edit">Edit Panel</button>';
                }
                if (deletable == '1') {
                    if (self.userType != 'User') {
                        msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="del">Del. Panel</button>';
                    }
                }
            }
            $('#cabinets_actions').html(msg);
        } else if (this.activeTab == 'shelves_table') {
            if (rowType == 'storage') {
                if (this.projectId != 'all' && self.userType != 'User') {
                    msg += '<button class="button left library_action" data-row-type="storage" data-storage="1" data-action="add">Add Shelf</button>';
                }
                if (self.userType != 'User') {
                    msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="edit">Edit Shelf</button>';
                }
                if (deletable == '1') {
                    if (self.userType != 'User') {
                        msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="del">Del. Shelf</button>';
                    }
                }
                if (self.userType != 'User') {
                    msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="add_sub">Add Binder</button>';
                }
            } else if (rowType == 'section') {
                if (self.userType != 'User') {
                msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="add">Add Binder</button>';
                msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="edit">Edit Binder</button>';
                }
                if (deletable == '1') {
                    if (self.userType != 'User') {
                        msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="del">Del. Binder</button>';
                    }
                }
                if (subsectionsCount < 6) {
                    if (self.userType != 'User') {
                        msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="add_sub">Add Tab</button>';
                    }
                }
            } else if (rowType == 'subsection') {
                if (subsectionsCount < 6) {
                    if (self.userType != 'User') {
                        msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="add">Add Tab</button>';
                    }
                }
                if (self.userType != 'User') {
                msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="edit">Edit Tab</button>';
                }
                if (deletable == '1') {
                    if (self.userType != 'User') {
                        msg += '<button class="button left library_action" data-id="' + id + '" data-row-type="' + rowType + '" data-storage="' + storage + '" data-action="del">Del. Tab</button>';
                    }
                }
            }
            $('#shelves_actions').html(msg);
        }

        this.initActionsButtons();
    },

    /**
     * Reset tree actions
     */
    resetTreeActions: function() {
        if (this.activeTab == 'cabinets_table') {
            if ($('#' + this.activeTab + ' .selected_item').length == 1) {
                this.setTreeActions($('#' + this.activeTab + ' .selected_item'));
            } else if ($('#' + this.activeTab + ' .selected_item').length == 0 && this.projectId != 'all' && self.userType != 'User') {
                $('#cabinets_actions').html('<button class="button left library_action" data-row-type="storage" data-storage="0" data-action="add">Add Cabinet</button>');
                this.initActionsButtons();
            } else {
                $('#cabinets_actions').html('');
            }
        } else if (this.activeTab == 'shelves_table') {
            if ($('#' + this.activeTab + ' .selected_item').length == 1) {
                this.setTreeActions($('#' + this.activeTab + ' .selected_item'));
            } else if ($('#' + this.activeTab + ' .selected_item').length == 0 && this.projectId != 'all'&& self.userType != 'User') {
                $('#shelves_actions').html('<button class="button left library_action" class="create_storage" data-row-type="storage" data-storage="1" data-action="add">Add Shelf</button>');
                this.initActionsButtons();
            } else {
                $('#shelves_actions').html('');
            }
        } else {
            $('#shelves_actions').html('');
            $('#cabinets_actions').html('');
        }
    },

    /**
     * Show modal box with necessary form
     */
    showForm: function(button) {

        var rowType = button.data('rowType');
        var storage = button.data('storage');
        var id = button.data('id');
        var action = button.data('action');
        var back_url = button.data('backurl');

        if (action == 'del') {
            var button = $('#dialogmodal a');
            button.attr('href', '#').attr('data-action', 'delete').attr('data-storage', storage).attr('data-row-type', rowType).attr('data-id', id);
            show_dialog('Are you sure you want to delete this item?', 450);
        } else {
            $.ajax({
                url: "/library/getlibraryform",
                data: {
                    rowType: rowType,
                    storage: storage,
                    id: id,
                    action: action,
                    back_url:back_url
                },

                type: "POST",
                success: function(msg) {
                    if (action == 'delete') {
                        window.location = back_url ? back_url : '/library';
                    } else if (msg != '') {
                        $('#library_form_modal').html(msg);
                        show_modal_box('#library_form_modal');
                    }
                }
            });
        }
    },

    /**
     * Scroll list to selected item
     * @param row
     */
    scrollToSelectedItem: function(row) {
        var scrollTop = row.position().top - 52;
        if (scrollTop > 0) {
            $('#' + this.activeTab + ' .table_list_scroll_block').scrollTop(scrollTop);
        }
    },

    /**
     * Get item info for right sidebar
     * @param row
     */
    getItemInfo: function(row) {
        var rowType = row.data('rowType');
        var storage = row.data('storage');
        var id = row.data('id');
        if (rowType != 'drawer') {
            $.ajax({
                url: "/library/getiteminfo",
                data: {
                    rowType: rowType,
                    storage: storage,
                    id: id
                },
                type: "POST",
                success: function(msg) {
                    if (msg != '') {
                        $('#storage_info').html(msg);
                    } else {
                        $('#storage_info').html('<span class="sidebar_block_header">Details:</span>');
                    }
                }
            });
        } else {
            $('#storage_info').html('<span class="sidebar_block_header">Details:</span>');
        }
    },

    /**
     * Change year
     */
    changeYear: function(year, url) {
        $.ajax({
            url: "/library/changeyear",
            data: {
                year: year
            },
            async: false,
            type: "POST",
            success: function() {
                window.location = url;
            }
        });
    }
}