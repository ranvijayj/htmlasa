function RemoteProcessing(stripe_publish_key) {
    var self = this;
    this.init();

}

RemoteProcessing.prototype =  {

    company_name:null,
    company_id:null,
    projects_array:[],
    amount_to_pay:null,
    use_last_cc : 1,
    rp_id:null,
    stripe_publish_key:null,

    /**
     * Initialize method
     */



    init: function() {
        var self = this;

        $('#brief_info').on('click','#start_processing', function (e) {
            e.preventDefault();
            $('#remote_processing_form').submit();
        });

        $('#projects_info').on('change','.project_list_checkbox',function (event) {
            self.projects_array = [];
            var i =0;

            $('.project_list_checkbox:checked').each(function() {
                self.projects_array[i] = $(this).val();
                i++;
            });

            if(i>0) {
                self.refreshBriefInfo();
            } else { $(this).attr('checked',true);}
        });

        $('#existing_exports').on('click','.request_paper_book', function (e) {
            e.preventDefault();
            self.amount_to_pay = $(this).data('analogprise');
            self.rp_id = $(this).attr('id');
            $('#analog_book_prize').attr('data-analogprise',self.amount_to_pay);
            $('#analog_book_prize').attr('data-id',self.rp_id);
            $('#analog_book_prize').val('Pay $'+self.amount_to_pay);

            show_modal_box('#book_calulation_widget', 500);
        });



        $(".client_list" ).autocomplete({
            source: function( request, response ) {
                $.ajax({
                    url: "/remoteprocessing/GetClientList",
                    dataType: "json",
                    type:"POST",
                    data: {
                        search_string: request.term
                    },

                    success: function( data ){
                        // to provide access to index we use nex expression instead of "response(data);"
                        response($.map(data, function (value, key) {
                            return {
                                label: value,
                                idx: key
                            };
                        }));
                    },

                    error: function (xhr, ajaxOptions, thrownError) {
                        console.log(xhr.status);
                        console.log(thrownError);
                    }

                });
            },
            minLength:0,

            select: function( event, ui ) {

                self.company_name = ui.item.value;
                self.company_id = ui.item.idx;
                $(this).val( self.company_name );

                self.showProjectsInfo(self.company_id);
                self.showBriefInfo(self.company_id);

                return false;
            },

            open: function() {
            },

            close: function() {
            }

        }).bind('focus', function () {
            $(this).autocomplete("search");
        });

    },

    refreshBriefInfo : function () {
        var self = this;
        $('#brief_info').fadeOut();
        $.ajax({
            url: "/remoteprocessing/GetClientBriefInfo",
            timeout:5000,
            type:"POST",
            data: {
                company_id: self.company_id,
                projects_array: self.projects_array
            },

            success: function( html ){
                $('#brief_info').html(html);
                $('#brief_info').fadeIn();
            },

            error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr.status);
                console.log(thrownError);
            }

        });

    },

    showProjectsInfo : function (company_id) {
        $('#projects_info').fadeOut();
        $.ajax({
            url: "/remoteprocessing/GetClientProjectInfo",
            timeout:5000,
            type:"POST",
            data: {
                company_id: company_id
            },

            success: function( html ){
                console.log('succes');
                $('#projects_info').html(html);
                $('#projects_info').fadeIn();
            },

            error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr.status);
                console.log(thrownError);
            }

        });
    },

    showBriefInfo : function (company_id) {
        $('#brief_info').fadeOut();
        $.ajax({
            url: "/remoteprocessing/GetClientBriefInfo",
            timeout:5000,
            type:"POST",
            data: {
                company_id: company_id
            },

            success: function( html ){
                console.log('succes');

                $('#brief_info').html(html);
                $('#brief_info').fadeIn();
            },

            error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr.status);
                console.log(thrownError);
            }

        });
    },


    /**
     * Add loading mask
     */
    loadingMask: function(item) {
        item.scrollTop(0).prepend("<div class='loadinng_mask'></div>");
    },

    /**
     * Remove loading mask
     */
    endLoadingMask: function(item) {
        item.find(' .loadinng_mask').remove();
    }


}