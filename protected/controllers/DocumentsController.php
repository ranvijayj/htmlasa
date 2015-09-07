<?php

class DocumentsController extends Controller
{
    /**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
            /*array(
                'application.filters.PageTitle + getdocumentfile',
                'controller'=> $this
            )*/

		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('getgdocumentfile'),
                'users'=>array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('index', 'getusersdocumentfile', 'printdocument', 'setdocumentidtoprint', 'senddocumentbyemail'),
                'users'=>array('@'),
            ),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('getdocumentfile','GetDocumentFileByPath', 'getdocumentthumbnail','deletedocument','deletedocuments','DeleteDocumentAjax',
                    'startbatch', 'getbatchfiles','getbatchthumbnail','getbatchsummary',
                'FilePreview','BatchPreview','ViewAudits','PrintAudit','GetFileBySesIndex','GetFileInViewer','PreviewFile','FileContent','ViewPdfOriginal'),
				'users'=>array('admin', 'user', 'approver', 'data_entry_clerk', 'db_admin', 'processor', 'client_admin'),
			),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('GetDocListForDeleteBySearchQuery','GetCueListBySearchQuery','GetApprCompanyInfo','SendApprCueNotification','ShowSendDialog','admchange'),
                'users'=>array('admin', 'approver', 'data_entry_clerk', 'db_admin', 'processor', 'client_admin'),
            ),

            array('allow', // allow authenticated user to perform delete documents and connected operations
                'actions'=>array('approvalcue'),
                'users'=>array('admin', 'approver', 'db_admin', 'processor', 'client_admin'),
            ),
            array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Index action
	 */
	public function actionIndex()
	{
        $this->redirect('/site');
	}

    /**
     * Return necessary file
     */
    public function actionGetDocumentFile($doc_id)
    {

        $doc_id = intval($doc_id);

        if ($doc_id > 0 && Documents::hasAccess($doc_id)) {
            $image = Images::model()->findByAttributes(array(
                'Document_ID' => $doc_id,
            ));
            if ($image) {
                header("Content-type: ". $image->Mime_Type);
                $content =  stripslashes($image->Img);
                echo $content;
                die;
            }
        }
    }


    /**
     * Return necessary file in PDF JS viewer
     */
    public function actionGetFileInViewer($doc_id)
    {
        $doc_id = intval($doc_id);

        $file_id = FileCache::addToFileCache($doc_id);
        $file_path = FileCache::getCacheFilePath($file_id);

        $this->renderPartial('application.views.documents.file_viewer',array(
            'path'=>$file_path,
            'file_id'=>$file_id
        ));

    }

    /**
     * Return necessary file in original PDF JS viewer
     */
    public function actionViewPdfOriginal($file_id)
    {
        //$doc_id = intval($doc_id);

        //$file_id = FileCache::addToFileCache($doc_id);
        $url = '/documents/FileContent?file_id='.$file_id;
        $basePath=Yii::getPathOfAlias('ext.pdfJs.assets');
        $baseUrl=Yii::app()->getAssetManager()->publish($basePath);


        $this->renderPartial('application.extensions.pdfJs.assets.web.viewer',array(
            'url'=>$url,
            'baseUrl'=>$baseUrl
        ));

    }

    /**
     * Return necessary file in PDF JS viewer
     */
    public function actionPreviewFile($file_id)
    {

        $file_path = FileCache::getCacheFilePath($file_id);
        $approved = intval($_GET['approved']);
        $height = intval($_GET['height']);

        $this->renderPartial('application.views.documents.file_viewer',array(
            'path'=>$file_path,
            'file_id'=>$file_id,
            'approved'=>$approved,
            'height'=>$height
        ));

    }


    /**
     * Used by previous one. Gets file content from filesystem. File Should be in the cache.
     * @param $file_id
     */
    public function actionFileContent($file_id)
    {

        $file_path = FileCache::getCacheFilePath($file_id);
        $image = fread(fopen($file_path,"rb"),filesize($file_path));
        $nameParts = explode('.',$file_path);
        $ext = strtolower($nameParts[count($nameParts) - 1]);

        if ($image) {
            ($ext == 'pdf') ? header("Content-type: application/pdf" ) : header("Content-type: image/jpeg" );
            echo $image;
            die;
        }
    }


    public function actionGetDocumentFileByPath($doc_id)
    {
        $documentFilePath = Yii::app()->basePath.'/data/current_uploads_files/'.Yii::app()->user->userID.'/'.date('Y-m-d').'/'.strval(urldecode($doc_id));
        $result = FileModification::PdfByFilePath($documentFilePath);
        //convert to pdf if file is not
        $documentFilePath = $result['filepath'];
        $image = fread(fopen($documentFilePath,"rb"),filesize($documentFilePath));
        $nameParts = explode('.',$documentFilePath);
        $ext = strtolower($nameParts[count($nameParts) - 1]);

            if ($image) {
                ($ext == 'pdf') ? header("Content-type: application/pdf" ) : header("Content-type: image/jpeg" );
                echo $image;
                die;
            }
    }


    /**
     * File preview. Filepath can be defined by session index.
     * @param $index
     */
    public function actionGetFileBySesIndex($index)
    {
        $documentFilePath = $_SESSION[$index]['filepath'];
        $result = FileModification::PdfByFilePath($documentFilePath);
        //convert to pdf if file is not
        $documentFilePath = $result['filepath'];
        $image = fread(fopen($documentFilePath,"rb"),filesize($documentFilePath));
        $nameParts = explode('.',$documentFilePath);
        $ext = strtolower($nameParts[count($nameParts) - 1]);

        if ($image) {
            ($ext == 'pdf') ? header("Content-type: application/pdf" ) : header("Content-type: image/jpeg" );
            echo $image;
            die;
        }
    }




    /**
     * Return necessary file for Google Docs Viewer
     */
    public function actionGetGDocumentFile($doc_id, $code)
    {
        Helper::deleteObsoleteGDocsAccessLinks();
        $doc_id = intval($doc_id);
        $code = trim($code);

        if ($doc_id > 0 && $code != '') {
            $fileAccess = GoogleDocsAccess::model()->findByAttributes(array(
                'Document_ID' => $doc_id,
                'Access_Code' => $code,
            ));

            if ($fileAccess) {
                $image = Images::model()->findByAttributes(array(
                    'Document_ID' => $doc_id,
                ));
                if ($image) {
                    header("Content-type: ". $image->Mime_Type);
                    echo stripslashes($image->Img);
                    die;
                }
            }
        }
    }

    /**
     * Return necessary user's file
     */
    public function actionGetUsersDocumentFile($doc_id)
    {
        $doc_id = intval($doc_id);

        if ($doc_id > 0) {
            $document = Documents::model()->findByAttributes(array(
                'Document_ID' => $doc_id,
                'User_ID' => Yii::app()->user->userID,
            ));
            if ($document) {
                $image = Images::model()->findByAttributes(array(
                    'Document_ID' => $doc_id,
                ));
                if ($image) {
                    header("Content-type: ". $image->Mime_Type);
                    echo stripslashes($image->Img);
                    die;
                }
            }
        }
    }

    /**
     * Get document thumbnail
     */
    public function actionGetDocumentThumbnail($doc_id)
    {
        $doc_id = intval($doc_id);

        if ($doc_id > 0 && Documents::hasAccess($doc_id)) {
            $filePath = 'protected/data/thumbs/' . $doc_id . '.jpg';

            // check existing of file
            /*if (!file_exists($filePath)) {

                $image = Images::model()->findByAttributes(array(
                    'Document_ID' => $doc_id,
                ));

                if ($image) {
                    $infile = stripslashes($image->Img);
                    $nameParts = explode('.', $image->File_Name);
                    $tempPath = 'protected/data/thumbs/temporary_files/' . $doc_id . '.' . $nameParts[count($nameParts) - 1];
                    file_put_contents($tempPath, $infile);

                    if ($image->Mime_Type != 'application/pdf' || ($image->Mime_Type == 'application/pdf' && $image->findPdfText($infile) == '')) {
                        Documents::crateDocumentThumbnail($tempPath, 'thumbs', $image->Mime_Type, $doc_id);
                    } else {
                        Documents::cratePDFThumbnail($tempPath, 'thumbs',  $doc_id);
                    }
                    unset($infile);
                    @unlink($tempPath);
                    if (!file_exists($filePath)) {
                        $document = Documents::model()->findByPk($doc_id);
                        $filePath = 'protected/data/thumbs/standart_thumbs/' . strtolower($document->Document_Type) . '.jpg';
                    }
                } else {
                    $document = Documents::model()->findByPk($doc_id);
                    $filePath = 'protected/data/thumbs/standart_thumbs/' . strtolower($document->Document_Type) . '.jpg';
                }
            }*/

            $document = Documents::model()->findByPk($doc_id);
            $filePath = 'protected/data/thumbs/standart_thumbs/' . strtolower($document->Document_Type) . '.jpg';


            // return thumbnail
            $image = fread(fopen($filePath,"rb"),filesize($filePath));
            header("Content-type: image/jpeg");
            echo $image;
            die;
        }
    }


    /**
     * Get document thumbnail
     */
    public function actionGetBatchThumbnail($batch_id)
    {
        $batch_id = intval($batch_id);
        $batch = Batches::model()->findByPk($batch_id);
        if ($batch && $batch->Client_ID == Yii::app()->user->clientID ) {
            $filePath = 'protected/data/thumbs/batch_' . $batch_id . '.jpg';

            // check existing of file
            if (!file_exists($filePath)) {

                if ($batch->Batch_Summary) {
                $infile = stripslashes($batch->Batch_Summary);
                    $tempPath = 'protected/data/thumbs/temporary_files/batch_' . $batch_id . '.pdf';
                    file_put_contents($tempPath, $infile);
                    Documents::cratePDFThumbnail($tempPath, 'thumbs',  $batch_id ,100, 130, true);
                }

                unset($infile);
                @unlink($tempPath);
                if (!file_exists($filePath)) {

                    $filePath = 'protected/data/thumbs/standart_thumbs/batch.jpg';
                }

            }

            // return thumbnail
            $image = fread(fopen($filePath,"rb"),filesize($filePath));
            header("Content-type: image/jpeg");
            echo $image;
            die;
        }
    }
    /**
     * Set document to print
     */
    public function actionSetDocumentIdToPrint()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['doc_id'])) {
            $docId = intval($_POST['doc_id']);
            if ($docId > 0 && Documents::hasAccess($docId)) {
                $_SESSION['document_to_print'] = $docId;
            } else {
                $_SESSION['document_to_print'] = '';
            }
        }
    }

    /**
     * Print document action
     */
    public function actionPrintDocument() {
        $docId = intval($_SESSION['document_to_print']);

        if ($docId > 0 && Documents::hasAccess($docId)) {
            $document = Documents::model()->findByPk($docId);

            $condition = new CDbCriteria();
            $condition->select = 'Mime_Type';
            $condition->condition = "Document_ID='" . $docId . "'";
            $file = Images::model()->find($condition);
            $this->renderPartial('print_document', array(
                'document' => $document,
                'file' => $file,
            ));
        }
    }

    /**
     * Send document by email action
     */
    public function actionSendDocumentByEmail()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['email']) && isset($_POST['doc_id'])) {
            $docId = intval($_POST['doc_id']);
            $email = trim($_POST['email']);
            $email_array = Helper::splitEmails($email) ;
            if ($docId > 0 && $email != '' && Documents::hasAccess($docId)) {
                $client = Clients::model()->findByPk(Yii::app()->user->clientID);
                $user = Users::model()->findByPk(Yii::app()->user->userID);

                $condition = new CDbCriteria();
                $condition->condition = "Document_ID='" . $docId . "'";
                $file = Images::model()->find($condition);

                $filePath = 'protected/data/docs_to_email/' . $file->File_Name;
                file_put_contents($filePath, stripslashes($file->Img));

                //send document
                foreach ($email_array as $email_item) {
                    Emails::logEmailSending(Yii::app()->user->clientID,Yii::app()->user->userID,Yii::app()->user->projectID,$email_item);
                    Mail::sendDocument($email_item, $file->File_Name, $filePath, $client->company->Company_Name,$user);
                }


                //delete file
                unlink($filePath);

                echo 1;
            } else {
                echo 0;
            }
        }
    }

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Images the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
        $id = intval($id);
		$model=Images::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Images $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='images-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}


    /**
     * Delete document from db
     * @param $doc_for_delete
     */
    public function actionDeleteDocument($doc_for_delete)
    {
        $docForDelete = intval($doc_for_delete);
        $document = Documents::model()->findByPk($docForDelete);

        if($document &&  Documents::hasAccess($docForDelete) && Documents::hasDeletePermission($docForDelete, $document->Document_Type, Yii::app()->user->userID, Yii::app()->user->clientID)) {
            Documents::deleteDocument($docForDelete);
            Helper::removeDocumentFromViewSession($docForDelete, strtolower($document->Document_Type) . '_to_review');
            Yii::app()->user->setFlash('success', "Document has been successfully deleted!");
        }

        $this->redirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * Ajax Delete  document from db
     * @param $doc_for_delete
     */
    public function actionDeleteDocumentAjax()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['doc_id']) ) {

            $success = false;
            $document = Documents::model()->findByPk(intval($_POST['doc_id']));

        if($document
            && Documents::hasAccess($document->Document_ID)
            && Documents::hasDeletePermission($document->Document_ID, $document->Document_Type, Yii::app()->user->userID, Yii::app()->user->clientID)) {

            Documents::deleteDocument($document->Document_ID);
            Helper::removeDocumentFromViewSession($document->Document_ID, strtolower($document->Document_Type) . '_to_review');
           // Yii::app()->user->setFlash('success', "Document has been successfully deleted!");
            $success = true;
        }

            echo CJSON::encode( array('location'=>$_SERVER['HTTP_REFERER'],
                                      'success'=>$success));

        }
    }


    /**
     * Action for deleting documents
     * default param is 1 for pagination
     */
    public function actionDeleteDocuments($page = 1)
    {

        $countPerPage = Aps::DISPLAY_LIMIT; //used in getDocListForDelete for setting 'OFFSET' in LIMIT clause in SQL generating
        $page = intval($page) > 0 ? intval($page) : 1; //used in getDocListForDelete for setting 'ROWS' in LIMIT clause in SQL generating

        if(isset($_POST['Documents'])){

            foreach($_POST['Documents'] as $document) {
                if(isset($document['Document_ID']) && Documents::hasAccess($document['Document_ID']) && Documents::hasDeletePermission($document['Document_ID'], $document['Document_Type'], Yii::app()->user->userID, Yii::app()->user->clientID)) {
                    Documents::deleteDocument($document['Document_ID']);
                }
            }
            Yii::app()->user->setFlash('success', "Documents have been successfully deleted!");
        }

        $doclist=Documents::getDocListForDelete($countPerPage, $page);
        $pages=new CPagination(Documents::getCountDocsForDelete());

        // results per page
        $pages->pageSize=$countPerPage;

        $availableStorage = ClientServiceSettings::getAvailableStorage(Yii::app()->user->clientID);
        $usedStorage = Images::getUsedStorage(Yii::app()->user->clientID);

        $cs = Yii::app()->getClientScript();
        $cs->registerCssFile(Yii::app()->request->baseUrl.'/css/filemodification.css');


        $this->render('deletelist', array(
                'availableStorage' => $availableStorage,
                'usedStorage' => $usedStorage,
                'doclist'=>$doclist,
                'pages' => $pages,
        ));
    }


    /**
     * Return batch files
     */
    public function actionGetBatchFiles($batch_id, $file)
    {
        $batch_id = intval($batch_id);
        $batch = Batches::model()->findByPk($batch_id);
        if ($batch && $batch->Client_ID == Yii::app()->user->clientID) {
            if ($file == 'report') {
                //header("Content-type: application/pdf");
                //header("Content-disposition: attachment; filename=batch_report_id_" . $batch->Batch_ID . '_date_' . $batch->Batch_Creation_Date . '.pdf');
                header("Content-type: application/pdf");
                echo stripslashes($batch->Batch_Summary);
                die;
            } else {
                if ($batch->Batch_Export_Type == 'excel') {
                    header("Content-type: application/excel");
                    header("Content-disposition: attachment; filename=batch_ID_" . $batch->Batch_ID . '_' . date('Ymd',strtotime($batch->Batch_Creation_Date)) . '.xlsx');
                } else if ($batch->Batch_Export_Type == 'csv') {
                    header("Content-type: text/csv");
                    header("Content-disposition: attachment; filename=batch__id_" . $batch->Batch_ID . '_' . date('Ymd',strtotime($batch->Batch_Creation_Date)) . '.csv');
                } else {
                    header("Content-type: application/pdf");
                    header("Content-disposition: attachment; filename=batch_id_" . $batch->Batch_ID . '_' . date('Ymd',strtotime($batch->Batch_Creation_Date)) . '.pdf');
                }
                echo stripslashes($batch->Batch_Document);
            }
            die;
        }
    }


    /**
     * Returns batch summary
     * @param $batch_id
     *
     */
    public function actionGetBatchSummary($batch_id)
    {
        $batch_id = intval($batch_id);
        $batch = Batches::model()->findByPk($batch_id);
        if ($batch && $batch->Client_ID == Yii::app()->user->clientID) {
            header("Content-type: application/pdf");
            echo stripslashes($batch->Batch_Summary);
            die;
        }
    }

    /**
     * Main function for Batching Documents
     *
     */
    public function actionStartBatch()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['batchFormat']) && isset($_POST['docType'])
           && isset($_POST['batchType']) && isset($_POST['documents'])) {
            $result = array(
                'success' => 0,
                'urlDocument' => '',
                'urlReport' => '',
            );
            $client_datetime = $_POST['date_time'];
            ////progress bar section
            $pb= ProgressBar::init();
            $pb->step(20);

            //

            $documents = $_POST['documents'];
            $docType = $_POST['docType'];
            $batchType = $_POST['batchType'];
            $batchFormat = $_POST['batchFormat'];

            $batch = new Batches();
            $batch->Client_ID = Yii::app()->user->clientID;
            $batch->User_ID = Yii::app()->user->userID;
            $batch->Project_ID = intval(Yii::app()->user->projectID);
            $batch->Batch_Creation_Date = date('Y-m-d');
            $batch->Batch_Export_Type = $batchType;
            $batch->Batch_Source = $docType;
            $batch->Batch_Total = ($docType == Documents::AP) ? Aps::getTotalsSum($documents) : Pos::getTotalsSum($documents);
            $batch->save();

            $batch->generateReports($docType, $batchType, $batchFormat, $documents,$client_datetime, $batch->Batch_ID);

            if ($batch->validate()) {
                $batch->save();

                $condition = new CDbCriteria();
                $condition->join = ($docType == Documents::AP) ? "LEFT JOIN documents ON aps.Document_ID = documents.Document_ID" :"LEFT JOIN documents ON pos.Document_ID = documents.Document_ID";
                $condition->addInCondition('documents.Document_ID', $documents);
                $model = ($docType == Documents::AP) ? Aps::model() : Pos::model();

                $model->updateAll(array(
                    'Export_Batch_ID' => $batch->Batch_ID,
                ), $condition);


                $result = array(
                    'success' => 1,
                    'urlDocument' => '/documents/getbatchfiles?batch_id=' . $batch->Batch_ID . '&file=document',
                    'urlReport' => '/documents/getbatchfiles?batch_id=' . $batch->Batch_ID . '&file=report',
                );
            }

            $pb->step(100);

            echo CJSON::encode($result);
        }
    }

    public function actionApprovalCue()
    {
        $sortOptions = array(
            'sort_by' => 'DocCreated',
            'sort_direction' => 'Desc',
        );
        $cueApprList = ApprovalCueView::getCueListByQueryString(false,'',$sortOptions);

        //we need to filter this array and leave only items with minimal approval_value value
        $cueApprList = ApprovalCueView::filterArray($cueApprList);

        $this->render('approval_cue', array(
            'cueApprList' => $cueApprList,
        ));
    }


    public function actionGetCueListBySearchQuery()
    {
        //$cueApprList = Documents::getCueListByQueryString($queryString, $options, $sortOptions);
        if (Yii::app()->request->isAjaxRequest && isset($_POST['query'])) {


            if (!isset($_SESSION['marked_aps'])) {
                $_SESSION['marked_aps'] = array();
            }

            // set query params
            $queryString = trim($_POST['query']);
            $options = array(
                'search_option_com_name' => intval($_POST['search_option_com_name']),
                'search_option_fed_id' => intval($_POST['search_option_fed_id']),
                'search_option_addr1' => intval($_POST['search_option_addr1']),
                'search_option_addr2' => intval($_POST['search_option_addr2']),
                'search_option_city' => intval($_POST['search_option_city']),
                'search_option_state' => intval($_POST['search_option_state']),
                'search_option_zip' => intval($_POST['search_option_zip']),
                'search_option_country' => intval($_POST['search_option_country']),
                'search_option_phone' => intval($_POST['search_option_phone']),

            );

            if ($_POST['sort_type']=='ApprName')
            {
                $sortOptions = array(
                    'sort_by' => 'ApprName,Approval_Value',
                    'sort_direction' => $_POST['sort_direction'],
                );

            } else {
                $sortOptions = array(
                    'sort_by' => $_POST['sort_type'],
                    'sort_direction' => $_POST['sort_direction'],
                );

            }

            // get ApprCue list
            $cueApprList = ApprovalCueView::getCueListByQueryString($queryString, $options, $sortOptions);

            $cueApprList = ApprovalCueView::filterArray($cueApprList);

            $html = $this->renderPartial('application.views.documents.tabs._partial_cue_list', array(
                'cueApprList' => $cueApprList
            ), true);



            $result = array(
                'html' => $html

            );

            echo CJSON::encode($result);
        }
    }

    /**
     * Used for filter and search in uploads- delete documents
     */
