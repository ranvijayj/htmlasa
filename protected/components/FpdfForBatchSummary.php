<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 11/21/14
 * Time: 5:19 PM
 */
require_once(Yii::app()->basePath.'/extensions/Fpdf/fpdf.php');
class FpdfForBatchSummary extends FPDF
{
    var $batch_id;
    var $format;
    var $cli_date;
    var $doc_type;


    function setVariabled ($batch_id,$format,$cli_date,$doc_type='AP'){
        $this->batch_id = $batch_id;
        $this->format = $format;
        $this->cli_date = $cli_date;
        $this->doc_type = $doc_type;

    }
    //Page header
    function Header()
    {
        $client =  Clients::model()->with('company')->findByPk(Yii::app()->user->clientID);
        $project = Projects::model()->findByPk(Yii::app()->user->projectID);
        $projectId = Yii::app()->user->projectID;

        //Logo
        //$this->Image('logo_pb.png',10,8,33);
        //Arial bold 15
        $this->SetFont('Arial','',12);
        $this->SetXY(5,5);
        //$this->Cell(20,10,$client->company->Company_Name.' - '.$project->Project_Name ,0,0,'L');
        $this->SetXY(5,5);
        $this->SetFont('Arial','B',12);
        $this->Cell(0,0, Helper::shortenString($client->company->Company_Name,30).' - '.Helper::shortenString($project->Project_Name,20),0,1,'L');
        $this->SetFont('Arial','',12);
        $this->SetXY(5,10);
        $this->Cell(0,0,"Export Summary:",0,1,'L');

       // $this->Text(0,10,"AP Batch Summary Report:  ");
        $this->SetFont('Arial','B',12);
        $this->SetXY(140,5);
        if($this->doc_type == 'AP') {
            $this->Cell(0,0,"AP Batch Summary Report:",0,1,'R');
        } else {
            $this->Cell(0,0,"PO Batch Summary Report:",0,1,'R');
        }
        $this->SetFont('Arial','',12);
        $this->SetXY(140,10);
        $this->Cell(0,0,"Batch ".$this->batch_id,0,1,'R');

        //$this->SetXY(120,5);
        //$this->Cell(0,0,"AP Batch Summary Report: ",0,0,'R');
        $this->Line(5,14,205,14);
        $this->Ln(5);

    }

//Page footer
    function Footer()
    {
        $this->Ln(1);
        //Position at 1.5 cm from bottom
        //;
        $this->Line(5,265,205,265);
        $this->Line(5,-14,205,-14);
        //Arial italic 8
        $this->SetFont('Arial','I',8);
        //$this->SetY(-15);
        $this->SetXY(5,-10);
        $this->Cell(0,0,"Batch #".$this->batch_id.'  '.$this->format,0,1,'L');

        $this->SetXY(-70,-15);
        $this->Cell(0,10,date("Y-m-d H:i:s", strtotime($this->cli_date)) ,0,0,'C');

        //Page number
        $this->SetXY(-20,-15);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }

    function  PrintContent($exportRows){
        $prev_InvNum='';
        $total = 0;

        $this->SetY(20);
        foreach($exportRows as $row) {

            if($row['InvNum'] != $prev_InvNum) {
                $this->Ln();
                //$this->Ln();
                if ($this->GetY() > 250) {
                    $this->AddPage();
                }

                $prev_InvNum = $row['InvNum'];
                $this->SetX(5);
                $this->Cell(0,6,Helper::shortenString($row['vendorName'],30),0,0,'L');
                $this->SetX(60);
                $this->Cell(0,6,'Number: '.$row['InvNum'],0,0,'L');
                $this->SetX(180);
                $this->Cell(0,6,number_format($row['InvAmt'], 2, '.', ','),0,1,'R');

                $total += $row['InvAmt'];
            }
            //$this->Ln()
            $this->SetX(30);
            $this->Cell(0,4,Helper::shortenString($row['GLCode'],25) ,0,0,'L');
            $this->SetX(70);
            $this->Cell(0,4,Helper::shortenString($row['GLDesc'],30),0,0,'L');
            $this->SetX(145);
            $this->Cell(0,4,number_format($row['GLAmt'], 2, '.', ','),0,1,'L');


        }
        $this->Ln();
        if ($this->GetY() > 260) {
            $this->AddPage();
        }
        $this->SetX(170);
        $this->Line(172,$this->GetY(),205,$this->GetY());
        $this->Ln();
        $this->SetX(175);
        $this->Cell(0,4,'Batch Total: '.number_format($total, 2, '.', ','),0,1,'R');

    }
}