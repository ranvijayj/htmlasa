function ClientsActiveMgmt() {
    var self = this;

    this.init();

    $('#admin_submit').click(function() {
        if ($('#admin_submit').attr('data') == 'tab9') {
            var url = '/admin/default/setactiveclients';

            if (self.clientActiveValues.length > 0) {
                url = url + '?';

                for (var key in self.clientActiveValues) {
                    url = url + key + '=' + self.clientActiveValues[key] + '&';
                }

                window.location = url;
            } else {
                return;
            }
        }
    });
}


ClientsActiveMgmt.prototype = {
    /**
     * Timeout ul
     */
    timeout: false,

    /**
     * Click timeout
     */
    timeoutClick: false,

    /**
     * Clients active values
     */
    clientActiveValues: [],

    /**
     * Current client to view
     */
    currentClient: 0,

    /**
     * Initialize method
     */
    init: function () {
        var self = this;

        $("#clients-to-active-grid-table td.dropdown_cell").click(function(event) {
            event.stopPropagation();
            $(this).parent().trigger('click');
            $("#clients-to-active-grid-table td.dropdown_cell ul:visible").slideUp(150);
            $(this).find('ul').slideDown(150);
        });

        $("#clients-to-active-grid-table td.dropdown_cell ul").mouseout(function() {
            var ul = $(this);
            self.timeout = setTimeout(function() {
                ul.slideUp(150);
            }, 100);
        });

        $("#clients-to-active-grid-table td.dropdown_cell ul").mouseover(function() {
            if (self.timeout) {
                clearTimeout(self.timeout);
                self.timeout = false;
            }
        });

        $("#clients-to-active-grid-table td.dropdown_cell ul li").click(function(event) {
            event.stopPropagation();
            var text = $(this).text();
            var cell = $(this).parent().parent().parent();
            var row = cell.parent();
            var dropdown_cell_value = cell.find('.dropdown_cell_value');
            dropdown_cell_value.text(text);
            // Set active values
            var clientId = row.data('id');
            if (clientId != 0) {
                if (text == 'Y') {
                    self.clientActiveValues[clientId] = 1;
                } else if (text == 'N') {
                    self.clientActiveValues[clientId] = 0;
                }
            }

            $(row).find("td.dropdown_cell ul:visible").slideUp(150);
        });

        $('body').click(function() {
            $("#clients-to-active-grid-table td.dropdown_cell ul:visible").slideUp(150);
        });

        $('#clients-to-active-grid-table tbody tr').click(function() {
            $('#clients-to-active-grid-table tbody tr').css('background', 'none');
            $(this).css('background-color', '#dFdDdD');
            var clientId = $(this).data('id');
            if (self.currentClient != clientId) {
                self.getClientInfo(clientId);
            }
            self.currentClient = clientId;
        });

        $('#client_to_active_name').keydown(function() {
            clearTimeout(self.timeoutClick);
            self.timeoutClick = setTimeout(function() {
                self.updateClientsList();
            }, 800);
        });
    },

    /**
     * Initialize projects grid
     */
    initProjects: function() {
        $('#client_to_active_projects tbody tr').click(function() {
            $('#client_to_active_projects tbody tr').css('background', 'none');
            $(this).css('background-color', '#dFdDdD');
        });
    },

    /**
     * Get client info to right sidebar
     */
    getClientInfo: function (clientId) {
        var self = this;
        $('#client_active_sidebar').scrollTop(0).prepend("<div class='loadinng_mask'></div>");
        $.ajax({
            url: "/admin/default/getclientactiveinfo",
            data: {clientId: clientId},
            type: "POST",
            success: function(msg){
                $('#client_active_sidebar').html(msg);
                self.initProjects();
                $('#client_active_sidebar .loadinng_mask').remove();
            }
        });
    },

    /**
     * Updates users list
     */
    updateClientsList: function () {
        var self = this;
        var clientname = $('#client_to_active_name').val();
        $('#clients-to-active-grid > div').scrollTop(0).prepend("<div class='loadinng_mask_dark'></div>");
        $.ajax({
            url: "/admin/default/getfilteredclientstoactivelist",
            data: {clientname: clientname},
            type: "POST",
            success: function(msg){
                $('#clients-to-active-grid-table tbody').html(msg);
                $('#client_active_sidebar').html('');
                self.clientActiveValues = [];
                self.currentClient = 0;
                self.init();
                $('#clients-to-active-grid > div .loadinng_mask_dark').remove();
            }
        });
    }
}