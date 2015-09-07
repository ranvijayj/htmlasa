$(document).ready(function() {
    var nowdate=new Date();
    var tz=-nowdate.getTimezoneOffset()*60;

    // set user's timezone offset
    var timezone_offset_field = $('#user_timezone_offset');
    if (timezone_offset_field) {
        timezone_offset_field.val(tz);
    }


    // set user's screen resolution
    var resolution = $('#user_resolution');
    if (resolution) {
        resolution.val(screen.width+'x'+screen.height+'x'+screen.colorDepth);
    }


    $('#logo').click(function() {
        window.location = '/';
    });


    $('body').on('blur','.dollar_fields',function() {
        var value = parseFloat($(this).val());
        if (!isNaN(value)) {
            $(this).val(value.toFixed(2));
        } else {
            $(this).val('');
        }

    });

    // close alert message
    $('.close-alert').on('click',function(){
        $(this).parent().slideUp(500);
    });

    // close alert message
    $('body').on('click','.close-popup',function(){
        $(this).parent().hide();

    });

    // close alert message

    var duration = $('.info').data('id');
    if (isNaN(duration)) {
        duration=4000;
    }

    setTimeout(function() {
        $('.info .close-alert').parent().slideUp(500);
    }, duration);


    $('.grid-view').each(function() {
        var summary = $(this).find('.summary');
        var table = $(this).find('.items');
        table.after(summary);
    });

    $('.list-view').each(function() {
        var summary = $(this).find('.summary');
        var table = $(this).find('.items');
        table.after(summary);
    });


    $('.pager').click(function() {
        var interval = false;
        interval = setInterval(function () {
            $('.list-view').each(function() {
                var summary = $(this).find('.summary');
                var table = $(this).find('.items');
                table.after(summary);
            });
            $('.grid-view').each(function() {
                var summary = $(this).find('.summary');
                var table = $(this).find('.items');
                table.after(summary);
            });
        }, 20);
        setTimeout(function() {
             //clearInterval(interval);
        }, 1000)
    });

    // check all items in the grid
    $("#check_all").click(function (event) {
        event.stopPropagation();
        if (!$(this).attr('checked')) {
            $('#list_table tr').each(function() {
                $(this).animate({"backgroundColor":"#fff"},200);
            });
            $(".list_checkbox").each(function() {
                $(this).removeAttr('checked');
            });
        } else {
            $('#list_table tr').each(function() {
                $(this).animate({"backgroundColor":"#eee"},200);
            });
            $(".list_checkbox").each(function() {
                $(this).attr('checked', 'checked');
            });
        }
    });

    // show user's info modal box
    $('#user_info_link').click(function() {
        $.ajax({
            url: "/site/getuserinfo",
            type: "POST",
            success: function(msg){
                $('#login_detail_box').html(msg);
                show_modal_box('#login_detail');
            }
        });
    });

    // show test for w9 modal box
    $('#link_to_test_w9').click(function() {
        show_modal_box('#test_for_w9');
    });

    // test for w9
    $('#test_for_w9_submit').click(function() {
        var fed_id = $('#fed_id_test_for_w9').val();
        var company_name = $('#company_name_test_for_w9').val();
        var address = $('#address_test_for_w9').val();
        var auth_user = $('#auth_user').val();
        var login = '';
        var password = '';
        if (auth_user == 0) {
            login = $('#login_test_for_w9').val();
            password = md5($('#password_test_for_w9').val());
        }

        if (fed_id == '' && company_name == '' && address == '') {
            $('#test_for_w9_result').html('');
        } else {
            $.ajax({
                url: "/site/testforw9",
                data: {
                    fed_id: fed_id,
                    company_name: company_name,
                    address: address,
                    login: login,
                    password: password
                },
                type: "POST",
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        $('#test_for_w9_result').html('Yes <img alt="" style="position: relative; top: 10px;" src="/images/verified_doc_bg.jpg">');
                        $('#password_test_for_w9_error').hide();
                    } else {
                        if (data.error == 1) {
                            $('#test_for_w9_result').html('No <img alt="" style="position: relative; top: 10px;" src="/images/notverified_doc_bg.jpg">');
                            $('#password_test_for_w9_error').hide();
                        } else if (data.error == 2) {
                            $('#test_for_w9_result').html('');
                            $('#password_test_for_w9_error').show();
                        }
                    }

                    $('#fed_id_test_for_w9').val(data.fed_id);
                    $('#company_name_test_for_w9').val(data.company_name);
                    $('#address_test_for_w9').val(data.address);
                }
            });
        }
    });

    $('#RegisterForm_First_Name, #RegisterForm_Last_Name, #Persons_First_Name, #Persons_Last_Name').bind('keyup blur',function(){
            var node = $(this);
            node.val(node.val().replace(/[^a-zA-Z\s]/g,'') ); }
    );

    // clear last search Payments query and Payments to review sessoin if go to list or detail page directly
    $('#payments_detail_page_link, #payments_list_page_link, #payments_list_page_main_link').click(function(event) {
        event.preventDefault();
        var url = $(this).attr('href');
        var opened = $(this).attr('data-opened');
        $.ajax({
            url: "/payments/clearpaymentstoreviewsession",
            type: "POST",
            data: {clear: true},
            async: false,
            success: function() {
                if (opened != 'no') {
                    window.location = url;
                }
            }
        });
    });

    // clear last search AP query and AP to review sessoin if go to list or detail page directly
    $('#ap_detail_page_link, #ap_list_page_link, #ap_list_page_main_link, #ap_detail_page_link2').click(function(event) {
        event.preventDefault();
        var url = $(this).attr('href');
        var opened = $(this).attr('data-opened');
        $.ajax({
            url: "/ap/clearaptoreviewsession",
            type: "POST",
            data: {clear: true},
            async: false,
            success: function() {
                if (opened != 'no') {
                    window.location = url;
                }
            }
        });
    });

    // clear last search Vendors query and Vendors to review sessoin if go to list or detail page directly
    $('.clear_vendors_to_review_list').click(function(event) {
        event.preventDefault();
        var url = $(this).attr('href');
        var opened = $(this).attr('data-opened');
        $.ajax({
            url: "/vendor/clearvendorstoreviewsession",
            type: "POST",
            data: {clear: true},
            async: false,
            success: function() {
                if (opened != 'no') {
                    window.location = url;
                }
            }
        });
    });

    // clear last W9 to review sessoin if go to detail page directly
    $('.clear_w9_to_review_list').click(function(event) {
        event.preventDefault();
        var url = $(this).attr('href');
        $.ajax({
            url: "/w9/clearw9toreviewsession",
            type: "POST",
            data: {clear: true},
            async: false,
            success: function() {
                window.location = url;
            }
        });
    });

    //clear PO to review session
    $('.clear_po_to_review_list').click(function(event) {
        event.preventDefault();
        var url = $(this).attr('href');
        var opened = $(this).attr('data-opened');
        $.ajax({
            url: "/po/clearpotoreviewsession",
            type: "POST",
            data: {clear: true},
            async: false,
            success: function() {
                if (opened != 'no') {
                    window.location = url;
                }
            }
        });
    });


    $("#users_to_add_list .list_checkbox").click(function (event) {
        event.stopPropagation();
        var checkbox = $(this);
        setTimeout(function() {
            if (!checkbox.attr('checked')) {
                checkbox.parent().parent().css({"backgroundColor":"#fff"});
            } else {
                var row = checkbox.parent().parent();
                row.css({"backgroundColor":"#eee"});
            }
        }, 10);
    });

    $('#users_to_add_list #list_table tbody tr').click(function (event) {
        event.stopPropagation();
        $(this).find(".list_checkbox").click();
    });

    var bodyWidth = $('body').width();
    var width = $('#page').width();
    if (width < bodyWidth) {
        width = bodyWidth;
    }

    $('#mainmenu').width(width);
    $('footer').width(width);

    $(window).resize(function() {
        var bodyWidth = $('body').width();
        var width = $('#page').width();
        if (width < bodyWidth) {
            width = bodyWidth;
        }

        $('#mainmenu').width(width);
        $('footer').width(width);
    });

    $('a.support_request').click(function() {

        var nowdate=new Date();
        var tz=-nowdate.getTimezoneOffset()*60;
        var resolution = screen.width+'x'+screen.height+'x'+screen.colorDepth;
        var login = $('#extra_user_login').val();

        if (login) {
            $.ajax({
                url: "/supportrequests/request",
                type: "POST",
                data: {
                    login: login,
                    timezone: tz,
                    resolution : resolution,
                    requesttype: 'requesttype'
                },
                async: true,
                success: function(html) {
                    $('#request_body').html(html);

                    close_modal_box('#extraloginmodal');

                    $('#lean_overlay').fadeIn(200);

                    show_modal_box('#support_request_device',600);
                }
            });
        } else {
            alert("Login field should be filled");
        }


    }) ;

});

