function UsersDocReassign() {
    var self = this;

    this.init();
    this.initTable();
    this.initProjectsCell();

    // Submit changes
    $('#admin_submit').click(function() {
        if ($('#admin_submit').attr('data') == 'tab3') {
            var url = '/admin/default/reassigndocumentsclients';
            if (self.documentsToReassign.length > 0 && self.userToShow != 0) {
                $.ajax({
                    url: url,
                    data: {docs: self.documentsToReassign, userId: self.userToShow},
                    type: "POST",
                    async: false,
                    success: function(msg) {
                        window.location = '/admin?tab=doc_reassign';
                    }
                });
            } else {
                return;
            }
        }
    });

}


UsersDocReassign.prototype = {

    /**
     * Timeout ul
     */
    timeout: false,

    /**
     * Users to show documents
     */
    userToShow: 0,

    /**
     * Documents to reassign
     */
    documentsToReassign: [],

    /**
     * Last uploaded document
     */
    lastUploadedDocument: false,

    /**
     * Initialize method
     */
    init: function () {
        var self = this;
        $( "#date_to_filter_docs" ).datepicker();

        $('#date_to_filter_docs').change(function() {
             self.updateDocumentsList();
        });

        $('#user_name_row_doc_reassign').click(function() {
            show_modal_box('#find_user_docs');
        });

        $('#find_user_docs #find_user_for_docs').click(function() {
            self.findUsers();
        });
    },

    /**
     * Table initialize method
     */
    initTable: function () {
        var self = this;

        $("#docs_users_grid td.dropdown_cell").click(function(event) {
            event.stopPropagation();
            $(this).parent().trigger('click');
            $("#docs_users_grid td.dropdown_cell ul:visible").slideUp(150);
            $(this).find('ul').slideDown(150);
        });

        $("#docs_users_grid td.dropdown_cell ul").mouseout(function() {
            var ul = $(this);
            self.timeout = setTimeout(function() {
                ul.slideUp(150);
            }, 100);
        });

        $("#docs_users_grid td.dropdown_cell ul").mouseover(function() {
            if (self.timeout) {
                clearTimeout(self.timeout);
                self.timeout = false;
            }
        });

        $("#docs_users_grid td#doc_client ul li").click(function(event) {
            event.stopPropagation();

            var clientId = $(this).find('.user_client_id').text();
            var clientName = $(this).find('.user_client_name').text();

            var cell = $(this).parent().parent().parent();
            cell.attr('data', clientId);
            var row = cell.parent();

            var dropdown_cell_value = cell.find('.dropdown_cell_value');
            dropdown_cell_value.text(clientId + ' / ' + clientName);
            var projectCell = row.find('td#doc_project');

            self.updateProjects(clientId, projectCell, row);

            $("#docs_users_grid td.dropdown_cell ul:visible").slideUp(150);
        });

        $('body').click(function() {
            $("#docs_users_grid td.dropdown_cell ul:visible").slideUp(150);
        });

        $('#docs_users_grid tbody tr').click(function() {
            $('#docs_users_grid tbody tr').css('background', 'none');
            $(this).css('background-color', '#dFdDdD');
            var docId = $(this).attr('id');
            var filename = $(this).find("td:eq(1)").find('span').attr('title');

            docId = docId.slice(3);
            if (docId != self.lastUploadedDocument) {
                self.getUserFile(docId,filename);
                self.lastUploadedDocument = docId;
            }
        });
    },

    /**
     * Initialize projects cell
     */
    initProjectsCell: function(row) {
        var self = this;
        var li = $("#docs_users_grid td#doc_project ul li");
        row = row ? row : false;

        if (row) {
            li = row.find("td#doc_project ul li");
        }

        li.click(function(event) {
            event.stopPropagation();

            $("#docs_users_grid td#doc_project ul").slideUp(150);

            var projectId = $(this).find('.user_project_id').text();
            var projectName = $(this).find('.user_project_name').text();

            var cell = $(this).parent().parent().parent();
            var row = cell.parent();

            var dropdown_cell_value = cell.find('.dropdown_cell_value');
            dropdown_cell_value.text(projectId + ' / ' + projectName);
            var clientCell = row.find('td#doc_client');
            var clientID = clientCell.attr('data');

            // Set client_id value to document
            var docId = row.attr('id');
            docId = docId.slice(3);
            if (docId != 0 && clientID != 0 && projectId != 0) {
                var number = 0
                if (self.documentsToReassign.length > 0) {
                    number = self.documentsToReassign.length
                    for (var key in self.documentsToReassign) {
                        if (self.documentsToReassign[key][0] == docId) {
                            self.documentsToReassign[key][1] = clientID;
                            self.documentsToReassign[key][2] = projectId;
                            return;
                        }
                    }
                }
                self.documentsToReassign[number] = [];
                self.documentsToReassign[number][0] = docId;
                self.documentsToReassign[number][1] = clientID;
                self.documentsToReassign[number][2] = projectId;
            }
        });
    },

    /**
     * Initialize document file
     */
    initDocumentFile: function() {
        var user_file_view = new DocumentView('#document_thumbnail_file', '#tab1_block', '#document_block', 350, 45, 10);
    },

    /**
     * Updates documents list
     */
    updateDocumentsList: function () {
        var self = this;
        var date = $('#date_to_filter_docs').val();
        console.log ('userToShow',self.userToShow);
        if (self.userToShow != 0) {
            $('#user_documents > div').scrollTop(0).prepend("<div class='loadinng_mask_dark'></div>");
            $.ajax({
                url: "/admin/default/getfiltereddocumentslist",
                data: {date: date, userId: self.userToShow},
                type: "POST",
                success: function(msg) {
                    $('#docs_users_grid tbody').html(msg);
                    self.initTable();
                    self.initProjectsCell();
                    self.documentsToReassign = [];
                    $('#document_thumbnail_file').html('<span>Choose Document from the List to Preview</span>');
                    $('#user_documents > div .loadinng_mask_dark').remove();
                }
            });
        }
    },

    /**
     * Update projects for client
     * @param clientId
     * @param projectCell
     */
    updateProjects: function(clientId, projectCell, row) {
        var self = this;
        if (self.userToShow != 0) {
            $.ajax({
                url: "/admin/default/getuserclientprojects",
                data: {clientId: clientId, userId: self.userToShow},
                type: "POST",
                dataType: 'json',
                success: function(data) {
                    row.find('#doc_project div.dropdown_cell_ul ul').html(data.list);
                    row.find('#doc_project span.dropdown_cell_value').html(data.projectID + ' / ' + data.projectName);
                    self.initProjectsCell();

                    // Set client_id value to document
                    var docId = row.attr('id');
                    docId = docId.slice(3);
                    if (docId != 0 && clientId != 0) {
                        var number = 0
                        if (self.documentsToReassign.length > 0) {
                            number = self.documentsToReassign.length
                            for (var key in self.documentsToReassign) {
                                if (self.documentsToReassign[key][0] == docId) {
                                    self.documentsToReassign[key][1] = clientId;
                                    self.documentsToReassign[key][2] = data.projectID;
                                    return;
                                }
                            }
                        }
                        self.documentsToReassign[number] = [];
                        self.documentsToReassign[number][0] = docId;
                        self.documentsToReassign[number][1] = clientId;
                        self.documentsToReassign[number][2] = data.projectID;
                    }
                }
            });
        }
    },

    /**
     * Find users and show below search button
     */
    findUsers: function() {
        var self = this;
        var login = $('#user_login_docs').val();
        $.ajax({
                url: "/admin/default/finduserbylogin",
                data: {login : login},
                type: "POST",
                success: function(msg){
                    if (msg) {
                        $("#find_user_docs #users_searsh_res_box_for_docs").slideUp(200);
                        setTimeout(function() {
                            $("#find_user_docs #users_searsh_res_box_for_docs").html(msg).slideDown(200);
                            $('#show_users_doc').click(function() {
                                var userId = $(this).attr('data');
                                var userName = $(this).parent().parent().find('td').first().text();
                                $('#user_name_row_doc_reassign').text('User: ' + userName);
                                close_modal_box('#find_user_docs');
                                self.userToShow = userId;
                                self.updateDocumentsList();
                            });
                        }, 200);
                    }
                }
        });
    },

    /**
     * Get user file html and put it in #document_thumbnail block
     * @param docId
     */
    getUserFile: function(docId,filename) {
        var self = this;

        $.ajax({
            url: "/admin/default/getuserfile",
            data: {docId : docId},
            type: "POST",
            success: function(msg){
                if (msg) {

                    $('#document_thumbnail_file').html(msg);
                    $('#fileinfo_line').text(filename+' '+docId);

                    self.initDocumentFile();
                }
            }
        });
    }
}