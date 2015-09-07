<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 11/21/14
 * Time: 5:19 PM
 */
require_once(Yii::app()->basePath.'/extensions/Fpdf/fpdf.php');
require_once(Yii::app()->basePath.'/extensions/Fpdi/fpdi.php');
class FpdfForBatchDetail extends FPDI
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
        $this->SetFont('Arial','B',12);
        $this->SetXY(5,5);
        $this->Cell(0,0, Helper::shortenString($client->company->Company_Name,30).' - '.Helper::shortenString($project->Project_Name,20),0,1,'L');
        $this->SetFont('Arial','',12);
        $this->SetXY(5,10);
        if($this->doc_type == 'AP') {
            $this->Cell(0,0,"AP Accounting System Entry:",0,0,'L');
            $this->SetFillColor(0,135,193);
            $this->Rect(65,8,30,5,'F');

        } else {
            $this->Cell(0,0,"PO Accounting System Entry:",0,1,'L');
            $this->SetFillColor(255,127,0);
            $this->Rect(65,8,30,5,'F');
        }

       // $this->Text(0,10,"AP Batch Summary Report:  ");
        $this->SetXY(140,5);
        $this->SetFont('Arial','B',12);
        if($this->doc_type == 'AP') {
            $this->Cell(0,0,"AP Detail Report:",0,1,'R');
        } else {
            $this->Cell(0,0,"PO Detail Report:",0,1,'R');
        }
        $this->SetFont('Arial','',12);
        $this->SetXY(140,10);
        $this->Cell(0,0,"Batch ".$this->batch_id,0,1,'R');

        //$this->SetXY(120,5);
        //$this->Cell(0,0,"AP Batch Summary Report: ",0,0,'R');
        //$this->Line(5,14,205,14);
        $this->Ln(5);
        $this->Ln(5);

    }

