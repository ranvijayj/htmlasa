<?php
/* @var $this PoController */

$this->breadcrumbs=array(
    'PO Approval List'=>array('/po'),
    'Create',
);
?>
<h1 class="po_header">PO Create: <?=@CHtml::encode(Yii::app()->user->userLogin);?></h1>
<div class="account_manage">
    <div class="account_header_left left">
        <?php
            if ($poId == 0) {
                ?>
                    <button class="not_active_button" id="create_po">New PO</button>
                <?php
            } else {
                ?>
                <button class="button" id="create_po">New PO</button>
                <?php
            }
        ?>
        <button class="button" id="save_po" data="<?php echo $po->PO_ID; ?>">Save PO</button>

        <button id="file_upload_button" class="button">Upload files...</button>
            <input id="fileupload" type="file" name="files[]" style="display: none;">
            <input id="filecounter" type="hidden" value="1">


        <button <?php echo $enableCreating ? 'class="button right" id="send_to_approve"' : 'class="button disable_uploading right" data-message="' . $disableCreatingMessage . '"';?>>Send To Aprv.</button>
    </div>
    <div class="right">
        <?php
        if ($availableStorage != 0) {
            echo "<span class='account_manage_b_span'>Used " . number_format($usedStorage, 2) . "GB of " . number_format($availableStorage) . "GB (" . number_format(100*$usedStorage/$availableStorage, 1) . "%)</span>";
        }
        ?>
    </div>
</div>
<?php if(Yii::app()->user->hasFlash('success')):?>
    <div class="info">
        <button class="close-alert">&times;</button>
        <?php echo Yii::app()->user->getFlash('success'); ?>
    </div>
<?php endif; ?>

<?php if(Yii::app()->user->hasFlash('error')):?>
    <div class="error_flash" style="color: #ff0000;">
        <button class="close-alert">&times;</button>
        <?php echo Yii::app()->user->getFlash('error'); ?>
    </div>
<?php endif; ?>

<div class="wrapper">
    <div class="w9_details">
        <span class="right created_on">Created on: <span class="created_on_date"><?php echo isset($po->document->Created) ? Helper::convertDateString($po->document->Created) . ' ' . CHtml::encode(Helper::shortenString($po->document->user->person->First_Name. ' ' . $po->document->user->person->Last_Name,20)) : Helper::convertDateString(date("Y-m-d H:i:s")); ?></span></span>
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
                'title'=>'Create PO',
                'view'=>'tabs/create',
                'data'=>array(
                    'clientApprovers' => $clientApprovers,
                    'dists' => $dists,
                    'descDetails' => $descDetails,
                    'po' => $po,
                    'poFormatting' => $poFormatting,
                    'poId' => $poId,
                    'vendors' => $vendors,
                    'currentVendor' => $currentVendor,
                    'vendorAdmin' => $vendorAdmin,
                    'signRequestedByUser' => $signRequestedByUser,
                    'detailsError' => $detailsError,
                    'distsError' => $distsError,
                    'coaStructure'=>$coaStructure
                ),
            ),
            'tab2'=>array(
                'title'=>'Backup View',
                'view'=>'tabs/back_up',
                'data' => array(
                    'backUp' => $backUp,
                ),
            )
        ),
    ));
    ?>
</div>
<div class="sidebar_right">
    <span class="sidebar_block_header" id="po_staging_items_header">Purchase Order Staging</span>
    <div class="sidebar_item" id="po_staging_items">
        <span class="po_staging_items_header">Date / Num. / Vendor</span>
        <div id="po_staging_items_block">
            <table>
            <?php
                foreach ($stagingItems as $stagingItem) {
                    $color = '';
                    if ($poId == $stagingItem->PO_ID) {
                        $color  = "style='color: #003399;'";
                    }
                    $str='';
                    $str =  Helper::convertDateDayMonthSimple($stagingItem->PO_Date) . ' / ' . $stagingItem->PO_Number . (($stagingItem->vendor) ? ' / ' . Chtml::encode($stagingItem->vendor->client->company->Company_Name) : '');
                    $str=Helper::cutText(15, 180, 13, $str );


                    echo '<tr><td>';
                    echo "<a href='" . Yii::app()->request->baseUrl . "/po/create/" . $stagingItem->PO_ID . "' class='staging_item_link' " . $color . ">".$str.  "</a>";
                    echo '</td><td>';
                    echo '<a href=# class="mark_as_void" data-id="'.$stagingItem->PO_ID.'">Delete</a>';
                    echo '</td></tr>';

                }
            ?>
            </table>
        </div>
    </div>
</div>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/doc_create.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/progress_bar.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/po_create.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/file_uploading.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/document_view.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/vendor_w9_upload.js"></script>


<script>
    $(document).ready(function() {
        setEqualHeight($(".wrapper,.sidebar_right"));
        new POCreate;
        new FileUploading('PO');
        new ProgressBar('PO');
        new VendorW9Upload;
    });
</script>

<?php
Yii::app()->clientScript->registerScriptFile('/js/mousewheel.js', CClientScript::POS_HEAD);
?>