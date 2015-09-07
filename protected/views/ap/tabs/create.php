<?php $form=$this->beginWidget('CActiveForm', array (
    'id'=>'po_creating_form',
));

if ($apId == 0) {
    ?>
    <p class="po_create_status">Creating new CKRQ</p>
<?php
} else {
    ?>
    <p class="po_create_status">Editing CKRQ #<?php echo $ap->Invoice_Number; ?></p>
<?php
}
?>
<div class="left po_creating_top_left">
    <div style="margin-left: 230px;position: relative; top:-35px;left: 355px;"> <a href="#" class="preview_pdf"> Preview </a> </div>
    <fieldset>
        <div class="group">
            <?php echo $form->label($ap,'Vendor_ID'); ?>
            <?php  echo $form->dropDownList($ap,'Vendor_ID', array('0' => 'Unknown Vendor') + $vendors); ?>
            <?php echo $form->error($ap,'Vendor_ID'); ?>
        </div>
    </fieldset>
    <div id="po_vendor_info_block">
        <?php $this->renderPartial('application.views.ap.vendor_info_block', array(
            'currentVendor' => $currentVendor,
            'vendorAdmin' => $vendorAdmin,
        )); ?>
    </div>

    <div style="margin-left: 230px;position: relative; top:-10px;"> <a href="#" class="add_new_vendor"> Add new vendor </a> </div>
    <input id="fileupload_add_block" type="file" name="files[]" style="display: none;">
    <script src="<?php echo Yii::app()->request->baseUrl.'/js/jupload/vendor/jquery.ui.widget.js'?>" > </script>
    <script src="<?php echo Yii::app()->request->baseUrl.'/js/jupload/jquery.fileupload.js'?>" > </script>

</div>
<div class="right po_creating_top_right">
    <fieldset>
        <div class="group">
            <label>Number</label>
            <span class="limited_width"><?php echo $ap->Invoice_Number; ?></span>
        </div>
        <div class="group">
            <label>Date</label>
            <span class="limited_width"><?php echo Helper::convertDateSimple($ap->Invoice_Date); ?></span>
        </div>
        <div class="group">
            <?php
            // convert date string to view format
            if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $ap->Invoice_Due_Date)) {
                $ap->Invoice_Due_Date = Helper::convertDateSimple($ap->Invoice_Due_Date);
            }
            ?>
            <label for="Aps_Invoice_Due_Date">Check Due Date</label>
            <?php echo $form->textField($ap,'Invoice_Due_Date'); ?>
            <?php echo $form->error($ap,'Invoice_Due_Date'); ?>
        </div>
        <div class="group">
            <label for="Aps_Invoice_Amount">AMOUNT</label>
            <?php echo $form->textField($ap,'Invoice_Amount', array('class' => 'float_type')); ?>
            <?php echo $form->error($ap,'Invoice_Amount'); ?>
        </div>
        <div class="group" id="1099type" <?php echo $ap->Detail_1099_Box_Number > 0 ? "" : 'style="display: none;"' ?>>
            <label for="Aps_Detail_1099_Box_Number">1099 Type</label>
            <?php echo $form->dropDownList($ap,'Detail_1099_Box_Number', array('0' => 'Unknown 1099 Type') + array_combine(range(1,18), range(1,18))); ?>
            <?php echo $form->error($ap,'Detail_1099_Box_Number'); ?>
        </div>
        <br/>
        <div class="group_radio">
            <input type="radio" <?php echo $ckReqDet->CK_Req_Purchase == 1 ? 'checked="checked"' : ''; ?>  name="purchase_rental" id="purchase_rental_p" value="0" /> <label for="purchase_rental_p">Purchase</label>
            <input type="radio" <?php echo $ckReqDet->CK_Req_Purchase == 0 ? 'checked="checked"' : ''; ?> name="purchase_rental" id="purchase_rental_r" value="1" /> <label for="purchase_rental_r">Rental</label>
        </div>
        <div class="group">
            <?php
                $options = array();
                if ($ckReqDet->CK_Req_Purchase == 1) {
                    $options['disabled'] = 'disabled';
                }

            // convert date string to view format
            if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $ckReqDet->Rental_Begin)) {
                $ckReqDet->Rental_Begin = Helper::convertDate($ckReqDet->Rental_Begin);
            }
            ?>
            <label for="CkReqDetails_Rental_Begin">Rental Begins</label>
            <?php echo $form->textField($ckReqDet,'Rental_Begin', $options); ?>
            <?php echo $form->error($ckReqDet,'Rental_Begin'); ?>
        </div>
        <div class="group">
            <?php
            // convert date string to view format
            if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $ckReqDet->Rental_End)) {
                $ckReqDet->Rental_End = Helper::convertDate($ckReqDet->Rental_End);
            }
            ?>
            <label for="CkReqDetails_Rental_End">Rental Ends</label>
            <?php echo $form->textField($ckReqDet,'Rental_End', $options); ?>
            <?php echo $form->error($ckReqDet,'Rental_End'); ?>
        </div>
    </fieldset>
