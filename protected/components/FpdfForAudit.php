<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 11/21/14
 * Time: 5:19 PM
 */
require_once(Yii::app()->basePath.'/extensions/Fpdf/fpdf.php');
require_once(Yii::app()->basePath.'/extensions/Fpdi/fpdi.php');
class FpdfForAudit extends FPDI
{
    var $doc_id;
    var $audits;
    var $document;
    var $user;
    var $doc_type;


    function setVariabled ($doc_id,$audits){
        $this->doc_id = $doc_id;
        $this->audits = $audits;
        $this->document = Documents::model()->findByPk($doc_id);
        $this->doc_type = Documents::model()->findByPk($doc_id)->Document_Type;
        $this->user = Users::model()->with('person')->findByPk($this->document->User_ID);
    }

    //Page header
    function Header()
    {
        $this->SetFont('ARIAL','B',14);
            $this->SetXY(20,10);
            $this->Cell(0,8, 'Audit trail',0,1,'L');
        $this->SetFont('ARIALN','',12);
            $this->SetX(25);
            $this->Cell(0,8, 'Document Type: '.$this->document->Document_Type,0,1,'L');

        if($this->doc_type == 'AP') {

            $this->SetFillColor(0,135,193);
            $this->Rect(60,19,20,5,'F');

        } else {

            $this->SetFillColor(255,127,0);
            $this->Rect(60,19,20,5,'F');
        }


        $this->SetX(25);
            $this->Cell(0,8, 'Created on : '.$this->document->Created,0,0,'L');
            //$this->ln(0);

            $this->SetX(95); $this->Cell(0,8, ' Created by : '.$this->user->person->First_Name.' '.$this->user->person->Last_Name,0,1,'L');

    }

//Page footer
    function Footer()
    {
        $this->SetFont('Arial','',7.4);
        $this->SetXY(70,-20);
        $this->Cell(0,8, 'Copyright '.html_entity_decode('&#64;',ENT_HTML401,'UTF-8').'2013 All Rights Reserved Mountain Asset Group, Inc',0,0,'L');

        $this->SetXY(170,-20);
        $cur_date = Helper::convertDate(date("Y-m-d H:i:s"));
        $this->Cell(0,8,$cur_date ,0,0,'C');

        //Page number
        $this->SetXY(195,-20);
        $this->Cell(0,8,$this->PageNo().'/{nb}',0,0,'C');

    }

    function  PrintContent(){
        $prev_InvNum='';
        $total = 0;

        $this->SetY(40);
        $this->SetX(20);

        $this->SetFont('ARIALN','',9);

        $this->Cell(50,6,'Date Time',1,0,'C');
        $this->Cell(50,6,'Action',1,0,'C');
        $this->Cell(50,6,'User',1,0,'C');
        $this->Cell(30,6,'Approval Value',1,0,'C');
        $this->Ln();

        foreach($this->audits as $audit) {

            $user = Users::model()->with('person')->findByPk($audit->Event_User_ID);
            $user_str = $user->person->First_Name.' '.$user->person->Last_Name;
            $this->SetX(20);
            $this->Cell(50,6,$audit->Event_Date,1,0,'L');
            $this->Cell(50,6,$audit->Event_Type,1,0,'L');
            $this->Cell(50,6,Helper::shortenString($user_str,32),1,0,'L');
            $this->Cell(30,6,$audit->User_Appr_Value,1,0,'L');
            $this->Ln();

        }

        $y = 110; $x = 20;
        if ($this->GetY() > 115) {
            $this->AddPage();
            $this->SetX(20);
            $y = 120;
        }
               //preparing file - saving from database to filesystem, converting to PDf if needed
               //$y = $this->GetY();




                $return_array=FileModification::prepareFile($this->document->Document_ID);
                if($return_array['ext']!='pdf'){
                    $return_array = FileModification::ImageToPdf($return_array['path_to_dir'],$return_array['filename'],$return_array['ext']);
                }

                $this->SetY($y);
                $this->SetX($x);
                $this->SetFont('ARIALN','',11.8);
                $this->Cell(70,5,'File name: '.$return_array['filename'],0,0,'L');
                $this->Ln();

                $this->setSourceFile($return_array['path_to_dir'].'/'. $return_array['filename']);
                $tplIdx = $this->importPage(1);
                $this->useTemplate($tplIdx, $x, $y+5, 100);
                $this->SetY($y+5);
                $this->SetX($x);
                //outer border
                $this->Cell(105,135,'',1,0,'L');


    }
}