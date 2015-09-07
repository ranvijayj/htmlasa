<table class="header">
    <tr>
            <td  width="250">
                <b><?= Yii::app()->user->clientInfo;   ?></b><br/>
                <b><?=Yii::app()->user->projectInfo;?></b><br/>
                <b><?=$batch_id;?></b><br/>
            </td>
            <td width="350" class="center">
                <p>
                    - - - - - - - - - - {page header} - - - - - - - - - -<br/>
                    - - - - - - - - - - {page header} - - - - - - - - - -<br/>
                    - - - - - - - - - - {page header} - - - - - - - - - -<br/>
                </p>
            </td>
            <td width="130" style="text-align: right;" >
                <b><?=$doc_type." Batch Summary Report";?></b><br/>
                <b><?=Helper::convertDate($batch_creation_date);?></b><br/>
                <b><?=$user_created;?></b><br/>
            </td>
     </tr>
</table>
    <div class="underline" style="border-bottom: 2px solid;width: 750px;" ></div>
        <table class="onepage" >
            <tr>
                <td style="height: 900px; vertical-align: text-top;">
            <table class="middle">
