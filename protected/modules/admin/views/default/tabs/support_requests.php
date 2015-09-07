<label class="free_label" for="user_to_approve_last_name">
    Users:
</label>
<input placeholder="Narrow by Last Name" id="user_to_approve_last_name" type="text" maxlength="45" name="user_to_approve_last_name" style="width: 220px;">
<div id="support_requests_div" class="grid-view">

    <div style="height: 400px; overflow: auto">
        <table class="items" id="support_requests_table">
            <thead>
            <tr>
                <th><span>ID</span></th> <th ><span>Name</span></th> <th><span>Login </span></th> <th><span>Email</span></th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (count($support_requests)) {
                foreach ($support_requests as $support_request) {
                    $user = Users::model()->findByAttributes(array(
                       'User_Login'=> $support_request->User_Login
                    ));
                    echo '<tr data-request="' . $support_request->Request_ID  . '">';
                    echo '<td >'.$support_request->Request_ID.'</td>';
                    echo '<td >' . CHtml::encode($user->person->First_Name . ' '  . $user->person->Last_Name) .  '</td>
                          <td class="login">'. $user->User_Login .'</td>
                          <td >' . $user->person->Email .  '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr id="user0">';
                echo '<td clospan="3">Support requests were not found</td>';
                echo '</tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

