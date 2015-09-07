<?php
/* @var $this MyAccountController */

$this->breadcrumbs=array(
	'My Account',
);
?>
<h1>My Account: <?=@CHtml::encode(Yii::app()->user->userLogin);?></h1>

<?php

$level_ids =  explode(',',$summary_sl_settings['Tiers_Str']);//array of servise levels for current user
$show_save_button = 'style="display:none;"';
if ($tab == 'profile' || $tab == 'doc_reassign'
    || $tab == 'settings'
    || ($tab == 'com_info' && $client_admin)
    || ($tab == 'ccard' && $client_admin)
    || ( $tab == 'service' && count($pending_client_service_settings)>0 )
    ) {
    $show_save_button = '';
}
if ($tab == 'profile') {
    $tab = 'tab1';
} elseif ($tab == 'settings') {
    $tab = 'tab2';
} elseif ($tab == 'man_users') {
    $tab = 'tab3';
} elseif ($tab == 'com_info') {
    $tab = 'tab4';
} elseif ($tab == 'ccard') {
    $tab = 'tab5';
} elseif ($tab == 'phistory') {
    $tab = 'tab6';
} elseif ($tab == 'service') {
    $tab = 'tab7';
} elseif ($tab == 'doc_reassign') {
    $tab = 'tab8';
} elseif ($tab == 'security') {
    $tab = 'tab9';
}
else {
    $tab = 'tab1';
}



?>
<div class="account_manage">
    <button class="button" id="account_submit" <?php echo $show_save_button;?> data="<?php echo $tab;?>">Save</button>
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
    <?php
//echo "Count of approvers is -".var_dump(UsersClientList::countFinalApprovers(Yii::app()->user->clientID));
    $this->widget('CTabView', array (
        'activeTab'=>$tab,
        'cssFile'=>'/css/jquery.yiitab2.css',
        'tabs'=>array(
            'tab1'=>array(
                'title'=>'My Profile',
                'view'=>'tabs/email_password',
                'data'=>array(
                    'person'=>$person,
                    'person_adress'=>$person_adress,
                    'password_form'=>$password_form),

            ),
            'tab2'=>array(
                'title'=>'Settings',
                'view'=>'tabs/settings',
                'data'=>array(
                    'user_settings'=>$user_settings,
                    'all_user_projects' => $all_user_projects,
                    'all_user_bank_accounts' => $all_user_bank_accounts,
                    'user_role'=>$user_role,
                ),
            ),
            'tab3'=>array(
                'title'=>'Manage Users',
                'view'=>'tabs/manage_users',
                'data'=>array(
                    'client_users'=>$client_users,
                    'client_admin' => $client_admin,
                    'users_page' => $users_page,
                    'enableAddUser' => $enableAddUser,
                    'user_role'=>$user_role,
                ),
            ),
            'tab4'=>array(
                'title'=>'Company Info',
                'view'=>'tabs/company_info',
                'data'=>array(
                    'client'=>$client,
                    'company'=>$company,
                    'company_adress'=>$company_adress,
                    'client_admin' => $client_admin,
                    'bankAccountNums' => $bankAccountNums,
                    'projects' => $projects,
                    'user_role'=>$user_role,
                ),
            ),
            'tab5'=>array(
                'title'=>'Credit Card',
                'view'=>'tabs/credit_card',
                'data'=>array(
                    'ccTypes' => $ccTypes,
                    'cCard' => $cCard,
                    'user_role'=>$user_role,
                ),
            ),
            'tab6'=>array(
                'title'=>'Payment History',
                'view'=>'tabs/payment_history',
                'data'=>array(
                    'model'=>$p_model,
                    'user_role'=>$user_role,
                ),
            ),
            'tab7'=>array(
                'title'=>'Service Level',
                'view'=>'tabs/service_level',
                'data'=>array(
                    'settings'=> $client_service_settings,
                    'serviceLevels' => $serviceLevels,
                    'client_admin' => $client_admin,
                    'pending_client_service_settings' => $pending_client_service_settings,
                    'delayed_client_service_settings'=>$delayed_client_service_settings,
                    'user_role'=>$user_role,
                    'summary_sl_settings'=>$summary_sl_settings,
                    'level_ids'=>$level_ids
                ),
            ),
            'tab8'=>array(
                'title'=>'Doc. Reassignment',
                'view'=>'tabs/doc_reassignment',
                'data' => array(
                    'userDocuments' => $userDocuments,
                    'user_clients' => $user_clients,
                    'userProjects' => $userProjects,
                    'user_role'=>$user_role,
                ),
            ),
            'tab9'=>array(
                'title'=>'Security',
                'view'=>'tabs/security_tab',
                'data' => array(
                    'user_settings'=>$user_settings,
                    'all_user_projects' => $all_user_projects,
                    'all_user_bank_accounts' => $all_user_bank_accounts,
                    'user_role'=>$user_role,
                    'users_questions'=>$users_questions
                ),
            ),

        ),
    ));
    ?>
