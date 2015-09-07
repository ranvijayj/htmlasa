<h2 class="left">Manage Users</h2>


<?php $restricted_users_array=array('user','data_entry_clerk');
$alowed_users_array=array('client_admin','db_admin','admin','approver','processor');
if(in_array($user_role,$alowed_users_array)){
echo '<div id="user_allowed" data-id="allowed"></div>';
?>
<button class="button right" id="add_user_btn">+ Add User</button>

<br />

<div id="users-grid" class="grid-view">
    <table class="items">
        <thead>
        <tr>
            <th><span>Name</span></th><th><span>Email</span></th><th><span>Admin</span></th>
        </tr>
        </thead>
        <tbody>
            <?php
                $num = 10;
                $total = intval((count($client_users) - 1) / $num) + 1;
                $page = intval($users_page);
                if(empty($page) or $page < 0) $page = 1;
                if($page > $total) $page = $total;
                $start = $page * $num - $num;
                foreach ($client_users as $key => $user) {
                    if ($key >= $start && $key <= ($start + $num - 1)) {
                        $admin = '';
                        if ($this->clientAdmins[$user->User_ID] == 1) {
                            $admin = 'Y';
                        }
                        echo '<tr id="user' . $user->User_ID  . '">';
                        echo '<td>' . CHtml::encode($user->person->First_Name) . ' '  . CHtml::encode($user->person->Last_Name) .  '</td><td>' . CHtml::encode($user->person->Email) .  '</td><td>' . $admin . '</td>';
                        echo '</tr>';
                    }
                }

                 if (count($client_users) == 0) {
                     echo '<tr id="user0">';
                     echo '<td colspan="3">Users were not found</td>';
                     echo '</tr>';
                 }
            ?>
        </tbody>
    </table>
    <?php
       Helper::createPaginationLinks('/myaccount/index?tab=man_users', 'users_page', $users_page, count($client_users), $num);
    ?>
</div>
<?php if ($client_admin) { ?>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#users-grid tbody tr').click(function() {
                var adminId = [];

                var userId = $(this).attr('id');
                userId = userId.slice(4);
                $('#users-grid tbody tr').css('background', 'none');
                $(this).css('background-color', '#dFdDdD');
                if (adminId[userId] != 1) {
                    $('#manage_users_sidebar_block').prepend("<div class='loadinng_mask'></div>");
                    $.ajax({
                        url: "/myaccount/getusersprojectslist",
                        data: {userId: userId},
                        type: "POST",
                        async: false,
                        success: function(data) {
                            $('#manage_users_sidebar_block #users_projects').html(data).show();
                            $('#manage_users_sidebar_block #users_to_add').hide();

                            $("#users_projects_list .list_checkbox").click(function (event) {
                                event.stopPropagation();
                                var checkbox = $(this);
                                setTimeout(function() {
                                    if (!checkbox.attr('checked')) {
                                        checkbox.parent().parent().css({"backgroundColor":"#fff"});
                                    } else {
                                        var row = checkbox.parent().parent();
                                        row.css({"backgroundColor":"#eee"});
                                    }
                                }, 10);
                            });

                            $('#users_projects_list #list_table tbody tr').click(function (event) {
                                event.stopPropagation();
                                $(this).find(".list_checkbox").click();
                            });

                            $('#save_user_projects').click(function() {
                                var checkedCount = $("#users_projects_list .list_checkbox:checked").length;
                                if (checkedCount > 0) {
                                    $('#users_projects_form').submit();
                                } else {
                                    show_alert("User must be linked at least to one Project!", 420);
                                }
                            });


                            $('#remove_user').click(function() {
                                if (userId != false && userId != '0') {
                                    $('#dialogmodal a').attr('href', '/myaccount/removeuser?id='+userId);
                                    show_dialog('Do you want to remove this user from the list?', 420);
                                }
                            });
                            $('#manage_users_sidebar_block .loadinng_mask').remove();

                            $('#UsersClientList_User_Approval_Value').blur(function() {
                                var val = parseInt($(this).val());
                                var userType = $('#UsersClientList_User_Type').val();
                                if (isNaN(val)) {
                                    val = 0;
                                }

                                if (val < 0) {
                                    val = 0;
                                } else if (val > 100) {
                                    val = 100;
                                }

                                if (val < 2 && (userType == 'Approver' || userType == 'Client Admin')) {
                                    val = 2;
                                    $(this).val(val).attr('disabled', false);
                                } else if (val >= 100 && (userType == 'Approver' || userType == 'Client Admin')) {
                                    $(this).val(val).attr('disabled', false);
                                } else if (userType == 'User' || userType == 'Processor') {
                                    val = '';
                                    $(this).val(val).attr('disabled', true);
                                }
                            });

                            $('#UsersClientList_User_Type').change(function() {
                                var userType = $(this).val();
                                var val = parseInt($('#UsersClientList_User_Approval_Value').val());

                                if (isNaN(val)) {
                                    val = 0;
                                }

                                if (val < 0) {
                                    val = 0;
                                } else if (val > 100) {
                                    val = 100;
                                }

                                if (val < 2 && (userType == 'Approver' || userType == 'Client Admin')) {
                                    val = 2;
                                    $('#UsersClientList_User_Approval_Value').val(val).attr('disabled', false);
                                } else if (val >= 100 && (userType == 'Approver' || userType == 'Client Admin')) {
                                    $(this).val(val).attr('disabled', false);
                                } else if (userType == 'User' || userType == 'Processor') {
                                    val = '';
                                    $('#UsersClientList_User_Approval_Value').val(val).attr('disabled', true);
                                }
                            });

                            $(".check_uncheck_all").click(function (event) {
                               event.stopPropagation();
                                if (!$(this).attr('checked')) {
                                    $('#list_table tr').each(function() {
                                        $(this).animate({"backgroundColor":"#fff"},200);
                                    });
                                    $(".list_checkbox").each(function() {
                                        $(this).removeAttr('checked');
                                    });
                                } else {
                                    $('#list_table tr').each(function() {
                                        $(this).animate({"backgroundColor":"#eee"},200);
                                    });
                                    $(".list_checkbox").each(function() {
                                        $(this).attr('checked', 'checked');
                                    });
                                }
                            });
                        }
                    });
                } else {
                    $('#manage_users_sidebar_block #users_to_add').show();
                    $('#manage_users_sidebar_block #users_projects').hide();
                }
            });

            new AddUserBox('<?php echo $enableAddUser ? 1 : 0;?>');
        });
    </script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/add_user.js"></script>
<?php } else { ?>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#users-grid tbody tr').click(function() {
                $('#users-grid tbody tr').css('background', 'none');
                $(this).css('background-color', '#dFdDdD');
            });
            $('#add_user_btn').remove();




        });
    </script>
<?php }?>
<?php }?>
