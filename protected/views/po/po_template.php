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
            <td width="450">
                <table>
                    <tr>
                        <td style="height: 20px; font-size: 18px; font-weight: bold;">
                            <?php echo isset($poFormatting->PO_Format_Client_Name) ? CHtml::encode($poFormatting->PO_Format_Client_Name) : ''; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 20px; font-size: 18px; font-weight: bold;">
                            <?php echo  isset($poFormatting->PO_Format_Project_Name) ? CHtml::encode($poFormatting->PO_Format_Project_Name) : ''; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 13px; font-size: 13px;">
                            <?php echo  isset($poFormatting->PO_Format_Address) ? CHtml::encode($poFormatting->PO_Format_Address) : ''; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 13px; font-size: 13px;">
                            <?php echo isset($poFormatting->PO_Format_City_St_ZIP) ? CHtml::encode($poFormatting->PO_Format_City_St_ZIP) : ''; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 13px; font-size: 13;">
                            <?php echo  isset($poFormatting->PO_Format_Phone) ? CHtml::encode($poFormatting->PO_Format_Phone) : ''; ?>
                        </td>
                    </tr>
                </table>
            </td>
            <td rowspan="2" width="250">
                <table>
                    <tr>
                        <td colspan="4" style="height: 50px; font-size: 18px; font-weight: bold; text-align: right; ">PURCHASE ORDER</td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; font-weight: bold; width: 60px; text-align: right;">Number:</td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; border-bottom: 1px solid #000; width: 130px;"><?php echo $po->PO_Number; ?></td>
                        <td width="30"></td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; font-weight: bold; width: 60px; text-align: right;"></td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; width: 130px;"></td>
                        <td width="30"></td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; width: 60px; text-align: right;">Date:</td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; border-bottom: 1px solid #000; width: 130px;"><?php echo Helper::convertDate($po->PO_Date); ?></td>
                        <td width="30"></td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; width: 60px; text-align: right;">Job Name:</td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; border-bottom: 1px solid #000; width: 130px;"><?php echo isset($poFormatting->PO_Format_Job_Name) ? CHtml::encode($poFormatting->PO_Format_Job_Name) : ''; ?></td>
                        <td width="30"></td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; font-weight: bold; width: 60px; text-align: right;"></td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; width: 130px;"></td>
                        <td width="30"></td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; font-weight: bold; width: 60px; text-align: right;"></td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; width: 130px;"></td>
                        <td width="30"></td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; width: 60px; text-align: right;">Account #:</td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; border-bottom: 1px solid #000; width: 130px;"><?php echo CHtml::encode($po->PO_Account_Number); ?></td>
                        <td width="30"></td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; font-weight: bold; width: 60px; text-align: right;">Fed ID:</td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; border-bottom: 1px solid #000; width: 130px;"><?php echo isset($currentVendor->client->company) ? $currentVendor->client->company->Company_Fed_ID : ''; ?></td>
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
                        <td style="height: 12px; font-size: 12px; font-weight: bold; width: 60px; text-align: right;"></td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; width: 270px;"></td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; font-weight: bold; width: 60px; text-align: right;">Co. Name:</td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; border-bottom: 1px solid #000; width: 270px;"><?php echo isset($currentVendor->client->company) ? $currentVendor->client->company->Company_Name : ''; ?></td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; font-weight: bold; width: 60; text-align: right;">Address:</td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; border-bottom: 1px solid #000; width: 270px;"><?php echo isset($currentVendor->client->company) ? $currentVendor->client->company->adreses[0]->Address1 : '';?> </td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; font-weight: bold; width: 60px; text-align: right;"></td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; border-bottom: 1px solid #000; width: 270px;"></td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; font-weight: bold; width: 60px; text-align: right;"></td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; border-bottom: 1px solid #000; width: 270px;"><?php echo isset($currentVendor->client->company) ? Helper::createFullAddressLine('', $currentVendor->client->company->adreses[0]->City, $currentVendor->client->company->adreses[0]->State, $currentVendor->client->company->adreses[0]->ZIP) : '';?> </td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; font-weight: bold; width: 60px; text-align: right;">Phone:</td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; border-bottom: 1px solid #000; width: 270px;"><?php echo isset($currentVendor->client->company) ? $currentVendor->client->company->adreses[0]->Phone : '';?></td>
                    </tr>
                    <tr>
                        <td style="height: 12px; font-size: 12px; font-weight: bold; width: 60px; text-align: right;">Contact:</td>
                        <td style="width: 1px;"></td>
                        <td style="height: 12px; font-size: 12px; border-bottom: 1px solid #000; width: 270px;"><?php echo isset($vendorAdmin) ? CHtml::encode($vendorAdmin->user->person->First_Name . ' ' . $vendorAdmin->user->person->Last_Name . ' ' . $vendorAdmin->user->person->Email) : '';?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
