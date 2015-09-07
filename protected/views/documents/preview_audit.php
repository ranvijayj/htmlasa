<div id="audit_doc_id" data-id="<?=$doc_id?>"> </div>
<table style="width: 500px;">


        <th>Date Time</th><th>Action</th><th>User</th><th>Approval Value</th>

 <?php foreach($audits as $audit) {?>


     <tr>
         <td style="width: 145px;"><?=$audit->Event_Date; ?> </td>
            <td style="width: 65px;"><?=$audit->Event_Type; ?> </td>
                <? $user = Users::model()->with('person')->findByPk($audit->Event_User_ID); ?>
                <td style="width: 120px;"><?=$user->person->First_Name.' '.$user->person->Last_Name; ?> </td>
                    <td style="width: 60px;"><?=$audit->User_Appr_Value; ?> </td>


     </tr>


<?php }?>
</table>
File Name: <?=$doc_name;?>


