<?php


class FileModification {

    /**
     * @param $docId
     * @return mixed
     */

    public static function prepareFileForExport($docId,$doc_type,$filepath){

        $path = $filepath;
        $image = Images::model()->findByAttributes(array(
            'Document_ID' => $docId,
        ));
        if($image) {
            $mime=explode('/',$image->Mime_Type);
            $temp_file_path=$path.'/'.$doc_type.'_'.$docId.'.'.$mime[1];
            $infile = stripslashes($image->Img);
            file_put_contents($temp_file_path, $infile);
            return $doc_type.'_'.$docId.'.'.$mime[1];
        } else {
            return false;
        }



    }

public static function prepareFile($docId){
    $path = self::createDirectory('temp_for_modification');
    $image = Images::model()->findByAttributes(array(
        'Document_ID' => $docId,
    ));
    if($image) {
        $mime=explode('/',$image->Mime_Type);
        $temp_file_path=$path.'/'.$image->File_Name;
        $infile = stripslashes($image->Img);
        file_put_contents($temp_file_path, $infile);


        $result['path_to_dir']=$path;
        $result['filename']=$image->File_Name;
        $result['ext']=$mime[1];
    } else {
        $empty_file = self::createEmpty();
        $result['path_to_dir']= $empty_file['path_to_dir'];
        $result['filename']=$empty_file['filename'];
        $result['ext']=$empty_file['ext'];

    }

    return $result;
}


public static function ConvertToImages($path_to_dir,$filename,$ext){
    if($ext=='pdf' || $ext=='PDF'){
       // $im = new Imagick($path_to_dir.'/'.$filename);
        $file=$path_to_dir.'/'.$filename;
        $output_dir=Helper::createImageDirectory(Yii::app()->user->userID);
        $output_dir=Helper::createImageDirectory(Yii::app()->user->userID.'/'.$filename);
        $part_dir='/images/out/'.Yii::app()->user->userID.'/'.$filename;


            //$path_to_dir.'/out_'.Yii::app()->user->userID.'/'.$filename;


        /*$fp_pdf = fopen($file, 'rb');

        $img = new imagick(); // [0] can be used to set page number
        $img->setResolution(300,300);
        $img->readImageFile($fp_pdf);

        $num_pages = $img->getNumberImages();
        for($i = 0;$i < $num_pages; $i++) {

                $img->setImageFormat( "png" );
                $img->writeImage($path_to_dir.'/'.$filename.'.png');
        }

        $img->clear();
        $img->destroy();*/

        $str='gs  -o "'.$output_dir.'/'.$filename.'%03d.png" -sDEVICE=pngalpha -sPAPERSIZE=a4 -dFitPage -r300 "'.$file.'"';
        //var_dump($str);die;
        exec($str);

        $i = 0;
        $dir = $output_dir;
        if ($handle = opendir($dir)) {
            while (($file = readdir($handle)) !== false){
                if (!in_array($file, array('.', '..')) && !is_dir($dir.'/'.$file)){
                    $i++;
                 $result['output_filenames'][]=array('number'=>$i,'name'=>$part_dir.'/'.$file);
            }
            }
        }

    }
    return $result;

}


public static function ImageToPdf($path_to_dir,$filename,$ext){

    require_once(Yii::app()->basePath.'/extensions/Fpdf/fpdf.php');
    //require_once(Yii::app()->basePath.'/extensions/Fpdi/fpdi.php');

    $size = getimagesize ($path_to_dir.'/'.$filename);
    $width=$size[0];
    $height=$size[1];

    $path=$path_to_dir.'/'.$filename;
    $path = FileModification::ImageToPng($path);
    $path_array = explode('/',$path);
    $filename = $path_array[count($path_array)-1];

    try{
        $pdf = new FPDF();
        if ($width>$height) {
            $opientation='L';
            $pdf->AddPage($opientation);
            $pdf->Image($path,0,1,295,strtoupper ($ext));
        } else {
            $opientation='P';
            $pdf->AddPage($opientation);
            $pdf->Image($path,0,1,210,strtoupper ($ext));
        }


        $pdf->Output($path.'.pdf','F');
    }catch (Exception $e) {
        $result['error']="Could not render this file";
    }


    unlink($path);


    $result['path_to_dir']=$path_to_dir;
    $result['filename']=$filename.'.pdf';
    $result['ext']='pdf';
    $result['path']=$path;

    return $result;

}


