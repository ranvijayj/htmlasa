<?php
/* @var $this VendorController */

$this->breadcrumbs=array(
    'Approval Cue List',
);

?>
<h1>Approval Cue: <?=@CHtml::encode(Yii::app()->user->userLogin);?><span class="right items_to_review">Documents to approve: <span id="number_items">0</span> items</span></h1>

<div class="account_manage">
    <div class="account_header_left left">
        <button id="notify_all_items" class="button" style="width: 120px" >Notify All</button>
        <button id="notify_selected_items" class="not_active_button" style="width: 120px">Notify Selected</button>
        <button id="open_selected_items" class="not_active_button" style="width: 120px">Open</button>

    </div>
    <div class="right">
        <span class="left search_block_label">Search: &nbsp;</span>
        <div class="search_block">
            <input type="text" name="search" value="<?php echo $searchQuery; ?>" id="search_field" maxlength="250">
            <div id="search_options">
                <span class="search_options_header">Search in the fields:</span><br/>
                <?php
                $options = array(
                    //'search_option_to_be_approved' => array('To be approved', $checkedTBABox),
                    'search_option_com_name' => array('Company Name', 1),
                    'search_option_fed_id' => array('Fed ID', 1),
                    'search_option_addr1' => array('Address 1', 0),
                    'search_option_addr2' => array('Address 2', 0),
                    'search_option_city' => array('City', 0),
                    'search_option_state' => array('State', 0),
                    'search_option_zip' => array('Zip', 0),
                    'search_option_country' => array('Country', 0),
                    'search_option_phone' => array('Phone', 0),

                );
                echo Helper::getSearchOptionsHtml(array(
                    'session_name' => 'last_aps_list_search',
                    'options' => $options,
                ));
                $_SESSION['last_aps_list_search']['options'] = array();
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
    <div class='w9_list_view'>
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
                    'title'=>'Approval List',
                    'view'=>'tabs/appr_list',
                    'data'=> array('cueApprList' => $cueApprList, 'notes' => $notes),
                ),
            ),
        ));
        ?>
    </div>
</div>
<div class="sidebar_right">
    <div class="sidebar_item approval_progress">
        <span class="center">Approval Progress</span>
        <br/>
        <div id="progress_bar" data-id="<?=$ap->Document_ID;?>" style="display: none;">

            <div id="progress_line">

            </div>
        </div>
    </div>
    <div class="sidebar_item ap_details_sidebar_block" id="company_info">
        <span class="sidebar_block_header">Details:</span>
        <h2 class="sidebar_block_list_header">Vendor Name</h2>
    </div>
</div>

<div class="modal_box" id="send_mail_dialog" style="display: none;">
    <img src="/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <div style="padding-left: 40px">
        <h2>You are going to send notifications to following users: </h2>


        <div id="email_block">
        </div>

        <br/><br/>
        <input id="send_mail" class="button" type="submit" value="Send" name="yt0">
    </div>
</div>

<div class="modal_box" id="open_dialog" style="display: none;">
    <img src="/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <div style="padding-left: 40px">
        <h2>Please select like documents (ie: POs or APs & not DEC)! </h2>
        <br/><br/><br/>
    </div>
</div>



<script>
    $(document).ready(function() {
        setEqualHeight($(".wrapper,.sidebar_right"));
        var listClass = new ApList;
    });
</script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/w9_list.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/apr_cue_list.js"></script>
