
<?php
/* @var $this RemoteProcessingController */
?>
<h3>Brief client's info</h3>
<form id="calculation_form">
<input type="hidden" name="values[Client_ID]" value="<?= $cli_id;?>">
<table>
    <tr>
        <td style="vertical-align: top;">
            Name : <span class="details_page_value"><?=$company->Company_Name?></span><br/>
            Email : <span class="details_page_value"><?=$company->Email?></span><br/>
            Company_Fed_ID : <span class="details_page_value"><?=$company->Company_Fed_ID?></span><br/>
        </td>

        <td>
            Address1 : <span class="details_page_value"><?=$company->adreses[0]->Address1?></span><br/>
            Address2 : <span class="details_page_value"><?=$company->adreses[0]->Address2?></span><br/>
            City : <span class="details_page_value"><?=$company->adreses[0]->City?></span><br/>
            State : <span class="details_page_value"><?=$company->adreses[0]->State?></span><br/>
        </td>
        <td>
            ZIP : <span class="details_page_value"><?=$company->adreses[0]->ZIP?></span><br/>
            Country : <span class="details_page_value"><?=$company->adreses[0]->Country?></span><br/>
            Phone : <span class="details_page_value"><?=$company->adreses[0]->Phone?></span><br/>
            Fax : <span class="details_page_value"><?=$company->adreses[0]->Fax?></span><br/>
        </td>
    </tr>
</table>


<table style="border: 1px solid;">
    <tr>
        <th style="vertical-align: top;border: 1px solid;text-align: center;">Clients users</th>
        <th style="vertical-align: top;border: 1px solid;">Documents</th> </tr>
    <tr>


        <td style="vertical-align: top;border: 1px solid;width: 300px;">
            <?
            echo "<table>";
            foreach ($users_client_list as $row) {

                $type='User';
                if ( $row['CL_User_Type']=='User' && $row['User_Type']=='User' ) $type='User';
                if ( $row['CL_User_Type']=='User' && $row['User_Type']=='Admin' ) $type='Admin';
                if ( $row['CL_User_Type']=='Approver' && $row['User_Type']=='User' ) $type='Approver';
                if ( $row['CL_User_Type']=='Client Admin' && $row['User_Type']=='User' ) $type='Client Admin';
                if ( $row['CL_User_Type']=='Client Admin' && $row['User_Type']=='DB Admin' ) $type='DB Admin';
                if ( $row['CL_User_Type']=='Client Admin' && $row['User_Type']=='Admin' ) $type='Client Admin';
                if ( $row['CL_User_Type']=='User' && $row['User_Type']=='Data Entry Clerk' ) $type='DEC';
                if ( $row['CL_User_Type']=='Processor' && $row['User_Type']=='User' ) $type='Processor';

                echo '<tr>';
                echo '<td>'.$row['First_Name'].' '.$row['Last_Name'].'</td><td>'.$row['User_Login'].'</td><td>'.$row['User_Approval_Value'].'</td><td> '.$type.'</td>' ;
                echo '</tr>';
            }
            echo "</table>";
            ?>
        </td>
        <td style="vertical-align: top;border: 1px solid;">

            <?
            echo "<table>";
            foreach ($documents_count as $row) {
                $count += $row['Total'];
                echo '<tr>';
                echo '<td>'.$row['Document_Type'].'</td><td>'.$row['Total'].'</td>';
                echo '</tr>';;
            }
            echo "<tr><td><b>Total</b>:</td><td>".$count."</td><tr>";
            echo "</table>";
            ?>

        </td>
    </tr>

</table>


<table style="border: 1px solid;">

    <tr >
        <th style="vertical-align: top;border: 1px solid;text-align: center;" > Notes Received </th> <th style="vertical-align: top;border: 1px solid;text-align: center;" >Notes Total Characters</th>
    <tr>
        <td style="vertical-align: middle;border: 1px solid;text-align: center;">
            <?= $notes['CountNotes'];?>
            <input type="hidden" name="values[CountNotes]" value="<?= $notes['CountNotes'];?>">
        </td>

        <td style="vertical-align: top;border: 1px solid;text-align: center;">
            <?= $notes['CommentsLength'];?>
            <input type="hidden" name="values[CommentsLength]" value="<?= $notes['CommentsLength'];?>">

        </td>

    </tr>
</table>



<table style="border: 1px solid;">
    <tr >
        <th style="vertical-align: top;border: 1px solid;text-align: center;" >Vendors</th> <th style="vertical-align: top;border: 1px solid;text-align: center;" >COAs</th>
    <tr>
        <td style="vertical-align: middle;border: 1px solid;text-align: center;">
        <?= $vendors?>
            <input type="hidden" name="values[Vendors]" value="<?= $vendors;?>">
        </td>

        <td style="vertical-align: top;border: 1px solid;text-align: center;">
            <?= $coas?>
            <input type="hidden" name="values[Coas]" value="<?= $coas;?>">

        </td>

    </tr>
</table>

