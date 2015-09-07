<!-- This is template for creating PDF -->
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <style type="text/css">
        div.main {
            position: relative;
            padding-right: 50px;
        }
        table{border-collapse: collapse;
                padding-right: 30px;}

        th {
            text-align: center;
        }

        #invoice {text-align: left;padding: 20px 0px 0px 15px; }
        #invoice_head {padding-top: 5px;
                       padding-bottom: 20px;
                       font-size: 25px;
                        }
        .second_table {align-content: center;}

        td.td_style1 {width: 60px;}
        td.td_style2 {width: 40px;}
        td .second_table {border="1px solid #000";}

        td.amount  {width: 100px; background-color: #d3d3d3;text-align: right;}

        td.amount1  {width: 100px;text-align: right;}

        #right {width: 90%; padding-left: 500px ;}

        p {
            line-height: 1.6;
            color: #444444;
        }


        .tabl_2 td {padding-left: 10px;
                    vertical-align: middle;
                    padding-right: 10px;
                    border: 3px solid;
                    border-color: #d3d3d3;
                }
        .bold td {font-weight: bold;}
        .tabl_2 {
        }
    </style>
</head>
<body>
<div class="main">
    <table  border="0">
        <tr>
            <td  width="250">
                <p><b>Asa Clerk </b><br/>
                1041 N Formosa Ave, <br/>
                SMB 111/113<br/>
                Los Angeles,CA 90046<br/>
                United States<br/>
                Tel: 323 850-2855<br/>
                </p>

            </td>
            <td width="250">
                <p><b>Bill to</b><br/>
                    <? if($data['company_to']['company_name']) {echo "<b>".$data['company_to']['company_name']."</b><br/>";} ?>
                    <?
                    //if($data['company_to']['director_name']) {echo $data['company_to']['director_name']."<br/>";}
                    ?>
                    <? if($data['company_to']['street']) {echo $data['company_to']['street']."<br/>";} ?>
                    <? if($data['company_to']['city']) {echo $data['company_to']['city']."<br/>";} ?>
                    <? if($data['company_to']['country']) {echo $data['company_to']['country']."<br/>";} ?>
                    <? if($data['company_to']['phone']) {echo "Tel: ".$data['company_to']['phone']."<br/>";} ?>
                    <? if($data['company_to']['email']) {echo $data['company_to']['email']."<br/>";} ?>
                 </p>


            </td>
            <td width="250" >
                <div id="invoice"> <div id="invoice_head"><b>INVOICE</b></div>
                <p><b>Invoice number: </b><?=$data['invoice']['number']?><br/>
                <b>Invoice date: </b><?=$data['invoice']['date']?><br/>
                <b>Due date: </b><?=$data['invoice']['due_date']?><br/>
                <b>Amount due:   </b>$<?=number_format($data['invoice']['amount_due'],2);?><br/></p>
                </div>
            </td>
        </tr>
    </table>

<br/><br/><br/><br/>

<table  align="center"   class="tabl_2">
        <tr>
          <td width="170" height="20" valign="middle"> PRODUCT</td>
            <td width="120"> QTY </td>
            <td width="120"> PRICE </td>
            <td width="130" class="amount"> AMOUNT</td>

        </tr>
        <tr >

            <? $result_str = 'Tiers - '.$data['service']['service_name'].
                '<br/>End Date - '.$data['service']['active_to'].'<br/>'.
                'Added / Total Users - '. $data['service']['added_users'].' / '.$data['service']['total_users'].'<br>'.
                'Added / Total projects - '.$data['service']['added_projects'].' / '.$data['service']['total_projects'].'<br/>'.
                'Added / Total Storage - '.$data['service']['added_storage'].' / '.$data['service']['total_storage'].'GB'; ?>

            <td width="170" height="45" valign="middle"> <b>D-APC Services</b> <br/> <br/> <?=$result_str;?></td>
            <td width="120" height="45" valign="middle"> 1 </td>
            <td width="120" height="45" valign="middle">$ <?=number_format($data['invoice']['amount_due'],2);?><br/> </td>
            <td width="130" height="45" valign="middle" class="amount1"> $ <?=number_format($data['invoice']['amount_due'],2);?><br/></td>

        </tr>

    </table>
    <br/ ><br/ ><br/ ><br/ >


    <div id="right">
        <table  border="2px" class="tabl_2" >
            <tr>
                <td width="75" height="25" ><b>TOTAL</b></td>
                <td width="75" height="25" class="amount1"><b>$ <?=number_format($data['invoice']['amount_due'],2);?></b> </td>
            </tr>
            <tr >
                <td width="75" height="25" class="bold"> Amount due </td>
                <td width="75" class="amount1" height="25"><b>$ <?=number_format($data['invoice']['amount_due'],2);?> </b> </td>
            </tr>
        </table>
    </div>

    <br/> <br/><br/> NOTES

</div>
</body>
</html>