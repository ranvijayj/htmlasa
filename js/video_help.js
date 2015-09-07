function VideoHelp() {
    var self = this;
    this.init();

}

VideoHelp.prototype =  {

    /**
     * Initialize method
     */
    init: function() {
        var self = this;

        /**
         *
         */
        $('#sidebar').on('click','.video_link',function (event) {
                event.preventDefault();
                console.log('click performed');
                var url = $(this).attr('href');
                //var url = $(this);



                $.ajax({
                    type:"POST",
                    url: '/help/GetVideoByUrl',
                    data: {url : url},
                    dataType: 'json',
                    success: function (data) {
                        $("#video_player_wraper").html(data.html);
                       if(data.pass) {
                           $("#video_pass").html('Password : '+data.pass);
                            $('#video_pass').show();
                       }
                    }
                });
        });




        $('#search_field').keydown(function() {
            clearTimeout(self.timeoutClick);
            self.timeoutClick = setTimeout(function() {
                console.log('keydown ');
                self.updateList();
            }, 800);
        });


        $('#search_field').focus(function() {
            clearTimeout(self.timeoutSearch);
            $('#search_options').fadeIn(200);
        });

        $('#search_field').blur(function() {
            self.timeoutSearch = setTimeout(function() {
                $('#search_options').fadeOut(200);
            }, 200);
        });

        $('#search_options').click(function() {
            $('#search_field').focus();
        });

        $('#search_options label').click(function() {
            clearTimeout(self.timeoutClick);
            self.timeoutClick = setTimeout(function() {
                self.updateList();
            }, 200);
        });

        $('.video_link')[0].click();


    },




    /**
     * Updates documents list
     */
    updateList: function() {
        var self = this;


        var url = '/help/GetVideoBySearchQuery';

        var query =  $('#search_field').val();

        var search_option_title = $('#search_option_title').attr('checked') ? 1 : 0;
        var search_option_log_line = $('#search_option_log_line').attr('checked') ? 1 : 0;
        var search_option_description = $('#search_option_description').attr('checked') ? 1 : 0;
        var search_option_link_name = $('#search_option_link_name').attr('checked') ? 1 : 0;
        $('#search_options').fadeOut();
        self.loadingMask('#sidebar');

        $.ajax({
            url: url,
            data: {
                query: query,
                search_option_title: search_option_title,
                search_option_log_line: search_option_log_line,
                search_option_description: search_option_description,
                search_option_link_name: search_option_link_name
            },
            async: false,
            dataType: 'json',
            type: "POST",
            success: function(data) {
                if (data) {

                    setTimeout(function (){
                        $('#sidebar').html(data.html);
                        self.endLoadingMask('#sidebar')
                    },500);
                }
                //self.endLoadingMask('#tab1 .table_list_scroll_block');
            }
        });
    },

    /**
     * Add loading mask
     */
    loadingMask: function(item) {
        $(item).scrollTop(0).prepend("<div class='loadinng_mask'></div>");
    },

    /**
     * Remove loading mask
     */
    endLoadingMask: function(item) {
        $(item + ' .loadinng_mask').remove();
    }




}