public function actionGetDocListForDeleteBySearchQuery(){
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['query'])) {


            if (!isset($_SESSION['marked_aps'])) {
                $_SESSION['marked_aps'] = array();
            }

            // set query params
            $limit= isset($_POST['limit']) ? intval($_POST['limit']) : 50;
            $queryString = trim($_POST['query']);
            $options = array(

                'search_option_filename' => intval($_POST['search_option_filename']),
                'search_option_doctype' => intval($_POST['search_option_doctype']),
                'search_option_date' => intval($_POST['search_option_date']),
                'search_option_createdby' => intval($_POST['search_option_createdby']),
                'search_option_modified' => intval($_POST['search_option_modified']),
            );

                $sortOptions = array(
                    'sort_by' => $_POST['sort_type'],
                    'sort_direction' => $_POST['sort_direction'],
                );


            $docForDeleteList = Documents::getDeleteDocListByQueryString($queryString, $options, $sortOptions,$limit);
            $count=count($docForDeleteList);


            $html = $this->renderPartial('application.views.documents.tabs._partial_delete_list', array(
                'doclist' => $docForDeleteList
            ), true);

            $result = array(
                'html' => $html,
                'count'=>$count

            );

            echo CJSON::encode($result);
        }
    }
}


    /**
     * Get company info to sidebar for ApprovalCue
     */
    public function actionGetApprCompanyInfo()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['docId'])) {
            $is_dec=false;

            $docId = intval($_POST['docId']);
            if ($_POST['is_dec']=='DEC') {
                $is_dec = true;
            }
            if ($docId > 0) {
                $appr = ApprovalCueView::model()->findByAttributes(array(
                    'DocID' => $docId,
                ));

            $this->renderPartial('application.views.documents.company_info_block',
                    array(
                        'appr' => $appr,
                        'is_dec'=>$is_dec,
                        'docId'=>$docId,
            )
            );
            }

        }
    }


    public function actionSendApprCueNotification()
    {

        if (Yii::app()->request->isAjaxRequest && isset($_POST['documents'])) {
            $query['doc_ids'] = $_POST['documents'];
            $query['control_ids'] = $_POST['control'];
            $ids=$query['doc_ids'];
            $cids=$query['control_ids'];

            $condition=new CDbCriteria;
            $condition->select='ApprName,NextApproverUID,ApprEmail';
            $condition->distinct=true;

            if($query) {
                $condition->addInCondition('DocID',$query['doc_ids'], 'AND');
                $condition->addInCondition('NextApproverUID',$query['control_ids'], 'AND');
                $ids=$query['doc_ids'];
                $controls=$query['control_ids'];
            }

            $rows = ApprovalCueView::model()->findAll($condition);

            $temp_arr = array();

            foreach ($rows as $row) {
           //retriving doc ids from array ids for selected control user

                for($i=0;$i<count($cids);$i++){
                    if($cids[$i]==$row->NextApproverUID){
                        $temp_arr[] = $ids[$i];
                    }
                }

                $cond=new CDbCriteria;
                $cond->select='CompanyName,Project_Name';
                $cond->compare('NextApproverUID', $row->NextApproverUID);
                $cond->addInCondition('DocID',$temp_arr, 'AND');
                $cond->distinct=true;
                $cond->order = "CompanyName ASC";
                $companies = ApprovalCueView::model()->findAll($cond);

                foreach ($companies as $company) {
                    if(!is_null($company->CompanyName)) {
                        $result=$result.$company->CompanyName." for project  '".$company->Project_Name."' ";
                    } else {$result=$result."Vendor not attached for project  '".$company->Project_Name."' ";}
                }

                unset($temp_arr);

                unset($companies);

                Mail::sendApprovalCueNotification($row->ApprEmail,$row->ApprName,$row->ApprName,$result,'');
           }


        }
        $result = array(
            'html_result' => "<h3 style='text-align: center'>Messages was sucessfully sended</h3>"
        );

        echo CJSON::encode($result);
     }


    /**
     * Generates data for ajax dialog befor sending mail inApprovalCUE
     */
    public function actionShowSendDialog()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['documents'])) {

            //$query['doc_ids'] = implode(",", $_POST['documents']);
            //$query['control_ids'] = implode(",", $_POST['control']);
            $query['doc_ids'] = $_POST['documents'];
            $query['control_ids'] = $_POST['control'];


            $html = ApprovalCueView::getUsersToNotifyForPendingApproval($query);

            } else {
                   $html= ApprovalCueView::getUsersToNotifyForPendingApproval('');
        }

        $result = array(
            'html_result' => $html
        );

        echo CJSON::encode($result);
    }



    public function actionAdmChange()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['docIds']) && isset($_POST['action'])) {

            //$query['doc_ids'] = implode(",", $_POST['documents']);
            //$query['control_ids'] = implode(",", $_POST['control']);

            $doc_ids = $_POST['docIds'];
            $value = intval($_POST['value']);

            $action = $_POST['action'];

            if ($action=='Change_approval_value_to'){

                foreach($doc_ids as $doc_id){

                    $document=Documents::model()->findByPk($doc_id);
                    if($document && $document->Document_Type=Documents::AP){
                        $ap=Aps::model()->findByAttributes(array(
                            'Document_ID' => $doc_id,
                        ));
                        if($ap) {
                            $ap->AP_Approval_Value=$value;
                            $ap->Approved=0;
                            $ap->save();
                        }
                    }
                    if($document && $document->Document_Type=Documents::PO){
                        $po=Pos::model()->findByAttributes(array(
                            'Document_ID' => $doc_id,
                        ));
                        if($po) {
                            $po->PO_Approval_Value=$value;
                            $po->PO_Approved=0;
                            $po->save();
                        }
                    }
                }
            }


            } else { }

        $result = array(
            'html_result' => 'done'
        );

        echo CJSON::encode($result);
    }

    public function actionFilePreview(){
        //var_dump($_GET);
        if (isset($_GET['id']) && intval($_GET['id'])>0) {
            $doc_id = intval($_GET['id']);

            $result = $this->renderPartial('application.views.documents.preview_document', array(
                'doc_id' => $doc_id,
                'mimetype'=>'pdf'
            ), true);

            echo $result;
        }

    }

    public function actionBatchPreview(){
        //var_dump($_GET);
        if (isset($_GET['id']) && intval($_GET['id'])>0) {
            $batch_id = intval($_GET['id']);

            $result = $this->renderPartial('application.views.documents.preview_batch', array(
                'batch_id' => $batch_id,
                'mimetype'=>'pdf'
            ), true);

            echo $result;
        }

    }

    public function actionViewAudits() {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['docId']) ) {

            $audit_mode='';
            $doc_id = intval($_POST['docId']);

            if(isset($_POST['audit_mode'])) {
                $audit_mode = strval($_POST['audit_mode']);
            }

            if ($audit_mode == 'Approved') {
                $audits = Audits::model()->findAllByAttributes(
                    array('Document_ID'=>$doc_id,
                           'Event_Type'=>array(Audits::ACTION_APPROVAL,Audits::ACTION_CREATION,Audits::ACTION_UPLOAD)
                    )
                );
            } else if ($audit_mode !='Approved' && $audit_mode == '') {
                $audits = Audits::model()->findAllByAttributes(
                    array('Document_ID'=>$doc_id,
                    )
                );
            } else {
                $audits = Audits::model()->findAllByAttributes(
                    array('Document_ID'=>$doc_id,
                        'Event_Type'=>$audit_mode
                    )
                );
            }

            $doc_name = Images::model()->findByAttributes(array(
                'Document_ID'=>$doc_id
            ))->File_Name;
            //var_dump($audits);

            $result = $this->renderPartial('application.views.documents.preview_audit', array(
                'audits' => $audits,
                'doc_name'=>$doc_name,
                'doc_id'=>$doc_id,
            ), true);

            echo $result;
        }
    }

    /**
     * Print Audit list
     */
    public function actionPrintAudit() {
        error_reporting(0);
       // var_dump($_GET);

        if (isset($_GET['doc']) && isset ($_GET['action']) ) {
            //old style audit print
            //Audits::printOldStyle();
            $doc_id = intval($_GET['doc']);
            $action = strval ($_GET['action']);
            Audits::prepareAuditForPrint($doc_id);
        }
    }




}
