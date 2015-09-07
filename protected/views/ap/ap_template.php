<!-- This is template for creating PDF -->
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <style>
        div.main {
            width: 750px;
            position: relative;
        }

        th {
            text-align: center;
        }
    </style>
</head>
<body>
<div class="main">
    <table>
        <tr>
            <td width="20"></td>
            <td width="370">
                <table>
                    <tr>
                        <td style="height: 50px; font-size: 23px; font-weight: bold; text-decoration: underline; font-style: italic;">
                            CHECK REQUEST
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 20px; text-align: center; font-size: 18px; font-weight: bold;">
                            <?php echo isset($poFormatting->PO_Format_Client_Name) ? CHtml::encode($poFormatting->PO_Format_Client_Name) : ''; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 20px; text-align: center; font-size: 18px; font-weight: bold;">
                            <?php echo isset($poFormatting->PO_Format_Project_Name) ? CHtml::encode($poFormatting->PO_Format_Project_Name) : ''; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 13px; text-align: center; font-size: 13px;">
                            <?php echo  isset($poFormatting->PO_Format_Address) ? CHtml::encode($poFormatting->PO_Format_Address) : ''; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 13px; text-align: center; font-size: 13px;">
                            <?php echo isset($poFormatting->PO_Format_City_St_ZIP) ? CHtml::encode($poFormatting->PO_Format_City_St_ZIP) : ''; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 13px; text-align: center; font-size: 13px;">
                            <?php echo  isset($poFormatting->PO_Format_Phone) ? CHtml::encode($poFormatting->PO_Format_Phone) : ''; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 30px;">

                        </td>
                    </tr>
                </table>
            </td>
            <td rowspan="2">
                <table>
                    <tr>
                        <td></td>

                        <td colspan="4" style="height: 55px; font-size: 18px; font-weight: bold; text-align: left; ">
                            <?=$ap->Invoice_Number?>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; width: 70px; text-align: right;">Date:</td>
                        <td style="width: 1px;"></td>
                        <td colspan="2" style="height: 12px; font-size: 12px; border-bottom: 1px solid #000; width: 240px;"><?php echo Helper::convertDate($ap->Invoice_Date); ?></td>
                        <td width="30"></td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; font-weight: bold; width: 70px; text-align: right;"></td>
                        <td style="width: 1px;"></td>
                        <td colspan="2" style="height: 12px; font-size: 12px; width: 240px;"></td>
                        <td width="30"></td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; width: 70px; text-align: right;"></td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; text-align: left;">Check Due Date: </td>
                        <td style="height: 12px; font-size: 12px; border-bottom: 1px solid #000; width: 130px;"><?php echo $ap->Invoice_Due_Date ? Helper::convertDate($ap->Invoice_Due_Date) : ''; ?></td>
                        <td width="30"></td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; width: 70px; text-align: right;"></td>
                        <td style="width: 1px;"></td>
                        <td colspan="2" style="height: 12px; font-size: 12px; width: 240px;"></td>
                        <td width="30"></td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 14px; width: 70px; text-align: right;">AMOUNT:</td>
                        <td style="width: 1px;"></td>
                        <td colspan="2" style="height: 12px; font-size: 12px; border-bottom: 1px solid #000; width: 240px;">$ <?php echo number_format($ap->Invoice_Amount, 2); ?></td>
                        <td width="30"></td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; font-weight: bold; width: 70px; text-align: right;"></td>
                        <td style="width: 1px;"></td>
                        <td colspan="2" style="height: 12px; font-size: 12px; width: 240px;"></td>
                        <td width="30"></td>
                    </tr>
                    <tr>
                        <td style="height: 20px; font-size: 12px; font-weight: bold; width: 70px; text-align: right;"></td>
                        <td style="width: 1px;"></td>
                        <td colspan="2" style="height: 12px; font-size: 12px; width: 240px;"></td>
                        <td width="30"></td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; width: 70px; text-align: right;">Contact:</td>
                        <td style="width: 1px;"></td>
                        <td colspan="2" style="height: 12px; font-size: 12px; border-bottom: 1px solid #000; width: 240px;"><?php echo isset($vendorAdmin) ? CHtml::encode($vendorAdmin->user->person->First_Name . ' ' . $vendorAdmin->user->person->Last_Name . ' ' . $vendorAdmin->user->person->Email) : '';?></td>
                        <td width="30"></td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; width: 70px; text-align: right;">Phone #:</td>
                        <td style="width: 1px;"></td>
                        <td colspan="2" style="height: 12px; font-size: 12px; border-bottom: 1px solid #000; width: 240px;"><?php echo isset($currentVendor->client->company) ? $currentVendor->client->company->adreses[0]->Phone : '';?></td>
                        <td width="30"></td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; width: 70px; text-align: right;">Fax #:</td>
                        <td style="width: 1px;"></td>
                        <td colspan="2" style="height: 12px; font-size: 12px; border-bottom: 1px solid #000; width: 240px;"><?php echo isset($currentVendor->client->company) ? $currentVendor->client->company->adreses[0]->Fax : '';?></td>
                        <td width="30"></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td width="20"></td>
            <td>
                <table>
                    <tr>
                        <td style="height: 12px; font-size: 12px; font-weight: bold; width: 80px; text-align: right;"></td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; width: 240px;"></td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; width: 80px; text-align: right;">Vendor Name:</td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; border-bottom: 1px solid #000; width: 240px;"><?php echo isset($currentVendor->client->company) ? wordwrap(CHtml::encode( $currentVendor->client->company->Company_Name), 40, "\n", true)  : ''; ?></td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; width: 80px; text-align: right;">Address:</td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; border-bottom: 1px solid #000; width: 240px;"><?php echo isset($currentVendor->client->company) ? $currentVendor->client->company->adreses[0]->Address1 : '';?></td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; width: 80px; text-align: right;">City/State/Zip:</td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; border-bottom: 1px solid #000; width: 240px;"><?php echo isset($currentVendor->client->company) ? Helper::createFullAddressLine('', $currentVendor->client->company->adreses[0]->City, $currentVendor->client->company->adreses[0]->State, $currentVendor->client->company->adreses[0]->ZIP) : '';?></td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; width:80px; text-align: right;">Federal ID #:</td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; border-bottom: 1px solid #000; width: 240px;"><?php echo isset($currentVendor->client->company) ? CHtml::encode($currentVendor->client->company->Company_Fed_ID) : '';?> </td>
                    </tr>
                    <tr>
                        <td colspan="3" style="height: 50px; font-size: 11px; font-weight: bold; width: 60px; text-align: right;">(Social Security or Federal ID number required for payment)</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td width="20"></td>
            <td>
                <table>
                    <tr>
                        <td style="height: 12px; font-size: 12px; width: 80px; text-align: right;">(circle one)</td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; width: 240px;">
                            <table>
                                <tr>
                                    <td style="<?php echo $ckReqDet->CK_Req_Purchase == 1 ? 'border: 1px solid #000;' : ''; ?>">Purchase</td>
                                    <td style="width: 30px;"></td>
                                    <td style="<?php echo $ckReqDet->CK_Req_Rental == 1 ? 'border: 1px solid #000;' : ''; ?>">Rental</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; width:80px; text-align: right;">Rental Begins:</td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; border-bottom: 1px solid #000; width: 240px;"><?php echo $ckReqDet->Rental_Begin ? Helper::convertDate($ckReqDet->Rental_Begin) : ''; ?> </td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; width:80px; text-align: right;">Rental Ends:</td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; border-bottom: 1px solid #000; width: 240px;"><?php echo $ckReqDet->Rental_End ? Helper::convertDate($ckReqDet->Rental_End) : ''; ?> </td>
                    </tr>
                </table>
            </td>
            <td>
                <!--
                <table>
                    <tr>
                        <td style="height: 12px; font-size: 12px; width: 80px; text-align: right;">(circle one)</td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; width: 240px;">
                            <table>
                                <tr>
                                    <td style="">Mail Check</td>
                                    <td style="width: 30px;"></td>
                                    <td style="border: 1px solid #000;">Hold for Pick Up</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; width:80px; text-align: right;"></td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; border-bottom: 1px solid #fff; width: 240px;">Mail to address: (if different than above)</td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; width:80px; text-align: right;"></td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; border-bottom: 1px solid #000; width: 240px;">dfdfdf </td>
                    </tr>
                </table>
                -->
            </td>
        </tr>
    </table>
