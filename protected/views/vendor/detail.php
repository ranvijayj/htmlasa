<?php
/* @var $this VendorController */

$this->breadcrumbs=array(
    'Vendor List'=>array('/vendor'),
    'Detail',
);
?>
<h1>Vendor Detail: <?=@CHtml::encode(Yii::app()->user->userLogin);?><span class="right items_to_review">Vendors to review: <?php echo $num_pages; ?> items</span></h1>

<div class="account_manage">
    <div class="account_header_left left">
        <?php if ($file) { ?>
        <button class="button" id="send_document_by_fax" data="<?php echo $client->Client_ID; ?>">Fax</button>
        <button class="button" id="send_document_by_email" data="<?php echo $client->Client_ID; ?>">Email</button>
        <?php
        if (strpos($file->Mime_Type, 'pdf')) {

        } else {
            echo '<button class="button" id="print_vendor_document" data="' .  $client->Client_ID . '">Print</button>';
        }

            if ($w9->Access_Type == 1 || $fed_ids_match ) {
                echo '<button class="button" id="share_document" data-id="' . $w9->W9_ID . '">Share</button>';
            }
        }
        ?>
    </div>
    <?php $this->renderPartial('application.views.widgets.pages_navigation', array(
        'page' => $page,
        'num_pages' => $num_pages,
        'url' => '/vendor/detail',
    )); ?>
</div>
<?php if(Yii::app()->user->hasFlash('success')):?>
    <div class="info">
        <button class="close-alert">&times;</button>
        <?php echo Yii::app()->user->getFlash('success'); ?>
    </div>
<?php endif; ?>

<div class="wrapper">

    <div class="w9_details" style="<?= $enable_editing_w9_form ? 'cursor :pointer;' : '';?>">
        <span class="right created_on">Created on: <span class="created_on_date"><?php echo $lastDocument ? Helper::convertDateString($lastDocument->Created) : ''; ?></span></span>
        <h2><?php
            $str = $company->Company_Name . ($vendor->Vendor_ID_Shortcut ? '('.CHtml::encode($vendor->Vendor_ID_Shortcut).')' : '');
            echo wordwrap(CHtml::encode($str),40, "\n", true);;
            ?>
        </h2>
        <input type="hidden" value="<?=$w9->Document_ID?>" id="w9_doc_id">
        <input type="hidden" value="<?=$company->Company_ID?>" id="company_id">
        <input type="hidden" value="<?=$vendor->Vendor_ID?>" id="vendor_id">
        <input type="hidden" value="<?=$adminPerson->Person_ID?>" id="cli_adm_user_id">

        <table class="details_table">
            <tr>
                <td class="width240">
                    <ul class="details_table_list">
                        <li>Fed ID: <span class="details_page_value"><?php echo $company->Company_Fed_ID; ?></span></li>
                        <li>Addre. 1: <span class="details_page_value"><?php echo $adress->Address1 ? CHtml::encode($adress->Address1) : '<span class="not_set">Not set</span>'; ?></span></li>
                        <li>Addre. 2: <span class="details_page_value"><?php echo $adress->Address2 ? CHtml::encode($adress->Address2) : '<span class="not_set">Not set</span>'; ?></span></li>
                    </ul>
                </td>
                <td>
                    <ul class="details_table_list">
                        <li>City/St/Zip: <span class="details_page_value"><?php echo Helper::createAddressLine('', $adress->City, $adress->State, $adress->ZIP); ?></span></li>
                        <li>Country: <span class="details_page_value"><?php echo $adress->Country ? CHtml::encode($adress->Country) : '<span class="not_set">Not set</span>'; ?></span></li>
                        <li>Contact - Phone: <span class="details_page_value"><?php echo isset($adminPerson->First_Name) ? (CHtml::encode($adminPerson->First_Name . ' ' . $adminPerson->Last_Name)) : '<span class="not_set">Not set</span>'; if (isset($adminPerson->Direct_Phone) && $adminPerson->Direct_Phone) echo ', ' . CHtml::encode($adminPerson->Direct_Phone);?></span></li>
                    </ul>
                </td>
            </tr>
        </table>
    </div>


    <?php
    $tab_css = '/css/jquery.yiitab.css';
    if (Helper::checkIE() || Helper::getMobileDetect()->isMobile() || Helper::getMobileDetect()->isTablet() || Helper::checkMobile()) {
        $tab_css = '/css/jquery.yiitabie.css';
    }
    $this->widget('CTabView', array(
        'activeTab'=>'tab1',
        'cssFile'=>$tab_css,
        'tabs'=>array(
            'tab1'=>array(
                'title'=>'Image View',
                'view'=>'tabs/image',
                'data'=>array('file' => $file, 'document' => $lastDocument,'company' => $company),
            ),
            'tab2'=>array(
                'title'=>'Image View 2',
                'view'=>'tabs/tab_2',
                'data'=>array('file' => $file2, 'document' => $lastDocument2),
            ),
            'tab3'=>array(
                'title'=>'Image View 3',
                'view'=>'tabs/tab_3',
                'data'=>array('file' => $file3, 'document' => $lastDocument3),
            ),
        ),
    ));
    ?>

