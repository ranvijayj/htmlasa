    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <div class="left uploads_block_left" style="height: inherit;">
        <p>Vendor Detail Data Entry:
        <?if ($mode=='dataentry' || $mode=='createpage') {?>
            <a href="#" id="w9_vendor_upload_link" style="margin-left: 20px;">Click to upload</a>
            <input id="fileupload_add_block" type="file" name="files[]" style="display: none;">
        <?}?>
        </p>
        <div id="additional_fields_block_conteiner" style="height: 570px;">
                <input type="hidden" id="current_file_name" value="<?=$file['name']?>">
                <div id="embeded_pdf">
                        <?php $this->widget('application.components.ShowPdfWidget', array(
                            'params' => array(
                                'doc_id'=> $file['name'],
                                'mime_type'=>$file['mimetype'],
                                'mode'=>Helper::isMobileComplexCheck()? 5 : 3,
                                'approved'=>0,
                                'show_rotate'=>1,
                                'height'=>570
                            ),
                        )); ?>
                    </div>

                </div>
                <div class="w9_detail_block_bar">
                    <div class="image_buttons right">
                        <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/fullsize.jpg" alt=""  class="w9_detail_block_bar_button fullsize_button"/>
                        <?php
                        if (strpos($file['mimetype'], 'pdf') === false) {
                            echo '<img src="' . Yii::app()->request->baseUrl . '/images/minlup.jpg" alt=""  class="w9_detail_block_bar_button minlup_button"/>
                                  <img src="' . Yii::app()->request->baseUrl . '/images/pluslup.jpg" alt="" class="w9_detail_block_bar_button pluslup_button"/>';
                        }
                        ?>
                </div>
        </div>
    </div>
    <div class="right uploads_block_right">

        <div class="additional_fields_form">

                <?php
                $url = '/vendor/detail';
                if ($page > 1) {
                    $url .= '?page=' . $page;
                }

                $form=$this->beginWidget('CActiveForm', array (
                    'id'=>'updatew9detail_form',
                    'action'=>Yii::app()->createUrl($url),
                    'clientOptions'=>array(
                        'validateOnSubmit'=>true,
                    ),
                )); ?>
                <input type="hidden" name="Company_ID" value="<?=$company->Company_ID;?>">
                <input type="hidden" name="Vendor_ID" value="<?=$vendor->Vendor_ID;?>">
                <input type="hidden" name="Person_ID" value="<?//=$person->Person_ID;?>">
                <div class="row">
                    <?php echo $form->labelEx($company,'Federal ID Number (EIN): '); ?>
                    <?php echo $form->textField($company,'Company_Fed_ID',array('class'=>'txtfield')); ?>
                    <?php echo $form->error($company,'Company_Fed_ID'); ?>
                </div>

                <div class="row">
                    <?php echo $form->labelEx($company,'Company Name:');?>
                    <?php echo $form->textField($company,'Company_Name',array('class'=>'txtfield'));?>
                    <?php echo $form->error($company,'Company_Name'); ?>
                </div>

                <div class="row">
                    <?php echo $form->labelEx($company,'Business Name: ');?>
                    <?php echo $form->textField($company,'Business_NameW9',array('class'=>'txtfield'));?>
                    <?php echo $form->error($company,'Business_NameW9'); ?>
                </div>



            <label> Tax Class: </label><br/>
            <?php echo CHtml::dropDownList('listname',1,
                array(
                    '0' => 'Unknown Tax Class',
                    'SP'=>'Sole Proprietor',
                    'C'=>'Corp.',
                    'CC'=>'C Corp.',
                    'CS'=>'S Corp.',
                    'PS'=>'Partnership',
                    'TE'=>'Trust/Estate',
                    'LL'=>'LLC',
                    'LC'=>'LLC, C Corp.',
                    'LS'=>'LLC, S Corp.',
                    'LP'=>'LLC, Prtshp.',
                    'OT' => 'Other',
                ),
                array('class'=>'txtfield'),
                array('options'=>array($tax_name=>array('selected'=>true)))
            );?>
            <br/>
                <div class="row">
                    <?php echo $form->labelEx($address,'Street Address :');?>
                    <?php echo $form->textField($address,'Address1',array('class'=>'txtfield'));?>
                    <?php echo $form->error($address,'Address1'); ?>
                </div>

                <div class="row">
                    <?php echo $form->labelEx($address,'Add Address :');?>
                    <?php echo $form->textField($address,'Address2',array('class'=>'txtfield'));?>
                    <?php echo $form->error($address,'Address2'); ?>
                </div>

                <div class="row">
                    <?php echo $form->labelEx($address,'City :');?>
                    <?php echo $form->textField($address,'City',array('class'=>'txtfield'));?>
                    <?php echo $form->error($address,'City'); ?>
                </div>

                <div class="row">
                    <?php echo $form->labelEx($address,'State :');?>
                    <?php echo $form->textField($address,'State',array('class'=>'txtfield'));?>
                    <?php echo $form->error($address,'State'); ?>
                </div>


                <div class="row">
                    <?php echo $form->labelEx($address,'ZIP :');?>
                    <?php echo $form->textField($address,'ZIP',array('class'=>'txtfield'));?>
                    <?php echo $form->error($address,'ZIP'); ?>
                </div>

                <div class="row">
                    <?php echo $form->labelEx($address,'Country :');?>
                    <?php echo $form->textField($address,'Country',array('class'=>'txtfield'));?>
                    <?php echo $form->error($address,'Country'); ?>
                </div>


            <?if ($person) {?>
                <div class="row">
                    <?php echo $form->labelEx($person,$person->First_Name.' '.$person->Last_Name).'\'s phone';?>
                    <?php echo $form->textField($person,'Direct_Phone',array('class'=>'txtfield'));?>
                    <?php echo $form->error($person,'Direct_Phone'); ?>
                </div>
            <?}?>

            <?if ($vendor) {?>

                <?php echo $form->hiddenField($vendor,'Vendor_ID');?>

                <div class="row">
                    <?php echo $form->labelEx($vendor,'Vendor Contact');?>
                    <?php echo $form->textField($vendor,'Vendor_Contact',array('class'=>'txtfield'));?>
                    <?php echo $form->error($vendor,'Vendor_Contact'); ?>
                </div>

                <div class="row">
                    <?php echo $form->labelEx($vendor,'Vendor Phone');?>
                    <?php echo $form->textField($vendor,'Vendor_Phone',array('class'=>'txtfield'));?>
                    <?php echo $form->error($vendor,'Vendor_Phone'); ?>
                </div>
            <?}?>



    </div>


    <div style="clear: both; height: 10px;"></div>
    <div class="center">
        <?php echo CHtml::submitButton('Save',array('id'=>'editw9vendorbtn','class'=>'button hidemodal')); ?>
    </div>

        <?php $this->endWidget(); ?>
    </div>
