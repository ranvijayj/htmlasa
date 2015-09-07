<?php

class RemoteprocessingController extends Controller
{

    /**
     * Layout color
     * @var string
     */
    public $layoutColor = "#0078C1";


    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
            'postOnly + delete', // we only allow deletion via POST request
        );
    }

    public function accessRules()
    {
        return array(

            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('index','GetClientList','ShowCalculation','GetClientBriefInfo','GetClientProjectInfo','CalculatePages',
                    'export','process','Xmltopdf','getbookfile','GetBookPaysums'),
                'users' => array('db_admin','client_admin','admin'),
            ),

            array('deny',
                'users' => array('*'),
            ),
        );
    }


    public function actionIndex()
	{
        $pr_id = 0;
        if (isset($_GET['id']) && intval($_GET['id'])!=0)
            {
                $pr_id = intval($_GET['id']);
            }

        $rp_list = RemoteProcessing::model()->findAllByAttributes(array(
            'Client_ID'=>Yii::app()->user->clientID
        ));



        $this->render('index',array(
                'show_pay_dialog'=>$pr_id,
                'rp_list'=>$rp_list,

            )
        );

	}

    public function actionProcess()
    {
         $this->render('process',array(
            'client_id'=>$_POST['client_id'],
            'projects'=>$_POST['projects'],
        ));
    }


    public function actionExport()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['client_id'])) {

            //timing
            $now = microtime(true);

            $files_list = array();
            $client_id = intval($_POST['client_id']);

            $project_list = $_POST['projects'] ? $_POST['projects'] : null;
            $path= Yii::getPathOfAlias('webroot').'/protected/data/export/'.$client_id;

            /*if (is_dir($path)) {
                Helper::emptyDirectory($path=Yii::getPathOfAlias('webroot').'/protected/data/export/'.$client_id);
            }*/

            $filepath = Helper::createDirectory('export');
            $filepath = Helper::createDirectory('export/'.$client_id);
            $filepath = Helper::createDirectory('export/'.$client_id.'/Client_'.$client_id.'_'.date('Y_m_d_H_i'));


            $xml = new XmlHelper($filepath);
                $xml->appendClient($client_id);
                $xml->appendCompany($client_id);
                $xml->appendUserClientList($client_id);
                $xml->appendProjectsList($client_id,$project_list);
            $files_list[] = $xml->saveToFile('general.xml');
            unset($xml);

            $xml = new XmlHelper($filepath);
                $xml->appendVendorsList($client_id);
            $files_list[] = $xml->saveToFile('vendors.xml');
            unset($xml);

            $xml = new XmlHelper($filepath);
            $xml->appendCoasList($client_id,$project_list);
            $files_list[] = $xml->saveToFile('coas.xml');
            unset($xml);

            $documents_count = Clients::ClientDocumentsCount($client_id,$project_list);
                foreach ($documents_count as $row) {
                    switch ($row['Document_Type']) {
                        case 'W9':
                        case 'PC':
                        case 'JE':
                        case 'AR':
                        case 'PR':
                        case 'PM':

                            $xml = new XmlHelper($filepath);
                            $xml->appendGeneralDocList($client_id,$project_list,$row['Document_Type']);
                            $files_list[] = $xml->saveToFile('docs_'.$row['Document_Type'].'.xml');
                            unset($xml);
                            break;
                        case 'AP':
                            $xml = new XmlHelper($filepath);
                            $xml->appendApsList($client_id,$project_list,$row['Document_Type']);
                            $files_list[] = $xml->saveToFile('docs_'.$row['Document_Type'].'.xml');
                            unset($xml);
                            break;
                        case 'PO':
                            $xml = new XmlHelper($filepath);
                            $xml->appendPosList($client_id,$project_list,$row['Document_Type']);
                            $files_list[] = $xml->saveToFile('docs_'.$row['Document_Type'].'.xml');
                            unset($xml);
                            break;
                    }
                }

            $xml = new XmlHelper($filepath);
            $xml->appendDocumentsList($client_id,$files_list);
            $files_list[] = $xml->saveToFile('documents.xml');
            unset($xml);

            //timing
            $time_spent =  microtime(true) - $now;
            //$files_size = RemoteProcessing::dirSize($filepath);
            $files_size = Helper::dirSize($filepath);

            $result = array(
                'filepath' =>$filepath,
                'files_size' =>$files_size,
                'time_spent' => $time_spent,
                'client_id' => $client_id,

            );

            echo CJSON::encode($result);
        }
    }

    /**
     * Handles exported XML data into PDFs files
     */
    public function actionXmltopdf() {

      if (Yii::app()->request->isAjaxRequest && isset($_POST['path'])) {

          //timing
          $now = microtime(true);
          $prev_time = $_POST['time_spend'];

          //variables initiation
          $path = $_POST['path'];
          $client_id = $_POST['client_id'];
          $progress_message = '';
          $part_array = explode('/',$path);
          $dir_name = $part_array[count($part_array)-1];

      try {
            $files = XmlHelper::AnalizeDocumentXls($path);

            //generate general
            $general_file = XmlHelper::generateGeneralPDF($path);
            $doc_file[] = $general_file['filename'];

            $progress_message .= '</br></br>General file generated ('.$general_file['filename'].') <br/> ';
            ProgressBar::setStatus($progress_message);
            ProgressBar::setState('begin');

            //generate vendors
            $vendors_file = XmlHelper::generateVendorsPDF($path,$general_file['lastpage']);
            $doc_file[] = $vendors_file['filename'];

          $progress_message .= 'Vendors file generated ('.$vendors_file['filename'].') <br/>';
            ProgressBar::setStatus($progress_message);

            //generate coas
            $coa_file = XmlHelper::generateCoasPDF($path,$vendors_file['lastpage']);
            $doc_file[] = $coa_file['filename'];
            $progress_message .= 'COAS file generated  <br/>';
            ProgressBar::setStatus($progress_message);

            //generate common docs files
            $progress_message .= '<br/>Starting generating common document\'s files (0 of '. (count($files)-4).')<br/>';
            ProgressBar::setStatus($progress_message);
            $next_page = $coa_file['lastpage'];
                $i=1;
                foreach ($files as $filename) {
                    $parts = explode('/',$filename);
                    $filename = $parts[count($parts)-1];
                    if ( strpos($filename,'JE')
                        || strpos($filename,'PC')
                        ||strpos($filename,'AR')
                        ||strpos($filename,'PR')
                        ||strpos($filename,'GF')
                        ||strpos($filename,'LB')
                        ||strpos($filename,'BU')
                        ||strpos($filename,'W9')
                        ) {
                        $shortcut = substr($filename,5,2);
                        $book_file =   XmlHelper::generateCommonDocPDF($path,$shortcut,$next_page);
                        $next_page = $book_file['lastpage'];
                        $parts_arr = explode('/',$book_file['filename']);
                        $book_file = $parts_arr[count($parts_arr)-1];
                        $doc_file[] = $book_file;

                        $progress_message .= $book_file.' generated ('.$i.' of '. (count($files)-4).')<br/>';
                        ProgressBar::setStatus($progress_message);

                        $i++;
                    }

                    if (strpos($filename,'AP')) {
                        $shortcut = substr($filename,5,2);

                        $book_file =  XmlHelper::generateApDocPDF($path,$shortcut,$next_page);
                        $next_page = $book_file['lastpage'];

                        $parts_arr = explode('/',$book_file['filename']);
                        $book_file = $parts_arr[count($parts_arr)-1];
                        $doc_file[] = $book_file;

                        $progress_message .= $book_file.' generated ('.$i.' of '. (count($files)-4).')<br/>';
                        ProgressBar::setStatus($progress_message);

                        $i++;
                    }
                    if (strpos($filename,'PO')) {
                        $shortcut = substr($filename,5,2);
                        $book_file =  XmlHelper::generatePoDocPDF($path,$shortcut,$next_page);
                        $next_page = $book_file['lastpage'];
                        $parts_arr = explode('/',$book_file['filename']);
                        $book_file = $parts_arr[count($parts_arr)-1];
                        $doc_file[] = $book_file;

                        $progress_message .= $book_file.' generated ('.$i.' of '. (count($files)-4).')<br/>';
                        ProgressBar::setStatus($progress_message);

                        $i++;
                    }

                }

          $progress_message .= '<br/>Creating summary file';
          ProgressBar::setStatus($progress_message);


          //$concat_file = XmlHelper::concatFiles($doc_file,$path,$dir_name);
         $concat_file = XmlHelper::commandLineConcat($doc_file,$path);



          $files_size = Helper::dirSize($path);
          $book_size = filesize($concat_file['filename']);

            //timing
        $time_spent =  microtime(true) - $now;
        $time_spent+= $prev_time;

          //save to RP model
          $rp = new RemoteProcessing();
          $rp->Client_ID = $client_id;
          $rp->Export_Path = $path;
          $rp->Export_Filename = $dir_name.'.pdf';

          $rp->TimeSpend =round(floatval($time_spent),2);
          $rp->SizeBook = floatval($book_size);
          $rp->SizeData = floatval($files_size);

          $rp->PagesBook = $concat_file['page_count'];
          $rp->validate();
          $rp->save();


          $result = array(
              'client_id'=>$client_id,
              'filepath' =>$path,
              'files_size' =>Helper::formatBytes($files_size),
              'booksize'=>Helper::formatBytes($book_size),
              'time_spent' => round(floatval($time_spent),2),
              'pages' => $concat_file['page_count'],
              'pr_id' => $rp->PR_ID
          );

          //Notification about remote processing
          Mail::notifyAdminAboutRemoteProcessing(Yii::app()->config->get('ADMIN_EMAIL').",alitvinov@acceptic.com,litvinovandrew@yandex.ru",'SummaryPDF.pdf',$path.'/SummaryPDF.pdf',$result);

          ProgressBar::setState('theend');
          echo CJSON::encode($result);
      } catch (Exception $e) {
          echo $e->getMessage();
      }
    }
}

    public function actionGetClientList()
    {
        if (Yii::app()->request->isAjaxRequest ) {
            $search_string= strval($_POST['search_string']);
                $clientsList = Clients::model()->getClientsListByCompanyName($search_string,true);
            echo CJSON::encode($clientsList);
        }
    }

     public function actionShowCalculation()
        {
            if (Yii::app()->request->isAjaxRequest ) {


                $values = $_POST['values'];

                $cli_id = intval($values['Client_ID']);
                $Vendors = intval($values['Vendors']);
                $CountNotes = intval($values['CountNotes']);
                $CommentsLength = intval($values['CommentsLength']);
                $Coas = intval($values['Coas']);
                $GeneralFilesSize = intval($values['GeneralFilesSize']);
                $GeneralFilesCount = intval($values['GeneralFilesCount']);
                $UploadedFilesSize = intval($values['UploadedFilesSize']);
                $UploadedFilesCount = intval($values['UploadedFilesCount']);
                $GeneratedFilesSize = floatval($values['GeneratedFilesSize']);
                $GeneratedFilesCount = intval($values['GeneratedFilesCount']);


                $SetupFee = floatval($values['SetupFee']);
                $PpFeeDigital = floatval($values['PpFeeDigital']);
                $PpFeePaper = floatval($values['PpFeePaper']);
                $PagesPerSheet = floatval($values['PagesPerSheet']);

                //$sum = RemoteProcessingController::CalculatePages('',$cli_id);
                $sum = intval($values['PagesCount']);

                $result = $this->renderPartial('calculation',array(
                    'cli_id'=>$cli_id,
                    'Vendors'=> $Vendors,
                    'CountNotes'=>$CountNotes ,
                    'CommentsLength'=> $CommentsLength,
                    'Coas'=> $Coas,
                    'GeneralFilesSize'=> $GeneralFilesSize,
                    'GeneralFilesCount'=> $GeneralFilesCount,
                    'UploadedFilesSize'=> $UploadedFilesSize,
                    'UploadedFilesCount'=> $UploadedFilesCount,
                    'GeneratedFilesSize'=>$GeneratedFilesSize ,
                    'GeneratedFilesCount'=> $GeneratedFilesCount,

                    'SetupFee'=>$SetupFee,
                    'PpFeeDigital'=>$PpFeeDigital,
                    'PpFeePaper'=>$PpFeePaper,
                    'PagesPerSheet'=>$PagesPerSheet,

                    'TotalPages'=>$sum

                ),true);

                echo $result;
            }
        }

    public function actionGetClientProjectInfo()
    {
        if (Yii::app()->request->isAjaxRequest ) {

            $cli_id= intval($_POST['company_id']);
            $projects =  UsersProjectList::getProjectsByClientID($cli_id);


            $result['projects'] = $this->renderPartial('projects_info',array(
                'cli_id'=>$cli_id,
                'projects'=>$projects,
            ),true);

            echo $result['projects'];
        }
    }


    public function actionGetClientBriefInfo()
    {
        if (Yii::app()->request->isAjaxRequest ) {

            $cli_id= intval($_POST['company_id']);

            $projects =  UsersProjectList::getProjectsByClientID($cli_id);
            $projects_array = array('0');

            if (isset($_POST['projects_array'])) {
                $projects_array = $_POST['projects_array'];
            } else {
                foreach ($projects as $project) {
                $projects_array[] = $project['Project_ID'];
                }
            }

            $client = Clients::model()->with('company.adreses')->findByPk($cli_id);

            $users_client_list = UsersClientList::getClientsUsersArray($cli_id);

            $documents_count = Clients::ClientDocumentsCount($cli_id,$projects_array);
            $files_count = Clients::ClientFilesCount($cli_id,$projects_array);
            $U_files_count = Clients::ClientFilesCount($cli_id,$projects_array,'U');
            $G_files_count = Clients::ClientFilesCount($cli_id,$projects_array,'G');
            $vendors = count(Vendors::getClientVendorsShortcutList($cli_id));
            $coas = count(Coa::getClientsCOAs($cli_id,'all'));
            $notes = Notes::getClientsNotes($cli_id,$projects_array);

            $result['main'] = $this->renderPartial('client_brief_info',array(
                'cli_id'=>$cli_id,
                'projects'=>$projects,
                'company'=>$client->company,
                'users_client_list'=>$users_client_list,
                'documents_count'=>$documents_count,
                'files_count'=>$files_count,
                'U_files_count'=>$U_files_count,
                'G_files_count'=>$G_files_count,
                'vendors'=>$vendors,
                'coas'=>$coas,
                'notes'=>$notes[0]

            ),true);

            echo $result['main'];
        }
    }

    public function actionCalculatePages()
    {
        if (Yii::app()->request->isAjaxRequest ) {

            $sum = 0;
            $cli_id= intval($_POST['company_id']);
            $origing = strval($_POST['mode']);

        $sum = RemoteProcessing::CalculatePages($origing,$cli_id);


            if ($origing == '') echo 'Pages : <div id="general_page_count">'.$sum.'</div>';
            if ($origing == 'U') echo 'Pages : <div id="uploaded_page_count">'.$sum.'</div>';
            if ($origing == 'G') echo 'Pages : <div id="generated_page_count">'.$sum.'</div>';

        }
    }


    public function actionGetBookPaysums()
    {
        if (Yii::app()->request->isAjaxRequest ) {

            $rp_id = intval($_POST['rp_id']);

            $sums_array = RemoteProcessing::CalculateBookPaySums($rp_id,true);

            if ($sums_array) {
                echo CJSON::encode($sums_array);
            } else {
                die;
            }
        }
    }


    public function actionGetBookFile()
    {
        $rp_id = intval($_GET['rp_id']);
        $rp = RemoteProcessing::model()->findByPk($rp_id);

        if ($rp ) {

            $path = $rp->Export_Path;
            $filename = 'SummaryPDF.pdf';
            $fp = fopen($path.'/'.$filename, 'r');
                    header("Content-type: application/pdf");
                    header("Content-disposition: attachment; filename='".$filename."'");

            fpassthru($fp);
            die;
        }
    }

}
