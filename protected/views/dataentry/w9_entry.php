<?php
/* @var $this VendorController */

$this->breadcrumbs=array(
    'W9 Data Entry'
);
?>
<h1>W9 Data Entry
    <?php if ($num_pages > 0) { ?>
    <span style="font-weight: normal; margin-left: 80px;">
        <?php
            $w9_verified_switch = 'disabled="disabled"';
            $show_verified_script = false;
            if ($w9->Revision_ID <= '0' && Yii::app()->user->userType != Users::DB_ADMIN) {
                // when data entry is not completed
                $w9_verified_switch = 'disabled="disabled"';
            } else if ($w9->Verified == 1 && Yii::app()->user->userType != Users::DB_ADMIN) {
                // when data entry is completed and verified
                $w9_verified_switch = 'disabled="disabled" checked="checked"';
            } else if ($w9->Verified != 1 && Yii::app()->user->userType == Users::DATA_ENTRY_CLERK) {
                // when data entry is completed and not verified (other Data Entry Clerk can verify if)
                $w9_verified_switch = '';
                $show_verified_script = true;
            } else if ($w9->Verified == 1 && Yii::app()->user->userType == Users::DB_ADMIN) {
                // for DB Admin checkbox always enabled
                $w9_verified_switch = 'checked="checked"';
                $show_verified_script = true;
            } else if (Yii::app()->user->userType == Users::DB_ADMIN) {
                // for DB Admin checkbox always enabled
                $show_verified_script = true;
                $w9_verified_switch = '';
            }

            if ($show_verified_script) {
                ?>
                <script type="text/javascript">
                    $(document).ready(function() {
                        $('#w9_verified_switch').click(function() {
                            if (!$(this).attr('checked')) {
                                $('#w9_verified_value').val('0');
                            } else {
                                $('#w9_verified_value').val('1');
                            }
                        });
                    });
                </script>
                <?php
            }
        ?>
        <label for='w9_verified_switch'>Verified: </label>
        <input id="w9_verified_switch" type="checkbox" value="1" name="w9_verified_switch" <?php echo $w9_verified_switch; ?>>
    </span>
        <?if ($show_already_exists_link) {?>
            <span id="fed_id_status" style="color: rgb(191, 0, 0);top:0px;cursor: pointer;font-weight: normal;margin-left: 40px;">already exists</span>
        <?}?>
    <?php } ?>
    <div id="filename_section" style="float: right;margin-right:10px;font-size:17.7px;font-style: normal;font-weight: normal; " ><?=$file->File_Name; ?> </div>
</h1>

<div class="account_manage">
  <div class="de_nav_wrapper">
    <?php if ($num_pages > 0) { ?>
        <div class="data_entry_nav_wrapper">
            <?php $this->renderPartial('application.views.widgets.pages_navigation_de', array(
                'page' => $page,
                'num_pages' => $num_pages,
                'url' => '/dataentry/w9',
                'position' => 'left'
            )); ?>
        </div>
    <?php } ?>
    <form method="post" action="/dataentry/w9">
        <div class="left" style="margin-left: <?php echo ($num_pages > 0) ? '65' : '10'; ?>px;">
            <span class="left search_block_label">Search: &nbsp;</span>
            <div class="search_block">
                <input type="text" name="search_field" id="search_field" maxlength="250" value="<?php echo $_SESSION['last_w9_to_entry_search']['query'];?>">
                <div id="search_options">
                    <span class="search_options_header">Search in the fields:</span><br/>
                    <?php
                    echo Helper::getSearchOptionsHtml(array(
                        'session_name' => 'last_w9_to_entry_search',
                        'options' => array(
                            'search_option_com_name' => array('Vendor Name', 1),
                            'search_option_fed_id' => array('Fed ID', 1),
                            'search_option_addr1' => array('Addre. 1', 0),
                            'search_option_addr2' => array('Addre. 2', 0),
                            'search_option_city' => array('City', 0),
                            'search_option_state' => array('State', 0),
                            'search_option_zip' => array('Zip', 0),
                            'search_option_country' => array('Country', 0),
                            'search_option_phone' => array('Phone', 0),
                        ),
                    ));
                    ?>
                </div>
            </div>
        </div>
    </form>
    <span class="de_count_items" style="margin-left: 40px;">W9s to Process: <?php echo $num_pages; ?> items</span>
  </div>
