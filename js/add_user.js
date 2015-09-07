function AddUserBox(enableAddUsers) {
    this.init(enableAddUsers);
}

AddUserBox.prototype = {

    /*
     * Is valid email
     */
    validMail: false,

    /**
     * Enable add users flag
     */
    enableAddUsers: 0,

    /*
     * Initialize method
     */
    init: function(enableAddUsers) {
        var self = this;

        self.enableAddUsers = enableAddUsers;

        $('#add_user_btn').click(function() {
            if (self.enableAddUsers == 1) {
                show_modal_box('#adduserbox');
            } else {
                $('#dialogmodal a').attr('href', '/myaccount?tab=service');
                show_dialog('The Client has exceeded the number of Users in its Service Level. Please navigate to Service Levels and add the number of Users required. Thank you.', 540);
            }
        });

        $('#adduser_email').blur(function() {
            self.checkEmail();
        });

        $('#adduserbox #find_user').click(function() {
            self.findUsers();
        });
    },

    /*
     * Check email
     */
    checkEmail: function () {
        var email = $('#adduser_email').val();
        var pattern = /^([0-9a-zA-Z]([\-\.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][\-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})$/;
        if (!pattern.test(email)) {
            this.validMail = false;
            if (email != '') {
                $('#adduserbox .errorMessage').show();
            } else {
                $('#adduserbox .errorMessage').hide();
            }
            $("#adduserbox #users_searsh_res_box").slideUp(200);
        } else {
            this.validMail = true;
            $('#adduserbox .errorMessage').hide();
        }
    },

    /*
     * Find users and show below search button
     */
    findUsers: function() {
        this.checkEmail();
        var email = $('#adduser_email').val();
        if (this.validMail) {
            $.ajax({
                url: "/myaccount/finduserbyemail",
                data: {email : email},
                type: "POST",
                success: function(msg){
                    if (msg) {
                        $("#adduserbox #users_searsh_res_box").slideUp(200);
                        setTimeout(function() {
                            $("#adduserbox #users_searsh_res_box").html(msg).slideDown(200);
                        }, 200);
                    }
                }
            });
        }
    }
}