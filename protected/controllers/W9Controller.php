<?php

class W9Controller extends Controller
{
    public $layoutColor = "#00a33d";

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
                'actions'=>array('clearw9toreviewsession'),
                'users'=>array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('index', 'detail', 'getlistbysearchquery', 'getcompanyinfo', 'addw9itemstosession', 'addnote',
                                 'printdocument', 'senddocumentbyemail', 'senddocumentbyfax', 'setfedidtoprintdocument',
                                 'sharew9','GetNextTempFedID'),
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

    /**
     * Index action
     * W9 list
     */
    public function actionIndex()
	{
        $vendorsList = array();
        $w9_to_review = array();
        $queryString = '';

        if (!isset($_SESSION['limiter'])) {
            $limit=Aps::DISPLAY_LIMIT;

        } else {$limit=$_SESSION['limiter'];}


        // set last vendors list
        //if (isset($_SESSION['last_w9_list_search'])) {
            $queryString = $_SESSION['last_w9_list_search']['query'];
            $searchOptions = $_SESSION['last_w9_list_search']['options'];
            $sortOptions = $_SESSION['last_w9_list_search']['sort_options'];
            //if (trim($queryString) != '') {
                $companies = new Companies();
                $vendorsList = $companies->getListByQueryString($queryString, $searchOptions, $sortOptions,$limit);
            //}
        //}

        // company ids to review
        if (isset($_SESSION['w9_to_review'])) {
            $w9_to_review = $_SESSION['w9_to_review'];
        }

        $current_client_w9 = W9::getW9ByClientID(Yii::app()->user->clientID);

        $this->render('index', array(
            'vendorsList' => $vendorsList,
            'w9_to_review' => $w9_to_review,
            'current_client_w9'=>$current_client_w9[0],
            'queryString' => $queryString,
        ));
	}

    /**
     * W9 detail page action
     * @param int $page
     */
    public function actionDetail($page = 1)
    {
        // check w9 to review
        if (!isset($_SESSION['w9_to_review']) || count($_SESSION['w9_to_review']) == 0) {
            Yii::app()->user->setFlash('success', "Please choose vendors to review!");
            $this->redirect('/w9');
        }

        $page = intval($page);
        $num_pages = count($_SESSION['w9_to_review']);
        if ($page <= 0) {
            $page = 1;
        } else if ($page > $num_pages) {
            $page = $num_pages;
        }

        $comId = $_SESSION['w9_to_review'][$page];

        //get company info
        $company = Companies::model()->with('adreses', 'client')->findByPk($comId);
        $client = $company->client;
        $adress = $company->adreses[0];

        $condition = UsersClientList::getClientAdminCondition($client->Client_ID);
        $clientAdmins = UsersClientList::model()->find($condition);

        // get company client admin's info
        $adminPerson = '';
        if ($clientAdmins) {
            $clientAdmin = Users::model()->with('person')->findByPk($clientAdmins->User_ID);
            $adminPerson = $clientAdmin->person;
        }

        // get last company w9 document
        $lastDocument  = W9::getCompanyW9Doc($client->Client_ID);

        if ($lastDocument === null) {
            Yii::app()->user->setFlash('success', "Please choose vendors to review!");
            $this->redirect('/w9');
            die;
        }

        // get last document's file
        $condition = new CDbCriteria();
        $condition->select = 'Mime_Type';
        $condition->condition = "Document_ID='" . $lastDocument->Document_ID . "'";
        $file = Images::model()->find($condition);

        // get w9 row
        $w9 = W9::getCompanyW9($client->Client_ID);

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

        // get notes
        $notes = Notes::model()->getCompanyClientsNotes($comId);

        $companiesToShareW9 = Companies::getCompaniesToShareW9();

        $this->render('detail', array(
            'page' => $page,
            'num_pages' => $num_pages,
            'company' => $company,
            'adress' => $adress,
            'client' => $client,
            'adminPerson' => $adminPerson,
            'lastDocument' => $lastDocument,
            'w9' => $w9,
            'notes' => $notes,
            'file' => $file,
            'companiesToShareW9' => $companiesToShareW9,
            'lastDocument2' => $lastDocument2,
            'file2' => $file2,
            'lastDocument3' => $lastDocument3,
            'file3' => $file3,
        ));
    }