</div>
    <div class="left po_creating_top_left">

    </div>
    <div class="right po_creating_top_right">
        <!--
        <div class="group_radio">
            <input type="radio" checked="checked" name="mail_act" id="mail_act_check" value="0"> <label for="mail_act_check">Mail Check</label>
            <input type="radio" name="mail_act" id="mail_act_hold" value="1"> <label for="mail_act_hold">Hold for Pick Up</label>
        </div>
        <div class="group">
            <span class="mail_act_h">Mail to address: (if different than above)</span>
            <div class="clear"></div>
        </div>
        <div class="group">
            <input id="mail_act_i" type="text" />
        </div>
        -->
    </div>
<div class="clear"></div>
    <div>
        <div class="left po_creating_top_left">
            <div class="ap_description_block">
                Description:<br />
                <?php echo $form->textArea($ap,'Invoice_Reference', array('maxlength' => 500)); ?>
                <?php echo $form->error($ap,'Invoice_Reference'); ?>
            </div>
        </div>
        <div class="clear"></div>
    </div>
<div>
    <div class="left po_creating_top_left">
        <div class="coa_row" id="COA_Allow_Manual_Coding" style="display: none" data-id="<?=$coaStructure->COA_Allow_Manual_Coding?>"></div>
        <div class="ap_distribution_block">
            Distribution:
            <table class="scroll_table_head scroll_table_head_dists_ap center">
                <thead>
                <tr>
                    <th class="width120">
                        GL / Prod
                    </th>
                    <th class="width60">
                        Amount
                    </th>
                    <th class="width90">
                        Description
                    </th>
                </tr>
                </thead>
            </table>
            <div id="ap_dists_block">
                <table id="po_dists">
                    <tbody>
                    <?php
                    foreach($dists as $key => $dist) {
                        $dist['GL_Dist_Detail_Desc'] = ($dist['GL_Dist_Detail_Desc'] == '-') ? '' : $dist['GL_Dist_Detail_Desc'];
                        echo '<tr>
                                  <td class="width120">
                                      <span><input type="text" class="GL_Code" data-short-hand="'.$dist['Short_Hand'].'" title="' . $dist['GL_Dist_Detail_COA_Acct_Number'] . '" value="' . $dist['GL_Dist_Detail_COA_Acct_Number'] . '" name="GlDistDetails[' . $key . '][GL_Dist_Detail_COA_Acct_Number]"></span>
                                      <input type="hidden" class="short_hand"  value="' . $dist['Short_Hand'] . '" name="GlDistDetails[' . $key . '][Short_Hand]">
                                  </td>
                                  <td class="width65">
                                       <span><input type="text" class="float_type dists_amounts gl_amount" value="' . $dist['GL_Dist_Detail_Amt'] . '" name="GlDistDetails[' . $key . '][GL_Dist_Detail_Amt]" ></span>
                                  </td>
                                  <td>
                                       <span><input type="text" value="' . $dist['GL_Dist_Detail_Desc'] . '" name="GlDistDetails[' . $key . '][GL_Dist_Detail_Desc]" class="dist_descriptions gl_descript" maxlength="125"></span>
                                  </td>
                             </tr>';
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row_control_buttons">
            <span id="add_dist" class="add_row">+add item</span>
            <span id="remove_dist" class="remove_row">-remove item</span>
        </div>
        <div class="errorMessage poDistErrorMessage"><?php echo $distsError; ?></div>
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
</div>
<div class="clear"></div>
<?php $this->endWidget(); ?>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/asa_datepicker.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/dataentry_dists_autocomplete.js"></script>
<script>
    $(document).ready(function() {
         new DistsHandling;
    });
</script>
