<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 11/21/14
 * Time: 5:19 PM
 */
define('FPDF_FONTPATH',Yii::app()->basePath.'/extensions/Fpdf/font/');
require_once(Yii::app()->basePath.'/extensions/Fpdf/fpdf.php');
require_once(Yii::app()->basePath.'/extensions/Fpdi/fpdi.php');
class FpdfPo extends FPDI
{
    var $custom_page_num;

    var $po,$poFormatting,$poDecrDetails,$poDists,$currentVendor,$vendorAdmin,$signRequestedByUser,$aproval_detail_list,$approved,$paymentTypes;

    var $desc_break_index= 0;
    var $dist_break_index = 0;
    var $approval_break_index = 0;


    function setPageNo($num) {
        $this->custom_page_num = $num;
    }

    function setVariables ($po,$poFormatting,$poDecrDetails,$poDists,$currentVendor,$vendorAdmin,$signRequestedByUser,$aproval_detail_list,$approved,$paymentTypes){

        $this->po = $po;
        $this->poFormatting = $poFormatting;
        $this->poDecrDetails = $poDecrDetails;
        $this->poDists = $poDists;
        $this->currentVendor = $currentVendor;
        $this->vendorAdmin = $vendorAdmin;
        $this->signRequestedByUser = $signRequestedByUser;
        $this->aproval_detail_list = $aproval_detail_list;
        $this->approved = $approved;
        $this->paymentTypes = $paymentTypes;
    }


    //Page header
    function Header()
    {

       /* $this->SetFont('Arial','',12);
        $this->SetFillColor(1,94,197);
        $this->SetDrawColor(255,255,255);
        $this->Rect(5,3,70,10,'DF');
        $this->SetXY(30,9);
        $this->SetTextColor(255,255,255);
        $this->Cell(0,0, "Asamanagement Book",0,1,'L');
        $this->SetTextColor(0,0,0);


        $this->SetFont('Arial','',12);
        $this->SetXY(10,20);
        if (isset($this->shortcut)) {$this->Cell(0,0, $this->shortcut." information - > Detail ",0,1,'L');}
        if (isset($this->vendors_result)) {$this->Cell(0,0, "Vendors information - > List ",0,1,'L');;}
        if (isset($this->coas_result)) {$this->Cell(0,0, "COAs information - > List ",0,1,'L');}
        $this->SetXY(5,10);
        $this->Ln(5);
        $this->setY(35);*/

    }

    //Page footer

    function Footer() {
        //Page number
        if ($this->PageNo()!=1) {
            $this->SetXY(-20,-15);
            $this->SetFont('Helvetica','',10);
            $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');

        }
        $this->custom_page_num = $this->PageNo();

    }

    function  PrintContent(){

        //$this->Ln(10);
        $this->SetFontSize(13.5);
        $this->SetXY(10,10);
        $this->SetFont('HelveticaB','',13.5);
        $this->Cell(0,6,$this->poFormatting->PO_Format_Client_Name);$this->Ln();
        $this->Cell(0,6,$this->poFormatting->PO_Format_Project_Name);$this->Ln();
        $this->SetFont('Helvetica','',9.8);
        $this->Cell(0,6,$this->poFormatting->PO_Format_Address);$this->Ln();
        $this->Cell(0,6,$this->poFormatting->PO_Format_City_St_ZIP);$this->Ln();
        $this->Cell(0,6,$this->poFormatting->PO_Format_Phone);$this->Ln();

        $this->printTopRightBlock(140,10);
        $this->printLeftBlock(10,40);

        $this->printDescriptionBlock(10,90,5);
        $x =$this->GetX();
        $y = $this->GetY();

        $this->printTotalBlock($x,$y);

        $this->printAccountBlock($x,$y);

        $this->printApprovalBlock(125,$y+40);

        $this->printAddLanguageBlock(10,$y+50,30);

        $x =$this->GetX();
        $y = $this->GetY();
        $this->printDistributionBlock(10,$y+10,30);



        if ($this->desc_break_index ) {
            $this->printAdditionalDeskPage();
        }

        if ($this->dist_break_index) {
            $this->printAdditionalDistPage();
        }

    }