    public static function ImageToPdfByFilePath($path){

        require_once(Yii::app()->basePath.'/extensions/Fpdf/fpdf.php');
        //require_once(Yii::app()->basePath.'/extensions/Fpdi/fpdi.php');
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

                $imageData = addslashes(fread(fopen($path.'.pdf',"rb"),filesize($path.'.pdf')));

                unlink($path.'.pdf');
            }catch (Exception $e) {
                $result['error']="Could not render this file";
            }
        } else {
            $imageData = addslashes(fread(fopen($path,"rb"),filesize($path)));
            unlink($path);
        }

        return $imageData;

    }

    public static function  getFileNameByPath ($path) {
        if (file_exists($path)) {
            $path_parts = pathinfo($path);
            $result = $path_parts['basename'];
        }
        return $result;
    }

    public static function  getFileExtByPath ($path) {
        if (file_exists($path)) {
            $path_parts = pathinfo($path);
            $result = $path_parts['extension'];
        }
        return $result;
    }

    public static function PdfByFilePath($path){

        //Horrible piece of shit
        if (!file_exists($path)) {
            $path = $path.'.pdf';
        }

        if (file_exists($path)) {
            require_once(Yii::app()->basePath.'/extensions/Fpdf/fpdf.php');
            //require_once(Yii::app()->basePath.'/extensions/Fpdi/fpdi.php');

            $path = FileModification::ImageToPng($path);

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

                    if ($width>$height) {
                        $pdf = new FpdfImageL();
                        $pdf->AddPage("L");
                        $pdf->centreImage($path);

                    } else {

                        $pdf = new FpdfImageP();
                        $pdf->AddPage("P");
                        $pdf->centreImage($path);
                    }

                    $pdf->Output($path.'.pdf','F');

                    unlink($path);
                    $path = $path.'.pdf';
                    $filename = $filename.'.pdf';

                }catch (Exception $e) {
                    $result['error']="Could not render this file";
                }


            }
            return array(
                'filepath'=>$path,
                'filetype'=>'application/pdf',
                'filename'=>$filename
            );

        } else {
            die;
        }



    }




public static function rotateFile($path_to_dir,$filename,$rotate_direction,$page=''){

    require_once(Yii::app()->basePath.'/extensions/Fpdf/fpdf.php');
    require_once(Yii::app()->basePath.'/extensions/Fpdi/fpdi.php');

    $pdf = new FPDI();


    try{
        $pageCount = $pdf->setSourceFile($path_to_dir.'/'.$filename);

        if($rotate_direction=='rotate_button_cw') {
            $rotate_direction=90;
        } else if ($rotate_direction=='rotate_button_ccw') {
            $rotate_direction=-90;
        } else {
            $rotate_direction=0;
        }

        for($i=1;$i<=$pageCount;$i++) {
            $tplIdx = $pdf->importPage($i, '/MediaBox');
            $size = $pdf->getTemplateSize($tplIdx);

            if ($size['w'] > $size['h']) {
                    $pdf->AddPage('L', array($size['w'], $size['h']));
                } else {
                    $pdf->AddPage('P', array($size['w'], $size['h']));
                }
            $pdf->useTemplate($tplIdx, true);
            $pdf->rotatedPage[$i] = $rotate_direction;


        }
        $pdf->Output($path_to_dir.'/'.$filename,'F');
    }catch (Exception $e) {
        $result['error']="Could not render this file";
    }
    $result['path_to_dir']=$path_to_dir;
    $result['file_name']=$filename;
    $result['ext']='pdf';
    return $result;
}