//Page footer
    function Footer()
    {
        $this->Ln(1);
        //Position at 1.5 cm from bottom
        //;
       // $this->Line(5,265,205,265);
        //$this->Line(5,-14,205,-14);
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
        $this->SetY(30);

        foreach($exportRows as $row) {
            if($row['InvNum'] != $prev_InvNum) {
                if ($prev_InvNum!='') { $this->AddPage('P'); }
                $prev_InvNum = $row['InvNum'];
                $this->Ln();

                //preparing file - saving from database to filesystem, converting to PDf if needed
                $return_array=FileModification::prepareFile($row['docID']);
                if($return_array['ext']!='pdf'){
                    $return_array = FileModification::ImageToPdf($return_array['path_to_dir'],$return_array['filename'],$return_array['ext']);
                }

                $this->SetY(7);$this->SetX(75);$this->Cell(70,5,Helper::shortenString($return_array['filename'],30),0,0,'R');
                $this->SetY(30);

                //try to import original file, and if fail - render empty file
                try {
                    $this->setSourceFile($return_array['path_to_dir'].'/'. $return_array['filename']);

                } catch (Exception $e) {
                    $new_empty_file = FileModification::createEmptyRenderError($return_array['filename'],$return_array['path_to_dir'].'/'. $return_array['filename']);
                    try {
                        $this->setSourceFile($new_empty_file);
                    } catch (Exception $e) {
                        die ($new_empty_file);
                    }
                }

                $tplIdx = $this->importPage(1);
                $this->useTemplate($tplIdx, 10, 20, 130);

                $this->SetY(18);
                $this->Cell(135,170,'',1,0,'L');

                $this->SetY(195);
                $this->SetFont('Arial','B',16);
                $this->Cell(0,6,'Data Entry ',0,1,'L');
                $this->Line(10,$this->GetY(),205,$this->GetY());

                $this->SetFont('Arial','',12);
                $this->SetY($this->GetY()+3);
                $this->Cell(0,6,'Shortcut: '.$row['VendorID'],0,1,'L');

                $this->Cell(0,6,'Vendor name: '.Helper::shortenString($row['vendorName'],30),0,1,'L');
                $this->Cell(0,6,'Number: '.$row['InvNum'],0,1,'L');
                $this->Cell(0,6,'Date: '.$row['InvDate'],0,1,'L');
                if (isset($row['DueDate'])) $this->Cell(0,6,'Due Date: '.$row['DueDate'],0,1,'L');
                $this->Cell(0,6,'Amount: '.number_format($row['InvAmt'], 2, '.', ','),0,1,'L');

                if ($this->doc_type=='AP') {
                    $this->Cell(0,6,'AP Description : '.Helper::shortenString($row['InvDesc'],30),0,1,'L');
                } else {
                    $this->Cell(0,6,'PO Description : '.Helper::shortenString($row['InvDesc'],30),0,1,'L');
                }

                if (isset($row['Detail_1099'])) {
                    if ($row['Detail_1099']){
                        $this->Cell(0,6,'1099: '.'Yes',0,0,'L');
                    } else {
                        $this->Cell(0,6,'1099: '.'No',0,0,'L');
                    }
                }

                if (isset($row['Box'])) {
                    $this->SetX(30);
                    $this->Cell(0,6,'Box: '.$row['Box'],0,1,'L');
                }
                $this->SetY(18);
                $this->SetX(150);

                //$notes = Aps::getAPNotes($row['docID']);
                $notes = Aps::getLastNotesByApprovalValue($row['docID']);

                    $this->SetFont('Arial','B',16);
                    $this->Cell(0,6,'Notes ',0,1,'L');
                    $this->Line(150,$this->GetY(),205,$this->GetY());
                    $this->Ln();
                    $this->SetFont('Arial','',7);
                        $i = 0;
                    foreach ($notes as $note) {

                        if($i>0) {
                            $this->Ln();
                            $this->Line(150,$this->GetY(),205,$this->GetY());

                        }
                        //in order to not overlay approval block that begins from line 150
                        if ($this->GetY() < 150) {

                            $user =Users::model()->findByPk($note['User_ID']);
                            $this->SetX(150);
                                if ($note['User_ID']) $this->Cell(0,3,'By '.$user->person->First_Name.' '.$user->person->Last_Name,0,1,'L');
                            $this->SetX(150);
                                if ($note['Created']) $this->Cell(0,3,$note['Created'],0,1,'L');
                            $this->SetX(150);
                                if ($note['Comment']) $this->MultiCell( 0, 3, $note['Comment'], 0);

                        } else {
                            $this->SetX(150);$this->Cell(0,3,'some data truncated...',0,1,'L');
                        }

                        //$this->Ln();
                        //if ($note->Comment) $this->Cell(0,6,'Notes: '.$note->Comment,0,1,'L');
                        $i++;
                    }

                $this->SetY(152);

                $i = 0;
                $approval_array = Audits::getApprovalDetailList2($row['docID']);
                $approval_array = array_reverse(array_slice($approval_array, -5));


                $this->SetFont('Arial','',9);
                $this->SetX(150);$this->Cell(0,5,'Approval Block:',0,1,'L');

                $this->SetFont('Arial','U',9);

                $this->SetX(150);$this->Cell(0,5,'Name',0,0,'L');
                $this->SetX(181);$this->Cell(0,5,'Date',0,0,'L');
                $this->SetX(199);$this->Cell(0,5,'Value',0,1,'L');
                $this->SetFont('Arial','',9);

                foreach ($approval_array as $appr_item) {
                    if($i>0) {
                        //$this->Ln();
                        //$this->Line(150,$this->GetY(),205,$this->GetY());
                    }
                    $this->SetX(150);
                    $this->Cell(0,5,Helper::shortenString($appr_item['formatted_name'],18),0,0,'L');
                    $this->SetX(181);
                    $this->Cell(0,5,$appr_item['date'].'       '.$appr_item['value'],0,1,'L');


                $i++;
                }


                $this->SetY(200);


            }

            $this->Ln();
            $this->SetFont('Arial','',9);
            $this->SetX(100);
            $this->Cell(0,2,Helper::shortenString($row['GLCode'],25),0,0,'L');
            $this->SetX(130);
            $this->Cell(0,2,' | '.number_format($row['GLAmt'], 2, '.', ','),0,0,'L');
            $this->SetX(155);
            $this->Cell(0,2,' | '.Helper::shortenString($row['GLDesc'],30),0,1,'L');


        }
        /*$this->Ln();
        if ($this->GetY() > 260) {
            $this->AddPage();
        }
        $this->SetX(170);
        $this->Line(172,$this->GetY(),205,$this->GetY());
        $this->Ln();
        $this->SetX(175);
        $this->Cell(0,4,'Batch Total: '.number_format($total, 2, '.', ','),0,1,'R');*/

    }
}