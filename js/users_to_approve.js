function UsersToApprove() {
    this.init();
}

UsersToApprove.prototype = {
    /**
     * Timeout ul
     */
    timeout: false,

    /**
     * Click timeout
     */
    timeoutClick: false,

    /**
     * Current User
     */
    currentUserId: 0,

    /**
     * Initialize method
     */
    init: function () {
        var self = this;

        $("#users-to-approve-grid td.dropdown_cell").click(function(event) {
            event.stopPropagation();
            $(this).parent().trigger('click');
            $("#users-to-approve-grid td.dropdown_cell ul:visible").slideUp(150);
            $(this).find('ul').slideDown(150);
        });

        $("#users-to-approve-grid td.dropdown_cell ul").mouseout(function() {
            var ul = $(this);
            self.timeout = setTimeout(function() {
                ul.slideUp(150);
            }, 100);
        });

        $("#users-to-approve-grid td.dropdown_cell ul").mouseover(function() {
            if (self.timeout) {
                clearTimeout(self.timeout);
                self.timeout = false;
            }
        });

        $("#users-to-approve-grid td.dropdown_cell ul li").click(function(event) {
            event.stopPropagation();
            var text = $(this).text();
            var cell = $(this).parent().parent().parent();
            var dropdown_cell_value = cell.find('.dropdown_cell_value');
            dropdown_cell_value.text(text);
            $("#users-to-approve-grid td.dropdown_cell ul:visible").slideUp(150);
        });

        $('body').click(function() {
            $("#users-to-approve-grid td.dropdown_cell ul:visible").slideUp(150);
        });

        $('#users-to-approve-grid tbody tr').click(function() {
            $('#users-to-approve-grid tbody tr').css('background', 'none');
            $(this).css('background-color', '#dFdDdD');
            var userId = $(this).attr('id');
            userId = userId.slice(4);
            if (userId == 0) {
                $('#users_to_approve_company').html('');
            } else if (self.currentUserId != userId) {
                self.showCompanyInfo(userId);
            }
            self.currentUserId = userId;
        });

        $('#user_to_approve_last_name').keydown(function() {
            clearTimeout(self.timeoutClick);
            self.timeoutClick = setTimeout(function() {
                self.updateUsersList();
            }, 800);
        });

        $('#admin_submit').click(function() {
            if ($('#admin_submit').attr('data') == 'tab7') {
                var url = '/admin/default/approveusers';
                var approveUsers = [];
                var rows = $('#users-to-approve-grid-table tbody tr');
                var i = 1;
                rows.each(function() {
                    var userId = $(this).attr('id');
                    userId = userId.slice(4);
                    if (userId == 0) {
                        return;
                    }
                    var user_approve_value = $(this).find('.user_approve_value').text();
                    var user_type_value = $(this).find('.user_type_value').text();
                    if (user_approve_value == 'Y' || user_approve_value == 'N') {
                        approveUsers[i] = 'users[' + userId + ']=' + user_approve_value;
                        i++;

                        if (user_type_value == 'Cl. Admin') {
                            user_type_value = 'ClAdmin';
                        }

                        approveUsers[i] = 'userTypes[' + userId + ']=' + user_type_value;
                        i++;
                    }
                });

                if (approveUsers.length > 0) {
                    var urlPart = approveUsers.join('&');
                    url = url + '?' + urlPart.slice(1);
                    window.location = url;
                } else {
                    return;
                }
            }
        });
    },

    /**
     * Shows company info of highlighted user
     */
    showCompanyInfo: function (userId) {
        $('#users_to_approve_company').scrollTop(0).prepend("<div class='loadinng_mask'></div>");
        $.ajax({
            url: "/admin/default/getcompanyinfo",
            data: {userId: userId, type: 'userToApprove'},
            type: "POST",
            success: function(msg) {
                $('#users_to_approve_company').html(msg);
                $('#users_to_approve_company .loadinng_mask').remove();
            }
        });
    },

    /**
     * Updates users list
     */
    updateUsersList: function () {
        var self = this;
        var lastname = $('#user_to_approve_last_name').val();
        $('#users-to-approve-grid > div').scrollTop(0).prepend("<div class='loadinng_mask_dark'></div>");
        $.ajax({
            url: "/admin/default/getfiltereduserstoapprovelist",
            data: {lastname: lastname},
            type: "POST",
            success: function(msg){
               $('#users-to-approve-grid table tbody').html(msg);
               $('#users_to_approve_company').html('');
               self.currentUserId = 0;
               self.init();
                $('#users-to-approve-grid > div .loadinng_mask_dark').remove();
            }
        });
    }
 }