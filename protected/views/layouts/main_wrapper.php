<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="google-site-verification" content="6-R4ZnLsoVSr2TRMPJNbgQj01YAVPAc9BsGuJFupYf4" />
    <link rel="icon" href="<?php echo Yii::app()->request->baseUrl; ?>/images/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo Yii::app()->request->baseUrl; ?>/images/favicon.ico" type="image/x-icon">

    <!-- blueprint CSS framework -->
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/screen.css" media="screen, projection, handheld" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/print.css" media="print" />

    <!--[if lt IE 8]>
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection, handheld" />
	<![endif]-->

	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />

    <link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/fontello.css"/>
    <!--[if IE 7]>
    <link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/fontello-ie7.css"/>
    <![endif]-->
    <link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/font_arialn.css"/>

    <?php if (Helper::getMobileDetect()->isMobile() || Helper::getMobileDetect()->isTablet() || Helper::checkMobile()) {  ?>
        <link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/font_arialn_tablet.css"/>
    <?php } ?>

    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" media="screen, projection, handheld" />

	<title><?php echo CHtml::encode($this->pageTitle); ?></title>

    <?php Yii::app()->getClientScript()->registerCoreScript('jquery'); ?>

</head>

<body>
<div id="wrapper">
    <header>
        <div id="top-header">
            <div class="left">
                <?php
                if (isset(Yii::app()->user->clientInfo) && Yii::app()->user->clientInfo) {
                    ?>
                    <span><a href="#change_client" onclick="open_change_client_box();"><? echo @CHtml::encode(Yii::app()->user->clientInfo);?></a> &nbsp;|&nbsp; <a href="#change_client" onclick="open_change_client_box();"><? echo @CHtml::encode(Yii::app()->user->projectInfo);?></a> &nbsp;|&nbsp; <? echo @CHtml::encode(Yii::app()->user->userType);?></span>
                <?php
                }
                ?>
            </div>
            <div class="right">
            <span>
                <?php
                if (Yii::app()->user->id) {
                    ?>
                    <span id="user_info_link"><? echo @CHtml::encode(Yii::app()->user->userInfo);?></span> &nbsp;|&nbsp;
                <?php
                }
                ?>
                <a href="#loginmodal" id="modaltrigger">
                    <? echo (Yii::app()->user->id) ? 'Sign Out' : 'User Login'; ?>
                </a>
            </span>
            </div>
        </div>
    </header>
    <div class="header_block"></div>
    <div id="mainmenu">
        <div class="container" style="">
            <div id="logo"><?php echo CHtml::encode(Yii::app()->name); ?></div>
            <?php $this->widget('zii.widgets.CMenu',array(
                'items'=>array(
                    array(
                        'label'=>'Home',
                        'url'=>array('/site'),
                        'items'=>array(
                            array('label'=>'Uploads', 'url'=>'/uploads'),
                            array('label'=>'Data Entry', 'url'=>'/dataentry/w9'),
                            array('label'=>'COA', 'url'=>'/coa'),
                            array('label'=>'Pending Approval', 'url'=>'/documents/approvalcue'),
                            array('label'=>'Batched', 'url'=>'/library?activeTab=tab3'),
                            array('label'=>'Remote Process', 'url'=>'/remoteprocessing'),
                            //commented out 16.10.2014 according to the ASAAP Phase I Logic Call Out V011.pdf  page 6
                            //array('label'=>'History', 'url'=>'#'),
                            // array('label'=>'View AP', 'url'=>'/ap/detail', 'linkOptions' => array('id'=>'ap_detail_page_link2')),
                            //array('label'=>'Manage Users', 'url'=>'/myaccount?tab=man_users'),
                            //array('label'=>'My Vendors', 'url'=>'/vendor/manage'),
                            array('label'=>'Help', 'url'=>'/help/video')
                        ),
                    ),
                    array(
                        'label'=>'Vendor',
                        'url'=>array('/vendor'),
                        'linkOptions' => array('class'=>'clear_vendors_to_review_list'),
                        'items'=>array(
                            array('label'=>'List', 'url'=>'/vendor', 'linkOptions' => array('class'=>'clear_vendors_to_review_list')),
                            array('label'=>'Detail', 'url'=>'/vendor/detail', 'linkOptions' => array('class'=>'clear_vendors_to_review_list')),
                            array('label'=>'Manage', 'url'=>'/vendor/manage'),
                            array('label'=>'W9 List', 'url'=>'/w9'),
                            array('label'=>'W9 Detail', 'url'=>'/w9/detail', 'linkOptions' => array('class'=>'clear_w9_to_review_list')),
                        ),
                    ),
                    array(
                        'label'=>'PO',
                        'url'=>array('/po'),
                        'linkOptions' => array('class'=>'clear_po_to_review_list'),
                        'items'=>array(
                            array('label'=>'Create', 'url'=>'/po/create'),
                            array('label'=>'List', 'url'=>'/po', 'linkOptions' => array('class'=>'clear_po_to_review_list')),
                            array('label'=>'Detail', 'url'=>'/po/detail', 'linkOptions' => array('class'=>'clear_po_to_review_list'))
                        ),
                    ),
                    array(
                        'label'=>'AP',
                        'url'=>array('/ap'),
                        'linkOptions' => array('id'=>'ap_list_page_main_link'),
                        'items'=>array(
                            array('label'=>'Create', 'url'=>'/ap/create'),
                            array('label'=>'List', 'url'=>'/ap', 'linkOptions' => array('id'=>'ap_list_page_link')),
                            array('label'=>'Detail', 'url'=>'/ap/detail', 'linkOptions' => array('id'=>'ap_detail_page_link'))
                        ),
                    ),
                    array(
                        'label'=>'Payments',
                        'url'=>array('/payments'),
                        'linkOptions' => array('id'=>'payments_list_page_main_link'),
                        'items'=>array(
                            array('label'=>'List', 'url'=>'/payments', 'linkOptions' => array('id'=>'payments_list_page_link')),
                            array('label'=>'Detail', 'url'=>'/payments/detail', 'linkOptions' => array('id'=>'payments_detail_page_link'))
                        ),
                    ),
                    array(
                        'label'=>'Archive',
                        'url'=>array('/library'),
                        'items'=>array(
                            array('label'=>'Library', 'url'=>'/library'),
                            array('label'=>'Organize', 'url'=>'/library/organize'),
                        ),
                    ),
                    array(
                        'label'=>'My Account',
                        'url'=>array('/myaccount'),
                        'items'=>array(
                            array('label'=>'My profile', 'url'=>'/myaccount?tab=profile'),
                            array('label'=>'Settings', 'url'=>'/myaccount?tab=settings'),
                            array('label'=>'Manage users', 'url'=>'/myaccount?tab=man_users'),
                            array('label'=>'Company info', 'url'=>'/myaccount?tab=com_info'),
                            array('label'=>'Credit Card', 'url'=>'/myaccount?tab=ccard'),
                            array('label'=>'Payment History', 'url'=>'/myaccount?tab=phistory'),
                            array('label'=>'Service Level', 'url'=>'/myaccount?tab=service'),
                            array('label'=>'Doc. Reassign.', 'url'=>'/myaccount?tab=doc_reassign'),
                            array('label'=>'Security', 'url'=>'/myaccount?tab=security'),
                        ),
                    ),
                    array(
                        'label'=>'Help',
                        'url'=>array('/help/video'),
                        'items'=>array(
                            array('label'=>'Video', 'url'=>'/help/video'),
                            array('label'=>'About', 'url'=>array('/site/page', 'view'=>'help')),
                        ),
                    ),
            )
            )); ?>
        </div>
        <script type="text/javascript">
            $(document).ready(function() {
                $('#mainmenu > .container > ul > li').hover(function () {
                    clearTimeout($.data(this,'timer'));
                    $('ul',this).stop(true,true).fadeIn(200);
                }, function () {
                    $.data(this,'timer', setTimeout($.proxy(function() {
                        $('ul',this).stop(true,true).fadeOut(200);
                    }, this), 100));
                });
            });
        </script>
    </div><!-- mainmenu -->

    <?php echo $content; ?>


    <?php //$this->renderPartial('application.views.widgets.admfooter'); ?>
    <footer>
        <span id="link_to_test_w9">Test for W9</span>
        <?php
        if (Yii::app()->user->id && (Yii::app()->user->userType == Users::ADMIN || Yii::app()->user->userType == Users::DB_ADMIN)) {
            ?>
            <span id="link_to_admin_panel"><a href="/admin">[+]</a></span>

        <?php
        }

        ?>
        <div id="copyright" style="right: 0;position: fixed;bottom: 0;">Copyright © 2013 All Rights Reserved Mountain Asset Group, Inc.</div>
    </footer>