<div style="clear: both;"></div>
<br/>
<div class="main">
    <table style="font-size: 12px;" cellspacing='0' class="description">
        <thead>
            <tr>
                <th width="20">

                </th>
                <th width="80">
                    Qty
                </th>
                <th width="340">
                    Description
                </th>
                <th width="60">
                    Purchase
                </th>
                <th width="60">
                    Rental
                </th>
                <th width="60">
                    Line #
                </th>
                <th width="100">
                    Amount
                </th>
            </tr>
        </thead>
        <tbody>
             <?php
                 $i = 1;
                 foreach($poDecrDetails as $key => $descDetail) {
                 $borderTop = ($i==1) ? "border-top: 1px solid #000;" : "";
                 $i++;
                 ?>

                 <tr>
                     <td width="20">

                     </td>
                     <td style="border-right: 1px solid #000;border-left: 1px solid #000; text-align: center; border-bottom: 1px solid #000; <?php echo $borderTop;?>">
                         <?php echo CHtml::encode($descDetail->PO_Desc_Qty); ?>
                     </td>
                     <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; <?php echo $borderTop;?>">
                         <?php echo CHtml::encode($descDetail->PO_Desc_Desc); ?>
                     </td>
                     <td style="border-right: 1px solid #000; text-align: center; border-bottom: 1px solid #000; <?php echo $borderTop;?>">
                         <?php echo ($descDetail->PO_Desc_Purchase == 1) ? 'x' : ''; ?>
                     </td>
                     <td style="border-right: 1px solid #000; text-align: center; border-bottom: 1px solid #000; <?php echo $borderTop;?>">
                         <?php echo ($descDetail->PO_Desc_Rental == 1) ? 'x' : ''; ?>
                     </td>
                     <td style="border-right: 1px solid #000; text-align: center; border-bottom: 1px solid #000; <?php echo $borderTop;?>">
                         <?php echo CHtml::encode($descDetail->PO_Desc_Budget_Line_Num); ?>
                     </td>
                     <td style="border-right: 1px solid #000; text-align: center; border-bottom: 1px solid #000; <?php echo $borderTop;?>">
                         <?php echo CHtml::encode($descDetail->PO_Desc_Amount); ?>
                     </td>
                 </tr>
             <?php } ?>
             <?php
             for($j = $i;  $j <= 5; $j++) {
                 $height = "height: 10px;";
                 ?>

                 <tr>
                     <td width="20">

                     </td>
                     <td style="border-right: 1px solid #000;border-left: 1px solid #000; text-align: center; border-bottom: 1px solid #000; <?php echo $height;?>">

                     </td>
                     <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; <?php echo $height;?>">

                     </td>
                     <td style="border-right: 1px solid #000; text-align: center; border-bottom: 1px solid #000; <?php echo $height;?>">

                     </td>
                     <td style="border-right: 1px solid #000; text-align: center; border-bottom: 1px solid #000; <?php echo $height;?>">

                     </td>
                     <td style="border-right: 1px solid #000; text-align: center; border-bottom: 1px solid #000; <?php echo $height;?>">

                     </td>
                     <td style="border-right: 1px solid #000; text-align: center; border-bottom: 1px solid #000; <?php echo $height;?>">

                     </td>
                 </tr>
             <?php } ?>
             <tr>
                 <td colspan="4" rowspan="5">
                     <table cellspacing="0">
                         <tr>
                             <td colspan="5" style="height: 15px;"> </td>
                         </tr>
                         <tr>
                             <td colspan="2" style="font-weight: bold; width: 200px; text-align: right;">
                                 Check One&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                             </td>
                             <td colspan="3">

                             </td>
                         </tr>
                         <tr>
                             <td style="text-align: right; width: 160px;">
                                 On Account &nbsp;
                             </td>
                             <td style="border: 1px solid #000; width: 60px; text-align: center;">
                                 <?php echo ($po->Payment_Type == "OA") ? 'x' : ''; ?>
                             </td>
                             <td>

                             </td>
                             <td>

                             </td>
                             <td>

                             </td>
                         </tr>
                         <tr>
                             <td style="text-align: right;">
                                 Credit Card &nbsp;
                             </td>
                             <td style="border-right: 1px solid #000; text-align: center; border-bottom: 1px solid #000;border-left: 1px solid #000;">
                                 <?php echo ($po->Payment_Type == "CC") ? 'x' : ''; ?>
                             </td>
                             <td>
                                 &nbsp; last 4 digits
                             </td>
                             <td style="border-bottom: 1px solid #000; width: 80px; text-align: center;">
                                 <?php echo ($po->Payment_Type == "CC") ? $po->PO_Card_Last_4_Digits : ''; ?>
                             </td>
                             <td>

                             </td>
                         </tr>
                         <tr>
                             <td style="text-align: right;">
                                 Other &nbsp;
                             </td>
                             <td style="border-right: 1px solid #000; text-align: center; border-bottom: 1px solid #000;border-left: 1px solid #000;">
                                 <?php echo ($po->Payment_Type != "CC" && $po->Payment_Type != "OA") ? 'x' : ''; ?>
                             </td>
                             <td>
                                 &nbsp;  <?php echo ($po->Payment_Type != "CC" && $po->Payment_Type != "OA") ? $paymentTypes[$po->Payment_Type] : ''; ?>
                             </td>
                             <td>

                             </td>
                             <td>

                             </td>
                         </tr>
                     </table>
                 </td>
                 <td colspan="2" style="font-weight: bold; text-align: right;">
                     Subtotal &nbsp;
                 </td>
                 <td style="border-right: 1px solid #000; text-align: center; border-bottom: 1px solid #000; border-left: 1px solid #000;">
                     <?php echo CHtml::encode($po->PO_Subtotal); ?>
                 </td>
             </tr>
             <tr>
                 <td colspan="2" style="text-align: right;">
                     Tax &nbsp;
                 </td>
                 <td style="border-right: 1px solid #000; text-align: center; border-bottom: 1px solid #000; border-left: 1px solid #000;">
                     <?php echo CHtml::encode($po->PO_Tax); ?>
                 </td>
             </tr>
             <tr>
                 <td colspan="2" style="text-align: right;">
                     Delivery Charge &nbsp;
                 </td>
                 <td style="border-right: 1px solid #000; text-align: center; border-bottom: 1px solid #000; border-left: 1px solid #000;">
                     <?php echo CHtml::encode($po->PO_Delivery_Chg); ?>
                 </td>
             </tr>
             <tr>
                 <td colspan="2" style="text-align: right;">
                     Other &nbsp;
                 </td>
                 <td style="border-right: 1px solid #000; text-align: center; border-bottom: 1px solid #000; border-left: 1px solid #000;">
                     <?php echo CHtml::encode($po->PO_Other_Chg); ?>
                 </td>
             </tr>
             <tr>
                 <td colspan="2" style="font-weight: bold; text-align: right;">
                     Total &nbsp;
                 </td>
                 <td style="border-right: 1px solid #000; text-align: center; border-bottom: 1px solid #000; border-left: 1px solid #000;">
                     <?php echo CHtml::encode($po->PO_Total); ?>
                 </td>
             </tr>
        </tbody>
    </table>
