function UserClientAssign() {
    var self = this
    this.initSidebar();
    this.initUsersGrid();
    this.initProjects();

    $('#submit_user_assign').click(function() {
        if (self.clientID != 0 && self.userID != 0 && self.projectID != 0) {
            var url = '/admin/default/assignusertoclient';
            url = url + '?clientID='+ self.clientID + '&projectID='+ self.projectID + '&userID=' + self.userID;
            window.location = url;
        } else {
            return;
        }
    });
}

UserClientAssign.prototype = {
    /**
     * Click timeout
     */
    timeoutClick: false,

    /**
     * Client Id
     */
    clientID: false,

    /**
     * Project Id
     */
    projectID: false,

    /**
     * User Id
     */
    userID: false,

    /**
     * Initialize method
     */
    initSidebar: function () {
        var self = this;

        $('#clients_list_sidebar_user_assign #clients_grid_table_user_assign tbody tr').click(function() {
            $('#clients_list_sidebar_user_assign #clients_grid_table_user_assign tbody tr').css('background', 'none');
            $(this).css('background-color', '#dFdDdD');
            var clientId = $(this).attr('id');
            clientId = clientId.slice(6);
            var companyName = $(this).find('td').first().text();
            if (clientId == 0) {
                self.clientID = 0;
                $('#company_name_row_user_assign').text('Client: Select a client');
            } else {
                $('#company_name_row_user_assign').text('Client: ' + clientId + ' / ' + companyName);
                self.clientID = clientId;
            }
            self.updateProjectsList();
        });



        $('#company_name_input_user_assign').keydown(function() {
            clearTimeout(self.timeoutClick);
            self.timeoutClick = setTimeout(function() {
                self.updateClientsList();
                self.clientID = 0;
                self.projectID = 0;
                self.updateProjectsList();
            }, 800);
        });
    },

    /**
     * Initialize projects grid
     */
    initProjects: function() {
        var self = this;

        $('#clients_projects_grid_user_assign #clients_projects_grid_table_user_assign tbody tr').click(function() {
            $('#clients_projects_grid_user_assign #clients_projects_grid_table_user_assign tbody tr').css('background', 'none');
            $(this).css('background-color', '#dFdDdD');
            var projectId = $(this).attr('id');
            projectId = projectId.slice(7);
            var projectName = $(this).find('td').first().text();
            if (projectId == 0) {
                self.projectID = 0;
                $('#project_name_row_user_assign').text('Project: Select a project');
            } else {
                $('#project_name_row_user_assign').text('Project: ' + projectId + ' / ' + projectName);
                self.projectID = projectId;

                self.getClientUsers(self.clientID,self.projectID);
            }
        });
    },

    /**
     * Users grid initialize method
     */
    initUsersGrid: function() {
        var self = this;

        $('#all_users_grid tbody tr').click(function() {
            $('#all_users_grid tbody tr').css('background', 'none');
            $(this).css('background-color', '#dFdDdD');
            var userName = $(this).find('td').first().text();
            var userID = $(this).attr('id');
            userID = userID.slice(4);
            if (userID != 0) {
                $('#user_name_row_user_assign').text('User: ' + userName);
            } else {
                $('#user_name_row_user_assign').text('User: Select a user');
            }
            self.userID = userID;
        });

        $('#user_to_filter_last_name').keydown(function() {
            clearTimeout(self.timeoutClick);
            self.timeoutClick = setTimeout(function() {
                self.updateUsersList();
                $('#user_name_row_user_assign').text('User: Select a user');
                self.userID = 0;
            }, 800);
        });

        $("a.delete_exsting").click(function (e) {
            e.preventDefault();
            var client_id = $(this).data('client');
            var project_id = $(this).data('project');
            var user_to_delete = $(this).data('uid');

            $.ajax({
                url: "/admin/default/ManageExistingUsersList",
                data: {
                    client_id: client_id,
                    project_id:project_id,
                    user_to_delete:user_to_delete
                },
                type: "POST",
                success: function(msg){
                    $('#all_users_grid1 tbody').html(msg);

                    //self.userID = 0;
                    //self.initUsersGrid();
                    $('#all_users_grid1 .loadinng_mask_dark').remove();
                }
            });

        });

    },

    /**
     * Updates clients list
     */
    updateClientsList: function () {
        var self = this;
        var companyName = $('#company_name_input_user_assign').val();
        $('#clients-grid_user_assign > div').scrollTop(0).prepend("<div class='loadinng_mask'></div>");
        $.ajax({
            url: "/admin/default/getfilteredclientslist",
            data: {companyName: companyName},
            type: "POST",
            success: function(msg){
                $('#clients_grid_table_user_assign tbody').html(msg);
                self.clientID = 0;
                $('#company_name_row_user_assign').text('Client: Select a client');
                self.initSidebar();
                $('#clients-grid_user_assign > div .loadinng_mask').remove();
            }
        });
    },

    /**
     * Updates projects list
     */
    updateProjectsList: function () {
        var self = this;
        $('#clients_projects_grid_user_assign > div').scrollTop(0).prepend("<div class='loadinng_mask'></div>");
        $.ajax({
            url: "/admin/default/getclientsprojectslist",
            data: {clientID: self.clientID},
            type: "POST",
            success: function(msg){
                $('#clients_projects_grid_table_user_assign tbody').html(msg);
                self.projectID = 0;
                $('#project_name_row_user_assign').text('Project: Select a project');
                self.initProjects();
                $('#clients_projects_grid_user_assign > div .loadinng_mask').remove();
            }
        });
    },

    /**
     * Updates clients list
     */
    updateUsersList: function () {
        var self = this;
        var lastname = $('#user_to_filter_last_name').val();
        $('#all_users > div').scrollTop(0).prepend("<div class='loadinng_mask_dark'></div>");
        $.ajax({
            url: "/admin/default/getfiltereduserslist",
            data: {lastname: lastname},
            type: "POST",
            success: function(msg){
                $('#all_users_grid tbody').html(msg);
                $('#user_name_row_user_assign').text('User: Select a user');
                self.userID = 0;
                self.initUsersGrid();
                $('#all_users > div .loadinng_mask_dark').remove();
            }
        });
    },

    getClientUsers:function(client_id,project_id) {
        var self = this;
        var lastname = $('#user_to_filter_last_name').val();
        $('#all_users_grid1 tr:eq(0) td').prepend("<div class='loadinng_mask_dark'></div>");
        $.ajax({
            url: "/admin/default/ManageExistingUsersList",
            data: {
                client_id: client_id,
                project_id:project_id
            },
            type: "POST",
            success: function(msg){
                $('#all_users_grid1 tbody').html(msg);

                //self.userID = 0;
                self.initUsersGrid();
                $('#all_users_grid1 .loadinng_mask_dark').remove();
            }
        });
    }

 }