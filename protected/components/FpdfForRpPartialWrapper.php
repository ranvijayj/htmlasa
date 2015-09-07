<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 11/21/14
 * Time: 5:19 PM
 */
require_once(Yii::app()->basePath.'/extensions/Fpdf/fpdf.php');
require_once(Yii::app()->basePath.'/extensions/Fpdi/fpdi.php');
class FpdfForRpPartialWrapper extends FPDI
{
    var $custom_page_num;


    function setPageNo($num) {
        $this->custom_page_num = $num;
    }

    var $doc_native_result;
    var $doc_documents_data;
    var $doc_images_data;
    var $shortcut;
    var $path;


   function setGeneralDocsVariabled ($doc_native_result,$doc_documents_data,$doc_images_data,$path,$shortcut){
        $this->doc_native_result = $doc_native_result;
        $this->doc_documents_data = $doc_documents_data;
        $this->doc_images_data = $doc_images_data;
        $this->path = $path;
        $this->shortcut = $shortcut;
    }

    var $company_result;
    var $address_result;
    var $users_result;
    var $project_result;


    function setGeneralVariabled ($company_result,$address_result,$users_result,$project_result){
        $this->company_result = $company_result;
        $this->address_result = $address_result;
        $this->users_result = $users_result;
        $this->project_result = $project_result;
    }


    var $vendors_result;
    function setVendorsVariabled ($vendors_result){
        $this->vendors_result = $vendors_result;
    }

    var $coas_result;
    function setCoasVariabled ($coas_result){
        $this->coas_result = $coas_result;
    }



    //Page header
    function Header()
    {



        $this->SetFont('Arial','',12);
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
        $this->setY(35);

    }

    //Page footer

    function Footer() {
            $this->Ln(1);
            //Position at 1.5 cm from bottom
            //;
            $this->Line(5,265,215,265);
            //$this->Line(5,-14,205,-14);
            //Arial italic 8
            $this->SetFont('Arial','I',8);
            //$this->SetY(-15);

            $this->SetXY(5,-15);
            $this->Cell(0,10,'Copyright (c) 2013 All Rights Reserved Mountain Asset Group, Inc' ,0,0,'L');

            $this->SetXY(-90,-15);
            $this->Cell(0,10,date("Y-m-d H:i:s") ,0,0,'C');

            //Page number
            $this->SetXY(-20,-15);
            $this->Cell(0,10,'Page '.$this->custom_page_num,0,0,'C');
            $this->custom_page_num++;
    }