</div>
<div class="main" style="font-weight: bold; font-size: 15px; text-align: center;">
    <br/>
    <!--*** All Purchase Orders Require Executive signature to be valid ***-->
    <br/><br/>
</div>
<div class="main">
    <table cellspacing="0">
        <tr>
            <td rowspan="5" style="width: 400px;padding-left: 30px; font-size: 11px;"><span style="overflow: hidden"><?php echo (isset($poFormatting->PO_Format_Addl_Language) && ($poFormatting->PO_Format_Sig_Req == 1)) ? CHtml::encode($poFormatting->PO_Format_Addl_Language) : ''; ?></span></td>
            <td style="width: 90px; text-align: right;height: 17px;vertical-align: middle;color: #41B50B"><b><?php echo ($approved) ? 'APPROVED ' : '';?></b> </td>
            <td style="height: 17px; width: 120px; vertical-align: middle;"> <b><?php echo ($approved) ? date('m/d/Y') : '';?></b></td>
        </tr>



        <tr>
            <td style="width: 90px; text-align: right;height: 17px;vertical-align: middle;">Requested By: </td>
            <td style="border-bottom: 1px solid #000; height: 17px; width: 120px; vertical-align: middle;"><?php echo ($signRequestedByUser) ? Chtml::encode($signRequestedByUser->person->First_Name . ' ' . $signRequestedByUser->person->Last_Name) : ''; ?></td>
        </tr>
    </table>
