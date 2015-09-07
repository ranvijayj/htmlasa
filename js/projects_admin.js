function ProjectsAdmin(enableAddProject) {
    this.init(enableAddProject);
}

ProjectsAdmin.prototype = $.extend(Projects.prototype, {
    /**
     * Enable add projects flag
     */
    enableAddProject: 0,

    /*
     * Initialize method
     */
    init: function(enableAddProject) {
        var self = this;

        self.enableAddProject = enableAddProject;

        $('#project_select').change(function() {
            var projectId = $(this).val();
            if (projectId == 0) {
                $('#company_info_sidebar_block').html('');
                self.currentProject = 0;
            } else if (projectId == 'add') {
                console.log('add project attempt');
                if (self.enableAddProject == 1) {
                    self.showNewProjectBox();
                } else {
                    $('#dialogmodal a').attr('href', '/myaccount?tab=service');
                    show_dialog('The Client has exceeded the number of Projects in its Service Level. Please navigate to Service Levels and add the number of Projects required. Thank you.', 540);
                }
                $("#project_select option:first-child").attr('selected', true);
                $('#company_info_sidebar_block').html('');
                self.currentProject = 0;
            } else {
                self.getProjectInfo(projectId);
                self.currentProject = projectId;
                self.initFieldsChanges();
            }

            $("#bank_account_select option:first-child").attr('selected', true);
        });
    },

    /**
     * Initialize fields changes
     */
    initFieldsChanges: function() {
        var self = this;

        // show project info form
        $('#company_info_sidebar_block .details_page_value').click(function() {
            if (self.editingAccess == 1) {
                $('#new_project_box h1').text('Edit Project');
                self.resetForm();
                $('#current_project_id').val(self.currentProject);

                var i = 0;
                $('#company_info_sidebar_block .details_page_value').each(function() {
                    var value_block = $(this);
                    var j = 0;
                    $('#new_project_form input[type=text]').each(function() {
                        if (i == j) {
                            $(this).val(value_block.text());
                        }
                        j++;
                    });
                    i++;
                });

                show_modal_box('#new_project_box', 260, 50);
            } else {
                show_alert("This project has already been assigned to a PO, AP or Payment and cannot be edited", 450);
            }
        });

        // show Po Formatting form
        $('#company_info_sidebar_block .formatting_details_page_value').click(function() {
            $('#current_po_formatting_id').val(self.currentPoFormatting);

            var i = 0;
            $('#company_info_sidebar_block .formatting_details_page_value').each(function() {
                var value_block = $(this);
                var j = 0;
                $('#po_formatting_form input[type=text], #po_formatting_form textarea, #po_formatting_form select').each(function() {
                    if (i == j) {
                        if ($(this).attr('id') == 'PoFormatting_PO_Format_Sig_Req') {
                            $(this).val(((value_block.text() == 'Yes') ? 1 : 0));
                        } else {
                            $(this).val(value_block.text());
                        }
                    }
                    j++;
                });
                i++;
            });

            show_modal_box('#po_formatting_block', 552, 70);
        });
    },

    /**
     * Show new account box
     */
    showNewProjectBox: function() {
        $('#new_project_box h1').text('New Project');
        this.resetForm();
        $('#current_project_id').val(0);
        show_modal_box('#new_project_box', 260, 50);
    },

    /**
     * Reset form
     */
    resetForm: function() {
        $('#new_project_form input[type=text]').val('');
        $('#new_project_form .errorMessage').hide();
    }
})