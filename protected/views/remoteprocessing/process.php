<?php
/* @var $this RemoteProcessingController */
$this->breadcrumbs=array(
	'Remote Processing',
);
?>

<div class="wrapper" style="min-height: 400px;">
<? $this->renderPartial('_process', array(
    'client_id' => $clientsList,
    'project_array'
)); ?>


    <div style="display: none">

        <form id="for_export">
        <input type="hidden" id="client_id_input" name="client_id" value= <?=$client_id?> >
        <?if($projects) {
          foreach ($projects as $project) {
            echo '<input type="checkbox" class="project_list_checkbox" name=projects[] checked value='.$project.'>';
            }
        }
        ?>

        </form>
    </div>

    <div id="status_section" style="display: none"> Starting processing....</div>
    <div id="second_section" style="display: none">  </div>
    <div id="loading_mask_left" style="height: 50px; width:610px;background: url(/images/rotate.gif) center no-repeat #fff;display: none; ">  </div>
    <br/>

    <a href="/remoteprocessing?id=0" id="submit_new_settings" class="button" data-id="2" style="display: none;">Pay by Stripe</a>











</div>





<script>
    $(document).ready(function() {
        var path= '';
        var client_id =0;
        var time_spend = '0';


                $('#status_section').fadeIn('slow');
                $('#loading_mask_left').fadeIn('slow');


                setTimeout(function() {
                    $('#status_section').fadeOut();
                    $('#status_section').append("<br>Preparing exchange files ....");
                    $('#status_section').fadeIn();
                },1000);

                var form_data =  $('#for_export').serialize();

                $.ajax({
                    url: "/remoteprocessing/export",
                    async:true,
                    type:"POST",
                    data: form_data,
                    dataType: 'json',

                    success: function( result ){
                        path = result['filepath'];
                        $('#status_section').append("<br>Time spent "+result['time_spent']);
                        time_spend = result['time_spent'];
                        client_id = result['client_id'];

                        $('#status_section').append("<br>Total files size "+result['files_size']+' B');
                                if (path !== '' ) {

                                    $('#second_section').show();
                                    var pb_interval= setInterval(function() {
                                        progress(pb_interval);
                                    },4000);



                                    $.ajax({
                                        url: "/remoteprocessing/xmltopdf",
                                        async:true,
                                        type:"POST",
                                        data: {path: path,
                                            time_spend:time_spend,
                                            client_id :client_id
                                        },
                                        dataType: 'json',
                                        success: function( result ){
                                            setTimeout(function () {
                                                $('#second_section').append("<br>Time spend "+result['time_spent']);
                                                $('#second_section').append("<br>Total files size "+result['files_size']+' B');
                                                $('#second_section').append("<br>Book pages "+result['pages']);
                                                $('#second_section').append("<br>Book size "+result['booksize']+' B');
                                                $('#loading_mask_left').fadeOut('slow');
                                                window.location = '/remoteprocessing?id='+result['pr_id'];
                                            },4001);
                                        },

                                        error: function (xhr, ajaxOptions, thrownError) {
                                            console.log('Error. Probably files are to large for concatenation. Run Remote Processing once again',xhr.responseText);
                                            clearInterval(pb_interval);

                                            alert('Remote Processing Error. \n'+xhr.responseText);
                                        }
                                    });


                                }
                    },

                    error: function (xhr, ajaxOptions, thrownError) {
                        console.log(xhr.status);
                        console.log(thrownError);
                    }
                });




        function progress (pb_interval) {
            $.ajax({
                url: "/site/GetTextProgressbar",
                type: "POST",
                async:true,
                dataType: 'json',
                    success: function(result) {

                        $('#second_section').html(result['status']);

                        if (result['state']==='theend') {
                            clearInterval(pb_interval);
                            $('#second_section').append("<br>Finished<br/>");
                        }
                    }
                });
        }


    });
</script>




<!--<div class="sidebar_right">
    <div class="row">
        <label style="margin-right: 10px;"> Select client for procesing </label>
        <input class="client_list">
    </div>



    <div id="projects_info" style="display: none;">

    </div>
</div>-->