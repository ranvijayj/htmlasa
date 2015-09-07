

<div style="padding: 10px 320px 10px 10px;margin-bottom: 20px;">
    <div class="right items_switch">
        <button class="not_active_button left" id="prev_button">Prev</button>
        <span class="items_switch_counter">
            <div id="current_page" style="display: inline" class='in_place_edit' data-editing="0"><?=$page?></div>
            of <div id="total_pages" style="display: inline" data-id="<?=count($similar_file_to_upload);?>"><?=count($similar_file_to_upload);?></div>
        </span>
        <button class="button right" id="next_button">Next</button>
    </div>
</div>

<div id="variable_part">
<?php $this->renderPartial('_files_dialog', array(
    'similar_file_to_upload' => $similar_file_to_upload[$page-1],
    'show_similar_files_block' => true,
)); ?>
</div>


<script type="text/javascript">
    $(document).ready(function() {
        var total_pages =$('#total_pages').data('id');

        if(total_pages==1) {$('button.right').addClass('not_active_button').removeClass('button');}

        $('#next_button').click(function (event) {
            event.preventDefault();
            if ($(this).hasClass('button')){
                var number_div=$('#current_page');
                var cur_val= number_div.html();
                cur_val++;
                refreshButtonsClass(cur_val);
                $.ajax({
                    type:"POST",
                    url: '/uploads/getCompareBlock',
                    data: {page:cur_val},
                    success: function (html) {

                            $('#variable_part').empty();
                            $('#variable_part').append(html);
                            number_div.html(cur_val);

                    }
                });
                if (cur_val == total_pages) {$(this).removeClass('button').addClass('not_active_button');}
            }
        });

        $('#prev_button').click(function (event) {
            event.preventDefault();
            if ($(this).hasClass('button')){
                var number_div=$(this).parent().find('.items_switch_counter').find('.in_place_edit');
                var cur_val= number_div.html();
                cur_val--;
                refreshButtonsClass(cur_val);
                console.log('Curr vall = '+cur_val);
                $.ajax({
                    type:"POST",
                    url: '/uploads/getCompareBlock',
                    data: {page:cur_val},
                    success: function (html) {

                        $('#variable_part').empty();
                        $('#variable_part').append(html);
                        number_div.html(cur_val);

                    }

                });
                if (cur_val == total_pages) {$(this).removeClass('button').addClass('not_active_button');}
            }
        });


    function refreshButtonsClass(cur_val){
        if (cur_val>1 && $('button.left').hasClass('not_active_button') ){
            $('button.left').removeClass('not_active_button').addClass('button');
        }
        if (cur_val==1 && $('button.left').hasClass('button') ){
            $('button.left').removeClass('button').addClass('not_active_button');
        }
        if (cur_val<total_pages && $('button.right').hasClass('not_active_button') ){
            $('button.right').removeClass('not_active_button').addClass('button');
        }
        if (cur_val==total_pages && $('button.right').hasClass('button') ){
            $('button.right').removeClass('button').addClass('not_active_button');
        }

    }
        $('#leave_dublicate_file').unbind('click');
        $('#leave_dublicate_file').bind('click',function(event) {
            console.log ('click event');
            //1 delete it from dublicates array
            //2 mark as not dublicate in uploads array
            var filename = $('#new_file_name').attr('data-filename');



            var cur_val= $('#current_page').html();
            var total =  $('#total_pages').html();
            $.ajax({
                type:"POST",
                url: '/uploads/DeleteFileFromHashChecking',
                data: {
                    page: cur_val,
                    filename: filename
                },
                success: function (html) {
                    showDublicates();

                    setTimeout(function () {
                        if (total == 1) {
                            window.location= '/uploads';
                        }
                    },500);
                }

            });

        });

        $('#delete_dublicate_file').unbind('click');
        $('#delete_dublicate_file').click(function(event) {
            //1. delete it from session
            var cur_val= $('#current_page').html();
            var total =  $('#total_pages').html();
            $.ajax({
                type:"POST",
                url: '/uploads/DeleteFileFromSession',
                data: {
                       page:cur_val,
                       filename: $('#new_file_name').data('filename')
                      },
                success: function (html) {
                            showDublicates();

                            setTimeout(function () {
                                if (total == 1) {
                                    window.location= '/uploads';
                                }
                            },500);
                        }

            });
        });

    function showDublicates() {
        $.ajax({
            type:"POST",
            url: '/uploads/AjaxCheckFileHash',
            success: function (html) {
                if (html=='files_are_not_similar') {
                } else {
                    $('#similar_files_block_wrapper').empty();
                    $('#similar_files_block_wrapper').append(html);
                }
            }
        });
    }

    });

</script>