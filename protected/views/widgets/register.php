<div class="modal_box" id="registermodal" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <h1>User Register</h1>
    <?php
        if (isset($this->register_model) && $this->register_model !== false) {
            $model = $this->register_model;
        } else {
            $model = new RegisterForm('newClientScenario');
        }

        $clientsList = array('-1' => 'Choose a company', '0' => 'New company') + Clients::model()->getClientsList(true);
    ?>
    <?php $form=$this->beginWidget('CActiveForm', array (
        'id'=>'registerform',
        'action'=>Yii::app()->createUrl('/site/register'),
        //'enableClientValidation'=>true,
        'clientOptions'=>array(
            'validateOnSubmit'=>true,
        ),
    )); ?>
<div style="float: left;" id="userInfo">
    <div class="row">
        <h3>User info</h3>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'User_Login'); ?>
        <?php echo $form->textField($model,'User_Login',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'User_Login'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'First_Name'); ?>
        <?php echo $form->textField($model,'First_Name',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'First_Name'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Last_Name'); ?>
        <?php echo $form->textField($model,'Last_Name',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Last_Name'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Email'); ?>
        <?php echo $form->textField($model,'Email',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Email'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'Email_Confirmation'); ?>
        <?php echo $form->textField($model,'Email_Confirmation',array('class'=>'txtfield')); ?>
        <?php echo $form->error($model,'Email_Confirmation'); ?>
    </div>
    <div class="row">
        <label for="clientId">Company <span class="required">*</span> </label>
        <?php echo $form->dropDownList($model, 'Client_ID', $clientsList,array('class'=>'txtfield', 'id' => 'clientId')); ?>
        <?php echo $form->error($model,'Client_ID'); ?>
    </div>
</div>


    <div style="float: right; padding-left: 13px; border-left: 1px dotted #a2aEF8;display: none;width: 270px;" id="three_questions_info">
        <div class="row">
            <h3>Select three questions</h3>
        </div>


        Your three "Question-Answer" pairs which
        allow you to login from new devices:
        <?


        if (isset($answers)) {
            foreach($answers as $key=> $value) {
                echo '<div class="row" style="padding-left: 10px;margin-bottom:15px;margin-top: 9px; ">';
                echo CHtml::dropDownList('question','',UsersQuestions::getAllQuestionsWithEmpty(),array(
                    'options'=>array(
                        $question['Question_ID']=>array('selected'=>'selected')),
                    'class'=>'questions_dropdown',
                    'id'=>false
                ));
                echo '<br/>';
                echo '<input type="text" class="answer txtfield" name=answers['.$key.']  autocomplete="off" value="'.$value.'" >';
                echo '<div class="input_error" style="display: none;color: #ff0000;font-size: 10px;">Value can\'t be empty! Value can contain only literal symbols and digits! </div>';
                echo '<br/>';
                echo '</div>';
            }
        } else {
            for ($i=1;$i<=3;$i++) {
                echo '<div class="row" style="padding-left: 10px;margin-bottom:15px;margin-top: 9px; ">';
                echo CHtml::dropDownList('question','',UsersQuestions::getAllQuestionsWithEmpty(),array(
                    'class'=>'questions_dropdown_register',
                    'id'=>false
                ));
                echo '<br/>';
                echo '<input type="text" class="answer txtfield" name=answers['.$question['Question_ID'].']  autocomplete="off" >';
                echo '<div class="input_error" style="display: none;color: #ff0000;font-size: 10px;">Value can\'t be empty! Value can contain only literal symbols and digits! </div>';
                echo '<br/>';
                echo '</div>';
            }

        }

        ?>

        <div style="clear: both;"></div>

    </div>



    <div style="float: right; padding-left: 13px;margin-right: 10px; border-left: 1px dotted #a2aEF8;" id="companyInfo">
        <div class="row">
            <h3>Company info</h3>
        </div>
        <div class="row">
            <?php echo $form->label($model, 'Company_Name').'<span class="required"> *</span>'; ?>
            <?php echo $form->textField($model,'Company_Name',array('class'=>'txtfield')); ?>
            <?php echo $form->error($model,'Company_Name'); ?>
        </div>
        <div class="row">
            <?php echo $form->label($model, 'Fed_ID').'<span class="required"> *</span>' ?>
            <?php echo $form->textField($model,'Fed_ID',array('class'=>'txtfield')); ?>
            <?php echo $form->error($model,'Fed_ID'); ?>
        </div>
        <div class="row">
            <?php echo $form->label($model, 'Address1').'<span class="required"> *</span>' ?>
            <?php echo $form->textField($model,'Address1',array('class'=>'txtfield')); ?>
            <?php echo $form->error($model,'Address1'); ?>
        </div>
        <div class="row">
            <?php echo $form->label($model, 'City').'<span class="required"> *</span>' ?>
            <?php echo $form->textField($model,'City',array('class'=>'txtfield')); ?>
            <?php echo $form->error($model,'City'); ?>
        </div>
        <div class="row">
            <div style="width: 110px; float: left;margin-right: 10px;">
                <?php echo $form->label($model, 'State').'<span class="required"> *</span>' ?>
                <?php echo $form->textField($model,'State',array('class'=>'txtfield', 'style' => 'width: 100px;')); ?>
                <?php echo $form->error($model,'State'); ?>
            </div>
        </div>

        <div class="row">
            <div style="width: 140px; float: left;">
                <?php echo $form->label($model, 'ZIP').'<span class="required"> *</span>' ?>
                <?php echo $form->textField($model,'ZIP',array('class'=>'txtfield', 'style' => 'width: 130px;')); ?>
                <?php echo $form->error($model,'ZIP'); ?>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>





    <div style="clear: both; height: 10px;"></div>
    <div class="center">
        <?php //echo CHtml::submitButton('Register',array('id'=>'rgisterbtn','class'=>'flatbtn-blu hidemodal')); ?>

        <input id="next_rgisterbtn" class="flatbtn-blu" type="button" value="Next">
    </div>
    <?php $this->endWidget(); ?>
    <script type="text/javascript">
        $(document).ready(function() {
            var ready  = 0;
                var error_flag = 0;

            $('#clientId').change(function() {
                var base_width = $('#registermodal').width();
                var base_margin = $('#registermodal').css('margin-left');
                var new_width,new_margin_left = 0;
                var id = $(this).val();
                if (id == 0) {
                    new_width = base_width + 290;
                    new_margin_left = base_margin +290;
                    $('#registermodal').animate({width:new_width+'px', marginLeft:new_margin_left+'px'}, 200);
                    setTimeout(function() {
                        $('#companyInfo').show();
                    }, 210);
                } else {
                    new_width = base_width - 290;
                    new_margin_left = base_margin -290;
                    if (new_margin_left < -150) {new_margin_left=-150;}
                    if (new_width < 260) {new_width=260;}
                    if (ready ==1 ) {
                        new_margin_left= -340;
                        new_width = 550;
                    }
                    $('#registermodal').animate({width:new_width+'px', marginLeft:new_margin_left+'px'}, 200);
                    $('#companyInfo').hide();
                }
            });

            $("#next_rgisterbtn").click(function (e) {
                var i = 0;

                if (ready == 0) {
                    e.preventDefault();
                    e.stopPropagation();
                    var base_width = $('#registermodal').width();
                    var new_width = base_width + 290;

                    $('#registermodal').animate({width:new_width+'px',marginLeft:'-420px' }, 200);
                    setTimeout(function() {
                        $('#three_questions_info').show();
                    }, 210);
                    $('next_rgisterbtn').text('Register');
                    ready = 1;
                } else {
                    var questions = [''] ;
                    var answers = [''];
                    var error_flag = 0;


                    $('#three_questions_info .questions_dropdown_register').each(function() {

                        questions[i] = checkSelect($(this));
                        answers[i] = checkInput( $(this).parent().find('input.answer'));

                        if (questions[i] && answers[i]) {
                            error_flag = 0;
                        } else {
                            error_flag = 1;
                        }
                        i++;
                    });
                    if (error_flag===0 && checkSelectForUnique($('#three_questions_info .questions_dropdown_register')) == 'unique')  {
                        $('#registerform').submit();
                    }
                }
            });

            $('#three_questions_info .questions_dropdown_register').change(function () {

                    checkSelect($(this));

            });

            function checkInput (input){
                var inp_val = input.val();
                var pattern = /[^a-zA-Z0-9]+/g;

                if (inp_val === '' || pattern.test(inp_val)) {
                    input.parent().find('.input_error').show();
                    return null;
                } else {
                    input.parent().find('.input_error').hide();
                    return inp_val;
                }

            }

            function checkSelectForUnique(select_set) {
                var sel_arr = [];
                select_set.each(function () {

                    var index = $(this).find('option:selected').val();
                    var value = $(this).find('option:selected').text();
                    sel_arr [index] = value

                });

                var result = 0;
                for(var prop in sel_arr) {
                    if (sel_arr.hasOwnProperty(prop)) {
                        result++;
                    }
                }


                var length = result;

                console.log('array lenghth',length);
                if (length == 3) {
                    return "unique";

                } else {
                    return 'error';
                }

            }

            function checkSelect (select){

                var index = select.find('option:selected').val(); //id of dropdown
                var value = select.val();


                $('#three_questions_info .questions_dropdown_register').not(select).each(function () {

                    $(this).children('option').each(function() {
                        if ( $(this).val() === value ) {
                            $(this).attr('disabled','true');
                                //.siblings().attr('disabled',false);
                        }
                    });
                });



                var value = select.find('option:selected').text(); //text of dropdown
                //var already_selected =
                var input_connected  = select.parent().find('input.answer');

                input_connected.attr('name','answers['+index+']');

                if (index == -1) {

                    $('#three_questions_info .questions_dropdown_register').each(function () {

                        $(this).children('option').each(function() {
                            $(this).attr('disabled',false);
                        });

                        $(this).val($("this option:first").val());
                    });

                }

                return value;
            }

            $('.txtfield').blur(function() {
                var height1 = $("#userInfo").height();
                $('#companyInfo').height(height1);
            });
        });
    </script>
</div>