<?php
/* @var $this VendorController */

$this->breadcrumbs=array(
	'W9 List',
);

?>
<h1 class="w9_header">W9 List: <?=@CHtml::encode(Yii::app()->user->userLogin);?><span class="right items_to_review">W9s to review: <span id="number_items">0</span> items</span><span class="right" style="font-size: 15px; margin-right: 15px; margin-right: 250px; font-weight: normal; display: none;">Prior Review: [###]</span></h1>

<div class="account_manage">
    <div class="account_header_left left">
        <!--
        <button class="button">Approve All</button>
        <button class="button">Aprv. Selctd.</button>
        -->
        <?php
           if (count($w9_to_review) == 1) {
               /*
                foreach ($vendorsList as $vendor) {
                    if (in_array($vendor['Company_ID'], $w9_to_review)) {
                        $fed_id = $vendor['Company_Fed_ID'];
                    }
                }
               */
               //echo '<button class="button" id="print_document" data="' . $fed_id .'">Print</button>';
           } else {
               //echo '<button class="button" id="print_document" style="display: none" data="">Print</button>';
           }
        ?>
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
                    'session_name' => 'last_w9_list_search',
                    'options' => array(
                        'search_option_com_name' => array('Vendor Name', 1),
                        'search_option_fed_id' => array('Fed ID', 1),
                        'search_option_addr1' => array('Addre. 1', 0),
                        'search_option_addr2' => array('Addre. 2', 0),
                        'search_option_city' => array('City', 0),
                        'search_option_state' => array('State', 0),
                        'search_option_zip' => array('Zip', 0),
                        'search_option_country' => array('Country', 0),
                        'search_option_phone' => array('Phone', 0),
                    ),
                ));
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

    <div class='w9_list_view' style="position: relative;">
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
                    'title'=>'W9 List',
                    'view'=>'tabs/w9_list',
                    'data'=> array('vendorsList' => $vendorsList, 'w9_to_review' => $w9_to_review,'current_client_w9'=>$current_client_w9),
                ),
            ),
        ));
        ?>
    </div>
</div>
<div class="sidebar_right">
    <div class="sidebar_item details_sidebar_block" id="company_info">
        <span class="sidebar_block_header">Details:</span>
    </div>
</div>
<script>
    $(document).ready(function() {
        setEqualHeight($(".wrapper,.sidebar_right"));
        new W9List;
    });
</script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/w9_list.js"></script>