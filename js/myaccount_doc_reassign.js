function MyAccountDocReassign() {
    var self = this;

    this.init();
    this.initTable();
    this.initProjectsCell();

    // Submit changes
    $('#account_submit').click(function() {
        if ($('#account_submit').attr('data') == 'tab8') {
            var url = '/myaccount/reassigndocumentsclients';
            if (self.documentsToReassign.length > 0) {
                $.ajax({
                    url: url,
                    data: {docs: self.documentsToReassign, userId: self.userToShow},
                    type: "POST",
                    async: false,
                    success: function(msg) {
                        window.location = '/myaccount?tab=doc_reassign';
                    }
                });
            } else {
                return;
            }
        }
    });

}

MyAccountDocReassign.prototype = $.extend(UsersDocReassign.prototype, {

    /**
     * Initialize method
     */
    init: function () {
        var self = this;
        $( "#date_to_filter_docs" ).datepicker();

        $('#date_to_filter_docs').change(function() {
            self.updateDocumentsList();
        });
    },

    /**
     * Updates documents list
     */
    updateDocumentsList: function () {
        var self = this;
        var date = $('#date_to_filter_docs').val();
        $('#user_documents > div').scrollTop(0).prepend("<div class='loadinng_mask_dark'></div>");
        $.ajax({
            url: "/myaccount/getfiltereddocumentslist",
            data: {date: date},
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
    },

    /**
     * Update projects for client
     * @param clientId
     * @param projectCell
     */
    updateProjects: function(clientId, projectCell, row) {
        var self = this;

        $.ajax({
            url: "/myaccount/getuserclientprojects",
            data: {clientId: clientId},
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
    },

    /**
     * Get user file html and put it in #document_thumbnail block
     * @param docId
     */
    getUserFile: function(docId,filename) {
        var self = this;
        $.ajax({
            url: "/myaccount/getuserfile",
            data: {docId : docId},
            type: "POST",
            success: function(msg){
                if (msg) {

                    $('#fileinfo_line').text(filename+' '+docId);
                    $('#document_thumbnail_file').html(msg);
                    self.initDocumentFile();
                }
            }
        });
    }
});