</div>

<div class="sidebar_right">
    <?php
        $show_profile_forms = 'style="display:none;"';
        if ($tab == 'tab1') {
            $show_profile_forms = '';
        }
    ?>
    <div id="my_profile_sidebar_block" <?php echo $show_profile_forms; ?>>
        <div class="sidebar_item">
            <form action="/myaccount/requesttojoincompany" id="requesttojoincompany">
                <span class="center sidebar_block_header">Request to join company</span><br/>
                <span class="center"><input id="join_fed_id" type="text" value="<?php echo isset($_SESSION['show_req_to_join']) ? $_SESSION['show_req_to_join']['fed_id'] : ''; ?>" placeholder="Company Fed ID" maxlength="45" name="join_fed_id" class="sidebar_input"></span><br/>
                <?php
                    if (isset($_SESSION['show_req_to_join'])) {
                         ?>
                           <div class="errorMessage"><?php echo $_SESSION['show_req_to_join']['message'] ?></div>
                         <?php
                         unset($_SESSION['show_req_to_join']);
                    }
                ?>
                <span class="center"><button class="button" id="submit_join_request">Send</button></span>
                <br/>
                <span class="left create_new_company_link" onclick="show_modal_box('#newcompanymodal', 260, 50)">Create new company</span>
            </form>
            <script>
                $(document).ready(function() {
                    $('#requesttojoincompany').submit(function(event) {
                        var fedId = $('#join_fed_id').val();
                        if (fedId == '') {
                            event.preventDefault();
                            $('#join_fed_id').focus();

                        }
                    });
                });
            </script>
        </div>
    </div>
    <div id="company_info_sidebar_block" style="display: none;">

    </div>
    <?php
    $show_users_list = 'style="display:none;"';
    if ($tab == 'tab3') {
        $show_users_list = '';
    }
    ?>
    <div id="manage_users_sidebar_block" <?php echo $show_users_list; ?>>
        <div id="users_projects" style="display: none;">

        </div>
    <?php
     if ($client_admin && count($usersToApprove)) {
     ?>
    <div id="users_to_add">
        <h3>Pending Approval:</h3>
        <div id="users_to_add_list">
            <form action="/myaccount/approveusers" enctype="text/plain" id="users_to_add_form">
                <table id="list_table">
                    <tbody>
                    <?php
                    foreach ($usersToApprove as $key => $user) {
                        ?>
                        <tr>
                            <td class="width30">
                                <input type="checkbox" class='list_checkbox' name="usertoupprove[<?php echo $user->id; ?>]" value="yes" />
                            </td>
                            <td>
                                <?php echo CHtml::encode($user->user->person->Last_Name) . ' ' . CHtml::encode($user->user->person->First_Name); ?>
                                <br/><span class="grey font10"><?php echo CHtml::encode($user->user->person->Email); ?></span>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                    </tbody>
                </table>
            </form>
        </div>
        <button class="button" id="add_user">Approve</button>
        <button class="button" id="reject_user">Reject</button>
        <script type="text/javascript">
            $(document).ready(function() {
                $('#add_user').click(function() {
                    <?php
                        if ($enableAddUser) {
                            echo "$('#users_to_add_form').submit();";
                        } else {
                            echo "$('#dialogmodal a').attr('href', '/myaccount?tab=service');";
                            echo "show_dialog('The Client has exceeded the number of Users in its Service Level. Please navigate to Service Levels and add the number of Users required. Thank you.', 540);";
                        }
                    ?>

                });
                $('#reject_user').click(function() {
                    $('#users_to_add_form').attr('action', '/myaccount/rejectusers').submit();
                });
            });
        </script>
    </div>
    <?php } ?>
    </div>
    <div id="service_levels_sidebar" style="margin-top: 5px; <?php if ($tab != 'tab7') echo " ;display: none;";?>">
        <input type="hidden" id="timeis_up" value="<?=$timeis_up?>">
        <?php
        if ($client_admin) {
            if ($amountToPay > 0) {
                ?>
                <div class="sidebar_item">
                    <p class="fs15 mbot0">
                        Client subscription has been revised to
                        include the added services at left. Total added cost: $<?php echo number_format($amountToPay, 2);?>
                    </p>
                    <div>
                        <div class="row">
                            <input type="radio" checked="checked" name="service_payment_type" data-amount="<?php echo $amountToPay;?>" id="service_payment_type1" class="service_payment_type" value="1"/>
                            <label for="service_payment_type1">Manual Payment - $<?php echo number_format($amountToPay, 2);?>.<br/>
                                <div style="font-size: 12px"></div></label>
                            <div class="service_payment_type_descr">
                                Download and print the invoice
                                provided <a href="#" id="download_invoice">here</a>  and forward your payment to the address on the invoice. Payment is due in our
                                office within 3 days when the service will be updated to your specifications.

                            </div>
                        </div>
                        <div class="row">
                            <input type="radio" name="service_payment_type" data-amount="<?php echo $amountToPay;?>" id="service_payment_type2" class="service_payment_type" value="2" />
                            <label for="service_payment_type2">Online Payment - $<?php echo number_format($amountToPay, 2);?>.<br/>
                                <div style="font-size: 12px"></div></label>
                            <div class="service_payment_type_descr">
                                Pay now using a credit card.
                            </div>
                        </div>

                    </div>
                    <div class="center">
                        <button id="submit_new_settings" class="button">Continue</button>
                        <a href="<?php echo Yii::app()->request->baseUrl; ?>/myaccount/rejectservises" class="button">Cancel</a>
                    </div>
                </div>
                <?php
            }
            ?>
        <?php
            if ($amountToPay < 0) {
            ?>
                <div class="sidebar_item">
                    <p class="fs15">
                        You are about to change current settings to lower settings.
                        Remaining amount will be used for renewing expiration date
                        to <?php echo Helper::convertDate($pending_client_service_settings->getLongerExpirationDate($amountToPay, $expirationDate)); ?>
                    </p>
                    <div class="center">
                        <a href="<?php echo Yii::app()->request->baseUrl; ?>/myaccount/applyservises" class="button">Apply</a>
                        <a href="<?php echo Yii::app()->request->baseUrl; ?>/myaccount/rejectservises" class="button">Cancel</a>
                    </div>
                </div>
            <?php
            }
             if ($client_service_settings->checkShowMonthlyPaymentAlert()) {
                $this->renderPartial('application.views.myaccount.payment_block', array(
                    'client_service_settings' => $client_service_settings,
                    'summary_sl_settings'=> $summary_sl_settings
                ));
             }
         }
        ?>
    </div>
    <div id="document_thumbnail" style="margin-top: 80px; <?php if ($tab != 'tab8') echo " ;display: none;";?>">
        <span>Document Preview</span>
        <br/>
        <span id="fileinfo_line"> </span>
        <div id="document_thumbnail_file">
            <span>Choose Document from the List to Preview</span>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#account_submit').on('click',function() {
            $('.wrapper')
                .find('form:visible input:submit')
                .trigger('click');
        });

        $('.tabs li a').click(function() {
            var tabNumber = $(this).attr('href');
            if (tabNumber == '#tab1') {
                $('#my_profile_sidebar_block').show();
            } else {
                $('#my_profile_sidebar_block').hide();
            }

            if (tabNumber == '#tab4') {
                $('#company_info_sidebar_block').show();
            } else {
                $('#company_info_sidebar_block').hide();
            }

            if (tabNumber == '#tab7') {
                $('#service_levels_sidebar').show();
            } else {
                $('#service_levels_sidebar').hide();
            }

            if (tabNumber == '#tab8') {

                var founded=$("#user_allowed");
                if(founded.data('id')=='allowed'){
                $('#document_thumbnail').show();
                }
            } else {
                $('#document_thumbnail').hide();
            }

            if (tabNumber == '#tab3') {
                $('#manage_users_sidebar_block').show();
            } else {
                $('#manage_users_sidebar_block').hide();
            }
        });

        new MyAccountDocReassign;

        <?php
            if ($show_new_company_form) {
                echo "setTimeout(function() {
                         show_modal_box('#newcompanymodal', 260, 50);
                      }, 200);";
            }

            if ($show_bank_account_form) {
                echo "setTimeout(function() {
                         show_modal_box('#new_bank_account', 260, 50);
                      }, 200);";
            }

            if ($show_projects_form) {
                echo "setTimeout(function() {
                         show_modal_box('#new_project_box', 260, 50);
                      }, 200);";
            }

            if ($show_po_formatting_form) {
                echo "setTimeout(function() {
                         show_modal_box('#po_formatting_block', 557, 70);
                      }, 200);";
            }

            if ($show_usr_appr_form) {
                echo "setTimeout(function() {
                         show_persistent_modal_box('#usr_approval_block', 557, 70);
                      }, 200);";
            }
        ?>
    });
