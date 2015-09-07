function UsersActiveMgmt() {
    var self = this;

    this.init();

    $('#admin_submit').click(function() {
        if ($('#admin_submit').attr('data') == 'tab5') {
            var url = '/admin/default/setactiveusers';

            if (self.userActiveValues.length > 0) {
                url = url + '?';

                for (var key in self.userActiveValues) {
                    url = url + key + '=' + self.userActiveValues[key] + '&';
                }

                window.location = url;
            } else {
                return;
            }
        }
    });
}


UsersActiveMgmt.prototype = {
    /**
     * Timeout ul
     */
    timeout: false,

    /**
     * Click timeout
     */
    timeoutClick: false,

    /**
     * Users active values
     */
    userActiveValues: [],

    /**
     * Current user
     */
    currentUser: 0,

    /**
     * Initialize method
     */
    init: function () {
        var self = this;

        $("#users-to-active-grid-table td.dropdown_cell").click(function(event) {
            event.stopPropagation();
            $(this).parent().trigger('click');
            $("#users-to-active-grid-table td.dropdown_cell ul:visible").slideUp(150);
            $(this).find('ul').slideDown(150);
        });

        $("#users-to-active-grid-table td.dropdown_cell ul").mouseout(function() {
            var ul = $(this);
            self.timeout = setTimeout(function() {
                ul.slideUp(150);
            }, 100);
        });

        $("#users-to-active-grid-table td.dropdown_cell ul").mouseover(function() {
            if (self.timeout) {
                clearTimeout(self.timeout);
                self.timeout = false;
            }
        });

        $("#users-to-active-grid-table td.dropdown_cell ul li").click(function(event) {
            event.stopPropagation();
            var text = $(this).text();
            var cell = $(this).parent().parent().parent();
            var row = cell.parent();
            var dropdown_cell_value = cell.find('.dropdown_cell_value');
            dropdown_cell_value.text(text);
            // Set active values
            var userId = row.attr('id');
            userId = userId.slice(4);
            if (userId != 0) {
                if (text == 'Y') {
                    self.userActiveValues[userId] = 1;
                } else if (text == 'N') {
                    self.userActiveValues[userId] = 0;
                }
            }

            $("#users-to-active-grid-table td.dropdown_cell ul:visible").slideUp(150);
        });

        $('body').click(function() {
            $("#users-to-active-grid-table td.dropdown_cell ul:visible").slideUp(150);
        });

        $('#users-to-active-grid-table tbody tr').click(function() {
            $('#users-to-active-grid-table tbody tr').css('background', 'none');
            $(this).css('background-color', '#dFdDdD');
            var userId = $(this).attr('id');
            userId = userId.slice(4);
            if (self.currentUser != userId) {
                self.getUserInfo(userId);
            }
            self.currentUser = userId;
        });

        $('#user_to_active_last_name').keydown(function() {
            clearTimeout(self.timeoutClick);
            self.timeoutClick = setTimeout(function() {
                self.updateUsersList();
            }, 800);
        });
    },

    /**
     * Initialize projects grid
     */
    initProjects: function() {
        $('#user_to_active_projects tbody tr').click(function() {
            $('#user_to_active_projects tbody tr').css('background', 'none');
            $(this).css('background-color', '#dFdDdD');
        });
    },

    /**
     * Get user info to right sidebar
     */
    getUserInfo: function (userId) {
        var self = this;
        $('#user_active_sidebar').scrollTop(0).prepend("<div class='loadinng_mask'></div>");
        $.ajax({
            url: "/admin/default/getusertypeinfo",
            data: {userId: userId, tab: 'active'},
            type: "POST",
            success: function(msg){
                $('#user_active_sidebar').html(msg);
                self.initProjects();
                $('#user_active_sidebar .loadinng_mask').remove();
            }
        });
    },

    /**
     * Updates users list
     */
    updateUsersList: function () {
        var self = this;
        var lastname = $('#user_to_active_last_name').val();
        $('#users-to-active-grid > div').scrollTop(0).prepend("<div class='loadinng_mask_dark'></div>");
        $.ajax({
            url: "/admin/default/getfiltereduserstoactivelist",
            data: {lastname: lastname},
            type: "POST",
            success: function(msg){
                $('#users-to-active-grid-table tbody').html(msg);
                $('#user_active_sidebar').html('');
                self.userActiveValues = [];
                self.currentUser = 0;
                self.init();
                $('#users-to-active-grid > div .loadinng_mask_dark').remove();
            }
        });
    }
}