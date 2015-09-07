<?php
/* @var $this VendorController */

$this->breadcrumbs=array(
	'Payments List',
);

?>
<h1>Payments List: <?=@CHtml::encode(Yii::app()->user->userLogin);?><span class="right items_to_review" id="items_to_review" data-id="<?php echo count($paymentsList); ?>">Payments: <span id="number_items"><?php echo count($paymentsList); ?></span> items</span></h1>

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
                       'search_option_payment_check_date' => array('Payment Check Date', 0),
                       'search_option_payment_check_number' => array('Payment Check Number', 1),
                       'search_option_payment_amount' => array('Payment Amount', 0),
                       'search_option_invoice_number' => array('Invoice Number', 0),
                       'search_option_invoice_amount' => array('Invoice Amount', 0),
                       'search_option_invoice_date' => array('Invoice Date', 0),
                       'search_option_com_name' => array('Vendor Name', 1),
                       'search_option_fed_id' => array('Fed ID', 0),
                       'search_option_addr1' => array('Addre. 1', 0),
                       'search_option_city' => array('City', 0),
                       'search_option_state' => array('State', 0),
                       'search_option_zip' => array('Zip', 0),
                   ),
               ));

               if (count($acctNums) > 0) {
                   ?>
                   <span class="search_options_header">Narrow by Bank Accounts:</span><br/>
                   <?php
                   foreach ($acctNums as $acctId => $acctNum) {
                       $checkedBA = (isset($_SESSION['last_payments_list_search']['options']['bankAccounts']) && is_array($_SESSION['last_payments_list_search']['options']['bankAccounts']) && in_array($acctId, $_SESSION['last_payments_list_search']['options']['bankAccounts'])) ? 'checked="checked"' : '';
                       echo '<label><input type="checkbox" class="account_nums_chbx" value="' . $acctId . '" name="account_nums_chbx' . $acctId . '" ' . $checkedBA . '/>' . $acctNum . '</label><br/>';
                   }
               }
               $_SESSION['last_payments_list_search']['options'] = array();
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
                    'title'=>'Check Register',
                    'view'=>'tabs/payments_list',
                    'data'=> array('paymentsList' => $paymentsList, 'payments_to_review' => $payments_to_review),
                ),
            ),
        ));
        ?>

        <div id="loading_mask_left" style="position: absolute;bottom: 8px; height: 50px; width:610px;left: 5px; background: url(/images/rotate.gif) center no-repeat #fff;display: none; ">  </div>
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
        new PaymentsList;
    });
</script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/w9_list.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/payments_list.js"></script>