<table style="border: 1px solid;">
    <tr >
        <th style="vertical-align: top;border: 1px solid;text-align: center;" colspan="3">Files info</th>
    <tr>
        <td style="vertical-align: middle;border: 1px solid;text-align: center;">
            <b>General files info </b>
        </td>

        <td style="vertical-align: top;border: 1px solid;">
            <?
            echo "<table>";
            $count = 0;$sum = 0;$pages_count = 0;
            foreach ($files_count as $row) {
                $count += $row['FilesCount'];
                $pages_count += $row['PagesCount'];
                $sum+= $row['MB'];
                echo '<tr>';
                echo '<td>'.$row['Mime_Type'].'</td><td>'.Helper::formatBytes($row['MB']).'</td><td>'.$row['FilesCount'].'</td>';
                echo '</tr>';;
            }
            echo '<tr>
                    <td><b>Total (size/count)</b>:</td><td>'.Helper::formatBytes($sum).'</td><td>'.$count.'</td>
                    <input type="hidden" name=values[GeneralFilesSize] value="'.$sum.'">
                    <input type="hidden" name=values[GeneralFilesCount] value="'.$count.'">
                 </tr>';
            echo "</table>";

            ?>

        </td>

        <td class="general" style="vertical-align: middle;border: 1px solid;text-align: center;">
            <div>

               Pages :  <?=$pages_count?>
                <input type="hidden" name=values[PagesCount] value="<?=$pages_count; ?>"> <br />
               

            </div>
        </td>
    </tr>

    <tr>
        <td style="vertical-align: middle;border: 1px solid;text-align: center;">
            <b>Uploaded files info </b>
        </td>

        <td style="vertical-align: top;border: 1px solid;">
            <?
            echo "<table>";
            $count = 0;$sum = 0;$pages_count = 0;
            foreach ($U_files_count as $row) {
                $count += $row['FilesCount'];
                $sum+= $row['MB'];
                $pages_count += $row['PagesCount'];
                echo '<tr>';
                echo '<td>'.$row['Mime_Type'].'</td><td>'.Helper::formatBytes($row['MB']).'</td><td>'.$row['FilesCount'].'</td>';
                echo '</tr>';
            }
            echo '<tr>
                    <td><b>Total (size/count)</b>:</td><td>'.Helper::formatBytes($sum).'</td><td>'.$count.'</td>
                    <input type="hidden" name=values[UploadedFilesSize] value="'.$sum.'">
                    <input type="hidden" name=values[UploadedFilesCount] value="'.$count.'">
                 </tr>';
            echo "</table>";
            ?>

        </td>

        <td style="vertical-align: middle;border: 1px solid;text-align: center;" class="uploaded">
            <div class="uploaded">
                Pages :  <?=$pages_count?>
            </div>
        </td>
    </tr>

    <tr>
        <td style="vertical-align: middle;border: 1px solid;text-align: center;">
            <b>Generated files info </b>
        </td>

        <td style="vertical-align: top;border: 1px solid;">
            <?
            echo "<table>";
            $count = 0;$sum =0;$pages_count = 0;
            foreach ($G_files_count as $row) {
                $count += $row['FilesCount'];
                $sum+= $row['MB'];
                $pages_count += $row['PagesCount'];
                echo '<tr>';
                echo '<td>'.$row['Mime_Type'].'</td><td>'.Helper::formatBytes($row['MB']).'</td><td>'.$row['FilesCount'].'</td>';
                echo '</tr>';;
            }
            echo '<tr>
                    <td><b>Total (size/count)</b>:</td><td>'.Helper::formatBytes($sum).'</td><td>'.$count.'</td>
                    <input type="hidden" name=values[GeneratedFilesSize] value="'.$sum.'">
                    <input type="hidden" name=values[GeneratedFilesCount] value="'.$count.'">
                 </tr>';
            echo "</table>";
            ?>

        </td>

        <td style="vertical-align: middle;text-align:center; border: 1px solid;">
            Pages :  <?=$pages_count?>
        </td>
    </tr>

</table>


<!--<h3>Calculation</h3>

<div>Settings<br/>

    <div class="calc_row" style="padding-top: 6px;"><label> Setup fee $</label>    <input type="text" name=values[SetupFee] class="setup_fee" value="1.5" ></div>
    <div class="calc_row" style="padding-top: 6px;"><label> Fee per page (digital) $</label>    <input type="text" name=values[PpFeeDigital] class="per_page_digital" value="0.5" ></div>
    <div class="calc_row" style="padding-top: 6px;"><label>Fee per page (paper) $</label>    <input type="text"  name=values[PpFeePaper] class="per_page_paper" value="0.7" ></div>
    <div class="calc_row" style="padding-top: 6px;"><label>Number of pages per sheet </label>
        <select class="per_page_paper" name=values[PagesPerSheet] value="1" >
            <option >1</option>
            <option >2</option>
            <option >4</option>
            <option >6</option>
            <option >8</option>
        </select>
    </div>
</div>
<br />
</form>

<button class="button" id="calculate_price"> Calculate price of selected Client processing </button>

<div id="calculation" style="padding-top: 10px;">

</div>-->
<a href="/remoteprocessing/export/<?=$cli_id?>" class="button" id="start_processing" style="padding: 1px 12px;">  Start processing  </a>