    function  PrintGeneralContent(){

        $this->SetY(30);

        //$this->Ln(10);
        $this->SetFontSize(15);
        $this->SetX(60);
        $this->Cell(0,6,"Client : ".$this->company_result['Company_Name']);$this->Ln();
        $this->SetFontSize(10);
        if ( isset($this->company_result['Company_Fed_ID']) ) {
            $this->SetX(60);
            $this->Cell(0,6,$this->company_result['Company_Fed_ID']);
            $this->Ln();
        }
        $this->SetX(60);
        if ( isset($this->company_result['SSN']) && $this->company_result['SSN']!='' ) {

            $this->Cell(0,6,"SSN : ".$this->company_result['SSN']);
            $this->Cell(0,6,$this->company_result['Company_Fed_ID'],0,0,'C');
            $this->Ln();
        }

        if ( isset($this->company_result['Email']) && $this->company_result['Email']!='' ) {
            $this->Cell(0,6,"Email : ".$this->company_result['Email']);
            $this->Ln();
        }


        $this->Ln();
        $this->SetX(60);
        if ( isset($this->address_result['Address1']) && $this->address_result['Address1']!='') {
            $this->Cell(0,6,$this->address_result['Address1']);
            $this->Ln();
        }

        $this->SetX(60);
        if ( isset($this->address_result['Address2']) && $this->address_result['Address2']!='' ) {
            $this->Cell(0,6,"Address2 : ".$this->address_result['Address2']);
            $this->Ln();
        }

        $this->SetX(60);
        if ( isset($this->address_result['City']) && $this->address_result['City']!='') {
            $this->Cell(0,6,"City : ".$this->address_result['City']);
            $this->Ln();
        }

        $this->SetX(60);
        if ( isset($this->address_result['State']) && $this->address_result['State']!='') {
            $this->Cell(0,6,"State : ".$this->address_result['State']);
            $this->Ln();
        }

        $this->SetX(60);
        if ( isset($this->address_result['Country']) && $this->address_result['Country']!='') {
            $this->Cell(0,6,"Country : ".$this->address_result['Country']);
            $this->Ln();
        }

        $this->SetX(60);
        if ( isset($this->address_result['Phone']) && $this->address_result['Phone']!='') {
            $this->Cell(0,6,"Phone : ".$this->address_result['Phone']);
            $this->Ln();
        }

        $this->SetX(60);
        if ( isset($this->address_result['Fax']) && $this->address_result['Fax']!='') {
            $this->Cell(0,6,"Fax : ".$this->address_result['Fax']);
            $this->Ln();

        }

        $this->Ln();
        $this->SetX(10);
        $this->SetFont('Times','BU',10);
        $this->Cell(0,6,"Users list:");
        $this->Ln();
        $this->SetFont('Times','',7);
        foreach ($this->users_result as $user) {
            $this->SetX(10);
            $this->Cell(0,5,$user['First_Name'].' '.$user['Last_Name']);
            $this->SetX(80);
            $this->Cell(0,5,''.$user['User_Type']);
            $this->SetX(120);
            $this->Cell(0,5,''.$user['User_Login']);
            $this->SetX(160);
            $this->Cell(0,5,' '.$user['Email']);
            $this->Ln();
        }

        $this->Ln();$this->Ln();

        $this->SetX(10);
        $this->SetFont('Times','BU',10);
        $this->Cell(0,6,"Projects list:");
        $this->Ln();
        $this->SetFont('Times','',7);
        foreach ($this->project_result as $project) {
            $this->SetX(10);
            $this->Cell(0,5,$project['Project_Name']);
            $this->SetX(50);
            $this->Cell(0,5,''.$project['Project_Description']);
            $this->SetX(110);
            $this->Cell(0,5,''.$project['Project_Prod_Number']);

            $this->SetX(120);
            $this->Cell(0,6,''.$project['PO_Starting_Number']);
            $this->SetX(140);
            $this->Cell(0,6,''.$project['Ck_Req_Starting_Numb']);
            $this->SetX(150);
            $this->Cell(0,6,''.$project['COA_Manual_Coding']);

            $this->SetX(160);
            $this->Cell(0,6,''.$project['COA_Break_Character']);

            $this->SetX(170);
            $this->Cell(0,6,' '.$project['COA_Break_Number']);

            $this->Ln();
        }
    }

    function  PrintCoasContent(){

        $this->SetY(30);

        $count = 1;
        foreach ($this->coas_result as $coa) {

            if ($this->GetY() > 250) {
                $this->AddPage();
                $this->SetY(30);
            }

            $this->SetX(10);
            $this->Cell(0,6,$count);
            $this->SetX(20);
            $this->Cell(0,6,''.$coa['COA_Name']);
            $this->SetX(120);
            $this->Cell(0,6,''.$coa['Class_Shortcut']);
            $this->SetX(140);
            $this->Cell(0,6,''.$coa['Class_Name']);

            $this->Ln();

            $this->SetX(30);
            $this->Cell(0,6,''.$coa['COA_Acct_Number']);

            $this->SetX(80);
            $this->Cell(0,6,''.$coa['COA_Budget']);
            $this->SetX(100);
            $this->Cell(0,6,''.$coa['COA_Current_Total']);
            $this->SetX(135);
            $this->Cell(0,6,''.$coa['COA_Used']);


            $this->Ln();
            $count++;

        }

    }

    function  PrintVendorsContent(){

        $this->SetY(30);

        $count = 1;
        foreach ($this->vendors_result as $vendor) {

            if ($this->GetY() > 250) {
                $this->AddPage();
                $this->SetY(30);
            }

            $this->SetX(10);
            $this->Cell(0,6,$count);
            $this->SetX(20);
            $this->Cell(0,6,''.$vendor['Company_Name']);
            $this->SetX(100);
            $this->Cell(0,6,''.$vendor['FullAddress']);
            $this->Ln();
            $this->SetX(110);
            $this->Cell(50,6,'Fed_ID: '.$vendor['Company_Fed_ID']);
            $this->SetX(150);
            $this->Cell(50,6,'SSN: '.$vendor['SSN']);

            $this->Ln();
            $count++;

        }

    }


