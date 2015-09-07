function ClientsAdminChange() {
    this.init();
}

ClientsAdminChange.prototype = {
    /**
     * Timeout ul
     */
    timeout: false,

    /**
     * Click timeout
     */
    timeoutClick: false,


    /**
     * Client Id
     */
    clientID: false,

    /**
     * Initialize method
     */
    init: function () {
        var self = this;

        // submit changes
        $('#admin_submit').click(function() {
            if ($('#admin_submit').attr('data') == 'tab1' && self.clientID != 0) {
                var url = '/admin/default/changeclientadmins';
                var approveUsers = [];
                var rows = $('#client_admin_change_users_table tbody tr');
                var i = 1;
                rows.each(function() {
                    var userId = $(this).attr('id');
                    userId = userId.slice(4);
                    if (userId == 0) {
                        return;
                    }
                    var dropdown_cell_value = $(this).find('.dropdown_cell_value').text();

                    if (dropdown_cell_value == 'Cl. Admin') {
                        approveUsers[i] = userId + '=ClAdmin';
                        i++;
                    } else {
                        approveUsers[i] = userId + '=' + dropdown_cell_value;
                        i++;
                    }
                });

                if (approveUsers.length > 0) {
                    var urlPart = approveUsers.join('&');
                    url = url + '?clientID='+ self.clientID + urlPart;
                    window.location = url;
                } else {
                    return;
                }
            }
        });

        $('#clients_list_sidebar_admin_change #clients-grid-table tbody tr').click(function() {
            $('#clients_list_sidebar_admin_change #clients-grid-table tbody tr').css('background', 'none');
            $(this).css('background-color', '#dFdDdD');
            var clientId = $(this).attr('id');
            clientId = clientId.slice(6);
            var companyName = $(this).find('td').first().text();
            if (clientId == 0) {
                self.clientID = 0;
                $('#company_name_row').text('Client: Select a client');
                $('#client_admin_change_users table tbody').html('<tr id="user0"><td colspan="3">Select a client to populate this grid</td></tr>');
            } else {
                self.showClientUsersList(clientId, 'Client: ' + clientId + ' / ' + companyName);
                self.clientID = clientId;
            }
        });

        $('#company_name_input_admin_change').keydown(function() {
            clearTimeout(self.timeoutClick);
            self.timeoutClick = setTimeout(function() {
                self.updateClientsList();
                self.clientID = 0;
            }, 800);
        });
    },

    /**
     * Users grid initialize method
     */
    initUsersGrid: function() {
        $("#client_admin_change_users td.dropdown_cell").click(function(event) {
            event.stopPropagation();
            $(this).parent().trigger('click');
            $("#client_admin_change_users td.dropdown_cell ul:visible").slideUp(150);
            $(this).find('ul').slideDown(150);
        });

        $("#client_admin_change_users td.dropdown_cell ul").mouseout(function() {
            var ul = $(this);
            self.timeout = setTimeout(function() {
                ul.slideUp(150);
            }, 100);
        });

        $("#client_admin_change_users td.dropdown_cell ul").mouseover(function() {
            if (self.timeout) {
                clearTimeout(self.timeout);
                self.timeout = false;
            }
        });

        $("#client_admin_change_users td.dropdown_cell ul li").click(function(event) {
            event.stopPropagation();
            var text = $(this).text();
            var cell = $(this).parent().parent().parent();
            var dropdown_cell_value = cell.find('.dropdown_cell_value');
            dropdown_cell_value.text(text);
            $("#client_admin_change_users td.dropdown_cell ul:visible").slideUp(150);
        });

        $('body').click(function() {
            $("#client_admin_change_users td.dropdown_cell ul:visible").slideUp(150);
        });

        $('#client_admin_change_users_table tbody tr').click(function() {
            $('#client_admin_change_users_table tbody tr').css('background', 'none');
            $(this).css('background-color', '#dFdDdD');
        });
    },

    /**
     * Shows client's users list
     */
    showClientUsersList: function (clientId, companyName) {
        var self = this;
        $('#client_admin_change_users > div').scrollTop(0).prepend("<div class='loadinng_mask_dark'></div>");
        $.ajax({
            url: "/admin/default/getclientuserslist",
            data: {clientId: clientId},
            type: "POST",
            success: function(msg) {
                $('#client_admin_change_users table tbody').html(msg);
                $('#company_name_row').text(companyName);
                self.initUsersGrid();
                $('#client_admin_change_users > div .loadinng_mask_dark').remove();
            }
        });
    },

    /**
     * Updates clients list
     */
    updateClientsList: function () {
        var self = this;
        var companyName = $('#company_name_input_admin_change').val();
        $('#clients-grid > div').scrollTop(0).prepend("<div class='loadinng_mask'></div>");
        $.ajax({
            url: "/admin/default/getfilteredclientslist",
            data: {companyName: companyName},
            type: "POST",
            success: function(msg){
               $('#clients-grid-table tbody').html(msg);
               $('#company_name_row').text('Client: Select a client');
                self.clientID = 0;
               $('#client_admin_change_users table tbody').html('<tr id="user0"><td colspan="3">Select a client to populate this grid</td></tr>');
               self.init();
               $('#clients-grid > div .loadinng_mask').remove();
            }
        });
    }
 }