/**
 * Set equal height of blocks
 * @param columns
 */
function setEqualHeight(columns)
{
    var tallestcolumn = 0;
    columns.each(function() {
            currentHeight = $(this).height();
            if(currentHeight > tallestcolumn)
                tallestcolumn = currentHeight;
        }
    );

    columns.height(tallestcolumn);
}

/**
 * Open change client box
 */
function open_change_client_box() {
    var modal_width = modal_width ? modal_width : 260;
    $.ajax({
        url: "/site/getuserclientslist",
        type: "POST",
        dataType: 'json',
        success: function(data) {
            $('#change_client_id').html(data.clients);
            $('#change_project_id').html(data.projects);
            $("#lean_overlay").css({"display":"block",opacity:0});
            $("#lean_overlay").fadeTo(150, 0.45);
            $('#change_client').css({"display":"block","position":"fixed","opacity":0,"z-index":11000,"left":"50%","margin-left":"-" + (modal_width/2 + 20) + "px","top":"110px", "width": modal_width+"px"});
            $('#change_client').fadeTo(150,1);
            $("#lean_overlay").click(function(){close_change_client_box()});
            $('.hidemodal').click(function(){close_change_client_box()});
        }
    });
}

// get user client projects
$('#change_client_id').change(function() {
    var client_id = $(this).val();
    $.ajax({
        url: "/site/getuserclientslist",
        type: "POST",
        data: {client_id: client_id},
        dataType: 'json',
        success: function(data) {
            $('#change_project_id').html(data.projects);
        }
    });
});

