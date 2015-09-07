<img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton" style="right: -15px;"/>

<div id="data_entry_center" style="background-color: #ffffff;overflow: auto;height: auto;z-index: 120;">


    <?php $form=$this->beginWidget('CActiveForm', array (
        'id'=>'po_creating_form',
    ));
    ?>

    <p class="po_create_status">Editing PO #<?php echo $po->PO_Number; ?></p>

    <div class="left po_creating_top_left">
        <!--<div style="margin-left: 230px;position: relative; top:-35px;left: 355px;"> <a href="#" class="preview_pdf"> Preview </a> </div>-->
        <fieldset>
            <div class="group">
                <?php echo $form->label($po,'Vendor_ID'); ?>
                <?php  echo $form->dropDownList($po,'Vendor_ID', array('0' => 'Unknown Vendor') + $vendors); ?>
                <?php echo $form->error($po,'Vendor_ID'); ?>
            </div>
        </fieldset>
        <div id="po_vendor_info_block">
            <?php $this->renderPartial('application.views.po.po_vendor_info_block', array(
                'currentVendor' => $vendor,
                'vendorAdmin' => $vendorAdmin,
            ));
            ?>
        </div>

        <!--<div style="margin-left: 230px;position: relative; top:-10px;"> <a href="#" class="add_new_vendor"> Add new vendor </a> </div>
        <input id="fileupload_add_block" type="file" name="files[]" style="display: none;">-->
        <script src="<?php echo Yii::app()->request->baseUrl.'/js/jupload/vendor/jquery.ui.widget.js'?>" > </script>
        <script src="<?php echo Yii::app()->request->baseUrl.'/js/jupload/jquery.fileupload.js'?>" > </script>


    </div>
    <div class="right po_creating_top_right">
        <fieldset>
            <div class="group">
                <label>Number:</label>
                <span class="limited_width"><?php echo $po->PO_Number; ?></span>
            </div>
            <div class="group">
                <label>Date:</label>
                <span class="limited_width"><?php echo Helper::convertDateSimple($po->PO_Date); ?></span>
            </div>
            <div class="group">
                <label>Job Name:</label>
                <span class="limited_width"><?php echo isset($poFormatting->PO_Format_Job_Name) ? CHtml::encode($poFormatting->PO_Format_Job_Name) : ''; ?></span>
            </div>
            <div class="group">
                <?php echo $form->label($po,'PO_Account_Number'); ?>
                <?php echo $form->textField($po,'PO_Account_Number'); ?>
                <?php echo $form->error($po,'PO_Account_Number'); ?>
            </div>
            <div class="group">
                <?php echo $form->label($po,'Payment_Type'); ?>
                <?php echo $form->dropDownList($po,'Payment_Type', array('0' => 'Unknown Payment Type') + $this->paymentTypes); ?>
                <?php echo $form->error($po,'Payment_Type'); ?>
            </div>
            <div class="group">
                <?php
                $htmlOptions = array();
                if ($po->Payment_Type != 'CC') {
                    $htmlOptions['disabled'] = 'disabled';
                }
                ?>
                <?php echo $form->label($po,'PO_Card_Last_4_Digits'); ?>
                <?php echo $form->textField($po,'PO_Card_Last_4_Digits', $htmlOptions); ?>
                <?php echo $form->error($po,'PO_Card_Last_4_Digits'); ?>
            </div>
        </fieldset>
    </div>
    <div class="clear"></div>
    <div>
        <table class="scroll_table_head center position_center">
            <thead>
            <tr>
                <th class="width60">
                    Qty
                </th>
                <th class="width240">
                    Description
                </th>
                <th class="width55">
                    Purchase
                </th>
                <th class="width55">
                    Rental
                </th>
                <th class="width65">
                    Line #
                </th>
                <th class="width65">
                    Amount
                </th>
            </tr>
            </thead>
        </table>
        <div id="po_descr_details_block">
            <table id="po_descr_details">
                <tbody>
                <?php
                foreach($descDetails as $key => $descDetail) {
                    $descDetail['PO_Desc_Desc'] = ($descDetail['PO_Desc_Desc'] == '-') ? '' : $descDetail['PO_Desc_Desc'];
                    echo '<tr>
                                  <td class="width60">
                                      <input type="text" value="' . $descDetail['PO_Desc_Qty'] . '" name="PoDescDetail[' . $key . '][PO_Desc_Qty]" class="qty_cell">

                                  </td>
                                  <td class="width240">
                                      <input type="text" value="' . $descDetail['PO_Desc_Desc'] . '" name="PoDescDetail[' . $key . '][PO_Desc_Desc]" maxlength="255" class="po_det_descriptions">
                                  </td>
                                  <td class="width60">
                                      <input type="radio" value="0" name="PoDescDetail[' . $key . '][PO_Desc_Purchase_Rental]" ' . (($descDetail['PO_Desc_Purchase_Rental'] == 0) ? 'checked="checked"' : '') . '>

                                  </td>
                                  <td class="width55">
                                      <input type="radio" value="1" name="PoDescDetail[' . $key . '][PO_Desc_Purchase_Rental]" ' . (($descDetail['PO_Desc_Purchase_Rental'] == 1) ? 'checked="checked"' : '') . '>

                                  </td>
                                  <td class="width55">
                                      <input type="text" value="' . $descDetail['PO_Desc_Budget_Line_Num'] . '" name="PoDescDetail[' . $key . '][PO_Desc_Budget_Line_Num]" maxlength="20">
                                  </td>
                                  <td>
                                      <input type="text" value="' . $descDetail['PO_Desc_Amount'] . '" name="PoDescDetail[' . $key . '][PO_Desc_Amount]" class="dollar_fields base_fields">
                                  </td>
                       </tr>';
                }
                ?>
                </tbody>
            </table>
        </div>
        <div class="left po_creating_top_left">
            <div class="row_control_buttons">
                <span id="add_desc_det" class="add_row">+add item</span>
                <span id="remove_desc_det" class="remove_row">-remove item</span>
            </div>
            <div class="errorMessage poDetailsErrorMessage"><?php echo $detailsError; ?></div>
            <div class="po_distribution_block">
                Distribution:
                <table class="scroll_table_head scroll_table_head_dists center">
                    <thead>
                    <tr>
                        <th class="width120">
                            GL Code
                        </th>
                        <th class="width50">
                            Amount
                        </th>
                        <th class="width190">
                            Desc
                        </th>
                    </tr>
                    </thead>
                </table>
                <div class="coa_row" id="COA_Allow_Manual_Coding" style="display: none" data-id="<?=$coaStructure->COA_Allow_Manual_Coding?>"></div>
                <div id="po_dists_block">
                    <table id="po_dists">
                        <tbody>
                        <?php
                        foreach($dists as $key => $dist) {
                            $dist['PO_Dists_Description'] = ($dist['PO_Dists_Description'] == '-') ? '' : $dist['PO_Dists_Description'];
                            echo '<tr>
                                  <td class="width120">
                                        <span><input type="text" class="GL_Code" data-short-hand="'.$dist['Short_Hand'].'" maxlength="63"  title="' . $dist['PO_Dists_GL_Code'] . '" value="' . $dist['PO_Dists_GL_Code'] .  '"  name="PoDists[' . $key . '][PO_Dists_GL_Code]"></span>
                                        <input type="hidden" class="short_hand"  value="' . $dist['Short_Hand'] . '" name="PoDists[' . $key . '][Short_Hand]">
                                  </td>
                                  <td class="width50">
                                       <span><input type="text" value="' . $dist['PO_Dists_Amount'] . '" name="PoDists[' . $key . '][PO_Dists_Amount]" class="float_type dists_amounts"></span>
                                  </td>
                                  <td>
                                       <span><input type="text" value="' . $dist['PO_Dists_Description'] . '" name="PoDists[' . $key . '][PO_Dists_Description]" maxlenght="125" class="dist_descriptions"></span>
                                  </td>
                             </tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row_control_buttons" style="margin-left: 55px;">
                <span id="add_dist_po" class="add_row">+add item</span>
                <span id="remove_dist" class="remove_row">-remove item</span>
            </div>
            <div class="errorMessage poDistErrorMessage"><?php echo $invalidDistsSum; ?></div>
        </div>
        <div class="right po_creating_right" id="total_fields">
            <fieldset>
                <div class="group">
                    <?php echo $form->label($po,'PO_Subtotal'); ?>
                    <?php echo $form->textField($po,'PO_Subtotal', array('readonly'=>true)); ?>
                    <?php echo $form->error($po,'PO_Subtotal'); ?>
                </div>
                <div class="group">
                    <?php echo $form->label($po,'PO_Tax'); ?>
                    <?php echo $form->textField($po,'PO_Tax', array('class'=>"dollar_fields base_fields")); ?>
                    <?php echo $form->error($po,'PO_Tax'); ?>
                </div>
                <div class="group">
                    <?php echo $form->label($po,'PO_Delivery_Chg'); ?>
                    <?php echo $form->textField($po,'PO_Delivery_Chg', array('class'=>"dollar_fields base_fields")); ?>
                    <?php echo $form->error($po,'PO_Delivery_Chg'); ?>
                </div>
                <div class="group">
                    <?php echo $form->label($po,'PO_Other_Chg'); ?>
                    <?php echo $form->textField($po,'PO_Other_Chg', array('class'=>"dollar_fields base_fields")); ?>
                    <?php echo $form->error($po,'PO_Other_Chg'); ?>
                </div>
                <div class="group">
                    <?php echo $form->label($po,'PO_Total'); ?>
                    <?php echo $form->textField($po,'PO_Total', array('readonly'=>true)); ?>
                    <?php echo $form->error($po,'PO_Total'); ?>
                </div>
            </fieldset>
        </div>
        <div class="clear"></div>
    </div>
    <div class="left po_creating_top_left"></div>
    <div class="right po_creating_right po_signers">
        <div class="group" style="width: 195px;">
            <? $fulname = $signRequestedByUser->person->First_Name . ' ' . $signRequestedByUser->person->Last_Name;?>
            <table cellspacing="0">
                <tr>
                    <td style="width: 80px; vertical-align: top;padding-top:4px;"><label>Requested by:</label></td>
                    <td style="width: 100px; vertical-align: top;padding-top:4px;"> <?= Helper::truncLongWordsToTable($fulname,10);?> </td>
                </tr>
            </table>
        </div>
        <!-- Approval block was removed 19.11.2014 according to ASAAP Phase II-2 Logic Call Out V007.pdf page 8  -->
    </div>
    <div class="clear"></div>

    <?php echo $form->hiddenField($po,'PO_ID'); ?>
    <?php echo $form->hiddenField($po,'PO_Date'); ?>
    <?php echo $form->hiddenField($po,'Document_ID'); ?>
    <input type="hidden" value="true" name="po_data_entry_form_values">
    <input type="hidden" value="<?=$return_url;?>" name="return_url" id="return_url">
    <?php $this->endWidget(); ?>

    <div class="center" style="margin-bottom: 20px;">
        <button class="button" id="save_po_create_form" >Save</button>
    </div>

</div>

    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/doc_create.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/progress_bar.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/po_create.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/file_uploading.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/document_view.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/vendor_w9_upload.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/dataentry_dists_autocomplete.js"></script>


    <script>
        $(document).ready(function() {
            new DistsHandling;
            new POCreate;
            new FileUploading('PO');
            new ProgressBar('PO');
            new VendorW9Upload;
        });
    </script>





