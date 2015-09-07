<!-- This is template for creating PDF -->
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <style type="text/css">
        div.main {
            position: relative;
            padding: 30px;
        }
        #middle { width: 700px;}
        table{border-collapse: collapse;
            padding-right: 30px;}

         .center td {
            text-align: center;
        }
        #underline{border-bottom: 3px solid;
        width:720px;}
        td.right {text-align: right;

            }
        td.center {text-align: center;

        }

    </style>
</head>
<body>
<div class="main">
    <?php
    $this->beginClip('tableHeader');
    ?>
    <div style="height: 850px;">
    <table id="header_table"  border="0">
        <tr>
            <td  width="150">
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
            <td width="150" class="right" >
                <b><?=$doc_type." Batch Summary Report";?></b><br/>
                <b><?=$batch_creation_date;?></b><br/>
                <b><?=$user_created;?></b><br/>
            </td>
        </tr>
    </table>
    <div id="underline"></div>
    <div id="middle" border="1px">
    <?php
    $this->endClip();
    echo $this->clips['tableHeader'];
    ?>

    <?php
    $this->beginClip('tableFooter');
    ?>
    </div>
    <table id="footer_table"  border="0">
        <tr>
            <td  width="150">

            </td>
            <td width="350" class="center">
                <p>
                    - - - - - - - - - - {page footer} - - - - - - - - - -<br/>
                    - - - - - - - - - - {page footer} - - - - - - - - - -<br/>
                </p>
            </td>
            <td class="right" width="150" >
                Page #<br>
                <?php echo date('m/d/Y') . ', ' . date('H:i:s'); ?>
            </td>
        </tr>
    </table>
    </div>
    <?php
    $this->endClip();
    ?>
        <?
        $rowsPerPage = 10;
        $page = 1;
        $prevInvNumb = '';
        $i = 1;
        $butchTotal = 0;
        foreach($data as $dist) {
            /*
            if (($dist['InvNum'] != $prevInvNumb && $rowsPerPage <= ($i + 1 + $dimArray[$dist['docID']])) || ($rowsPerPage <= $i)) {
                echo $this->clips['tableFooter'];
                echo $this->clips['tableHeader'];
                $i = 1;
                $page++;
            }

            if ($dist['InvNum'] != $prevInvNumb) {
                echo $dist['vendorName'] . ' - ' . $dist['InvNum'] . ' - ' . $dist['InvAmt'] . '<br/>';
                $i++;
            }

            echo $dist['GLCode'] . ' - ' . $dist['GLAmt'] . '<br/>';
            $prevInvNumb = $dist['InvNum'];
            $butchTotal += $dist['GLAmt'];
            $i++;
            /*
             *
             *
              $page->setCellValue("A".$x,$row['VendorID']);
                $page->setCellValue("B".$x,$row['InvNum']);
                $page->setCellValue("C".$x,Helper::convertDate($row['InvDate']));
                $page->setCellValue("D".$x,$row['InvAmt']);
                $page->setCellValue("E".$x,$row['InvDesc']);
                $page->setCellValue("F".$x,$row['GLCode']);
                $page->setCellValue("G".$x,$row['GLAmt']);
                $page->setCellValue("H".$x,$row['GLDesc']);
            $iteration=$row[1];
            $count=$count+$iteration;
            if ($count<=$max)
            {
                for($j=1;$j<=$iteration;$j++)
                {
                    Yii::app()->controller->renderPartial('application.views.widgets.batch_summary_template', array(
                        'data'=>$data,
                    ), true);
                }
            }*/
        }
        ?>

<?php //$butchTotal ?>

   <?php echo $this->clips['tableFooter']; ?>
</div>
</body>
</html>