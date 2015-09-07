<?php
/* @var $this VendorController */

$this->breadcrumbs=array(
	'Vendor List',
);

?>
<h1>Vendor List: <?=@CHtml::encode(Yii::app()->user->userLogin);?> <span class="right items_to_review" id="items_to_review" data-id="<?php echo count($vendorsList); ?>" ">Vendors in List: <?php echo count($vendorsList); ?></span> </h1>

<div class="account_manage">
    <div class="account_header_left left">

        <? if (Yii::app()->user->userType != UsersClientList::USER) {?>
        <button class="button" id="import_vendors_list">Import</button>
        <a href="/vendor/export" target="_blank" class="button">Export</a>
        <a href="/vendor/print" target="_blank" class="button">Print</a>
        <?} else {?>

        <button class="button" id="import_vendors_list" style="display: none">Import</button>
        <a href="/vendor/export" target="_blank" class="button">Export</a>
        <a href="/vendor/print" target="_blank" class="button">Print</a>

        <?}?>


        <button class="button right" id="submit_list_form">View Doc</button>
    </div>
    <div class="right">
        <span class="left search_block_label">Search: &nbsp;</span>
        <div class="search_block">
            <input type="text" name="search" id="search_field" maxlength="250" value="<?php echo $searchQuery; ?>">
            <div id="search_options">
               <span class="search_options_header">Search in the fields:</span><br/>
               <?php
                echo Helper::getSearchOptionsHtml(array(
                    'session_name' => 'last_vendors_list_search',
                    'options' => array(

                        'search_option_temporary' => array('Temporary Vendors', 0),
                        'search_option_international' => array('International Vendors', 0),
                        'delimiter'=>'',
                        'search_option_com_name' => array('Vendor Name', 1),
                        'search_option_fed_id' => array('Fed ID', 1),
                        'search_option_shortcut' => array('Shortcut', 1),
                        'search_option_addr1' => array('Addre. 1', 0),
                        'search_option_addr2' => array('Addre. 2', 0),
                        'search_option_city' => array('City', 0),
                        'search_option_state' => array('State', 0),
                        'search_option_zip' => array('Zip', 0),
                        'search_option_country' => array('Country', 0),
                        'search_option_phone' => array('Phone', 0),
                    ),
                ));
                $_SESSION['last_vendors_list_search']['options'] = array();
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
                    'title'=>'Vendor List',
                    'view'=>'tabs/vendor_list',
                    'data'=> array('vendorsList' => $vendorsList,'current_client_w9'=>$current_client_w9),
                ),
            ),
        ));
        ?>
    <div id="loading_mask_left" style="position: absolute;bottom: 8px; height: 50px; width:610px;left: 5px; background: url(/images/rotate.gif) center no-repeat #fff;display: none; ">  </div>
    </div>
</div>
<div class="sidebar_right">
    <div style="position: relative; top:25px;float: right;"> <a href="#" class="add_new_vendor"> Add new vendor </a> </div>
    <div class="sidebar_item details_sidebar_block" id="company_info">
        <span class="sidebar_block_header">Details:</span>
    </div>
</div>
<script>
    $(document).ready(function() {
        setEqualHeight($(".wrapper,.sidebar_right"));
        new VendorList;
        new VendorW9Upload('vendor_list_mode');

    });
</script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/ajaxupload_xls.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/progress_bar.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/w9_list.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/vendor_list.js"></script>


<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/vendor_w9_upload.js"></script>