public static function appendVoidText($path_to_dir,$filename,$page=''){

        require_once(Yii::app()->basePath.'/extensions/Fpdf/fpdf.php');
        require_once(Yii::app()->basePath.'/extensions/Fpdi/fpdi.php');

        $pdf = new FPDI();
        $pdf->setSourceFile($path_to_dir.'/'.$filename);
        $tplIdx = $pdf->importPage(1, '/MediaBox');
        $pdf->addPage();
        $pdf->useTemplate($tplIdx, true);
        //die("after set sourse");
        try{

            $pdf->SetFont('Helvetica','B',20);
            $pdf->SetTextColor(255,0,0);
            $pdf->SetXY (10,5);
            $pdf->SetFontSize(10);
            $pdf->Write(5,'VOID void VOID void VOID void VOID void VOID void VOID void VOID void VOID void VOID void VOID ');
            $pdf->Output($path_to_dir.'/'.$filename,'F');
        }catch (Exception $e) {
            $result['error']="Could not render this file";
            die('Could not render this file');
        }
        $result['path_to_dir']=$path_to_dir;
        $result['filename']=$filename;
        $result['ext']='pdf';
        return $result;
    }

    public static function appendApprovalSignature($path_to_dir,$filename,$page=''){
            //do nothing need to be changed
        require(Yii::app()->basePath.'/extensions/Fpdf/fpdf.php');
        require(Yii::app()->basePath.'/extensions/Fpdi/fpdi.php');


        $pdf = new FPDF();
        $pdf->setSourceFile($path_to_dir.'/'.$filename);
        $tplIdx = $pdf->importPage(1, '/MediaBox');
        $pdf->addPage();
        $pdf->useTemplate($tplIdx, true);
        //die("after set sourse");
        try{

            $pdf->SetFont('Helvetica','B',20);
            $pdf->SetTextColor(255,0,0);
            $pdf->SetXY (10,5);
            $pdf->SetFontSize(10);
            $pdf->Write(5,'VOID void VOID void VOID void VOID void VOID void VOID void VOID void VOID void VOID void VOID ');
            $pdf->Output($path_to_dir.'/'.$filename,'F');
        }catch (Exception $e) {
            $result['error']="Could not render this file";
            die('Could not render this file');
        }
        $result['path_to_dir']=$path_to_dir;
        $result['filename']=$filename;
        $result['ext']='pdf';
        return $result;
    }


public static function createEmpty(){

    require(Yii::app()->basePath.'/extensions/Fpdf/fpdf.php');
    //create a FPDF object
    $pdf=new FPDF();
    //set document properties
    $pdf->SetAuthor('ASA AP');
    $pdf->SetTitle('Void document');
    //set font for the entire document
    $pdf->SetFont('Helvetica','B',20);
    $pdf->SetTextColor(255,0,0);
    //set up a page
    $pdf->AddPage('P');
    $pdf->SetDisplayMode(real,'default');


    //Set x and y position for the main text, reduce font size and write content
    $pdf->SetXY (10,5);
    $pdf->SetFontSize(10);
    //$pdf->Write(5,'VOID void VOID void VOID void VOID void VOID void VOID void VOID void VOID void VOID void VOID ');
    //Output the document

    $path = self::createDirectory('temp_for_modification');
    $filename= "Void_pdf_". date('Y_m_d_H_i_s');

    $pdf->Output($path.'/'.$filename.'.pdf','F');

    $result['path_to_dir']=$path;
    $result['filename']=$filename.'.pdf';
    $result['ext']='pdf';

    return $result;
}