</div>
<div class="sidebar_right">
    <div class="sidebar_item sidebar_verified">
        <span class="center sidebar_block_header">Document Verified</span>
        <br/>
        <?php if (isset($w9->Verified) && $w9->Verified == 1) {
            echo '<p class="center"><img src="' . Yii::app()->request->baseUrl . '/images/verified_doc.jpg" alt=""/></p>';
        } else if (isset($w9->Verified)) {
            echo '<p class="center"><img src="' . Yii::app()->request->baseUrl . '/images/notverified_doc.jpg" alt=""/></p>';
        }
        ?>
    </div>
    <div class="sidebar_item" id="notes_blok">
        <span>Add an Internal Note:</span>
        <textarea name="" class="note_textarea" maxlength="255"></textarea>
        <input type="hidden" name="note_to_vendor" id="note_to_vendor" value="<?php echo $vendor->Vendor_ID; ?>"/>
        <button class="button block-center" id="add_note_button">Add Note</button>
        <div id="notes_list">
            <?php
                if (trim($vendor->Vendor_Note_General) != '') {
                    echo '<div class="note_item">
                             <p class="note_body">' . CHtml::encode($vendor->Vendor_Note_General) . '</p>
                          </div>';
                }
            ?>
        </div>
    </div>
    <div class="sidebar_item" id="vendor_info_block">
        <span class="sidebar_block_header">Vendor Detail:</span>
        <ul>
            <li>Vendor Shortcut: <span class="details_page_value"><?php echo $vendor->Vendor_ID_Shortcut ? CHtml::encode($vendor->Vendor_ID_Shortcut) : '<span class="not_set">Not set</span>'; ?></span></li>
            <li>Vendor Contact: <span class="details_page_value"><?php echo $vendor->Vendor_Contact ? CHtml::encode($vendor->Vendor_Contact) : '<span class="not_set">Not set</span>'; ?></span></li>
            <li>Vendor Phone: <span class="details_page_value"><?php echo $vendor->Vendor_Phone ? CHtml::encode($vendor->Vendor_Phone) : '<span class="not_set">Not set</span>'; ?></span></li>
            <li>Check Print: <span class="details_page_value"><?php echo $vendor->Vendor_Name_Checkprint ? CHtml::encode($vendor->Vendor_Name_Checkprint) : '<span class="not_set">Not set</span>'; ?></span></li>
            <li>Check Print Address 1: <span class="details_page_value"><?php echo $vendor->Vendor_Checkprint_Add1 ? CHtml::encode($vendor->Vendor_Checkprint_Add1) : '<span class="not_set">Not set</span>'; ?></span></li>
            <li>Check Print Address 2:  <span class="details_page_value"><?php echo $vendor->Vendor_Checkprint_Add2 ? CHtml::encode($vendor->Vendor_Checkprint_Add2) : '<span class="not_set">Not set</span>'; ?></span></li>
            <li>Check Print: <span class="details_page_value"><?php echo $vendor->Vendor_Checkprint_City ? CHtml::encode($vendor->Vendor_Checkprint_City) : '<span class="not_set">Not set</span>'; ?></span></li>
            <li>Check Print: <span class="details_page_value"><?php echo $vendor->Vendor_Checkprint_ST ? CHtml::encode($vendor->Vendor_Checkprint_ST) : '<span class="not_set">Not set</span>'; ?></span></li>
            <li>Check Print: <span class="details_page_value"><?php echo $vendor->Vendor_Checkprint_Zip ? CHtml::encode($vendor->Vendor_Checkprint_Zip) : '<span class="not_set">Not set</span>'; ?></span></li>
            <li>Check Print: <span class="details_page_value"><?php echo $vendor->Vendor_Checkprint_Country ? CHtml::encode($vendor->Vendor_Checkprint_Country) : '<span class="not_set">Not set</span>'; ?></span></li>
            <li>1099 Vendor: <span class="details_page_value"><?php echo ($vendor->Vendor_1099 == 0) ? 'No' : (($vendor->Vendor_1099 == 1) ? 'Yes' : ''); ?></span></li>
            <li>Default GL Code: <span class="details_page_value"><?php echo $vendor->Vendor_Default_GL ? CHtml::encode($vendor->Vendor_Default_GL) : '<span class="not_set">Not set</span>'; ?></span></li>
            <li>Default GL Note: <span class="details_page_value"><?php echo $vendor->Vendor_Default_GL_Note ? CHtml::encode($vendor->Vendor_Default_GL_Note) : '<span class="not_set">Not set</span>'; ?></span></li>
        </ul>
    </div>
</div>
<?php
if (isset($this->edit_vendor_model) && $this->edit_vendor_model !== false) {
    $model = $this->edit_vendor_model;
} else {
    $model = $vendor;
}

?>
<?php $this->renderPartial('application.views.widgets.editvendorinfo', array('model' => $model, 'page' => $page)); ?>
<script>
    $(document).ready(function() {
        <?php
            if ($show_edit_vendor_form) {
                echo "setTimeout(function() {
                         show_modal_box('#edit_vendor_info', 260, 50);
                      }, 200);";
            }
            if ($show_edit_company_form) {

            }
        ?>

        setEqualHeight($(".wrapper,.sidebar_right"));
        new VendorDetail;
        new VendorReUpload('detail');



    });
</script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/detail_page.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/w9_detail.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/vendor_detail.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/document_view.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/vendor_reupload.js"></script>
<?php
Yii::app()->clientScript->registerScriptFile('/js/mousewheel.js', CClientScript::POS_HEAD);
?>
<?php $this->renderPartial('application.views.widgets.share_w9', array(
    'companiesToShareW9' => $companiesToShareW9,
    'w9' => $w9,
    'fed_ids_match'=>$fed_ids_match,
    'user_settings'=>$user_settings
)); ?>
<div id="dataentry_block_ext" style="display: none;background-color: #ffffff;padding: 40px;">
</div>