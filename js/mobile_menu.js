$(document).ready(function() {
    var mainMenuLink = $('#mainmenu .container > ul > li > a');

    mainMenuLink.each(function() {
        var submenu = $(this).parent().find('ul').length;
        if (submenu > 0) {
            $(this).attr('data-opened', 'no');
        } else {
            $(this).attr('data-opened', 'yes');
        }
    });

    mainMenuLink.click(function(event) {
        event.stopPropagation();
        var opened = $(this).attr('data-opened');
        if (opened == 'no') {
            event.preventDefault();
            mainMenuLink.attr('data-opened', 'no');
            $(this).attr('data-opened', 'yes');
        }
    });

});