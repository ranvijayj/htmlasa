<?php
$this->breadcrumbs=array(
	'PC List',
);

?>
<h1>PC List: <?=@CHtml::encode(Yii::app()->user->userLogin);?><span class="right items_to_review">PCs to review: <span id="number_items">0</span> items</span></h1>

<div class="account_manage">
    <div class="account_header_left left">
        <button class="button right" id="submit_list_form">View Doc</button>
    </div>
    <div class="right">
        <span class="left search_block_label">Search: &nbsp;</span>
        <div class="search_block">
            <input type="text" name="search" value="<?php echo $queryString; ?>" id="search_field" maxlength="250">
            <div id="search_options">
               <span class="search_options_header">Search in the fields:</span><br/>
               <?php
               echo Helper::getSearchOptionsHtml(array(
                   'session_name' => 'last_payments_list_search',
                   'options' => array(
                       'search_option_employee_name' => array('Employee Name', 1),
                       'search_option_envelope_number' => array('Number', 1),
                       'search_option_envelope_total' => array('Total', 0),
                       'search_option_envelope_date' => array('Date', 0),
                   ),
               ));

               $_SESSION['last_pcs_list_search']['options'] = array();
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
                    'title'=>'PC List',
                    'view'=>'tabs/pcs_list',
                    'data'=> array('pcsList' => $pcsList, 'pcs_to_review' => $pcs_to_review),
                ),
            ),
        ));
        ?>
    </div>
</div>
<div class="sidebar_right" id="sidebar">
    <div class="sidebar_item details_sidebar_block" id="company_info">
        <span class="sidebar_block_header">Details:</span>
    </div>
</div>
<script>
    $(document).ready(function() {
        setEqualHeight($(".wrapper,.sidebar_right"));
        new PCList;
    });
</script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/w9_list.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/pcs_list.js"></script>