    /**
     * Add w9 items to session action
     */
    public function actionAddW9ItemsToSession()
    {
        if (isset($_POST['companies'])) {
            $_SESSION['w9_to_review'] = array();
            $i = 1;
            foreach ($_POST['companies'] as $comId) {
                $comId = intval($comId);
                if ($comId > 0) {
                    $company = Companies::model()->with('client')->findByPk($comId);
                    $w9 = W9::model()->findByAttributes(array(
                        'Client_ID' => $company->client->Client_ID,
                        'W9_Owner_ID' => Yii::app()->user->clientID,
                    ));
                    if ($w9) {
                        $_SESSION['w9_to_review'][$i] = $comId;
                        $i++;
                    }
                }
            }

            $this->redirect('/w9/detail');
        }
    }

    /**
     * Clear W9 to review session and put there
     */
    public function actionClearW9ToReviewSession()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['clear'])) {
            $_SESSION['w9_to_review'] = array();

            if (isset(Yii::app()->user->clientID)) {
                $client = Clients::model()->findByPk(Yii::app()->user->clientID);
                $w9 = W9::model()->findByAttributes(array(
                    'Client_ID' => Yii::app()->user->clientID,
                    'W9_Owner_ID' => Yii::app()->user->clientID,
                ));

                if ($w9) {
                    $_SESSION['w9_to_review'][1] = $client->Company_ID;
                }
            }

        }
    }

    /**
     * Get vendors list search query action
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
                'search_option_addr1' => intval($_POST['search_option_addr1']),
                'search_option_addr2' => intval($_POST['search_option_addr2']),
                'search_option_city' => intval($_POST['search_option_city']),
                'search_option_state' => intval($_POST['search_option_state']),
                'search_option_zip' => intval($_POST['search_option_zip']),
                'search_option_country' => intval($_POST['search_option_country']),
                'search_option_phone' => intval($_POST['search_option_phone']),
                'search_option_limit' => intval($_POST['search_option_limit']),
            );

            if(!$options['search_option_limit']) {$limit=0;}
            else {$limit=Aps::DISPLAY_LIMIT;}

            $sortOptions = array(
                'sort_by' => $_POST['sort_type'],
                'sort_direction' => $_POST['sort_direction'],
            );

            // set last search query params to session
            $_SESSION['last_w9_list_search']['query'] = $queryString;
            $_SESSION['last_w9_list_search']['options'] = $options;
            $_SESSION['last_w9_list_search']['sort_options'] = $sortOptions;

            // get vendors list
                $companies = new Companies();
                $vendorsList = $companies->getListByQueryString($queryString, $options, $sortOptions,$limit);

            $this->renderPartial('w9list', array(
                'vendorsList' => $vendorsList,
            ));
        }
    }


    /**
     * Get company info to sidebar
     */
    public function actionGetCompanyInfo()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['comId'])) {
            $comId = intval($_POST['comId']);
            if ($comId > 0) {
                $company = Companies::model()->with('adreses', 'client')->findByPk($comId);
                $client = $company->client;
                $adress = $company->adreses[0];

                $condition = UsersClientList::getClientAdminCondition($client->Client_ID);
                $clientAdmins = UsersClientList::model()->find($condition);

                $adminPerson = '';
                if ($clientAdmins) {
                    $clientAdmin = Users::model()->with('person')->findByPk($clientAdmins->User_ID);
                    $adminPerson = $clientAdmin->person;
                }

                $lastDocument  = W9::getCompanyW9Doc($client->Client_ID);

                $this->renderPartial('company_info_block', array(
                    'company' => $company,
                    'adress' => $adress,
                    'adminPerson' => $adminPerson,
                    'lastDocument' => $lastDocument,
                ));
            }
        }
    }

    /**
     * Print document action
     */
    public function actionPrintDocument() {
        $fed_id = trim($_SESSION['document_to_print']);

        //get company with client
        $company = Companies::model()->with('client')->findByAttributes(array(
            'Company_Fed_ID' => $fed_id,
        ));

        if ($company) {
            $lastDocument  = W9::getCompanyW9Doc($company->client->Client_ID);
            if ($lastDocument && Documents::hasAccess($lastDocument->Document_ID)) {
                $condition = new CDbCriteria();
                $condition->select = 'Mime_Type';
                $condition->condition = "Document_ID='" . $lastDocument->Document_ID . "'";
                $file = Images::model()->find($condition);
                $this->renderPartial('print_document', array(
                    'lastDocument' => $lastDocument,
                    'file' => $file,
                ));
            }
        }
    }

    /**
     * Set Fed ID of the company to print document
     */
    public function actionSetFedIdToPrintDocument()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['fed_id'])) {
            $fed_id = trim($_POST['fed_id']);
            $company = Companies::model()->with('client')->findByAttributes(array(
                'Company_Fed_ID' => $fed_id,
            ));

            if ($company) {
                $_SESSION['document_to_print'] = $fed_id;
            } else {
                $_SESSION['document_to_print'] = '';
            }
        }
    }

    /**
     * Send document by email action
     */
    public function actionSendDocumentByEmail()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['email']) && isset($_POST['client_id'])) {
            $client_id = intval($_POST['client_id']);
            $email = trim($_POST['email']);
            if ($client_id > 0 && $email != '') {
                $client = Clients::model()->with('company')->findByPk($client_id);

                $lastDocument  = W9::getCompanyW9Doc($client_id);

                if (Documents::hasAccess($lastDocument->Document_ID)) {
                    $condition = new CDbCriteria();
                    $condition->condition = "Document_ID='" . $lastDocument->Document_ID . "'";
                    $file = Images::model()->find($condition);

                    $filePath = 'protected/data/docs_to_email/' . $file->File_Name;
                    file_put_contents($filePath, stripslashes($file->Img));

                    //send document
                    Mail::sendDocument($email, $file->File_Name, $filePath, $client->company->Company_Name);

                    //delete file
                    unlink($filePath);

                    echo 1;
                } else {
                    echo 0;
                }
            } else {
                echo 0;
            }
        }
    }

    /**
     * Send document by fax action
     */
    public function actionSendDocumentByFax()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['fax']) && isset($_POST['client_id'])) {
            $client_id = intval($_POST['client_id']);
            $fax = trim($_POST['fax']);
            if ($client_id > 0 && $fax != '') {

                //get last company w9
                $lastDocument  = W9::getCompanyW9Doc($client_id);

                if (Documents::hasAccess($lastDocument->Document_ID)) {
                    //get document's file
                    $condition = new CDbCriteria();
                    $condition->condition = "Document_ID='" . $lastDocument->Document_ID . "'";
                    $file = Images::model()->find($condition);

                    $filePath = 'protected/data/docs_to_fax/' . $file->File_Name;
                    file_put_contents($filePath, stripslashes($file->Img));

                    //send document
                    $fax = new Fax($fax, $filePath, $file->Mime_Type);
                    $result = $fax->sendDocument();

                    //delete file
                    unlink($filePath);

                    if ($result > 0) {
                        echo 1;
                    } else {
                        echo 0;
                    }
                } else {
                    echo 0;
                }
            } else {
                echo 0;
            }
        }
    }

    public function actionShareW9()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['companyId']) && isset($_POST['accessType']) && isset($_POST['w9Id'])) {
            $clientId = intval($_POST['companyId']);
            $access = intval($_POST['accessType']);
            $w9Id = intval($_POST['w9Id']);

            //check if user can change permitions for this w9
            $client = Clients::model()->findByPk($clientId);
            $cur_client = Clients::model()->findByPk(Yii::app()->user->clientID);
            $cur_fed_id = $cur_client->company->Company_Fed_ID;

            if (($cur_fed_id == $client->company->Company_Fed_ID) || (Yii::app()->user->userType == Users::DB_ADMIN) ) {
                $fed_ids_match = true;
            }


            if ($clientId > 0 && $access >= 0 && $access <= 1 && $w9Id > 0 ) {
                $w9 = W9::model()->findByAttributes(array(
                    'W9_ID' => $w9Id,
                    'W9_Owner_ID' => Yii::app()->user->clientID,
                    'Access_Type' => W9::HAS_ACCESS,
                ));

                if ($w9 !== null) {
                    $w9->share($clientId, $fed_ids_match ? $access : 0);
                }
            }
        }
    }

    /**
     * Add note action
     */
    public function actionAddNote()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['comment']) && isset($_POST['company_id'])) {
            $comId = intval($_POST['company_id']);
            $comment = $_POST['comment'];
            if ($comId > 0 && trim($comment) != '') {
                $note = new Notes;
                $note->Document_ID = 0;
                $note->User_ID = Yii::app()->user->userID;
                $note->Company_ID = $comId;
                $note->Client_ID = Yii::app()->user->clientID;
                $note->Comment = $comment;
                $note->Created = date("Y-m-d H:i:s");
                $note->save();

                $user = Users::model()->with('person')->findByPk(Yii::app()->user->userID);

                $this->renderPartial('note_item_block', array(
                    'note' => $note,
                    'user' => $user,
                ));
            }
        }
    }

    public function actionGetNextTempFedID() {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['fed_id_type'])) {
            $type = strval($_POST['fed_id_type']);
            $number = W9::getGetNextTempFedIDNumber($type);
            echo $number;
        }
    }



}