</script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/bank_acct_nums.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/projects.js"></script>
<?php if ($client_admin) { ?>
    <?php $this->renderPartial('application.views.widgets.newbankaccount', array('acctId' => $acctId)); ?>
    <?php $this->renderPartial('application.views.widgets.newproject', array('projectId' => $projectId)); ?>
    <?php $this->renderPartial('application.views.widgets.po_formatting_form', array('poFormattingId' => $poFormattingId)); ?>
    <?php $this->renderPartial('application.views.widgets.usr_approval_value_form',array('client'=>$client)); ?>
    <?php $this->renderPartial('application.views.widgets.add_service_level', array(
        'client_service_settings' => $client_service_settings,
        'serviceLevels'=>$serviceLevels,
        'summary_sl_settings'=>$summary_sl_settings,
        'level_ids'=>$level_ids
    )); ?>
    <?php $this->renderPartial('application.views.widgets.credit_card_info'); ?>
    <?php $this->renderPartial('application.views.widgets.ask_using_last_card'); ?>
    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/bank_acct_nums_admin.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/projects_admin.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/service_level_client_admin.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/ajaxupload.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.tabs li a').click(function() {
                var tabNumber = $(this).attr('href');

                if (tabNumber == '#tab1') {
                    $('#account_submit').attr('data', 'tab1').show();
                } else if (tabNumber == '#tab2') {
                    $('#account_submit').attr('data', 'tab2').show();
                } else if (tabNumber == '#tab3') {
                    $('#account_submit').attr('data', 'tab3').hide();
                } else if (tabNumber == '#tab4') {
                    $('#account_submit').attr('data', 'tab4').hide();
                } else if (tabNumber == '#tab5') {
                    $('#account_submit').attr('data', 'tab5').hide();
                } else if (tabNumber == '#tab6') {
                    $('#account_submit').attr('data', 'tab6').hide();
                } else if (tabNumber == '#tab7') {

                    $('#account_submit').attr('data', 'tab7');//.hide();
                    if ($('.pending').length>0 ) {
                        $('#account_submit').show();
                    } else {$('#account_submit').hide();}

                } else if (tabNumber == '#tab8') {
                    $('#account_submit').attr('data', 'tab8').show();
                } else if (tabNumber == '#tab9') {
                    $('#account_submit').attr('data', 'tab9').hide();
                }
            });

            new BankAcctNumsAdmin;
            new ProjectsAdmin('<?php echo $enableAddProject ? 1 : 0; ?>');
            new ServiceLevelClientAdmin('<?php echo Yii::app()->config->get('STRIPE_PUBLISH_KEY'); ?>');
        });
    </script>

    <div class="modal_box" id="askcvv2" style="display:none;">
        <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
        <h2>Enter CVV2</h2>
        <label class="required" for="doc_to_user_email">
            CVV2 code from your Credit Card
        </label>
        <input id="askcvv2_input" class="txtfield" type="text" name="askcvv2_input">
        <div class="errorMessage" style="display: none;">Ivalid CVV2 code</div>
        <div class="center">
            <button id="submit_cvv2" class="button" type="submit">Submit</button>
        </div>
    </div>
