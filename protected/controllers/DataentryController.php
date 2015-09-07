<?php

class DataentryController extends Controller
{
    public $layoutColor = "#00a33d";
    public $showDataEntryMenu = true;

    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public $layout='//layouts/data_entry';

    /**
     * Available payment types
     * @var array
     */
    public $paymentTypes = array(
        'OA' => 'On Account',
        'CC' => 'Credit Card',
        'DP' => 'Deposit',
        'CK' => 'Payment Check',
        'PC' => 'Petty Cash',
    );

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
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
                'actions'=>array('index', 'getvendorslist', 'getcustomerslist','ajaxw9save','AjaxPOSave','AjaxAPSave','AjaxPaySave','AjaxPcSave','AjaxPayrSave','AjaxJESave','AjaxARSave',
                    'AjaxApDataentry','AjaxApFromDetail',
                    'AjaxPoDataentry','AjaxPoFromDetail','AjaxPoFromDetailFull',
                    'AjaxPaymDataEntry','AjaxPaymFromDetail',
                    'AjaxPayrDataEntry','AjaxPayrFromDetail',
                    'AjaxJeDataEntry','AjaxJeFromDetail',
                    'AjaxArDataEntry','AjaxArFromDetail',
                ),
                'users'=>array('admin', 'data_entry_clerk', 'approver', 'processor', 'db_admin', 'client_admin','user'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('w9', 'ap', 'payments', 'po', 'pc', 'payroll', 'ar', 'je','ajaxw9save','assign'),
                'expression'=>function() {

                    $users = array('admin', 'data_entry_clerk', 'approver', 'processor', 'db_admin', 'client_admin');
                    $clientID = isset(Yii::app()->user->clientID) ? Yii::app()->user->clientID : 0;
                    $companyServiceLevel = ClientServiceSettings::getClientServiceSettings($clientID);
                    $action = Yii::app()->controller->action->id;

                    $tier_settings = isset(Yii::app()->user->tier_settings) ? Yii::app()->user->tier_settings : null ;//array of aggregated settings for current user
                    if (isset(Yii::app()->user->id)
                        && in_array(Yii::app()->user->id, $users)
                        && $companyServiceLevel
                        && $tier_settings
                        && in_array($action, $tier_settings['data_entry'])
                        && $companyServiceLevel->Active_To >= date('Y-m-d')) {
                        return true;
                    } else {return false;}
                },
            ),
            array('allow', // allow authenticated users with type "User"  perform actions
                'actions'=>array('w9', 'ap', 'payments','pc', 'po', 'payroll', 'ar', 'je','ajaxw9save'),
                'expression'=>function() {
                        $users = array('user');
                        $clientID = isset(Yii::app()->user->clientID) ? Yii::app()->user->clientID : 0;
                        $companyServiceLevel = ClientServiceSettings::getClientServiceSettings($clientID);
                        $action = Yii::app()->controller->action->id;
                        $tier_settings = isset(Yii::app()->user->tier_settings) ? Yii::app()->user->tier_settings : null ;//array of aggregated settings for current user

                        if (isset(Yii::app()->user->id)
                            && in_array(Yii::app()->user->id, $users)
                            && $companyServiceLevel
                            && $tier_settings['data_entry']
                            && in_array($action, $tier_settings['data_entry'])
                            && $companyServiceLevel->Active_To >= date('Y-m-d')) {
                            return true;
                        } else {return false;}
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
        $this->redirect('/dataentry/w9');
	}

    /**
     * Payments data entry page action
     * @param int $page
     */
    public function actionPayments($page = 1)
    {
        //initialize variables
        $company = '';
        $vendor = '';
        $client = '';
        $document = '';
        $payment = '';
        $file = '';
        $invoices = array();
        $saved = false;
        $invalidInvoices = '';
        $invalidInvoicesTopMess = '';

        //reset search option to default
        if (!isset($_SESSION['last_paym_to_entry_search'])) {
            Payments::resetDataentrySearchOptions();
        }
        // set query params
        if (isset($_POST['search_field'])) {
            Payments::initDataentrySearchOptions($_POST);
        }


        // form processing
        if (isset($_POST['payment_data_entry_form_values'])) {
            $paymentId = intval($_POST['Payments']['Payment_ID']);
            $payment = Payments::model()->findByPk($paymentId);
            $invoices = $_POST['Invoice'] ? $_POST['Invoice'] : array();
            $invoices =  PaymentsInvoice::adjustInvoicesType($invoices);

            $invoicesToDB = array();
            $totalInvSum = 0;
            $void = intval($_POST['Payments']['Void']);
            //convert date string to server format
            $_POST['Payments']['Payment_Check_Date'] = Helper::checkDate($_POST['Payments']['Payment_Check_Date']);

            // get valid invoices if not voided

            if (!$void) {
                foreach ($invoices as $key => $invoice) {
                    $invNumber = $invoice['Invoice_Number'];
                    //$invAmount = $invoice['Invoice_Amount'];
                    $invAmount = round(floatval($invoice['Invoice_Amount']),2);

                    if (!is_numeric($invAmount) || $invAmount === '') {
                        $invAmount = '';
                    }

                    if ($invAmount !== '' && $invNumber != '') {
                        $invoicesToDB[] = $invoice;
                        $totalInvSum += $invAmount;
                    }
                }

            }
            // if payment exists
            if ($payment) {
                $document = Documents::model()->findByPk($payment->Document_ID);

                // get last document's file
                $condition = new CDbCriteria();
                $condition->select = 'Mime_Type,File_Name';
                $condition->condition = "Document_ID='" . $document->Document_ID . "'";
                $file = Images::model()->find($condition);

                $payment->attributes = $_POST['Payments'];

                if ($payment->Vendor_ID > 0) {
                    $vendor = Vendors::model()->with('client')->findByPk($payment->Vendor_ID);
                    $client = $vendor->client;
                    $company = $client->company;
                } else {
                    $client = false;
                    $company = false;
                    $vendor = false;
                }

                // validate form
                $is_valid = $payment->validate();
                if ($payment->Payment_Check_Number == '' || $payment->Payment_Check_Number == '0') {
                    $payment->addError('Payment_Check_Number','Pmt. Number is required!');
                }

                if ($payment->Payment_Amount == '' || $payment->Payment_Amount < 0 ) {
                    $payment->addError('Payment_Amount','Pmt. Amount is required!');
                }

                if ($payment->Payment_Amount == 0 && $payment->Void == 0 ) {
                    $payment->addError('Payment_Amount','Zero value allowed only for voided');
                }

                if ($payment->Payment_Check_Date == '') {
                    $payment->addError('Payment_Check_Date','Pmt. Date is required!');
                }

                if ($payment->Vendor_ID == '0') {
                    $payment->addError('Vendor_ID','Choose Vendor!');
                }

                /**
                if ($payment->Account_Num_ID == '0') {
                    $payment->addError('Account_Num_ID','Choose Acct. Number!');
                }
               */

                if (round(floatval($payment->Payment_Amount),2) != round(floatval($totalInvSum),2) ) {
                    $invalidInvoices = "The detail of Invoices Attached is not in balance with the payment total.
                                           You're out by $" . round(abs(round(floatval($totalInvSum),2) - round(floatval($payment->Payment_Amount),2)),2);
                }

                // check related APs
                foreach ($invoicesToDB as $invoiceToDB) {
                    $paymentAPs = Payments::getPaymentInvoices($payment, $invoiceToDB['Invoice_Number']);
                    if (!$paymentAPs) {
                        $invalidInvoices = 'The invoice number entered is not in the AP system. This check might not bind to the AP intended.';
                        $invalidInvoicesTopMess = 'The invoice Number "' . $invoiceToDB['Invoice_Number'] . '" you have entered
                                                   cannot be found in the AP that has been
                                                   previously entered. Please verify the detail on the payment.';
                        break;
                    //} else if ($paymentAP->Invoice_Amount != $invoiceToDB['Invoice_Amount']) {
                    } else if (!Payments::checkInvoicesAmount($invoiceToDB['Invoice_Amount'],$paymentAPs)) {

                        $invalidInvoices = 'Invalid Invoice Amount';
                        $invalidInvoicesTopMess = 'The Amount for invoice Number "' . $invoiceToDB['Invoice_Number'] . '" you have entered
                                                   does not correspond to amount of founded AP that has been
                                                   previously entered. Please verify the detail on the payment.';
                        break;
                    }
                }

                $amount_validate = true;
                    if($payment->Payment_Amount == '') {
                        $amount_validate = false;
                    }
                    if($payment->Payment_Amount == 0 && !$void) {
                        $amount_validate = false;
                    }
                    if($payment->Payment_Amount < 0 ) {
                        $amount_validate = false;
                    }


                if ($is_valid && $payment->Payment_Check_Number != '' && $payment->Payment_Check_Number != '0' &&
                    $amount_validate && $payment->Payment_Check_Date != ''
                    && round(floatval($payment->Payment_Amount),2) == round(floatval($totalInvSum),2)
                    && $payment->Vendor_ID > 0 && $invalidInvoices=='') {

                    // begin transaction
                    $transaction = Yii::app()->db->beginTransaction();
                    try {
                        $payment->save();

                        // write invoises to DB
                        foreach ($invoicesToDB as $invoiceToDB) {
                            $paymentInvoice = new PaymentsInvoice();
                            $paymentInvoice->Payment_ID = $payment->Payment_ID;
                            $paymentInvoice->Check_Invoice_Number = $invoiceToDB['Invoice_Number'];
                            $paymentInvoice->Check_Invoice_Amount = $invoiceToDB['Invoice_Amount'];
                            $paymentInvoice->save();

                            // add Payment-AP relationship
                            Payments::addAPPaymentRelationship($payment, $paymentInvoice);
                        }

                        LibraryDocs::addDocumentToFolder($payment->Document_ID);
                        LibraryDocs::addDocumentToBinder($payment->Document_ID);

                        $saved = true;

                        $transaction->commit();
                    } catch(Exception $e) {
                        $transaction->rollback();
                    }

                }
            }
        }

        // get payments to enter data
        $payments = Payments::model()->findPaymentsToEntry();
        $num_pages = count($payments);
        $page = intval($page);

        if (count($payments) > 0) {
            if ($page <= 0) {
                $page = 1;
            } else if ($page > $num_pages) {
                $page = $num_pages;
            }
        }

        if (count($payments) > 0 && ((isset($_POST['payment_data_entry_form_values']) && $saved) || !isset($_POST['payment_data_entry_form_values']))) {
            // get payment to view
            $payment = $payments[$page - 1];

            $document = Documents::model()->findByPk($payment->Document_ID);

            // get last document's file
            $condition = new CDbCriteria();
            $condition->select = 'Mime_Type,File_Name';
            $condition->condition = "Document_ID='" . $document->Document_ID . "'";
            $file = Images::model()->find($condition);

            if ($payment->Vendor_ID > 0) {
                $vendor = Vendors::model()->with('client')->findByPk($payment->Vendor_ID);
                $client = $vendor->client;
                $company = $client->company;
            } else {
                $client = false;
                $company = false;
                $vendor = false;
            }


           /* for($i = 1; $i <= 6; $i++) {
                $invoices[$i] = array(
                    'Invoice_Number' => '',
                    'Invoice_Amount' => '',
                );
            }*/

            $dists=PaymentsInvoice::getInvoicesDist($payment->Payment_ID);
            $invoices=$dists['dists'];

        }


        $vendorsCP = array();
        $acctNumbs = array();
        if ($document) {
            //get vendors Shortcut
            $vendorsCP = Vendors::getClientVendorsShortcutList($document->Client_ID);

            // get client's account numbers
            $acctNumbs = Payments::getClientAccountNumbers($document->Client_ID, $document->Project_ID);
        }


        $this->render('payments_entry', array(
            'page' => $page,
            'num_pages' => $num_pages,
            'company' => $company,
            'client' => $client,
            'document' => $document,
            'payment' => $payment,
            'file' => $file,
            'vendorsCP' => $vendorsCP,
            'vendor' => $vendor,
            'invoices' => $invoices,
            'dists_empty' => $dists['empty'],
            'invalidInvoices' => $invalidInvoices,
            'acctNumbs' => $acctNumbs,
            'invalidInvoicesTopMess' => $invalidInvoicesTopMess,
        ));
    }

    /**
     * PC data entry page action
     * @param int $page
     */
    public function actionPC($page = 1)
    {
        //initialize variables
        $document = '';
        $pc = '';
        $file = '';
        $saved = false;

        //reset search option to default
        if (!isset($_SESSION['last_pc_to_entry_search'])) {
            Pcs::resetDataentrySearchOptions();
        }
        // set query params
        if (isset($_POST['search_field'])) {
            Pcs::initDataentrySearchOptions($_POST);
        }


        // form processing
        if (isset($_POST['pc_data_entry_form_values'])) {
            $pcId = intval($_POST['Pcs']['PC_ID']);
            $pc = Pcs::model()->findByPk($pcId);

            //convert date string to server format
            $_POST['Pcs']['Envelope_Date'] = Helper::checkDate($_POST['Pcs']['Envelope_Date']);

            // if PC exists
            if ($pc) {
                $document = Documents::model()->findByPk($pc->Document_ID);

                // get last document's file
                $condition = new CDbCriteria();
                $condition->select = 'Mime_Type,File_Name';
                $condition->condition = "Document_ID='" . $document->Document_ID . "'";
                $file = Images::model()->find($condition);

                $pc->attributes = $_POST['Pcs'];

                // validate form
                $is_valid = $pc->validate();
                if ($pc->Envelope_Number == '' || $pc->Envelope_Number == '0') {
                    $pc->addError('Envelope_Number','Number is required!');
                }

                if ($pc->Employee_Name == '' || $pc->Employee_Name == '0') {
                    $pc->addError('Employee_Name','Employee Name is required!');
                }

                if ($pc->Envelope_Total == '' || $pc->Envelope_Total <= 0) {
                    $pc->addError('Envelope_Total','Total is required!');
                }

                if ($pc->Envelope_Date == '') {
                    $pc->addError('Envelope_Date','Date is required!');
                }

                if ($is_valid && $pc->Envelope_Number != '' && $pc->Envelope_Number != '0' &&
                    $pc->Envelope_Total != '' && $pc->Envelope_Total > 0 && $pc->Envelope_Date != '' && $pc->Employee_Name != '') {

                    // begin transaction
                    $transaction = Yii::app()->db->beginTransaction();
                    try {
                        $pc->save();
                        LibraryDocs::addDocumentToFolder($document->Document_ID);

                        $transaction->commit();
                    } catch(Exception $e) {
                        $transaction->rollback();
                    }


                    $saved = true;
                }
            }
        }

        // get PCs to enter data
        $pcs = Pcs::model()->findPCsToEntry();
        $num_pages = count($pcs);
        $page = intval($page);

        if (count($pcs) > 0) {
            if ($page <= 0) {
                $page = 1;
            } else if ($page > $num_pages) {
                $page = $num_pages;
            }
        }

        if (count($pcs) > 0 && ((isset($_POST['pc_data_entry_form_values']) && $saved) || !isset($_POST['pc_data_entry_form_values']))) {
            // get PC to view
            $pc = $pcs[$page - 1];

            $document = Documents::model()->findByPk($pc->Document_ID);

            // get last document's file
            $condition = new CDbCriteria();
            $condition->select = 'Mime_Type,File_Name';
            $condition->condition = "Document_ID='" . $document->Document_ID . "'";
            $file = Images::model()->find($condition);
        }

        $pcFolderNames = Pcs::getLibraryFolderNames($document->Project_ID);

        $this->render('pcs_entry', array(
            'page' => $page,
            'num_pages' => $num_pages,
            'document' => $document,
            'pc' => $pc,
            'file' => $file,
            'pcFolderNames' => $pcFolderNames,
        ));
    }

    /**
     * JE data entry page action
     * @param int $page
     */
    public function actionJE($page = 1)
    {
        //initialize variables
        $document = '';
        $je = '';
        $file = '';
        $saved = false;

        //reset search option to default
        if (!isset($_SESSION['last_je_to_entry_search'])) {
            Journals::resetDataentrySearchOptions();
        }
        // set query params
        if (isset($_POST['search_field'])) {
            Journals::initDataentrySearchOptions($_POST);
        }



        // form processing
        if (isset($_POST['je_data_entry_form_values'])) {
            $jeId = intval($_POST['Journals']['JE_ID']);
            $je = Journals::model()->findByPk($jeId);

            //convert date string to server format
            $_POST['Journals']['JE_Date'] = Helper::checkDate($_POST['Journals']['JE_Date']);

            // if JE exists
            if ($je) {
                $document = Documents::model()->findByPk($je->Document_ID);

                // get last document's file
                $condition = new CDbCriteria();
                $condition->select = 'Mime_Type,File_Name';
                $condition->condition = "Document_ID='" . $document->Document_ID . "'";
                $file = Images::model()->find($condition);

                $je->attributes = $_POST['Journals'];

                // validate form
                $is_valid = $je->validate();
                if ($je->JE_Number == '' || $je->JE_Number == '0') {
                    $je->addError('JE_Number','Number is required!');
                }

                if ($je->JE_Date == '') {
                    $je->addError('JE_Date','Date is required!');
                }

                if ($is_valid && $je->JE_Number != '' && $je->JE_Number != '0' && $je->JE_Date != '') {
                    // begin transaction
                    $transaction = Yii::app()->db->beginTransaction();
                    try {
                        $je->save();
                        LibraryDocs::addDocumentToFolder($document->Document_ID);

                        $transaction->commit();
                    } catch(Exception $e) {
                        $transaction->rollback();
                    }

                    $saved = true;
                }
            }
        }

        // get JEs to enter data
        $jes = Journals::model()->findJEsToEntry();
        $num_pages = count($jes);
        $page = intval($page);

        if (count($jes) > 0) {
            if ($page <= 0) {
                $page = 1;
            } else if ($page > $num_pages) {
                $page = $num_pages;
            }
        }

        if (count($jes) > 0 && ((isset($_POST['je_data_entry_form_values']) && $saved) || !isset($_POST['je_data_entry_form_values']))) {
            // get JE to view
            $je = $jes[$page - 1];

            $document = Documents::model()->findByPk($je->Document_ID);

            // get last document's file
            $condition = new CDbCriteria();
            $condition->select = 'Mime_Type,File_Name';
            $condition->condition = "Document_ID='" . $document->Document_ID . "'";
            $file = Images::model()->find($condition);
        }

        $this->render('jes_entry', array(
            'page' => $page,
            'num_pages' => $num_pages,
            'document' => $document,
            'je' => $je,
            'file' => $file,
        ));
    }

    /**
     * AR data entry page action
     * @param int $page
     */
    public function actionAR($page = 1)
    {
        //initialize variables
        $document = '';
        $ar = '';
        $file = '';
        $saved = false;

        //reset search option to default
        if (!isset($_SESSION['last_ar_to_entry_search'])) {
            Ars::resetDataentrySearchOptions();
        }
        // set query params
        if (isset($_POST['search_field'])) {
            Ars::initDataentrySearchOptions($_POST);
        }

        // form processing
        if (isset($_POST['ar_data_entry_form_values'])) {
            $arId = intval($_POST['Ars']['AR_ID']);
            $ar = Ars::model()->findByPk($arId);

            //convert date string to server format
            $_POST['Ars']['Invoice_Date'] = Helper::checkDate($_POST['Ars']['Invoice_Date']);

            // if AR exists
            if ($ar) {
                $document = Documents::model()->findByPk($ar->Document_ID);

                // get last document's file
                $condition = new CDbCriteria();
                $condition->select = 'Mime_Type,File_Name';
                $condition->condition = "Document_ID='" . $document->Document_ID . "'";
                $file = Images::model()->find($condition);

                $ar->attributes = $_POST['Ars'];

                // validate form
                $is_valid = $ar->validate();

                if ($ar->Company_Name == '') {
                    $ar->addError('Company_Name','Company Name is required!');
                }

                if ($ar->Invoice_Number == '' || $ar->Invoice_Number == '0') {
                    $ar->addError('Invoice_Number','Number is required!');
                }

                if ($ar->Invoice_Amount == '' || $ar->Invoice_Amount <= 0) {
                    $ar->addError('Invoice_Amount','Amount is required!');
                }

                if ($ar->Invoice_Date == '') {
                    $ar->addError('Invoice_Date','Invoice Date is required!');
                }

                /*
                if ($ar->Customer_ID == '0') {
                    $ar->addError('Customer_ID','Choose Customer!');
                }
                */

                if ($is_valid && $ar->Company_Name != '' &&
                    $ar->Invoice_Number != '' && $ar->Invoice_Number != '0' && //$ar->Customer_ID > 0 &&
                    $ar->Invoice_Amount != '' && $ar->Invoice_Amount > 0 && $ar->Invoice_Date != '') {
                    // begin transaction
                    $transaction = Yii::app()->db->beginTransaction();
                    try {
                        $ar->save();
                        LibraryDocs::addDocumentToFolder($document->Document_ID);

                        $transaction->commit();
                    } catch(Exception $e) {
                        $transaction->rollback();
                    }

                    $saved = true;
                }
            }
        }

        // get ARs to enter data
        $ars = Ars::model()->findARsToEntry();
        $num_pages = count($ars);
        $page = intval($page);

        if (count($ars) > 0) {
            if ($page <= 0) {
                $page = 1;
            } else if ($page > $num_pages) {
                $page = $num_pages;
            }
        }

        if (count($ars) > 0 && ((isset($_POST['ar_data_entry_form_values']) && $saved) || !isset($_POST['ar_data_entry_form_values']))) {
            // get payment to view
            $ar = $ars[$page - 1];

            $document = Documents::model()->findByPk($ar->Document_ID);

            // get last document's file
            $condition = new CDbCriteria();
            $condition->select = 'Mime_Type,File_Name';
            $condition->condition = "Document_ID='" . $document->Document_ID . "'";
            $file = Images::model()->find($condition);
        }

        $customers = array();
        if ($document) {
            //get customers
            $customers = Customers::getClientCustomersList($document->Client_ID);
        }

        $compNames = Ars::getPreviousCompanyNamesForClient($document->Client_ID);
        $terms = Ars::getPreviousTermsForClient($document->Client_ID);

        $this->render('ars_entry', array(
            'page' => $page,
            'num_pages' => $num_pages,
            'document' => $document,
            'ar' => $ar,
            'file' => $file,
            'customers' => $customers,
            'compNames' => $compNames,
            'terms' => $terms,
        ));
    }

    /**
     * Payroll data entry page action
     * @param int $page
     */
    public function actionPayroll($page = 1)
    {
        //initialize variables
        $document = '';
        $payroll = '';
        $file = '';
        $saved = false;

        //reset search option to default
        if (!isset($_SESSION['last_payr_to_entry_search'])) {
            Payrolls::resetDataentrySearchOptions();
        }
        // set query params
        if (isset($_POST['search_field'])) {
            Payrolls::initDataentrySearchOptions($_POST);
        }

        // form processing
        if (isset($_POST['payroll_data_entry_form_values'])) {
            $payrollId = intval($_POST['Payrolls']['Payroll_ID']);
            $payroll = Payrolls::model()->findByPk($payrollId);

            //convert date string to server format
            $_POST['Payrolls']['Week_Ending'] = Helper::checkDate($_POST['Payrolls']['Week_Ending']);


            // if Payroll exists
            if ($payroll) {
                $document = Documents::model()->findByPk($payroll->Document_ID);

                // get last document's file
                $condition = new CDbCriteria();
                $condition->select = 'Mime_Type,File_Name';
                $condition->condition = "Document_ID='" . $document->Document_ID . "'";
                $file = Images::model()->find($condition);

                $payroll->attributes = $_POST['Payrolls'];

                // validate form
                $is_valid = $payroll->validate();

                if ($payroll->Payroll_Type_ID == 0) {
                    $payroll->addError('Payroll_Type_ID','Choose Payroll Type!');
                }

                if ($payroll->Week_Ending == '') {
                    $payroll->addError('Week_Ending','Week Ending is required!');
                }

                if ($is_valid && $payroll->Payroll_Type_ID != '0' && $payroll->Week_Ending != '') {
                    // begin transaction
                    $transaction = Yii::app()->db->beginTransaction();
                    try {
                        $payroll->save();
                        LibraryDocs::addDocumentToFolder($document->Document_ID);

                        $transaction->commit();
                    } catch(Exception $e) {
                        $transaction->rollback();
                    }

                    $saved = true;
                }
            }
        }

        // get Payrolls to enter data
        $payrolls = Payrolls::model()->findPayrollsToEntry();
        $num_pages = count($payrolls);
        $page = intval($page);

        if (count($payrolls) > 0) {
            if ($page <= 0) {
                $page = 1;
            } else if ($page > $num_pages) {
                $page = $num_pages;
            }
        }

        if (count($payrolls) > 0 && ((isset($_POST['payroll_data_entry_form_values']) && $saved) || !isset($_POST['payroll_data_entry_form_values']))) {
            // get Payroll to view
            $payroll = $payrolls[$page - 1];

            $document = Documents::model()->findByPk($payroll->Document_ID);

            // get last document's file
            $condition = new CDbCriteria();
            $condition->select = 'Mime_Type,File_Name';
            $condition->condition = "Document_ID='" . $document->Document_ID . "'";
            $file = Images::model()->find($condition);
        }

        $payrollTypes = PayrollTypes::getPayrollTypesList();

        $this->render('payrolls_entry', array(
            'page' => $page,
            'num_pages' => $num_pages,
            'document' => $document,
            'payroll' => $payroll,
            'file' => $file,
            'payrollTypes' => $payrollTypes,
        ));
    }

    /**
     * AP data entry page action
     * @param int $page
     */
    public function actionAP($page = 1)
    {
        //initialize variables
        $company = '';
        $vendor = '';
        $client = '';
        $document = '';
        $ap = '';
        $file = '';
        $saved = false;
        $poNum = '';
        $poError = '';
        $dists_enabled=strval($_POST['dists_enabled']);

        //reset search option to default
        if (!isset($_SESSION['last_ap_to_entry_search'])) {
            Aps::resetDataentrySearchOptions();
        }
        // set query params
        if (isset($_POST['search_field'])) {
            Aps::initDataentrySearchOptions($_POST);
        }


        // form processing
        if (isset($_POST['ap_data_entry_form_values'])) {
            $apId = intval($_POST['Aps']['AP_ID']);
            $ap = Aps::model()->findByPk($apId);
            $dists = array();
            if($dists_enabled=='on') $dists = $_POST['Dist'];
            $distsToDB = array();
            $totalDistSum = 0;
            $invalidDistsSum = '';

            // get valid invoices
            foreach ($dists as $key => $dist) {
                $distAcctNum = $dist['GL_Dist_Detail_COA_Acct_Number'];
                $distDesc = $dist['GL_Dist_Detail_Desc'];
                $distAmount = $dist['GL_Dist_Detail_Amt'];

                if (!is_numeric($distAmount) || $distAmount == '') {
                    $distAmount = '';
                }

                if (($distAmount != '' || $distAcctNum != '' || $distDesc != '') && ($distAmount == '' || $distAcctNum == '' ||  $distDesc == '')) {
                    $invalidDistsSum = 'All fields in populated rows are required';
                } else if ($distAmount != '' && $distAcctNum != '' && $distDesc != '') {
                    $distsToDB[] = $dist;
                    $totalDistSum += $distAmount;
                }
            }

            // if ap exists
            if ($ap) {
                //convert date string to server format
                $_POST['Aps']['Invoice_Date'] = Helper::checkDate($_POST['Aps']['Invoice_Date']);
                $_POST['Aps']['Invoice_Due_Date'] = Helper::checkDate($_POST['Aps']['Invoice_Due_Date']);

                $document = Documents::model()->findByPk($ap->Document_ID);
                // get last document's file
                $condition = new CDbCriteria();
                $condition->select = 'Mime_Type';
                $condition->condition = "Document_ID='" . $document->Document_ID . "'";
                $file = Images::model()->find($condition);

                $ap->attributes = $_POST['Aps'];

                if ($ap->Vendor_ID > 0) {
                    $vendor = Vendors::model()->with('client')->findByPk($ap->Vendor_ID);
                    $client = $vendor->client;
                    $company = $client->company;
                } else {
                    $client = false;
                    $company = false;
                    $vendor = false;
                }

                $poNum = $_POST['PO_Number'];
                if ($poNum != '' && $ap->Vendor_ID != '0') {
                    // get po
                    $condition = new CDbCriteria();
                    $condition->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID";
                    $condition->condition = "t.PO_Number = '$poNum'";
                    $condition->addCondition("t.Vendor_ID = '" . $ap->Vendor_ID . "'");
                    $condition->addCondition("documents.Project_ID = '" . $document->Project_ID . "'");
                    $po = Pos::model()->find($condition);

                    if (!$po) {
                        $poError = 'PO does not exist';
                    } else {
                        $outBalance = Pos::checkPOBalance($po->PO_ID, $ap->Invoice_Amount);
                        if ($outBalance > 0) {
                            $poError = 'This AP can not be attached to PO. Balance will be out of by $' . number_format($outBalance, 2);
                        }
                    }
                }


                // validate form
                $is_valid = $ap->validate();
                if ($ap->Invoice_Number == '' || $ap->Invoice_Number == '0') {
                    $ap->addError('Invoice_Number','Inv. Number is required!');
                }

                if ($ap->Invoice_Amount == '') {
                    $ap->addError('Invoice_Amount','Inv. Amount is required!');
                }

                if ($ap->Invoice_Date == '') {
                    $ap->addError('Invoice_Date','Inv. Date is required!');
                }

                if ($ap->Invoice_Reference == '') {
                    $ap->addError('Invoice_Reference','Description is required!');
                }

                /*
                if ($ap->Detail_1099_Box_Number == '0') {
                    $ap->addError('Detail_1099_Box_Number','Choose 1099 Type!');
                }
                */

                if ($ap->Vendor_ID == '0') {
                    $ap->addError('Vendor_ID','Choose Vendor!');
                }

                if($dists_enabled=='on') {
                    if (round(floatval($ap->Invoice_Amount),2) != round(floatval($totalDistSum),2) && $invalidDistsSum == '') {
                        $invalidDistsSum = "The detail of GL Dists is not in balance with the Inv. Amount.
                                               You're out by $" . round(abs(round(floatval($totalDistSum),2) - round(floatval($ap->Invoice_Amount),2)),2);
                    }
                }

                if($dists_enabled) {
                    if( $invalidDistsSum == '' && count($distsToDB) == 0 || (round(floatval($ap->Invoice_Amount),2) == round(floatval($totalDistSum),2) && count($distsToDB) > 0)) {
                        $dists_condition=true;
                    } else {
                        $dists_condition=false;
                    }
                } else {$dists_condition=true;}

                if ($is_valid && $ap->Invoice_Number != '' && $ap->Invoice_Amount != '' && $ap->Invoice_Date != '' && $ap->Invoice_Reference != ''
                    && $ap->Vendor_ID != '0' && $ap->Invoice_Number != '0' && $poError == '' &&  $dists_condition) {

                    $approvalRange = Aps::getUserApprovalRange();

                    // check approval range and set approval values
                    if ($approvalRange['user_appr_val'] == Aps::APPROVED) {
                        $ap->AP_Approval_Value = $approvalRange['user_appr_val'];
                        $ap->Previous_AP_A_Val = $approvalRange['prev_user_appr_val'];
                        $ap->Approved = 1;

                    } else if ($approvalRange['user_appr_val'] > Aps::READY_FOR_APPROVAL) {
                        $ap->AP_Approval_Value = $approvalRange['user_appr_val'];
                        $ap->Previous_AP_A_Val = $approvalRange['prev_user_appr_val'];
                    } else {
                        $ap->AP_Approval_Value = Aps::READY_FOR_APPROVAL;
                        $ap->Previous_AP_A_Val = Aps::READY_FOR_APPROVAL;
                    }
                    /*$ap->AP_Approval_Value = Aps::READY_FOR_APPROVAL;
                    $ap->Previous_AP_A_Val = Aps::READY_FOR_APPROVAL;*/

                    if ($ap->Invoice_Due_Date == '') {
                        $ap->Invoice_Due_Date = null;
                    }

                    // begin transaction
                    $transaction = Yii::app()->db->beginTransaction();
                    try {
                        $ap->save();
                        if ($ap->Approved == 1) {
                            LibraryDocs::addDocumentToFolder($ap->Document_ID);
                        }

                        if ($poNum != '') {
                            Pos::addApPORelation($po, $ap->Invoice_Amount, $ap->Invoice_Date, $ap->Invoice_Number, $ap->Invoice_Reference);
                            $poNum = '';
                        }
                        // write dists to DB
                        GlDistDetails::saveAPDists($ap->AP_ID, $distsToDB);
                        $saved = true;

                        $transaction->commit();
                        Audits::LogAction($ap->Document_ID ,Audits::ACTION_SAVE);

                    } catch(Exception $e) {
                        $transaction->rollback();
                    }


                    Yii::app()->user->setFlash('success', "Changes Saved!");

                    //get first user to approve document
                    $condition = new CDbCriteria();
                    $condition->select = 'User_ID';
                    $condition->condition = "t.Client_ID='" . $document->Client_ID . "'";
                    $condition->addCondition("t.User_Approval_Value > '1'");
                    $condition->order = "t.User_Approval_Value ASC";
                    $firstUser = UsersClientList::model()->find($condition);
                    if ($firstUser) {
                        $nextUserId = $firstUser->User_ID;
                        $user = Users::model()->with('settings','person')->findByPk($nextUserId);
                        // send notification

                        $project = Projects::model()->findByPk(Yii::app()->user->projectID);

                        $client = Clients::model()->findByPk(Yii::app()->user->clientID);
                        $clientsToApprove = array($client->company->Company_Name.' - '.$project->Project_Name.'');

                        Mail::sendPendingApprovalDocumentsNotification(!$user->settings->Notification,$user, $clientsToApprove, Documents::PO,$client,$project);

                    }
                }
            }
        }

        // get ap to enter data
        $aps = Aps::model()->findAPToEntry();
        $num_pages = count($aps);
        $page = intval($page);

        if (count($aps) > 0) {
            if ($page <= 0) {
                $page = 1;
            } else if ($page > $num_pages) {
                $page = $num_pages;
            }
        }

        if (count($aps) > 0 && ((isset($_POST['ap_data_entry_form_values']) && $saved) || !isset($_POST['ap_data_entry_form_values']))) {


            // get ap to view
            $ap = $aps[$page - 1];

            $document = Documents::model()->findByPk($ap->Document_ID);

            // get last document's file
            $condition = new CDbCriteria();
            $condition->select = 'Mime_Type,File_Name';
            $condition->condition = "Document_ID='" . $document->Document_ID . "'";
            $file = Images::model()->find($condition);

            //get users settings for Due Date Terms assigning
            $user = Users::model()->with('settings')->findByPk(Yii::app()->user->userID);
            $due_date_term = $user->settings->Due_Date_Terms;



            if ($ap->Vendor_ID > 0) {
                $vendor = Vendors::model()->with('client')->findByPk($ap->Vendor_ID);
                $client = $vendor->client;
                $company = $client->company;
            } else {
                $client = false;
                $company = false;
                $vendor = false;
            }

             $dists= GlDistDetails::getAPDists($ap->AP_ID);


        }

        //if correct dists were inputed but not saved to db due to other po error -we need to return them back to user's form
        if (count($distsToDB)>0) {
            $dists = array(
                'empty'=>false,
                'dists'=>$distsToDB
            );
        } else {
            $dists= GlDistDetails::getAPDists($ap->AP_ID);
        }


        $vendorsCP = array();
        if ($document) {
            //get vendors Shortcut
            $vendorsCP = Vendors::getClientVendorsShortcutList($document->Client_ID);
        }

        $coaStructure = CoaStructure::model()->findByAttributes(array(
            'Project_ID' => Yii::app()->user->projectID,
        ));

        $this->render('ap_entry', array(
            'page' => $page,
            'num_pages' => $num_pages,
            'company' => $company,
            'client' => $client,
            'document' => $document,
            'ap' => $ap,
            'file' => $file,
            'vendorsCP' => $vendorsCP,
            'vendor' => $vendor,
            'poNum' => $poNum,
            'poError' => $poError,
            'dists' => $dists['dists'],
            'dists_empty' => $dists['empty'],
            'dists_enabled'=>$dists_enabled,
            'invalidDistsSum' => $invalidDistsSum,
            'coaStructure'=>$coaStructure,
            'due_date_term'=> $due_date_term
        ));
    }

    /**
     * PO data entry page action
     * @param int $page
     */
    public function actionPO($page = 1)
    {
        //initialize variables
        $company = '';
        $vendor = '';
        $client = '';
        $document = '';
        $po = '';
        $file = '';
        $saved = false;
        $dists_enabled=strval($_POST['dists_enabled']);

        //reset search option to default
        if (!isset($_SESSION['last_po_to_entry_search'])) {
            Pos::resetDataentrySearchOptions();
        }
        // set query params
        if (isset($_POST['search_field'])) {
            Pos::initDataentrySearchOptions($_POST);
        }


        // form processing
        if (isset($_POST['po_data_entry_form_values'])) {
            $poId = intval($_POST['Pos']['PO_ID']);
            $po = Pos::model()->findByPk($poId);
            $dists=array();
            if($dists_enabled=='on') $dists = $_POST['Dist'];
            $distsToDB = array();
            $totalDistSum = 0;
            $invalidDistsSum = '';


            // get valid invoices
            foreach ($dists as $key => $dist) {
                $distAcctNum = $dist['PO_Dists_GL_Code'];
                $distDesc = $dist['PO_Dists_Description'];
                $distAmount = $dist['PO_Dists_Amount'];

                if (!is_numeric($distAmount) || $distAmount == '') {
                    $distAmount = '';
                }

                if ( !Coa::checkCoaNumber( Yii::app()->user->projectID,$distAcctNum )) {
                    $invalidDistsSum = $distAcctNum . '- too long number or bad format';
                }

                if (($distAmount != '' || $distAcctNum != '' || $distDesc != '') && ($distAmount == '' || $distAcctNum == '' ||  $distDesc == '')) {
                    $invalidDistsSum .= ' All fields in populated rows are required';
                } else if ($distAmount != '' && $distAcctNum != '' && $distDesc != '' && Coa::checkCoaNumber( Yii::app()->user->projectID,$distAcctNum )) {
                    $distsToDB[] = $dist;
                    $totalDistSum += $distAmount;
                }
            }

            // if po exists
            if ($po) {
                //convert date string to server format
                $_POST['Pos']['PO_Date'] = Helper::checkDate($_POST['Pos']['PO_Date']);

                $document = Documents::model()->findByPk($po->Document_ID);
                // get last document's file
                $condition = new CDbCriteria();
                $condition->select = 'Mime_Type,File_Name';
                $condition->condition = "Document_ID='" . $document->Document_ID . "'";
                $file = Images::model()->find($condition);

                $po->attributes = $_POST['Pos'];

                if ($po->Vendor_ID > 0) {
                    $vendor = Vendors::model()->with('client')->findByPk($po->Vendor_ID);
                    $client = $vendor->client;
                    $company = $client->company;
                } else {
                    $client = false;
                    $company = false;
                    $vendor = false;
                }

                // validate form
                $is_valid = $po->validate();
                if ($po->PO_Account_Number == '' || $po->PO_Account_Number == '0') {
                    //$po->addError('PO_Account_Number','Account Number is required!');
                }

                if ($po->PO_Subtotal == '' || $po->PO_Subtotal == '0') {
                    $po->addError('PO_Subtotal','Subtotal is required!');
                }

                if ($po->PO_Total == '' || $po->PO_Total == '0') {
                    $po->addError('PO_Total','Total is required!');
                }

                if ($po->PO_Date == '') {
                    $po->addError('PO_Date','Date is required!');
                }

                if ($po->Payment_Type == '0') {
                    $po->addError('Payment_Type','Choose Payment Type!');
                }


                if ($po->Payment_Type == 'CC' && $po->PO_Card_Last_4_Digits == '') {
                    $po->addError('PO_Card_Last_4_Digits','Last 4 Digits are required!');
                }

                if ($po->Payment_Type == 'CC' && !preg_match('/^\d{4}$/', $po->PO_Card_Last_4_Digits)) {
                    $po->addError('PO_Card_Last_4_Digits','Invalid Last 4 Digits!');
                }

                if ($po->Vendor_ID == '0') {
                    $po->addError('Vendor_ID','Choose Vendor!');
                }

                if($dists_enabled=='on') {
                    if (round(floatval($po->PO_Total),2) != round(floatval($totalDistSum),2) && $invalidDistsSum == '') {
                        $invalidDistsSum = "The detail of Distributions is not in balance with the PO Total
                                               You're out by $" . round(abs(round(floatval($totalDistSum),2) - round(floatval($po->PO_Total),2)),2);
                    }
                }

                if($dists_enabled=='on') {
                    if( $invalidDistsSum == '' && count($distsToDB) == 0 || (round(floatval($po->PO_Total),2) == round(floatval($totalDistSum),2) && count($distsToDB) > 0)) {
                        $dists_condition=true;
                    } else {
                        $dists_condition=false;
                    }
                } else {$dists_condition=true;}

                if ($is_valid &&  $po->PO_Subtotal != '' && $po->PO_Subtotal != '0'
                    && $po->PO_Total != '' && $po->PO_Total != '0'  && $po->Vendor_ID != '0' && $po->PO_Date != '' && $po->Payment_Type != '0'
                    && ($po->Payment_Type != 'CC' || preg_match('/^\d{4}$/', $po->PO_Card_Last_4_Digits)) && $invalidDistsSum == '' && $dists_condition ) {

                    $approvalRange = Aps::getUserApprovalRange();

                    if ($approvalRange['user_appr_val'] == Aps::APPROVED) {
                        $po->PO_Approval_Value = $approvalRange['user_appr_val'];
                        $po->PO_Previous_PO_Val = $approvalRange['prev_user_appr_val'];
                        $po->PO_Approved = 1;
                        LibraryDocs::addDocumentToFolder($po->Document_ID);
                    } else if ($approvalRange['user_appr_val'] > Aps::READY_FOR_APPROVAL) {
                        $po->PO_Approval_Value = $approvalRange['user_appr_val'];
                        $po->PO_Previous_PO_Val = $approvalRange['prev_user_appr_val'];
                    } else {
                        $po->PO_Approval_Value = Aps::READY_FOR_APPROVAL;
                        $po->PO_Previous_PO_Val = Aps::READY_FOR_APPROVAL;
                    }


                    /*$po->PO_Approval_Value = Pos::READY_FOR_APPROVAL;
                    $po->PO_Previous_PO_Val = Pos::READY_FOR_APPROVAL;*/

                    //How to return VOIDED PO in approval cycle? To set Approved value to 0?
                    //$po->PO_Approved = Pos::NOT_READY_FOR_APPROVAL;

                    // begin transaction
                    $transaction = Yii::app()->db->beginTransaction();
                    try {
                        $po->save();

                        // write invoices to DB
                        $error_array = PoDists::savePODists($po->PO_ID, $distsToDB);
                        if (count($error_array) == 0) {
                            $saved = true;

                            $transaction->commit();
                            Audits::LogAction($po->Document_ID ,Audits::ACTION_SAVE);
                        } else $transaction->rollback();

                    } catch(Exception $e) {
                        $transaction->rollback();
                    }


                    Yii::app()->user->setFlash('success', "Changes Saved!");

                    //get first user to approve document
                    $condition = new CDbCriteria();
                    $condition->select = 'User_ID';
                    $condition->condition = "t.Client_ID='" . $document->Client_ID . "'";
                    $condition->addCondition("t.User_Approval_Value > '1'");
                    $condition->order = "t.User_Approval_Value ASC";
                    $firstUser = UsersClientList::model()->find($condition);
                    if ($firstUser) {
                        $nextUserId = $firstUser->User_ID;
                        $user = Users::model()->with('settings','person')->findByPk($nextUserId);
                        // send notification
                        $project = Projects::model()->findByPk(Yii::app()->user->projectID);

                        $client = Clients::model()->findByPk(Yii::app()->user->clientID);
                        $clientsToApprove = array($client->company->Company_Name.' - '.$project->Project_Name.'');

                        Mail::sendPendingApprovalDocumentsNotification(!$user->settings->Notification,$user, $clientsToApprove, Documents::PO,$client,$project);
                    }
                }
            }
        }

        // get po to enter data
        $pos = Pos::model()->findPOToEntry();
        $num_pages = count($pos);
        $page = intval($page);

        if (count($pos) > 0) {
            if ($page <= 0) {
                $page = 1;
            } else if ($page > $num_pages) {
                $page = $num_pages;
            }
        }

        if (count($pos) > 0 && ((isset($_POST['po_data_entry_form_values']) && $saved) || !isset($_POST['po_data_entry_form_values']))) {
            // get ap to view
            $po = $pos[$page - 1];

            $document = Documents::model()->findByPk($po->Document_ID);

            // get last document's file
            $condition = new CDbCriteria();
            $condition->select = 'Mime_Type,File_Name';
            $condition->condition = "Document_ID='" . $document->Document_ID . "'";
            $file = Images::model()->find($condition);

            if ($po->Vendor_ID > 0) {
                $vendor = Vendors::model()->with('client')->findByPk($po->Vendor_ID);
                $client = $vendor->client;
                $company = $client->company;
            } else {
                $client = false;
                $company = false;
                $vendor = false;
            }





        }

        //if correct dists were inputed but not saved to db due to other po error -we need to return them back to user's form
        if (count($distsToDB)>0) {
            $dists = array(
                'empty'=>false,
                'dists'=>$distsToDB
            );
        } else {
            $dists=PoDists::model()->getPODists($po->PO_ID);
        }


        $vendorsCP = array();
        if ($document) {
            //get vendors Shortcut
            $vendorsCP = Vendors::getClientVendorsShortcutList($document->Client_ID);
        }
        $coaStructure = CoaStructure::model()->findByAttributes(array(
            'Project_ID' => Yii::app()->user->projectID,
        ));

        $coaStructure = CoaStructure::model()->findByAttributes(array(
            'Project_ID' => Yii::app()->user->projectID,
        ));

        $this->render('po_entry', array(
            'page' => $page,
            'num_pages' => $num_pages,
            'company' => $company,
            'client' => $client,
            'document' => $document,
            'po' => $po,
            'file' => $file,
            'vendorsCP' => $vendorsCP,
            'vendor' => $vendor,
            'dists' => $dists['dists'],
            'dists_empty' => $dists['empty'],
            'invalidDistsSum' => $invalidDistsSum,
            'coaStructure'=>$coaStructure
        ));
    }

    /**
     * W9 data entry page action
     * @param int $page
     */
    public function actionW9($page = 1)
    {
        //initialize variables

        $company = '';
        $client = '';
        $document = '';
        $w9 = '';
        $file = '';
        $address = '';
        $saved = false;
        if (!isset($_SESSION['last_w9_to_entry_search'])) {
            $_SESSION['last_w9_to_entry_search']['query'] = '';
            $_SESSION['last_w9_to_entry_search']['options'] = array(
                'search_option_com_name' => 1,
                'search_option_fed_id' => 1,
                'search_option_addr1' => 0,
                'search_option_addr2' => 0,
                'search_option_city' => 0,
                'search_option_state' => 0,
                'search_option_zip' => 0,
                'search_option_country' => 0,
                'search_option_phone' => 0,
            );
        }

        if (isset($_POST['search_field'])) {
            // set query params
            $queryString = trim($_POST['search_field']);
            $options = array(
                'search_option_com_name' => (isset($_POST['search_option_com_name']) ? 1 : 0),
                'search_option_fed_id' => (isset($_POST['search_option_fed_id']) ? 1 : 0),
                'search_option_addr1' => (isset($_POST['search_option_addr1']) ? 1 : 0),
                'search_option_addr2' => (isset($_POST['search_option_addr2']) ? 1 : 0),
                'search_option_city' => (isset($_POST['search_option_city']) ? 1 : 0),
                'search_option_state' => (isset($_POST['search_option_state']) ? 1 : 0),
                'search_option_zip' => (isset($_POST['search_option_zip']) ? 1 : 0),
                'search_option_country' => (isset($_POST['search_option_country']) ? 1 : 0),
                'search_option_phone' => (isset($_POST['search_option_phone']) ? 1 : 0),
            );

            // set last search query params to session
            $_SESSION['last_w9_to_entry_search']['query'] = $queryString;
            $_SESSION['last_w9_to_entry_search']['options'] = $options;
        }

        // form processing
        if (isset($_POST['w9_data_entry_form_values'])) {
            $mustToBeSave = false;

            $w9Id = intval($_POST['W9']['W9_ID']);
            $w9 = W9::model()->findByPk($w9Id);

            //convert date string to server format
            $_POST['W9']['Signature_Date'] = Helper::checkDate($_POST['W9']['Signature_Date']);

            // if w9 exists
            if ($w9) {
                // get client info
                $client = Clients::model()->with('company')->findByPk($w9->Client_ID);
                //get company info
                $company = $client->company;
                $address = $company->adreses[0];
                $document = Documents::model()->findByPk($w9->Document_ID);
                // get last document's file
                $condition = new CDbCriteria();
                $condition->select = 'Mime_Type,File_Name';
                $condition->condition = "Document_ID='" . $document->Document_ID . "'";
                $file = Images::model()->find($condition);

                // check user's access
                if (Yii::app()->user->userType == Users::DB_ADMIN) {
                    $mustToBeSave = true;
                } else if ((Yii::app()->user->userType == Users::ADMIN || Yii::app()->user->userType == Users::DATA_ENTRY_CLERK)
                    && $w9->Verified != 1 && $w9->W9_Data_Entry != Yii::app()->user->userType) {
                    $mustToBeSave = true;
                } else if ($w9->Verified != 1 && $w9->W9_Data_Entry != Yii::app()->user->userType && $w9->Revision_ID <= '0') {
                    $mustToBeSave = true;
                }

                // if user has access to change data
                if ($mustToBeSave) {
                    //set new values
                    $company->SSN = $_POST['Companies']['SSN'];

                    $previous_verified_value = $w9->Verified;
                    $previous_revision_value = $w9->Revision_ID;

                    $w9->attributes = $_POST['W9'];


                    if ($w9->W9_Data_Entry == 0) {
                        $w9->W9_Data_Entry = Yii::app()->user->userID;
                    }
                    $w9->Revision_ID = $_POST['W9']['Revision_ID'];

                    $address->attributes = $_POST['Addresses'];

                    // validate form
                    $is_valid_comp_info = $company->validate();
                    $is_valid_w9_info = $w9->validate();
                    $is_valid_address_info = $address->validate();

                        if ((Yii::app()->user->userType == Users::DB_ADMIN || Yii::app()->user->userType == Users::ADMIN
                            || Yii::app()->user->userType == Users::DATA_ENTRY_CLERK) && $is_valid_w9_info && $is_valid_comp_info) {
                            $w9->Verified = $_POST['W9']['Verified'];
                            if ($previous_verified_value != $_POST['W9']['Verified']) {
                                W9::logVerifiedChange($w9->W9_ID, $w9->Verified);
                            }
                        } else {
                            $w9->Verified = 0;
                        }


                    if ((Yii::app()->user->userType != Users::DB_ADMIN && Yii::app()->user->userType != Users::ADMIN && Yii::app()->user->userType != Users::DATA_ENTRY_CLERK)
                        || (Yii::app()->user->userType == Users::DB_ADMIN || Yii::app()->user->userType == Users::ADMIN
                        || Yii::app()->user->userType == Users::DATA_ENTRY_CLERK && $previous_revision_value != '0')) {
                        if ($w9->Signed == 0) {
                            $w9->addError('Signed','Document must be signed!');
                        }
                        if (!preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $w9->Signature_Date)) {
                            $w9->addError('Signature_Date','Correct date format: mm/dd/yyyy!');
                        }
                        if ($address->Address1 == '') {
                            $address->addError('Address1','Value is required!');
                        }
                        if ($address->City == '') {
                            $address->addError('City','Value is required!');
                        }
                        if ($address->State == '') {
                            $address->addError('State','Value is required!');
                        }
                        if ($address->ZIP == '') {
                            $address->addError('ZIP','Value is required!');
                        }
                        if ($w9->Tax_Class == '0') {
                            $w9->addError('Tax_Class','Choose Tax Class!');
                        }
                        if ($w9->Revision_ID == '0') {
                            $w9->addError('Revision_ID','Choose Revision!');
                        }
                    } else {
                        if (!preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $w9->Signature_Date) && $w9->Signature_Date != '') {
                            $w9->addError('Signature_Date','Correct date format: mm/dd/yyyy!');
                        }
                    }

                    if ($w9->Signature_Date == '') {
                        $w9->Signature_Date = null;
                    }

                    if ((Yii::app()->user->userType == Users::DB_ADMIN || Yii::app()->user->userType == Users::ADMIN
                        || Yii::app()->user->userType == Users::DATA_ENTRY_CLERK) && $is_valid_comp_info && $is_valid_w9_info
                        && $is_valid_address_info && (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $w9->Signature_Date) || $w9->Signature_Date == null
                        && $previous_revision_value == '0')) {
                        //if form is valid, save it
                        // begin transaction
                        $transaction = Yii::app()->db->beginTransaction();
                        try {
                            $company->save();
                            $w9->save();
                            $address->save();
                            Yii::app()->user->setFlash('success', "Changes Saved!");
                            $saved = true;
                            $transaction->commit();
                        } catch(Exception $e) {
                            $transaction->rollback();
                        }
                    } else if ($is_valid_comp_info && $is_valid_w9_info && $is_valid_address_info && $w9->Signed != 0
                        && preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $w9->Signature_Date) && $address->Address1 != ''
                        && $address->City != '' && $address->State != '' && $address->ZIP != '' && $w9->Tax_Class != '0'
                        && $w9->Revision_ID != '0') {
                            //if form is valid, save it
                            // begin transaction
                            $transaction = Yii::app()->db->beginTransaction();
                            try {
                                $company->save();
    //                            why it saves verified for user and didnt for DEC

                                $w9->save();
                                $address->save();
                                Yii::app()->user->setFlash('success', "Changes Saved!");
                                $saved = true;
                                $transaction->commit();
                            } catch(Exception $e) {
                                $transaction->rollback();
                            }
                    }
                }
            }
        }

        //get w9 revisions
        $w9Revs = array();
        $w9Revisions = W9Revisions::model()->findAll();
        foreach($w9Revisions as $revision) {
            $w9Revs[$revision->Revision_ID] = $revision->Description;
        }

        // get w9s to enter data
        $w9s = W9::model()->findW9ToEntry($_SESSION['last_w9_to_entry_search']['query'], $_SESSION['last_w9_to_entry_search']['options']);
        $num_pages = count($w9s);
        $page = intval($page);

        if (count($w9s) > 0) {
            if ($page <= 0) {
                $page = 1;
            } else if ($page > $num_pages) {
                $page = $num_pages;
            }
        }

        if (count($w9s) > 0 && ((isset($_POST['w9_data_entry_form_values']) && $saved) || !isset($_POST['w9_data_entry_form_values']))) {
            // get w9 to view
            $w9 = $w9s[$page - 1];

            // get client info
            $client = Clients::model()->with('company')->findByPk($w9->Client_ID);

            //get company info
            $company = $client->company;

            $address = $company->adreses[0];



            $document = Documents::model()->findByPk($w9->Document_ID);

            // get last document's file
            $condition = new CDbCriteria();
            $condition->select = 'Mime_Type,File_Name';
            $condition->condition = "Document_ID='" . $document->Document_ID . "'";
            $file = Images::model()->find($condition);

            // get previous company w9 document
            $condition = new CDbCriteria();
            $condition->join = "LEFT JOIN w9 ON w9.Document_ID=t.Document_ID";
            $condition->condition = "w9.Client_ID='" . $client->Client_ID . "'";
            $condition->addCondition("t.Document_Type='W9'");
            $condition->addCondition("w9.Revision_ID>'0'");
            $condition->addCondition("w9.W9_ID<'" . $w9->W9_ID . "'");
            $condition->order = "t.Created DESC";
            $lastDocument = Documents::model()->find($condition);


            if ($lastDocument && ($w9->Revision_ID == '0' || $w9->Revision_ID == '-1')) {

                $show_already_exists_link = false;

                $condition = new CDbCriteria();
                $condition->condition = "t.Client_ID='" . $client->Client_ID . "'";
                $condition->addCondition("t.Verified='1'");
                $previousW9verified = $previousW9 = W9::model()->findAll($condition);
                if ($previousW9verified) $show_already_exists_link = true;

                $previousW9 = W9::model()->findByAttributes(array(
                    'Document_ID' => $lastDocument->Document_ID,
                ));


                // set empty fields by previous values
                if ($w9->Business_Name == '') {
                    $w9->Business_Name = $previousW9->Business_Name;
                }
                if ($w9->Exempt == 0) {
                    $w9->Exempt = $previousW9->Exempt;
                }
                if ($w9->Tax_Class == '') {
                    $w9->Tax_Class = $previousW9->Tax_Class;
                }
                if ($w9->Account_Nums == '') {
                    $w9->Account_Nums = $previousW9->Account_Nums;
                }
            }
        }
        $this->render('w9_entry', array(
            'page' => $page,
            'num_pages' => $num_pages,
            'company' => $company,
            'client' => $client,
            'document' => $document,
            'w9' => $w9,
            'file' => $file,
            'address' => $address,
            'w9Revs' => $w9Revs,
            'show_already_exists_link'=>$show_already_exists_link
        ));
    }

    public function actionAssign($page = 1)
    {
        //initialize variables
        $document = '';
        $je = '';
        $file = '';
        $saved = false;

        $num_pages = count($jes);
        $page = intval($page);

        if (count($jes) > 0) {
            if ($page <= 0) {
                $page = 1;
            } else if ($page > $num_pages) {
                $page = $num_pages;
            }
        }


        $this->render('assign_entry', array(
            'page' => $page,
            'num_pages' => $num_pages,
            'document' => $document,
            'je' => $je,
            'file' => $file,
        ));
    }


    /**
     * Get Vendors List
     */
    public function actionGetVendorsList()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['query']) && isset($_POST['docId'])) {
            // set query params
            $queryString = trim($_POST['query']);
            $docId = intval($_POST['docId']);
            $document = Documents::model()->findByPk($docId);

            $condition = new CDbCriteria();
            $condition->join = "LEFT JOIN clients ON t.Vendor_Client_ID=clients.Client_ID
                                LEFT JOIN companies as com ON clients.Company_ID=com.Company_ID";

            if ($queryString != '') {
                $condition->compare('t.Vendor_ID_Shortcut', $queryString, true, 'OR');
                $condition->compare('com.Company_Name', $queryString, true, 'OR');
            }

            $condition->addCondition("t.Client_Client_ID = '" . $document->Client_ID . "'");
            $condition->order = 'com.Company_Name ASC';
            $vendors = Vendors::model()->with('client.company')->findAll($condition);

            $this->renderPartial('vendors_list', array(
                'vendors' => $vendors,
            ));
        }
    }

    /**
     * Get customers List
     */
    public function actionGetCustomersList()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['query']) && isset($_POST['docId'])) {
            // set query params
            $queryString = trim($_POST['query']);
            $docId = intval($_POST['docId']);
            $document = Documents::model()->findByPk($docId);

            $condition = new CDbCriteria();
            $condition->join = "LEFT JOIN clients ON t.Vendor_Client_ID=clients.Client_ID
                                LEFT JOIN companies as com ON clients.Company_ID=com.Company_ID";

            if ($queryString != '') {
                $condition->compare('t.Cust_ID_Shortcut', $queryString, true, 'OR');
                $condition->compare('com.Company_Name', $queryString, true, 'OR');
            }

            $condition->addCondition("t.Client_Client_ID = '" . $document->Client_ID . "'");
            $condition->order = 'com.Company_Name ASC';
            $customers = Customers::model()->with('client.company')->findAll($condition);

            $this->renderPartial('customers_list', array(
                'customers' => $customers,
            ));
        }
    }

    public  function  actionAjaxW9Save(){


        if (Yii::app()->request->isAjaxRequest && isset($_POST['W9'])) {

            $w9Id = intval($_POST['W9']['W9_ID']);
            $w9 = W9::model()->findByPk($w9Id);

            //convert date string to server format
            $_POST['W9']['Signature_Date'] = Helper::checkDate($_POST['W9']['Signature_Date']);

            // if w9 exists
            if ($w9) {
                // get client info
                $client = Clients::model()->with('company')->findByPk($w9->Client_ID);
                //get company info
                $company = $client->company;
                $address = $company->adreses[0];




                    //set new values
                if($_POST['Companies']['SSN']!=''){
                    $company->SSN = $_POST['Companies']['SSN'];
                }

                $previous_verified_value = $w9->Verified;
                $previous_revision_value = $w9->Revision_ID;



                if ((Yii::app()->user->userType == Users::DB_ADMIN || Yii::app()->user->userType == Users::ADMIN
                        || Yii::app()->user->userType == Users::DATA_ENTRY_CLERK)) {

                        $w9->Verified = $_POST['W9']['Verified'];
                        if ($previous_verified_value != $_POST['W9']['Verified']) {
                            W9::logVerifiedChange($w9->W9_ID, $w9->Verified);
                        }
                    } else {
                        $w9->Verified = 0;
                    }


                if ($w9->W9_Data_Entry == 0) {
                      //  $w9->W9_Data_Entry = Yii::app()->user->userID;
                }



                $w9->Revision_ID = 0;

                if($_POST['Addresses']['Address1']!=''){
                    $address->Address1= $_POST['Addresses']['Address1'];
                }

                if($_POST['Addresses']['City']!=''){
                    $address->City = $_POST['Addresses']['City'];
                }

                if($_POST['Addresses']['State']!=''){
                    $address->State = $_POST['Addresses']['State'];
                }

                if($_POST['Addresses']['ZIP']!=''){
                    $address->ZIP = $_POST['Addresses']['ZIP'];
                }


                if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $_POST['W9']['Signature_Date'])) {
                    $w9->Signature_Date = $_POST['W9']['Signature_Date'];
                            $w9->Signed = 1;
                } else {
                    $w9->Signed = 0;
                }


                if($_POST['W9']['Tax_Class']!=''){
                    $w9->Tax_Class = $_POST['W9']['Tax_Class'];
                }


                if($_POST['W9']['Revision_ID']!=0){
                    $w9->Revision_ID = $_POST['W9']['Revision_ID'];
                }

                if(isset($_POST['W9']['Exempt'])){
                    $w9->Exempt = $_POST['W9']['Exempt'];
                }
                if(isset($_POST['W9']['Tax_Class']) && $_POST['W9']['Tax_Class']!=0 ){
                    $w9->Tax_Class = $_POST['W9']['Tax_Class'];
                }

                if(isset($_POST['W9']['Account_Nums']) && $_POST['W9']['Account_Nums']!=''){
                    $w9->Account_Nums = $_POST['W9']['Account_Nums'];
                }


                    // validate form
                    $is_valid_comp_info = $company->validate();
                    $is_valid_w9_info = $w9->validate();
                    $is_valid_address_info = $address->validate();



                        // begin transaction

                    if( $is_valid_comp_info ){
                        $transaction = Yii::app()->db->beginTransaction();
                        try {
                            $company->save();
                            $transaction->commit();
                        } catch(Exception $e) {
                            $transaction->rollback();
                            $result=$e;
                        }
                    }

                    if($is_valid_address_info ){
                        $transaction = Yii::app()->db->beginTransaction();
                        try {
                            $address->save();
                            $transaction->commit();
                        } catch(Exception $e) {
                            $transaction->rollback();
                            $result=$e;
                        }
                    }

                    if( $is_valid_w9_info ){
                        $transaction = Yii::app()->db->beginTransaction();
                        try {
                            $w9->save();
                            $transaction->commit();
                            $result="Success";
                        } catch(Exception $e) {
                            $transaction->rollback();
                            $result=$e;
                        }

                    }
                }

        echo $result;
        }




    }

    public function actionAjaxPOSave(){

        if (Yii::app()->request->isAjaxRequest && isset($_POST['Pos'])) {
            //initialize variables
            $company = '';
            $vendor = '';
            $client = '';
            $document = '';
            $po = '';
            $file = '';
            $saved = false;
            $dists_enabled=strval($_POST['dists_enabled']);

            // form processing
            if (isset($_POST['po_data_entry_form_values'])) {
                $poId = intval($_POST['Pos']['PO_ID']);
                $po = Pos::model()->findByPk($poId);
                $dists=array();
                if($dists_enabled=='on') $dists = $_POST['Dist'];
                $distsToDB = array();
                $totalDistSum = 0;
                $invalidDistsSum = '';


                // get valid invoices
                foreach ($dists as $key => $dist) {
                    $distAcctNum = $dist['PO_Dists_GL_Code'];
                    $distDesc = $dist['PO_Dists_Description'];
                    $distAmount = $dist['PO_Dists_Amount'];

                    if (!is_numeric($distAmount) || $distAmount == '') {
                        $distAmount = '';
                    }

                    if (($distAmount == '' || $distAcctNum == '' || $distDesc == '') ) {

                        $invalidDistsSum = 'All fields in populated rows are required';
                    } else if ($distAmount != '' && $distAcctNum != '' && $distDesc != '') {
                        $distsToDB[] = $dist;
                        $totalDistSum += $distAmount;
                    }
                }

                // if ap exists
                if ($po) {
                    //convert date string to server format
                    $_POST['Pos']['PO_Date'] = Helper::checkDate($_POST['Pos']['PO_Date']);

                    if (isset($_POST['Pos']['Vendor_ID']) && intval($_POST['Pos']['Vendor_ID'])!=0){
                        $po->Vendor_ID = $_POST['Pos']['Vendor_ID'];
                    }

                    if (isset($_POST['Pos']['PO_Account_Number']) && strval($_POST['Pos']['PO_Account_Number'])!=''){
                        $po->PO_Account_Number = intval($_POST['Pos']['PO_Account_Number']);
                    }

                    if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/',  $_POST['Pos']['PO_Date'])) {
                        $po->PO_Date = $_POST['Pos']['PO_Date'];
                    }


                    if (isset($_POST['Pos']['PO_Subtotal']) && floatval($_POST['Pos']['PO_Subtotal'])!=0){
                        $po->PO_Subtotal = $_POST['Pos']['PO_Subtotal'];
                    }

                    if (isset($_POST['Pos']['PO_Tax']) && intval($_POST['Pos']['PO_Tax'])!=0){
                        $po->PO_Tax = $_POST['Pos']['PO_Tax'];
                    }


                    if (isset($_POST['Pos']['PO_Delivery_Chg']) && intval($_POST['Pos']['PO_Delivery_Chg'])!=0){
                        $po->PO_Delivery_Chg = $_POST['Pos']['PO_Delivery_Chg'];
                    }

                    if (isset($_POST['Pos']['PO_Other_Chg']) && intval($_POST['Pos']['PO_Other_Chg'])!=0){
                        $po->PO_Other_Chg = $_POST['Pos']['PO_Other_Chg'];
                    }

                    if (isset($_POST['Pos']['PO_Total']) && strval($_POST['Pos']['PO_Total'])!=0){
                        $po->PO_Total = $_POST['Pos']['PO_Total'];
                    }

                    if (isset($_POST['Pos']['Payment_Type']) && $_POST['Pos']['Payment_Type']!=''){
                        $po->Payment_Type = $_POST['Pos']['Payment_Type'];
                    }

                    if (isset($_POST['Pos']['PO_Card_Last_4_Digits']) && strval($_POST['Pos']['Payment_Type'])!=''){

                        if ($po->Payment_Type == 'CC' && preg_match('/^\d{4}$/', $_POST['Pos']['PO_Card_Last_4_Digits'])) {
                            if (preg_match('/^\d{4}$/',$_POST['Pos']['PO_Card_Last_4_Digits'])) {
                                $po->PO_Card_Last_4_Digits = $_POST['Pos']['PO_Card_Last_4_Digits'];
                            }
                        }

                    }



                    if($dists_enabled=='on') {
                        if( count($distsToDB) == 0 || (round(floatval($po->PO_Total),2) == round(floatval($totalDistSum),2) && count($distsToDB) > 0)) {$dists_condition=true;}
                        else {$dists_condition=false;}
                    } else {$dists_condition=true;}

                    $is_valid = $po->validate();

                    /*if ($is_valid &&  $po->PO_Subtotal != '' && $po->PO_Subtotal != '0'
                        && $po->PO_Total != '' && $po->PO_Total != '0'  && $po->PO_Date != ''
                        && ($po->Payment_Type != 'CC' || preg_match('/^\d{4}$/', $po->PO_Card_Last_4_Digits)) && $dists_condition ) {*/

                        //$po->PO_Approval_Value = Pos::READY_FOR_APPROVAL;
                        //$po->PO_Previous_PO_Val = Pos::READY_FOR_APPROVAL;

                        // begin transaction

                    if ($is_valid && $dists_condition) {
                        $transaction = Yii::app()->db->beginTransaction();
                        try {
                            $po->PO_Approval_Value =Pos::NOT_READY_FOR_APPROVAL;
                            $po->save();
                            // write invoises to DB
                            PoDists::savePODists($po->PO_ID, $distsToDB);
                            $saved = true;

                            $transaction->commit();
                            Audits::LogAction($po->Document_ID ,Audits::ACTION_AUTOSAVE);
                            $result="Success";
                        } catch(Exception $e) {
                            $transaction->rollback();
                            $result="Error";
                        }
                    }
                }
            }
        echo $result;
        }
    }


    /**
     * @param $model
     */
    protected function performAjaxValidation($model)
    {
        if(isset($_POST['ajax']) && $_POST['ajax']==='user-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }


    /**
     * used for showing ap entry form from AP->> Detail view
     */

    public function actionAjaxApDataentry()
    {
        // if it is ajax validation request
        if (isset($_POST['ap_id'])) {

            $ap_id = intval($_POST['ap_id']);
            $ap = Aps::model()->findByPk($ap_id);

            //getting current page. We can return to it after editing.
            $url_array= parse_url(Yii::app()->request->urlReferrer);
            $return_url = $url_array['path'].'?'.$url_array['query'];


            if(isset($_POST['ajax']) && $_POST['ajax']==='ap_data_entry_form')
            {
                echo CActiveForm::validate(array($ap));
                Yii::app()->end();
            }

            $document = Documents::model()->findByPk($ap->Document_ID);

            if ($ap->Vendor_ID > 0) {
                $vendor = Vendors::model()->with('client')->findByPk($ap->Vendor_ID);
                $client = $vendor->client;
                $company = $client->company;
            } else {
                $client = false;
                $company = false;
                $vendor = false;
            }

            $dists= GlDistDetails::getAPDists($ap->AP_ID);

            $vendorsCP = array();
            if ($document) {
                //get vendors Shortcut
                $vendorsCP = Vendors::getClientVendorsShortcutList($document->Client_ID);
            }

            $coaStructure = CoaStructure::model()->findByAttributes(array(
                'Project_ID' => Yii::app()->user->projectID,
            ));

            //get users settings for Due Date Terms assigning
            $user = Users::model()->with('settings')->findByPk(Yii::app()->user->userID);
            $due_date_term = $user->settings->Due_Date_Terms;

            $this->renderPartial('application.views.widgets.ap_dataentry_widget', array(
                'company' => $company,
                'client' => $client,
                'document' => $document,
                'ap' => $ap,
                'vendorsCP' => $vendorsCP,
                'vendor' => $vendor,
                'dists' => $dists['dists'],
                'dists_empty' => $dists['empty'],
                'coaStructure'=>$coaStructure,
                'due_date_term'=>$due_date_term,
                'return_url'=>$return_url
            ));

    }

  }

    /**
     * used for showing payment entry form from Payment->Detail view
     */
    public function actionAjaxPaymDataentry()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['paym_id']) ) {
            $paym_id = intval($_POST['paym_id']);

            //getting current page. We can return to it after editing.
            $url_array= parse_url(Yii::app()->request->urlReferrer);
            $return_url = $url_array['path'].'?'.$url_array['query'];

            if ($paym_id > 0) {
                $payment = Payments::model()->findByPk($paym_id);

                if ($payment) {
                    $document = Documents::model()->findByPk($payment->Document_ID);

                    if ($payment->Vendor_ID > 0) {
                        $vendor = Vendors::model()->with('client')->findByPk($payment->Vendor_ID);
                        $client = $vendor->client;
                        $company = $client->company;
                    } else {
                        $client = false;
                        $company = false;
                        $vendor = false;
                    }

                    $vendorsCP = array();
                    $acctNumbs = array();
                    if ($document) {
                        //get vendors Shortcut
                        $vendorsCP = Vendors::getClientVendorsShortcutList($document->Client_ID);

                        // get client's account numbers
                        $acctNumbs = Payments::getClientAccountNumbers($document->Client_ID, $document->Project_ID);
                    }


                    $dists=PaymentsInvoice::getInvoicesDist($payment->Payment_ID);
                    $invoices=$dists['dists'];



                    $this->renderPartial('application.views.widgets.paym_dataentry_widget', array(
                        'client'=>$client,
                        'company'=>$company,
                        'payment' => $payment,
                        'acctNumbs'=>$acctNumbs,
                        'document' => $document,
                        'invoices'=>$invoices,
                        'vendorsCP'=>$vendorsCP,
                        'dists' => $dists['dists'],
                        'dists_empty' => $dists['empty'],
                        'return_url'=>$return_url
                    ));
                }
            }
        }
    }


    public function actionAjaxJeDataEntry()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['doc_id']) ) {
            $doc_id = intval($_POST['doc_id']);

            if ($doc_id > 0) {

                $je = Journals::model()->findByAttributes(array(
                    'Document_ID' => $doc_id
                ));
                //$payrollTypes = PayrollTypes::getPayrollTypesList();

                if ($je) {

                    $this->renderPartial('application.views.widgets.dataentry_widget_je', array(
                        'je'=>$je

                    ));
                }

            }
        }

    }

    public function actionAjaxJeFromDetail()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['je_data_entry_form_values']) ) {

            $doc_id = intval($_POST['Journals']['Document_ID']);
            $saved = false;

            if ($doc_id > 0) {

                // form processing
                if (isset($_POST['je_data_entry_form_values'])) {
                    $jeId = intval($_POST['Journals']['JE_ID']);
                    $je = Journals::model()->findByPk($jeId);

                    //analize section and subsection where document placed
                    if ($je->JE_Date != $_POST['Payrolls']['JE_Date']) {
                        //means that library folder for payroll should be changed
                        $je_date_changed = true;
                        $year = substr($je->JE_Date, 0, 4);

                        $subsection =  Sections::getJeSubsection($je->document->Project_ID, $year, $je->JE_Date);

                        $libDoc = LibraryDocs::model()->findAllByAttributes(array(
                            'Subsection_ID' => $subsection->Subsection_ID,
                        ));

                        if (count($libDoc) == 1) {
                            //it means in section there are no items except current - section should be deleted
                            Sections::deleteSubsection($subsection->Section_ID,$subsection->Subsection_ID);
                        } else if (count($libDoc) >1)  {
                            //else simple delete document from this folder
                            $libDoc = LibraryDocs::model()->findByAttributes(array(
                                'Subsection_ID' => $subsection->Subsection_ID,
                                'Document_ID' =>  $je->Document_ID
                            ));
                            $libDoc->delete();
                        }
                    }


                    //convert date string to server format
                    $_POST['Journals']['JE_Date'] = Helper::checkDate($_POST['Journals']['JE_Date']);

                    // if JE exists
                    if ($je) {
                        $document = Documents::model()->findByPk($je->Document_ID);

                        // get last document's file
                        /*$condition = new CDbCriteria();
                        $condition->select = 'Mime_Type,File_Name';
                        $condition->condition = "Document_ID='" . $document->Document_ID . "'";
                        $file = Images::model()->find($condition);*/

                        $je->attributes = $_POST['Journals'];

                        // validate form
                        $is_valid = $je->validate();
                        if ($je->JE_Number == '' || $je->JE_Number == '0') {
                            $je->addError('JE_Number','Number is required!');
                        }

                        if ($je->JE_Date == '') {
                            $je->addError('JE_Date','Date is required!');
                        }

                        if ($is_valid && $je->JE_Number != '' && $je->JE_Number != '0' && $je->JE_Date != '') {
                            // begin transaction
                            $transaction = Yii::app()->db->beginTransaction();
                            try {
                                $je->save();
                                LibraryDocs::addDocumentToFolder($document->Document_ID);

                                $transaction->commit();
                                $saved = true;
                                //$_SESSION['storage_to_review'] =
                                $subsection =  Sections::getJeSubsection($je->document->Project_ID, $year, $je->JE_Date);
                                LibraryDocs::updateSesStorageToReview ($subsection->Section_ID,$subsection->Subsection_ID);

                            } catch(Exception $e) {
                                $transaction->rollback();
                            }


                        }
                    }
                }


                $result['html']=$this->renderPartial('application.views.widgets.dataentry_widget_je', array(
                        'je'=>$je,
                    ),true);

                    $result['saved']=$saved;
                    $result['je_date_changed']=$je_date_changed;

                    echo CJSON::encode($result);


                }

            }

        }


    public function actionAjaxArDataEntry()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['doc_id']) ) {
            $doc_id = intval($_POST['doc_id']);

            if ($doc_id > 0) {

                $ar = Ars::model()->findByAttributes(array(
                    'Document_ID' => $doc_id
                ));


                if ($ar) {

                    $this->renderPartial('application.views.widgets.dataentry_widget_ar', array(
                        'ar'=>$ar,
                    ));
                }

            }
        }

    }


    public function actionAjaxArFromDetail()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['ar_data_entry_form_values']) ) {

            $doc_id = intval($_POST['Ars']['Document_ID']);
            $saved = false;

            if ($doc_id > 0) {

                $_POST['Ars']['Invoice_Date'] = Helper::checkDate($_POST['Ars']['Invoice_Date']);

                // form processing
                $ar = Ars::model()->findByAttributes(array (
                        'Document_ID' => $doc_id
                ));
                $year = substr($ar->Invoice_Date, 0, 4);

                if ($ar->Invoice_Date != $_POST['Ars']['Invoice_Date']) {
                    //means that library folder for payroll should be changed
                    $ar_date_changed = true;
                    $subsection =  Sections::getArSubsection($ar->document->Project_ID, $year, $ar->Invoice_Date);

                    $libDoc = LibraryDocs::model()->findAllByAttributes(array(
                        'Subsection_ID' => $subsection->Subsection_ID,
                    ));

                    if (count($libDoc) == 1) {
                        //it means in section there are no items except current - section should be deleted
                        Sections::deleteSubsection($subsection->Section_ID,$subsection->Subsection_ID);
                    } else if (count($libDoc) >1)  {
                        //else simple delete document from this folder
                        $libDoc = LibraryDocs::model()->findByAttributes(array(
                            'Subsection_ID' => $subsection->Subsection_ID,
                            'Document_ID' =>  $ar->Document_ID
                        ));
                        $libDoc->delete();
                    } else if (count($libDoc) == 0) {
                        die;
                    }
                }

                //convert date string to server format

                // if AR exists
                if ($ar) {

                        $ar->attributes = $_POST['Ars'];

                        // validate form
                        $is_valid = $ar->validate();

                        if ($ar->Company_Name == '') {
                            $ar->addError('Company_Name','Company Name is required!');
                        }

                        if ($ar->Invoice_Number == '' || $ar->Invoice_Number == '0') {
                            $ar->addError('Invoice_Number','Number is required!');
                        }

                        if ($ar->Invoice_Amount == '' || $ar->Invoice_Amount <= 0) {
                            $ar->addError('Invoice_Amount','Amount is required!');
                        }

                        if ($ar->Invoice_Date == '') {
                            $ar->addError('Invoice_Date','Invoice Date is required!');
                        }

                        /*
                        if ($ar->Customer_ID == '0') {
                            $ar->addError('Customer_ID','Choose Customer!');
                        }
                        */

                        if ($is_valid && $ar->Company_Name != '' &&
                            $ar->Invoice_Number != '' && $ar->Invoice_Number != '0' && //$ar->Customer_ID > 0 &&
                            $ar->Invoice_Amount != '' && $ar->Invoice_Amount > 0 && $ar->Invoice_Date != '') {
                            // begin transaction
                            $transaction = Yii::app()->db->beginTransaction();
                            try {
                                $ar->save();
                                LibraryDocs::addDocumentToFolder($ar->Document_ID);

                                $transaction->commit();
                                $saved = true;

                                $subsection =  Sections::getArSubsection($ar->document->Project_ID, $year, $ar->Invoice_Date);
                                LibraryDocs::updateSesStorageToReview ($subsection->Section_ID,$subsection->Subsection_ID);
                            } catch(Exception $e) {
                                $transaction->rollback();
                                $saved = false;
                            }


                        }
                }
            }

            $result['html']=$this->renderPartial('application.views.widgets.dataentry_widget_ar', array(
                        'ar'=>$ar,
            ),true);

            $result['saved']=$saved;
            $result['ar_date_changed']=$ar_date_changed;

            echo CJSON::encode($result);


        }

    }

    public function actionAjaxPayrDataEntry()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['doc_id']) ) {
            $doc_id = intval($_POST['doc_id']);

            if ($doc_id > 0) {

                $payroll = Payrolls::model()->findByAttributes(array(
                    'Document_ID' => $doc_id
                ));
                $payrollTypes = PayrollTypes::getPayrollTypesList();

                if ($payroll) {

                    $this->renderPartial('application.views.widgets.dataentry_widget_payr', array(
                        'payroll'=>$payroll,
                        'payrollTypes'=>$payrollTypes
                    ));
                }

            }
        }

    }

    public function actionAjaxPayrFromDetail()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['payroll_data_entry_form_values']) ) {

            $doc_id = intval($_POST['Payrolls']['Document_ID']);
            $saved = false;

            if ($doc_id > 0) {

                $_POST['Payrolls']['Week_Ending'] = Helper::checkDate($_POST['Payrolls']['Week_Ending']);

                $payroll = Payrolls::model()->findByAttributes(array(
                    'Document_ID' => $doc_id
                ));
                $payrollTypes = PayrollTypes::getPayrollTypesList();



                if ($payroll) {
                    $year = substr($payroll->Week_Ending, 0, 4);
                    //analize section and subsection where document placed
                    if ($payroll->Week_Ending != $_POST['Payrolls']['Week_Ending']) {
                        //means that library folder for payroll should be changed
                        $weekending_changed = true;

                        $subsection =  Sections::getPayrollSubsection($payroll->document->Project_ID, $year, $payroll->Week_Ending);

                        $libDoc = LibraryDocs::model()->findAllByAttributes(array(
                            'Subsection_ID' => $subsection->Subsection_ID,
                        ));

                        if (count($libDoc) == 1) {
                            //it means in section there are no items except current - section should be deleted
                            Sections::deleteSubsection($subsection->Section_ID,$subsection->Subsection_ID);
                        } else if (count($libDoc) >1)  {
                            //else simple delete document from this folder
                            $libDoc = LibraryDocs::model()->findByAttributes(array(
                                'Subsection_ID' => $subsection->Subsection_ID,
                                'Document_ID' =>  $payroll->Document_ID
                            ));
                            $libDoc->delete();
                        }
                    }


                    $payroll->attributes = $_POST['Payrolls'];
                    // validate form
                    $is_valid = $payroll->validate();

                    if ($payroll->Payroll_Type_ID == 0) {
                        $payroll->addError('Payroll_Type_ID','Choose Payroll Type!');
                    }

                    if ($payroll->Week_Ending == '') {
                        $payroll->addError('Week_Ending','Week Ending is required!');
                    }

                    if ($is_valid && $payroll->Payroll_Type_ID != '0' && $payroll->Week_Ending != '') {
                        // begin transaction
                        $transaction = Yii::app()->db->beginTransaction();
                        try {
                            $payroll->save();
                            LibraryDocs::addDocumentToFolder($payroll->Document_ID);
                            $transaction->commit();
                            $saved = true;
                            //$_SESSION['storage_to_review'] =
                            $subsection =  Sections::getPayrollSubsection($payroll->document->Project_ID, $year, $payroll->Week_Ending);
                            LibraryDocs::updateSesStorageToReview ($subsection->Section_ID,$subsection->Subsection_ID);


                        } catch(Exception $e) {
                            $transaction->rollback();
                        }

                    }

                    $result['html']=$this->renderPartial('application.views.widgets.dataentry_widget_payr', array(
                        'payroll'=>$payroll,
                        'payrollTypes'=>$payrollTypes
                    ),true);

                    $result['saved']=$saved;
                    $result['weekending_changed']=$weekending_changed;

                    echo CJSON::encode($result);


                }

            }

        }

    }




    /**
     * Used for showing po entry form from Po->> Detail view
     */
    public function actionAjaxPoDataentry()
    {
        // if it is ajax validation request
        if (isset($_POST['po_id'])) {

            $po_id = intval($_POST['po_id']);
            $po = Pos::model()->findByPk($po_id);

            //getting current page. We can return to it after editing.
            $url_array= parse_url(Yii::app()->request->urlReferrer);
            $return_url = $url_array['path'].'?'.$url_array['query'];

            $document = Documents::model()->findByPk($po->Document_ID);

            $vendor = $po->vendor;
            if ($vendor) {
                $client = $vendor->client;
                $company = $client->company;
            }

            $dists=PoDists::model()->getPODists($po->PO_ID);

            $vendorsCP = array();
            if ($document) {
                //get vendors Shortcut
                $vendorsCP = Vendors::getClientVendorsShortcutList($document->Client_ID);
            }

            $coaStructure = CoaStructure::model()->findByAttributes(array(
                'Project_ID' => Yii::app()->user->projectID,
            ));

            $this->renderPartial('application.views.widgets.po_dataentry_widget', array(
                    'client'=>$client,
                    'company'=>$company,
                    'document' => $document,
                    'po' => $po,
                    'dists' => $dists['dists'],
                    'dists_empty' => $dists['empty'],
                    'vendorsCP'=> $vendorsCP,
                    'coaStructure'=>$coaStructure,
                    'return_url'=> $return_url
            ));
        }
    }



    /**
     * saving PO dataentry form in ajax mode with validation
     * Can be deleted since actionAjaxPoFromDetailFull is present.
     */
    public function actionAjaxPoFromDetail()
    {
        if (Yii::app()->request->isAjaxRequest ) {
            //initialize variables
            $company = '';
            $vendor = '';
            $client = '';
            $document = '';
            $po = '';
            $file = '';
            $saved = false;
            $dists_enabled=strval($_POST['dists_enabled']);

            //getting current page. We can return to it after editing.
            $url_array= parse_url(Yii::app()->request->urlReferrer);
            $return_url = $url_array['path'].'?'.$url_array['query'];


            // form processing
            if (isset($_POST['po_data_entry_form_values'])) {
                $poId = intval($_POST['Pos']['PO_ID']);
                $po = Pos::model()->findByPk($poId);
                $dists=array();
                if($dists_enabled=='on') $dists = $_POST['Dist'];
                $distsToDB = array();
                $totalDistSum = 0;
                $invalidDistsSum = '';


                // get valid invoices
                foreach ($dists as $key => $dist) {
                    $distAcctNum = $dist['PO_Dists_GL_Code'];
                    $distDesc = $dist['PO_Dists_Description'];
                    $distAmount = $dist['PO_Dists_Amount'];

                    if (!is_numeric($distAmount) || $distAmount == '') {
                        $distAmount = '';
                    }

                    if (($distAmount != '' || $distAcctNum != '' || $distDesc != '') && ($distAmount == '' || $distAcctNum == '' ||  $distDesc == '')) {
                        $invalidDistsSum = 'All fields in populated rows are required';
                    } else if ($distAmount != '' && $distAcctNum != '' && $distDesc != '') {
                        $distsToDB[] = $dist;
                        $totalDistSum += $distAmount;
                    }
                }

                // if ap exists
                if ($po) {
                    //convert date string to server format
                    $_POST['Pos']['PO_Date'] = Helper::checkDate($_POST['Pos']['PO_Date']);

                    $document = Documents::model()->findByPk($po->Document_ID);
                    // get last document's file
                    $condition = new CDbCriteria();
                    $condition->select = 'Mime_Type,File_Name';
                    $condition->condition = "Document_ID='" . $document->Document_ID . "'";
                    $file = Images::model()->find($condition);

                    $po->attributes = $_POST['Pos'];

                    if ($po->Vendor_ID > 0) {
                        $vendor = Vendors::model()->with('client')->findByPk($po->Vendor_ID);
                        $client = $vendor->client;
                        $company = $client->company;
                    } else {
                        $client = false;
                        $company = false;
                        $vendor = false;
                    }

                    // validate form
                    $is_valid = $po->validate();
                    if ($po->PO_Account_Number == '' || $po->PO_Account_Number == '0') {
                        //$po->addError('PO_Account_Number','Account Number is required!');
                    }

                    if ($po->PO_Subtotal == '' || $po->PO_Subtotal == '0') {
                        $po->addError('PO_Subtotal','Subtotal is required!');
                    }

                    if ($po->PO_Total == '' || $po->PO_Total == '0') {
                        $po->addError('PO_Total','Total is required!');
                    }

                    if ($po->PO_Date == '') {
                        $po->addError('PO_Date','Date is required!');
                    }

                    if ($po->Payment_Type == '0') {
                        $po->addError('Payment_Type','Choose Payment Type!');
                    }


                    if ($po->Payment_Type == 'CC' && $po->PO_Card_Last_4_Digits == '') {
                        $po->addError('PO_Card_Last_4_Digits','Last 4 Digits are required!');
                    }

                    if ($po->Payment_Type == 'CC' && !preg_match('/^\d{4}$/', $po->PO_Card_Last_4_Digits)) {
                        $po->addError('PO_Card_Last_4_Digits','Invalid Last 4 Digits!');
                    }

                    if ($po->Vendor_ID == '0') {
                        $po->addError('Vendor_ID','Choose Vendor!');
                    }

                    if($dists_enabled=='on') {
                        if (round(floatval($po->PO_Total),2) != round(floatval($totalDistSum),2) && $invalidDistsSum == '') {
                            $invalidDistsSum = "The detail of Distributions is not in balance with the PO Total
                                               You're out by $" . round(abs(round(floatval($totalDistSum),2) - round(floatval($po->PO_Total),2)),2);

                            $po->addError('Dists',$invalidDistsSum);
                        }
                    }

                    if($dists_enabled=='on') {
                        if( $invalidDistsSum == '' && count($distsToDB) == 0 || (round(floatval($po->PO_Total),2) == round(floatval($totalDistSum),2) && count($distsToDB) > 0)) {$dists_condition=true;} else {$dists_condition=false;}
                    } else {$dists_condition=true;}

                    if ($is_valid &&  $po->PO_Subtotal != '' && $po->PO_Subtotal != '0'
                        && $po->PO_Total != '' && $po->PO_Total != '0'  && $po->Vendor_ID != '0' && $po->PO_Date != '' && $po->Payment_Type != '0'
                        && ($po->Payment_Type != 'CC' || preg_match('/^\d{4}$/', $po->PO_Card_Last_4_Digits)) && $invalidDistsSum == '' && $dists_condition ) {
                       // $po->PO_Approval_Value = Pos::READY_FOR_APPROVAL;
                        //$po->PO_Previous_PO_Val = Pos::READY_FOR_APPROVAL;


                        $approvalRange = Aps::getUserApprovalRange();
                        $appr_level_was_changed = 0;
                        // check approval range and set approval values
                        /*if ($approvalRange['user_appr_val'] == Aps::APPROVED) {
                            $po->PO_Approval_Value = $approvalRange['user_appr_val'];
                            $po->PO_Previous_PO_Val = $approvalRange['prev_user_appr_val'];
                            $po->PO_Approved = 1;
                            $appr_level_was_changed =1;

                        } else if ($approvalRange['user_appr_val'] > Aps::READY_FOR_APPROVAL && $approvalRange['user_appr_val']>$po->PO_Approval_Value ) {
                            $po->PO_Approval_Value = $approvalRange['user_appr_val'];
                            $po->PO_Previous_PO_Val = $approvalRange['prev_user_appr_val'];
                            $appr_level_was_changed =1;
                        } else {
                            //$ap->AP_Approval_Value = Aps::READY_FOR_APPROVAL;
                            //$ap->Previous_AP_A_Val = Aps::READY_FOR_APPROVAL;
                        }*/

                        // begin transaction
                        $transaction = Yii::app()->db->beginTransaction();
                        try {
                            $po->save();

                            //save distributions
                            $error_array = PoDists::savePODists($po->PO_ID, $distsToDB);

                            if (count($error_array) == 0) {



                                $saved = true;
                                $transaction->commit();
                                Audits::LogAction($po->Document_ID,Audits::ACTION_DE_SAVE);

                                //if approval level was changed we need to log it to Audits
                                if($appr_level_was_changed) {
                                    Audits::LogAction($po->Document_ID,Audits::ACTION_APPROVAL);
                                }

                                // regenerate pdf
                                if ($document->Origin == 'G') {
                                    Documents::pdfGeneration($po->Document_ID,'PO',($approvalRange['user_appr_val'] == Pos::APPROVED));
                                    Audits::LogAction($po->Document_ID,Audits::ACTION_REPDF);
                                }
                                //if finished with this - po add it to library
                                if ($po->PO_Approved == 1) LibraryDocs::addDocumentToFolder($po->Document_ID);

                            } else {
                                $transaction->rollback();
                                $invalidDistsSum='Error saving COA';
                                $po->addError('Dists',$invalidDistsSum);
                            }
                        } catch(Exception $e) {
                               $transaction->rollback();
                        }
                    }
                }
            }
            $dists=PoDists::getPODists($po->PO_ID);
            $vendorsCP = array();
            if ($document) {
                //get vendors Shortcut
                $vendorsCP = Vendors::getClientVendorsShortcutList($document->Client_ID);
            }

            $result['html']=$this->renderPartial('application.views.widgets.po_dataentry_widget', array(
                    'company' => $company,
                    'client' => $client,
                    'document' => $document,
                    'po' => $po,
                    'vendorsCP' => $vendorsCP,
                    'vendor' => $vendor,
                    'dists' => $dists['dists'],
                    'dists_empty' => $dists['empty'],
                    'return_url'=>$return_url

                ),true
            );
            $result['saved']=$saved;

            echo CJSON::encode($result);

        }
    }


    /**
     * saving PO dataentry form in ajax mode with validation
     */
    public function actionAjaxPoFromDetailFull()
    {
        if (Yii::app()->request->isAjaxRequest ) {
            //initialize variables
            $company = '';
            $vendor = '';
            $client = '';
            $document = '';
            $po = '';
            $file = '';
            $saved = false;
            $dists_enabled=strval($_POST['dists_enabled']);

            //getting current page. We can return to it after editing.
            $url_array= parse_url(Yii::app()->request->urlReferrer);
            $return_url = $url_array['path'].'?'.$url_array['query'];


            // form processing
            if (isset($_POST['Pos']) && isset($_POST['PoDescDetail']) && isset($_POST['PoDists'])) {
                $poId = intval($_POST['Pos']['PO_ID']);
                $po = Pos::model()->findByPk($poId);

                $dists=array();
                $dists = $_POST['PoDists'];

                $distsToDB = array();
                $totalDistSum = 0;
                $invalidDistsSum = '';



                $result = PoDescDetail::prepareDescDetails($_POST['PoDescDetail']);
                $detailsToSave = $result['detailsToSave'];
                $subtotal = $result['subtotal'];
                $desc_total = round(floatval($result['total']),2) ;





                // get valid invoices
                foreach ($dists as $key => $dist) {
                    $distAcctNum = $dist['PO_Dists_GL_Code'];
                    $distDesc = $dist['PO_Dists_Description'];
                    $distAmount = $dist['PO_Dists_Amount'];

                    if (!is_numeric($distAmount) || $distAmount == '') {
                        $distAmount = '';
                    }

                    if (($distAmount != '' || $distAcctNum != '' || $distDesc != '') && ($distAmount == '' || $distAcctNum == '' ||  $distDesc == '')) {
                        $invalidDistsSum = 'All fields in populated rows are required';
                    } else if ($distAmount != '' && $distAcctNum != '' && $distDesc != '') {
                        $distsToDB[] = $dist;
                        $totalDistSum += $distAmount;
                    }
                }

                // if ap exists
                if ($po) {
                    //convert date string to server format
                    $_POST['Pos']['PO_Date'] = Helper::checkDate($_POST['Pos']['PO_Date']);

                    $document = Documents::model()->findByPk($po->Document_ID);
                    // get last document's file
                    $condition = new CDbCriteria();
                    $condition->select = 'Mime_Type,File_Name';
                    $condition->condition = "Document_ID='" . $document->Document_ID . "'";
                    $file = Images::model()->find($condition);

                    $po->attributes = $_POST['Pos'];

                    $other_charges = round(floatval($po->PO_Tax),2) + round(floatval($po->PO_Delivery_Chg),2) + round(floatval($po->PO_Other_Chg),2);

                    if ($po->Vendor_ID > 0) {
                        $vendor = Vendors::model()->with('client')->findByPk($po->Vendor_ID);
                        $client = $vendor->client;
                        $company = $client->company;
                    } else {
                        $client = false;
                        $company = false;
                        $vendor = false;
                    }

                    // validate form
                    $is_valid = $po->validate();
                    if ($po->PO_Account_Number == '' || $po->PO_Account_Number == '0') {
                        //$po->addError('PO_Account_Number','Account Number is required!');
                    }

                    if ($po->PO_Subtotal == '' || $po->PO_Subtotal == '0') {
                        $po->addError('PO_Subtotal','Subtotal is required!');
                    }

                    if ($po->PO_Total == '' || $po->PO_Total == '0') {
                        $po->addError('PO_Total','Total is required!');
                    }

                    if ($po->PO_Date == '') {
                        $po->addError('PO_Date','Date is required!');
                    }

                    if ($po->Payment_Type == '0') {
                        $po->addError('Payment_Type','Choose Payment Type!');
                    }


                    if ($po->Payment_Type == 'CC' && $po->PO_Card_Last_4_Digits == '') {
                        $po->addError('PO_Card_Last_4_Digits','Last 4 Digits are required!');
                    }

                    if ($po->Payment_Type == 'CC' && !preg_match('/^\d{4}$/', $po->PO_Card_Last_4_Digits)) {
                        $po->addError('PO_Card_Last_4_Digits','Invalid Last 4 Digits!');
                    }

                    if ($po->Vendor_ID == '0') {
                        $po->addError('Vendor_ID','Choose Vendor!');
                    }

                        if (round(floatval($po->PO_Total),2) != round(floatval($totalDistSum),2) && $invalidDistsSum == '') {
                            $invalidDistsSum = "The detail of Distributions is not in balance with the PO Total
                                               You're out by $" . round(abs(round(floatval($totalDistSum),2) - round(floatval($po->PO_Total),2)),2);

                            $po->addError('Dists',$invalidDistsSum);
                        }

                    if( $invalidDistsSum == '' && count($distsToDB) == 0 ||
                        (round(floatval($po->PO_Total),2) == round(floatval($totalDistSum),2) && count($distsToDB) > 0)) {
                        $dists_condition=true;
                    } else {
                        $dists_condition=false;
                    }



                    // check details
                    if ($detailsToSave) {
                        foreach ($detailsToSave as $poDecrDetail) {
                            if ($poDecrDetail['PO_Desc_Qty'] == '' || $poDecrDetail['PO_Desc_Qty'] == '0' || $poDecrDetail['PO_Desc_Desc'] == ''
                                || $poDecrDetail['PO_Desc_Desc'] == '-'|| $poDecrDetail['PO_Desc_Amount'] == '' || $poDecrDetail['PO_Desc_Amount'] == '0') {
                                $detailsError = 'Qty, Description and Amount are required for all populated rows';
                                $hasErrors = true;
                                break;
                            }
                        }

                        if ( $desc_total == round(floatval($totalDistSum - $other_charges),2) && $desc_total == round(floatval($po->PO_Total - $other_charges),2)) {
                            $desc_dist_matched = true;
                        } else {
                            $desc_dist_matched = false;
                            $detailsError .= " Desc total sum doesn't match to other sums";
                        }

                    } else {
                        $detailsError = 'At least one row of PO details is required';
                        $hasErrors = true;
                    }

                    if ($is_valid &&  $po->PO_Subtotal != '' && $po->PO_Subtotal != '0'
                        && $po->PO_Total != '' && $po->PO_Total != '0'  && $po->Vendor_ID != '0' && $po->PO_Date != '' && $po->Payment_Type != '0'
                        && ($po->Payment_Type != 'CC' || preg_match('/^\d{4}$/', $po->PO_Card_Last_4_Digits)) && $invalidDistsSum == '' && $dists_condition && $desc_dist_matched ) {
                        // $po->PO_Approval_Value = Pos::READY_FOR_APPROVAL;
                        //$po->PO_Previous_PO_Val = Pos::READY_FOR_APPROVAL;


                        $approvalRange = Aps::getUserApprovalRange();
                        $appr_level_was_changed = 0;
                        // check approval range and set approval values
                        /*if ($approvalRange['user_appr_val'] == Aps::APPROVED) {
                            $po->PO_Approval_Value = $approvalRange['user_appr_val'];
                            $po->PO_Previous_PO_Val = $approvalRange['prev_user_appr_val'];
                            $po->PO_Approved = 1;
                            $appr_level_was_changed =1;

                        } else if ($approvalRange['user_appr_val'] > Aps::READY_FOR_APPROVAL && $approvalRange['user_appr_val']>$po->PO_Approval_Value ) {
                            $po->PO_Approval_Value = $approvalRange['user_appr_val'];
                            $po->PO_Previous_PO_Val = $approvalRange['prev_user_appr_val'];
                            $appr_level_was_changed =1;
                        } else {
                            //$ap->AP_Approval_Value = Aps::READY_FOR_APPROVAL;
                            //$ap->Previous_AP_A_Val = Aps::READY_FOR_APPROVAL;
                        }*/

                        // begin transaction
                        $transaction = Yii::app()->db->beginTransaction();
                        try {
                            $po->save();

                            // save descr. details
                            $desc_saving_result = PoDescDetail::savePODetails($po->PO_ID, $detailsToSave);
                            if (!$desc_saving_result) throw new Exception('Desc saving error');

                            //save distributions
                            $error_array = PoDists::savePODists($po->PO_ID, $distsToDB);

                            if (count($error_array) == 0) {



                                $saved = true;
                                $transaction->commit();
                                Audits::LogAction($po->Document_ID,Audits::ACTION_DE_SAVE);

                                //if approval level was changed we need to log it to Audits
                                if($appr_level_was_changed) {
                                    Audits::LogAction($po->Document_ID,Audits::ACTION_APPROVAL);
                                }

                                // regenerate pdf
                                if ($document->Origin == 'G') {
                                    Documents::pdfGeneration($po->Document_ID,'PO',($approvalRange['user_appr_val'] == Pos::APPROVED));
                                    Audits::LogAction($po->Document_ID,Audits::ACTION_REPDF);
                                }
                                //if finished with this - po add it to library
                                if ($po->PO_Approved == 1) LibraryDocs::addDocumentToFolder($po->Document_ID);



                            } else {
                                $transaction->rollback();
                                $invalidDistsSum='Error saving COA';
                                $po->addError('Dists',$invalidDistsSum);
                            };
                        } catch(Exception $e) {
                            $transaction->rollback();
                        }
                    }
                }
            }
            $dists=PoDists::getPODists($po->PO_ID);
            $vendorsCP = array();
            if ($document) {
                //get vendors Shortcut
                $vendorsCP = Vendors::getClientVendorsShortcutList($document->Client_ID);
            }

            //but we need return some variables
            $descDetails = PoDescDetail::fromModelToArray($po);
            $coaStructure = CoaStructure::model()->findByAttributes(array(
                'Project_ID' => Yii::app()->user->projectID,
            ));
            $condition = UsersClientList::getClientAdminCondition($client->Client_ID);
            $vendorAdmin = UsersClientList::model()->with('user.person')->find($condition);


            $result['html']=$this->renderPartial('application.views.widgets.po_dataentry_full_widget', array(
                'company' => $company,
                'client' => $client,
                'document' => $document,
                'po' => $po,
                'vendor' => $vendor,
                'dists' => $dists['dists'],
                'dists_empty' => $dists['empty'],

                'descDetails' => $descDetails,
                'detailsError'=>$detailsError,
                'invalidDistsSum'=>$invalidDistsSum,
                'vendors'=> $vendorsCP,
                'vendorAdmin'=> $vendorAdmin,
                'coaStructure'=>$coaStructure,
                'return_url'=> $return_url


            ),true
            );
            $result['saved']=$saved;

            echo CJSON::encode($result);

        }
    }


    /**
     * save Payment form in ajax mode with validation
     */
    public function actionAjaxPaymFromDetail()
    {
        if (Yii::app()->request->isAjaxRequest ) {
            //initialize variables
            $company = '';
            $vendor = '';
            $client = '';
            $document = '';
            $payment = '';
            $file = '';
            $invoices = array();
            $saved = false;
            $invalidInvoices = '';
            $invalidInvoicesTopMess = '';

            //reset search option to default
            //if (!isset($_SESSION['last_paym_to_entry_search'])) {Payments::resetDataentrySearchOptions();}
            // set query params
            //if (isset($_POST['search_field'])) {Payments::initDataentrySearchOptions($_POST);}


            // form processing
            if (isset($_POST['payment_data_entry_form_values'])) {
                $paymentId = intval($_POST['Payments']['Payment_ID']);
                $payment = Payments::model()->findByPk($paymentId);
                $invoices = $_POST['Invoice'] ? $_POST['Invoice'] : array();
                $invoicesToDB = array();
                $totalInvSum = 0;
                $void = intval($_POST['Payments']['Void']);
                //convert date string to server format
                $_POST['Payments']['Payment_Check_Date'] = Helper::checkDate($_POST['Payments']['Payment_Check_Date']);

                // get valid invoices if not voided

                if (!$void) {
                    foreach ($invoices as $key => $invoice) {
                        $invNumber = $invoice['Invoice_Number'];
                        //$invAmount = $invoice['Invoice_Amount'];
                        $invAmount = round(floatval($invoice['Invoice_Amount']),2);

                        if (!is_numeric($invAmount) || $invAmount == '') {
                            $invAmount = '';
                        }

                        if ($invAmount != '' && $invNumber != '') {
                            $invoicesToDB[] = $invoice;
                            $totalInvSum += $invAmount;
                        }
                    }

                }
                // if payment exists
                if ($payment) {
                    $document = Documents::model()->findByPk($payment->Document_ID);

                    // get last document's file
                    //$condition = new CDbCriteria();
                    //$condition->select = 'Mime_Type,File_Name';
                    //$condition->condition = "Document_ID='" . $document->Document_ID . "'";
                    //$file = Images::model()->find($condition);

                    $payment->attributes = $_POST['Payments'];

                    if ($payment->Vendor_ID > 0) {
                        $vendor = Vendors::model()->with('client')->findByPk($payment->Vendor_ID);
                        $client = $vendor->client;
                        $company = $client->company;
                    } else {
                        $client = false;
                        $company = false;
                        $vendor = false;
                    }

                    // validate form
                    $is_valid = $payment->validate();
                    if ($payment->Payment_Check_Number == '' || $payment->Payment_Check_Number == '0') {
                        $payment->addError('Payment_Check_Number','Pmt. Number is required!');
                    }

                    if ($payment->Payment_Amount == '' || $payment->Payment_Amount < 0 ) {
                        $payment->addError('Payment_Amount','Pmt. Amount is required!');
                    }

                    if ($payment->Payment_Amount == 0 && $payment->Void == 0 ) {
                        $payment->addError('Payment_Amount','Zero value allowed only for voided');
                    }

                    if ($payment->Payment_Check_Date == '') {
                        $payment->addError('Payment_Check_Date','Pmt. Date is required!');
                    }

                    if ($payment->Vendor_ID == '0') {
                        $payment->addError('Vendor_ID','Choose Vendor!');
                    }

                    /**
                    if ($payment->Account_Num_ID == '0') {
                    $payment->addError('Account_Num_ID','Choose Acct. Number!');
                    }
                     */

                    if (round(floatval($payment->Payment_Amount),2) != round(floatval($totalInvSum),2) ) {
                        $invalidInvoices = "The detail of Invoices Attached is not in balance with the payment total.
                                           You're out by $" . round(abs(round(floatval($totalInvSum),2) - round(floatval($payment->Payment_Amount),2)),2);

                        $payment->addError('Payment_Amount',$invalidInvoices);
                        $is_valid = false;

                    }

                    // check related APs
                    foreach ($invoicesToDB as $invoiceToDB) {
                        $paymentAP = Payments::getPaymentInvoice($payment, $invoiceToDB['Invoice_Number']);
                        if (!$paymentAP) {
                            $invalidInvoices = 'The invoice number entered is not in the AP system. This check might not bind to the AP intended.';
                            $invalidInvoicesTopMess = 'The invoice Number "' . $invoiceToDB['Invoice_Number'] . '" you have entered
                                                   cannot be found in the AP that has been
                                                   previously entered. Please verify the detail on the payment.';
                            $payment->addError('Payment_Amount',$invalidInvoicesTopMess);
                            $is_valid = false;break;
                        } else if ($paymentAP->Invoice_Amount != $invoiceToDB['Invoice_Amount']) {
                            $invalidInvoices = 'Invalid Invoice Amount';
                            $invalidInvoicesTopMess = 'The Amount for invoice Number "' . $invoiceToDB['Invoice_Number'] . '" you have entered
                                                   does not correspond to amount of founded AP that has been
                                                   previously entered. Please verify the detail on the payment.';
                            $is_valid = false;
                            $payment->addError('Payment_Amount',$invalidInvoicesTopMess);
                            break;
                        }
                    }

                    $amount_validate = true;
                    if($payment->Payment_Amount == '') { $amount_validate = false;}
                    if($payment->Payment_Amount == 0 && !$void) { $amount_validate = false; }
                    if($payment->Payment_Amount < 0 ) { $amount_validate = false;}


                    if ($is_valid && $payment->Payment_Check_Number != '' && $payment->Payment_Check_Number != '0' &&
                        $amount_validate && $payment->Payment_Check_Date != ''
                        && round(floatval($payment->Payment_Amount),2) == round(floatval($totalInvSum),2)
                        && $payment->Vendor_ID > 0 && $invalidInvoices=='') {

                        // begin transaction
                        $transaction = Yii::app()->db->beginTransaction();
                        try {
                            $payment->save();
                            PaymentsInvoice::model()->deleteAllByAttributes(array('Payment_ID' => $payment->Payment_ID));
                            ApPayments::model()->deleteAllByAttributes(array('Payment_ID' => $payment->Payment_ID));

                            // write invoises to DB
                            foreach ($invoicesToDB as $invoiceToDB) {
                                $paymentInvoice = new PaymentsInvoice();
                                $paymentInvoice->Payment_ID = $payment->Payment_ID;
                                $paymentInvoice->Check_Invoice_Number = $invoiceToDB['Invoice_Number'];
                                $paymentInvoice->Check_Invoice_Amount = $invoiceToDB['Invoice_Amount'];
                                $paymentInvoice->save();

                                // add Payment-AP relationship
                                Payments::addAPPaymentRelationship($payment, $paymentInvoice);
                            }

                            LibraryDocs::addDocumentToFolder($payment->Document_ID);
                            LibraryDocs::addDocumentToBinder($payment->Document_ID);

                            $saved = true;

                            $transaction->commit();
                        } catch(Exception $e) {
                            $transaction->rollback();
                        }

                    }
                }
            }

            // get payments to enter data
            //$payments = Payments::model()->findPaymentsToEntry();
            //$num_pages = count($payments);
            //$page = intval($page);

            /*if (count($payments) > 0) {
                if ($page <= 0) {
                    $page = 1;
                } else if ($page > $num_pages) {
                    $page = $num_pages;
                }
            }*/

            if ($payment) {
                // get payment to view


                if ($payment->Vendor_ID > 0) {
                    $vendor = Vendors::model()->with('client')->findByPk($payment->Vendor_ID);
                    $client = $vendor->client;
                    $company = $client->company;
                } else {
                    $client = false;
                    $company = false;
                    $vendor = false;
                }

                $dists=PaymentsInvoice::getInvoicesDist($payment->Payment_ID);
                $invoices=$dists['dists'];

            }


            $vendorsCP = array();
            $acctNumbs = array();
            if ($document) {
                //get vendors Shortcut
                $vendorsCP = Vendors::getClientVendorsShortcutList($document->Client_ID);

                // get client's account numbers
                $acctNumbs = Payments::getClientAccountNumbers($document->Client_ID, $document->Project_ID);
            }

            $result['html']=$this->renderPartial('application.views.widgets.paym_dataentry_widget', array(
                    'client'=>$client,
                    'company'=>$company,
                    'payment' => $payment,
                    'acctNumbs'=>$acctNumbs,
                    'document' => $document,
                    'invoices'=>$invoices,
                    'vendorsCP'=>$vendorsCP,
                    'dists' => $dists['dists'],
                    'dists_empty' => $dists['empty'],
                ),true
            );

            $result['saved']=$saved;

            echo CJSON::encode($result);

        }
    }




     /**
     * for saving and validating AP in ajax mode for ap -> detail page only
     */
    public function actionAjaxApFromDetail()
    {

        if (Yii::app()->request->isAjaxRequest ) {

            //initialize variables
            $company = '';
            $vendor = '';
            $client = '';
            $document = '';
            $ap = '';
            $file = '';
            $saved = false;
            $poNum = '';
            $poError = '';
            $dists_enabled=strval($_POST['dists_enabled']);

            // form processing
            if (isset($_POST['ap_data_entry_form_values'])) {
                $apId = intval($_POST['Aps']['AP_ID']);
                $ap = Aps::model()->findByPk($apId);
                $dists = array();
                if($dists_enabled=='on') $dists = $_POST['Dist'];
                $distsToDB = array();
                $totalDistSum = 0;
                $invalidDistsSum = '';

                // get valid invoices
                foreach ($dists as $key => $dist) {
                    $distAcctNum = $dist['GL_Dist_Detail_COA_Acct_Number'];
                    $distDesc = $dist['GL_Dist_Detail_Desc'];
                    $distAmount = $dist['GL_Dist_Detail_Amt'];

                    if (!is_numeric($distAmount) || $distAmount == '') {
                        $distAmount = '';
                    }

                    if (($distAmount != '' || $distAcctNum != '' || $distDesc != '') && ($distAmount == '' || $distAcctNum == '' ||  $distDesc == '')) {
                        $invalidDistsSum = 'All fields in populated rows are required';
                    } else if ($distAmount != '' && $distAcctNum != '' && $distDesc != '') {
                        $distsToDB[] = $dist;
                        $totalDistSum += $distAmount;
                    }
                }

                // if ap exists
                if ($ap) {
                    //convert date string to server format
                    $_POST['Aps']['Invoice_Date'] = Helper::checkDate($_POST['Aps']['Invoice_Date']);
                    $_POST['Aps']['Invoice_Due_Date'] = Helper::checkDate($_POST['Aps']['Invoice_Due_Date']);

                    $document = Documents::model()->findByPk($ap->Document_ID);
                    // get last document's file
                    $condition = new CDbCriteria();
                    $condition->select = 'Mime_Type';
                    $condition->condition = "Document_ID='" . $document->Document_ID . "'";
                    $file = Images::model()->find($condition);

                    $ap->attributes = $_POST['Aps'];

                    if ($ap->Vendor_ID > 0) {
                        $vendor = Vendors::model()->with('client')->findByPk($ap->Vendor_ID);
                        $client = $vendor->client;
                        $company = $client->company;
                    } else {
                        $client = false;
                        $company = false;
                        $vendor = false;
                    }

                    $poNum = $_POST['PO_Number'];
                    if ($poNum != '' && $ap->Vendor_ID != '0') {
                        // get po
                        $condition = new CDbCriteria();
                        $condition->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID";
                        $condition->condition = "t.PO_Number = '$poNum'";
                        $condition->addCondition("t.Vendor_ID = '" . $ap->Vendor_ID . "'");
                        $condition->addCondition("documents.Project_ID = '" . $document->Project_ID . "'");
                        $po = Pos::model()->find($condition);

                        if (!$po) {
                            $poError = 'PO does not exist';
                        } else {
                            $outBalance = Pos::checkPOBalance($po->PO_ID, $ap->Invoice_Amount);
                            if ($outBalance > 0) {
                                $poError = 'This AP can not be attached to PO. Balance will be out of by $' . number_format($outBalance, 2);
                            }
                        }
                    }


                    // validate form
                    $is_valid = $ap->validate();
                    if ($ap->Invoice_Number == '' || $ap->Invoice_Number == '0') {
                        $ap->addError('Invoice_Number','Inv. Number is required!');
                    }

                    if ($ap->Invoice_Amount == '') {
                        $ap->addError('Invoice_Amount','Inv. Amount is required!');
                    }

                    if ($ap->Invoice_Date == '') {
                        $ap->addError('Invoice_Date','Inv. Date is required!');
                    }

                    if ($ap->Invoice_Reference == '') {
                        $ap->addError('Invoice_Reference','Description is required!');
                    }

                    if ($ap->Vendor_ID == '0') {
                        $ap->addError('Vendor_ID','Choose Vendor!');
                    }

                    if($dists_enabled=='on') {
                        if (round(floatval($ap->Invoice_Amount),2) != round(floatval($totalDistSum),2) && $invalidDistsSum == '') {

                           $str = "The detail of GL Dists is not in balance with the Inv. Amount.
                                               You're out by $" . round(abs(round(floatval($totalDistSum),2) - round(floatval($ap->Invoice_Amount),2)),2);
                            $ap->addError('Dists',$str);
                        }
                    }

                    if($dists_enabled) {
                        if( $invalidDistsSum == '' && count($distsToDB) == 0 || (round(floatval($ap->Invoice_Amount),2) == round(floatval($totalDistSum),2) && count($distsToDB) > 0)) {$dists_condition=true;} else {$dists_condition=false;}
                    } else {$dists_condition=true;}

                    if ($is_valid && $ap->Invoice_Number != '' && $ap->Invoice_Amount != '' && $ap->Invoice_Date != '' && $ap->Invoice_Reference != ''
                        && $ap->Vendor_ID != '0' && $ap->Invoice_Number != '0' && $poError == '' &&  $dists_condition) {
                       // $ap->AP_Approval_Value = Aps::READY_FOR_APPROVAL;
                       // $ap->Previous_AP_A_Val = Aps::READY_FOR_APPROVAL;
                        $approvalRange = Aps::getUserApprovalRange();
                        $appr_level_was_changed = 0;

                        // check approval range and set approval values
                        /*if ($approvalRange['user_appr_val'] == Aps::APPROVED) {
                            $ap->AP_Approval_Value = $approvalRange['user_appr_val'];
                            $ap->Previous_AP_A_Val = $approvalRange['prev_user_appr_val'];
                            $ap->Approved = 1;
                            $appr_level_was_changed =1;


                        } else if ($approvalRange['user_appr_val'] > Aps::READY_FOR_APPROVAL && $approvalRange['user_appr_val']>$ap->AP_Approval_Value) {
                            $ap->AP_Approval_Value = $approvalRange['user_appr_val'];
                            $ap->Previous_AP_A_Val = $approvalRange['prev_user_appr_val'];
                            $appr_level_was_changed =1;

                        } else {
                            //$ap->AP_Approval_Value = Aps::READY_FOR_APPROVAL;
                            //$ap->Previous_AP_A_Val = Aps::READY_FOR_APPROVAL;
                        }*/


                        if ($ap->Invoice_Due_Date == '') {
                            $ap->Invoice_Due_Date = null;
                        }

                        // begin transaction
                        $transaction = Yii::app()->db->beginTransaction();
                        try {
                            $ap->save();
                            // write dists to DB
                            $error_array = GlDistDetails::saveAPDists($ap->AP_ID, $distsToDB);

                            if (count($error_array) == 0) {

                                if ($poNum != '') {
                                    Pos::addApPORelation($po, $ap->Invoice_Amount, $ap->Invoice_Date, $ap->Invoice_Number, $ap->Invoice_Reference);
                                    $poNum = '';
                                }
                                $saved = true;
                                $transaction->commit();
                                Audits::LogAction($ap->Document_ID,Audits::ACTION_DE_SAVE);

                                //if approval level was changed we need to log it to Audits
                                if($appr_level_was_changed) {
                                    Audits::LogAction($ap->Document_ID,Audits::ACTION_APPROVAL);
                                }

                                // regenerate pdf
                                if ($document->Origin == 'G') {
                                    //APs::generatePdfFpdf($ap->AP_ID, ($approvalRange['user_appr_val'] == Aps::APPROVED));
                                    Documents::pdfGeneration($ap->Document_ID,'AP',($approvalRange['user_appr_val'] == Aps::APPROVED));
                                    Audits::LogAction($ap->Document_ID,Audits::ACTION_REPDF);
                                }

                                //if finished with this - po add it to library
                                if ($po->PO_Approved == 1) LibraryDocs::addDocumentToFolder($ap->Document_ID);

                            } else {$transaction->rollback(); $invalidDistsSum='Error saving COA'; $ap->addError('Dists',$invalidDistsSum); };

                        } catch(Exception $e) {
                            $transaction->rollback();
                        }

                    }
                }
            }



            $vendorsCP = array();
            if ($document) {
                //get vendors Shortcut
                $vendorsCP = Vendors::getClientVendorsShortcutList($document->Client_ID);
            }



            $result['html']=$this->renderPartial('application.views.widgets.ap_dataentry_widget', array(
                'company' => $company,
                'client' => $client,
                'document' => $document,
                'ap' => $ap,
                'vendorsCP' => $vendorsCP,
                'vendor' => $vendor,
                'dists' => $dists,
                'dists_empty' => $dists['empty'],
                'saved'=>$saved,
                ),true
            );

            $result['saved']=$saved;
            $result['origin']=$document->Origin;
            $result['extra_errors']=$error_array;

            echo CJSON::encode($result);



        }
    }