</div>
<?php if(Yii::app()->user->hasFlash('success')):?>
    <div class="info">
        <button class="close-alert">&times;</button>
        <?php echo Yii::app()->user->getFlash('success'); ?>
    </div>
<?php endif; ?>


<div id="data_entry_left_right_wrapper">
<div id="data_entry_left">
<?php if ($num_pages > 0) {?>
    <h2><?php echo CHtml::encode($company->Company_Name);?></h2>
    <span class="de_company_id">
        Company ID: <span class="details_page_value"><?php echo $company->Company_ID;//echo isset($client->vendor->Vendor_ID_Shortcut) ? CHtml::encode($client->vendor->Vendor_ID_Shortcut) : ''; ?></span>
    </span>

    <?php $form=$this->beginWidget('CActiveForm', array (
        'id'=>'w9_data_entry_form',
        'htmlOptions'=>array(
            'class'=>'data_entry_form',
        ),
    ));



    ?>
    <fieldset>
        <div class="group">
            <?php
                $htmlOptions = array();
                if ($w9->Verified == 1 && Yii::app()->user->userType != Users::DB_ADMIN) {
                    $htmlOptions = array('disabled'=>'disabled');
                }
            ?>
            <label for="W9_Business_Name">Business Name</label>
            <?php echo $form->textField($w9,'Business_Name' , $htmlOptions); ?>
            <?php echo $form->error($w9,'Business_Name'); ?>
        </div>
        <div class="group">
            <?php
            $htmlOptions = array();
            if ($w9->Verified == 1 && Yii::app()->user->userType != Users::DB_ADMIN) {
                $htmlOptions = array('disabled'=>'disabled');
            }
            ?>
            <label for="W9_Tax_Class"><span class="red">*</span> Tax Class</label>
            <?php echo $form->dropDownList($w9,'Tax_Class',
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
                ), $htmlOptions); ?>
            <?php echo $form->error($w9,'Tax_Class'); ?>
        </div>
        <div class="group">
            <?php
            $htmlOptions = array();
            if ($w9->Verified == 1 && Yii::app()->user->userType != Users::DB_ADMIN) {
                $htmlOptions = array('disabled'=>'disabled');
            }
            ?>
            <?php echo $form->labelEx($w9,'Exempt'); ?>
            <?php echo $form->checkBox($w9,'Exempt', $htmlOptions); ?>
            <?php echo $form->error($w9,'Exempt'); ?>
        </div>
        <div class="group">
            <?php
            $htmlOptions = array();
            if ($w9->Verified == 1 && Yii::app()->user->userType != Users::DB_ADMIN) {
                $htmlOptions = array('disabled'=>'disabled');
            }
            ?>
            <label for="Addresses_Address1"><span class="red">*</span> Street Address</label>
            <?php echo $form->textField($address,'Address1', $htmlOptions); ?>
            <?php echo $form->error($address,'Address1'); ?>
        </div>
        <div class="group">
            <?php
            $htmlOptions = array();
            if ($w9->Verified == 1 && Yii::app()->user->userType != Users::DB_ADMIN) {
                $htmlOptions = array('disabled'=>'disabled');
            }
            ?>
            <label for="Addresses_City"><span class="red">*</span> City</label>
            <?php echo $form->textField($address,'City', $htmlOptions); ?>
            <?php echo $form->error($address,'City'); ?>
        </div>
        <div class="group">
            <?php
            $htmlOptions = array();
            if ($w9->Verified == 1 && Yii::app()->user->userType != Users::DB_ADMIN) {
                $htmlOptions = array('disabled'=>'disabled');
            }
            ?>
            <label for="Addresses_State"><span class="red">*</span> State</label>
            <?php echo $form->textField($address,'State', $htmlOptions); ?>
            <?php echo $form->error($address,'State'); ?>
        </div>
        <div class="group">
            <?php
            $htmlOptions = array();
            if ($w9->Verified == 1 && Yii::app()->user->userType != Users::DB_ADMIN) {
                $htmlOptions = array('disabled'=>'disabled');
            }
            ?>
            <label for="Addresses_ZIP"><span class="red">*</span> Zip</label>
            <?php echo $form->textField($address,'ZIP', $htmlOptions); ?>
            <?php echo $form->error($address,'ZIP'); ?>
        </div>
        <div class="group">
            <?php
            $htmlOptions = array();
            if ($w9->Verified == 1 && Yii::app()->user->userType != Users::DB_ADMIN) {
                $htmlOptions = array('disabled'=>'disabled');
            }
            ?>
            <?php echo $form->labelEx($w9,'Account_Nums'); ?>
            <?php echo $form->textField($w9,'Account_Nums', $htmlOptions); ?>
            <?php echo $form->error($w9,'Account_Nums'); ?>
        </div>
        <div class="group">
            <?php
            $htmlOptions = array();
            if ($w9->Verified == 1 && Yii::app()->user->userType != Users::DB_ADMIN) {
                $htmlOptions = array('disabled'=>'disabled');
            }
            ?>
            <?php echo $form->labelEx($company,'SSN'); ?>
            <?php echo $form->textField($company,'SSN', $htmlOptions); ?>
            <?php echo $form->error($company,'SSN'); ?>
        </div>
        <div class="group">
            <?php
                $htmlOptions = array('disabled'=>'disabled');
            ?>
            <label for="Companies_Company_Fed_ID"><span class="red">*</span> Fed_ID</label>
            <?php echo $form->textField($company,'Company_Fed_ID', $htmlOptions); ?>
            <?php echo $form->error($company,'Company_Fed_ID'); ?>
        </div>
        <div class="group">
            <?php
            $htmlOptions = array();
            if ($w9->Verified == 1 && Yii::app()->user->userType != Users::DB_ADMIN) {
                $htmlOptions = array('disabled'=>'disabled');
            }
            ?>
            <label for="W9_Signed"><span class="red">*</span> Signed</label>
            <?php echo $form->checkBox($w9,'Signed', $htmlOptions); ?>
            <?php echo $form->error($w9,'Signed'); ?>
        </div>
        <div class="group">
            <?php
            $htmlOptions = array();
            if ($w9->Verified == 1 && Yii::app()->user->userType != Users::DB_ADMIN) {
                $htmlOptions = array('disabled'=>'disabled');
            }

            // convert date string to view format
            if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $w9->Signature_Date)) {

                $w9->Signature_Date = Helper::convertDateSimple($w9->Signature_Date);
            }
           /* echo "Date time explanation<br> ";
            echo "<br>Row w9 signature date<br> ".$w9->Signature_Date."<br>";

            //$date = date("Y-m-d H:i:s", strtotime($w9->Signature_Date) - date('Z') + Yii::app()->user->userTimezoneOffset);
            $date = date("Y-m-d H:i:s", strtotime($w9->Signature_Date));
            echo "<br>Full format signature date full format <br>  ".$date."<br>";

            $date = date('Z');
            echo "<br>Timezone offset offset from GMT ".$date."<br>";

            $date = Yii::app()->user->userTimezoneOffset;
            echo "<br>User Timezone offset app()->user->userTimezoneOffset  ".$date."<br>";

            $date = date("Y-m-d H:i:s", strtotime($w9->Signature_Date) + date('Z') - Yii::app()->user->userTimezoneOffset);
            echo "<br>User +date(Z) - Yii:zoneofset  ".$date."<br>";
            */
            ?>
            <label for="W9_Signature_Date"><span class="red">*</span> Signature Date</label>
            <?php echo $form->textField($w9,'Signature_Date', $htmlOptions); ?>
            <?php echo $form->error($w9,'Signature_Date'); ?>
            <?php
                if (!isset($w9['_errors']["Signature_Date"])) {
                    echo '<div class="errorMessage grey">Correct date format: mm/dd/yyyy!</div>';
                }
            ?>
        </div>
        <div class="group">
            <?php
            $htmlOptions = array();
            if ($w9->Verified == 1 && Yii::app()->user->userType != Users::DB_ADMIN) {
                $htmlOptions = array('disabled'=>'disabled');
            }
            ?>
            <label for="W9_Revision_ID"><span class="red">*</span> Form Revision</label>
            <?php echo $form->dropDownList($w9,'Revision_ID', array('0' => 'Unknown Revision') + $w9Revs, $htmlOptions); ?>
            <?php echo $form->error($w9,'Revision_ID'); ?>

            <div id="sample_revision_link" style="display: inline;float: right;font-size: 15px;margin-right: 10px;text-align: right; " > <a  href="#">Example</a></div>

        </div>
        <?php echo $form->hiddenField($w9,'Verified',array('id'=>'w9_verified_value')); ?>
        <?php echo $form->hiddenField($w9,'W9_ID'); ?>
        <input type="hidden" value="true" name="w9_data_entry_form_values">
        <input type="hidden" name="company_business_name" id="company_business_name">
        <?php
            if ($w9->Verified != 1 || Yii::app()->user->userType == Users::DB_ADMIN) {
                ?>
                <div class="center">
                    <?php echo CHtml::submitButton('Save',array('class'=>'button')); ?>
                </div>


            <?php
            }
        ?>
    </fieldset>
    <?php $this->endWidget(); ?>

    <div class="delimiter" style="height: 30px;"></div>
    <?if ( in_array(Yii::app()->user->id,array('admin','db_admin')) ) {
        $this->renderPartial("application.views.widgets.document_details",array('document'=> $document)); //document details widget
    }?>


