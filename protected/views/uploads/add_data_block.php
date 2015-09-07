    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <div class="left uploads_block_left" style="height: inherit;">
        <p>Upload Data Entry:
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
                                //'doc_id'=> $file['name'],
                                'doc_id'=> $file['filepath'],
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

        <button class="button" id="add_fields_ok_button">OK</button>
        <button class="button" id="add_fields_remove_button">Remove</button>

        <br/>
        <div class="additional_fields_form">
            <div id="fed_id_block" style="position: relative;">
                <label>
                    Federal ID Number (EIN): <span class="required">*</span>
                </label>


                <input type="text" class="txtfield" name="add_fed_id" id="add_fed_id" value="<?php echo $file['fed_id'];?>" data-can-be-temp="<?=intval(!$existingCompany)?>" data-fed_id="<?=$file['fed_id'];?>" >



                    <?php if (!$existingCompany) { ?>
                        <div id="assign_fedid" style="position: absolute;top: 1px;right: 1px;"><a href="#">Assign</a></div>
                    <?}?>

                    <?php if (!$existingCompany && $file['fed_id']) { ?>

                        <div id="fed_id_status" style="color: <?php echo ($existingCompany) ? '#f00' : '#41B50B'; ?>; text-align: left;width: 180px;" >
                        new company
                        </div>
                    <?} else if ($existingCompany && $file['fed_id']) {?>
                        <div id="fed_id_status" style="color: <?php echo ($existingCompany) ? '#f00' : '#41B50B'; ?>; text-align: right;width: 180px;" > already exists</div>

                    <?} else {?>

                        <div id="fed_id_status" style="text-align: right;width: 180px;">
                        </div>
                    <?}?>


                <div id="assign_fedid_popup" style="position: absolute; top:25px;left:21px;width: 180px;height: 25px;background-color: #ffffff;display: none;">
                    <a href="#" class="add_intern_number" style="padding: 0px 20px;">Temp</a>
                    <a href="#" class="add_temp_number" style="padding: 0px 20px;"> Intern’tl</a>
                </div>
            </div>



            <label> Company Name: <span class="required">*</span> </label>

            <input type="text" class="txtfield" name="add_com_name" id="add_com_name"  value="<?php echo $file['company_name']; ?>"/>
            <?php if (!$existingCompany && $file['fed_id']) { ?>
                <div id="comp_name_status" style="color: <?php echo ($existingCompany) ? '#f00' : '#41B50B'; ?>; text-align: left;width: 180px;" >
                    new company
                </div>
            <?} else if ($existingCompany && $file['fed_id']) {?>
                <div id="comp_name_status" style="color: <?php echo ($existingCompany) ? '#f00' : '#41B50B'; ?>; text-align: right;width: 180px;" >
                    already exists
                </div>
            <?} else {?>
                <div id="comp_name_status" style="text-align: right;width: 180px;">
                </div>
            <?}?>


            <label> Business Name: </label>
            <input  class="txtfield" type="text" name="add_business_name" id="add_bussiness_name" value="<?=$bus_name;?>">
            <span id="add_business_name_status"></span>

            <label> Tax Class: </label>
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
                array(
                    'options'=>array(
                        $tax_name=>array('selected'=>true),
                    ),
                    'class' => 'txtfield'
                )
            );?>
            <br/>
            <span id="add_tax_class_status"></span>
            <label> Street Address: </label>
            <input type="text" class="txtfield" name="add_street_adr" id="add_street_adr" value="<?=$street_adr?>" >
            <span id="add_street_adr_status"></span>

            <label> City: </label>
            <input type="text" class="txtfield" name="add_city" id="add_city" value="<?=$city?>" >
            <span id="add_city_status"></span>

            <label> St: </label>
            <input type="text" class="txtfield"  name="add_street" id="add_street" value="<?=$state?>" >
            <span id="add_street_status"></span>

            <label> Zip: </label>
            <input type="text" class="txtfield" name="add_zip" id="add_zip" value="<?=$zip?>" >
            <span id="add_zip_status"></span>

            <label> Contact: </label>
            <input type="text" class="txtfield"  name="add_contact" id="add_contact" value="<?=$contact?>" >
            <span id="add_contact_status"></span>

            <label> Phone: </label>
            <input type="text" class="txtfield" name="add_phone" id="add_phone" value="<?=$phone?>" >
            <span id="add_phone_status"></span>


            <button class="button hidemodal" style="bottom: 35px; position: absolute; right: 20px;">Cancel</button>
        </div>
    </div>

    <div class="modal_box" id="companies_view_block" style="display:none;z-index: 1000;position: absolute;right: 245px;top:55px;">


        <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="close-popup"/>

        <div style="float: left;width: 260px;padding-top: 15px;padding-left: 10px;">
            <h4><b>Copy Companies Detais</b></h4>
        </div>


        <div  id="companies_view_block_details" style="border: 1px solid;float: right;overflow-y:auto;overflow-x:hidden; height: 250px ;" >
            <table>
                <tr><td></td><td></td></tr>
            </table>
        </div>



    </div>


    <div class="clear"></div>