<?php } else { ?>

    <script type="text/javascript">
        $(document).ready(function() {
            $('.tabs li a').click(function() {
                var tabNumber = $(this).attr('href');

                if (tabNumber == '#tab1') {
                    $('#account_submit').attr('data', 'tab1').show();
                } else if (tabNumber == '#tab2') {
                    $('#account_submit').attr('data', 'tab2').show();
                } else if (tabNumber == '#tab3') {
                    $('#account_submit').attr('data', 'tab3').hide();
                } else if (tabNumber == '#tab4') {
                    $('#account_submit').attr('data', 'tab4').hide();
                } else if (tabNumber == '#tab5') {
                    $('#account_submit').attr('data', 'tab5').hide();
                } else if (tabNumber == '#tab6') {
                    $('#account_submit').attr('data', 'tab6').hide();
                } else if (tabNumber == '#tab7') {
                    $('#account_submit').attr('data', 'tab7');//.hide();
                    if ($('.pending').length>0 ) {
                        $('#account_submit').show();
                    } else {$('#account_submit').hide();}
                } else if (tabNumber == '#tab8') {
                    $('#account_submit').attr('data', 'tab8').show();
                } else if (tabNumber == '#tab9') {
                     $('#account_submit').attr('data', 'tab9').hide();
                }
            });




            new BankAcctNums;
            new Projects;


        });
    </script>
<?php }?>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/user_doc_reassign.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/myaccount_doc_reassign.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/document_view.js"></script>

<?php
Yii::app()->clientScript->registerScriptFile('/js/mousewheel.js', CClientScript::POS_HEAD);
?>