<?php
/* @var $this VendorController */

$this->breadcrumbs=array(
    'PO Approval List'=>array('/po'),
    'Detail',
);
?>
<h1 class="po_header">PO Approval Detail: <?=@CHtml::encode(Yii::app()->user->userLogin);?><span class="right" style="font-size: 15px; margin-right: 15px;">PO to review: <?php echo $num_pages; ?> items</span></h1>

<div class="account_manage">
    <div class="account_header_left left">
        <?php if ($mustBeApproved) { ?>
        <button class="button" id="approve_po" data="<?php echo $document->Document_ID; ?>">Approve</button>
        <button <?php echo ($userApprovalRange['prev_user_appr_val'] > 1) ? 'class="button" id="return_document" data="'. $document->Document_ID . '"' : 'class="not_active_button"' ; ?>>Return</button>
        <?php } ?>

        <? if ($hard_approval && !$mustBeApproved ) {?>
            <button class="button" id="hard_po_approve" data="<?php echo $document->Document_ID; ?>">Apprv./Skip</button>
        <?}?>

        <?php
        if($po->PO_Approval_Value ==100) {
            echo '<button class="button" id="send_document_by_email" data="'.$document->Document_ID.'">Email</button>';
        } else {


        }
        if ($enableEditing) {
            echo '<button class="button" id="open_ap_details_box" data="' .  $document->Document_ID . '">Edit Info</button>';
        }

        if (strpos($file->Mime_Type, 'pdf') === false) {
            //echo '<button class="button" id="print_document" data="' .  $document->Document_ID . '">Print</button>';
        }
        if (Documents::hasDeletePermission($document->Document_ID,Documents::PO,Yii::app()->user->userID,Yii::app()->user->clientID)) {
            echo '<button class="button" id="delete_document" data-href="' . Yii::app()->createUrl('documents/deletedocument', array('doc_for_delete'=>$document->Document_ID) ). '">Delete</button>';
        }

        ?>
    </div>
    <?php $this->renderPartial('application.views.widgets.pages_navigation', array(
        'page' => $page,
        'num_pages' => $num_pages,
        'url' => '/po/detail',
    )); ?>
</div>
<?php if(Yii::app()->user->hasFlash('success')):?>
    <div class="info">
        <button class="close-alert">&times;</button>
        <?php echo Yii::app()->user->getFlash('success'); ?>
    </div>
<?php endif; ?>
<div class="wrapper">
    <div class="w9_details" style="<?php echo $enableEditing ? ' cursor: pointer;' : ''; ?>">

        <span id="doc_id" data-id="<?php echo $po->PO_ID; ?>"></span>

        <!-- Important the next div data used in JS logic-->
        <div id="save_po" data="<?php echo $po->PO_ID; ?>"></div>
        <!-- End of Important-->

        <span class="right created_on">Created on: <span class="created_on_date"><?php echo Helper::convertDateString($document->Created) . ' ' . CHtml::encode($user->person->First_Name) . ' ' . CHtml::encode($user->person->Last_Name); ?></span></span>
        <h2><?php echo isset($company->Company_Name) ? Helper::cutText(25,400,45,$company->Company_Name) : '<span class="not_set">Vendor not attached</span>'; ?></h2>
        <table class="details_table">
            <tr>
                <td class="width240">
                    <ul class="details_table_list">
                        <li>Total Amount: <span class="details_page_value"><?php echo $po->PO_Total ? CHtml::encode(number_format($po->PO_Total, 2)) : '<span class="not_set">Not set</span>'; ?></span></li>
                        <li>Date: <span class="details_page_value"><?php echo $po->PO_Date ? CHtml::encode(Helper::convertDate($po->PO_Date)) : '<span class="not_set">Not set</span>'; ?></span></li>
                        <li>Account Number: <span class="details_page_value"><?php echo $po->PO_Account_Number ? CHtml::encode($po->PO_Account_Number) : '<span class="not_set">Not set</span>'; ?></span></li>
                    </ul>
                </td>
                <td>
                    <ul class="details_table_list">
                        <li>Number: <span class="details_page_value"><?php echo $po->PO_Number ? CHtml::encode($po->PO_Number) : '<span class="not_set">Not set</span>'; ?></span></li>
                        <li>Payment Type: <span class="details_page_value"><?php echo isset($this->paymentTypes[$po->Payment_Type]) ? $this->paymentTypes[$po->Payment_Type] . (($po->Payment_Type == 'CC') ? ' (' . $po->PO_Card_Last_4_Digits . ')' : '') : '<span class="not_set">Not set</span>'; ?></span></li>
                        <li>Job Name: <span class="details_page_value"><?php echo (isset($poFormatting->PO_Format_Job_Name) && $poFormatting->PO_Format_Job_Name) ? $poFormatting->PO_Format_Job_Name : '<span class="not_set">Not set</span>'; ?></span></li>
                    </ul>
                </td>
            </tr>
        </table>
    </div>


    <?php
    $tab_css = '/css/jquery.yiitab.css';
    $tab = 'tab1';
    if (Helper::checkIE() || Helper::getMobileDetect()->isMobile() || Helper::getMobileDetect()->isTablet() || Helper::checkMobile()) {
        $tab_css = '/css/jquery.yiitabie.css';
    }
    if (isset($_POST['PoPmtsTraking'])) {
        $tab = 'tab3';
    }
    $this->widget('CTabView', array(
        'activeTab'=>$tab,
        'cssFile'=>$tab_css,
        'tabs'=>array(
            'tab1'=>array(
                'title'=>'PO view',
                'view'=>'tabs/image',
                'data'=>array('file' => $file, 'document' => $document,'approved'=>$po->PO_Approved),
            ),
            'tab2'=>array(
                'title'=>'Backup View',
                'view'=>'tabs/detail_back_up',
                'data'=>array('backUp' => $backUp,'approved'=>$po->PO_Approved),
            ),
            'tab3'=>array(
                'title'=>'Tracking Sheet',
                'view'=>'tabs/traking_sheet',
                'data'=>array('poTracks' => $poTracks, 'po' => $po, 'pmtsTracking' => $pmtsTracking, 'poError' => $poError,),
            ),
        ),
    ));
    ?>