    function  PrintApContent(){

        $this->SetY(30);

        $count = 1;
        $doc_count = count($this->doc_native_result);
        foreach ($this->doc_native_result as $doc) {

            if ($count > 1) {
                $this->AddPage();
                // $this->setY(30);

            }

            if (is_file($this->path.'/'. $doc['File_Name'])) {
                    //if sourse is not PDf we will meke it
                    $fullpath = self::ImageToPdfByFilePath($this->path.'/'. $doc['File_Name']);

                    try {
                        if (is_file($fullpath)) {
                            $num_pages = $this->setSourceFile($fullpath);
                        } else {
                            //if file can't be extracted from database or wasn't attached due to some errors
                            $fullpath = self::createAbsentFile ($this->shortcut,$doc['Document_ID'],$fullpath);
                            $num_pages = $this->setSourceFile($fullpath);
                        }

                    } catch (Exception $e) {

                        $new_empty_file = self::createEmpty($doc['File_Name'],$fullpath);
                        try {
                            $num_pages = $this->setSourceFile($new_empty_file);
                        } catch (Exception $e) {
                            die ($fullpath);
                        }
                    }

                    for ($i = 1;$i<=$num_pages;$i++) {

                        if ($i== 1) {
                            //first page printing
                            $this->printCounters($count,$doc_count,$i,$num_pages);
                            $this->SetY(30);
                            $tplIdx = $this->importPage($i);
                            $this->useTemplate($tplIdx, 10, 30, 120);
                            $this->Cell(135,170,'',1,0,'L');

                            //data entry block
                            $this->SetXY(150,40);
                            $this->Cell(0,6,'Date : '.$doc['Invoice_Date']);
                            $this->Ln();
                            $this->SetX(150);
                            $this->Cell(0,6,'Invoice Due Date : '.$doc['Invoice_Due_Date']);
                            $this->Ln();

                            $this->SetX(150);
                            $this->Cell(0,6,'Created : '.$doc['DocumentsCreated']);
                            $this->Ln();

                            $this->SetX(150);
                            $this->Cell(0,6,'Invoice Number : '.$doc['Invoice_Number']);
                            $this->Ln();

                            $this->SetX(150);
                            $this->Cell(0,6,'AMOUNT : '.$doc['Invoice_Amount']);
                            $this->Ln();

                            $this->SetX(150);
                            if ($doc['Payment_ID']!=0) {
                                $this->Cell(0,6,'No payments connected');
                            } else {
                                $this->Cell(0,6,'PAYMENT CONNECTED');
                            }
                            $this->Ln();

                            $this->SetX(150);
                            if ($doc['AP_Backup_Document_ID']!=0) {
                                $this->Cell(0,6,'No backups connected');
                            } else {
                                $this->Cell(0,6,'BACKUP CONNECTED');
                            }
                            $this->Ln();
                            $this->SetY(210);

                            if (count($doc['dists'])>0) {
                                $this->SetX(30);
                                $this->Cell(0,6,'GL/Prod                    AMOUNT                Desc ');
                                $this->Ln();
                                foreach ($doc['dists'] as $dist){
                                    $this->SetX(30);
                                    $this->Cell(0,6,$dist['GL_Dist_Detail_COA_Acct_Number']);
                                    $this->SetX(60);
                                    $this->Cell(0,6,$dist['GL_Dist_Detail_Amt']);
                                    $this->SetX(90);
                                    $this->Cell(0,6,$dist['GL_Dist_Detail_Desc']);
                                    $this->Ln();
                                }
                            }
                        } else {
                            $this->AddPage();
                            $this->printCounters($count,$doc_count,$i,$num_pages);
                            $this->SetY(30);
                            $tplIdx = $this->importPage($i);
                            $this->useTemplate($tplIdx, 10, 30, 180);
                        }


                    }
                    $count++;

            }


        }

    }

