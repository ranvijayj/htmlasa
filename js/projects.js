function Projects() {
    this.init();
}

Projects.prototype = {
    /**
     * Cancel editing of project if any Payment is linking to it
     */
    editingAccess: 0,

    /**
     * Current project
     */
    currentProject: 0,

    /**
     * Current Po Formatting
     */
    currentPoFormatting: 0,

    /*
     * Initialize method
     */
    init: function() {
        var self = this;

        $('#project_select').change(function() {
            var projectId = $(this).val();
            if (projectId == 0) {
                $('#company_info_sidebar_block').html('');
                self.currentProject = 0;
            } else if (projectId == 'add') {
                console.log('add project attempt');
                $('#company_info_sidebar_block').html('');
                self.currentProject = 0;
            } else {
                self.getProjectInfo(projectId);
                self.currentProject = projectId;
            }

            $("#bank_account_select option:first-child").attr('selected', true);
        });
    },

    /**
     * Get project info
     * @param int projectId
     */
    getProjectInfo: function(projectId) {
        var self = this;
        $.ajax({
            url: "/myaccount/getprojectinfo",
            data: {projectId: projectId},
            type: "POST",
            dataType: 'json',
            async: false,
            success: function(data) {
                $('#company_info_sidebar_block').html(data.info).show();
                self.editingAccess = data.editingAccess;
                self.currentPoFormatting = data.currentPoFormatting;
            }
        });
    }
}