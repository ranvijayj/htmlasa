<?php

/**
 * Class UploadsController
 */
class UploadsController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

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

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{

        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('getdocumentfileforgoogle'),
                'users'=>array('*'),
            ),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index', 'upload', 'checkfedid', 'testupload', 'addfiletouploadsession',
                                 'changecompanyname', 'deletefile', 'getfilesblock', 'getdocumentfile', 'getadditionfieldsblock',
                                 'changeadditionalfields', 'checkformrequest', 'clearuploadsession', 'changedocumenttype',
                    //all for progress-bar
                    'UploadStatus', 'SaveUploadedFiles','SessionVarsInit','AjaxCheckFileHash','GetFileByName',
                    'GetApFileBlock','GetPoFileBlock','ApSessionVarsInit','PoSessionVarsInit','GetPoBuFileBlock','GetApBuFileBlock','GetPayBuFileBlock',
                    'getCompareBlock','DeleteFileFromSession','DeleteFileFromHashChecking','ReplaceImage',
                    'Simple','HandleUploadedFile'
                    ),
                'expression'=>function() {
                    $users = array('admin', 'user', 'approver', 'processor', 'db_admin', 'client_admin');
                    $clientID = isset(Yii::app()->user->clientID) ? Yii::app()->user->clientID : 0;
                    $companyServiceLevel = ClientServiceSettings::getClientServiceSettings($clientID);

                    if (isset(Yii::app()->user->id)
                        && in_array(Yii::app()->user->id, $users)
                        && $companyServiceLevel
                        && $companyServiceLevel->Active_To >= date('Y-m-d')) {
                        return true;
                    }
                    return false;
                },
			),

            array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    public function actionSimple()
    {
     // if (Yii::app()->request->isAjaxRequest && isset($_POST['upload']) && $_POST['upload'] == true) {
        $this->render('simple',array(
            'filelist'=>null
        ));
      //}
    }
	/**
	 * Main upload action
	 */
	public function actionIndex_old()
	{


        // get last uploaded images
        $images = new Images();
        $last_images = $images->getLastUsersImages();

        if (!isset($_SESSION['current_upload_files'])) {
            $_SESSION['current_upload_files'] = array();
        }

        // check user's folder
        if (!file_exists('protected/data/current_uploads_files/' . Yii::app()->user->userID)) {
            mkdir('protected/data/current_uploads_files/' . Yii::app()->user->userID, 0777);
        }

        //delete user's obsolete files
        $dir_catalogs = scandir('protected/data/current_uploads_files/' . Yii::app()->user->userID);
        $empty_user_catalog = true;
        foreach ($dir_catalogs as $catalog) {
            if ($catalog != '.' && $catalog != '..') {
                $empty = true;
                $catalog_files = scandir('protected/data/current_uploads_files/' . Yii::app()->user->userID . '/' . $catalog);
                foreach ($catalog_files as $file) {
                    if ($file != '.' && $file != '..') {
                        $current = false;
                        foreach ($_SESSION['current_upload_files'] as $cur_upl) {
                            if ($cur_upl['filepath'] == 'protected/data/current_uploads_files/' . Yii::app()->user->userID . '/' . $catalog . '/' . $file
                                || $catalog >= date('Y-m-d', time() - 3600*24*7)) {
                                $current = true;
                                $empty = false;
                                $empty_user_catalog = false;
                            }
                        }
                        if ($catalog >= date('Y-m-d', time() - 3600*24*7)) {
                            $current = true;
                            $empty = false;
                            $empty_user_catalog = false;
                        }
                        if (!$current) {
                            @unlink('protected/data/current_uploads_files/' . Yii::app()->user->userID . '/' . $catalog . '/' . $file);
                        }
                    }
                }
                if ($empty) {
                    @rmdir('protected/data/current_uploads_files/' . Yii::app()->user->userID . '/' . $catalog);
                }
            }
        }
        if ($empty_user_catalog) {
            @rmdir('protected/data/current_uploads_files/' . Yii::app()->user->userID);
        }

        //check existing of files
        foreach ($_SESSION['current_upload_files'] as $key => $file) {
            if (!file_exists($file['filepath'])) {
                unset($_SESSION['current_upload_files'][$key]);
            }
        }

        $similar_file_to_upload = array();
        $show_similar_files_block = false;
        if (isset($_SESSION['similar_file_to_upload'])) {
            $similar_file_to_upload = $_SESSION['similar_file_to_upload'];
            $show_similar_files_block = true;
            unset($_SESSION['similar_file_to_upload']);
        }

        $current_uploads = $_SESSION['current_upload_files'];

        $enableUploading = 0;
        $disableUploadingMessage = '';

        $availableStorage = ClientServiceSettings::getAvailableStorage(Yii::app()->user->clientID);
        $usedStorage = Images::getUsedStorage(Yii::app()->user->clientID);

        if ($usedStorage >= $availableStorage && $availableStorage != 0) {
            $enableUploading = 1;
            $disableUploadingMessage = 'Files cannot be uploaded. Your storage is full. You can purchase
                                        more storage space on My Account page - Service Level tab.
                                        Do you want to increase your storage now?';
        }

        if (Yii::app()->user->projectID === 'all') {
            $enableUploading = 2;
            $disableUploadingMessage = 'Please select a specific Project for this process ! ';
        }

        $clientServiceSettings = ClientServiceSettings::getClientServiceSettings(Yii::app()->user->clientID);

        $this->render('index', array(
            'last_images' => $last_images,
            'current_uploads' => $current_uploads,
            'similar_file_to_upload' => $similar_file_to_upload,
            'show_similar_files_block' => $show_similar_files_block,
            'enableUploading' => $enableUploading,
            'disableUploadingMessage' => $disableUploadingMessage,
            'availableStorage' => $availableStorage,
            'usedStorage' => $usedStorage,
            'clientServiceSettings' => $clientServiceSettings,
        ));
	}


    public function actionIndex()
    {
        //FileModification::Delete(Yii::app()->basePath.'/data/current_uploads_files/'.Yii::app()->user->userID);
        // get last uploaded images
        $images = new Images();
        $last_images = $images->getLastUsersImages();

        if (!isset($_SESSION['current_upload_files'])) {
            $_SESSION['current_upload_files'] = array();
            Helper::emptyDirectory('protected/data/current_uploads_files/' . Yii::app()->user->userID);

        }

        // check user's folder
        if (!file_exists('protected/data/current_uploads_files')) {
            mkdir('protected/data/current_uploads_files', 0775);
            chmod('protected/data/current_uploads_files', 0775);

        }


        if (!file_exists('protected/data/current_uploads_files/' . Yii::app()->user->userID)) {
            mkdir('protected/data/current_uploads_files/' . Yii::app()->user->userID, 0775);
            chmod('protected/data/current_uploads_files/' . Yii::app()->user->userID, 0775);
        }

        //delete user's obsolete files
        /*$dir_catalogs = scandir('protected/data/current_uploads_files/' . Yii::app()->user->userID);
        $empty_user_catalog = true;
        foreach ($dir_catalogs as $catalog) {
            if ($catalog != '.' && $catalog != '..') {
                $empty = true;
                $catalog_files = scandir('protected/data/current_uploads_files/' . Yii::app()->user->userID . '/' . $catalog);
                foreach ($catalog_files as $file) {
                    if ($file != '.' && $file != '..') {
                        $current = false;
                        foreach ($_SESSION['current_upload_files'] as $cur_upl) {
                            if ($cur_upl['filepath'] == 'protected/data/current_uploads_files/' . Yii::app()->user->userID . '/' . $catalog . '/' . $file
                                || $catalog >= date('Y-m-d', time() - 3600*24*7)) {
                                $current = true;
                                $empty = false;
                                $empty_user_catalog = false;
                            }
                        }
                        if ($catalog >= date('Y-m-d', time() - 3600*24*7)) {
                            $current = true;
                            $empty = false;
                            $empty_user_catalog = false;
                        }
                        if (!$current) {
                            @unlink('protected/data/current_uploads_files/' . Yii::app()->user->userID . '/' . $catalog . '/' . $file);
                        }
                    }
                }
                if ($empty) {
                    @rmdir('protected/data/current_uploads_files/' . Yii::app()->user->userID . '/' . $catalog);
                }
            }
        }
        if ($empty_user_catalog) {
            @rmdir('protected/data/current_uploads_files/' . Yii::app()->user->userID);
        }*/

        //check existing of files
        foreach ($_SESSION['current_upload_files'] as $key => $file) {
            if (!file_exists($file['filepath'])) {
                unset($_SESSION['current_upload_files'][$key]);
            }
        }

        $similar_file_to_upload = array();
        $show_similar_files_block = false;
        if (isset($_SESSION['similar_file_to_upload'])) {
            $similar_file_to_upload = $_SESSION['similar_file_to_upload'];
            $show_similar_files_block = true;
            unset($_SESSION['similar_file_to_upload']);
        }

        $current_uploads = $_SESSION['current_upload_files'];

        $enableUploading = 0;
        $disableUploadingMessage = '';

        $availableStorage = ClientServiceSettings::getAvailableStorage(Yii::app()->user->clientID);
        $usedStorage = Images::getUsedStorage(Yii::app()->user->clientID);

        if ($usedStorage >= $availableStorage && $availableStorage != 0) {
            $enableUploading = 1;
            $disableUploadingMessage = 'Files cannot be uploaded. Your storage is full. You can purchase
                                        more storage space on My Account page - Service Level tab.
                                        Do you want to increase your storage now?';
        }

        if (Yii::app()->user->projectID === 'all') {
            $enableUploading = 2;
            $disableUploadingMessage = 'Please select a specific Project for this process!';
        }

        $clientServiceSettings = ClientServiceSettings::getClientServiceSettings(Yii::app()->user->clientID);


        $cs = Yii::app()->getClientScript();



        $cs->registerCssFile(Yii::app()->request->baseUrl.'/css/jupload/css/jquery.fileupload.css');
        $cs->registerCssFile(Yii::app()->request->baseUrl.'/css/jupload/css/jquery.fileupload-ui.css');

        $cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/jupload/vendor/jquery.ui.widget.js');
        $cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/jupload/jquery.iframe-transport.js');
        $cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/jupload/jquery.fileupload.js');
        $cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/jupload/jquery.fileupload-process.js');

        $this->render('alternative', array(
            'last_images' => $last_images,
            'current_uploads' => $current_uploads,
            'similar_file_to_upload' => $similar_file_to_upload,
            'show_similar_files_block' => $show_similar_files_block,
            'enableUploading' => $enableUploading,
            'disableUploadingMessage' => $disableUploadingMessage,
            'availableStorage' => $availableStorage,
            'usedStorage' => $usedStorage,
            'clientServiceSettings' => $clientServiceSettings,
        ));
    }

    /**
     * Main upload action
     */
    public function actionUpload()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['upload']) && $_POST['upload'] == true) {
            //FileModification::ClearUploadDir();
            $result = Documents::uploadDocuments('current_upload_files');
            echo $result;
        }
    }

    /**
     * Returns data about upload progress PHP 5.4 only
     */
    public function actionUploadStatus()
    {
        if (Yii::app()->request->isAjaxRequest ) {

        $upload_handler = new UploadHandler();

        }
    }





    /**
     * Returns default document type for client or recognized document type according to filename uploaded
     * @param $clientId
     * @param $filename
     * @return array|mixed|null|string
     */
    public static function getDocType($clientId,$filename){
        $clientServiceSettings = ClientServiceSettings::getClientServiceSettings($clientId);
        //$doc_array=ServiceLevelSettings::$serviceLevelAvailableDocTypes[$clientServiceSettings->Service_Level_ID]['docs'];
        $tierSettings = Yii::app()->user->tier_settings;//array of aggregated settings for current user
        $doc_array = $tierSettings['docs'];
        $file_name_begining = substr($filename,0,2);
        $userSettings = UsersSettings::model()->findByAttributes(array(
            'User_ID' => Yii::app()->user->userID,
        ));

        if (in_array($userSettings->Default_Doc_Type, $tierSettings['docs'])) {
            $default_item = $userSettings->Default_Doc_Type;
        } else {
            $default_item = Documents::W9;
        }

        foreach ($doc_array as $item){
            if (strcasecmp($item,$file_name_begining)==0) {$default_item=$item;}
        }

        return $default_item;

    }

    /**
     * used unly wor W9 which are uploaded from Dataentry or Create pages
     */
    public function actionHandleUploadedFile() {
        if (Yii::app()->request->isAjaxRequest ) {

            //we need to determine if such vendor exists
            $company_exists = Companies::checkExistByFedID(strval($_POST['fed_id']));

            if (strval($_POST['filename'])=='W9-Temporary.pdf') {
                $filepath = Yii::getPathOfAlias('webroot').'/images/W9-Temporary.pdf';
                $dest_filepath = Helper::createDirectory('current_uploads_files/' . Yii::app()->user->userID);
                $dest_filepath = Helper::createDirectory('current_uploads_files/' . Yii::app()->user->userID.'/'.date('Y-m-d'));
                $dest_filepath .='/W9-Temporary.pdf';
                if (!copy($filepath,$dest_filepath)) {die ('error copy');};
                $mimetype = 'application/pdf';

                //convert to pdf
                $result_file_array = FileModification::PdfByFilePath($dest_filepath);
                $dest_filepath = $result_file_array['filepath'];
                $filename = $result_file_array['filename'];

            } else {


                $dest_filepath = FileCache::getCacheFilePath($_POST['fileId']);


                //convert to pdf
                $result_file_array = FileModification::PdfByFilePath($dest_filepath);
                $dest_filepath = $result_file_array['filepath'];
                $filename = $result_file_array['filename'];


                //get mimetype
                $pathParts = explode('.', $filename);
                $extension = strtolower($pathParts[(count($pathParts) - 1)]);

                if ($extension != 'pdf') {
                    $mimetype = 'image/jpeg';
                } else {
                    $mimetype = 'application/pdf';
                }


            }

            $filesize = filesize($dest_filepath);
            $key = 0;
            unset($_SESSION['current_upload_files']);
            if (ClientServiceSettings::checkUploadPossibility($filesize) && !$company_exists ) {

                $_SESSION['current_upload_files'][$key]['name'] = $result_file_array['filename'];
                $_SESSION['current_upload_files'][$key]['filepath'] = $dest_filepath; //here we are changing the file destination because it was copied above, in order not to delete the example
                $_SESSION['current_upload_files'][$key]['mimetype'] = $mimetype;
                $_SESSION['current_upload_files'][$key]['doctype'] = Documents::W9;
                $_SESSION['current_upload_files'][$key]['fed_id'] = $_POST['fed_id'];
                $_SESSION['current_upload_files'][$key]['company_name'] = $_POST['com_name'];
                $_SESSION['current_upload_files'][$key]['bus_name'] = $_POST['bus_name'];
                $_SESSION['current_upload_files'][$key]['tax_name'] = $_POST['tax_name'];
                $_SESSION['current_upload_files'][$key]['street_adr'] = $_POST['street_adr'];
                $_SESSION['current_upload_files'][$key]['city'] = $_POST['city'];
                $_SESSION['current_upload_files'][$key]['state'] = $_POST['state'];
                $_SESSION['current_upload_files'][$key]['zip'] = $_POST['zip'];


                //write W9 to database
                $newClient = Companies::createEmptyCompany($_POST['fed_id'],$_POST['com_name']);
                //if client and if !hasErrors
                $w9 = W9::createNewFromSessionData($_SESSION['current_upload_files'][$key],$newClient);

                //Documents::uploadDocuments('current_upload_files');

                //now if everything is ok we can use new company as a vendor for PO or AP
                $vendors_list  = Vendors::getVendorsShortcutListByFed($_POST['fed_id']);


                echo CJSON::encode($vendors_list);


            } else {
                if (!ClientServiceSettings::checkUploadPossibility($filesize)) die ("Not enough space");
                if ($company_exists) die ("Company already exists");
            }
        }
    }

    /**
     * Add file to upload session.
     */
    public function actionSaveUploadedFiles()
    {
            if (Yii::app()->request->isAjaxRequest ) {

                    $count= count($_SESSION['current_upload_files'])+1;

                    $current_upload_file=json_decode(stripslashes($_POST['files']));

                    $filesize = $current_upload_file->size;

                    if ( !isset($current_upload_file->error) && ClientServiceSettings::checkUploadPossibility($filesize) ){

                        //$filepath = 'protected/data/current_uploads_files/' . Yii::app()->user->userID . '/'  . date('Y-m-d') . '/' . $current_upload_file->name;
                        $filepath = FileCache::getCacheFilePath($current_upload_file->path);
                        @chmod($filepath,0664);

                        $clientServiceSettings = ClientServiceSettings::getClientServiceSettings(Yii::app()->user->clientID);
                        $pathParts = explode('/',$current_upload_file->type);
                        $type=$pathParts[1];


                        $complete = '<span class="additional_field_pointer" style="color: #f00;" data="' . $count . '">REQUIRED</span>';

                        if ($clientServiceSettings->Service_Level_ID === ServiceLevelSettings::DEFAULT_SERVICE_LEVEL) {
                            $dropDownCell = Yii::app()->user->tier_settings['docsHtml'];

                        } else {
                            $doc_html_array=Yii::app()->user->tier_settings['docsHtml'];

                            $dropDownCell = '<div class="dropdown_cell_ul">'.$doc_html_array;

                            //get doc type default or calculated
                            $def_doc_type = self::getDocType(Yii::app()->user->clientID,$current_upload_file->name);


                            if (!($def_doc_type=='W9')) {$complete='';}
                            $dropDownCell=$dropDownCell .'<span class="dropdown_cell_value">'.$def_doc_type .'</span></div>';


                        }
                        /*$dropDownCell = '<div class="dropdown_cell_ul">
                                             ' . Yii::app()->user->tier_settings['docsHtml'] . '
                                             <span class="dropdown_cell_value">' . $current_upload_file['doctype'] . '</span>
                                         </div>';*/

                        if (Images::checkHashDublication($filepath)) {$dublicate='DUBL';} else {$dublicate='';};

                        $result= '<tr id="image' . $count . '">
                                                  <td class="dropdown_cell_upload">' . $dropDownCell . '</td>
                                                  <td class="uploaded_file_name"><span><img src="' . Yii::app()->request->baseUrl . '/images/file_types/pdf.png" alt="PDF" class="img_type" />'
                            . CHtml::encode(Helper::shortenString($current_upload_file->name,30)) . '</span></td>
                                                  <td class="additions_cell">
                                                       ' . $complete . '
                                                  </td>
                                                  <td class="dublicate"><span class="dublicate_field_pointer" style="font-size:12px; color: #f00;cursor:pointer;">
                                                       ' . $dublicate . '</span>
                                                  </td>
                                                  <td style="padding: 0px;"><img src="' . Yii::app()->request->baseUrl . '/images/delete.png" alt="Delete file from upload session?" title="Delete file from upload session?" class="delete_file" id="delete_file_' . $count . '" style="cursor: pointer;"/></td>
                                              </tr>';
                        $key = $count;
                        $default_Doc_Type= self::getDocType(Yii::app()->user->clientID,$current_upload_file->name);

                        $_SESSION['current_upload_files'][$key]['name'] = $current_upload_file->name;
                        $_SESSION['current_upload_files'][$key]['filepath'] = $filepath;

                        $_SESSION['current_upload_files'][$key]['fileId'] = $current_upload_file->path;

                        $_SESSION['current_upload_files'][$key]['mimetype'] = 'application/pdf';
                        $_SESSION['current_upload_files'][$key]['doctype'] = $default_Doc_Type;
                        $_SESSION['current_upload_files'][$key]['fed_id'] = '';
                        $_SESSION['current_upload_files'][$key]['company_name'] = '';




                        if ($default_Doc_Type == Documents::W9) {
                            $_SESSION['current_upload_files'][$key]['complete'] = false;
                        } else {
                            $_SESSION['current_upload_files'][$key]['complete'] = true;
                        }

                        $_SESSION['current_upload_files'][$key]['dublicate'] = $dublicate;



                        echo $result;



                    } else {
                        echo "error";
                        //$current_upload_file->error;
                    }

            }

    }




    /**
     * action for replacing existing files from data entry view
     */
    public function actionReplaceImage()
    {
            if (Yii::app()->request->isAjaxRequest && isset($_POST['doc_id']) ) {

                $current_upload_file=json_decode(stripslashes($_POST['files']));
                $docId = intval($_POST['doc_id']);

                if(!isset($current_upload_file->error)){

                    //$filepath = 'protected/data/current_uploads_files/' . Yii::app()->user->userID . '/'  . date('Y-m-d') . '/' . $current_upload_file->name;
                    $filepath = FileCache::getCacheFilePath($current_upload_file->path);
                    $image =Images::model()->findByAttributes(array(
                            'Document_ID'=>$docId
                    ));

                    if(!$image) {
                        $image = new Images();
                        $image->Document_ID = $docId;
                    }
                    //updating document
                    $document = Documents::model()->findByPk($docId);
                    //$document->User_ID = Yii::app()->user->userID;
                    //$document->save();
                    if ($document->Document_Type=='W9') {
                        $W9 = W9::model()->findByAttributes(array(
                           'Document_ID'=>$docId
                        ));
                        //$W9->Document_ID = $document->Document_ID;
                        $W9->W9_Owner_ID = Yii::app()->user->clientID;
                        //$W9->Creator_ID = Yii::app()->user->userID;
                    }

                    // updating image
                    $imageData = addslashes( fread(fopen($filepath,'rb'),filesize($filepath)) );

                    $image->Img = $imageData;
                    $image->Mime_Type = 'application/pdf';
                    $image->File_Hash = sha1_file($filepath);
                    $image->File_Name = $current_upload_file->name;
                    $image->File_Size = intval(filesize($filepath));
                    $image->Pages_Count = FileModification::calculatePagesByPath($filepath);
                    $image->save();

                    Audits::LogAction($image->Document_ID ,Audits::ACTION_REUPLOAD);

                    //@unlink($filepath);

                    echo 'success';

                } else {echo $current_upload_file->error;}
            }
    }



    /**
     *
     */
    public function actionApSessionVarsInit()
    {
        $files=json_decode(stripslashes($_POST['files']));

        if (!isset($_SESSION['current_upload_files']) || (isset($_SESSION['current_upload_files']) &&  count($_SESSION['current_upload_files']) == 0)) {
            $key = 1;
        } else if (count($_SESSION['current_upload_files']) > 0) {
            $key = 1;
            foreach ($_SESSION['current_upload_files'] as $k => $value) {
                if ($k > $key) {
                    $key = $k;
                }
            }
            $key++;
        }

        $filepath = 'protected/data/current_uploads_files/' . Yii::app()->user->userID . '/'  . date('Y-m-d') . '/' . $files->name;
        //$filepath = 'protected/data/current_uploads_files/' . Yii::app()->user->userID . '/'  . date('Y-m-d') . '/' . $files->name;

        $result_file_array = FileModification::PdfByFilePath($filepath);


        //get user settings
        $userSettings = UsersSettings::model()->findByAttributes(array(
            'User_ID' => Yii::app()->user->userID,
        ));

        //set default doc type
        //$clientServiceSettings = ClientServiceSettings::getClientServiceSettings(Yii::app()->user->clientID);
        $tierSettings = Yii::app()->user->tier_settings;
        if (in_array($userSettings->Default_Doc_Type, $tierSettings['docs'])) {
            $default_Doc_Type = $userSettings->Default_Doc_Type;
        } else {
            $default_Doc_Type = Documents::W9;
        }

        $_SESSION['ap_upload_file']['name'] =  $result_file_array['filename'];
        $_SESSION['ap_upload_file']['filepath'] = $result_file_array['filepath'];
        $_SESSION['ap_upload_file']['mimetype'] = 'application/pdf';
        $_SESSION['ap_upload_file']['doctype'] = Documents::BU;
        $_SESSION['ap_upload_file']['fed_id'] = '';
        $_SESSION['ap_upload_file']['company_name'] = '';
        $_SESSION['ap_upload_file']['complete'] = true;

        $_SESSION['ap_upload_file']['detailsPage'] = false;
        if (isset($_GET['page'])) {
            $_SESSION['ap_upload_file']['detailsPage'] = true;
        }

        if ($default_Doc_Type == Documents::W9) {
            $_SESSION['current_upload_files'][$key]['complete'] = false;
        } else {
            $_SESSION['current_upload_files'][$key]['complete'] = true;
        }


    }



    /**
     *
     */
