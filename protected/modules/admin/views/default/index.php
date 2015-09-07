<?php

$this->breadcrumbs=array(
	'Admin',
    'DB Admin Panel'=>array('/admin/users'),
);

if (Yii::app()->user->userType == Users::DB_ADMIN) {
    $this->breadcrumbs=array(
        ''=>array('/'), //empty item
    );

} else {
    $this->breadcrumbs=array(
        'Admin',
    );
}
?>


<?
if ($tab == 'cl_adm_change') {
    $tab = 'tab1';
} elseif ($tab == 'us_cl_assign') {
    $tab = 'tab2';
} elseif ($tab == 'doc_reassign') {
    $tab = 'tab3';
} elseif ($tab == 'us_appr_value') {
    $tab = 'tab4';
} elseif ($tab == 'user_act_mgmt') {
    $tab = 'tab5';
} elseif ($tab == 'user_type_mgmt') {
    $tab = 'tab6';
} elseif ($tab == 'reg_requests') {
    $tab = 'tab7';
} elseif ($tab == 'empty_companies') {
    $tab = 'tab8';
} elseif ($tab == 'client_act_mgmt') {
    $tab = 'tab9';
} elseif ($tab == 'service_level') {
    $tab = 'tab10';
} elseif ($tab == 'service_settings') {
    $tab = 'tab11';
} elseif ($tab == 'support_requests' ) {
    $tab = 'tab12';
}

else {
    $tab = 'tab1';
}

$show_save_button = '';
$button_class = 'button';
if ($tab == 'tab2') {
    $show_save_button = 'style="display:none;"';
    }
if ($tab == 'tab10') {
    $button_class = 'not_active_button';
    }
if (Yii::app()->user->id=='db_admin') {
    $button_class = 'button';
}

?>
<h1 class="admin_header">System administration</h1>
    <input type="hidden" id="user_mode" value="<?= Yii::app()->user->id=='db_admin' ? '1' : '2' ?>">
<div class="account_manage">
    <button class="<?=$button_class?>" id="admin_submit" data="<?php echo $tab; ?>" <?php echo $show_save_button; ?>>Save</button>
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

    $this->widget('CTabView', array (
        'activeTab'=>$tab,
        'cssFile'=>'/css/jquery.yiitab3.css',
        'tabs'=>array(
            'tab1'=>array(
                'title'=>'Cl. User Type Mgmt.',
                'view'=>'tabs/user_type_management',
            ),
            'tab2'=>array(
                'title'=>'User/Project Assign',
                'view'=>'tabs/user_client_assign',
            ),
            'tab3'=>array(
                'title'=>'Doc. Reassignment',
                'view'=>'tabs/doc_reassign',
            ),
            'tab4'=>array(
                'title'=>'User Approval Value',
                'view'=>'tabs/user_approval_value',
                'data'=>array('auto_loaded_data'=>$auto_loaded_tabs['client_users_list_appr_value']['auto_loaded_data'],
                              'client'=>$auto_loaded_tabs['client_users_list_appr_value']['client'])
            ),
            'tab5'=>array(
                'title'=>'User Active Mgmt.',
                'view'=>'tabs/user_active_mgmt',
            ),
            'tab6'=>array(
                'title'=>'User Type Mgmt.',
                'view'=>'tabs/user_type_mgmt',
            ),
            'tab7'=>array(
                'title'=>'Registration Requests',
                'view'=>'tabs/registration_requests',
                'data'=>array('usersToApprove' => $usersToApprove),
            ),
            'tab12'=>array(
                'title'=>'Support Requests',
                'view'=>'tabs/support_requests',
                'data'=>array('support_requests' => $support_requests),
            ),
            'tab8'=>array(
                'title'=>'Comp. Without Users',
                'view'=>'tabs/empty_companies',
            ),
            'tab9'=>array(
                'title'=>'Client Active Mgmt.',
                'view'=>'tabs/client_active_mgmt',
            ),
            'tab10'=>array(
                'title'=>'Service Level Mgmt.',
                'view'=>'tabs/service_level',
                'data'=>array('auto_loaded_data'=>$auto_loaded_tabs['client_service_level_settings']['auto_loaded_data'],
                              'client'=>$auto_loaded_tabs['client_service_level_settings']['client'])
            ),
            'tab11'=>array(
                'title'=>'Service Level Settings',
                'view'=>'tabs/service_level_settings',
                'data'=>array('serviceLevelSettings' => $serviceLevelSettings),
            ),
        ),
    ));
    ?>
