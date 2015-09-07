<?php

class VendorController extends Controller
{
    /**
     * Layout color
     * @var string
     */
    public $layoutColor = "#0078C1";

    /**
     * Vendor model for editing
     * @var bool
     */
    public $edit_vendor_model = false;

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
                'actions'=>array('clearvendorstoreviewsession'),
                'users'=>array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('index', 'detail', 'getlistbysearchquery', 'export', 'print', 'import', 'getcompanyinfo', 'addvendoritemstosession',
                                 'printdocument', 'setvendortoprintdocument',  'updateshortcut', 'getnote',
                                 'getmanagelistbysearchquery', 'removevendorsfromlist', 'addvendorstolist', 'updatenote',
                                 'getinplaceinput', 'updatecellvalue', 'copyvendorslist','GetManageListBySearchQueryNextBlock','GetVendorsListBySearchQueryNextBlock','GetCompanyDataByName',
                                 'UpdateVendor','GetAdditionFieldsBlock','updatew9detail'
                ),
                'expression'=>function() {
                    $users = array('admin', 'user', 'approver', 'processor', 'db_admin', 'client_admin');
                    $clientID = isset(Yii::app()->user->clientID) ? Yii::app()->user->clientID : 0;
                    $companyServiceLevel = ClientServiceSettings::getClientServiceSettings($clientID);
                        $tier_settings = isset(Yii::app()->user->tier_settings) ? Yii::app()->user->tier_settings : null ;//array of aggregated settings for current user
                    if (isset(Yii::app()->user->id)
                        && in_array(Yii::app()->user->id, $users)
                        && $companyServiceLevel
                        && isset($tier_settings['vendors'])
                        && $companyServiceLevel->Active_To >= date('Y-m-d')) {
                        return true;
                    }
                    return false;
                },
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('manage'),
                'expression'=>function() {
                        $tier_settings = isset(Yii::app()->user->tier_settings) ? Yii::app()->user->tier_settings : null ;//array of aggregated settings for current user
                        $users = array('admin', 'user', 'approver', 'processor', 'db_admin', 'client_admin');
                        $clientID = isset(Yii::app()->user->clientID) ? Yii::app()->user->clientID : 0;
                        $companyServiceLevel = ClientServiceSettings::getClientServiceSettings($clientID);
                        if (isset(Yii::app()->user->id)
                            && in_array(Yii::app()->user->id, $users)
                            && $companyServiceLevel
                            && isset($tier_settings['vendors'])
                            && in_array('manage', $tier_settings['vendors'] )
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

    /**
     * Index action
     * Vendors list
     */
    public function actionIndex()
	{
        $cs = Yii::app()->getClientScript();
        $cs->registerCssFile(Yii::app()->request->baseUrl.'/css/jupload/css/jquery.fileupload.css');
        $cs->registerCssFile(Yii::app()->request->baseUrl.'/css/jupload/css/jquery.fileupload-ui.css');

        $cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/jupload/vendor/jquery.ui.widget.js');
        $cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/jupload/jquery.iframe-transport.js');
        $cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/jupload/jquery.fileupload.js');
        $cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/jupload/jquery.fileupload-process.js');



        if (!isset($_SESSION['last_vendors_list_search']['query'])) {
            $_SESSION['last_vendors_list_search']['query'] = '';
        }

        $searchQuery = $_SESSION['last_vendors_list_search']['query'];

        if (!isset($_SESSION['limiter'])) {
            $limit=Aps::DISPLAY_LIMIT;
        } else {$limit=$_SESSION['limiter'];}

        // process import form
        if (isset($_POST['import_vendors_form']) && isset($_SESSION['imported_vendors'])) {
            Vendors::importVendors(Yii::app()->user->clientID, $_SESSION['imported_vendors']);
            Yii::app()->user->setFlash('success', "Vendors have been imported!");
        }
        unset($_SESSION['imported_vendors']);

        //get Vendors list
        if (isset($_SESSION['last_vendors_list_search']['options']) && count($_SESSION['last_vendors_list_search']['options']) > 0) {
            $vendorsList = Vendors::getListByQueryString($_SESSION['last_vendors_list_search']['query'], $_SESSION['last_vendors_list_search']['options'], $_SESSION['last_vendors_list_search']['sort_options'],$limit,0,false);
            $_SESSION['vendors_to_review'] = array();
            $_SESSION['last_vendors_list_search']['query'] = '';
            $_SESSION['last_vendors_list_search']['sort_options'] = array();
        } else {
            $vendorsList = Vendors::getCompanyVendors($limit);
        }

        // vendor and w9 of current user's company
        //$current_client_w9 = W9::getCompanyW9()
        //commented out from rev 12859
        //$current_client_w9 = W9::getW9ByClientID(Yii::app()->user->clientID);

        //var_dump($current_vendor);die;

        $this->render('index', array(
            'vendorsList' => $vendorsList,
            'searchQuery' => $searchQuery,
            //commented out from rev 12859
        ));
	}

    /**
     * Vendors detail page action
     * @param int $page
     */
    public function actionDetail($page = 1)
    {
        // edit vendor's info
        $show_edit_vendor_form = false;
        if (isset($_POST['Vendors'])) {
            $editVendorId = intval($_POST['Vendors']['Vendor_ID']);
            $vendorToEdit = Vendors::model()->findByAttributes(array(
                'Vendor_ID' => $editVendorId,
                'Client_Client_ID' => Yii::app()->user->clientID,
            ));

            if ($vendorToEdit) {
                /*$vendorToEdit->Vendor_ID_Shortcut = $_POST['Vendors']['Vendor_ID_Shortcut'];
                $vendorToEdit->Vendor_Name_Checkprint = $_POST['Vendors']['Vendor_Name_Checkprint'];
                $vendorToEdit->Vendor_1099 = intval($_POST['Vendors']['Vendor_1099']);
                $vendorToEdit->Vendor_Default_GL = $_POST['Vendors']['Vendor_Default_GL'];
                $vendorToEdit->Vendor_Default_GL_Note = $_POST['Vendors']['Vendor_Default_GL_Note'];*/
                $vendorToEdit->attributes = $_POST['Vendors'];

                if ($vendorToEdit->Vendor_1099 < 0) {
                    $vendorToEdit->Vendor_1099 = 0;
                } elseif ($vendorToEdit->Vendor_1099 > 1) {
                    $vendorToEdit->Vendor_1099 = 1;
                }

                if ($vendorToEdit->validate()) {
                    $vendorToEdit->save();
                    Yii::app()->user->setFlash('success', "Changes Saved!");
                } else {
                    $show_edit_vendor_form = true;
                    $this->edit_vendor_model = $vendorToEdit;
                }
            } else {
                Yii::app()->user->setFlash('success', "You don't have permissions to edit this info!");
            }
        }

        if (isset($_POST['Companies']) && isset($_POST['Addresses']) && isset($_POST['Company_ID']) )
        {
            $company_id = intval($_POST['Company_ID']);
            //$vendor_id = intval($_POST['Vendor_ID']);

            //what da fuck?
            /*$person_id = intval($_POST['Person_ID']);
            $person = Persons::model()->findByPk($person_id);*/

            $company  = Companies::model()->findByPk($company_id);
            $adress = $company->adreses[0];

            $company->attributes = $_POST['Companies'];
            $adress->attributes = $_POST['Addresses'];

            //what???
            //$person->attributes = $_POST['Persons'];


            if ($company->validate() && $adress->validate()) {
                $company->save();
                $adress->save();
               // $person->save();
                Yii::app()->user->setFlash('success', "Changes Saved!");
            } else {

                $show_edit_company_form = true;
                Yii::app()->user->setFlash('success', "Fields validate error");

            }


        }

        // check Vendors to review
        if (!isset($_SESSION['vendors_to_review']) || count($_SESSION['vendors_to_review']) == 0) {
            $_SESSION['vendors_to_review'] = Vendors::getVendorsToApproveToSession();
            if (!isset($_SESSION['vendors_to_review']) || count($_SESSION['vendors_to_review']) == 0) {
                Yii::app()->user->setFlash('success', "Please choose Vendors to review!");
                $this->redirect('/vendor');
            }
        }

        $page = intval($page);
        $num_pages = count($_SESSION['vendors_to_review']);
        if ($page <= 0) {
            $page = 1;
        } else if ($page > $num_pages) {
            $page = $num_pages;
        }

        $vendorId = $_SESSION['vendors_to_review'][$page];

        //get company info
        $vendor = Vendors::model()->with('client.company')->findByPk($vendorId);
        $client = $vendor->client;
        $company = $company ? $company :$client->company ;
        $adress = $adress ? $adress : $company->adreses[0];

        //what da fuck?
        $condition = UsersClientList::getClientAdminCondition($client->Client_ID);
        $clientAdmins = UsersClientList::model()->find($condition);

        $clientAdmin = null;
        $adminPerson = null;
        // get company client admin's info
        if (isset($clientAdmins->User_ID)) {
            $clientAdmin = Users::model()->with('person')->findByPk($clientAdmins->User_ID);
            $adminPerson = $clientAdmin->person;
        }

        // get last company w9 document
        $lastDocument  = W9::getCompanyW9Doc($client->Client_ID);

        $w9 = null;
        $file = null;

        $condition = new CDbCriteria();
        $condition->select = 'File_Name,Mime_Type';
        if ($lastDocument) {
            // get last document's file
            $condition->condition = "Document_ID='" . $lastDocument->Document_ID . "'";
            $file = Images::model()->find($condition);

            // get w9 row
            $w9 = W9::getCompanyW9($client->Client_ID);
        }

        if ( $w9 && $w9->Verified == 0 ) {
            $enable_editing_w9_form = true; //enable editing only for w9 that has Verified = 0
        }


        //get old w9s with documents
        $lastDocument2 = null;
        $file2 = null;
        $lastDocument3 = null;
        $file3 = null;

        $lastDocument2  = W9::getCompanyW9Doc($client->Client_ID, 2);
        if ($lastDocument2) {
            $condition->condition = "Document_ID='" . $lastDocument2->Document_ID . "'";
            $file2 = Images::model()->find($condition);
        }

        $lastDocument3  = W9::getCompanyW9Doc($client->Client_ID, 3);
        if ($lastDocument3) {
            $condition->condition = "Document_ID='" . $lastDocument3->Document_ID . "'";
            $file3 = Images::model()->find($condition);
        }

        $companiesToShareW9 = Companies::getCompaniesToShareW9();

        $cur_client = Clients::model()->findByPk(Yii::app()->user->clientID);
        $cur_fed_id = $cur_client->company->Company_Fed_ID;

        if (($cur_fed_id == $client->company->Company_Fed_ID) || (Yii::app()->user->userType == Users::DB_ADMIN) ) {
            $fed_ids_match = true;
        }

        $user = Users::model()->with('settings')->findByPk(Yii::app()->user->userID);
        $user_settings = $user->settings;


        $this->render('detail', array(
            'page' => $page,
            'num_pages' => $num_pages,
            'company' => $company,
            'adress' => $adress,
            'client' => $client,
            'adminPerson' => $adminPerson,
            'lastDocument' => $lastDocument,
            'w9' => $w9,
            'file' => $file,
            'vendor' => $vendor,
            'show_edit_vendor_form' => $show_edit_vendor_form,
            'show_edit_company_form' => $show_edit_company_form,
            'enable_editing_w9_form'=>$enable_editing_w9_form,
            'cur_fed_id'=>$cur_fed_id,
            'fed_ids_match'=>$fed_ids_match,
            'companiesToShareW9' => $companiesToShareW9,
            'lastDocument2' => $lastDocument2,
            'file2' => $file2,
            'lastDocument3' => $lastDocument3,
            'file3' => $file3,
            'user_settings'=>$user_settings
        ));
    }

    /**
     * Export Vendors list to Excel
     */
    public function actionExport() {
        error_reporting(0);
        $vendorsList = Vendors::getCompanyVendors('');
        Vendors::exportVendors($vendorsList);
    }

    /**
     * Print Vendors list
     */
    public function actionPrint() {
        error_reporting(0);
        $vendorsList = Vendors::getCompanyVendors('');
        $this->renderPartial('print_vendors_list', array(
            'vendorsList' => $vendorsList,
        ));
    }

    /**
     * Import Vendors List
     */
    public function actionImport() {
        error_reporting(0);
        if (isset($_FILES)) {
            // create user's folder
            if (!file_exists('protected/data/current_uploads_files/' . Yii::app()->user->userID)) {
                mkdir('protected/data/current_uploads_files/' . Yii::app()->user->userID, 0777);
            }

            if (!file_exists('protected/data/current_uploads_files/' . Yii::app()->user->userID . '/' . date('Y-m-d'))) {
                mkdir('protected/data/current_uploads_files/' . Yii::app()->user->userID  . '/' . date('Y-m-d'), 0777);
            }

            if ($_FILES['userfile']['name'] != '') {
                if ($_FILES['userfile']['error'] == 0) {
                    $pathParts = explode('.', $_FILES['userfile']['name']);
                    $mimeType = $_FILES['userfile']['type'];
                    if (isset($pathParts[1])) {
                        $extension = strtolower($pathParts[(count($pathParts) - 1)]);
                        if ($extension == 'xls' || $extension == 'xlsx' || $extension == 'csv') {
                            $filepath = 'protected/data/current_uploads_files/' . Yii::app()->user->userID . '/'  . date('Y-m-d') . '/' . $_FILES['userfile']['name'];
                            move_uploaded_file($_FILES['userfile']['tmp_name'], $filepath);
                           // die("before import file parsing");die;
                            $vendors = Coa::parseImportExcel($filepath);
                          //  die("after import file parsing");
                            $vendors = Vendors::prepareVendorsListForImport($vendors);

                            $this->renderPartial('build_vendors_list_for_import', array(
                                'vendors' => $vendors,
                            ));

                            $_SESSION['imported_vendors'] = $vendors;

                            @unlink($filepath);
                            die;
                        } else {
                            echo Documents::ERROR_INVALID_EXTENSION;
                        }
                    } else {
                        echo Documents::ERROR_INVALID_EXTENSION;
                    }
                } else if ($_FILES['userfile']['error'] == 1) {
                    echo Documents::ERROR_BIG_FILE_SIZE;
                } else {
                    echo Documents::ERROR_LOADING;
                }
            } else {
                echo Documents::ERROR_INVALID_FILE_NAME;
            }
        }
    }

    /**
     * Vendor Management action
     */
    public function actionManage()
    {

        if (!isset($_SESSION['limiter_vendor_left'])) {
            $limit=Aps::DISPLAY_LIMIT;

        } else {$limit=$_SESSION['limiter_vendor_left'];}
        //get company's Vendors list
        $vendorsList = Vendors::getCompanyVendors($limit);

        //get external Vendors list
        $externalClients = Vendors::getExternalClients();
        $companiesToCopyList = Clients::getOtherUserClients();

        $this->render('manage', array(
            'vendorsList' => $vendorsList,
            'companiesToCopyList' => $companiesToCopyList,
            'externalClients' => $externalClients,
        ));
    }

    /**
     * Remove vendors from client's list
     */
    public function actionRemoveVendorsFromList()
    {
        if (isset($_POST['clients'])) {
            foreach ($_POST['clients'] as $clientId) {
                $clientId = intval($clientId);
                if ($clientId > 0) {
                    $clientVendor = Vendors::model()->findByAttributes(array(
                        'Client_Client_ID' => Yii::app()->user->clientID,
                        'Vendor_Client_ID' => $clientId,
                    ));

                    if ($clientVendor) {
                        // set relationship as inactive
                        $clientVendor->Active_Relationship = Vendors::NOT_ACTIVE_RELATIONSHIP;
                        $clientVendor->save();
                    }
                }
            }

            Yii::app()->user->setFlash('success', "Vendors have been successfully removed!");
            $this->redirect('/vendor/manage');
        } else {
            Yii::app()->user->setFlash('success', "Vendors was not removed!");
            $this->redirect('/vendor/manage');
        }
    }

    /**
     * Add vendors to client's list
     */
    public function actionAddVendorsToList()
    {
        if (isset($_POST['clients'])) {
            foreach ($_POST['clients'] as $clientId) {
                $clientId = intval($clientId);
                if ($clientId > 0) {
                    //find existing relationship
                    $clientVendor = Vendors::model()->findByAttributes(array(
                        'Client_Client_ID' => Yii::app()->user->clientID,
                        'Vendor_Client_ID' => $clientId,
                    ));

                    if ($clientVendor) {
                        // if relationship exists, set it active
                        $clientVendor->Active_Relationship = Vendors::ACTIVE_RELATIONSHIP;
                        $clientVendor->save();
                    } else {
                        // if relationship doesn't exists, create it
                        $clientVendor = new Vendors();
                        $clientVendor->Vendor_ID_Shortcut = '';
                        $clientVendor->Vendor_Client_ID = $clientId;
                        $clientVendor->Client_Client_ID = Yii::app()->user->clientID;
                        $clientVendor->Vendor_Name_Checkprint = '';
                        $clientVendor->Vendor_1099 = '';
                        $clientVendor->Vendor_Default_GL = '';
                        $clientVendor->Vendor_Default_GL_Note = '';
                        $clientVendor->Vendor_Note_General = '';
                        $clientVendor->save();
                    }
                }
            }

            Yii::app()->user->setFlash('success', "Vendors have been successfully included!");
            $this->redirect('/vendor/manage');
        } else {
            Yii::app()->user->setFlash('success', "Vendors was not included!");
            $this->redirect('/vendor/manage');
        }
    }

    /**
     * Update Vendor's Shortcut
     */
    public static function actionUpdateShortcut()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['shortcut']) && isset($_POST['client_id'])) {
            $clientId = intval($_POST['client_id']);
            $shortcut = trim($_POST['shortcut']);
            $result = array(
                'changed' => 0,
                'message' => '',
            );

            if ($clientId > 0) {
                if (preg_match('/^[a-zA-Z\d]{5}$/', $shortcut)) {
                    $clientVendor = Vendors::model()->findByAttributes(array(
                        'Client_Client_ID' => Yii::app()->user->clientID,
                        'Vendor_ID_Shortcut' => $shortcut,
                    ));
                    if ($clientVendor) {
                        $result['message'] = 'Shortcut must be unique';
                    } else {
                        $clientVendor = Vendors::model()->findByAttributes(array(
                            'Vendor_Client_ID' => $clientId,
                            'Client_Client_ID' => Yii::app()->user->clientID,
                        ));
                        if ($clientVendor) {
                            $clientVendor->Vendor_ID_Shortcut = $shortcut;
                            $clientVendor->save();
                            $result = array(
                                'changed' => 1,
                                'message' => $shortcut,
                            );
                        }
                    }
                } else {
                    $result['message'] = 'Shortcut must be 5 alpha numeric characters';
                }
            }

            echo CJSON::encode($result);
        }
    }

    /**
     * Add Vendors items to session action
     */
    public function actionAddVendorItemsToSession()
    {
        if (isset($_POST['vendors'])) {
            $_SESSION['vendors_to_review'] = array();
            $i = 1;
            foreach ($_POST['vendors'] as $vendorId) {
                $vendorId = intval($vendorId);
                if ($vendorId > 0) {
                    $clientVendor = Vendors::model()->findByAttributes(array(
                        'Client_Client_ID' => Yii::app()->user->clientID,
                        'Vendor_ID' => $vendorId,
                    ));
                    if ($clientVendor) {
                        $_SESSION['vendors_to_review'][$i] = $vendorId;
                        $i++;
                    }
                }
            }

            $this->redirect('/vendor/detail');
        }
        $this->redirect('/vendor');
    }

    /**
     * Get Vendors list search query action
     */
    public function actionGetListBySearchQuery()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['query'])) {
            $vendorsList = array();

            // set query params
            $queryString = trim($_POST['query']);
            $options = array(

                'search_option_com_name' => intval($_POST['search_option_com_name']),
                'search_option_fed_id' => intval($_POST['search_option_fed_id']),
                'search_option_international' => intval($_POST['search_option_international']),
                'search_option_temporary' => intval($_POST['search_option_temporary']),
                'search_option_shortcut' => intval($_POST['search_option_shortcut']),
                'search_option_addr1' => intval($_POST['search_option_addr1']),
                'search_option_addr2' => intval($_POST['search_option_addr2']),
                'search_option_city' => intval($_POST['search_option_city']),
                'search_option_state' => intval($_POST['search_option_state']),
                'search_option_zip' => intval($_POST['search_option_zip']),
                'search_option_country' => intval($_POST['search_option_country']),
                'search_option_phone' => intval($_POST['search_option_phone']),
                'search_option_limit' => intval($_POST['search_option_limit']),
            );

            $sortOptions = array(
                'sort_by' => $_POST['sort_type'],
                'sort_direction' => $_POST['sort_direction'],
            );

            if(!$options['search_option_limit']) {$limit=0;}
            else {$limit=Aps::DISPLAY_LIMIT;}

            // get Vendors list
            $vendorsList = Vendors::getListByQueryString($queryString, $options, $sortOptions,$limit,0,'');

            // set last search query params to session
            $_SESSION['last_vendors_list_search']['query'] = $queryString;
            $_SESSION['last_vendors_list_search']['options'] = $options;
            $_SESSION['last_vendors_list_search']['sort_options'] = $sortOptions;

           /* $this->renderPartial('vendorlist', array(
                'vendorsList' => $vendorsList,
            ),true);*/

            $result['count']=count($vendorsList);
            $result['html']=$this->renderPartial('application.views.vendor.vendorlist', array(
                    'vendorsList' => $vendorsList,

                ),true
            );


            echo CJSON::encode($result);
        }
    }

    /**
     * Get Vendors list search query action to manage page
     */
    public function actionGetManageListBySearchQuery()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['query'])) {
            $listType = $_POST['list'];

            if ($listType == 'external') {
                $externalVendors = true;
            } else {
                $externalVendors = false;
            }


            $vendorsList = array();

            // set query params
            $queryString = trim($_POST['query']);
            $options = array(
                'search_option_com_name' => intval($_POST['search_option_com_name']),
                'search_option_fed_id' => intval($_POST['search_option_fed_id']),
                'search_option_international' => intval($_POST['search_option_international']),
                'search_option_temporary' => intval($_POST['search_option_temporary']),
                'search_option_addr1' => intval($_POST['search_option_addr1']),
                'search_option_addr2' => intval($_POST['search_option_addr2']),
                'search_option_city' => intval($_POST['search_option_city']),
                'search_option_state' => intval($_POST['search_option_state']),
                'search_option_zip' => intval($_POST['search_option_zip']),
                'search_option_country' => intval($_POST['search_option_country']),
                'search_option_phone' => intval($_POST['search_option_phone']),
                'search_option_vendorlimit_right' => intval($_POST['search_option_vendorlimit_right']),
            );

            /*if(!$options['search_option_vendorlimit_right']) {$limit=0;}
            else {$limit=Aps::DISPLAY_LIMIT;}*/
            $limit=intval($_POST['offset'])?intval($_POST['offset']):0;

           //  var_dump($limit);die;
            $sortOptions = array(
                'sort_by' => $_POST['sort_type'],
                'sort_direction' => $_POST['sort_direction'],
            );

            // get Vendors list
            $vendorsList = Vendors::getListByQueryString($queryString, $options, $sortOptions, $limit,0,$externalVendors);



            $this->renderPartial('managevendorlist', array(
                'vendorsList' => $vendorsList,
                'externalVendors' => $externalVendors,
                'queryString' =>  $queryString,
            ));
        }
    }

    /**
     * Get Vendors list search query action to manage page
     */
    public function actionGetManageListBySearchQueryNextBlock()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['query'])) {
            $listType = $_POST['list'];

            if ($listType == 'external') {
                $externalVendors = true;
            } else {
                $externalVendors = false;
            }


            $vendorsList = array();

            // set query params
            $queryString = trim($_POST['query']);
            $options = array(
                'search_option_com_name' => intval($_POST['search_option_com_name']),
                'search_option_international' => intval($_POST['search_option_international']),
                'search_option_temporary' => intval($_POST['search_option_temporary']),
                'search_option_fed_id' => intval($_POST['search_option_fed_id']),
                'search_option_addr1' => intval($_POST['search_option_addr1']),
                'search_option_addr2' => intval($_POST['search_option_addr2']),
                'search_option_city' => intval($_POST['search_option_city']),
                'search_option_state' => intval($_POST['search_option_state']),
                'search_option_zip' => intval($_POST['search_option_zip']),
                'search_option_country' => intval($_POST['search_option_country']),
                'search_option_phone' => intval($_POST['search_option_phone']),
                'search_option_vendorlimit_right' => intval($_POST['search_option_vendorlimit_right']),
            );

/*            if(!$options['search_option_vendorlimit_right']) {$limit=0;}
            else {$limit=Aps::DISPLAY_LIMIT;}*/

            $limit=intval($_POST['limit']);
            $offset=intval($_POST['offset']);

            //  var_dump($limit);die;
            $sortOptions = array(
                'sort_by' => $_POST['sort_type'],
                'sort_direction' => $_POST['sort_direction'],
            );

            // get Vendors list
            $vendorsList = Vendors::getListByQueryString($queryString, $options, $sortOptions, $limit,$offset,$externalVendors);

            $result['count']=count($vendorsList);
            $result['html']=$this->renderPartial('managevendorlist', array(
                'vendorsList' => $vendorsList,
                'externalVendors' => $externalVendors,
                'queryString' =>  $queryString,
            ),true);



            echo CJSON::encode($result);


        }
    }