public function actionPoSessionVarsInit()
    {
        $files=json_decode(stripslashes($_POST['files']));
        if (!isset($_SESSION['current_upload_files']) || (isset($_SESSION['current_upload_files']) &&  count($_SESSION['current_upload_files']) == 0)) {
            $key = 1;
        } else if (count($_SESSION['current_upload_files']) > 0) {
            $key = 1;
            foreach ($_SESSION['current_upload_files'] as $k => $value) {
                if ($k > $key) {
                    $key = $k;
                }
            }
            $key++;
        }

        //$filepath = 'protected/data/current_uploads_files/' . Yii::app()->user->userID . '/'  . date('Y-m-d') . '/' . $files->name;
        $filepath = FileCache::getCacheFilePath($files->path);
        $result_file_array = FileModification::PdfByFilePath($filepath);

        //get user settings
        $userSettings = UsersSettings::model()->findByAttributes(array(
            'User_ID' => Yii::app()->user->userID,
        ));

        //set default doc type
        //$clientServiceSettings = ClientServiceSettings::getClientServiceSettings(Yii::app()->user->clientID);
        //if (in_array($userSettings->Default_Doc_Type, ServiceLevelSettings::$serviceLevelAvailableDocTypes[$clientServiceSettings->Service_Level_ID]['docs'])) {
        $tierSettings = isset(Yii::app()->user->tier_settings) ? Yii::app()->user->tier_settings : null ;//array of aggregated settings for current user
        if (in_array($userSettings->Default_Doc_Type,$tierSettings['docs'] )) {
            $default_Doc_Type = $userSettings->Default_Doc_Type;
        } else {
            $default_Doc_Type = Documents::W9;
        }

        $_SESSION['po_upload_file']['name'] =  $result_file_array['filename'];
        $_SESSION['po_upload_file']['filepath'] = $result_file_array['filepath'];
        $_SESSION['po_upload_file']['mimetype'] = 'application/pdf';
        $_SESSION['po_upload_file']['doctype'] = Documents::BU;
        $_SESSION['po_upload_file']['fed_id'] = '';
        $_SESSION['po_upload_file']['company_name'] = '';
        $_SESSION['po_upload_file']['complete'] = true;

        $_SESSION['po_upload_file']['detailsPage'] = false;
        if (isset($_GET['page'])) {
            $_SESSION['po_upload_file']['detailsPage'] = true;
        }

        if ($default_Doc_Type == Documents::W9) {
            $_SESSION['current_upload_files'][$key]['complete'] = false;
        } else {
            $_SESSION['current_upload_files'][$key]['complete'] = true;
        }

        var_dump($_SESSION['po_upload_file']);


    }