    function  PrintPoContent(){

        $this->SetY(30);

        $count = 1;
        $doc_count = count($this->doc_native_result);
        foreach ($this->doc_native_result as $doc) {

            if ($count > 1) {
                $this->AddPage();
                // $this->setY(30);

            }

            //if sourse is not PDf we will make it
            if (is_file($this->path.'/'. $doc['File_Name'])) {
                    $fullpath = self::ImageToPdfByFilePath($this->path.'/'. $doc['File_Name']);

                    try {
                        if (is_file($fullpath)) {
                            $num_pages = $this->setSourceFile($fullpath);
                        } else {
                            //if file can't be extracted from database or wasn't attached due to some errors
                            $fullpath = self::createAbsentFile ($this->shortcut,$doc['Document_ID'],$fullpath);
                            $num_pages = $this->setSourceFile($fullpath);
                        }

                    } catch (Exception $e) {

                        $new_empty_file = self::createEmpty($doc['File_Name'],$fullpath);
                        try {
                            $num_pages = $this->setSourceFile($new_empty_file);
                        } catch (Exception $e) {
                            die ($fullpath);
                        }
                    }

                    for ($i = 1;$i<=$num_pages;$i++) {

                        if ($i== 1) {
                            //first page printing
                            $this->printCounters($count,$doc_count,$i,$num_pages);
                            $this->SetY(30);
                            $tplIdx = $this->importPage($i);
                            $this->useTemplate($tplIdx, 10, 30, 120);
                            $this->Cell(135,170,'',1,0,'L');

                            //data entry block
                            $this->SetXY(150,40);
                            $this->Cell(0,6,'Date : '.$doc['PO_Date']);
                            $this->Ln();

                            $this->SetX(150);
                            $this->Cell(0,6,'Created : '.$doc['DocumentsCreated']);
                            $this->Ln();

                            $this->SetX(150);
                            $this->Cell(0,6,'Payment Type: '.$doc['Payment_Type']);
                            $this->Ln();

                            $this->SetX(150);
                            $this->Cell(0,6,'PO Number : '.$doc['PO_Number']);
                            $this->Ln();

                            $this->SetX(150);
                            $this->Cell(0,6,'TOTAL : '.$doc['PO_Total']);
                            $this->Ln();

                            $this->SetX(150);
                            if ($doc['PO_Backup_Document_ID']!=0) {
                                $this->Cell(0,6,'No backups connected');
                            } else {
                                $this->Cell(0,6,'BACKUP CONNECTED');
                            }
                            $this->Ln();
                            $this->SetY(210);

                            if (count($doc['dists'])>0) {
                                $this->SetX(30);
                                $this->Cell(0,6,'GL/Prod                    AMOUNT                Desc ');
                                $this->Ln();
                                foreach ($doc['dists'] as $dist){
                                    $this->SetX(30);
                                    $this->Cell(0,6,$dist['PO_Dists_GL_Code']);
                                    $this->SetX(60);
                                    $this->Cell(0,6,$dist['PO_Dists_Amount']);
                                    $this->SetX(90);
                                    $this->Cell(0,6,$dist['PO_Dists_Description']);
                                    $this->Ln();
                                }
                            }
                        } else {
                            $this->AddPage();
                            $this->printCounters($count,$doc_count,$i,$num_pages);
                            $this->SetY(30);
                            $tplIdx = $this->importPage($i);
                            $this->useTemplate($tplIdx, 10, 30, 180);
                        }


                    }
                    $count++;

            }


        }

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

    function  PrintGeneralDocsContent(){

        $this->SetY(30);

        $count = 1;
        $doc_count = count($this->doc_native_result);
        foreach ($this->doc_native_result as $doc) {

            if ($count > 1) {
                $this->AddPage();

            }

            //importing existing pdf
            $fullpath = self::ImageToPdfByFilePath($this->path.'/'. $doc['File_Name']);
            try {
                $num_pages = $this->setSourceFile($fullpath);
            } catch (Exception $e) {
                $new_empty_file = self::createEmpty($doc['File_Name'],$fullpath);
                $num_pages = $this->setSourceFile($new_empty_file);
            }

            for ($i = 1;$i<=$num_pages;$i++) {

                if ($i== 1) {
                    //first page printing
                    $this->printCounters($count,$doc_count,$i,$num_pages);
                    $this->SetY(30);
                    $tplIdx = $this->importPage($i);
                    $this->useTemplate($tplIdx, 10, 30, 120);
                    $this->Cell(135,170,'',1,0,'L');


                    foreach ($doc as $key => $value) {
                        $this->SetX(150);
                        $this->Cell(0,6,$key);
                        $this->SetX(180);
                        $this->Cell(0,6,$value,0,0,'R');
                        $this->Ln();

                    }


                    $this->Ln();


                } else {
                    $this->AddPage();
                    $this->printCounters($count,$doc_count,$i,$num_pages);
                    $this->SetY(30);
                    $tplIdx = $this->importPage($i);
                    $this->useTemplate($tplIdx, 10, 30, 180);

                }


            }
            $count++;




        }

    }

    public static function ImageToPdfByFilePath($path){

        require_once(Yii::app()->basePath.'/extensions/Fpdf/fpdf.php');
        //require_once(Yii::app()->basePath.'/extensions/Fpdi/fpdi.php');
        $return_result = $path;

        $parts=explode('/',$path);

        $filename = $parts[count($parts)-1];
        $filetype = explode('.',$filename);
        $filetype = $filetype[count($filetype)-1];

        if ( strtoupper($filetype)!='PDF' ) {
            //if file not pdf
            $size = getimagesize ($path);
            $width=$size[0];
            $height=$size[1];

            //$path=$path_to_dir.'/'.$filename;
            try{
                $pdf = new FPDF();
                if ($width>$height) {
                    $opientation='L';
                    $pdf->AddPage($opientation);
                    $pdf->Image($path,0,1,295,strtoupper ($filetype));
                } else {
                    $opientation='P';
                    $pdf->AddPage($opientation);
                    $pdf->Image($path,0,1,210,strtoupper ($filetype));
                }


                $pdf->Output($path.'.pdf','F');
                $return_result = $path.'.pdf';

            }catch (Exception $e) {
                $result['error']="Could not render this file";
            }

        }
        return $return_result;
    }

    public static function createEmpty($filename,$filepath){


        require_once(Yii::app()->basePath.'/extensions/Fpdf/fpdf.php');

        $pdf = new FPDF('P','mm','Letter');

        $pdf->SetAuthor('ASA AP');
        $pdf->SetTitle('Render error');
        //set font for the entire document
        $pdf->SetFont('Helvetica','B',20);
        $pdf->SetTextColor(255,0,0);
        //set up a page
        $pdf->AddPage('P');
        $pdf->SetDisplayMode(90,'default');

        //Set x and y position for the main text, reduce font size and write content
        $pdf->SetXY (30,55);
        $pdf->SetFontSize(20);
        $pdf->Write(5,'Existing document '.$filename.' can\'t be rendered');
        //Output the document
        $pdf->Output($filepath,'F');
        $pdf->Close();

        return $filepath;
    }



    function _putpages()
    {
        $nb = $this->page;
        if(!empty($this->AliasNbPages))
        {
            // Replace number of pages
            for($n=1;$n<=$nb;$n++)
            {
                if($this->compress)
                    $this->pages[$n] = gzcompress(str_replace($this->AliasNbPages,$nb,gzuncompress($this->pages[$n])));
                else
                    $this->pages[$n] = str_replace($this->AliasNbPages,$nb,$this->pages[$n]);
            }
        }
        if($this->DefOrientation=='P')
        {
            $wPt = $this->DefPageSize[0]*$this->k;
            $hPt = $this->DefPageSize[1]*$this->k;
        }
        else
        {
            $wPt = $this->DefPageSize[1]*$this->k;
            $hPt = $this->DefPageSize[0]*$this->k;
        }
        $filter = ($this->compress) ? '/Filter /FlateDecode ' : '';
        for($n=1;$n<=$nb;$n++)
        {
            // Page
            $this->_newobj();
            $this->_out('<</Type /Page');
            $this->_out('/Parent 1 0 R');
            if(isset($this->PageSizes[$n]))
                $this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]',$this->PageSizes[$n][0],$this->PageSizes[$n][1]));
            $this->_out('/Resources 2 0 R');
            if(isset($this->PageLinks[$n]))
            {
                // Links
                $annots = '/Annots [';
                foreach($this->PageLinks[$n] as $pl)
                {
                    $rect = sprintf('%.2F %.2F %.2F %.2F',$pl[0],$pl[1],$pl[0]+$pl[2],$pl[1]-$pl[3]);
                    $annots .= '<</Type /Annot /Subtype /Link /Rect ['.$rect.'] /Border [0 0 0] ';
                    if(is_string($pl[4]))
                        $annots .= '/A <</S /URI /URI '.$this->_textstring($pl[4]).'>>>>';
                    else
                    {
                        $l = $this->links[$pl[4]];
                        $h = isset($this->PageSizes[$l[0]]) ? $this->PageSizes[$l[0]][1] : $hPt;
                        $annots .= sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]>>',1+2*$l[0],$h-$l[1]*$this->k);
                    }
                }
                $this->_out($annots.']');
            }
            if($this->PDFVersion>'1.3')
                $this->_out('/Group <</Type /Group /S /Transparency /CS /DeviceRGB>>');
            $this->_out('/Contents '.($this->n+1).' 0 R>>');
            $this->_out('endobj');
            // Page content
            $p = $this->pages[$n];
            $this->_newobj();
            $this->_out('<<'.$filter.'/Length '.strlen($p).'>>');
            $this->_putstream($p);
            $this->_out('endobj');
        }
        // Pages root
        $this->offsets[1] = strlen($this->buffer);
        $this->_out('1 0 obj');
        $this->_out('<</Type /Pages');
        $kids = '/Kids [';
        for($i=0;$i<$nb;$i++)
            $kids .= (3+2*$i).' 0 R ';
        $this->_out($kids.']');
        $this->_out('/Count '.$nb);
        $this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]',$wPt,$hPt));
        $this->_out('>>');
        $this->_out('endobj');
    }

    function _endpage()
    {
        parent::_endpage();
        if($this->compress)
            $this->pages[$this->page] = gzcompress($this->pages[$this->page]);
    }


}
