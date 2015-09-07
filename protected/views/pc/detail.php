<?php
$this->breadcrumbs=array(
    'PC List'=>array('/pc'),
    'Detail',
);
?>
<h1>PC Detail: <?=@CHtml::encode(Yii::app()->user->userLogin);?><span class="right items_to_review">PCs to review: <?php echo $num_pages; ?> items</span></h1>

<div class="account_manage">
    <div class="account_header_left left">
        <button class="button" id="send_document_by_email" data="<?php echo $document->Document_ID; ?>">Email</button>
        <?php
        if (strpos($file->Mime_Type, 'pdf') === false) {
            echo '<button class="button" id="print_document" data="' .  $document->Document_ID . '">Print</button>';
        }
        ?>
    </div>
    <?php $this->renderPartial('application.views.widgets.pages_navigation', array(
        'page' => $page,
        'num_pages' => $num_pages,
        'url' => '/pc/detail',
    )); ?>
</div>

<div class="wrapper">
    <div class="w9_details">
        <span class="right created_on">Created on: <span class="created_on_date"><?php echo Helper::convertDateString($document->Created) . ' ' . CHtml::encode($user->person->First_Name) . ' ' . CHtml::encode($user->person->Last_Name); ?></span></span>
        <h2><?php echo isset($pc->Employee_Name) ? CHtml::encode($pc->Employee_Name) : '<span class="not_set">Not set</span>'; ?></h2>
        <table class="details_table">
            <tr>
                <td class="width240">
                    <ul class="details_table_list">
                        <li>Total: <span class="details_page_value"><?php echo ($pc->Envelope_Total != 0) ? CHtml::encode(number_format($pc->Envelope_Total, 2)) : '<span class="not_set">Not set</span>'; ?></span></li>
                        <li>Date: <span class="details_page_value"><?php echo $pc->Envelope_Date ? CHtml::encode(Helper::convertDate($pc->Envelope_Date)) : '<span class="not_set">Not set</span>'; ?></span></li>
                        <li>Number: <span class="details_page_value"><?php echo $pc->Envelope_Number != 0 ? CHtml::encode($pc->Envelope_Number) : '<span class="not_set">Not set</span>'; ?></span></li>
                    </ul>
                </td>
                <td>
                    <ul class="details_table_list">
                        <li> <span class="details_page_value"><?php //echo $payment->Payment_Check_Number ? CHtml::encode($payment->Payment_Check_Number) : '<span class="not_set">Not set</span>'; ?></span></li>
                        <li> <span class="details_page_value"><?php //echo '<span class="not_set">Not set</span>'; //echo $payment->Vendor_ID; ?></span></li>
                        <li> <span class="details_page_value"><?php //echo isset($payment->bank_account->Account_Name) ? Chtml::encode($payment->bank_account->Account_Name . ' / ' . $payment->bank_account->Account_Number) : '<span class="not_set">Not set</span>'; ?></span></li>
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
                'title'=>'PC View',
                'view'=>'tabs/pc_view',
                'data'=>array('file' => $file, 'document' => $document),
            ),
            'tab2'=>array(
                'title'=>'View 2',
                'view'=>'tabs/view3',
                //'data'=>array('model'=>$model),
            ),
            'tab3'=>array(
                'title'=>'View 3',
                'view'=>'tabs/view3',
                //'data'=>array('file' => $file, 'document' => $document),
            ),
        ),
    ));
    ?>

</div>
<div class="sidebar_right">
    <div class="sidebar_item">
        <span class="sidebar_block_header">Details:</span>

    </div>
    <div class="sidebar_item OCR_Layer">
        <span class="sidebar_block_header">Details:</span>

    </div>
</div>
<script>
    $(document).ready(function() {
        setEqualHeight($(".wrapper,.sidebar_right"));
        new PCDetail();
    });
</script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/detail_page.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/pcs_detail.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/document_view.js"></script>
<?php
Yii::app()->clientScript->registerScriptFile('/js/mousewheel.js', CClientScript::POS_HEAD);
?>