/**
*
*/
public function actionGetApFileBlock()
    {
        if (Yii::app()->request->isAjaxRequest ) {

            $result = $this->renderPartial('application.views.ap.uploads_block', array (
                'file' => $_SESSION['ap_upload_file'],
                'detailsPage' => $_SESSION['ap_upload_file']['detailsPage'],
            ) );

        echo $result;
        }
    }


public function actionGetPoFileBlock()
    {
        if (Yii::app()->request->isAjaxRequest ) {

            $result = $this->renderPartial('application.views.po.uploads_block', array (
                'file' => $_SESSION['po_upload_file'],
                'detailsPage' => $_SESSION['po_upload_file']['detailsPage'],
            ) );

            echo $result;
        }
    }

public function actionGetPoBuFileBlock()
    {
        if (Yii::app()->request->isAjaxRequest ) {

           // var_dump($_SESSION['po_upload_file']); die;

            $result = $this->renderPartial('application.views.po.bu_uploads_block', array (
                'file' => $_SESSION['po_upload_file'],
                'detailsPage' => $_SESSION['po_upload_file']['detailsPage'],
            ) );

            echo $result;
        }
    }

public function actionGetPayBuFileBlock()
    {
        if (Yii::app()->request->isAjaxRequest ) {

            $result = $this->renderPartial('application.views.payments.bu_uploads_block', array (
                'file' => $_SESSION['po_upload_file'],
                'detailsPage' => $_SESSION['po_upload_file']['detailsPage'],
            ) );

            echo $result;
        }
    }