<?php } else {?>
    W9s weren't found.
<?php } ?>
</div>
<div id="data_entry_right">
    <input id="fileupload" type="file" name="files[]" style="display: none;" >
    <script src="<?php echo Yii::app()->request->baseUrl.'/js/jupload/vendor/jquery.ui.widget.js'?>" > </script>
    <script src="<?php echo Yii::app()->request->baseUrl.'/js/jupload/jquery.fileupload.js'?>" > </script>


    <?php if ($num_pages > 0) {?>
        <div id="tab1_block">
            <div class="w9_detail_block_bar">
                <div class="image_buttons left">
                    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/fullsize.jpg" alt=""  class="w9_detail_block_bar_button fullsize_button"/>
                    <?php
                    if (strpos($file->Mime_Type, 'pdf') === false) {
                        echo '<img src="' . Yii::app()->request->baseUrl . '/images/minlup.jpg" alt=""  class="w9_detail_block_bar_button minlup_button"/>
                              <img src="' . Yii::app()->request->baseUrl . '/images/pluslup.jpg" alt="" class="w9_detail_block_bar_button pluslup_button"/>';
                    }


                    ?>
                </div>
                <?php
                //Buttons modification widget
                $this->renderPartial("application.views.filemodification.buttons",array(
                    'buttons' => array('rotate_cw','rotate_ccw','delete','reupload'),
                    'docId'  => $document->Document_ID,
                ));
                //end of widget
                ?>
            </div>
            <div id="embeded_pdf">
                <?php $this->widget('application.components.ShowPdfWidget', array(
                    'params' => array(
                        'doc_id'=>intval($document->Document_ID),
                        'mime_type'=>$file->Mime_Type,
                        'approved'=>$approved,
                        'mode'=>Helper::isMobileComplexCheck()? 5 : 3
                    ),
                )); ?>
            </div>

        </div>
    <?php }

    $this->renderPartial('//widgets/image_view_block');

    ?>