/**
     * Get Vendors list search query action to manage page
     */
    public function actionGetVendorsListBySearchQueryNextBlock()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['query'])) {
            $listType = $_POST['list'];

            if ($listType == 'external') {
                $externalVendors = true;
            } else {
                $externalVendors = false;
            }


            $vendorsList = array();

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
                'search_option_vendorlimit_right' => intval($_POST['search_option_vendorlimit_right']),
            );


            $limit=intval($_POST['limit']);
            $offset=intval($_POST['offset']);


            $sortOptions = array(
                'sort_by' => $_POST['sort_type'],
                'sort_direction' => $_POST['sort_direction'],
            );

            // get Vendors list
            $vendorsList = Vendors::getListByQueryString($queryString, $options, $sortOptions, $limit,$offset,$externalVendors);

            $result['count']=count($vendorsList);
            $result['html']=$this->renderPartial('application.views.vendor.vendorlist', array(
                'vendorsList' => $vendorsList,

            ),true
            );


            echo CJSON::encode($result);


        }
    }

    /**
     * Get company info to sidebar
     */
    public function actionGetCompanyInfo()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['vendorId'])) {
            $vendorId = intval($_POST['vendorId']);
            if ($vendorId > 0) {
                $vendor = Vendors::model()->with('client.company.adreses')->findByPk($vendorId);

                $client = $vendor->client;
                $company = $client->company;
                $addresses = $company->adreses;
                $address = $addresses[0];

                $condition = UsersClientList::getClientAdminCondition($client->Client_ID);
                $clientAdmins = UsersClientList::model()->find($condition);

                $clientAdmin = null;
                $adminPerson = null;
                // get company client admin's info
                if (isset($clientAdmins->User_ID)) {
                    $clientAdmin = Users::model()->with('person')->findByPk($clientAdmins->User_ID);
                    $adminPerson = $clientAdmin->person;
                }

                $lastDocument  = W9::getCompanyW9Doc($client->Client_ID);

                $this->renderPartial('company_info_block', array(
                    'address' => $address,
                    'company' => $company,
                    'vendor'=>$vendor,
                    'adminPerson'=>$adminPerson,
                    'lastDocument'=>$lastDocument,
                ));
            }
        }
    }

    /**
     * Clear $_SESSION['vendors_to_review'] if we go to details page directly
     */
    public function actionClearVendorsToReviewSession()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['clear'])) {
            $_SESSION['vendors_to_review'] = array();
            $_SESSION['last_vendors_list_search']['query'] = '';
            $_SESSION['last_vendors_list_search']['options'] = array();
            $_SESSION['last_vendors_list_search']['sort_options'] = array();
        }
    }

    /**
     * Print document action
     */
    public function actionPrintDocument() {
        $clientId = trim($_SESSION['vendor_to_print']);

        $lastDocument  = W9::getCompanyW9Doc($clientId);

        if (Documents::hasAccess($lastDocument->Document_ID)) {
            $condition = new CDbCriteria();
            $condition->select = 'Mime_Type';
            $condition->condition = "Document_ID='" . $lastDocument->Document_ID . "'";
            $file = Images::model()->find($condition);
            $this->renderPartial('print_document', array(
                'document' => $lastDocument,
                'file' => $file,
            ));
        }
    }

    /**
     * Set Vendor_ID to print
     */
    public function actionSetVendorToPrintDocument()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['client_id'])) {
            $client_id= intval($_POST['client_id']);
            if ($client_id > 0) {
                $clientVendor = Vendors::model()->findByAttributes(array(
                    'Client_Client_ID' => Yii::app()->user->clientID,
                    'Vendor_Client_ID' => $client_id,
                ));

                if ($clientVendor) {
                    $_SESSION['vendor_to_print'] = $client_id;
                } else {
                    $_SESSION['vendor_to_print'] = '';
                }
            }
        }
    }

    /**
     * Get vendor's note
     */
    public function actionGetNote()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['vendor_id'])) {
            $vendor_id = intval($_POST['vendor_id']);
            if ($vendor_id > 0) {
                $vendor = Vendors::model()->findByAttributes(array(
                    'Client_Client_ID' => Yii::app()->user->clientID,
                    'Vendor_ID' => $vendor_id,
                ));

                if ($vendor) {
                    echo $vendor->Vendor_Note_General;
                }
            }
        }
    }

    /**
     * Update vendor's note
     */
    public function actionUpdateNote()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['vendor_id']) && isset($_POST['comment'])) {
            $vendor_id = intval($_POST['vendor_id']);
            $comment = trim($_POST['comment']);

            if ($vendor_id > 0 && $comment != '') {
                $vendor = Vendors::model()->findByAttributes(array(
                    'Client_Client_ID' => Yii::app()->user->clientID,
                    'Vendor_ID' => $vendor_id,
                ));

                if ($vendor) {
                    $vendor->Vendor_Note_General = $comment;
                    $vendor->save();

                    $this->renderPartial('note_item_block', array(
                        'vendor' => $vendor,
                    ));
                }
            }
        }
    }

    /**
     * Get in place input html
     */
    public function actionGetInPlaceInput()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['vendorID'])) {
            $vendorID = intval($_POST['vendorID']);

            $vendor = Vendors::model()->findByAttributes(array(
                'Client_Client_ID' => Yii::app()->user->clientID,
                'Vendor_ID' => $vendorID,
            ));

            if ($vendor !== null) {
                echo '<input type="text" value="' . $vendor->Vendor_ID_Shortcut . '" maxlength="5" class="in_place_input" name="in_place_input">';
            }
        }
    }

    /**
     * Update cell value
     */
    public function actionUpdateCellValue()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['vendorID']) && isset($_POST['value'])) {
            $value = trim($_POST['value']);
            $vendorID = intval($_POST['vendorID']);

            $vendor = Vendors::model()->findByAttributes(array(
                'Client_Client_ID' => Yii::app()->user->clientID,
                'Vendor_ID' => $vendorID,
            ));

            if ($vendor !== null) {
                $previousValue = $vendor->Vendor_ID_Shortcut;
                $valueToShow = $value;
                $vendor->Vendor_ID_Shortcut = $value;
                if ($vendor->validate()) {
                    $vendor->save();
                } else {
                    $valueToShow = $previousValue;
                }

                if ($valueToShow == '') {
                    echo '<span class="not_set">Not set</span>';
                } else {
                    echo $valueToShow;
                }
            }
        }
    }

    /**
     * Copy vendors List
     */
    public function actionCopyVendorsList()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['companyId']) && isset($_POST['copyType'])) {
            $clientId = intval($_POST['companyId']);
            $copyType = intval($_POST['copyType']);
            $copyType = ($copyType == 1) ? $copyType : 0;

            $userClient = UsersClientList::model()->findByAttributes(array(
                'User_ID' => Yii::app()->user->userID,
                'Client_ID' => $clientId,
            ));

            if ($userClient !== null) {
                if ($copyType == 0) {
                    Vendors::copyVendorsList(Yii::app()->user->clientID, $clientId);
                } else {
                    Vendors::copyVendorsList($clientId, Yii::app()->user->clientID);
                }
            }
        }
    }


    public function actionGetCompanyDataByName() {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['com_name'])) {
            $name = strval($_POST['com_name']);
            $result = array();
            if (strlen($name)>0){
            $result = Companies::getDataByName($name);
            echo CJSON::encode($result);
            }

        }
    }

   public function actionUpdateVendor() {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['new_fed_id']) && isset($_POST['old_fed_id']) && isset($_POST['w9_doc_id'])&& isset($_POST['filename'])) {

            $doc_id = intval($_POST['w9_doc_id']);

            $document = Documents::model()->findByPk($doc_id);


                if ($document) {
                    $filename = strval($_POST['filename']);
                    $file_id = strval($_POST['file_id']);
                    if ($filename && $file_id) {
                        //$filepath = Yii::app()->basePath.'/data/current_uploads_files/'.Yii::app()->user->userID.'/'.date('Y-m-d').'/'.strval($filename);
                        $filepath = FileCache::getCacheFilePath($file_id);
                        //convert to pdf
                        $result_file_array = FileModification::PdfByFilePath($filepath);
                        $filepath = $result_file_array['filepath'];
                        $filename = $result_file_array['filename'];


                        $image = Images::model()->findByAttributes(array(
                            'Document_ID' => $document->Document_ID
                        ));
                        if (!$image) {
                            $image = new Images();
                            $image->Document_ID = $document->Document_ID;
                        }
                        // updating image

                        $imageData = addslashes( fread(fopen($filepath,'rb'),filesize($filepath)) );
                        $image->Img = $imageData;
                        $image->Mime_Type = Helper::getMimeTypeByFilePAth($filepath);
                        $image->File_Hash = sha1_file($filepath);
                        $image->File_Name = $filename;
                        $image->File_Size = intval(filesize($filepath));

                        $image->Pages_Count = FileModification::calculatePagesByPath($filepath);
                        $image->save();

                        Audits::LogAction($image->Document_ID ,Audits::ACTION_REUPLOAD);

                        @unlink($filepath);

                        $company = Companies::model()->findByAttributes(array(
                            "Company_Fed_ID"=>$_POST['old_fed_id']
                        ));

                        $company->Temp_Fed_ID_Flag = null;
                        $company->Company_Fed_ID = strval($_POST['new_fed_id']);
                        $company->Company_Name = strval($_POST['com_name']);
                        $company->save();

                        $adr = $company->adreses[0];
                        $adr->Address1 = strval($_POST['street_adr']);
                        $adr->City = strval($_POST['city']);
                        $adr->State = strval($_POST['state']);;
                        $adr->ZIP = strval($_POST['zip']);
                        $adr->save();

                    } else { //if file name is empty we are using previous image and just update company info
                        $company = Companies::model()->findByAttributes(array(
                            "Company_Fed_ID"=>$_POST['old_fed_id']
                        ));

                        $company->Temp_Fed_ID_Flag = null;
                        $company->Company_Fed_ID = strval($_POST['new_fed_id']);
                        $company->Company_Name = strval($_POST['com_name']);
                        $company->save();

                        $adr = $company->adreses[0];
                        $adr->Address1 = strval($_POST['street_adr']);
                        $adr->City = strval($_POST['city']);
                        $adr->State = strval($_POST['state']);;
                        $adr->ZIP = strval($_POST['zip']);
                        $adr->save();
                    }



                }

            }
   }