</div>
<div style="clear: both;"></div>


<div class="main" style="font-weight: bold; font-size: 15px; text-align: center;">

    <table>
        <tr>
            <td style="vertical-align: top;height: 50px;padding-left: 30px;">
                DESCRIPTION:
            </td>
            <td style="border: 0px;height: 50px;width: 550px;text-align: left;">
               <span style="text-decoration: underline; font-weight: normal; font-size: 14px;">
                    <?php echo CHtml::encode($ap->Invoice_Reference); ?>
               </span>
            </td>
        </tr>
    </table>
</div>

<div class="main">
    <table cellspacing="0">
        <tr>
            <td rowspan="5" style="width: 400px;padding-left: 30px; font-size: 11px;"></td>
            <td style="width: 90px; text-align: right;height: 17px;vertical-align: middle;"><b><?php echo ($approved) ? 'APPROVED ' : '';?></b> </td>
            <td style="height: 17px; width: 120px; vertical-align: middle;"> <b><?php echo ($approved) ? date('m/d/Y') : '';?></b></td>
        </tr>

        <tr>
            <td style="width: 120px; text-align: right;height: 17px;vertical-align: middle;">Requested By: </td>
            <td style="border-bottom: 1px solid #000; height: 17px; width: 120px; vertical-align: middle;"><?php echo ($signRequestedByUser) ? Chtml::encode($signRequestedByUser->person->First_Name . ' ' . $signRequestedByUser->person->Last_Name) : ''; ?></td>
        </tr>

    </table>