</div>
<div class="sidebar_right">
    <div class="sidebar_item approval_progress">
        <span class="center">Approval Progress</span>
        <br/>
        <div id="progress_bar" data-id="<?=$po->Document_ID;?>">
            <div id="progress_line" style="width: 0%" data-width="<?php echo $po->PO_Approval_Value ?>">

            </div>
        </div>
    </div>
    <div class="sidebar_item" id="notes_blok">
        <span class="sidebar_block_header">Add an Internal Note:</span>
        <textarea name="" class="note_textarea" maxlength="250"></textarea>
        <input type="hidden" name="note_to_document" id="note_to_document" value="<?php echo $document->Document_ID; ?>"/>
        <button class="button block-center" id="add_note_button">Add Note</button>
        <div id="notes_list">
            <?php
            foreach ($notes as $note) {
                echo '<div class="note_item">
                            <p class="note_body">' . CHtml::encode($note->Comment) . '</p>
                            <p class="note_date">' . Helper::convertDateString($note->Created) . '</p>
                            <p class="note_title">' . CHtml::encode($note->user->person->First_Name) . ' ' . CHtml::encode($note->user->person->Last_Name) . '</p>

                          </div>';
            }
            ?>
        </div>
    </div>
    <div class="sidebar_item OCR_Layer" style="height: 469px;">
        <span class="sidebar_block_header">Budget Comparison:</span>
        <div class="sidebar_attd_inv_list height445">
            <?php
            if (is_array($budgets[$po->PO_ID])) {
                foreach ($budgets[$po->PO_ID] as $code => $budget) {
                    echo '<span class="details_page_value">' . CHtml::encode($code) . ' / ' . $budget . '</span><br />';
                }
            }
            ?>
        </div>
    </div>

    <?if ($document->Origin == 'G' && Yii::app()->user->id == 'db_admin') {echo '<a style="margin:5px;" href="#" id="recreate_document" data-id="' . $document->Document_ID. '">Recreate</a>';}?>

</div>

    <div id="dataentry_block" style="display: none">

    </div>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/doc_create.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/file_uploading.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/progress_bar.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/detail_page.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/po_detail.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/document_view.js"></script>


    <script>
        $(document).ready(function() {
            setEqualHeight($(".wrapper,.sidebar_right"));
            new PODetail;

            <?php
                //echo (!$backUp['document']) ? "new BuUpload('po');" : '';
            ?>
        });
    </script>



<?php
Yii::app()->clientScript->registerScriptFile('/js/mousewheel.js', CClientScript::POS_HEAD);
?>