public static function writeToBase($path_to_dir,$filename,$mime,$docId){

    $image = Images::model()->findByAttributes(array(
        'Document_ID' => $docId,
    ));

    if(!$image) {
        $image= new Images();
        $image->Document_ID=$docId;
    }
    $path= $path_to_dir.'/'.$filename;
    $imageData = addslashes(fread(fopen($path,"rb"),filesize($path)));

    $image->Img = $imageData;
    $image->File_Name = $filename;
    $image->Mime_Type = $mime;
    $image->File_Hash = sha1_file($path);
    $image->File_Size = intval(filesize($path));
    $image->Pages_Count = FileModification::calculatePagesByPath($path);
    $image->save();

    unlink($path);

    $result['path_to_dir']=$path_to_dir;
    $result['file_name']=$filename;
    $result['mime']=$mime;

}



public static function convertImage($file_array){

    require(Yii::app()->basePath.'/extensions/Fpdf/fpdf.php');

    require(Yii::app()->basePath.'/extensions/Fpdi/fpdi.php');

    $pdf = new FPDI();
    $pdf->setSourceFile($file_array['path']);
    $tplIdx = $pdf->importPage(1, '/MediaBox');


}

public static function createDirectory($category)
{
        $path=Yii::app()->getBasePath()."/data/".$category;
        if(!is_dir($path)) {
            mkdir($path);
            chmod($path, 766);
        }
        return $path;
}

public static function PrepareTempPdfFile($doc_id)
{
    $image= Images::model()->findByAttributes(array('Document_ID'=>$doc_id));
    if($image) {
        $filename = 'protected/data/temp_for_pdf/' . $image->File_Name;
        $infile = stripslashes($image->Img);
        file_put_contents($filename, $infile);

        return $filename;
    }
}

