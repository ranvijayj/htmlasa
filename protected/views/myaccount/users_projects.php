<h3>User's Projects:</h3>
<table id="other_table">
    <tr style="background-color: #fff";>
        <td class="width30">
            <input type="checkbox" class='check_uncheck_all' name="check_uncheck_all" value="no" />
        </td>
        <td>
           <b> Check/uncheck all<b>
        </td>
    </tr>
</table>
<form action="/myaccount/approveusersprojects" method="post" id="users_projects_form">
<div id="users_projects_list">

        <table id="list_table">
            <tbody>


            <?php
            foreach ($clientProjects as $key => $project) {
                $checked = "";
                $color = '';
                if (in_array($project->Project_ID, $userProjects)) {
                    $checked = "checked='checked'";
                    $color = 'style="background-color: #eee"';
                }
                ?>
                <tr <?php echo $color; ?>>
                    <td class="width30">
                        <input type="checkbox" class='list_checkbox' name="projecttoupprove[<?php echo $project->Project_ID; ?>]" value="yes" <?php echo $checked; ?> />
                    </td>
                    <td>
                        <?php echo CHtml::encode($project->Project_Name); ?>
                    </td>
                </tr>
            <?php
            }
            ?>
            </tbody>
        </table>
        <input type="hidden" value="<?php echo $userId; ?>" name="user_id">
</div>
<div class="change_user_type left">
        <label for="UsersClientList_User_Type">User Type:</label>
        <?php
         if ($nativetype=='User' || $nativetype=='Admin' || $nativetype=='Data Entry Clerk') {echo CHtml::activeDropDownList($userClientRow,'User_Type',array(
            UsersClientList::USER=>UsersClientList::USER,
            UsersClientList::PROCESSOR=>UsersClientList::PROCESSOR,
            UsersClientList::APPROVER=>UsersClientList::APPROVER,
            UsersClientList::CLIENT_ADMIN=>UsersClientList::CLIENT_ADMIN,
        ));} else {
                    echo "<select disabled>...</select>";
         }

        ?>
</div>
<div class="change_user_approver_value right">
        <label for="UsersClientList_User_Type">Approver Level:</label>
        <?php
        $htmlOptions = array();
        if ($userClientRow->User_Approval_Value < 2 || $nativetype=='DB Admin' ) {
            $htmlOptions['disabled'] = 'disabled';
            $htmlOptions['value'] = '';
        }
        echo CHtml::activeTextField($userClientRow,'User_Approval_Value', $htmlOptions);
        ?>
</div>
<div class="clear"></div>
</form>
<button class="button" id="save_user_projects">Save</button>
<button class="button" id="remove_user">Remove User</button>