public function actionGetApBuFileBlock()
    {
        if (Yii::app()->request->isAjaxRequest ) {

            $result = $this->renderPartial('application.views.ap.bu_uploads_block', array (
                'file' => $_SESSION['ap_upload_file'],
                'detailsPage' => $_SESSION['ap_upload_file']['detailsPage'],
            ) );

            echo $result;
        }
    }

public function actionGetCompareBlock()
    {
        if (Yii::app()->request->isAjaxRequest ) {
            if (isset($_POST['page']) && intval($_POST['page']!=0)){
                $page= $_POST['page'];
            } else {
                $page = 1;
            }

            // $files=json_decode(stripslashes($_POST['files']));
            // var_dump($files);die;
            //$files= end($files);
            $i=0;
            foreach($_SESSION['similar_file_to_upload'] as $curr_file){
                if ($i==$page-1) {
                    $similar_file_to_upload[]=$curr_file;
                }
                $i++;
            }

        $result=$this->renderPartial('_files_dialog', array(
                'similar_file_to_upload' => $similar_file_to_upload[0],
                'show_similar_files_block' => true,
                'page'=>$page,
            ));

        }
    }
    /**
     *
     */
public function actionAjaxCheckFileHash()
{
    if (Yii::app()->request->isAjaxRequest ) {
        if (isset($_POST['page']) && intval($_POST['page']!=0)){
            $page= $_POST['page'];
        } else {
            $page = 1;
        }

        foreach($_SESSION['current_upload_files'] as $curr_file){
            if($curr_file['dublicate']!=''){
                    // check if file exists in database by comparing files hash
                    $image= Images::checkHashDublication($curr_file['filepath']);


                    if ($image) {
                        $_SESSION['similar_file_to_upload'][] = array(
                            'uploading' => array('mimetype' => $curr_file['mimetype'], 'name'=> $curr_file['name']),
                            'uploaded' => array('doc_id' => $image->Document_ID, 'mimetype' => $image->Mime_Type, 'name'=> $image->File_Name),

                        );
                        $similar_file_to_upload[]= array(
                            'uploading' => array('mimetype' => $curr_file['mimetype'], 'name'=> $curr_file['name']),
                            'uploaded' => array('doc_id' => $image->Document_ID, 'mimetype' => $image->Mime_Type, 'name'=> $image->File_Name),

                        );

                    }
            }
        }



    if (count($similar_file_to_upload)>0){
        $result=$this->renderPartial('similar_files_dialog', array(
                'similar_file_to_upload' => $similar_file_to_upload,
                'show_similar_files_block' => true,
                'page'=>$page,
            ));
    } else {
        echo 'files_are_not_similar';
    }



    }
}

    /**
     * Delete File From session array for HashChecking
     */
    public function actionDeleteFileFromHashChecking()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['page']) && intval($_POST['page']!=0))
        {

            $delete_filename = strval($_POST['filename']);

            foreach ($_SESSION['current_upload_files'] as $key=>$value){
                if ($value['name']==$delete_filename){
                    $_SESSION['current_upload_files'][$key]['dublicate']='';
                }
            }


            foreach ($_SESSION['similar_file_to_upload'] as $key=>$value){
                if ($value['uploading']['name']==$delete_filename){
                    unset($_SESSION['similar_file_to_upload'][$key]);
                }

            }



        }
    }


    /**
     * Deletes file from current_upload_files and similar_file_to_upload arrays
     */
    public function actionDeleteFileFromSession()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['page']) && intval($_POST['page']!=0))
        {
           $page = intval($_POST['page']);
           $delete_filename = strval($_POST['filename']);

            foreach ($_SESSION['current_upload_files'] as $key=>$value){
                if ($value['name']==$delete_filename){
                    unset($_SESSION['current_upload_files'][$key]);
                    unlink($value['filepath']);
                }
            }
            asort($_SESSION['current_upload_files']);

            foreach ($_SESSION['similar_file_to_upload'] as $key=>$value){
                if ($value['name']==$delete_filename){
                    unset($_SESSION['similar_file_to_upload'][$key]);
                }

            }
            asort($_SESSION['similar_file_to_upload']);


        }
    }




    /**
     * Change document type
     */
    public function actionChangeDocumentType() {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['imgId']) && isset($_POST['docType'])) {
            $imgId = intval($_POST['imgId']);
            $docType = trim($_POST['docType']);
            if (isset($_SESSION['current_upload_files'][$imgId]) && in_array($docType, Documents::$availableDocTypes)) {
                $_SESSION['current_upload_files'][$imgId]['doctype'] = $docType;
                $_SESSION['current_upload_files'][$imgId]['fed_id'] = '';
                $_SESSION['current_upload_files'][$imgId]['company_name'] = '';
                if ($docType == Documents::W9) {
                    $_SESSION['current_upload_files'][$imgId]['complete'] = false;
                } else {
                    $_SESSION['current_upload_files'][$imgId]['complete'] = true;
                }
            }
        }
    }

    /**
     * Check uploads form and return result
     */
    public function actionCheckFormRequest()
    {
        if (Yii::app()->request->isAjaxRequest) {
            $complete = 1;

            foreach($_SESSION['current_upload_files'] as $key => $file) {
                if ($file['doctype'] == Documents::W9) {
                    if ($file['fed_id'] != '') {
                        $company = Companies::model()->findByAttributes(array(
                            'Company_Fed_ID' => $file['fed_id'],
                        ));

                        if ($company) {
                            $_SESSION['current_upload_files'][$key]['company_name'] = '';
                        } else {
                            if ($file['company_name'] == '') {
                                $_SESSION['current_upload_files'][$key]['complete'] = false;
                                $complete = 0;
                            }
                        }
                    } else {
                        $_SESSION['current_upload_files'][$key]['complete'] = false;
                        $_SESSION['current_upload_files'][$key]['company_name'] = '';
                        $complete = 0;
                    }
                } else {
                    $_SESSION['current_upload_files'][$key]['complete'] = true;
                    $_SESSION['current_upload_files'][$key]['company_name'] = '';
                    $_SESSION['current_upload_files'][$key]['fed_id'] = '';
                }
            }

            echo $complete;
        }
    }

    /**
     * Return necessary file
     */
    public function actionGetDocumentFile($doc_num)
    {
        $doc_num = intval($doc_num);
        if ($_SESSION['current_upload_files'][$doc_num]) {
            $fileData = fread(fopen($_SESSION['current_upload_files'][$doc_num]['filepath'],"rb"),filesize($_SESSION['current_upload_files'][$doc_num]['filepath']));
            header("Content-type: ". $_SESSION['current_upload_files'][$doc_num]['mimetype']);
            echo $fileData;
            die;
        }
    }

    /**
     * Return necessary file
     */
    public function actionGetFileByName($filename)
    {
        $name = strval($filename);
        $filepath = 'protected/data/current_uploads_files/' . Yii::app()->user->userID . '/'  . date('Y-m-d') . '/' . $name;
        $fileData = fread(fopen($filepath,"rb"),filesize($filepath));
        foreach ($_SESSION['current_upload_files'] as $file_arr) {
            if ($file_arr['filepath']==$filepath) $mimetipe = $file_arr['mimetype'];
        }
        header("Content-type: ".$mimetipe);
        echo $fileData;
    }

    /**
     * Return necessary file
     */
    public function actionGetFileByPath($filepath)
    {
        //$finfo = finfo_open(FILEINFO_MIME_TYPE);n
        $fi = new finfo(FILEINFO_MIME,'/usr/share/file/magic');
        $mimeType = $fi->buffer(file_get_contents($filepath));

        $fileData = fread(fopen($filepath,"rb"),filesize($filepath));

        header("Content-type: ".$mimeType);
        echo $fileData;
    }

    /**
     * Return necessary file for Google Docs Viewer
     */
    public function actionGetDocumentFileForGoogle($docId,$code)
    {
        Helper::deleteObsoleteGDocsAccessLinks();
        $docId = intval($docId);
        $code = trim($code);

        if ($docId > 0 && $code != '') {
            $fileAccess = GoogleDocsAccess::model()->findByAttributes(array(
                'Document_ID' => $docId,
                'Access_Code' => $code,
            ));

            if ($fileAccess) {
                $fileData = fread(fopen($code,"rb"),filesize($code));
                header("Content-type: application/pdf");
                echo $fileData;
                die;
            }
        }
    }

    /**
     * Returns block with document's file
     */
    public function actionGetFilesBlock()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['imgId']) && isset($_POST['fileType'])) {
            $imgId = intval($_POST['imgId']);
            $fileType = trim($_POST['fileType']);

            if ($fileType == 'current_uploads') {
                if (isset($_SESSION['current_upload_files'][$imgId])) {
                    $file = $_SESSION['current_upload_files'][$imgId];

                    $this->renderPartial('files_block', array(
                        'file' => $file,
                        'mode'=>'filesystem',
                        'imgId' => $imgId,
                        'url' => '/uploads/getdocumentfile?doc_num=',
                        'session' => true,
                    ));
                }
            } elseif ($fileType == 'last_uploads') {
                $image = Images::model()->findByAttributes(array(
                    'Document_ID' => $imgId,
                ));
                if ($image) {
                    $file['mimetype'] = $image->Mime_Type;
                    $file['name'] = $image->File_Name;
                    $this->renderPartial('files_block', array(
                        'file' => $file,
                        'mode'=>'database',
                        'imgId' => $imgId,
                        'url' => '/documents/getdocumentfile?docId=',
                        'session' => false,
                    ));
                }
            } elseif ($fileType == 'sample_revision') {
                $file['mimetype'] = 'image/jpeg' ;
                $file['name'] = 'ASA-AP-W9-Revision.jpg';
                $this->renderPartial('application.views.dataentry.files_block', array(
                    'file' => $file,
                    'mode'=>'filesystem',
                    'imgId' => $imgId,
                    'url' => '/images/ASA-AP-W9-Revision.jpg',
                    'session' => false,
                ));
            }
        }
    }

    /**
     * Delete file from upload session
     */
    public function actionDeleteFile()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['imgId'])) {
            $imgId = intval($_POST['imgId']);
            if (isset($_SESSION['current_upload_files'][$imgId])) {
                @unlink($_SESSION['current_upload_files'][$imgId]['filepath']);
                unset($_SESSION['current_upload_files'][$imgId]);
            }
        }
    }

    /**
     * Clear upload session
     */
    public function actionClearUploadSession() {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['clear'])) {
            foreach ($_SESSION['current_upload_files'] as $key => $file) {
                @unlink($_SESSION['current_upload_files'][$key]['filepath']);
                unset($_SESSION['current_upload_files'][$key]);
            }

           //FileModification::ClearUploadDir();
           //FileModification::Delete(Yii::app()->basePath.'/data/current_uploads_files/'.Yii::app()->user->userID);
           FileModification::EmptyDir(Yii::app()->basePath.'/data/current_uploads_files/'.Yii::app()->user->userID);

        }


    }

    /**
     * Check company with such Fed ID
     */
    public function actionCheckFedId()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['add_fed_id'])) {
            $fedId = trim($_POST['add_fed_id']);
            $pattern = '/^(\d{2}\-\d{7})|(\d{3}\-\d{2}\-\d{4})$/';

            if (preg_match($pattern, $fedId)) {                         //for usual fed ids
                $company = Companies::model()->findByAttributes(array(
                    'Company_Fed_ID' => $fedId,
                ));


                if ($company) {
                    $result = array(
                        'Company_Name' => $company->Company_Name ,
                        'BusinessName' => '',
                        'Address1' => $company->adreses[0]->Address1,
                        'Address2' => $company->adreses[0]->Address2,
                        'City' => $company->adreses[0]->City,
                        'State' => $company->adreses[0]->State,
                        'Zip' => $company->adreses[0]->ZIP

                    );
                    echo CJSON::encode($result);

                } else {
                    echo 0;
                }
            } else if (substr($fedId,0,1)=='T' || substr($fedId,0,1)=='I') { //for temporary or international ids
                $company = Companies::model()->findByAttributes(array(
                    'Company_Fed_ID' => $fedId,
                ));
                if ($company) {
                    $result = array(
                        'Company_Name' => $company->Company_Name ,
                        'Address1' => $company->Company_Name->Address1,
                        'City' => $company->Company_Name->City,
                        'State' => $company->Company_Name->State,
                        'Zip' => $company->Company_Name->Zip

                    );
                    echo CJSON::encode($result);

                } else {
                    echo 0;
                }


            } else {
                echo 0;
            }
        }
    }

    /**
     * Change additional fields in $_Session variable
     */
    public function actionChangeAdditionalFields()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['imgId']) && isset($_POST['fed_id']) && isset($_POST['com_name'])) {
            $fedId = trim($_POST['fed_id']);
            $comName = trim($_POST['com_name']);
            $bus_name = trim($_POST['bus_name']);
            $tax_name = trim($_POST['tax_name']);
            $street_adr = trim($_POST['street_adr']);
            $city = trim($_POST['city']);
            $state = trim($_POST['state']);
            $zip = strval($_POST['zip']);
            $contact = strval($_POST['contact']);
            $phone = strval($_POST['phone']);

            $imgId = intval($_POST['imgId']);
            if (isset($_SESSION['current_upload_files'][$imgId])) {
                $_SESSION['current_upload_files'][$imgId]['fed_id'] = $fedId;
                $_SESSION['current_upload_files'][$imgId]['company_name'] = $comName;
                $_SESSION['current_upload_files'][$imgId]['bus_name'] = $bus_name;
                $_SESSION['current_upload_files'][$imgId]['tax_name'] = $tax_name;
                $_SESSION['current_upload_files'][$imgId]['street_adr'] = $street_adr;
                $_SESSION['current_upload_files'][$imgId]['city'] = $city;
                $_SESSION['current_upload_files'][$imgId]['state'] = $state;
                $_SESSION['current_upload_files'][$imgId]['zip'] = $zip;
                $_SESSION['current_upload_files'][$imgId]['contact'] = $contact;
                $_SESSION['current_upload_files'][$imgId]['phone'] = $phone;

                $_SESSION['current_upload_files'][$imgId]['complete'] = true;
            }
        }
    }

    public function actionGetAdditionFieldsBlock()
    {
        /**
         * Please don't hurt me . This action works in four modes. There are to much IFs in code.
         */
        if (Yii::app()->request->isAjaxRequest && isset($_POST['imgId'])) {
            $imgId = intval($_POST['imgId']);
            $mode = strval($_POST['mode']);
            if (isset($_SESSION['current_upload_files'][$imgId])) {
                $file = $_SESSION['current_upload_files'][$imgId];

                $fileId = FileCache::addToFileCache($_SESSION['current_upload_files'][$imgId]['filepath']);
                $file['filepath'] = FileCache::getCacheFilePath($fileId);
                $existingCompany = false;

                $company = Companies::model()->findByAttributes(array(
                    'Company_Fed_ID' => $_SESSION['current_upload_files'][$imgId]['fed_id'],
                ));
                if ($company) {
                    $existingCompany = true;
                    $street_adr = $company->adreses[0]->Address1;
                    $city = $company->adreses[0]->City;
                    $state = $company->adreses[0]->State;
                    $zip = $company->adreses[0]->ZIP;


                } else {
                    $existingCompany = false;
                    $street_adr = $_SESSION['current_upload_files'][$imgId]['street_adr'];
                    $city = $_SESSION['current_upload_files'][$imgId]['city'];
                    $state = $_SESSION['current_upload_files'][$imgId]['state'];
                    $zip = $_SESSION['current_upload_files'][$imgId]['zip'];
                    $contact = $_SESSION['current_upload_files'][$imgId]['contact'];
                    $phone = $_SESSION['current_upload_files'][$imgId]['phone'];
                }

                $tax_name = $_SESSION['current_upload_files'][$imgId]['tax_name'];
                $bus_name = $_SESSION['current_upload_files'][$imgId]['bus_name'];

                $this->renderPartial('add_data_block', array(
                    'file' => $file,
                    'imgId' => $imgId,
                    'existingCompany' => $existingCompany,
                    'street_adr' => $street_adr,
                    'city' => $city,
                    'state' => $state,
                    'zip' => $zip,
                    'tax_name' => $tax_name,
                    'bus_name' => $bus_name,
                    'contact' => $contact,
                    'phone' => $phone
                ));

            } else {
                //here will be code for vendor or W9 uploading from DataEntry or Create Page

                $company = Companies::model()->findByAttributes(array(
                    'Company_Fed_ID' => $_POST['fed_id'],
                ));

                $img = Images::model()->findByAttributes(array(
                    'Document_ID' => intval($_POST['doc_id'])
                ));

                if ($company && $img) {
                    $existingCompany = true;
                    $street_adr = $company->adreses[0]->Address1;
                    $city = $company->adreses[0]->City;
                    $state = $company->adreses[0]->State;
                    $zip = $company->adreses[0]->ZIP;

                    //its strange but here should be definitely Document_id, it help to system recognize that its file from database. Not from filepath
                    $file['name'] = $img->Document_ID ? $img->Document_ID :$file['name'] = 'W9-Temporary.pdf'; ;

                    $fileId = FileCache::addToFileCache($img->Document_ID);
                    $file['filepath'] = FileCache::getCacheFilePath($fileId);

                    $file['fed_id'] = $company->Company_Fed_ID;
                    $file['company_name'] = $company->Company_Name;
                    $file['mimetype']=$img->Mime_Type ? $img->Mime_Type : 'application/pdf';


                    $existingCompany = true;

                } else if ($company && !$img) {

                    $file['filepath']=Yii::getPathOfAlias('webroot').'/images/W9-Temporary.pdf';
                    $file['name'] = 'W9-Temporary.pdf';
                    $file['fed_id'] = $_POST['fed_id'];
                    $file['mimetype']='application/pdf';

                    $file['company_name'] = $company->Company_Name ? $company->Company_Name : '' ;

                    //copy sample file to upload directory
                    $dest_filepath = Helper::createDirectory('current_uploads_files/' . Yii::app()->user->userID);
                    $dest_filepath = Helper::createDirectory('current_uploads_files/' . Yii::app()->user->userID.'/'.date('Y-m-d'));
                    $dest_filepath .='/W9-Temporary.pdf';

                    if (!copy($file['filepath'],$dest_filepath)) {die ('error copy');};


                    $existingCompany = true;

                    $street_adr = $company->adreses[0]->Address1;
                    $city = $company->adreses[0]->City;
                    $state = $company->adreses[0]->State;
                    $zip = $company->adreses[0]->ZIP;

                } else {
                    $file['filepath']=Yii::getPathOfAlias('webroot').'/images/W9-Temporary.pdf';
                    $file['name'] = 'W9-Temporary.pdf';
                    $file['fed_id'] = $_POST['fed_id'];
                    $file['mimetype']='application/pdf';

                    $file['company_name'] = $company->Company_Name ? $company->Company_Name : '' ;

                    //copy sample file to upload directory
                    $dest_filepath = Helper::createDirectory('current_uploads_files/' . Yii::app()->user->userID);
                    $dest_filepath = Helper::createDirectory('current_uploads_files/' . Yii::app()->user->userID.'/'.date('Y-m-d'));
                    $dest_filepath .='/W9-Temporary.pdf';

                    if (!copy($file['filepath'],$dest_filepath)) {die ('error copy');};


                    $existingCompany = false;

                    $street_adr = $_SESSION['current_upload_files'][$imgId]['street_adr'];
                    $city = $_SESSION['current_upload_files'][$imgId]['city'];
                    $state = $_SESSION['current_upload_files'][$imgId]['state'];
                    $zip = $_SESSION['current_upload_files'][$imgId]['zip'];
                    $tax_name = $_SESSION['current_upload_files'][$imgId]['tax_name'];
                    $bus_name = $_SESSION['current_upload_files'][$imgId]['bus_name'];
                }

                $this->renderPartial('add_data_block', array(
                    'file' => $file,
                    'imgId' => $imgId,
                    'existingCompany' => $existingCompany,
                    'street_adr' => $street_adr,
                    'city' => $city,
                    'state' => $state,
                    'zip' => $zip,
                    'tax_name' => $tax_name,
                    'bus_name' => $bus_name,
                    'mode'=>$mode // only from de and create mode this variable will be not empty

                ));


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



}
