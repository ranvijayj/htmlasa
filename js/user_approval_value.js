function UserApprovalValue() {
    var self = this;
    this.init();

    $('#admin_submit').click(function() {

        /*
        * Next if used for cases when client was loaded not according user input but based on session data, so we populate clientID from previously found data
        * */
        if (!self.clientID) {
            self.clientID = $('#ua_loaded_client_id').data('id');
            $('#client_id_input').val(self.clientID);

        }

        if ($('#admin_submit').attr('data') == 'tab4' && self.clientID != 0) {
            var submit = false;

           //checking for at least one approver 100
            $('#appr_value_form .appr_value_input').each(function() {
                var value = $(this).val();
                if (value == 100) {
                    submit = true;
                }
            });

           /*if (submit) {*/
                $('#appr_value_form').submit();
           /*} else {
               show_alert("At least one Approver must have an Approval value of 100.", 500);
           }*/

        }
    });
}

UserApprovalValue.prototype = {

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

        $('#clients_list_sidebar_appr_value #clients_grid_table_appr_value tbody tr').click(function() {
            $('#clients_list_sidebar_appr_value #clients_grid_table_appr_value tbody tr').css('background', 'none');
            $(this).css('background-color', '#dFdDdD');
            var clientId = $(this).attr('id');
            clientId = clientId.slice(6);
            var companyName = $(this).find('td').first().text();
            if (clientId == 0) {
                self.clientID = 0;
                $('#client_id_input').val(0);
                $('#company_name_row').text('Client: Select a client');
                $('#client_admin_appr_value_table tbody').html('<tr id="user0"><td colspan="3">Select a client to populate this grid</td></tr>');
            } else {
                self.showClientUsersList(clientId, 'Client: ' + clientId + ' / ' + companyName);
                $('#client_id_input').val(clientId);
                self.clientID = clientId;
            }
        });

        $('#company_name_input_appr_value').keydown(function() {
            clearTimeout(self.timeoutClick);
            self.timeoutClick = setTimeout(function() {
                self.updateClientsList();
                self.clientID = 0;
                $('#client_id_input').val(0);
            }, 800);
        });
    },

    /**
     * Users grid initialize method
     */
    initUsersGrid: function() {
        var self = this;

        $('#client_admin_appr_value_table tbody tr').click(function() {
            $('#client_admin_appr_value_table tbody tr').css('background', 'none');
            $(this).css('background-color', '#dFdDdD');
        });

        $('.appr_value_input').blur(function() {
            var input = $(this);
            var value = input.val();
            var userId = input.attr('name');
            userId = userId.slice(6);
            userId = parseInt(userId);
            value = parseInt(value);

            $.ajax({
                url: "/admin/default/checkuserapprovalvalue",
                data: {userId: userId, value: value, clientId: self.clientID},
                type: "POST",
                async: false,
                success: function(data) {
                    input.val(data);
                }
            });
        });
    },

    /**
     * Shows client's users list
     */
    showClientUsersList: function (clientId, companyName) {
        var self = this;
        $('#appr_value_form > div').scrollTop(0).prepend("<div class='loadinng_mask_dark'></div>");
        $.ajax({
            url: "/admin/default/getclientuserslistapprvalue",
            data: {clientId: clientId},
            type: "POST",
            success: function(msg) {
                $('#client_admin_appr_value_table tbody').html(msg);
                $('#company_name_row_appr_value').text(companyName);
                self.initUsersGrid();
                $('#appr_value_form > div .loadinng_mask_dark').remove();
            }
        });
    },

    /**
     * Updates clients list
     */
    updateClientsList: function () {
        var self = this;
        var companyName = $('#company_name_input_appr_value').val();
        $('#clients-grid_appr_value > div').scrollTop(0).prepend("<div class='loadinng_mask'></div>");
        $.ajax({
            url: "/admin/default/getfilteredclientslist",
            data: {companyName: companyName},
            type: "POST",
            success: function(msg) {
               $('#clients_grid_table_appr_value tbody').html(msg);
               $('#company_name_row_appr_value').text('Client: Select a client');
               self.clientID = 0;
               $('#client_id_input').val(0);
               $('#client_admin_appr_value_table tbody').html('<tr id="user0"><td colspan="3">Select a client to populate this grid</td></tr>');
               self.init();
                $('#clients-grid_appr_value > div .loadinng_mask').remove();
            }
        });
    }
 }