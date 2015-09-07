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
class FpdfAp extends FPDI
{
    var $custom_page_num;

    var $ap,$poFormatting,$apDecrDetails,$apDists,$currentVendor,$signRequestedByUser,$aproval_detail_list,$approved,$paymentTypes,$ckRequest;
    var $vendorAdmin = 0;
    var $desc_break_index= 0;
    var $dist_break_index = 0;
    var $approval_break_index = 0;


    function setPageNo($num) {
        $this->custom_page_num = $num;
    }

    function setVariables($ap,$poFormatting,$ckRequest,$apDists,$currentVendor,$vendorAdmin,$signRequestedByUser,$aproval_detail_list,$approved){
        $this->ap = $ap;
        $this->poFormatting = $poFormatting;
        $this->ckRequest = $ckRequest;


        $this->apDists = $apDists;
        $this->currentVendor = $currentVendor;
        if ($vendorAdmin) {
            $this->vendorAdmin = $vendorAdmin;
        }
        $this->signRequestedByUser = $signRequestedByUser;
        $this->aproval_detail_list = $aproval_detail_list;
        $this->approved = $approved;
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

        $this->printTopLeftBlock(15,7);
        $this->printTopRightBlock(140,7);


        $this->printLeftBlock(10,60);
        $x =$this->GetX();
        $y = $this->GetY();
        $this->printDescriptionBlock(10,$y+5,5);
        $x =$this->GetX();
        $y = $this->GetY();

        $this->printApprovalBlock(125,$y+5);

     //   $this->printAddLanguageBlock(10,$y+30,30);

        $x =$this->GetX();
        $y = $this->GetY();

        $this->printDistributionBlock(10,$y,30);




        if ($this->desc_break_index ) {
            $this->printAdditionalDeskPage();
        }

        if ($this->dist_break_index) {
            $this->printAdditionalDistPage();
        }

    }
    public function printAddLanguageBlock ($x,$y) {

        $this->SetXY(10,$y);
        $h = 4;
        $this->SetFont('Helvetica','',9);
        $this->MultiCell(80,$h,$this->poFormatting->PO_Format_Addl_Language,'','L');
        $this->Ln($h);
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
            $this->Cell(30,$h,'This is an additional page for AP # '.$this->po->PO_Number.' dist ','','L');
            $this->SetXY(10,10);
        }