</div>
</div>



<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/data_entry_detail.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/document_view.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/asa_datepicker.js"></script>

<?php
Yii::app()->clientScript->registerScriptFile('/js/mousewheel.js', CClientScript::POS_HEAD);
?>
<script type="text/javascript">
    $(document).ready(function() {
        new DataEntryDetail;



        $('#W9_Signature_Date').datepicker({
            dateFormat: "mm/dd/yy",
            maxDate: new Date,
            onClose: function(selectedDate){
                //check if the year part of the date correct
                var arr = selectedDate.split('/');
                if(isNaN(arr[2])){
                    //if not correct changing it 2xxx format
                    arr[2]=arr[2].replace(/_/g, '');
                    var res =parseInt(arr[2],10);
                    if( res<2000 ) { res=res+2000; arr[2]=res;}
                    var new_date=arr[0]+'/'+arr[1]+'/'+arr[2];
                    $(this).datepicker( "setDate", new_date );
                }
            }
        });


        $("#W9_Signature_Date").mask("99/99/9999");
        $("#W9_Signature_Date").blur(function() {
            $("#W9_Revision_ID").focus();
        });


        $('#company_business_name').val($('#Companies_Business_NameW9').val());

        $('#Companies_Business_NameW9').change(function() {
            $('#company_business_name').val($('#Companies_Business_NameW9').val());
        });
    });
</script>