</div>
<div class="sidebar_right">
    <div id="clients_list_sidebar_admin_change" style="margin-top: 14px; <?php if ($tab != 'tab1') echo "display: none;";?>">
        <label class="free_label" for="company_name_input_admin_change">
            Co. Name:
        </label>
        <input placeholder="Narrow by Company Name" id="company_name_input_admin_change" type="text" maxlength="45" name="company_name_input_admin_change" class="narrow_by_com_name_admin">
        <div id="clients-grid" class="grid-view">
            <table class="items mbot0">
                <thead>
                <tr>
                    <th class="width180"><span>Company Name</span></th><th><span>ID</span></th></tr>
                </thead>
            </table>
            <div style="height: 400px; overflow: auto">
                <table class="items" id="clients-grid-table">
                    <tbody>
                    <?php
                    $this->beginClip('companiesListHTML');
                    if (count($clientsList)) {
                        foreach ($clientsList as $id => $companyName) {
                            echo '<tr id="client' . $id  . '">';
                            echo '<td class="width180">' . CHtml::encode($companyName) .  '</td><td>' . $id . '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr id="client0">';
                        echo '<td clospan="2">Companies were not found</td>';
                        echo '</tr>';
                    }
                    $this->endClip();
                    echo $this->clips['companiesListHTML'];
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div id="clients_list_sidebar_user_assign" style="margin-top: 66px; <?php if ($tab != 'tab2') echo "display: none;";?>">
        <label class="free_label" for="company_name_input_user_assign">
            Co. Name:
        </label>
        <input placeholder="Narrow by Company Name" id="company_name_input_user_assign" type="text" maxlength="45" name="company_name_input_user_assign" class="narrow_by_com_name_admin">
        <div id="clients-grid_user_assign" class="grid-view" style="padding-top: 11px;" >
            <table class="items mbot0">
                <thead>
                <tr>
                    <th class="width180"><span>Company Name</span></th><th><span>ID</span></th></tr>
                </thead>
            </table>
            <div style="height: 200px; overflow: auto">
                <table class="items" id="clients_grid_table_user_assign">
                    <tbody>
                    <?php
                    echo $this->clips['companiesListHTML'];
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="clients_projects_grid_user_assign" class="grid-view">
            <table class="items mbot0">
                <thead>
                <tr>
                    <th class="width180"><span>Project Name</span></th><th><span>ID</span></th></tr>
                </thead>
            </table>
            <div style="height: 125px; overflow: auto">
                <table class="items" id="clients_projects_grid_table_user_assign">
                    <tbody>
                        <tr id="project0">
                            <td clospan="2">Choose company to view projects</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div id="document_thumbnail" style="<?php if ($tab != 'tab3') echo "display: none;";?>"">
        <span>Document Preview</span>
    <br/>
    <span id="fileinfo_line"> </span>
        <div id="document_thumbnail_file">
            <span>Choose Document from the List to Preview</span>
        </div>
    </div>
    <div id="clients_list_sidebar_appr_value" style="margin-top: 14px; <?php if ($tab != 'tab4') echo "display: none;";?>">
        <label class="free_label" for="company_name_input_appr_value">
            Co. Name:
        </label>
        <input placeholder="Narrow by Company Name" id="company_name_input_appr_value" type="text" maxlength="45" name="company_name_input_appr_value" class="narrow_by_com_name_admin">
        <div id="clients-grid_appr_value" class="grid-view">
            <table class="items mbot0">
                <thead>
                <tr>
                    <th class="width180"><span>Company Name</span></th><th><span>ID</span></th></tr>
                </thead>
            </table>
            <div style="height: 400px; overflow: auto">
                <table class="items" id="clients_grid_table_appr_value">
                    <tbody>
                    <?php
                    echo $this->clips['companiesListHTML'];
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div id="user_active_sidebar" style="<?php if ($tab != 'tab5') echo "display: none;";?>">

    </div>
    <div id="user_type_sidebar" style="<?php if ($tab != 'tab6') echo "display: none;";?>">

    </div>
    <div id="users_to_approve_company" style="<?php if ($tab != 'tab7') echo "display: none;";?>">

    </div>
    <div id="empty_company_info" style="<?php if ($tab != 'tab8') echo "display: none;";?>">

    </div>
    <div id="client_active_sidebar" style="<?php if ($tab != 'tab9') echo "display: none;";?>">

    </div>
    <div id="service_levels_sidebar" style="<?php if ($tab != 'tab10') echo "display: none;";?> margin-top: 14px;">
        <label class="free_label" for="company_name_input_service_levels">
            Co. Name:
        </label>
        <input placeholder="Narrow by Company Name" id="company_name_input_service_levels" type="text" maxlength="45" name="company_name_input_service_levels" class="narrow_by_com_name_admin">
        <div id="clients-grid-service-levels" class="grid-view">
            <table class="items mbot0">
                <thead>
                <tr>
                    <th class="width180"><span>Company Name</span></th><th><span>ID</span></th>
                </tr>
                </thead>
            </table>
            <div style="height: 400px; overflow: auto">
                <table class="items" id="clients-grid-table-service-levels">
                    <tbody>
                    <?php
                    echo $this->clips['companiesListHTML'];
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(function(){
        $('#account_submit').on('click',function(){
            $('.wrapper')
                .find('form:visible input:submit')
                .trigger('click');
        });
    });
</script>
<script type="text/javascript">
        $(document).ready(function() {
            new UsersToApprove;
            new ClientsAdminChange;
            new UserClientAssign;
            new UserApprovalValue;
            new UsersActiveMgmt;
            new UsersTypeMgmt;
            new UsersDocReassign;
            new EmptyCompanies;
            new ClientsActiveMgmt;
            new ServiceLevelAdmin();
            new SupportRequests;

            $('#db_fake_breadcrumb').show();


            $('.tabs li a').click(function() {
                var tabNumber = $(this).attr('href');

                if (tabNumber == '#tab1') {
                    $('#admin_submit').attr('data', 'tab1').show();
                } else if (tabNumber == '#tab2') {
                    $('#admin_submit').attr('data', 'tab2').hide();
                } else if (tabNumber == '#tab3') {
                    $('#admin_submit').attr('data', 'tab3').show();
                } else if (tabNumber == '#tab4') {
                    $('#admin_submit').attr('data', 'tab4').show();
                } else if (tabNumber == '#tab5') {
                    $('#admin_submit').attr('data', 'tab5').show();
                } else if (tabNumber == '#tab6') {
                    $('#admin_submit').attr('data', 'tab6').show();
                } else if (tabNumber == '#tab7') {
                    $('#admin_submit').attr('data', 'tab7').show();
                } else if (tabNumber == '#tab8') {
                    $('#admin_submit').attr('data', 'tab8').hide();
                } else if (tabNumber == '#tab9') {
                    $('#admin_submit').attr('data', 'tab9').show();
                } else if (tabNumber == '#tab10') {
                    $('#admin_submit').attr('data', 'tab10').show();
                } else if (tabNumber == '#tab11') {
                    $('#admin_submit').attr('data', 'tab11').show();
                }

                if (tabNumber == '#tab7') {
                    $('#users_to_approve_company').show();
                } else {
                    $('#users_to_approve_company').hide();
                }

                if (tabNumber == '#tab1') {
                    $('#clients_list_sidebar_admin_change').show();
                } else {
                    $('#clients_list_sidebar_admin_change').hide();
                }

                if (tabNumber == '#tab3') {
                    $('#document_thumbnail').show();
                } else {
                    $('#document_thumbnail').hide();
                }

                if (tabNumber == '#tab2') {
                    $('#clients_list_sidebar_user_assign').show();
                } else {
                    $('#clients_list_sidebar_user_assign').hide();
                }

                if (tabNumber == '#tab4') {
                    $('#clients_list_sidebar_appr_value').show();
                } else {
                    $('#clients_list_sidebar_appr_value').hide();
                }

                if (tabNumber == '#tab5') {
                    $('#user_active_sidebar').show();
                } else {
                    $('#user_active_sidebar').hide();
                }

                if (tabNumber == '#tab6') {
                    $('#user_type_sidebar').show();
                } else {
                    $('#user_type_sidebar').hide();
                }

                if (tabNumber == '#tab8') {
                    $('#empty_company_info').show();
                } else {
                    $('#empty_company_info').hide();
                }

                if (tabNumber == '#tab9') {
                    $('#client_active_sidebar').show();
                } else {
                    $('#client_active_sidebar').hide();
                }

                if (tabNumber == '#tab10') {
                    $('#service_levels_sidebar').show();
                } else {
                    $('#service_levels_sidebar').hide();
                }
            });
        });
</script>

<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/users_to_approve.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/clients_admin_change.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/user_client_assign.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/user_approval_value.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/user_active_mgmt.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/user_doc_reassign.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/user_type_mgmt.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/empty_companies.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/document_view.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/client_active_mgmt.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/service_level_admin.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/support_requests.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/asa_datepicker.js"></script>
<?php
   Yii::app()->clientScript->registerScriptFile('/js/mousewheel.js', CClientScript::POS_HEAD);
?>