</div>

<div class="sign" style=" position: absolute;top: 610px;left: 565px;">
    <table cellspacing="0">
        <? foreach ($aproval_detail_list as $approval_item) {?>
            <tr>
                <td style="width: 100px; vertical-align: top;padding-top:4px;"> <?= Helper::truncLongWordsToTable($approval_item['name'],10);?> </td>
                <td style="width: 80px; vertical-align: top;padding-top:4px;"><?= $approval_item['date'];?></td>
                <? ?>
            </tr>
        <?}?>
    </table>
</div>


<br/> <br/><br/><br/><br/><br/> <br/><br/><br/>
<div class="main">
    <table cellspacing="0" style="font-size: 12px;">
        <tr>
            <td></td>
            <td colspan="3">&nbsp;&nbsp;&nbsp;&nbsp;:</td>
        </tr>
        <tr style="font-weight: bold;">
            <td style="width: 15px;"></td>
            <td style="border-right: 1px solid #000; width: 140px; border-top: 1px solid #000; text-align: center; border-bottom: 1px solid #000; border-left: 1px solid #000;">
                GL / Prod
            </td>
            <td style="border-right: 1px solid #000; width: 70px;  border-top: 1px solid #000; text-align: center; border-bottom: 1px solid #000; ">
                Amount
            </td>
            <td style="border-right: 1px solid #000; width: 280px;  border-top: 1px solid #000; text-align: left; border-bottom: 1px solid #000; ">
                &nbsp;&nbsp;&nbsp;Desc
            </td>
        </tr>

        <?php
        $i = 1;
        foreach($apDists as $key => $dist) {
            $i++;
            ?>
            <tr>
                <td style="width: 15px;"></td>
                <td style="border-right: 1px solid #000; width: 140px; text-align: center; border-bottom: 1px solid #000; border-left: 1px solid #000; height: 10px;">
                    <?php echo CHtml::encode($dist->GL_Dist_Detail_COA_Acct_Number); ?>
                </td>
                <td style="border-right: 1px solid #000; width: 70px;   text-align: center; border-bottom: 1px solid #000; height: 10px;">
                    <?php echo CHtml::encode($dist->GL_Dist_Detail_Amt); ?>
                </td>
                <td style="border-right: 1px solid #000; width: 280px; text-align: left; border-bottom: 1px solid #000; height: 10px;">
                    <?php echo CHtml::encode($dist->GL_Dist_Detail_Desc); ?>
                </td>
            </tr>
        <?php }?>

        <?php for($j = $i;  $j <= 5; $j++) {?>
            <tr>
                <td style="width: 15px;"></td>
                <td style="border-right: 1px solid #000; width: 140px; text-align: center; border-bottom: 1px solid #000; border-left: 1px solid #000; height: 10px;">

                </td>
                <td style="border-right: 1px solid #000; width: 70px;   text-align: center; border-bottom: 1px solid #000; height: 10px;">

                </td>
                <td style="border-right: 1px solid #000; width: 280px; text-align: left; border-bottom: 1px solid #000; height: 10px;">

                </td>
            </tr>
        <?php }?>
    </table>
</div>
</body>
</html>