</div>
<?php if(!Yii::app()->user->id): ?>
    <?php $this->renderPartial('application.views.widgets.login'); ?>

    <?php $this->renderPartial('application.views.widgets.register',array(
            'answers'=>$answers
        )
    ); ?>
    <?php $this->renderPartial('application.views.widgets.register_as_client_admin'); ?>
    <?php $this->renderPartial('application.views.widgets.forgotpassword'); ?>
    <?php $this->renderPartial('application.views.widgets.support_request_general'); ?>
    <?php $this->renderPartial('application.views.widgets.support_request_device'); ?>
    <?php Yii::app()->clientScript->registerScriptFile('/js/md5.js', CClientScript::POS_HEAD); ?>
<?php endif; ?>
<?php if(Yii::app()->user->id): ?>
    <?php $this->renderPartial('application.views.widgets.logout'); ?>
    <?php $this->renderPartial('application.views.widgets.dialog'); ?>
    <?php $this->renderPartial('application.views.widgets.dialog_no_hide'); ?>
    <?php $this->renderPartial('application.views.widgets.dialog_de_only'); ?>
    <?php $this->renderPartial('application.views.widgets.alert'); ?>
    <?php $this->renderPartial('application.views.widgets.alert_alone'); ?>
    <?php $this->renderPartial('application.views.widgets.adduser'); ?>
    <?php $this->renderPartial('application.views.widgets.find_user_docs'); ?>
    <?php $this->renderPartial('application.views.widgets.change_client'); ?>
    <?php $this->renderPartial('application.views.widgets.askemail'); ?>
    <?php $this->renderPartial('application.views.widgets.askfax'); ?>
    <?php $this->renderPartial('application.views.widgets.newcompany'); ?>
    <?php $this->renderPartial('application.views.widgets.comparison_block'); ?>
    <?php $this->renderPartial('application.views.widgets.login_detail'); ?>
    <?php $this->renderPartial('application.views.widgets.additional_fields_block'); ?>
    <?php $this->renderPartial('application.views.widgets.change_shortcut'); ?>
    <?php $this->renderPartial('application.views.widgets.audit_view_block'); ?>
    <?php $this->renderPartial('application.views.widgets.client_panel'); ?>


<?php endif; ?>
<?php $this->renderPartial('application.views.widgets.test_for_w9'); ?>
<div id="tooltip"></div>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.leanModal.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/main.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui-min.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/jqtouch.js"></script>


<script>
    $(function(){
        $('#modaltrigger').leanModal({ top: 110, overlay: 0.45, closeButton: ".hidemodal" });
    });
</script>
</body>

</html>