    public function printAdditionalDeskPage () {
        $h = 5;

        $this->AddPage('P');
        $this->SetXY(10,5);
        $this->Cell(30,$h,'This is an additional page for PO # '.$this->po->PO_Number,'','L');
        $this->SetXY(10,10);

        if ($this->desc_break_index && count($this->poDecrDetails)) {
            $this->SetFont('HelveticaB','',10);
            $this->Cell(30,$h,'Description','','L');
            $this->SetXY(20,15);
            $this->Cell(30,$h,'Continued ...','','L');

            $this->poDecrDetails = array_slice($this->poDecrDetails,$this->desc_break_index);
            $this->printDescriptionBlock(10,20,50);
            //$this->printTotalBlock($this->GetX(),$this->GetY());
        }


    }

    public function printAdditionalDistPage () {
        $h = 5;

        if ($this->GetY() >= 230) {
            $this->AddPage('P');
            $this->SetXY(10,5);
            $this->Cell(30,$h,'This is an additional page for PO # '.$this->po->PO_Number.' dist ','','L');
            $this->SetXY(10,10);
        }


            if ($this->dist_break_index && count($this->poDists)) {


                $this->poDists = array_slice($this->poDists,$this->dist_break_index);
                $this->SetFont('HelveticaB','',10);
                $this->SetXY(10,$this->GetY()+10);
                $this->Cell(30,$h,'Distribution','','L');
                $this->SetXY(20,$this->GetY()+5);
                $this->Cell(30,$h,'Continued ...','','L');
                $this->printDistributionBlock(10,$this->GetY()+5,50);

            }




        if ($this->approval_break_index && count($this->aproval_detail_list)) {

            $this->aproval_detail_list= array_slice($this->aproval_detail_list,$this->approval_break_index);
            $this->SetFont('HelveticaB','',10);
            $this->SetXY(10,$this->GetY()+10);
            $this->Cell(30,$h,'Approval','','L');
            $this->SetXY(15,$this->GetY()+5);
            $this->Cell(30,$h,'Continued ...','','L');
            $this->printApprovalBlock(10,$this->GetY(),50);

        }




    }

    public function printApprovalBlock ($x,$y) {
        $this->SetXY($x,$y);
        $h = 5;
        if ($this->approval_break_index == 0) { //only for first page we printing next block

            $this->SetFont('HelveticaB','',11);
            $this->SetTextColor(65,181,11);
            $this->Cell(25,$h,($this->po->PO_Approved) ? 'APPROVED ' : '','','R');
            $this->SetFont('HelveticaB','',10);
            $this->SetTextColor(0,0,0);
            $this->Cell(30,$h,($this->po->PO_Approved) ? date('m/d/Y')  : '','',$h,'L');

            $this->SetX($x);
            $this->SetFont('Helvetica','',10);
            $this->SetTextColor(0,0,0);
            $this->Cell(30,$h,'Requested By:','','L');
            $this->MultiCell(50,$h,($this->signRequestedByUser) ? Helper::shortenString($this->signRequestedByUser->person->First_Name . ' ' . $this->signRequestedByUser->person->Last_Name,27) : '','B','L');
        }

        $this->SetFont('Helvetica','',10);
        $i =0;
        foreach ($this->aproval_detail_list as $approval_item) {

            $y1= $this->GetY();
            $x1 = $x+30;
            $this->SetXY($x1,$y1);
            $this->MultiCell(30,$h,$approval_item['name'],'','L');

            $y2= $this->GetY();
            $h2 = $y2 - $y1;
            //returning previous line

            $this->SetXY($x1+30,$y1);
            $this->Cell(30,$h2,$approval_item['date'],'','L');
            $this->Ln($h2+2);
            $i++;

            //transfer to another page
            if ($this->GetY()>250) {
                $this->approval_break_index = $i;
                $this->SetXY($x1,$y1+$h2);
                $this->Cell(50,6,'see next page ...','','','R');
                break;
            }

        }





    }