/**
 * Close change client box
 */
function close_change_client_box() {
    $("#lean_overlay").fadeOut(200);
    $('#change_client').css({"display":"none"});
}

/**
 * Close dialog box
 */
function close_dialog() {
    $("#lean_overlay").fadeOut(200);
    $('#dialogmodal').css({"display":"none"});
}

/**
 * Open dialog box
 * @param int modal_width Width of modal box
 */
function show_dialog(text, modal_width) {
    var modal_width = modal_width ? modal_width : 260;
    $('#dialogmodal h2').text(text);
    $("#lean_overlay").css({"display":"block",opacity:0});
    $("#lean_overlay").fadeTo(150, 0.45);
    $('#dialogmodal').css({"display":"block","position":"fixed","opacity":0,"z-index":11000,"left":"50%","margin-left":"-" + (modal_width/2 + 20) + "px","top":"110px", "width": modal_width+"px"});
    $('#dialogmodal').fadeTo(150,1);
    $("#lean_overlay").click(function(){close_dialog()});
    $('.hidemodal').click(function(){close_dialog()});
}
/**
 * This dialog doesn't hide any previous dialogs and modal windows
 * @param text
 * @param modal_width
 */
function show_deffered_dialog(text, modal_width, prev_answer) {

    $("#lean_overlay").css({"display":"block",opacity:0});
    $("#lean_overlay").fadeTo(150, 0.45);
    var defer = $.Deferred();

    if (prev_answer=='true') {
        var modal_width = modal_width ? modal_width : 260;
        $('#polite_dialog h2').text(text);

        $('#polite_dialog .yesbutton').click(function () {
            defer.resolve("true");
            $('#polite_dialog').fadeOut();
        });

        $('#polite_dialog .nobutton').click(function () {
            defer.resolve("false");
            $('#polite_dialog').fadeOut();

        });

        $('#polite_dialog').css({"display":"block","position":"fixed","opacity":0,"z-index":11000,"left":"50%","margin-left":"-" + (modal_width/2 + 20) + "px","top":"110px", "width": modal_width+"px"});
        $('#polite_dialog').fadeTo(150,1);

        $("#lean_overlay").click(function(){close_dialog();$('#polite_dialog').fadeOut();});
        $('.hidemodal').click(function(){close_dialog();$('#polite_dialog').fadeOut();});

    } else {
        defer.resolve("false");
    }

    return defer.promise();
}
    /**
     * This dialog return promise
     * @param text
     * @param modal_width
     */
    function show_def_dialog(text, modal_width, prev_answer) {

        $("#lean_overlay").css({"display":"block",opacity:0});
        $("#lean_overlay").fadeTo(150, 0.45);
        var defer = $.Deferred();

        if (prev_answer=='true') {
            var modal_width = modal_width ? modal_width : 260;
            $('#polite_dialog h2').text(text);

            $('#polite_dialog .yesbutton').click(function () {
                defer.resolve("true");
                $('#polite_dialog').fadeOut();
                $("#lean_overlay").fadeOut();
            });

            $('#polite_dialog .nobutton').click(function (e) {
                    e.stopPropagation();
                    defer.resolve("false");
                    $('#polite_dialog').fadeOut();
                    $("#lean_overlay").fadeOut();
            });

                $('#polite_dialog').css({"display":"block","position":"fixed","opacity":0,"z-index":11000,"left":"50%","margin-left":"-" + (modal_width/2 + 20) + "px","top":"110px", "width": modal_width+"px"});
                $('#polite_dialog').fadeTo(150,1);

                $("#lean_overlay").click(function(){close_dialog();$('#polite_dialog').fadeOut();});
                $('.hidemodal').click(function(){close_dialog();$('#polite_dialog').fadeOut();});

            } else {
                defer.resolve("false");
            }

            return defer.promise();
        }



        /**
 * Close alert box
 */
