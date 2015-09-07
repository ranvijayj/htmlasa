<?php
/* @var $this VendorController */

$this->breadcrumbs=array(
	'PO Approval List',
);
?>

<h1 class="po_header">PO Approval List: <?=@CHtml::encode(Yii::app()->user->userLogin);?><span class="right items_to_review" id="items_to_review" data-id="<?php echo count($poList); ?>"> POs to Approve: <span id="number_items"><?php echo count($to_be_approved_count); ?></span> items</span></h1>

<div class="account_manage">
    <div class="account_header_left left">
        <button class="<?php echo $approvalButtonsClass; ?>" id="approve_all_items">Approve All</button>
        <button class="<?php echo $approvalButtonsClass; ?>" id="approve_selected_items">Aprv. Selctd.</button>
        <button class="not_active_button" id="submit_for_batch" data-href="#process_batch">Batch</button>
        <button class="button right" id="submit_po_form">View Doc</button>
    </div>
    <div class="right">
        <span class="left search_block_label">Search: &nbsp;</span>
        <div class="search_block">
            <input type="text" name="search" value="<?php echo $searchQuery; ?>" id="search_field" maxlength="250" autocomplete="off">
            <div id="search_options">
               <span class="search_options_header">Search in the fields:</span><br/>
                <?php
                $options = array(
                    'search_option_to_be_approved' => array('To be approved', $checkedTBABox),
                    'delimiter'=>'',
                    'search_option_com_name' => array('Vendor Name', 1),
                    'search_option_fed_id' => array('Fed ID', 1),
                    'search_option_po_number' => array('PO Number', 0),
                    'search_option_po_date' => array('PO Date', 0),
                    'search_option_po_total' => array('PO Total', 0),
                    'search_option_po_acct_number' => array('PO Account Number', 0),
                    'search_option_payment_type' => array('Payment Type', 0),
                    'search_option_last_digits' => array('Card Last 4 Digits', 0),
                    'search_option_addr1' => array('Addre. 1', 0),
                    'search_option_addr2' => array('Addre. 2', 0),
                    'search_option_city' => array('City', 0),
                    'search_option_state' => array('State', 0),
                    'search_option_zip' => array('Zip', 0),
                    'search_option_country' => array('Country', 0),
                    'search_option_phone' => array('Phone', 0),
                    'search_option_batch' => array('Batch', 0),
                );
                if (Yii::app()->user->id != 'user') {
                    $options = array_merge(array_slice($options, 0, 1), array('search_option_to_be_batched' => array('To be Batched', 0)), array_slice($options,1));
                }
                echo Helper::getSearchOptionsHtml(array(
                    'session_name' => 'last_pos_list_search',
                    'options' => $options,
                ));
                $_SESSION['last_pos_list_search']['options'] = array();
                ?>
            </div>
        </div>
    </div>
</div>
<?php if(Yii::app()->user->hasFlash('success')):?>
    <div class="info">
        <button class="close-alert">&times;</button>
        <?php echo Yii::app()->user->getFlash('success'); ?>
    </div>
<?php endif; ?>
<div class="info_new" id="ready_for_approval_all" style="display: none;">
    <button class="close-alert">×</button>
    The list has been modified to include only the items you want to approve. If correct please click "Approve All" again.
</div>
<div class="info_new" id="ready_for_approval_selctd" style="display: none;">
    <button class="close-alert">×</button>
    The list has been modified to include only the items you want to approve. If correct please click "Appr. Selctd." again.
</div>
<div class="wrapper">
    <div class='w9_list_view'  style="position: relative;">
        <?php
        //if(!$checkedTBABox) {
        if (!isset($_SESSION['limiter'])) {
            $_SESSION['limiter']=Aps::DISPLAY_LIMIT;
            echo '<div id="limiter_div"><label><input type="checkbox" id="limiter_checkbox" checked="true"> Limit '.Aps::DISPLAY_LIMIT.'</label></div>';

        } else {

            if ($_SESSION['limiter']==Aps::DISPLAY_LIMIT) {
                echo '<div id="limiter_div"><label><input type="checkbox" id="limiter_checkbox" checked="true"> Limit '.Aps::DISPLAY_LIMIT.'</label></div>';

            } else {
                echo '<div id="limiter_div"><label><input type="checkbox" id="limiter_checkbox" > Limit '.Aps::DISPLAY_LIMIT.'</label></div>';
            }
        }
        //}
        ?>
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
                    'title'=>'PO List',
                    'view'=>'tabs/po_list',
                    'data'=> array('poList' => $poList, 'notes' => $notes, 'budgets' => $budgets),
                ),
            ),
        ));
        ?>
        <div id="loading_mask_left" style="position: absolute;bottom: 8px; height: 50px; width:610px;left: 5px; background: url(/images/rotate.gif) center no-repeat #fff;display: none; ">  </div>
    </div>



</div>
<div class="sidebar_right">
    <div class="sidebar_item approval_progress">
        <span class="center">Approval Progress</span>
        <br/>
        <div id="progress_bar" style="display: none">
            <div id="progress_line">

            </div>
        </div>
    </div>
    <div class="sidebar_item ap_details_sidebar_block" id="company_info">
        <span class="sidebar_block_header">Details:</span>
    </div>
</div>
<div id="lean_overlay"></div>
<? if (count($client_change_array)) { ?>
    <div id="client_to_change" data-cid="<?=$client_change_array['cid']?>" data-pid="<?=$client_change_array['pid']?>"
         data-uid="<?=$client_change_array['uid']?>" data-uname="<?=$client_change_array['uname']?>"></div>

<? } ?>

<?php $this->renderPartial('application.views.widgets.batch_form', array('userSettings' => $userSettings, 'docType' => 'PO')); ?>
<script>
    $(document).ready(function() {
        setEqualHeight($(".wrapper,.sidebar_right"));
        var listClass = new PoList;
        new BatchExporting('<?php echo Documents::PO; ?>', listClass);
        new ProgressBar('PO');
    });
</script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/w9_list.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/po_list.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/batch_exporting.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/progress_bar.js"></script>