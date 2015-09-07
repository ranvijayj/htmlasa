
<p>Login:
<?php echo CHtml::encode($user->User_Login);?>
</p>
<p>User:

    <?php echo CHtml::encode($user->person->First_Name);?>&nbsp;&nbsp;
    <?php echo CHtml::encode($user->person->Last_Name);?>&nbsp;&nbsp;(<?php echo $user->User_ID;?>)</p>
<p>Client: <?php echo ($clientId > 0 ? (CHtml::encode($client->company->Company_Name) . '&nbsp;&nbsp;(' . $client->Client_ID . ')') : 'No company');?></p>
<p>Fed ID: <?php echo ($clientId > 0 ? (CHtml::encode($client->company->Company_Fed_ID) . '&nbsp;&nbsp;') : 'No company');?></p>

<?php
    if ($projectId === 'all') {
        ?>
        <p>Project: All</p>
        <?php
    } else if ($projectId == 0) {
        ?>
        <p>Project:  No project</p>
        <?php
    } else {
        ?>
        <p>Project: <?php echo CHtml::encode($project->Project_Name);?>&nbsp;&nbsp;(<?php echo $project->Project_ID;?>)</p>
        <?php
    }
?>
<p>Approver Level: <?php echo $userClientRow->User_Approval_Value;?></p>
<p>Type:  <?php echo CHtml::encode($userClientRow->User_Type);?></p>
<?php
if ($projectId != 'all') {
    ?>
    <hr/>
    <p>Break Character:  <?php echo CHtml::encode($project->COA_Break_Character);?></p>
    <p>Break Number:  <?php echo CHtml::encode($project->COA_Break_Number);?></p>
<?php
}
?>