function close_alert() {
    $("#lean_overlay").fadeOut(200);
    $('#alertmodal').css({"display":"none"});
}

/**
 * Show alert box
 * @param int modal_width Width of modal box
 */
function show_alert(text, modal_width) {
    var modal_width = modal_width ? modal_width : 260;
    $('#alertmodal h2').text(text);
    $("#lean_overlay").css({"display":"block",opacity:0});
    $("#lean_overlay").fadeTo(150, 0.45);
    $('#alertmodal').css({"display":"block","position":"fixed","opacity":0,"z-index":11000,"left":"50%","margin-left":"-" + (modal_width/2 + 20) + "px","top":"110px", "width": modal_width+"px"});
    $('#alertmodal').fadeTo(150,1);
    $("#lean_overlay").click(function(){close_alert()});
    $('.hidemodal').click(function(){close_alert()});
}

/**
 * Show alert box for already existing pop-ups, doesn't close existing pop-ups
 * @param int modal_width Width of modal box
 */
function show_alert2(text, modal_width) {
    var modal_width = modal_width ? modal_width : 260;
    $('#alert_alone h2').text(text);
    $("#lean_overlay").css({"display":"block",opacity:0});
    $("#lean_overlay").fadeTo(150, 0.45);
    $('#alert_alone').css({"display":"block","position":"fixed","opacity":0,"z-index":11000,"left":"50%","margin-left":"-" + (modal_width/2 + 20) + "px","top":"110px", "width": modal_width+"px"});
    $('#alert_alone').fadeTo(150,1);

    $('#alert_alone .alert2_ok_button').click(function(e){$('#alert_alone').fadeOut(); });

}

/**
 * Close modal box
 * @param id
 */
function close_modal_box(id) {
    $("#lean_overlay").fadeOut(200);
    $(id).css({"display":"none"});
}

/**
 * Show modal box
 * @param id
 * @param int modal_width Width of modal box
 */
function show_modal_box(id, modal_width, top_margin) {
    var modal_width = modal_width ? modal_width : 260;
    var top_margin = top_margin ? top_margin : 110;
    $("#lean_overlay").css({"display":"block",opacity:0});
    $("#lean_overlay").fadeTo(150, 0.45);
    $(id).css({"display":"block","position":"absolute","opacity":0,"z-index":110,"left":"50%","margin-left":"-" + (modal_width/2 + 20) + "px","top": top_margin+"px", "width": modal_width+"px"});
    $(id).fadeTo(150,1);
    $("#lean_overlay").click(function(){close_modal_box(id)});
    $('.hidemodal').click(function(){close_modal_box(id)});
}

/**
 * Show modal box
 * @param id
 * @param int modal_width Width of modal box
 */
function show_persistent_modal_box(id, modal_width, top_margin) {
    var modal_width = modal_width ? modal_width : 260;
    var top_margin = top_margin ? top_margin : 110;
    $("#lean_overlay").css({"display":"block",opacity:0});
    $("#lean_overlay").fadeTo(150, 0.45);
    $(id).css({"display":"block","position":"absolute","opacity":0,"z-index":110,"left":"50%","margin-left":"-" + (modal_width/2 + 20) + "px","top": top_margin+"px", "width": modal_width+"px"});
    $(id).fadeTo(150,1);

}


function show_modification_box(id) {

    $("#lean_overlay").css({"display":"block",opacity:0});
    $("#lean_overlay").fadeTo(150, 0.45);
    $(id).css({"display":"block","position":"fixed","z-index":11000,"left":"5%","top": "5%", "width": "90%", "height": "90%"});
    $(id).fadeTo(150,1);
    $("#lean_overlay").click(function(){close_modal_box(id)});
    $('.hidemodal').click(function(){close_modal_box(id)});
}


/**
 * Get position of block
 * @param obj
 * @returns {{left: number, top: number}}
 */
function get_obj_position(obj)
{
    var curleft = 0, curtop = 0;

    if ( obj.offsetParent )
    {
        curleft = obj.offsetLeft;
        curtop = obj.offsetTop;

        while ( obj = obj.offsetParent )
        {
            curleft += obj.offsetLeft;
            curtop += obj.offsetTop;
        }
    }

    return { left : curleft, top : curtop };
}

