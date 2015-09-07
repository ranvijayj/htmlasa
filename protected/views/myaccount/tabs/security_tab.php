<h2>Security Settings</h2>

<?php
$restricted_users_array=array('');
$alowed_users_array=array('client_admin','db_admin','admin','approver','processor','user','data_entry_clerk');
if(in_array($user_role,$alowed_users_array)){
    $form=$this->beginWidget('CActiveForm', array (
        'id'=>'securityform',
        'action'=>Yii::app()->createUrl('/myaccount/SetDeviceCheck'),
    ));
    ?>

    <fieldset>
        <div id="user_id" data-id="<?=Yii::app()->user->userID;?>"></div>
        <div class="group radio_group" style="min-height: 550px;">
            <div style="text-align: left;">
                <?echo $form->checkBox($user_settings,'Use_Device_Checking',array('checked'=>$user_settings->Use_Device_Checking ? 'checked' : ''));?>
                Strengthen user authentication by device checking
                <?php echo $form->error($user_settings,'Use_Device_Checking'); ?>
            </div>

            <div id="three_questions" style="padding-left: 30px;padding: 5px ;margin-bottom:10px; border: 1px solid #d3d3d3; display: none;">
                Your three "Question-Answer" pairs which allow you to login from new devices:
                <?
                if (count($users_questions)>0) {
                    $i=1;
                    foreach ($users_questions as $question) {

                        echo '<div class="row" style="padding-left: 10px;margin-bottom:15px;margin-top: 9px; ">';
                        //echo $i.') ';
                        echo CHtml::dropDownList('question','',UsersQuestions::getAllQuestions(),array(
                            'options'=>array(
                                $question['Question_ID']=>array('selected'=>'selected')),
                                'class'=>'questions_dropdown',
                                'id'=>false
                        ));
                        echo '<br/>';
                        echo '<input type="text" class="answer" name=answers['.$question['Question_ID'].'] value="'.$question['Hint'].'" autocomplete="off" >';
                        echo '<div class="input_error" style="display: none;color: #ff0000;font-size: 10px;">Value can\'t be empty! Value can contain only literal symbols and digits! </div>';
                        echo '<br/>';
                        $i++;
                        echo '</div>';
                    }
                }
                ?>
                <button class="not_active_button" href="#" id="save_questions" style="padding:0px 6px;"> Save security questions </button>
                <br/><br/>
            </div>
           <br/>
            Your devices :
            <div class="devices" style="max-height: 465px;margin-top: 10px;overflow: auto;">

                <?foreach (UsersDevices::getUsersDeviceList(Yii::app()->user->userID) as $device) {?>
                    <div class="device-item"  style="border: 1px solid #DDDDDD">
                        <div class="manage_link" style="float: right;padding-right: 5px; "> <a href="#" data-id="<?=$device['Hash']?>">Remove from trusted</a> </div>
                        <table>
                            <tr>
                                <th>Last access</th> <td style="width: 265px;"> <?=$device['Lastlogin']; ?> </td>
                            </tr>

                            <tr>
                                <th>Last location</th> <td style="width: 265px;"> <?=$device['IP']; ?> </td>
                            </tr>
                            <tr>
                                <th>Device type</th> <td style="width: 265px;"> <?=$device['DeviceType']; ?> </td>
                            </tr>
                            <tr>
                                <th>Hash</th> <td style="width: 265px;"> <?=$device['Hash']; ?> </td>
                            </tr>
                        </table>
                    </div>

                <?}?>
            </div>



        </div>

    </fieldset>
    <input type="hidden" value="true" name="settings_form">
    <input name="submit" type="submit" value="submit" class="hide">
        <br/>


    <?php
        $this->endWidget();
}
     ?>

<script type="text/javascript">
    $(document).ready(function() {
        if ($('#UsersSettings_Use_Device_Checking').prop('checked')) {
            $('#three_questions').show();
        }

        $('#UsersSettings_Use_Device_Checking').change(function () {
           if ($(this).prop('checked')) {
               $('#three_questions').slideDown( "slow", function() {
                   // Animation complete.
               });
               $.ajax({
                   url: "/myaccount/SetDeviceCheck",
                   type: "POST",

                   success: function(msg){

                   }
               });
           } else {
               $('#three_questions').slideUp( "slow", function() {
                   // Animation complete.
               });
               $.ajax({
                   url: "/myaccount/UnsetDeviceCheck",
                   type: "POST",

                   success: function(msg){

                   }
               });

           }
        });

        $('.questions_dropdown').on('change',function (){
            $('#save_questions').removeClass('not_active_button').addClass('button');
        });

        $('.answer').on('blur',function (){
            $('#save_questions').removeClass('not_active_button').addClass('button');
        });

        $('#save_questions').click(function (e) {
            e.stopPropagation();
            e.preventDefault();
            if ($(this).hasClass('button')) {
                var questions = [''] ;
                var answers = [''];
                var error_flag = 0;
                var i = 0;

                var value,index = null;
                $('#three_questions .questions_dropdown').each(function() {

                    index = $(this).find('option:selected').val(); //id of dropdown
                    value = $(this).find('option:selected').text(); //text of dropdown

                    questions[i]= value;
                    answers[i] = $(this).parent().find('input.answer').val();


                    var pattern = /[^a-zA-Z]+/g;
                    if (answers[i] === '' || pattern.test(answers[i]) ) {
                        $(this).parent().find('.input_error').show();
                        error_flag = 1;
                    } else {
                        $(this).parent().find('.input_error').hide();
                    }
                    i++;
                });

                if (error_flag===0) {
                    $.ajax({
                        url: "/usersAnswers/setanswers",
                        type: "POST",
                        data: {
                            questions: questions,
                            answers: answers
                        },
                        success: function(msg){
                            window.location = '/myaccount?tab=security'
                        }
                    });
                }

            }


        });

        $('.manage_link a').click(function () {
            var link =$(this);
            var id= link.data('id');
            $.ajax({
                url: "/usersdevice/deletedevice",
                type: "POST",
                data: {device_id: id},
                success: function(msg){
                    if (msg==1) {


                        console.log(link.parent().parent());
                        link.parent().parent().fadeOut('slow');
                    }
                }
            });

        });




    });
</script>