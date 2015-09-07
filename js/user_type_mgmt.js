function UsersTypeMgmt() {
    var self = this;

    this.init();

    $('#admin_submit').click(function() {
        if ($('#admin_submit').attr('data') == 'tab6') {
            var url = '/admin/default/settypeusers';

            if (self.userTypeValues.length > 0) {
                url = url + '?';

                for (var key in self.userTypeValues) {
                    if (self.userTypeValues[key] == 'DB Admin') {
                        self.userTypeValues[key] = 'DBAdmin';
                    }
                    url = url + key + '=' + self.userTypeValues[key] + '&';
                }

                window.location = url;
            } else {
                return;
            }
        }
    });
}


UsersTypeMgmt.prototype = {
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
    userTypeValues: [],

    /**
     * Current user
     */
    currentUser: 0,

    /**
     * Initialize method
     */
    init: function () {
        var self = this;

        $("#users-to-type-grid-table td.dropdown_cell").click(function(event) {
            event.stopPropagation();
            $(this).parent().trigger('click');
            $("#users-to-type-grid-table td.dropdown_cell ul:visible").slideUp(150);
            $(this).find('ul').slideDown(150);
        });

        $("#users-to-type-grid-table td.dropdown_cell ul").mouseout(function() {
            var ul = $(this);
            self.timeout = setTimeout(function() {
                ul.slideUp(150);
            }, 100);
        });

        $("#users-to-type-grid-table td.dropdown_cell ul").mouseover(function() {
            if (self.timeout) {
                clearTimeout(self.timeout);
                self.timeout = false;
            }
        });

        $("#users-to-type-grid-table td.dropdown_cell ul li").click(function(event) {
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
                self.userTypeValues[userId] = text;
            }

            $("#users-to-type-grid-table td.dropdown_cell ul:visible").slideUp(150);
        });

        $('body').click(function() {
            $("#users-to-type-grid-table td.dropdown_cell ul:visible").slideUp(150);
        });

        $('#users-to-type-grid-table tbody tr').click(function() {
            $('#users-to-type-grid-table tbody tr').css('background', 'none');
            $(this).css('background-color', '#dFdDdD');
            var userId = $(this).attr('id');
            userId = userId.slice(4);
            if (self.currentUser != userId) {
                self.getUserInfo(userId);
            }
            self.currentUser = userId;
        });

        $('#user_to_type_last_name').keydown(function() {
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
        $('#user_to_type_projects tbody tr').click(function() {
            $('#user_to_type_projects tbody tr').css('background', 'none');
            $(this).css('background-color', '#dFdDdD');
        });
    },

    /**
     * Get user info to right sidebar
     */
    getUserInfo: function (userId) {
        var self = this;
        $('#user_type_sidebar').scrollTop(0).prepend("<div class='loadinng_mask'></div>");
        $.ajax({
            url: "/admin/default/getusertypeinfo",
            data: {userId: userId, tab: 'type'},
            type: "POST",
            success: function(msg){
                $('#user_type_sidebar').html(msg);
                self.initProjects();
                $('#user_type_sidebar .loadinng_mask').remove();
            }
        });
    },

    /**
     * Updates users list
     */
    updateUsersList: function () {
        var self = this;
        var lastname = $('#user_to_type_last_name').val();
        $('#user_type_sidebar').html('');
        $('#users-to-type-grid > div').scrollTop(0).prepend("<div class='loadinng_mask_dark'></div>");
        $.ajax({
            url: "/admin/default/getfiltereduserstotypelist",
            data: {lastname: lastname},
            type: "POST",
            success: function(msg){
                $('#users-to-type-grid-table tbody').html(msg);
                self.userActiveValues = [];
                self.currentUser = 0;
                self.init();
                $('#users-to-type-grid > div .loadinng_mask_dark').remove();
            }
        });
    }
}