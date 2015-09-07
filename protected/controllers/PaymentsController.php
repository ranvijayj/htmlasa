<?php

class PaymentsController extends Controller
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

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('clearpaymentstoreviewsession'),
                'users'=>array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('index', 'detail', 'getlistbysearchquery', 'getpaymentinfo', 'addpaymentsitemstosession',
                                 'printdocument', 'setdocidtoprintdocument', 'senddocumentbyemail', 'getapview','GetNextBlockBySearchquery','GetFreeAps','CheckPaymentNumber'),
                'expression'=>function() {
                    $users = array('admin', 'user', 'approver', 'processor', 'db_admin', 'client_admin');
                    $clientID = isset(Yii::app()->user->clientID) ? Yii::app()->user->clientID : 0;
                    $companyServiceLevel = ClientServiceSettings::getClientServiceSettings($clientID);
                    $tier_settings = isset(Yii::app()->user->tier_settings) ? Yii::app()->user->tier_settings : null ;//array of aggregated settings for current user
                    if (isset(Yii::app()->user->id)
                        && in_array(Yii::app()->user->id, $users)
                        && $companyServiceLevel
                        && $tier_settings['payments']
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
     * Payments list
     */
    public function actionIndex()
	{
        $payments_to_review = array();
        $queryString = '';

        if (!isset($_SESSION['limiter'])) {
            $limit=Aps::DISPLAY_LIMIT;

        } else {$limit=$_SESSION['limiter'];}

        //get APs list
        if (isset( $_SESSION['last_payments_list_search']['options']) && count($_SESSION['last_payments_list_search']['options']) > 0) {
            $queryString = $_SESSION['last_payments_list_search']['query'];
            $options = $_SESSION['last_payments_list_search']['options'];
            $sortOptions = $_SESSION['last_payments_list_search']['sort_options'];

            // get Payments list
            $paymentsList = Payments::getListByQueryString($queryString, $options, $sortOptions,$limit);

            $_SESSION['last_payments_list_search']['query'] = '';
            $_SESSION['last_payments_list_search']['sort_options'] = array();
        } else {
            $paymentsList = Payments::getPaymentsList($limit);
        }

        // company ids to review
        if (isset($_SESSION['pm_to_review'])) {
            $payments_to_review = $_SESSION['pm_to_review'];
            $_SESSION['pm_to_review'] = array();
        }

        $acctNums = BankAcctNums::getClientAccountNumbers(6, false);

        $this->render('index', array(
            'paymentsList' => $paymentsList,
            'payments_to_review' => $payments_to_review,
            'queryString' => $queryString,
            'acctNums' => $acctNums,
        ));
	}

    public function actionCheckPaymentNumber()
    {
        if (Yii::app()->request->isAjaxRequest && $_POST['num']!='' ) {
            $num=strval($_POST['num']);

            $sql = "select count(payment_id) from payments where payment_check_number=".$num;

            $list= Yii::app()->db->createCommand($sql)->queryAll();

            echo $list[0]['count(payment_id)'];

        }
    }


    /*
     * Returns Aps not connected to any payment
     * Used for dists tables in Dataentry
     */
    public function actionGetFreeAps()
    {
        if (Yii::app()->request->isAjaxRequest && $_POST['vendor_id']!='' && intval($_POST['vendor_id'])!=0 ) {

            $document = Documents::model()->findByPk(intval($_POST['doc_id']));

            $condition = new CDbCriteria();

            $condition->condition = "t.Vendor_ID=".intval($_POST['vendor_id'])." AND t.Payment_ID=0";

            $condition->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID";

            $condition->addSearchCondition('t.Invoice_Number', $_POST['inv_number']);
            $condition->addCondition("documents.Project_ID = '" . $document->Project_ID . "'");

        $aps = Aps::model()->findAll($condition);

            foreach ($aps as $ap){
                $result[]= $ap["Invoice_Number"].' : $'. $ap["Invoice_Amount"];
            }

          echo CJSON::encode($result);

        }
    }

    /**
     *
     */
    public function actionGetNextBlockBySearchquery()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['query'])) {
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

            $limit=intval($_POST['limit']);
            $offset=intval($_POST['offset']);

            $sortOptions = array(
                'sort_by' => $_POST['sort_type'],
                'sort_direction' => $_POST['sort_direction'],
            );




            $paymentsList = Payments::getListByQueryString($queryString, $options, $sortOptions,$limit,$offset);

            $result['count']=count($paymentsList);
            $result['html']=$this->renderPartial('application.views.payments.paymentslist', array(
                    'paymentsList' => $paymentsList,

                ),true
            );



            echo CJSON::encode($result);


        }
    }


    /**
     * Payment detail page action
     * @param int $page
     */
    public function actionDetail($page = 1)
    {
        $cs = Yii::app()->getClientScript();

        $cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/jupload/vendor/jquery.ui.widget.js');
        $cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/jupload/jquery.iframe-transport.js');
        $cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/jupload/jquery.fileupload.js');
        $cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/jupload/jquery.fileupload-process.js');


        // check AP to review
        if (!isset($_SESSION['pm_to_review']) || count($_SESSION['pm_to_review']) == 0) {
            $_SESSION['pm_to_review'] = Payments::getLastClientsPayment();
            if (!isset($_SESSION['pm_to_review']) || count($_SESSION['pm_to_review']) == 0) {
                Yii::app()->user->setFlash('success', "Please choose Payments to review!");
                $this->redirect('/payments');
            }
        }

        $page = intval($page);
        $num_pages = count($_SESSION['pm_to_review']);
        if ($page <= 0) {
            $page = 1;
        } else if ($page > $num_pages) {
            $page = $num_pages;
        }

        $docId = $_SESSION['pm_to_review'][$page];

        $payment = Payments::model()->with('vendor', 'document', 'payment_invoices', 'bank_account','aps')->findByAttributes(array(
            'Document_ID' => $docId,
        ));
        //var_dump($payment);
        $vendor = $payment->vendor;
        $company = '';
        $address = '';
        if ($vendor) {
            $client = $vendor->client;
            $company = $client->company;
            $address = $company->adreses[0];
        }

        $document = $payment->document;
        $user = $document->user;
        $aps = $payment->aps;
        //var_dump($aps);die;

        $payment_invoices = $payment->payment_invoices;

        $bank_account = '';
        if ($payment->bank_account) {
            $bank_account = $payment->bank_account->Account_Name . ' / ' . Helper::prepareAcctNum($payment->bank_account->Account_Number, 6);
        }

        $condition = new CDbCriteria();
        $condition->select = 'Mime_Type';

        $ap = array();
        $apBackup = array();
        if (is_array($aps) && count($aps) > 0) {
            $ap['payment'] = $aps[0];
            $ap['AP_ID']   = $aps[0]->AP_ID;
            $ap['Count_AP']   = count($aps);

            $condition->condition = "Document_ID='" . $aps[0]->Document_ID . "'";
            $ap['image'] = Images::model()->find($condition);
            if ($aps[0]->AP_Backup_Document_ID != 0) {
                $condition->condition = "Document_ID='" . $aps[0]->AP_Backup_Document_ID . "'";
                $apBackup['image'] = Images::model()->find($condition);
            }
        }

        // get document's file
        $condition->condition = "Document_ID='" . $document->Document_ID . "'";
        $file = Images::model()->find($condition);

        $this->render('detail', array(
            'page' => $page,
            'num_pages' => $num_pages,
            'company' => $company,
            'payment' => $payment,
            'user' => $user,
            'document' => $document,
            'aps' => $aps,
            'file' => $file,
            'address' => $address,
            'payment_invoices' => $payment_invoices,
            'bank_account' => $bank_account,
            'ap' => $ap,
            'apBackup' => $apBackup,
        ));
    }

    /**
     * Clear $_SESSION['pm_to_review'] if we go to details page directly
     */
    public function actionClearPaymentsToReviewSession()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['clear'])) {
            $_SESSION['pm_to_review'] = array();
            $_SESSION['last_payments_list_search']['query'] = '';
            $_SESSION['last_payments_list_search']['options'] = array();
            $_SESSION['last_payments_list_search']['sort_options'] = array();
        }
    }

    /**
     * Add Payments items to session action
     */
    public function actionAddPaymentsItemsToSession()
    {
        if (isset($_POST['documents'])) {
            $_SESSION['pm_to_review'] = array();
            $i = 1;
            foreach ($_POST['documents'] as $docId) {
                $docId = intval($docId);
                if ($docId > 0 && Documents::hasAccess($docId)) {
                    $document = Documents::model()->with('client')->findByPk($docId);
                    $payment = Payments::model()->findByAttributes(array(
                        'Document_ID' => $docId,
                    ));
                    if ($document && $payment) {
                        $_SESSION['pm_to_review'][$i] = $docId;
                        $i++;
                    }
                }
            }

            $this->redirect('/payments/detail');
        }
    }

    /**
     * Get Payments list by search query action
     */
    public function actionGetListBySearchQuery()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['query'])) {
            $paymentsList = array();
            $bankAccounts = array();

            if (isset($_POST['bankAccounts'])) {
                foreach ($_POST['bankAccounts'] as $bankAccount) {
                    $bankAccount = intval($bankAccount);
                    $bankAccounts[] = $bankAccount;
                }
            }

            // set query params
            $queryString = trim($_POST['query']);
            $options = array(
                'search_option_payment_check_date' => intval($_POST['search_option_payment_check_date']),
                'search_option_payment_check_number' => intval($_POST['search_option_payment_check_number']),
                'search_option_payment_amount' => intval($_POST['search_option_payment_amount']),
                'search_option_invoice_number' => intval($_POST['search_option_invoice_number']),
                'search_option_invoice_amount' => intval($_POST['search_option_invoice_amount']),
                'search_option_invoice_date' => intval($_POST['search_option_invoice_date']),
                'search_option_com_name' => intval($_POST['search_option_com_name']),
                'search_option_fed_id' => intval($_POST['search_option_fed_id']),
                'search_option_addr1' => intval($_POST['search_option_addr1']),
                'search_option_city' => intval($_POST['search_option_city']),
                'search_option_state' => intval($_POST['search_option_state']),
                'search_option_zip' => intval($_POST['search_option_zip']),
                'search_option_limit' => intval($_POST['search_option_limit']),
                'bankAccounts' => $bankAccounts,
            );

            $sortOptions = array(
                'sort_by' => $_POST['sort_type'],
                'sort_direction' => $_POST['sort_direction'],
            );

            if(!$options['search_option_limit']) {$limit=0;}
            else {$limit=Aps::DISPLAY_LIMIT;}

            // set last search query params to session
            $_SESSION['last_payments_list_search']['query'] = $queryString;
            $_SESSION['last_payments_list_search']['options'] = $options;
            $_SESSION['last_payments_list_search']['sort_options'] = $sortOptions;

            // get Payments list
            $paymentsList = Payments::getListByQueryString($queryString, $options, $sortOptions,$limit);

            $this->renderPartial('paymentslist', array(
                'paymentsList' => $paymentsList,
            ));
        }
    }

    /**
     * Get Payment info to sidebar
     */
    public function actionGetPaymentInfo()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['docId'])) {
            $docId = intval($_POST['docId']);
            if ($docId > 0) {
                $payment = Payments::model()->with('vendor', 'document', 'payment_invoices', 'bank_account')->findByAttributes(array(
                    'Document_ID' => $docId,
                ));

                $vendor = $payment->vendor;
                $company = '';
                $address = '';
                if ($vendor) {
                    $client = $vendor->client;
                    $company = $client->company;
                    $address = $company->adreses[0];
                }

                $document = $payment->document;
                $user = $document->user;
                $aps = $payment->payment_invoices;

                $bankAccount = '';
                if ($payment->bank_account) {
                    $bankAccount = $payment->bank_account->Account_Name . ' / ' . Helper::prepareAcctNum($payment->bank_account->Account_Number, 6);
                }

                $this->renderPartial('payment_info_block', array(
                    'payment' => $payment,
                    'company' => $company,
                    'user' => $user,
                    'document' => $document,
                    'aps' => $aps,
                    'address' => $address,
                    'bankAccount' => $bankAccount,
                ));
            }
        }
    }

    /**
     * Get AP view
     */
    public function actionGetAPView()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['apId'])) {
            $apId = intval($_POST['apId']);
            $ap = Aps::model()->with('dists')->findByPk($apId);
            $apArr = array();
            $approvalValue = Aps::NOT_READY_FOR_APPROVAL;
            if ($ap && Documents::hasAccess($ap->Document_ID)) {
                $document = Documents::model()->with('image')->findByPk($ap->Document_ID);
                $apArr['payment'] = $ap;
                $apArr['image'] = $document->image;
                $approvalValue = intval($ap->AP_Approval_Value);
            }

            $htmlAP =  $this->renderPartial('application.views.payments.tabs.payable_view', array(
                'ap' => $apArr,
            ), true);

            $apBackup = array();
            if ($ap->AP_Backup_Document_ID != 0) {
                $condition = new CDbCriteria();
                $condition->select = 'Mime_Type';
                $condition->condition = "Document_ID='" . $ap->AP_Backup_Document_ID . "'";
                $apBackup['image'] = Images::model()->find($condition);
            }

            $htmlAPBackup =  $this->renderPartial('application.views.payments.tabs.ap_detail_view', array(
                'ap' => $apArr,
                'apBackup' => $apBackup,
            ), true);

            $distsHtml = $this->renderPartial('application.views.ap.dists_list', array('dists' => $ap->dists), true);

            $result = array(
                'htmlAP' => $htmlAP,
                'htmlAPBackup' => $htmlAPBackup,
                'appval' => $approvalValue,
                'distsHtml' => $distsHtml,
            );

            echo CJSON::encode($result);
        }
    }

    /**
     * Print document action
     */
    public function actionPrintDocument()
    {
        $docId = trim($_SESSION['payment_to_print']);

        //get document
        $document = Documents::model()->findByPk($docId);

        if ($document && Documents::hasAccess($docId)) {
            $condition = new CDbCriteria();
            $condition->select = 'Mime_Type';
            $condition->condition = "Document_ID='" . $document->Document_ID . "'";
            $file = Images::model()->find($condition);
            $this->renderPartial('print_document', array(
                'document' => $document,
                'file' => $file,
            ));
        }
    }

    /**
     * Set Doc ID of Payment to print
     */
    public function actionSetDocIdToPrintDocument()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['docId'])) {
            $docId = intval($_POST['docId']);
            if ($docId > 0 && Documents::hasAccess($docId)) {
                $document = Documents::model()->findByPk($docId);
                if ($document) {
                    $_SESSION['payment_to_print'] = $docId;
                } else {
                    $_SESSION['payment_to_print'] = '';
                }
            }
        }
    }

    /**
     * Send document by email action
     */
    public function actionSendDocumentByEmail()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['email']) && isset($_POST['docId'])) {
            $docId = intval($_POST['docId']);
            $email = trim($_POST['email']);
            $email_array = Helper::splitEmails($email) ;
            if ($docId > 0 && $email != '' && Documents::hasAccess($docId)) {
                $document = Documents::model()->findByPk($docId);
                $condition = new CDbCriteria();
                $condition->condition = "Document_ID='" . $document->Document_ID . "'";
                $user = Users::model()->findByPk(Yii::app()->user->userID);
                $file = Images::model()->find($condition);

                $payment = Payments::model()->with('vendor', 'document', 'aps')->findByAttributes(array(
                    'Document_ID' => $docId,
                ));

                $vendor = $payment->vendor;
                $client = $vendor->client;

                $filePath = 'protected/data/docs_to_email/' . $file->File_Name;
                file_put_contents($filePath, stripslashes($file->Img));

                //send document(s)
                foreach ($email_array as $email_item) {
                    Emails::logEmailSending(Yii::app()->user->clientID,Yii::app()->user->userID,Yii::app()->user->projectID,$email_item);
                    Mail::sendDocument($email, $file->File_Name, $filePath, $client->company->Company_Name,$user);
                }

                //delete file
                unlink($filePath);

                echo 1;
            } else {
                echo 0;
            }
        }
    }



}