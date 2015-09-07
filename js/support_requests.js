function SupportRequests() {
    this.init();
}

SupportRequests.prototype = {
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
    login:null,
    currentUserId: 0,

    /**
     * Initialize method
     */
    init: function () {
        var self = this;




        $('#support_requests_table tbody tr').click(function() {

            $('#support_requests_table tbody tr').css('background', 'none');

            $(this).css('background-color', '#dFdDdD');

            var req_id = $(this).data('request');
            self.login = $(this).find('td.login').text();

            self.showRequestInfo(req_id,self.login);

        });


        $('#users_to_approve_company').on('change','#add_device_check_box',function () {
            console.log("inside");
            if ($(this).prop('checked')) {

                var checkbox = $(this);
                checkbox.removeAttr('checked');
                var user_id = $(this).data('uid');
                var hash = $(this).data('hash');

                $('#dialogmodal a').attr('href', '#');
                show_dialog('User will be able to login without entering login and password (only once). Do you want to continue? ', 540);
                $('#dialogmodal a').bind('click',function(){
                    $.ajax({
                        url: "/usersdevice/addsuperdevice",
                        data:{
                            user_id : user_id,
                            hash: hash
                        },
                        type: "POST",
                        success: function(msg){
                            checkbox.attr('checked','true');
                            checkbox.attr('disabled',true);

                        },

                        error: function (){
                            checkbox.removeAttr('checked');
                        }
                    });
                })

            }
        });

        $('#users_to_approve_company').on('change','#UsersSettings_Use_Device_Checking',function () {

            if ($(this).prop('checked')) {

                $.ajax({
                 url: "/myaccount/SetDeviceCheck",
                 data:{
                     login : $(this).data('login')
                 },
                 type: "POST",

                 success: function(msg){

                 }
                 });
            } else {
                $.ajax({
                    url: "/myaccount/UnsetDeviceCheck",
                    type: "POST",
                    data:{
                        login : $(this).data('login')
                    },
                    success: function(msg){

                    }
                });

            }
        });

        $('#users_to_approve_company').on('change','#mark_request_as_solved',function () {

            if ($(this).prop('checked')) {

                var checkbox = $(this);
                $(this).removeAttr('checked');
                var user_id = $(this).data('uid');
                var hash = $(this).data('hash');
                var req_id = $(this).data('id');

                $('#dialogmodal a').attr('href', '#');
                show_dialog('Are you sure want to mark request as "Solved"?  ', 540);
                $('#dialogmodal a').bind('click',function(){
                    $.ajax({
                        url: "/supportrequests/marksolved",
                        data:{
                            request_id : req_id

                        },
                        type: "POST",
                        success: function(msg){
                            checkbox.attr('disabled',true);
                            checkbox.attr('checked');
                            window.location = '/admin?tab=support_requests';
                        },

                        error: function (){
                            checkbox.removeAttr('checked');
                        }
                    });
                })

            }
        });

        var timeout;

        $('#users_to_approve_company').on('keypress','.row input.answer',function(){

            var textfield =  $(this);


            if(timeout) {
                clearTimeout(timeout);
                timeout = null;
            }

            timeout = setTimeout(function() {

                $.ajax({
                    url: "/usersAnswers/CheckAnswer",
                    type: "POST",
                    data: {
                        question_id: textfield.data('question'),
                        answer: textfield.val(),
                        user_id: textfield.data('uid')
                    },
                    success: function (data) {
                        if (data==1) {
                            textfield.css('background','rgb(204, 234, 204)');
                        } else {
                            textfield.css('background','pink');
                        }
                    }
                });
            }, 1000)


                /**/

        });


    },

    /**
     * Shows company info of highlighted user
     */
    showRequestInfo: function (req_id,login) {
        $('#users_to_approve_company').scrollTop(0).prepend("<div class='loadinng_mask'></div>");
        $.ajax({
            url: "/supportrequests/getrequestinfo",
            data: {request_id: req_id, login: login },
            type: "POST",
            success: function(msg) {
                $('#users_to_approve_company').html(msg);
                $('#users_to_approve_company').show();
                $('#users_to_approve_company .loadinng_mask').remove();
            }
        });
    }


 }