    public function printDistributionBlock ($x,$y,$limit) {

        $this->SetXY(10,$y);
        $this->dist_break_index = 0; //renull
        $h = 4;
        if ($limit == 30) $this->Cell(30,$h,'Distribution','','5','C'); //only on the first page
        $this->SetFont('HelveticaB','',9);


        $this->Cell(50,$h,' GL Code','1','','C');
        $this->Cell(30,$h,'Amount','1','','C');
        $this->Cell(60,$h,'Desc','1','','C');
        $this->Ln($h);

        $this->SetFont('Helvetica','',8);

        $i=0;
        foreach($this->poDists as $key => $dist) {

            $this->Cell(50,$h,Helper::shortenString($dist->PO_Dists_GL_Code,30),'1','','C');
            $this->Cell(30,$h,$dist->PO_Dists_Amount,'1','','C');
            $this->Cell(60,$h, Helper::shortenString($dist->PO_Dists_Description,35),'1','','L');
            $this->Ln($h);
            $i++;


            if ($this->GetY()>260) {
                $this->dist_break_index = $i;
                $this->Cell(140,$h,'see next page ...','1','','C');
                $this->printAdditionalDistPage();
                break;
            }
        }

        //adittional lines if needed
        if ($limit == 30 && !$this->dist_break_index) {
            for($j = $i;  $j <= 5; $j++) {
                $this->Cell(50,$h,'','1','','C');
                $this->Cell(30,$h,'','1','','C');
                $this->Cell(60,$h,'','1','','C');
                $this->Ln($h);
            }
        }
    }

    public function printAddLanguageBlock ($x,$y) {

        $this->SetXY(10,$y);
        $h = 4;
        $this->SetFont('Helvetica','',9);
        $this->MultiCell(80,$h,$this->poFormatting->PO_Format_Addl_Language,'','L');
        $this->Ln($h);
    }

    public function printAccountBlock ($x,$y) {
        $this->SetXY(10,$y+10);

        $h = 5;

        $this->SetFont('HelveticaB','',9);
        $this->Cell(60,$h,'Check One','',$h,'C');

        $this->SetFont('Helvetica','',9);
        $this->Cell(30,$h,'On Account','','','R');
        $this->Cell(30,$h,($this->po->Payment_Type == "OA") ? 'x' : '','1',$h,'C');

        $this->SetX(10);
        $this->Cell(30,$h,'Credit Card','','','R');
        $this->Cell(30,$h,($this->po->Payment_Type == "CC") ? 'x' : '','1','','C');
        $this->Cell(20,$h,'last 4 digits','','','R');
        $this->Cell(20,$h, ($this->po->Payment_Type == "CC") ? $this->po->PO_Card_Last_4_Digits : '','B',$h);

        $this->SetX(10);
        $this->Cell(30,$h,'Other','','','R');
        $this->Cell(30,$h,($this->po->Payment_Type != "CC" && $this->po->Payment_Type != "OA") ? 'x' : '','1','','C');
        $this->Cell(20,$h, ($this->po->Payment_Type != "CC" && $this->po->Payment_Type != "OA") ? $this->paymentTypes[$this->po->Payment_Type] : '',$h);
    }


    public function printTotalBlock ($x,$y) {
        $h = 5;

        $y +=6;
        $x=145;
        $this->SetXY($x,$y);
        $this->SetFont('HelveticaB','',9);
            $this->Cell(30,$h,'Subtotal','','','R');
        $this->SetFont('Helvetica','',9);
            $this->Cell(30,$h,$this->po->PO_Subtotal,'1',$h,'C');

            $this->SetX($x);
            $this->Cell(30,$h,'Tax','','','R');
            $this->Cell(30,$h,$this->po->PO_Tax,'1',$h,'C');

            $this->SetX($x);
            $this->Cell(30,$h,'Delivery Charge','','','R');
            $this->Cell(30,$h,$this->po->PO_Delivery_Chg,'1',$h,'C');

            $this->SetX($x);
            $this->Cell(30,$h,'Other','','','R');
            $this->Cell(30,$h,$this->po->PO_Other_Chg,'1',$h,'C');

        $this->SetFont('HelveticaB','',9.8);
        $this->SetX($x);
            $this->Cell(30,$h,'Total','','','R');
        $this->SetFont('Helvetica','',9.8);
            $this->Cell(30,$h,$this->po->PO_Total,'1','','C');


    }