public static function calculatePagesByPath($path)
    {
        require_once(Yii::app()->basePath.'/extensions/Fpdf/fpdf.php');
        require_once(Yii::app()->basePath.'/extensions/Fpdi/fpdi.php');

        try{

            $pdf = new FPDI();
            $num = $pdf->setSourceFile($path);

        } catch (Exception $e) { $num = 1;}


        return $num;
    }

    /**
     * This function was used only once after adding to Images table PageCount column for calculating
     * @param $doc_id
     * @return int
     */
    public static function calculatePagesByDocID($doc_id)
    {
        $num =1;
        $path = self::PrepareTempPdfFile($doc_id);
        if(is_file($path)) {
            try{

                $pdf = new FPDI();
                $num = $pdf->setSourceFile($path);

            } catch (Exception $e) {
                $num = 1;
            }

            @unlink($path);


        }
        return $num;
    }

    public static function concatFiles($files_path_array) {

        require_once(Yii::app()->basePath.'/extensions/Fpdf/fpdf.php');
        require_once(Yii::app()->basePath.'/extensions/Fpdi/fpdi.php');

        $pdf = new FPDI();
        foreach ($files_path_array AS $file) {
            // get the page count
            $pageCount = $pdf->setSourceFile($file);
            // iterate through all pages
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                // import a page
                $templateId = $pdf->importPage($pageNo);
                // get the size of the imported page
                $size = $pdf->getTemplateSize($templateId);

                // create a page (landscape or portrait depending on the imported page size)
                if ($size['w'] > $size['h']) {
                    $pdf->AddPage('L', array($size['w'], $size['h']));
                } else {
                    $pdf->AddPage('P', array($size['w'], $size['h']));
                }

                // use the imported page
                $pdf->useTemplate($templateId);

            }
        }

        // Output the new PDF. Here we need to overwrite existing file so first array element is used.
        $pdf->Output($files_path_array[0],'F');

        return $files_path_array[0];



    }

    public static function getPdfPagesCount($filepaht) {

        //require_once(Yii::app()->basePath.'/extensions/Fpdf/fpdf.php');
        //require_once(Yii::app()->basePath.'/extensions/Fpdi/fpdi.php');

        $pdf = new FPDI();
        $pageCount = $pdf->setSourceFile($filepaht);
        $pdf->Close();

        return $pageCount;
    }

    public static function ClearUploadDir(){

        $pathes_array = array(
            '1'=>Yii::app()->basePath.'/data/temp_for_modification/'.Yii::app()->user->userID,
            '2'=>Yii::app()->basePath.'/data/temp_for_pdf/'.Yii::app()->user->userID,
            '3'=>Yii::app()->basePath.'/data/current_uploads_files/'.Yii::app()->user->userID
        );
        //addition clearing
        foreach ($pathes_array as $key=>$value) {
            $files = glob($value.'/*'); // get all file names
            foreach($files as $file){ // iterate files
                if(is_file($file))
                    unlink($file); // delete file
                if(is_dir($file)) {
                    $files1 = glob($file.'/*');
                    foreach($files1 as $file1){
                        if(is_file($file1))
                            unlink($file1); // delete file
                    }
                }
            }

        }

    }

    public static function Delete($path)
    {
        if (is_dir($path) === true)   {
            $files = array_diff(scandir($path), array('.', '..','current_upload_files','thumbs','standart_thumbs','temporary_files'));

            foreach ($files as $file) {
                self::Delete(realpath($path) . '/' . $file);
            }
            //return rmdir($path);
        } else if (is_file($path) === true) {
            return unlink($path);
        }

        return false;
    }

    public static function EmptyDir($path,$root_dir='')
    {
        $root_dir = $root_dir ? $root_dir : $path;
        if (is_dir($path) === true)   {
            $files = array_diff(scandir($path), array('.', '..','current_upload_files','thumbs','standart_thumbs','temporary_files'));

            foreach ($files as $file) {
                self::Delete(realpath($path) . '/' . $file,$root_dir);
            }
            if ($path != $root_dir) return rmdir($path);
        } else if (is_file($path) === true) {
            return unlink($path);
        }

        return false;
    }


    public static function ImageToPng($path) {
        $parts=explode('/',$path);

        $filename = $parts[count($parts)-1];
        $filetype = explode('.',$filename);
        $filetype = strtolower($filetype[count($filetype)-1]);
        if ($filetype=='tiff' || $filetype=='tif' || $filetype=='bmp') {
            $img = new Imagick($path);
            $img->setImageFormat( "png" );
            $img->writeImage($path.'.png');
            unlink($path);
            $path = $path.'.png';
        }

        return $path;

    }

    public static function createEmptyRenderError($filename,$filepath){


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
        $pdf->SetFontSize(15);
        $pdf->Write(5,'Existing document '.$filename.' can\'t be rendered');
        //Output the document
        $pdf->Output($filepath,'F');
        $pdf->Close();

        return $filepath;
    }

    /**
     * Generate or regenerate PDF for AP using FPDF library
     * @param $apId
     * @param bool $approved
     */
    public static function generatePdfFpdf($doc_id, $doc_type, $approved = false)
    {


        if ( $doc_type == 'AP' ) {
            // get AP
            $ap = Aps::model()->with('dists', 'document', 'ck_req_detail')->findByAttributes(
                array('Document_ID' => $doc_id)
            );

            $ckRequest = $ap->ck_req_detail;

            // get PO dists
            $apDists = $ap->dists;

            // get PO formatting
            $poFormatting = PoFormatting::model()->findByAttributes(array(
                'Project_ID' => $ap->document->Project_ID,
            ));

            // get Sign_Requested_By user info
            $signRequestedByUser = Users::model()->with('person')->findByPk($ckRequest->Sign_Requested_By);

            $aproval_detail_list = Audits::getApprovalDetailList($ap->Document_ID);
            // get current vendor info
            $currentVendor = Vendors::model()->with('client.company.adreses')->findByPk($ap->Vendor_ID);

            $condition = UsersClientList::getClientAdminCondition($currentVendor->client->Client_ID);
            $vendorAdmin = UsersClientList::model()->with('user.person')->find($condition);

            $pdf = new FpdfAp('P','mm','Letter');

            $pdf->AddFont('HelveticaB','','helveticab.php');
            $pdf->AddFont('Courier','','courier.php');
            $pdf->AddFont('CourierB','','courierb.php');
            $pdf->SetAutoPageBreak(true, 10);

            $pdf->setVariables($ap,$poFormatting,$ckRequest,$apDists,$currentVendor,$vendorAdmin,$signRequestedByUser,$aproval_detail_list,$approved);
            $pdf->AliasNbPages();
            $pdf->setPageNo(1);
            //$pdf->AliasNbPages();
            $pdf->AddPage('P');
            $pdf->SetFont('Helvetica','',13.5);
            $pdf->SetXY(5,10);
            $pdf->PrintContent();

            //$path=Helper::createDirectory('batches');// creates directory "protected/data/batches" if not exists
            $fileName = 'ApTempPdf'.date('Y-m-d h:i:s').'.pdf';
            $filepath = Helper::createDirectory('ap');
            $filepath = Helper::createDirectory('ap/'.Yii::app()->user->clientID);
            $filepath.= '/'.$fileName;
            $pdf->Output($filepath, 'F');
            //$pdf->Output();

            $last_page = $pdf->custom_page_num;



            $pdf->Close();
        }
        if (( $doc_type == 'PO' )) {

            $po = Pos::model()->findByAttributes(
                array('Document_ID' => $doc_id)
            );

            // get PO dists
            $poDists = $po->dists;

            // get PO details
            $poDecrDetails = $po->decr_details;

            // get PO formatting
            $poFormatting = PoFormatting::model()->findByAttributes(array(
                'Project_ID' => $po->document->Project_ID,
            ));

            // get Sign_Requested_By user info
            $signRequestedByUser = Users::model()->with('person')->findByPk($po->Sign_Requested_By);

            $aproval_detail_list = Audits::getApprovalDetailList($po->Document_ID);

            // get current vendor info
            $currentVendor = Vendors::model()->with('client.company.adreses')->findByPk($po->Vendor_ID);

            $condition = UsersClientList::getClientAdminCondition($currentVendor->client->Client_ID);
            $vendorAdmin = UsersClientList::model()->with('user.person')->find($condition);

            $paymentTypes = array(
                'OA' => 'On Account',
                'CC' => 'Credit Card',
                'DP' => 'Deposit',
                'CK' => 'Payment Check',
                'PC' => 'Petty Cash',
            );


            $pdf = new FpdfPo('P','mm','Letter');

            $pdf->AddFont('HelveticaB','','helveticab.php');
            $pdf->AddFont('Courier','','courier.php');
            $pdf->AddFont('CourierB','','courierb.php');
            $pdf->SetAutoPageBreak(true, 10);

            $pdf->setVariables($po,$poFormatting,$poDecrDetails,$poDists,$currentVendor,$vendorAdmin,$signRequestedByUser,$aproval_detail_list,$approved,$paymentTypes);
            $pdf->AliasNbPages();
            $pdf->setPageNo(1);
            //$pdf->AliasNbPages();
            $pdf->AddPage('P');
            $pdf->SetFont('Helvetica','',13.5);
            $pdf->SetXY(5,10);
            $pdf->PrintContent();

            //$path=Helper::createDirectory('batches');// creates directory "protected/data/batches" if not exists
            $fileName = 'TempFile.pdf';
            $filepath = Helper::createDirectory('po');
            $filepath = Helper::createDirectory('po/'.Yii::app()->user->clientID);
            $filepath.= '/'.$fileName;
            $pdf->Output($filepath, 'F');
            //$pdf->Output();

            $last_page = $pdf->custom_page_num;
            $pdf->Close();
        }


        return array(
          'path' => $filepath,
          'pages'=> $last_page
        );
    }

}