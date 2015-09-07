<?php
/* @var $this VendorController */

$this->breadcrumbs=array(
    'AP Approval List'=>array('/ap'),
    'Detail',
);
?>
<h1>AP Approval Detail: <?=@CHtml::encode(Yii::app()->user->userLogin);?><span class="right items_to_review">AP to review: <?php echo $num_pages; ?> items</span></h1>

<div class="account_manage">
    <div class="account_header_left left">
        <?php if ($mustBeApproved) { ?>
        <button class="button" id="approve_ap" data="<?php echo $document->Document_ID; ?>">Approve</button>
        <button <?php echo ($userApprovalRange['prev_user_appr_val'] > 1) ? 'class="button" id="return_document" data="'. $document->Document_ID . '"' : 'class="not_active_button"' ; ?>>Return</button>
        <?php } ?>
        <?php
        if (strpos($file->Mime_Type, 'pdf') === false) {
            echo '<button class="button" id="print_document" data="' .  $document->Document_ID . '">Print</button>';
        }

        if ($enableEditing) {
            echo '<button class="button" id="open_ap_details_box" data="' .  $document->Document_ID . '">Edit Info</button>';
        }
        if (Documents::hasDeletePermission($document->Document_ID,Documents::AP,Yii::app()->user->userID,Yii::app()->user->clientID)) {
            echo '<button class="button" id="delete_document" data-href="' . Yii::app()->createUrl('documents/deletedocument', array('doc_for_delete'=>$document->Document_ID) ). '">Delete</button>';
        }
        ?>
    </div>
    <?php $this->renderPartial('application.views.widgets.pages_navigation', array(
        'page' => $page,
        'num_pages' => $num_pages,
        'url' => '/ap/detail',
    )); ?>
</div>
<?php if(Yii::app()->user->hasFlash('success')):?>
    <div class="info">
        <button class="close-alert">&times;</button>
        <?php echo Yii::app()->user->getFlash('success'); ?>
    </div>
<?php endif; ?>
<div class="wrapper">
    <div class="w9_details">
        <span id="doc_id" data-id="<?php echo $ap->AP_ID; ?>"></span>
        <span class="right created_on">Created on: <span class="created_on_date"><?php echo Helper::convertDateString($document->Created) . ' ' . CHtml::encode($user->person->First_Name) . ' ' . CHtml::encode($user->person->Last_Name); ?></span></span>
        <h2><?php echo isset($company->Company_Name) ? $company->Company_Name : '<span class="not_set">Vendor not attached</span>'; ?></h2>
        <table class="details_table">
            <tr>
                <td class="width240">
                    <ul class="details_table_list">
                        <li>Amount: <span class="details_page_value"><?php echo $ap->Invoice_Amount ? CHtml::encode(number_format($ap->Invoice_Amount, 2,'.', ',')) : '<span class="not_set">Not set</span>'; ?></span></li>
                        <li>Date: <span class="details_page_value"><?php echo $ap->Invoice_Date ? CHtml::encode(Helper::convertDate($ap->Invoice_Date)) : '<span class="not_set">Not set</span>'; ?></span></li>
                        <li>Due Date: <span class="details_page_value"><?php echo $ap->Invoice_Due_Date ? CHtml::encode(Helper::convertDate($ap->Invoice_Due_Date)) : '<span class="not_set">Not set</span>'; ?></span></li>
                    </ul>
                </td>
                <td>
                    <ul class="details_table_list">
                        <li>Invoice Number: <span class="details_page_value"><?php echo $ap->Invoice_Number ? CHtml::encode($ap->Invoice_Number) : '<span class="not_set">Not set</span>'; ?></span></li>
                        <li>Vendor ID: <span class="details_page_value"><?php echo $ap->Vendor_ID ? $ap->Vendor_ID : '<span class="not_set">Not set</span>'; ?></span></li>
                        <li>Check Number: <span class="details_page_value"><?php echo $paymentCheckNumber ? CHtml::encode($paymentCheckNumber) : '<span class="not_set">Not set</span>'; ?></span></li>
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
                'title'=>'Payable View',
                'view'=>'tabs/ap_view',
                'data'=>array('file' => $file, 'document' => $document),
            ),
            'tab2'=>array(
                'title'=>'Backup View',
                'view'=>'tabs/detail_back_up',
                'data'=>array('backUp'=>$backUp),
            ),
            'tab3'=>array(
                'title'=>'Check View',
                'view'=>'tabs/check_view',
                'data'=>array('check'=>$check),
            ),
        ),
    ));
    ?>

</div>
<div class="sidebar_right">
    <div class="sidebar_item approval_progress">
        <span class="center">Approval Progress</span>
        <br/>
        <div id="progress_bar">
            <div id="progress_line" style="width: 0%" data-width="<?php echo $ap->AP_Approval_Value ?>">

            </div>
        </div>
    </div>
    <div class="sidebar_item" id="notes_blok">
        <span>Add an Internal Note:</span>
        <textarea name="" class="note_textarea" maxlength="250"></textarea>
        <input type="hidden" name="note_to_document" id="note_to_document" value="<?php echo $document->Document_ID; ?>"/>
        <button class="button block-center" id="add_note_button">Add Note</button>
        <div id="notes_list">
            <?php
            foreach ($notes as $note) {
                echo '<div class="note_item">
                            <p class="note_date">' . Helper::convertDateString($note->Created) . '</p>
                            <p class="note_title">' . CHtml::encode($note->user->person->First_Name) . ' ' . CHtml::encode($note->user->person->Last_Name) . '</p>
                            <p class="note_body">' . CHtml::encode($note->Comment) . '</p>
                          </div>';
            }
            ?>
        </div>
    </div>
    <div id="sidebar_dists_block" class="sidebar_item OCR_Layer" style="height: 469px; <?php echo $enableEditing ? ' cursor: pointer;' : ''; ?>">
        <span class="sidebar_block_header">Details / Distribution:</span>
        <?php $this->renderPartial('application.views.ap.dists_list', array('dists' => $ap->dists)); ?>
    </div>
</div>
<?php
    if ($enableEditing) {
        $this->renderPartial('application.views.widgets.editapinfo', array(
            'ap' => $editAp,
            'vendorsCP' => $vendorsCP,
            'relatedPONumber' => $relatedPONumber,
            'relatedPOError' => $relatedPOError,
        ));
    }
?>
<?php
if ($enableEditing) {
    $this->renderPartial('application.views.widgets.ap_dists', array('dists' => $dists, 'invalidDistsSum' => $invalidDistsSum));
}
?>
<script>
    $(document).ready(function() {
        <?php
            if ($show_ap_detail_box) {
                echo "setTimeout(function() {
                         show_modal_box('#edit_ap_info', 260, 50);
                      }, 200);";
            }
        ?>

        <?php
            if ($show_dists_box) {
                echo "setTimeout(function() {
                         show_modal_box('#ap_dists_modal', 295);
                      }, 200);";
            }
        ?>

        setEqualHeight($(".wrapper,.sidebar_right"));
        new APDetail('<?php echo $enableEditing ? 'enable' : 'disable'; ?>');
        <?php echo (!$backUp['document']) ? "new BuUpload('ap');" : ''; ?>
    });
</script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/ajaxupload.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/detail_page.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/ap_detail.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/document_view.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/bu_upload.js"></script>
<?php
Yii::app()->clientScript->registerScriptFile('/js/mousewheel.js', CClientScript::POS_HEAD);
?>