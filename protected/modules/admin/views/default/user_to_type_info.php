<span class="sidebar_block_header">User Info:</span>
<ul class="sidebar_list">
    <li>Username: <?php echo $user->User_Login; ?></li>
    <li>Active: <?php echo $user->Active ? 'Yes' : 'No'; ?></li>
    <li>Last Login: <?php  echo $user->Last_Login ? Helper::convertDateString($user->Last_Login) : ''; ?></li>
</ul>
<div class="grid-view" style="padding-top: 11px;" >
    <table class="items mbot0">
        <thead>
        <tr>
            <th><span>Company Name / Project Name</span></th>
        </tr>
        </thead>
    </table>
    <div style="height: 280px; overflow: auto">
        <table class="items" id="user_to_<?php echo $tab; ?>_projects">
            <tbody>
            <?php
            if (count($projects)) {
                foreach ($projects as $project) {
                    echo '<tr>';
                    echo '<td>' . CHtml::encode($project) .  '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr>';
                echo '<td>Companies were not found</td>';
                echo '</tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