    public function printDescriptionBlock ($x,$y,$limit) {

        $ln = 7;
        $this->SetXY($x,$y);
        //$this->SetFont('CourierB','',9);
        $this->SetFont('HelveticaB','',9.8);
        $this->Cell(20,6,'Qty','','','C');
        $this->Cell(100,6,'Description','','','C');
        $this->Cell(15,6,'Purchase','','','C');
        $this->Cell(15,6,'Rental','','','C');
        $this->Cell(15,6,'Line #','','','C');
        $this->Cell(30,6,'Amount','','','C');
        $this->Ln(5);
        $i=0;
        //$this->SetFont('Courier','',9);
        $this->SetFont('Helvetica','',9);
        foreach($this->poDecrDetails as $key => $descDetail) {
            $this->SetX($x);

            //printing multi cell
            //1) get current coordinates
            $y1 = $this->GetY();
            $x1 = $this->GetX();
            $this->SetXY($x1+20,$y1);
            $this->MultiCell(100,5, Helper::shortenString($descDetail->PO_Desc_Desc,100),'1');

            //calculate new y
            $y2 = $this->GetY();
            $h = $y2 - $y1; //hight of row

            $this->SetXY($x,$y1); //return to the previos line and now printing other cells
            $this->Cell(20,$h,$descDetail->PO_Desc_Qty,'1','','C');


            $this->SetX($x1+120);
            $this->Cell(15,$h,($descDetail->PO_Desc_Purchase == 1) ? 'x' : '','1','','C');
            $this->Cell(15,$h,($descDetail->PO_Desc_Rental == 1) ? 'x' : '','1','','C');
            $this->Cell(15,$h,$descDetail->PO_Desc_Budget_Line_Num,'1','','C');
            $this->Cell(30,$h,$descDetail->PO_Desc_Amount,'1','','C');
            $this->Ln($h);

            $i++;
            if ($i > $limit) {
                $this->desc_break_index = $i;
                $this->Cell(195,6,'see next page ...','1','','C');
                break;
            }

            if ($this->GetY()>230) {
                $this->desc_break_index = $i;
                $this->Cell(195,6,'see next page ...','1','','C');
                $this->printAdditionalDeskPage();
                break;
            }


        }

        //adittional lines if needed
        // $limit == 5 means that this function is called om the first page,so we don't print additional rows on the second page
        if ($limit == 5) {
            for($j = $i;  $j <= 5; $j++) {
                $this->SetX($x);
                $this->Cell(20,6,'','1','','C');
                $this->Cell(100,6,'','1','','C');
                $this->Cell(15,6,'','1','','C');
                $this->Cell(15,6,'','1','','C');
                $this->Cell(15,6,'','1','','C');
                $this->Cell(30,6,'','1','','C');
                $this->Ln(6);
            }
        }


    }



