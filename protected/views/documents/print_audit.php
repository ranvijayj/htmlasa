<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Document Approval Audit Trail</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquerymin.js"></script>
    <style>
        table {
            border-collapse: collapse;
            border: 1px solid #000;
        }

        td, th {
            border: 1px solid #000;
            font-size: 10px;
        }
    </style>
</head>
<body>
<div id="print_audit_wrapper" style="
        width: 700px;
        margin: 50px auto;
        padding: 20px;

        ">
    <div id="audit_doc_id" data-id="<?=$doc_id?> "> </div>





        <h3>Audit trail information for document: <?=$document->Document_Type;?> from <?=$document->Created;?>   </h3>
        <table style="">

         <th>Date Time</th><th>Action</th><th>User</th><th>Approval Value</th>

         <?php foreach($audits as $audit) {?>
             <tr>
                 <td style="width: 200px;height: 20px;"><?=$audit->Event_Date; ?> </td>
                 <td style="width: 180px;"><?=$audit->Event_Type; ?> </td>
                     <? $user = Users::model()->with('person')->findByPk($audit->Event_User_ID); ?>
                 <td style="width: 220px;"><?=$user->person->First_Name.' '.$user->person->Last_Name; ?> </td>
                 <td style="width: 60px;"><?=$audit->User_Appr_Value; ?> </td>
             </tr>


        <?php }?>
        </table>
    <br/><br/>

    File name: <?=$doc_name;?>



    <div id="copyright" style="right: 0;position: fixed;bottom: 0;font-size: 10px;" class="">Copyright © 2013 All Rights Reserved Mountain Asset Group, Inc.</div>
</div>

<script>
    $(document).ready(function() {

        setTimeout(function() {
            window.print();
            window.close();
        }, 200);


    });
</script>
</body>
</html>