</div>

<div class="sign" style=" position: absolute;top: 570px;left: 555px;">
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

<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>


<div class="main">
    <table cellspacing="0" style="font-size: 12px;">
        <tr>
            <td></td>
            <td colspan="3">&nbsp;&nbsp;&nbsp;&nbsp;Distribution:</td>
        </tr>
        <tr style="font-weight: bold;">
            <td style="width: 15px;"></td>
            <td style="border-right: 1px solid #000; width: 150px; border-top: 1px solid #000; text-align: center; border-bottom: 1px solid #000; border-left: 1px solid #000;">
                GL Code
            </td>
            <td style="border-right: 1px solid #000; width: 70px;  border-top: 1px solid #000; text-align: center; border-bottom: 1px solid #000; ">
                Amount
            </td>
            <td style="border-right: 1px solid #000; width: 270px;  border-top: 1px solid #000; text-align: left; border-bottom: 1px solid #000; ">
                &nbsp;&nbsp;&nbsp;Desc
            </td>
        </tr>
        <?php
        $i = 1;
        foreach($poDists as $key => $dist) {
            $i++;
            ?>
            <tr>
                <td style="width: 15px;"></td>
                <td style="border-right: 1px solid #000; width: 150px; text-align: center; border-bottom: 1px solid #000; border-left: 1px solid #000;">
                    <?php echo CHtml::encode($dist->PO_Dists_GL_Code); ?>
                </td>
                <td style="border-right: 1px solid #000; width: 70px;   text-align: center; border-bottom: 1px solid #000; ">
                    <?php echo CHtml::encode($dist->PO_Dists_Amount); ?>
                </td>
                <td style="border-right: 1px solid #000; width: 270px; text-align: left; border-bottom: 1px solid #000; ">
                    <?php echo CHtml::encode($dist->PO_Dists_Description); ?>
                </td>
            </tr>
        <?php }?>
        <?php for($j = $i;  $j <= 5; $j++) {?>
            <tr>
                <td style="width: 15px;"></td>
                <td style="border-right: 1px solid #000; width: 150px; text-align: center; border-bottom: 1px solid #000; border-left: 1px solid #000; height: 10px;">

                </td>
                <td style="border-right: 1px solid #000; width: 70px;   text-align: center; border-bottom: 1px solid #000; height: 10px;">

                </td>
                <td style="border-right: 1px solid #000; width: 270px; text-align: left; border-bottom: 1px solid #000; height: 10px;">

                </td>
            </tr>
        <?php }?>
    </table>
</div>
</body>
</html>