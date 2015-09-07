<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 5/22/15
 * Time: 10:29 AM
 */?>
<?
$user = Users::model()->with('person')->findByPk($document->User_ID);
$client = Clients::model()->with('company')->findByPk($document->Client_ID);
$project = Projects::model()->findByPk($document->Project_ID);

$userClientRow = UsersClientList::model()->findByAttributes(array(
    'User_ID' => Yii::app()->user->userID,
    'Client_ID' => Yii::app()->user->clientID,
));

?>
<div id="document_info_block" style="margin-top:10px; ">

    <div style="border: 1px solid #DDDDDD; padding-left: 20px;">

        <table>
            <tr>
                <th>Created:</th> <td style="width: 265px;"> <?=$document->Created; ?> </td>
            </tr>

            <tr>
                <th>By: </th> <td style="width: 265px;"> <?= CHtml::encode($user->person->First_Name).' '.CHtml::encode($user->person->Last_Name).'('.CHtml::encode($userClientRow->User_Type).')'; ?> </td>
            </tr>

            <tr>
                <th>Client/Project</th> <td style="width: 265px;"> <?=CHtml::encode($client->company->Company_Name).'/'.CHtml::encode($project->Project_Name); ?> </td>
            </tr>

        </table>

    </div>
</div>