public function actionAjaxAPSave($page = 1)
    {

        if (Yii::app()->request->isAjaxRequest && isset($_POST['Aps'])) {

        $dists_enabled=strval($_POST['dists_enabled']);

        // form processing
        if (isset($_POST['ap_data_entry_form_values'])) {
            $apId = intval($_POST['Aps']['AP_ID']);
            $ap = Aps::model()->findByPk($apId);
            $dists = array();
            if($dists_enabled=='on') $dists = $_POST['Dist'];
            $distsToDB = array();
            $totalDistSum = 0;
            $invalidDistsSum = '';

            // get valid invoices
            foreach ($dists as $key => $dist) {
                $distAcctNum = $dist['GL_Dist_Detail_COA_Acct_Number'];
                $distDesc = $dist['GL_Dist_Detail_Desc'];
                $distAmount = $dist['GL_Dist_Detail_Amt'];

                if (!is_numeric($distAmount) || $distAmount == '') {
                    $distAmount = '';
                }

                if (($distAmount != '' || $distAcctNum != '' || $distDesc != '') && ($distAmount == '' || $distAcctNum == '' ||  $distDesc == '')) {

                } else if ($distAmount != '' && $distAcctNum != '' && $distDesc != '') {
                    $distsToDB[] = $dist;
                    $totalDistSum += $distAmount;
                }
            }

            // if ap exists
            if ($ap) {
                //convert date string to server format
                $_POST['Aps']['Invoice_Date'] = Helper::checkDate($_POST['Aps']['Invoice_Date']);
                $_POST['Aps']['Invoice_Due_Date'] = Helper::checkDate($_POST['Aps']['Invoice_Due_Date']);



                if (isset($_POST['Aps']['Vendor_ID']) && intval($_POST['Aps']['Vendor_ID'])!=0){
                    $ap->Vendor_ID = $_POST['Aps']['Vendor_ID'];
                }

                $poNum = $_POST['PO_Number'];
                if ($poNum != '' && $ap->Vendor_ID != '0') {
                    // get po
                    $condition = new CDbCriteria();
                    $condition->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID";
                    $condition->condition = "t.PO_Number = '$poNum'";
                    $condition->addCondition("t.Vendor_ID = '" . $ap->Vendor_ID . "'");
                    $condition->addCondition("documents.Project_ID = '" . $document->Project_ID . "'");
                    $po = Pos::model()->find($condition);

                    if ($po) {
                        $ap->PO_ID = $po->PO_ID;
                    }
                }


                // validate form


                if (isset($_POST['Aps']['Invoice_Number']) && strval($_POST['Aps']['Invoice_Number'])!=''){
                    $ap->Invoice_Number = $_POST['Aps']['Invoice_Number'];
                }

                if (isset($_POST['Aps']['Invoice_Amount']) && strval($_POST['Aps']['Invoice_Amount'])!=''){
                    $ap->Invoice_Amount = $_POST['Aps']['Invoice_Amount'];
                }

                if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/',  $_POST['Aps']['Invoice_Date'])) {
                    $ap->Invoice_Date = $_POST['Aps']['Invoice_Date'];
                }

                if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/',  $_POST['Aps']['Invoice_Due_Date'])) {
                    $ap->Invoice_Due_Date = $_POST['Aps']['Invoice_Due_Date'];
                }

                if (isset($_POST['Aps']['Invoice_Reference']) && strval($_POST['Aps']['Invoice_Reference'])!=''){
                    $ap->Invoice_Reference = $_POST['Aps']['Invoice_Reference'];
                }

                if (isset($_POST['Aps']['Detail_1099'][1]) && strval($_POST['Aps']['Detail_1099'][1])!=0){
                    $ap->Detail_1099 = 1;
                } else {
                    $ap->Detail_1099 = 0;
                }

                if (isset($_POST['Aps']['Detail_1099_Box_Number']) && strval($_POST['Aps']['Detail_1099_Box_Number'])!=''){
                    $ap->Detail_1099_Box_Number = $_POST['Aps']['Detail_1099_Box_Number'];
                    $ap->Detail_1099 = 1;
                }


                if($dists_enabled=='on') {
                    if (round(floatval($ap->Invoice_Amount),2) != round(floatval($totalDistSum),2) && $invalidDistsSum == '') {
                        $invalidDistsSum = "The detail of GL Dists is not in balance with the Inv. Amount.
                                               You're out by $" . round(abs(round(floatval($totalDistSum),2) - round(floatval($ap->Invoice_Amount),2)),2);
                    }
                }

                if($dists_enabled) {

                    if( $invalidDistsSum == '' && count($distsToDB) == 0 || (round(floatval($ap->Invoice_Amount),2) == round(floatval($totalDistSum),2) && count($distsToDB) > 0)) {
                        $dists_condition=true;
                    } else {
                        $dists_condition=false;
                    }

                } else {$dists_condition=true;}

                $is_valid = $ap->validate();

                if ($is_valid &&  $dists_condition) {
                    $ap->AP_Approval_Value = Aps::NOT_READY_FOR_APPROVAL;
                    //$ap->AP_Approval_Value = Aps::READY_FOR_APPROVAL;  //phase II-1 Logic call out v15 page  55

                    // begin transaction
                    $transaction = Yii::app()->db->beginTransaction();
                    try {
                        $ap->save();
                        Audits::LogAction($ap->Document_ID ,Audits::ACTION_AUTOSAVE);

                        if ($poNum != '') {
                            Pos::addApPORelation($po, $ap->Invoice_Amount, $ap->Invoice_Date, $ap->Invoice_Number, $ap->Invoice_Reference);
                            $poNum = '';
                        }
                        // write dists to DB
                        GlDistDetails::saveAPDists($ap->AP_ID, $distsToDB);
                        $saved = true;

                        $transaction->commit();
                    } catch(Exception $e) {
                        $transaction->rollback();
                    }

                }
            }
        }
    }
}
    public function actionAjaxPaySave()
    {
        // form processing
        if (Yii::app()->request->isAjaxRequest && isset($_POST['payment_data_entry_form_values'])) {
            $paymentId = intval($_POST['Payments']['Payment_ID']);
            $payment = Payments::model()->findByPk($paymentId);
            $invoices = $_POST['Invoice'];
            $invoicesToDB = array();
            $totalInvSum = 0;

            //convert date string to server format
            $_POST['Payments']['Payment_Check_Date'] = Helper::checkDate($_POST['Payments']['Payment_Check_Date']);

            // get valid invoices
            foreach ($invoices as $key => $invoice) {
                $invNumber = $invoice['Invoice_Number'];
                $invAmount = $invoice['Invoice_Amount'];
                if (!is_numeric($invAmount) || $invAmount == '') {
                    $invAmount = '';
                }

                if ($invAmount != '' && $invNumber != '') {
                    $invoicesToDB[] = $invoice;
                    $totalInvSum += $invAmount;
                }
            }

            // if payment exists
            if ($payment) {
              //  $document = Documents::model()->findByPk($payment->Document_ID);


                if (isset($_POST['Payments']['Vendor_ID']) && intval($_POST['Payments']['Vendor_ID'])!=0){
                    $payment->Vendor_ID = $_POST['Payments']['Vendor_ID'];
                }

                // validate form
                //$is_valid = $payment->validate();
                /*if (isset($_POST['Payments']['Payment_Check_Number']) && intval($_POST['Payments']['Payment_Check_Number'])!=0){
                    $payment->Payment_Check_Number = $_POST['Payments']['Payment_Check_Number'];
                }*/

               if (isset($_POST['Payments']['Payment_Amount']) && intval($_POST['Payments']['Payment_Amount'])>0 ){
                    $payment->Payment_Amount = $_POST['Payments']['Payment_Amount'];
                }


                if (isset($_POST['Payments']['Account_Num_ID']) && intval($_POST['Payments']['Account_Num_ID'])>0 ){
                    $payment->Account_Num_ID = $_POST['Payments']['Account_Num_ID'];
                }


                if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/',  $_POST['Payments']['Payment_Check_Date'])) {
                    $payment->Payment_Check_Date = $_POST['Payments']['Payment_Check_Date'];
                }



                // check related APs
                $temp_array =array();
               foreach ($invoicesToDB as $invoiceToDB) {
                    $paymentAP = Payments::getPaymentInvoice($payment, $invoiceToDB['Invoice_Number']);
                    if ($paymentAP) {
                                $temp_array [] = $invoiceToDB;
                    }

                }

                $invoicesToDB = $temp_array;//now in $invoicesToDB remains only records with valid Invoice_Number


                $is_valid = $payment->validate();

                if ($is_valid) {

                    // begin transaction
                    $transaction = Yii::app()->db->beginTransaction();
                    try {
                        $payment->Payment_Check_Number=0;
                        $payment->save();

                        // write invoises to DB
                        PaymentsInvoice::model()->deleteAllByAttributes(array(
                            'Payment_ID' => $payment->Payment_ID,
                        ));
                        foreach ($invoicesToDB as $invoiceToDB) {
                            $paymentInvoice = new PaymentsInvoice();
                            $paymentInvoice->Payment_ID = $payment->Payment_ID;
                            $paymentInvoice->Check_Invoice_Number = $invoiceToDB['Invoice_Number'];
                            $paymentInvoice->Check_Invoice_Amount = $invoiceToDB['Invoice_Amount'];
                            $paymentInvoice->save();

                            // add Payment-AP relationship
                           // Payments::addAPPaymentRelationship($payment, $paymentInvoice);
                        }

                        //LibraryDocs::addDocumentToFolder($payment->Document_ID);
                        //LibraryDocs::addDocumentToBinder($payment->Document_ID);

                        $saved = true;

                        $transaction->commit();
                        echo "Success";
                    } catch(Exception $e) {
                        $transaction->rollback();
                        echo "Error";
                    }

                }
            }
        }
    }

    public function actionAjaxPcSave($page = 1)
    {

        // form processing
        if (Yii::app()->request->isAjaxRequest && isset($_POST['pc_data_entry_form_values'])) {

            $pcId = intval($_POST['Pcs']['PC_ID']);
            $pc = Pcs::model()->findByPk($pcId);

            //convert date string to server format
            $_POST['Pcs']['Envelope_Date'] = Helper::checkDate($_POST['Pcs']['Envelope_Date']);

            // if PC exists
            if ($pc) {


                // validate form
                if (isset($_POST['Pcs']['Employee_Name']) && strval($_POST['Pcs']['Employee_Name'])!=''){
                    $pc->Employee_Name = $_POST['Pcs']['Employee_Name'];
                }

                if (isset($_POST['Pcs']['Envelope_Total']) && strval($_POST['Pcs']['Envelope_Total'])!=''){
                    $pc->Envelope_Total = $_POST['Pcs']['Envelope_Total'];
                }

                if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/',  $_POST['Pcs']['Envelope_Date'])) {
                    $pc->Envelope_Date = $_POST['Pcs']['Envelope_Date'];
                }


                $is_valid = $pc->validate();

                if ($is_valid ) {

                    // begin transaction
                    $transaction = Yii::app()->db->beginTransaction();
                    try {
                        $pc->Envelope_Number=0;
                        $pc->save();
                        //LibraryDocs::addDocumentToFolder($document->Document_ID);

                        $transaction->commit();
                        echo "Success";
                    } catch(Exception $e) {
                        $transaction->rollback();
                        echo "Error";
                    }



                }
            }
        }
    }

    public function actionAjaxPayrSave()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['payroll_data_entry_form_values'])) {
            $payrollId = intval($_POST['Payrolls']['Payroll_ID']);
            $payroll = Payrolls::model()->findByPk($payrollId);

            //convert date string to server format
            $_POST['Payrolls']['Week_Ending'] = Helper::checkDate($_POST['Payrolls']['Week_Ending']);


            // if Payroll exists
            if ($payroll) {


                if (isset($_POST['Payrolls']['Payroll_Type_ID']) && strval($_POST['Payrolls']['Payroll_Type_ID'])!=''){
                    $payroll->Payroll_Type_ID = $_POST['Payrolls']['Payroll_Type_ID'];
                }

                if (isset($_POST['Payrolls']['Submitted']) && strval($_POST['Payrolls']['Submitted'])!=''){
                    $payroll->Submitted = $_POST['Payrolls']['Submitted'];
                }

                if (isset($_POST['Payrolls']['Version']) && strval($_POST['Payrolls']['Version'])!=''){
                    $payroll->Version = $_POST['Payrolls']['Version'];
                }


                // validate form
                $is_valid = $payroll->validate();



                if ($is_valid ) {
                    // begin transaction
                    $transaction = Yii::app()->db->beginTransaction();
                    try {
                        $payroll->Week_Ending= null;
                        $payroll->save();
                    //    LibraryDocs::addDocumentToFolder($document->Document_ID);

                        $transaction->commit();
                    } catch(Exception $e) {
                        $transaction->rollback();
                    }

                    $saved = true;
                }
            }
        }
    }

    public function actionAjaxJESave($page = 1)
    {

        // form processing
        if (Yii::app()->request->isAjaxRequest && isset($_POST['je_data_entry_form_values'])) {

            $jeId = intval($_POST['Journals']['JE_ID']);
            $je = Journals::model()->findByPk($jeId);

            //convert date string to server format
            $_POST['Journals']['JE_Date'] = Helper::checkDate($_POST['Journals']['JE_Date']);

            // if JE exists
            if ($je) {

                /*if (isset($_POST['Journals']['JE_Number']) && intval($_POST['Journals']['JE_Number'])!=0){
                    $je->JE_Number = $_POST['Journals']['JE_Number'];
                }*/

                if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/',  $_POST['Journals']['JE_Date'])) {
                    $je->JE_Date = $_POST['Journals']['JE_Date'];
                }

                if (isset($_POST['Journals']['JE_Transaction_ID']) && intval($_POST['Journals']['JE_Transaction_ID'])!=0){
                    $je->JE_Transaction_ID = $_POST['Journals']['JE_Transaction_ID'];
                }

                if (isset($_POST['Journals']['JE_Desc']) && strval($_POST['Journals']['JE_Desc'])!=''){
                    $je->JE_Desc = $_POST['Journals']['JE_Desc'];
                }



                // validate form
                $is_valid = $je->validate();



                if ($is_valid ) {
                    // begin transaction
                    $transaction = Yii::app()->db->beginTransaction();
                    try {

                        $je->JE_Number=0;
                        $je->save();
                      //  LibraryDocs::addDocumentToFolder($document->Document_ID);

                        $transaction->commit();
                    } catch(Exception $e) {
                        $transaction->rollback();
                    }

                    $saved = true;
                }
            }
        }
    }

    public function actionAjaxARSave()
    {
        // form processing
        if (Yii::app()->request->isAjaxRequest && isset($_POST['ar_data_entry_form_values'])) {
            $arId = intval($_POST['Ars']['AR_ID']);
            $ar = Ars::model()->findByPk($arId);

            //convert date string to server format
            $_POST['Ars']['Invoice_Date'] = Helper::checkDate($_POST['Ars']['Invoice_Date']);

            // if AR exists
            if ($ar) {




                if (isset($_POST['Ars']['Company_Name']) && strval($_POST['Ars']['Company_Name'])!=''){
                    $ar->Company_Name = $_POST['Ars']['Company_Name'];
                }
               /* if (isset($_POST['Ars']['Invoice_Number']) && intval($_POST['Ars']['Invoice_Number'])!=0){

                    $ap= Aps::model()->findAllByAttributes(array(
                        'Invoice_Number'=>intval($_POST['Ars']['Invoice_Number']),
                        )
                    );

                    if ($ap) {
                    $ar->Invoice_Number = $_POST['Ars']['Invoice_Number'];
                    }
                }*/

                if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/',  $_POST['Ars']['Invoice_Date'])) {
                    $ar->Invoice_Date = $_POST['Ars']['Invoice_Date'];
                }

                if (isset($_POST['Ars']['Invoice_Amount']) && intval($_POST['Ars']['Invoice_Amount'])!=0){
                    $ar->Invoice_Amount = $_POST['Ars']['Invoice_Amount'];
                }

                if (isset($_POST['Ars']['Description']) && strval($_POST['Ars']['Description'])!=''){
                    $ar->Description = $_POST['Ars']['Description'];
                }
                if (isset($_POST['Ars']['Terms']) && strval($_POST['Ars']['Terms'])!=''){
                    $ar->Terms = $_POST['Ars']['Terms'];
                }
                // validate form
                $is_valid = $ar->validate();

                if ($is_valid ) {
                    // begin transaction
                    $transaction = Yii::app()->db->beginTransaction();
                    try {
                        $ar->Invoice_Number=0;
                        $ar->save();

                        //LibraryDocs::addDocumentToFolder($document->Document_ID);

                        $transaction->commit();
                    } catch(Exception $e) {
                        $transaction->rollback();
                    }

                    $saved = true;
                }
            }
        }}


}