public function actionShowCompanyEditWindow () {
    if (Yii::app()->request->isAjaxRequest && isset($_POST['w9_doc_id']) && isset($_POST['company_id'])) {




    }
}

public function actionGetAdditionFieldsBlock()
    {
        /**
         * Please don't hurt me . This action works in four modes. There are to much IFs in code.
         */
        if (Yii::app()->request->isAjaxRequest && isset($_POST['company_id'])) {

            $company_id = intval($_POST['company_id']);
            $w9_doc_id =intval($_POST['w9_doc_id']);
            $vendor_id =intval($_POST['vendor_id']);
            $cli_adm_person_id = intval($_POST['cli_adm_user_id']);

            //if ($company_id && $w9_doc_id) {
                //here will be code for vendor(W9) uploading from DataEntry or Create Page
                $company = Companies::model()->findByPk($company_id);
                $address = $company->adreses[0];
                $vendor = Vendors::model()->findByPk($vendor_id);
                if ($cli_adm_person_id!=0) {
                    $person = Persons::model()->findByPk($cli_adm_person_id);
                }


                $img = Images::model()->findByAttributes(array(
                    'Document_ID' => $w9_doc_id
                ));

                if ($company && $img) {

                    //its strange but here should be definitely Document_id, it help to system recognize that its file from database. Not from filepath
                    $file['name'] = $img->Document_ID ? $img->Document_ID :$file['name'] = 'W9-Temporary.pdf';
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


                }

            $this->renderPartial('add_data_block', array(
                    'file' => $file,
                    'imgId' => $imgId,
                    'existingCompany' => $existingCompany,
                    'company' => $company,
                    'address' => $address,
                    'vendor' => $vendor,
                    'person' =>$person
                ));
            }

    }





}