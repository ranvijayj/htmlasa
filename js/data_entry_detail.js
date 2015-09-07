function DataEntryDetail() {
    this.init();
}

DataEntryDetail.prototype = {
    /**
     * Search timeout
     */
    timeoutSearch:false,

    /**
     * Show alert on switch
     */
    showAlertOnSwitch: false,

    /*
     * Initialize method
     */
    init: function() {

        var self = this;

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

        $('#sample_revision_link ').click(function(event) {
            event.stopPropagation();
            var docId = 'sample_revision_picture'
            self.displayFile(docId, 'sample_revision');
        });

        $("#data_entry_left select, #data_entry_left input").click(function() {
            self.showAlertOnSwitch = true;
        });

        $('.data_entry_nav_wrapper a, .container_data_entry .breadcrumbs a, #data_entry_menu a').click(function(event) {


            if (self.showAlertOnSwitch) {
                event.preventDefault();

                /**
                //looking for modified fields. For future if the will be needed to find only modified fields
                var new_inputs_array=new Array();
                var form_id=$('.data_entry_form').attr('id');
                var inputs_array=$('.data_entry_form').find('input');
                console.log(form_id);
                $.each(inputs_array,function(){
                        var name=$(this).attr('name');
                        var old=$(this).prop('defaultValue');
                        var new_val=$(this).val();

                        if(old!==new_val){
                            new_inputs_array[name]=new_val;
                            console.log(name+' changed from '+old+' to '+new_val);
                        }
                    }
                );
                //transfering modified fields to function for saving in database
                //console.log(new_inputs_array);
                self.updatePartialFields(form_id,new_inputs_array);
                */
                //show_alert('All required fields must be complete before moving forward. A second click will abandon your data', 500);

                var url=$(this).attr('href');
                $('#dialogmodal a').attr('href',url);
                self.showAlertOnSwitch = false;
                //show_dialog('Some data you entered might not be saved. Press "Yes" to leave or press "No" to save data manually \n', 500);

                show_dialog('You are about to leave this page, but there are some changes that have not been saved. Press "Yes" to Save&Continue or press "No" to go back', 500);

            }
        });

        $('#dialogmodal a').click(function(event) {
            //event.preventDefault();
            //event.stopPropagation();
            self.submitFormInBackground();
        });

        var document_view = new DocumentView('#data_entry_right', '#tab1_block', '#w9_detail_block1', 735, 45, 10);

        $('.in_place_edit').click(function() {

            var editing = $(this).attr('data-editing');
            if (editing==0){
                var page_value =parseInt($(this).text());

                $(this).attr('data-editing','1');
                $(this).html('<input type="text" value="'+page_value+'" class="in_place_input" style="width:30px;" >');

                $('.in_place_input').focus();
                $('.in_place_input').bind('blur',function(){

                    var value = parseInt($(this).val());
                    if(isNaN(value)){value=1;}
                    //console.log(document.URL);
                    $(this).parent().text(value);
                    var url= $('.items_switch_de a.button').attr('href');
                    var url_arr = url.split('?');
                    console.log(url);
                    window.location=url_arr[0]+'?page='+value;
                });
                $('.in_place_input').bind('keypress',function(event){
                    if (event.keyCode == 13) {
                        $(this).blur();
                    }
                });

            }

        });

        $('.in_place_input').bind('blur',function(){

            var value = parseInt($(this).val());
            if(isNaN(value)){value=1;}
            //console.log(document.URL);
            $(this).parent().text(value);
            var url= $('.items_switch_de a.button').attr('href');
            var url_arr = url.split('?');
            console.log(url);
            window.location=url_arr[0]+'?page='+value;
        });
        $('.in_place_input').bind('keypress',function(event){
            if (event.keyCode == 13) {
                $(this).blur();
            }
        });




    },

    displayFile: function(docId, fileType) {
        var self = this;
        $.ajax({
            url: "/uploads/getfilesblock",
            data: {imgId: docId, fileType: fileType},
            async: false,
            type: "POST",
            success: function(msg) {
                if (msg) {

                    $('#image_view_block').html(msg);
                    show_modal_box('#image_view_block', 725, 20);
                    self.initDetailsBlock();
                }
            }
        });
    },
    /**
     * Initialize details block
     */
    initDetailsBlock: function() {
        var image_view = new DocumentView('#image_view_block', '#file_detail_block_conteiner', '#file_detail_block', 525, 76, 1000);


    },

    updatePartialFields:function (form_id,new_inputs_array){
        var self = this;

        if (form_id=='w9_data_entry_form'){
            var arr=JSON.stringify(new_inputs_array);
            console.log(arr);
          $.ajax({
              url: "/w9/UpdatePartialField",
              data: {arr: arr},
              dataType:'json',
              type: "POST",
              success: function(msg) {
                  if (msg) {

                  }
              }
          });

      }
    },


    submitFormInBackground:function(){

        var form_id=$('.data_entry_form').attr('id');
        //var action=$('.data_entry_form').attr('action');

        if (form_id=='w9_data_entry_form'){
            $.ajax({
                type: 'POST',
                url: '/dataentry/ajaxw9save',
                data: $('#'+form_id).serialize(),
                success: function(){
                    console.log('Success');
                }
            });
        } else if (form_id=='po_data_entry_form'){
            $.ajax({
                type: 'POST',
                url: '/dataentry/ajaxposave',
                data: $('#'+form_id).serialize(),
                success: function(){
                    console.log('Success');
                }
            });
        }else if (form_id=='ap_data_entry_form'){
            $.ajax({
                type: 'POST',
                url: '/dataentry/ajaxapsave',
                data: $('#'+form_id).serialize(),
                success: function(){
                    console.log('Success');
                }
            });
        }else if (form_id=='payment_data_entry_form'){
            $.ajax({
                type: 'POST',
                url: '/dataentry/ajaxpaysave',
                data: $('#'+form_id).serialize(),
                success: function(){
                    console.log('Success');
                }
            });
        } else if (form_id=='pc_data_entry_form'){
            $.ajax({
                type: 'POST',
                url: '/dataentry/ajaxpcsave',
                data: $('#'+form_id).serialize(),
                success: function(){
                    console.log('Success');
                }
            });
        } else if (form_id=='payroll_data_entry_form'){
            $.ajax({
                type: 'POST',
                url: '/dataentry/ajaxpayrsave',
                data: $('#'+form_id).serialize(),
                success: function(){
                    console.log('Success');
                }
            });
        } else if (form_id=='je_data_entry_form'){
            $.ajax({
                type: 'POST',
                url: '/dataentry/ajaxjesave',
                data: $('#'+form_id).serialize(),
                success: function(){
                    console.log('Success');
                }
            });
        }else if (form_id=='ar_data_entry_form'){
            $.ajax({
                type: 'POST',
                url: '/dataentry/AjaxArSave',
                data: $('#'+form_id).serialize(),
                success: function(){
                    console.log('Success');
                }
            });
        }
    }

}