    public function printLeftBlock ($x,$y) {
        $ln = 7;

        //saving curr Y position
        $y1= $this->GetY();

        $this->SetXY($x,$y);
        $this->SetFont('HelveticaB','',9.8);
            $this->Cell(20,6,'Co. Name:','','','R');
        $this->SetFont('Helvetica','',9.8);
            $company_name = $this->currentVendor->client->company->Company_Name;
            $this->MultiCell(60,6,isset($this->currentVendor->client->company) ? Helper::shortenString( $company_name , 60) : '','B','L');

        //calc new Y
        $y2 = $this->GetY();
        //finding height of previous line
        $h2 = $y2 - $y1;
        //calculating Y for next row
        $y = $y1+$h2;

        $this->SetXY($x,$y);
        $this->SetFont('HelveticaB','',9.8);
            $this->Cell(20,6,'Address:','','','R');
        $this->SetFont('Helvetica','',9.8);
            $this->Cell(60,6,isset($this->currentVendor->client->company) ? $this->currentVendor->client->company->adreses[0]->Address1 : '','B');

        $address_line = isset($this->currentVendor->client->company) ? Helper::createFullAddressLine('', $this->currentVendor->client->company->adreses[0]->City, $this->currentVendor->client->company->adreses[0]->State, $this->currentVendor->client->company->adreses[0]->ZIP) : '';
        $y += $ln;
        $this->SetXY($x,$y);
        $this->SetFont('HelveticaB','',9.8);
            $this->Cell(20,6,' ','','','R');
        $this->SetFont('Helvetica','',9.8);
            $this->Cell(60,6,' ','B');

        $y += $ln;
        $this->SetXY($x,$y);
        $this->SetFont('HelveticaB','',9.8);
            $this->Cell(20,6,'','','','R');
        $this->SetFont('Helvetica','',9.8);
            $this->Cell(60,6,$address_line,'B');


        $y += $ln;
        $this->SetXY($x,$y);
        $this->SetFont('HelveticaB','',9.8);
        $this->Cell(20,6,'Phone:','','','R');
        $this->SetFont('Helvetica','',9.8);
        $this->Cell(60,6,isset($this->currentVendor->client->company) ? $this->currentVendor->client->company->adreses[0]->Phone : '','B');

        $y += $ln;
        $this->SetXY($x,$y);
        $this->SetFont('HelveticaB','',9.8);
        $this->Cell(20,6,'Contact:','','','R');
        $this->SetFont('Helvetica','',9.8);
        $this->Cell(60,6,isset($this->vendorAdmin) ? $this->vendorAdmin->user->person->First_Name . ' ' . $this->vendorAdmin->user->person->Last_Name . ' ' . $this->vendorAdmin->user->person->Email : '','B');


    }


    public function printTopRightBlock ($x,$y) {
        $this->SetXY($x+20,$y);
        $this->SetFont('HelveticaB','',13.5);
            $this->Cell(0,6,'PURCHASE ORDER');

        $this->SetXY($x,$y+15);
        $this->SetFont('HelveticaB','',9.8);
            $this->Cell(20,6,'Number:','','','R');
            $this->Cell(35,6,$this->po->PO_Number,'B');

        $this->SetXY($x,$y+30);
        $this->SetFont('Helvetica','',9.8);
            $this->Cell(20,6,'Date:','','','R');
            $this->Cell(35,6,$this->po->PO_Date,'B');

        $this->SetXY($x,$y+37);
            $this->Cell(20,6,'Job Name:','','','R');
            $this->Cell(35,6,$this->po->PO_Date,'B');

        $this->SetXY($x,$y+50);
            $this->Cell(20,6,'Account #:','','','R');
            $this->Cell(35,6,$this->po->PO_Account_Number,'B');

        $this->SetXY($x,$y+57);
        $this->SetFont('HelveticaB','',9.8);
            $this->Cell(20,6,'Fed ID:','','','R');
        $this->SetFont('Helvetica','',9.8);
            $this->Cell(35,6,$this->currentVendor->client->company->Company_Fed_ID,'B');

    }

    public function printCounters ($count,$doc_count,$i,$num_pages) {
        $this->SetFont('Arial','',11);

        $this->SetFillColor(200,200,200);
        $this->Rect(145,17,80,10,'F');
        $this->SetY(19);
        $this->SetX(150);
        $this->Cell(0,6,$this->shortcut.'  '.$count.' of '.$doc_count.'    Page '.$i.' of '.$num_pages);

        $this->Ln();

        $this->SetFont('Times','',10);
    }


}