            if ($this->dist_break_index && count($this->apDists)) {


                $this->apDists = array_slice($this->apDists,$this->dist_break_index);
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
            $this->Cell(25,$h,($this->ap->Approved) ? 'APPROVED ' : '','','R');
            $this->SetFont('HelveticaB','',10);
            $this->SetTextColor(0,0,0);
            $this->Cell(30,$h,($this->ap->Approved) ? date('m/d/Y')  : '','',$h,'L');

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


        $this->Cell(50,$h,'GL / Prod','1','','C');
        $this->Cell(30,$h,'Amount','1','','C');
        $this->Cell(60,$h,'Desc','1','','C');
        $this->Ln($h);

        $this->SetFont('Helvetica','',8);

        $i=0;
        foreach($this->apDists as $key => $dist) {

            if ($this->GetY()>260) {
                $this->dist_break_index = $i;
                $this->Cell(140,$h,'see next page ...','1','','C');
                $this->printAdditionalDistPage();
                break;
            }

            $this->Cell(50,$h,Helper::shortenString($dist->GL_Dist_Detail_COA_Acct_Number,30),'1','','C');
            $this->Cell(30,$h,$dist->GL_Dist_Detail_Amt,'1','','C');
            $this->Cell(60,$h, Helper::shortenString($dist->GL_Dist_Detail_Desc,35),'1','','L');
            $this->Ln($h);
            $i++;



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






    public function printDescriptionBlock ($x,$y,$limit) {

        $ln = 5;
        $y += $ln;
        $this->SetXY($x,$y);
        $this->SetFont('HelveticaB','',10);
        $this->Cell(40,$ln,'DESCRIPTION:','','','C');
        $this->SetFont('Helvetica','U',8);
        $this->MultiCell(60,$ln,$this->ap->Invoice_Reference,'','L');
    }



    public function printLeftBlock ($x,$y) {
        $ln = 5;

        //saving curr Y position
        $y1= $this->GetY();

        $this->SetXY($x,$y);
        $this->SetFont('Helvetica','',8);
            $this->Cell(20,6,'Vendor Name:','','','R');
        $this->SetFont('Helvetica','',8);
            $this->MultiCell(80,5,isset($this->currentVendor->client->company) ? Helper::shortenString($this->currentVendor->client->company->Company_Name, 60) : '','B','L');

        //calc new Y
        $y2 = $this->GetY();
        //finding height of previous line
        $h2 = $y2 - $y1;
        //calculating Y for next row
        $y = $y1+$h2;

        $this->SetXY($x,$y);
        $this->SetFont('Helvetica','',8);
            $this->Cell(20,$ln,'Address:','','','R');
        $this->SetFont('Helvetica','',8);
            $this->Cell(80,$ln,isset($this->currentVendor->client->company) ? $this->currentVendor->client->company->adreses[0]->Address1 : '','B');

        $address_line = isset($this->currentVendor->client->company) ? Helper::createFullAddressLine('', $this->currentVendor->client->company->adreses[0]->City, $this->currentVendor->client->company->adreses[0]->State, $this->currentVendor->client->company->adreses[0]->ZIP) : '';


        $y += $ln;
        $this->SetXY($x,$y);
        $this->SetFont('Helvetica','',8);
            $this->Cell(20,$ln,'City/State/Zip:','','','R');
        $this->SetFont('Helvetica','',8);
            $this->Cell(80,$ln,$address_line,'B');


        $y += $ln;
        $this->SetXY($x,$y);
        $this->SetFont('Helvetica','',8);
        $this->Cell(20,$ln,'Federal ID #:','','','R');
        $this->SetFont('Helvetica','',8);
        $this->Cell(80,$ln,isset($this->currentVendor->client->company) ? $this->currentVendor->client->company->Company_Fed_ID : '','B');

        $y += $ln;
        $this->SetXY($x,$y);
        $this->SetFont('HelveticaB','',7.8);
        $this->Cell(100,$ln,'(Social Security or Federal ID number required for payment)','','','C');

        $y += 4*$ln;
        $this->SetXY($x,$y);
        $this->SetFont('Helvetica','',8);
        $this->Cell(20,$ln,'(circle one) ','','','R');
        $this->SetFont('Helvetica','',8);
        $this->Cell(20,$ln,'Purchase',$this->ckRequest->CK_Req_Purchase == 1,'','C');
        $this->SetFont('Helvetica','',8);
        $this->Cell(20,$ln,'Rental',$this->ckRequest->CK_Req_Rental == 1,'','C');

        $y += $ln;
        $this->SetXY($x,$y);
        $this->SetFont('Helvetica','',8);
        $this->Cell(20,$ln,'Rental Begins:','','','C');
        $this->SetFont('Helvetica','',8);
        $this->Cell(80,$ln,$this->ckRequest->Rental_Begin ? Helper::convertDate($this->ckRequest->Rental_Begin) : '','B');


        $y += $ln;
        $this->SetXY($x,$y);
        $this->SetFont('Helvetica','',8);
        $this->Cell(20,$ln,'Rental Ends:','','','C');
        $this->SetFont('Helvetica','',8);
        $this->Cell(80,$ln, $this->ckRequest->Rental_End ? Helper::convertDate($this->ckRequest->Rental_End) : '','B');
    }


    public function printTopLeftBlock ($x,$y) {

        $this->SetXY($x,$y);
        $this->SetFont('Arial','IB',16);
        $this->Cell(50,6,'CHECK REQUEST','B','','C');

        $this->SetFontSize(13.5);
        $this->SetXY($x,$y+14);

        $this->SetFont('HelveticaB','',13.5);
        $this->Cell(50,6,$this->poFormatting->PO_Format_Client_Name,'','','C');$this->Ln();
        $this->SetX($x);
        $this->Cell(50,6,$this->poFormatting->PO_Format_Project_Name,'','','C');$this->Ln();
        $this->SetX($x);
        $this->SetFont('Helvetica','',9.8);
        $this->Cell(50,6,$this->poFormatting->PO_Format_Address,'','','C');$this->Ln();
        $this->SetX($x);
        $this->Cell(50,6,$this->poFormatting->PO_Format_City_St_ZIP,'','','C');$this->Ln();
        $this->SetX($x);
        $this->Cell(50,6,$this->poFormatting->PO_Format_Phone,'','','C');$this->Ln();

    }

    public function printTopRightBlock ($x,$y) {
        $this->SetXY($x-5,$y);

        $this->SetFont('HelveticaB','',13.5);
            $this->Cell(35,6,$this->ap->Invoice_Number,'');

        $this->SetXY($x-25,$y+14);
        $this->SetFont('Helvetica','',9.8);
            $this->Cell(20,6,'Date:','','','R');
            $this->Cell(70,6,$this->ap->Invoice_Date,'B');

        $this->SetXY($x+4,$y+22);
        $this->SetFont('Helvetica','',9.8);
            $this->Cell(18,6,'Check Due Date:','','','R');
            $this->Cell(44,6,$this->ap->Invoice_Due_Date,'B');


        $this->SetXY($x-25,$y+35);
        $this->SetFont('Helvetica','',9.3);
        $this->Cell(20,6,'AMOUNT:','','','R');
        $this->Cell(70,6,'$ ' . number_format(floatval($this->ap->Invoice_Amount), 2),'B');


        $vendor_admin_str = '';
        if ($this->vendorAdmin) {
            $vendor_admin_str = $this->vendorAdmin->user->person->First_Name . ' ' . $this->vendorAdmin->user->person->Last_Name . ' ' . $this->vendorAdmin->user->person->Email;
        }
        $this->SetXY($x-25,$y+50);
        $this->SetFont('Helvetica','',8);
        $this->Cell(20,6,'Contact:','','','R');
        $this->Cell(70,6,'$' . $vendor_admin_str,'B');

        $this->SetXY($x-25,$y+56);
        $this->SetFont('Helvetica','',8);
        $this->Cell(20,6,'Phone #:','','','R');
        $this->Cell(70,6,'$' . isset($this->currentVendor->client->company) ? $this->currentVendor->client->company->adreses[0]->Phone : '','B');

        $this->SetXY($x-25,$y+62);
        $this->SetFont('Helvetica','',8);
        $this->Cell(20,6,'Fax #:','','','R');
        $this->Cell(70,6,'$' . isset($this->currentVendor->client->company) ? $this->currentVendor->client->company->adreses[0]->Fax : '','B');

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
