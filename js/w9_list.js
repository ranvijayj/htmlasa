function W9List() {
    this.init();
    this.initTable();
    this.initSorting();
    $('#list_table tbody .cutted_cell').tooltip(this.tooltipOptions);
}

W9List.prototype = {

    /**
     * Sort parameters
     */
    sortType: '`companies`.`Company_Name`',
    sortDirection: 'ASC',

    /**
     * Click timeout
     */
    timeoutClick: false,

    /**
     * Search timeout
     */
    timeoutSearch:false,
    location:'/w9',
    /**
     * Tooltip options
     * @type {{position: {my: string, at: string, using: Function}, show: {effect: string, delay: number}, hide: {effect: string, delay: number}}}
     */
    tooltipOptions : {
        position: {
            my: "left top",
            at: "left bottom",
            using: function( position, feedback ) {
                $( this ).css( position );
                $( "<div>" )
                    .addClass( "tooltip" )
                    .addClass( feedback.vertical )
                    .addClass( feedback.horizontal )
                    .appendTo( this );
            }
        },
        show: {
            effect: "slideDown",
            delay: 400
        },
        hide: {
            effect: "hide",
            delay: 0
        }
    },


    limit:50,
    offset:50,

    label_is_checked:null,
    /**
     * scroll dimension
     */
    visibleHeight : 0,

    tableHeight :0,

    endReached:0,

    lastSelectedCount:0,

    table_object:null,

    projectsArray:null,

    /**
     * Initialize method
     */
    init: function() {
        var self = this;
        self.projectsArray = new Array();
        /*
        $('#print_document').click(function() {
            var fed_id = $(this).attr('data');
            $.ajax({
                url: "/w9/setfedidtoprintdocument",
                data: {fed_id: fed_id},
                type: "POST",
                success: function(msg) {

                }
            });
            var url = '/w9/printdocument';
            window.open(url, 'popup', 'toolbar=no, menubar=no, status=no, location=no, resizable=no, width=640, height=600');
        });
        */

        $('#search_field').keydown(function() {
           /* clearTimeout(self.timeoutClick);
            self.timeoutClick = setTimeout(function() {
            self.updateList();
            }, 800);*/

        });

        $('#search_field').keyup(function(e) {
            //console.log("Char code "+ e.keyCode);
            if(e.keyCode==106) {
                $('#search_field').val('');
                $('#search_option_to_be_batched').attr('checked', false);
                $('#search_option_to_be_approved').attr('checked', false);
            }


        });


        $('#search_field').on('input',function() {
            clearTimeout(self.timeoutClick);
            var str=$('#search_field').val();
            self.timeoutClick = setTimeout(function() {
            self.updateList();
            }, 800);
        });

        /*$('#search_options input').change(function () {
            self.updateList();
        });*/


        $("#check_all").click(function () {
            setTimeout(function() {
                self.setCheckedCount();
            }, 10);
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
            var search_text=$('#search_field').val();

            var clicked_input = $(this).find('input').attr('id');

            clearTimeout(self.timeoutClick);
            //if( clicked_input=='search_option_to_be_approved' || clicked_input=='search_option_to_be_batched' ){
                self.timeoutClick = setTimeout(function() {
                    self.updateList();
                }, 200);
            //}
        });

        $('#submit_list_form').click(function() {
            if ($(".list_checkbox:checked").length > 0) {
                $('#detail_form').submit();
            } else {
                if ($(".list_checkbox").length == 1) {
                    $(".list_checkbox").attr('checked', true);
                    $('#detail_form').submit();
                } else {
                    show_alert("You must select at least one item", 350);
                }
            }
        });

        $('#limiter_checkbox').change(function(){
            self.label_is_checked  = $(this).is(":checked")  ? 1 : 0;
            //don't use ajax scroll when search string contain smth
            if ( self.label_is_checked==0){
                self.endReached =1;
            }

            var limiter_checkbox=false;
            if ( self.label_is_checked==1) {
                limiter_checkbox = true;
            }

            self.table_object = $('#list_table tbody')
            $.ajax({
                url: '/site/changedisplaylimit',
                data: {
                    limiter_checkbox: limiter_checkbox
                },
                async: true,
                type: "POST",
                success: function(data) {
                    self.updateList();
                }

            });
        });


        self.recalculateScroll();

        $('.table_list_scroll_block').on('scroll',function(){
            if(self.label_is_checked == 0 &&  self.endReached!=1 ){
                if((self.tableHeight-self.visibleHeight-$(this).scrollTop())<20) {
                    setTimeout(function(){
                        $('#loading_mask_left').show();
                        setTimeout(self.appendNextBlockToGrid('list_table'),500);
                    },1000);
                }
            }
        });
        //end of scroll routines


        /*$('#company_info ').on('click','a.audit_view', function (event) {
            event.preventDefault();
                $.ajax({
                    url: "/documents/ViewAudits",
                    data: {
                        docId: $(this).data('id')
                    },
                    type: "POST",
                    success: function(data) {
                        $('#audit_view_block').html(data);

                        show_modal_box('#audit_view_block',500)
                    }
                });
        });*/
        $('#progress_bar').unbind('click');
        $('#progress_bar').on('click',function (event) {
            var docId = $('#progress_bar').attr('data-id');
            console.log (docId);
            if (docId) {
                $.ajax({
                    url: "/documents/ViewAudits",
                    data: {
                        docId: docId,
                        audit_mode: 'Approved'
                    },
                    type: "POST",
                    success: function(data) {
                        $('#audit_view_block_detail').html(data);
                        $('#audit_checkbox').removeAttr('checked');
                        show_modal_box('#audit_view_block',505)
                    }
                });
            }

        });

        var cid = $('#client_to_change').data('cid');
        var pid = $('#client_to_change').data('pid');
        var uid = $('#client_to_change').data('uid');
        var uname = $('#client_to_change').data('uname');

        if ((cid || pid) && !uid) {


            var str = "The Company and Project will be changed to access the items requiring approval.";

            show_alert(str,500);
            $('#alertmodal button.button').click(function() {
                self.changeClientProject(cid, pid);
            });

            $("#lean_overlay").click(function(){
                self.changeClientProject(cid, pid);
                close_alert()});
            $('.hidemodal').click(function(){
                self.changeClientProject(cid, pid);
                close_alert()});


            /*show_def_dialog(str,500,'true').then(function (answer) {
                if(answer == 'true') {
                    self.changeClientProject(cid, pid);
                }
            });*/
        } else if (uid) {

            var str = "To view desired items you should change current user to '"+uname+"'. Do you agree?";

            show_def_dialog(str,500,'true').then(function (answer) {
                if(answer == 'true') {
                    self.changeUser(uid);
                }
            });

        }


    },

    /**
     * Sorting initialize
     */
    initSorting: function() {
        var self = this;

        $('#vendor_name_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == '`companies`.`Company_Name`') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = '`companies`.`Company_Name`';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });

        $('#fed_id_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == '`companies`.`Company_Fed_ID`') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = '`companies`.`Company_Fed_ID`';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });

        $('#address_cell_header').click(function() {
            self.clearArrows($(this));
            if (self.sortType == '`addresses`.`Address1`') {
                if (self.sortDirection == 'ASC') {
                    self.sortDirection = 'DESC';
                } else {
                    self.sortDirection = 'ASC';
                }
            } else {
                self.sortType = '`addresses`.`Address1`';
            }
            self.addSortingArrow($(this));
            self.updateList();
        });
    },

    /**
     * Table initialize method
     */
    initTable: function() {
        var self = this;

        $(".list_checkbox").click(function (event) {
            event.stopPropagation();
            var checkbox = $(this);
            setTimeout(function() {
                if (!checkbox.attr('checked')) {
                    checkbox.parent().parent().css({"backgroundColor":"#fff"});
                    if ($(".list_checkbox:checked").length == 1) {
                        var checked = $(".list_checkbox:checked");
                        checked.each(function() {
                            var row = $(this).parent().parent();
                            var fed_id = row.find('.fed_id_cell').text();
                            var comId = row.attr('id');
                            comId = comId.slice(3);
                            self.getCompanyInfo(comId);
                            //$('#print_document').attr('data', fed_id).show();
                        });
                    }
                } else {
                    var row = checkbox.parent().parent();
                    row.css({"backgroundColor":"#eee"});
                    var fed_id = row.find('.fed_id_cell').text();
                    var comId = row.attr('id');
                    comId = comId.slice(3);
                    if ($(".list_checkbox:checked").length == 1) {
                        self.getCompanyInfo(comId);
                        //$('#print_document').attr('data', fed_id).show();
                    }
                }
                self.setCheckedCount();
            }, 10);
        });

        $('#list_table tbody tr').click(function (event) {
            event.stopPropagation();
            $(this).find(".list_checkbox").click();
        });
    },

    /**
     * Updates vendors list
     */
    updateList: function() {
        var self = this;
        var query =  $('#search_field').val();
        var search_option_com_name = $('#search_option_com_name').attr('checked') ? 1 : 0;
        var search_option_fed_id = $('#search_option_fed_id').attr('checked') ? 1 : 0;
        var search_option_addr1 = $('#search_option_addr1').attr('checked') ? 1 : 0;
        var search_option_addr2 = $('#search_option_addr2').attr('checked') ? 1 : 0;
        var search_option_city = $('#search_option_city').attr('checked') ? 1 : 0;
        var search_option_state = $('#search_option_state').attr('checked') ? 1 : 0;
        var search_option_zip = $('#search_option_zip').attr('checked') ? 1 : 0;
        var search_option_country = $('#search_option_country').attr('checked') ? 1 : 0;
        var search_option_phone = $('#search_option_phone').attr('checked') ? 1 : 0;
        var search_option_limit = $('#limiter_checkbox').attr('checked') ? 1 : 0;

        self.loadingMask('#tab1 .table_list_scroll_block');
        $.ajax({
            url: "/w9/getlistbysearchquery",
            data: {
                query: query,
                search_option_com_name: search_option_com_name,
                search_option_fed_id: search_option_fed_id,
                search_option_addr1: search_option_addr1,
                search_option_addr2: search_option_addr2,
                search_option_city: search_option_city,
                search_option_state: search_option_state,
                search_option_zip: search_option_zip,
                search_option_country: search_option_country,
                search_option_phone: search_option_phone,
                search_option_limit:search_option_limit,
                sort_type: self.sortType,
                sort_direction: self.sortDirection
            },
            type: "POST",
            success: function(msg) {

                    $('#list_table tbody').html(msg);
                    self.initTable();
                    $('#list_table tbody .cutted_cell').tooltip(self.tooltipOptions);
                    self.setCheckedCount();


                self.endLoadingMask('#tab1 .table_list_scroll_block');
            }
        });
    },

    /**
     * Sets count of checked items
     */
    setCheckedCount: function() {
        var checkedCount = $(".list_checkbox:checked").length;
        $("#number_items").text(checkedCount);
        if (checkedCount != 1) {
            $('#company_info').html('<span style="font-size: 18px;">Details:</span>');
            //$('#print_document').hide();
        }
    },

    /**
     * Gets company info
     */
    getCompanyInfo: function(comId) {
        $.ajax({
            url: "/w9/getcompanyinfo",
            data: {comId: comId},
            type: "POST",
            success: function(msg) {
                $('#company_info').html(msg);
            }
        });
    },

    /**
     * Clear sorting arrows
     * @param cell
     */
    clearArrows: function(cell) {
        var row = cell.parent();
        var ths = row.find('th');
        ths.each(function() {
            $(this).removeClass('sorting_arrow_down');
            $(this).removeClass('sorting_arrow_up');
        });
    },

    /**
     * Add sort direction arrow
     * @param cell
     */
    addSortingArrow: function(cell) {

        if (this.sortDirection == 'ASC') {
            cell.addClass('sorting_arrow_up');
        } else {
            cell.addClass('sorting_arrow_down');

        }
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
    },

    toggleLimitCheckBoxVisibility: function(){
        var checked_apr = $('#search_option_to_be_approved').attr('checked') ? 1 : 0;
        var checked_batch = $('#search_option_to_be_batched').attr('checked') ? 1 : 0;

        if(checked_apr || checked_batch) {
            $("#limiter_div").hide();
        } else {
            $("#limiter_div").show();
        }

    },

    recalculateScroll:function(){
        var self = this;
        self.visibleHeight = $('.table_list_scroll_block').height();
        self.tableHeight = $('#scroll_wrapper').height();
    },


    //change client and project
    changeClientProject:function (cid,pid) {

        $('#change_client_id option:selected').val(cid);
        $('#change_project_id option:selected').val(pid);

        //console.log('form found ',$('#change_client form'));
        $('#change_client form')[0].submit();

        $("#lean_overlay").unbind('click');
        $(".hidemodal").unbind('click');


    },

    //change user (relogin)
    changeUser:function (uid) {
        window.location = '/site/relogin';

    },

    addToProjectsArray: function(projectID) {
        var self = this;
        self.projectsArray.push(projectID);
    },

    removeFromProjectsArray: function(projectID) {
        var self = this;
        var index = self.projectsArray.indexOf(projectID);
        if (index > -1) {
            self.projectsArray.splice(index, 1);
        }
    },

    getProjectItemsCount: function (projectID) {
        var self = this;
        var count = 0;
        for(var i = 0; i < self.projectsArray.length; ++i){
            if(array[i] == projectID)
                count++;
        }
        return count;
    },

    getProjectUniqueItemsCount: function () {
        var self = this;
        var uniqueNames = [];
        $.each(self.projectsArray, function(i, el){
            if($.inArray(el, uniqueNames) === -1) uniqueNames.push(el);
        });

        return uniqueNames.length;
    }



}