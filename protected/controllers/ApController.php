<?php

class ApController extends Controller
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
                'actions'=>array('clearaptoreviewsession'),
                'users'=>array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('index', 'detail', 'getlistbysearchquery', 'getcompanyinfo', 'addapitemstosession', 'addnote',
                                 'printdocument', 'setdocidtoprintdocument', 'getapprogress', 'approveaps', 'returnap', 'setmarkeditems',
                                 'addsearchquerytosession', 'addfiletouploadsession', 'GetDocumentFile', 'uploadfile', 'create',
                                 'changedocumenttype', 'getvendorinfoblock', 'addsessionpointertoapprove', 'checkvendorw9','AddSessionPointerToReview',
                                 'GetNextBlockBySearchquery','HardApsApprove','MarkAsVoid','CreateForPreview'),
                'expression'=>function() {
                    $users = array('admin', 'user', 'approver', 'processor', 'db_admin', 'client_admin');
                    $clientID = isset(Yii::app()->user->clientID) ? Yii::app()->user->clientID : 0;
                    $companyServiceLevel = ClientServiceSettings::getClientServiceSettings($clientID);
                    $tier_settings = isset(Yii::app()->user->tier_settings) ? Yii::app()->user->tier_settings : null ;//array of aggregated settings for current user
                    if (isset(Yii::app()->user->id)
                        && in_array(Yii::app()->user->id, $users)
                        && $companyServiceLevel
                        && $tier_settings['ap']
                        && $companyServiceLevel->Active_To >= date('Y-m-d')) {
                        return true;
                    }
                    return false;
                },
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('recreate'),
                'expression'=>function() {
                        $users = array('db_admin');
                        $clientID = isset(Yii::app()->user->clientID) ? Yii::app()->user->clientID : 0;
                        $companyServiceLevel = ClientServiceSettings::getClientServiceSettings($clientID);
                        $tier_settings = isset(Yii::app()->user->tier_settings) ? Yii::app()->user->tier_settings : null ;//array of aggregated settings for current user
                        if (isset(Yii::app()->user->id)
                            && in_array(Yii::app()->user->id, $users)
                            && $companyServiceLevel
                            && $tier_settings['po']
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
     * APs list
     */
    public function actionIndex()
	{
        //var_dump($_POST);die;
        if (!isset($_SESSION['last_aps_list_search']['query'])) {
            $_SESSION['last_aps_list_search']['query'] = '';
        }

        if (!isset($_SESSION['limiter'])) {
                $limit=Aps::DISPLAY_LIMIT;

        } else {$limit=$_SESSION['limiter'];}


        //check if we need to change client/project
        $cid = intval($_GET['cid']);
        $pid = intval($_GET['pid']);
        $uid = intval($_GET['uid']);
        $client_change_array = Helper::checkUserClientProjectToSwitch($cid,$pid,$uid);

        $searchQuery = $_SESSION['last_aps_list_search']['query'];
        $checkedTBABox = 1;

        //get APs list
        if (isset($_SESSION['last_aps_list_search']['options']) && count($_SESSION['last_aps_list_search']['options']) > 0) {
            $to_be_approved_count=Aps::getLastAps(0, true);
            $apList = Aps::getListByQueryString($_SESSION['last_aps_list_search']['query'], $_SESSION['last_aps_list_search']['options'], $_SESSION['last_aps_list_search']['sort_options'],$limit);
            $_SESSION['last_aps_list_search']['query'] = '';
            $_SESSION['last_aps_list_search']['sort_options'] = array();
            $_SESSION['ap_to_review'] = array();
        } else {
            // get items to approve
            $apList = Aps::getLastAps(0, true);
            $to_be_approved_count=$apList;
            if (!$apList) {
                // if no items to approve, get last items in quantity that defined  in Aps::DISPLAY_LIMIT
                $apList = Aps::getLastAps($limit);
                $checkedTBABox = 0;
            }
        }

        //get last APs' notes
        $notes = Aps::getAPsNotes($apList);

        // define if buttons must be active or not
        $approvalButtonsClass = Aps::getApprovalButtonsClass($apList);

        $userSettings = UsersSettings::model()->findByAttributes(array(
            'User_ID' => Yii::app()->user->userID,
        ));


        $this->render('index', array(
            'apList' => $apList,
            'to_be_approved_count'=>$to_be_approved_count,
            'notes' => $notes,
            'approvalButtonsClass' => $approvalButtonsClass,
            'searchQuery' => $searchQuery,
            'checkedTBABox' => $checkedTBABox,
            'userSettings' => $userSettings,
            'client_change_array'=>$client_change_array
        ));
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




            $apList = Aps::getListByQueryString($queryString, $options, $sortOptions, $limit,$offset);

            $result['count']=count($apList);
            $result['html']=$this->renderPartial('application.views.ap.aplist', array(
                    'apList' => $apList,

                ),true
            );



            echo CJSON::encode($result);


        }
    }


    /**
     * AP detail page action
     * @param int $page
     */
    public function actionDetail($page = 1)
    {

        $cs = Yii::app()->getClientScript();

        $cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/jupload/vendor/jquery.ui.widget.js');
        $cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/jupload/jquery.iframe-transport.js');
        $cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/jupload/jquery.fileupload.js');
        $cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/jupload/jquery.fileupload-process.js');

        // form processing
        $show_ap_detail_box = false;
        $show_dists_box = false;
        $editAp = false;
        $dists = array();
        $relatedPONumber = '';
        $relatedPOError = '';

        if (isset($_POST['Aps'])) {
            $apId = intval($_POST['Aps']['AP_ID']);
            $ap = Aps::model()->with('document')->findByPk($apId);

            // if ap exists
            if ($ap) {
                //convert date string to server format
                $_POST['Aps']['Invoice_Date'] = Helper::checkDate($_POST['Aps']['Invoice_Date']);
                $_POST['Aps']['Invoice_Due_Date'] = Helper::checkDate($_POST['Aps']['Invoice_Due_Date']);

                $prevAP = clone $ap;
                $ap->attributes = $_POST['Aps'];
                $relatedPONumber = $_POST['po_number'];

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

                $document = $ap->document;
                if ($relatedPONumber != '' && $ap->Vendor_ID != '0') {
                    // get po
                    $condition = new CDbCriteria();
                    $condition->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID";
                    $condition->condition = "t.PO_Number = '$relatedPONumber'";
                    $condition->addCondition("t.Vendor_ID = '" . $ap->Vendor_ID . "'");
                    $condition->addCondition("documents.Project_ID = '" . $document->Project_ID . "'");
                    $po = Pos::model()->find($condition);

                    if (!$po) {
                        $relatedPOError = 'PO does not exist';
                    } else {
                        $outBalance = Pos::checkPOBalance($po->PO_ID, $ap->Invoice_Amount);
                        if ($outBalance > 0) {
                            $relatedPOError = 'This AP can not be attached to PO. Balance will be out of by $' . number_format($outBalance, 2);
                        }
                    }
                }

                // if form is valid - save data
                if ($is_valid && $ap->Invoice_Number != '' && $ap->Invoice_Amount != '' && $ap->Invoice_Date != '' && $ap->Invoice_Reference != ''
                    && $ap->Vendor_ID != '0' && $ap->Invoice_Number != '0' && $relatedPOError == '') {

                    if ($ap->AP_Approval_Value == Aps::NOT_READY_FOR_APPROVAL) {
                        $ap->AP_Approval_Value = Aps::READY_FOR_APPROVAL;
                        $ap->Previous_AP_A_Val = Aps::READY_FOR_APPROVAL;
                    }

                    if ($ap->Invoice_Due_Date == '') {
                        $ap->Invoice_Due_Date = null;
                    }

                    $previousPoId = $ap->PO_ID;

                    if (isset($po) && $po !== null) {
                        $ap->PO_ID = $po->PO_ID;
                    } else {
                        $ap->PO_ID = 0;
                    }

                    $ap->save();

                    if ($relatedPONumber != '') {
                        Pos::addApPORelation($po, $ap->Invoice_Amount, $ap->Invoice_Date, $ap->Invoice_Number, $ap->Invoice_Reference);
                    }

                    Pos::removeApPORelation($previousPoId, $prevAP->Invoice_Amount, $prevAP->Invoice_Date, $prevAP->Invoice_Number);


                    Yii::app()->user->setFlash('success', "Changes Saved!");
                } else {
                    $show_ap_detail_box = true;
                    $editAp = $ap;
                }
            }
        }


        // check AP to review
        if (!isset($_SESSION['ap_to_review']) || count($_SESSION['ap_to_review']) == 0) {
            $_SESSION['ap_to_review'] = Aps::getAPsToApproveToSession();
            if (!isset($_SESSION['ap_to_review']) || count($_SESSION['ap_to_review']) == 0) {
                if (isset($_SESSION['last_aps_list_search']['options']) && count($_SESSION['last_aps_list_search']['options']) > 0 && $_SESSION['last_aps_list_search']['options']['search_option_to_be_approved'] == 1) {
                    $_SESSION['last_aps_list_search']['query'] = '';
                    $_SESSION['last_aps_list_search']['sort_options'] = array();
                    $_SESSION['ap_to_review'] = array();
                    $_SESSION['last_aps_list_search']['options'] = array();
                }
                Yii::app()->user->setFlash('success', "Please choose APs to review!");
                $this->redirect('/ap');
            }
        }

        $page = intval($page);
        $num_pages = count($_SESSION['ap_to_review']);
        if ($page <= 0) {
            $page = 1;
        } else if ($page > $num_pages) {
            $page = $num_pages;
        }

        $docId = $_SESSION['ap_to_review'][$page];

        $ap = Aps::model()->with('vendor', 'document', 'payments', 'dists')->findByAttributes(array(
            'Document_ID' => $docId,
        ));

        if (!$relatedPONumber && $ap->PO_ID > 0) {
            $relatedPO = Pos::model()->findByPk($ap->PO_ID);
            if ($relatedPO) {
                $relatedPONumber = $relatedPO->PO_Number;
            }
        }


        // if isset Dists form
        $invalidDistsSum = '';
        if (isset($_POST['Dist'])) {
            $dists = $_POST['Dist'];
            $totalDistSum = 0;
            $distsToDB = array();

            // get valid dists
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

            if (count($distsToDB) > 0 && $ap->Invoice_Amount != $totalDistSum && $invalidDistsSum == '') {
                $invalidDistsSum = "The detail of GL Dists is not in balance with the Inv. Amount.
                                    You're out by $" . abs($totalDistSum - $ap->Invoice_Amount);
            }

            if ($invalidDistsSum == '' && ((count($distsToDB) == 0) || ($ap->Invoice_Amount == $totalDistSum && count($distsToDB) > 0))) {
                // write dists to DB
                GlDistDetails::saveAPDists($ap->AP_ID, $distsToDB);
                Yii::app()->user->setFlash('success', "Distributions have been saved!");
                $ap = Aps::model()->with('vendor', 'document', 'payments', 'dists')->findByAttributes(array(
                    'Document_ID' => $docId,
                ));
            } else {
                $show_dists_box = true;
            }
        }

        // define if AP must be approved by user
        $mustBeApproved = false;

        // get user approval range
        $userApprovalRange = Aps::getUserApprovalRange();
        if ($ap->AP_Approval_Value >= $userApprovalRange['prev_user_appr_val'] &&
            $ap->AP_Approval_Value < $userApprovalRange['user_appr_val']) {
            $mustBeApproved = true;
        }

        //used for documents that are being opened fro ApprovalCue list
        if ($_SESSION['ap_hard_approve'][$page]==$docId) {
            $hard_approval = true;
        }

        $company = '';
        $vendor = $ap->vendor;
        if ($vendor) {
            $client = $vendor->client;
            $company = $client->company;
        }

        $document = $ap->document;
        $user = $document->user;
        $payments = $ap->payments;

        if (!isset($_POST['Dist'])) {
            $i = 1;
            foreach ($ap->dists as $dist) {
                $dists[$i] = array(
                    'GL_Dist_Detail_COA_Acct_Number' => $dist->GL_Dist_Detail_COA_Acct_Number,
                    'GL_Dist_Detail_Desc' => $dist->GL_Dist_Detail_Desc,
                    'GL_Dist_Detail_Amt' => $dist->GL_Dist_Detail_Amt,
                );
                $i++;
            }
            for($i = count($dists) + 1; $i <= 4; $i++) {
                $dists[$i] = array(
                    'GL_Dist_Detail_COA_Acct_Number' => '',
                    'GL_Dist_Detail_Desc' => '',
                    'GL_Dist_Detail_Amt' => '',
                );
            }
        }

        if (is_array($payments) && count($payments) > 0) {
            $paymentCheckNumber = $payments[0]->Payment_Check_Number;
        } else {
            $paymentCheckNumber = '';
        }

        // get document's file
        $condition = new CDbCriteria();
        $condition->select = 'Mime_Type';
        $condition->condition = "Document_ID='" . $document->Document_ID . "'";
        $file = Images::model()->find($condition);

        // get notes
        $notes = Notes::model()->getDocumentNotes($ap->Document_ID);

        $enableEditing = ($userApprovalRange['user_appr_val'] >= $ap->AP_Approval_Value) && ($ap->Approved == 0);

        // get client vendors
        $vendorsCP = array();
        if ($enableEditing) {
            $vendorsCP = Vendors::getClientVendorsShortcutList($document->Client_ID);
        }

        if (!$editAp) {
            $editAp = $ap;
        }

        // get PO Back Up
        $backUp = Documents::getBackupDoc($ap->AP_Backup_Document_ID, false);

        $check = array();
        if (is_array($payments) && count($payments) > 0) {
            $check['payment'] = $payments[0];
            $check['image'] = Images::model()->findByAttributes(array(
                'Document_ID' => $payments[0]->Document_ID,
            ));
        }

        $this->render('detail', array(
            'page' => $page,
            'num_pages' => $num_pages,
            'company' => $company,
            'ap' => $ap,
            'user' => $user,
            'document' => $document,
            'notes' => $notes,
            'file' => $file,
            'paymentCheckNumber' => $paymentCheckNumber,
            'mustBeApproved' => $mustBeApproved,
            'hard_approval'=>$hard_approval,
            'userApprovalRange' => $userApprovalRange,
            'enableEditing' => $enableEditing,
            'vendorsCP' => $vendorsCP,
            'show_ap_detail_box' => $show_ap_detail_box,
            'editAp' => $editAp,
            'check' => $check,
            'backUp' => $backUp,
            'dists' => $dists,
            'show_dists_box' => $show_dists_box,
            'invalidDistsSum' => $invalidDistsSum,
            'relatedPONumber' => $relatedPONumber,
            'relatedPOError' => $relatedPOError,
        ));
    }

    /**
     * Create AP from input data
     * @param int $id
     */
    public function actionCreate($id = 0)
    {

        $cs = Yii::app()->getClientScript();
        $cs->registerCssFile(Yii::app()->request->baseUrl.'/css/jupload/css/jquery.fileupload.css');
        $cs->registerCssFile(Yii::app()->request->baseUrl.'/css/jupload/css/jquery.fileupload-ui.css');

        $cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/jupload/vendor/jquery.ui.widget.js');
        $cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/jupload/jquery.iframe-transport.js');
        $cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/jupload/jquery.fileupload.js');
        $cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/jupload/jquery.fileupload-process.js');



        if (Yii::app()->user->projectID == 'all') {
            Yii::app()->user->setFlash('success', "Please select a specific Project for this process.");
            $this->redirect('/ap');
        }

        // set AP_ID
        $apId = intval($id);

        // initiate variables
        $dists = array();
        for($i = 1; $i <= 6; $i++) {
            $dists[$i] = array(
                'GL_Dist_Detail_COA_Acct_Number' => '',
                'GL_Dist_Detail_Amt' => '',
                'GL_Dist_Detail_Desc' => '',
            );
        }

        // check access to staging PO
        if ($apId != 0 && !Aps::hasStagingAPAccess($apId)) {
            $this->redirect('/ap/create');
        }

        if ($apId == 0) {
            $ap = new Aps();
            $ap->Invoice_Date = date('Y-m-d');
            //$ap->Invoice_Date = Helper::clientDateToDatabase(date('Y-m-d')); ????????
            $ckReqDet = new CkReqDetails();
            $ap->Invoice_Number = Aps::getNewAPNumber(Yii::app()->user->clientID,Yii::app()->user->projectID);
            $ckReqDet->Sign_Requested_By = Yii::app()->user->userID;
        } else {
            $ap = Aps::model()->with('dists', 'document', 'ck_req_detail')->findByPk($apId);
            $ckReqDet =  $ap->ck_req_detail;
            $apDists = $ap->dists;

            // set dists
            $result= GlDistDetails::getAPDists($ap->AP_ID);
            $dists = $result ['dists'];
        }

        // process creation AP
        if (isset($_POST['Aps'])) {
            //convert date string to server format
            $_POST['Aps']['Invoice_Due_Date'] = Helper::checkDate($_POST['Aps']['Invoice_Due_Date']);
            $_POST['CkReqDetails']['Rental_Begin'] = Helper::checkDate($_POST['CkReqDetails']['Rental_Begin']);
            $_POST['CkReqDetails']['Rental_End'] = Helper::checkDate($_POST['CkReqDetails']['Rental_End']);

            $ap->attributes = $_POST['Aps'];
            $ckReqDet->attributes = $_POST['CkReqDetails'];

            // init variables
            $distsToSave = array();

            // set dists
           $distsToSave = GlDistDetails::prepareDistsToSave($_POST['GlDistDetails']);

            if (!$ap->AP_Backup_Document_ID) {
                if (isset($_SESSION['last_uploaded_backup'])) {
                    $ap->AP_Backup_Document_ID = $_SESSION['last_uploaded_backup'];
                    unset($_SESSION['last_uploaded_backup']);
                } else {
                    $ap->AP_Backup_Document_ID = 0;
                }

            }

            if (!$ap->Document_ID) {
                $ap->Document_ID = 0;
            }

            $ap->AP_Approval_Value = Aps::NOT_READY_FOR_APPROVAL;

            $ap->Detail_1099 = Aps::DO_NOT_NEED_1099;
            if ($ap->Vendor_ID != 0) {
                $vendor = Vendors::model()->with('client')->findByPk($ap->Vendor_ID);
                $vendorW9 = W9::getCompanyW9($vendor->client->Client_ID);
                if ($vendorW9) {
                    if (!($vendorW9->Tax_Class == 'C' || $vendorW9->Tax_Class == 'CC'
                        || $vendorW9->Tax_Class == 'CS')) {
                        $ap->Detail_1099 = Aps::NEED_1099;
                        if (!$ap->Detail_1099_Box_Number) {
                            $ap->Detail_1099_Box_Number = Aps::NEED_1099_DEFAULT_NUMBER;
                        }
                    } else {
                        $ap->Detail_1099_Box_Number = Aps::DO_NOT_NEED_1099_DEFAULT_NUMBER;
                    }
                }
            }

            if ($_POST['purchase_rental'] == 1) {
                $ckReqDet->CK_Req_Purchase = 0;
                $ckReqDet->CK_Req_Rental = 1;
            } else {
                $ckReqDet->CK_Req_Purchase = 1;
                $ckReqDet->CK_Req_Rental = 0;
            }

            $ckReqDet->AP_ID = 0;
            // validate and save
            if ($ap->validate() && $ckReqDet->validate()) {
                if ($ap->Document_ID == 0) {
                    $document = new Documents();
                    $document->Document_Type = Documents::AP;
                    $document->Origin = 'G';
                    $document->User_ID = Yii::app()->user->userID;
                    $document->Client_ID = Yii::app()->user->clientID;
                    $document->Project_ID = Yii::app()->user->projectID;
                    $document->Created = date("Y-m-d H:i:s");
                    $document->save();

                    Audits::LogAction($document->Document_ID , Audits::ACTION_CREATION);

                    $ap->Document_ID = $document->Document_ID ;
                }

                // begin transaction
                $transaction = Yii::app()->db->beginTransaction();
                try {

                    $ap->save();

                    $ckReqDet->AP_ID = $ap->AP_ID;
                    $ckReqDet->save();

                    //save dists
                    GlDistDetails::saveAPDists($ap->AP_ID, $distsToSave);

                    $transaction->commit();
                } catch (Exception $e) {
                    $transaction->rollback();
                }


                if (!isset($_SESSION['ap_to_approve'])) {
                    Yii::app()->user->setFlash('success', "CHRQ has been successfully saved. Click Send To Aprv. again to get request into the Approval cue!");
                }
                $this->redirect('/ap/create/' . $ap->AP_ID);
                die;
            }
        }

        $poFormatting = PoFormatting::model()->findByAttributes(array(
            'Project_ID' => Yii::app()->user->projectID,
        ));

        $backUp = Documents::getBackupDoc($ap->AP_Backup_Document_ID);

        // get stading items
        $stagingItems = Aps::getStagingItems();

        // get client approvers
        $clientApprovers = UsersClientList::getClientApprovers();

        // get client vendors
        $vendors = Vendors::getClientVendorsShortcutList(Yii::app()->user->clientID);

        // get current vendor info
        $currentVendor = null;
        $vendorAdmin = null;
        if ($ap->Vendor_ID != 0) {
            $currentVendor = Vendors::model()->with('client.company.adreses')->findByPk($ap->Vendor_ID);

            $condition = UsersClientList::getClientAdminCondition($currentVendor->client->Client_ID);
            $vendorAdmin = UsersClientList::model()->with('user.person')->find($condition);
        }

        // get Sign_Requested_By user info
        $signRequestedByUser = Users::model()->with('person')->findByPk($ckReqDet->Sign_Requested_By);


        $distsError = '';
        $hasErrors = false;
        // generate AP pdf and send to approve
        if (isset($_SESSION['ap_to_approve']) && $_SESSION['ap_to_approve'] == $apId) {
            //check po fields
            if ($ap->Vendor_ID == '0') {
                $ap->addError('Vendor_ID','Choose Vendor!');
                $hasErrors = true;
            }

            if ($ap->Invoice_Amount == '0' || $ap->Invoice_Amount == null || $ap->Invoice_Amount == '') {
                $ap->addError('Invoice_Amount','Amount is required!');
                $hasErrors = true;
            }

            if ($ap->Invoice_Reference == '0' || $ap->Invoice_Reference == null || $ap->Invoice_Reference == '') {
                $ap->addError('Invoice_Reference','Description is required!');
                $hasErrors = true;
            }

            /*if ($ckReqDet->Sign_Dept_Approval == '0' || $ckReqDet->Sign_UPM_Executive == '0' || $ckReqDet->Sign_Accounting == '0') {
                $ckReqDet->addError('Sign_Accounting','Choose signers!');
                $hasErrors = true;
            }*/

            if ($ckReqDet->CK_Req_Rental == 1 && !preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $ckReqDet->Rental_Begin)) {
                $ckReqDet->addError('Rental_Begin','Invalid date!');
                $hasErrors = true;
            }

            if ($ckReqDet->CK_Req_Rental == 1 && !preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $ckReqDet->Rental_End)) {
                $ckReqDet->addError('Rental_End','Invalid date!');
                $hasErrors = true;
            }

            // check dists
            if ($apDists) {
                $distsSum = 0;
                foreach ($apDists as $apDist) {
                    $distsSum += $apDist->GL_Dist_Detail_Amt;
                    if ($apDist->GL_Dist_Detail_Amt == '' || $apDist->GL_Dist_Detail_Amt == '0'
                        || $apDist->GL_Dist_Detail_Desc == '' || $apDist->GL_Dist_Detail_Desc == '-'
                        || $apDist->GL_Dist_Detail_COA_Acct_Number == '' || $apDist->GL_Dist_Detail_COA_Acct_Number == '-') {
                        $distsError = 'All fields in populated rows are required';
                        $hasErrors = true;
                    }
                }

                if ($distsSum != $ap->Invoice_Amount) {
                    $distsError = 'The sum of all the distribution line must equal to AMOUNT. The AP is out of balance by $' . abs($distsSum - $ap->Invoice_Amount);
                    $hasErrors = true;
                }
            } else {
                $distsError = 'At least one row of AP distribution is required';
                $hasErrors = true;
            }

            if (!$hasErrors) {
                // get user approval range
                $appr_level_was_changed = 0;

                $approvalRange = Aps::getUserApprovalRange();

                // check approval range and set approval values
                if ($approvalRange['user_appr_val'] == Aps::APPROVED) {
                    $ap->AP_Approval_Value = $approvalRange['user_appr_val'];
                    $ap->Previous_AP_A_Val = $approvalRange['prev_user_appr_val'];
                    $ap->Approved = 1;

                    $appr_level_was_changed=1;

                    LibraryDocs::addDocumentToFolder($ap->Document_ID);
                    Audits::LogAction($ap->Document_ID,Audits::ACTION_LIBRARY);
                } else if ($approvalRange['user_appr_val'] > Aps::READY_FOR_APPROVAL) {
                    $ap->AP_Approval_Value = $approvalRange['user_appr_val'];
                    $ap->Previous_AP_A_Val = $approvalRange['prev_user_appr_val'];
                    $appr_level_was_changed=1;
                } else {
                    $ap->AP_Approval_Value = Aps::READY_FOR_APPROVAL;
                    $ap->Previous_AP_A_Val = Aps::READY_FOR_APPROVAL;
                }

                // begin transaction
                $transaction = Yii::app()->db->beginTransaction();
                try {
                    $ap->save();

                    if ($appr_level_was_changed) Audits::LogAction($ap->Document_ID,Audits::ACTION_APPROVAL);

                    // generate pdf
                    Documents::pdfGeneration($ap->Document_ID,'AP',($approvalRange['user_appr_val'] == Aps::APPROVED));

                    //Audits::LogAction($ap->Document_ID,Audits::ACTION_LIBRARY);

                    $transaction->commit();
                } catch(Exception $e) {
                    $transaction->rollback();
                }


                if ($approvalRange['user_appr_val'] != Aps::APPROVED) {

                    //get next user to approve
                    $userToClient = UsersClientList::model()->findByAttributes(array(
                        'User_ID' => Yii::app()->user->userID,
                        'Client_ID' => Yii::app()->user->clientID,
                    ));

                    $nextUsers = UsersClientList::getNextUserToApprove($approvalRange);
                    Aps::notifyNextUsers($nextUsers,$userToClient);
                }

                Yii::app()->user->setFlash('success', "CKRQ has been successfully generated and sent to Approval cycle!");
                unset($_SESSION['ap_to_approve']);
                $this->redirect('/ap/create');
                die;
            } else {
                Yii::app()->user->setFlash('error', "Missing information. This item cannot be created.Please review the notes in red below.");
            }
            unset($_SESSION['ap_to_approve']);
        } else if (isset($_SESSION['ap_to_approve']) && $_SESSION['ap_to_approve'] != $apId) {
            unset($_SESSION['ap_to_approve']);
        }

        $availableStorage = ClientServiceSettings::getAvailableStorage(Yii::app()->user->clientID);
        $usedStorage = Images::getUsedStorage(Yii::app()->user->clientID);

        $enableCreating = true;
        $disableCreatingMessage = '';
        if ($usedStorage >= $availableStorage && $availableStorage != 0) {
            $enableCreating = false;
            $disableCreatingMessage = 'Your storage is full. You can purchase
                                       more storage space on My Account page - Service Level tab.
                                       Do you want to increase your storage now?';
        }

        $coaStructure = CoaStructure::model()->findByAttributes(array(
            'Project_ID' => Yii::app()->user->projectID,
        ));

        $this->render('create', array(
            'clientApprovers' => $clientApprovers,
            'dists' => $dists,
            'ap' => $ap,
            'poFormatting' => $poFormatting,
            'apId' => $apId,
            'vendors' => $vendors,
            'currentVendor' => $currentVendor,
            'vendorAdmin' => $vendorAdmin,
            'signRequestedByUser' => $signRequestedByUser,
            'stagingItems' => $stagingItems,
            'backUp' => $backUp,
            'distsError' => $distsError,
            'ckReqDet' => $ckReqDet,
            'availableStorage' => $availableStorage,
            'usedStorage' => $usedStorage,
            'enableCreating' => $enableCreating,
            'disableCreatingMessage' => $disableCreatingMessage,
            'coaStructure'=>$coaStructure
        ));
    }

    /**
     * Change document type
     */
    public function actionChangeDocumentType() {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['docType'])) {
            $docType = trim($_POST['docType']);
            if (isset($_SESSION['ap_upload_file']) && ($docType == Documents::BU || $docType == Documents::W9)) {
                $_SESSION['ap_upload_file']['doctype'] = $docType;
                $_SESSION['ap_upload_file']['fed_id'] = '';
                $_SESSION['ap_upload_file']['company_name'] = '';
                if ($docType == Documents::W9) {
                    $_SESSION['ap_upload_file']['complete'] = false;
                } else {
                    $_SESSION['ap_upload_file']['complete'] = true;
                }
            }
        }
    }

    /**
     * Get Vendor Info block
     */
    public function actionGetVendorInfoBlock()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['vendorId'])) {
            $vendorId = intval($_POST['vendorId']);

            // get current vendor info
            $currentVendor = null;
            $vendorAdmin = null;
            if ($vendorId != 0) {
                $currentVendor = Vendors::model()->with('client.company.adreses')->findByPk($vendorId);

                $condition = UsersClientList::getClientAdminCondition($currentVendor->client->Client_ID);
                $vendorAdmin = UsersClientList::model()->with('user.person')->find($condition);
            }

            $this->renderPartial('vendor_info_block', array(
                'currentVendor' => $currentVendor,
                'vendorAdmin' => $vendorAdmin,
            ));
        }
    }

    /**
     * Add file to upload session
     */
    public function actionAddFileToUploadSession()
    {
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
                        if (($extension == 'jpg' && (strpos($mimeType, 'jpeg') !== false || strpos($mimeType, 'jpg') !== false))
                            || ($extension == 'jpeg' && (strpos($mimeType, 'jpeg') !== false || strpos($mimeType, 'jpg') !== false))
                            || ($extension == 'bmp' && strpos($mimeType, 'bmp') !== false)
                            || ($extension == 'gif' && strpos($mimeType, 'gif') !== false)
                            || ($extension == 'png' && strpos($mimeType, 'png') !== false)
                            || ($extension == 'tiff' && (strpos($mimeType, 'tiff') !== false || strpos($mimeType, 'tif') !== false))
                            || ($extension == 'pdf' && strpos($mimeType, 'pdf') !== false)) {

                            $filepath = 'protected/data/current_uploads_files/' . Yii::app()->user->userID . '/'  . date('Y-m-d') . '/' . $_FILES['userfile']['name'];

                            // delete old file
                            if (isset($_SESSION['ap_upload_file']) && file_exists($_SESSION['ap_upload_file']['filepath'])) {
                                @unlink($_SESSION['ap_upload_file']['filepath']);
                            }

                            $_SESSION['ap_upload_file']['name'] = $_FILES['userfile']['name'];
                            $_SESSION['ap_upload_file']['filepath'] = $filepath;
                            $_SESSION['ap_upload_file']['mimetype'] = $_FILES['userfile']['type'];
                            $_SESSION['ap_upload_file']['doctype'] = Documents::BU;
                            $_SESSION['ap_upload_file']['fed_id'] = '';
                            $_SESSION['ap_upload_file']['company_name'] = '';
                            $_SESSION['ap_upload_file']['complete'] = true;

                            move_uploaded_file($_FILES['userfile']['tmp_name'], $_SESSION['ap_upload_file']['filepath']);

                            $detailsPage = false;
                            if (isset($_GET['page'])) {
                                $detailsPage = true;
                            }
                            $this->renderPartial('uploads_block', array (
                                'file' => $_SESSION['ap_upload_file'],
                                'detailsPage' => $detailsPage,
                            ));
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
     * Return necessary file
     */
    public function actionGetDocumentFile()
    {
        if ($_SESSION['ap_upload_file']) {
            $fileData = fread(fopen($_SESSION['ap_upload_file']['filepath'],"rb"),filesize($_SESSION['ap_upload_file']['filepath']));
            header("Content-type: ". $_SESSION['ap_upload_file']['mimetype']);
            echo $fileData;
            die;
        }
    }

    public function actionUploadFile() {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['docType']) && (isset($_POST['apId']) || isset($_POST['docId']))
            && isset($_POST['fed_id']) && isset($_POST['comp_name'])) {


            $result = array(
                'success' => false,
                'back_up_block' => '',
                'vendors_list' => '',
            );

            $apId = isset($_POST['apId']) ? trim($_POST['apId']) : trim($_POST['docId']);
            $fed_id = trim($_POST['fed_id']);
            $comp_name = trim($_POST['comp_name']);
            $docType = trim($_POST['docType']);
            if (isset($_SESSION['ap_upload_file']) && ($docType == Documents::BU || $docType == Documents::W9)) {


                $_SESSION['ap_upload_file']['doctype'] = $docType;
                $_SESSION['ap_upload_file']['fed_id'] = $fed_id;
                $_SESSION['ap_upload_file']['company_name'] = $comp_name;
                $_SESSION['ap_upload_file']['complete'] = true;
                if ($docType == Documents::W9) {
                    $_SESSION['ap_upload_file_w9'][1] = $_SESSION['ap_upload_file'];
                    Documents::uploadDocuments('ap_upload_file_w9', true);
                    $result['success'] = true;
                    $result['vendors_list'] = $this->renderPartial('application.views.po.vendors_list', array(
                        'vendors' => Vendors::getClientVendorsShortcutList(Yii::app()->user->clientID),
                    ), true);
                } else {
                    // begin transaction

                    $transaction = Yii::app()->db->beginTransaction();
                    try {
                        $document = new Documents();
                        $document->Document_Type = $_SESSION['ap_upload_file']['doctype'];
                        $document->User_ID = Yii::app()->user->userID;
                        $document->Client_ID = Yii::app()->user->clientID;
                        $document->Project_ID = Yii::app()->user->projectID;
                        $document->Created = date("Y-m-d H:i:s");
                        $document->save();

                        Audits::LogAction($document->Document_ID ,Audits::ACTION_UPLOAD);

                        // insert image
                        $image = new Images();
                        $imageData = addslashes(fread(fopen($_SESSION['ap_upload_file']['filepath'],"rb"),filesize($_SESSION['ap_upload_file']['filepath'])));
                        $image->Document_ID = $document->Document_ID;
                        $image->Img = $imageData;
                        $image->File_Name = $_SESSION['ap_upload_file']['name'];
                        $image->Mime_Type = $_SESSION['ap_upload_file']['mimetype'];
                        $image->File_Hash = sha1_file($_SESSION['ap_upload_file']['filepath']);
                        $image->File_Size = intval(filesize($_SESSION['ap_upload_file']['filepath']));
                        $image->Pages_Count = FileModification::calculatePagesByPath($_SESSION['ap_upload_file']['filepath']);
                        $image->save();

                        $transaction->commit();
                    } catch(Exception $e) {
                        $transaction->rollback();
                    }

                    // delete file from temporary catalog
                    unlink($_SESSION['ap_upload_file']['filepath']);

                    if ($apId == 0) {
                        $_SESSION['last_uploaded_backup'] = $document->Document_ID;
                    } else if(Aps::hasStagingAPAccess($apId) || Aps::hasAPAccess($apId)) {
                        $ap = Aps::model()->findByPk($apId);
                        $ap->AP_Backup_Document_ID = $document->Document_ID;
                        $ap->save();
                        if (isset($_SESSION['last_uploaded_backup'])) {
                            unset($_SESSION['last_uploaded_backup']);
                        }

                        if ($ap->AP_Approval_Value == 100) {
                            LibraryDocs::addDocumentToFolder($document->Document_ID, $ap->Vendor_ID);
                        }
                    }

                    $result['success'] = true;
                    $result['back_up_block'] = $this->renderPartial('application.views.ap.tabs.back_up', array(
                        'backUp' => array(
                            'file' => $image,
                            'document' => $document,
                        ),
                    ), true);
                }
            }

            if (file_exists($_SESSION['ap_upload_file']['filepath'])) {
                @unlink($_SESSION['ap_upload_file']['filepath']);
            }
            unset($_SESSION['ap_upload_file']);

            echo CJSON::encode($result);
        }
    }

    /**
     * Add AP items to session action
     */
    public function actionAddAPItemsToSession()
    {
        if (isset($_POST['documents'])) {
            $_SESSION['ap_to_review'] = array();
            $i = 1;
            foreach ($_POST['documents'] as $docId) {
                $docId = intval($docId);
                if ($docId > 0 && Documents::hasAccess($docId)) {
                    $document = Documents::model()->with('client')->findByAttributes(array(
                        'Client_ID' => Yii::app()->user->clientID,
                        'Document_ID' => $docId,
                    ));
                    $ap = Aps::model()->findByAttributes(array(
                        'Document_ID' => $docId,
                    ));
                    if ($document && $ap) {
                        $_SESSION['ap_to_review'][$i] = $docId;
                        $i++;
                    }
                }
            }

            $this->redirect('/ap/detail');
        }
    }

    /**
     * Clear $_SESSION['ap_to_review'] if we go to details page directly
     */
    public function actionClearApToReviewSession()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['clear'])) {
            $_SESSION['ap_to_review'] = array();
            $_SESSION['last_aps_list_search']['query'] = '';
            $_SESSION['last_aps_list_search']['options'] = array();
            $_SESSION['last_aps_list_search']['sort_options'] = array();
        }
    }



    /**
     * Get APs list search query action
     */

    public function actionGetListBySearchQuery()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['query'])) {
            $apList = array();
            $batchedMode = false;
            if (!isset($_SESSION['marked_aps'])) {
                $_SESSION['marked_aps'] = array();
            }

            // set query params
            $queryString = trim($_POST['query']);
            $options = array(
                'search_option_to_be_batched' => intval($_POST['search_option_to_be_batched']),
                'search_option_to_be_approved' => intval($_POST['search_option_to_be_approved']),

                'search_option_com_name' => intval($_POST['search_option_com_name']),
                'search_option_fed_id' => intval($_POST['search_option_fed_id']),
                'search_option_addr1' => intval($_POST['search_option_addr1']),
                'search_option_addr2' => intval($_POST['search_option_addr2']),
                'search_option_city' => intval($_POST['search_option_city']),
                'search_option_state' => intval($_POST['search_option_state']),
                'search_option_zip' => intval($_POST['search_option_zip']),
                'search_option_country' => intval($_POST['search_option_country']),
                'search_option_phone' => intval($_POST['search_option_phone']),
                'search_option_batch' => intval($_POST['search_option_batch']),
                'search_option_invoice_number' => intval($_POST['search_option_invoice_number']),
                'search_option_invoice_amount' => intval($_POST['search_option_invoice_amount']),
                'search_option_date' => intval($_POST['search_option_date']),
                'search_option_limit' => intval($_POST['search_option_limit']),

            );

            if(!$options['search_option_limit']) {
                $limit=0;
            } else {
                $limit=Aps::DISPLAY_LIMIT;
            }

            $sortOptions = array(
                'sort_by' => $_POST['sort_type'],
                'sort_direction' => $_POST['sort_direction'],
            );

            if ($options['search_option_to_be_batched'] && Yii::app()->user->projectID === 'all') {
                $sortOptions = array(
                    'sort_by' => 'documents.Project_ID asc, '.$_POST['sort_type'],
                    'sort_direction' => $_POST['sort_direction'],
                );
                $batchedMode = true;
            }

            // get APs list
            $apList = Aps::getListByQueryString($queryString, $options, $sortOptions,$limit);

            //get last APs' notes
            $notes = Aps::getAPsNotes($apList);

            $apsList = $this->renderPartial('aplist', array(
                'apList' => $apList,
                'notes' => $notes,
                'markSelctd' => $_POST['mark_selected'],
                'batchedMode' =>$batchedMode
            ), true);

            // define if buttons must be active or not
            $approvalButtonsClass = Aps::getApprovalButtonsClass($apList);

            $result = array(
                'count' => count($apList),
                'html' => $apsList,
                'btnsClass' => $approvalButtonsClass,
            );

            echo CJSON::encode($result);
        }
    }

    /**
     * Add search query to session
     */
    public function actionAddSearchQueryToSession()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['query'])) {
            // set query params
            $queryString = trim($_POST['query']);
            $options = array(
                'search_option_to_be_approved' => intval($_POST['search_option_to_be_approved']),
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

            $sortOptions = array(
                'sort_by' => $_POST['sort_type'],
                'sort_direction' => $_POST['sort_direction'],
            );

            // set last search query params to session
            $_SESSION['last_aps_list_search']['query'] = $queryString;
            $_SESSION['last_aps_list_search']['options'] = $options;
            $_SESSION['last_aps_list_search']['sort_options'] = $sortOptions;
        }
    }

    /**
     * Get company info to sidebar
     */
    public function actionGetCompanyInfo()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['docId'])) {
            $docId = intval($_POST['docId']);
            if ($docId > 0) {
                $ap = Aps::model()->with('vendor', 'document', 'payments')->findByAttributes(array(
                    'Document_ID' => $docId,
                ));


                $company = null;

                $vendor = $ap->vendor;
                if ($vendor) {
                    $client = $vendor->client;
                    $company = $client->company;
                }
                $document = $ap->document;
                $user = $document->user;
                $payments = $ap->payments;

                if (is_array($payments) && count($payments) > 0) {
                    $paymentCheckNumber = $payments[0]->Payment_Check_Number;
                } else {
                    $paymentCheckNumber = '';
                }
                //var_dump($ap);die;
                $html=$this->renderPartial('company_info_block', array(
                    'ap' => $ap,
                    'company' => $company,
                    'user' => $user,
                    'document' => $document,
                    'paymentCheckNumber' => $paymentCheckNumber,
                ));
            }

        }
    }

    /**
     * Set marked items to session
     */
    public function actionSetMarkedItems()
    {
        if (Yii::app()->request->isAjaxRequest && (isset($_POST['docs']) || isset($_POST['clearList']))) {
            if (isset($_POST['docs'])) {
                $_SESSION['marked_aps'] = $_POST['docs'];
            }
            if ($_POST['clearList'] == 1) {
                $_SESSION['marked_aps'] = array();
            }
            echo 1;
        }
    }

    /**
     * Approve necessary items
     */
    public static function actionApproveAps()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['docs'])) {
            // get user approval range
            $userApprovalRange = Aps::getUserApprovalRange();

            if (is_array($_POST['docs'])) {

                $all_amount=count($_POST['docs']);
                $i=0;
                $pb= ProgressBar::init();

                foreach($_POST['docs'] as $docId) {

                    $docId = intval($docId);
                    //approve document
                    Aps::approveAP($docId, $userApprovalRange);


                    /**
                     * * Next block used for progress bar animation only
                     */
                    $i++;
                    $percent=intval($i/$all_amount*100);
                    session_start();
                    $_SESSION['progress']=$percent;
                    session_write_close();
                    //end of block

                }
            } else {
                $docId = intval($_POST['docs']);
                //approve document
                Aps::approveAP($docId, $userApprovalRange);

            }

            // get user approval value
            $userToClient = UsersClientList::model()->findByAttributes(array(
                'User_ID' => Yii::app()->user->userID,
                'Client_ID' => Yii::app()->user->clientID,
            ));

            //get next user to approve
            $nextUsers = UsersClientList::getNextUserToApprove($userApprovalRange);
            Aps::notifyNextUsers($nextUsers,$userToClient);


            Yii::app()->user->setFlash('success', "APs have been approved!");
        }
    }

    /**
     * Approve necessary items skipping queue. Jumping over the queue.
     */
    public static function actionHardApsApprove()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['docs'])) {


            // get user approval range
            $userApprovalRange = Aps::getUserApprovalRange(); //for current action we are interested only in param $userApprovalRange['user_appr_val']

            if (is_array($_POST['docs'])) {

                $all_amount=count($_POST['docs']);
                $i=0;
                $pb= ProgressBar::init();

                foreach($_POST['docs'] as $docId) {

                    $docId = intval($docId);
                    //approve document
                    Aps::HardApApprove($docId, $userApprovalRange);


                    /**
                     * * Next block used for progress bar animation only
                     */
                    $i++;
                    $percent=intval($i/$all_amount*100);
                    session_start();
                    $_SESSION['progress']=$percent;
                    session_write_close();
                    //end of block

                }
            } else {
                $docId = intval($_POST['docs']);
                //approve document
                Aps::HardApApprove($docId, $userApprovalRange);

            }

            // get user approval value
            $userToClient = UsersClientList::model()->findByAttributes(array(
                'User_ID' => Yii::app()->user->userID,
                'Client_ID' => Yii::app()->user->clientID,
            ));


            //get next user to approve
            $nextUsers = UsersClientList::getNextUserToApprove($userApprovalRange);
            Aps::notifyNextUsers($nextUsers,$userToClient);

            Yii::app()->user->setFlash('success', "APs have been approved!");
        }
    }



    /**
     * Return AP to previous approver
     */
    public function actionReturnAp()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['doc'])) {

            $docId = intval($_POST['doc']);

            //check document
            $document = Documents::model()->with('client')->findByAttributes(array(
                'Client_ID' => Yii::app()->user->clientID,
                'Document_ID' => $docId,
            ));

            if ($document) {
                //get AP
                $ap = Aps::model()->findByAttributes(array(
                    'Document_ID' => $docId,
                ));

                if ($ap) {
                    //get pervios user approval value
                    $perviosUserApproval = UsersClientList::getPreviousUserApprovalValue($ap->Previous_AP_A_Val);

                    if ($perviosUserApproval) {
                        $LastApproverValue = $perviosUserApproval->User_Approval_Value;
                    } else {
                        $LastApproverValue = 1;
                    }

                    // set AP_Approval_Value and save
                    $ap->AP_Approval_Value = $ap->Previous_AP_A_Val;
                    $ap->Previous_AP_A_Val = $LastApproverValue;
                    $ap->save();

                    Audits::LogAction($ap->Document_ID,Audits::ACTION_REVERT.' to '.$ap->AP_Approval_Value);

                    // find and unset doc from session
                    Helper::removeDocumentFromViewSession($docId, 'ap_to_review');

                    // get user approval value
                    $userToClient = UsersClientList::model()->findByAttributes(array(
                        'User_ID' => Yii::app()->user->userID,
                        'Client_ID' => Yii::app()->user->clientID,
                    ));

                    //get pervious user to approve
                    $previousUser = UsersClientList::getPreviousUserToApprove($userToClient->User_Approval_Value);

                    if ($previousUser) {
                        $previousUserId = $previousUser->User_ID;
                        $user = Users::model()->with('settings', 'person')->findByPk($previousUserId);

                        // send notification
                            $client = Clients::model()->findByPk(Yii::app()->user->clientID);
                            $project = Projects::model()->findByPk(Yii::app()->user->projectID);
                            $clientsToApprove = array($client->company->Company_Name.' - '.$project->Project_Name.'');
                            $project = Projects::model()->findByPk(Yii::app()->user->projectID);

                            Mail::sendPendingApprovalDocumentsNotification(!$user->settings->Notification,$user, $clientsToApprove, Documents::AP, $client, $project);

                    }

                    Yii::app()->user->setFlash('success', "APs have been returned!");
                }
            }
        }
    }

    /**
     * Return AP_Approval_Value
     */
    public function actionGetApProgress()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['docId'])) {
            $docId = intval($_POST['docId']);
            if ($docId > 0) {
                $ap = Aps::model()->findByAttributes(array(
                    'Document_ID' => $docId,
                ));
                echo isset($ap->AP_Approval_Value) ? $ap->AP_Approval_Value : 0;
            }
        }
    }

    /**
     * Print document action
     */
    public function actionPrintDocument()
    {
        $docId = trim($_SESSION['ap_to_print']);

        //get document
        $document = Documents::model()->findByAttributes(array(
            'Document_ID' => $docId,
            'Client_ID' => Yii::app()->user->clientID,
        ));

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
     * Set Doc ID of AP to print
     */
    public function actionSetDocIdToPrintDocument()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['docId'])) {
            $docId = intval($_POST['docId']);
            if ($docId > 0 && Documents::hasAccess($docId)) {
                $document = Documents::model()->findByAttributes(array(
                    'Document_ID' => $docId,
                    'Client_ID' => Yii::app()->user->clientID,
                ));

                if ($document) {
                    $_SESSION['ap_to_print'] = $docId;
                } else {
                    $_SESSION['ap_to_print'] = '';
                }
            }
        }
    }

    /**
     * Add note action
     */
    public function actionAddNote()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['comment']) && isset($_POST['docId'])) {
            $docId = intval($_POST['docId']);
            $comment = $_POST['comment'];
            if ($docId > 0 && trim($comment) != '' && Documents::hasAccess($docId)) {
                $note = new Notes;
                $note->Document_ID = $docId;
                $note->User_ID = Yii::app()->user->userID;
                $note->Company_ID = 0;
                $note->Client_ID = Yii::app()->user->clientID;
                $note->Comment = $comment;
                $note->Created = date("Y-m-d H:i:s");
                $note->save();

                Audits::LogAction($docId,Audits::ACTION_NOTE);

                $user = Users::model()->with('person')->findByPk(Yii::app()->user->userID);

                $this->renderPartial('note_item_block', array(
                    'note' => $note,
                    'user' => $user,
                ));
            }
        }
    }

    /**
     *  Check Vendor W9 to show or hide 1099 box number drop down
     */
    public function actionCheckVendorW9()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['vendorId'])) {
            $vendorId = intval($_POST['vendorId']);
            $vendor = Vendors::model()->with('client')->findByPk($vendorId);

            //commented out 21.10.2014 according to email Fwd: Entry field switch in CkRq & PO form Tim
            /*$vendorW9 = W9::getCompanyW9($vendor->client->Client_ID);

            if ($vendorW9) {
                if (!($vendorW9->Tax_Class == 'C' || $vendorW9->Tax_Class == 'CC'
                    || $vendorW9->Tax_Class == 'CS')) {
                    echo Aps::NEED_1099;
                    die;
                }
            }*/

            if ($vendor->Vendor_1099 == 1) {
                echo Aps::NEED_1099;
            } else { echo Aps::DO_NOT_NEED_1099;}
        }
    }

    /**
     * Add session pointer to send to approve item
     */
    public function actionAddSessionPointerToApprove()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['apId'])) {
            $apId = trim($_POST['apId']);
            if (Aps::hasStagingAPAccess($apId)) {
                $_SESSION['ap_to_approve'] = $apId;
            }
        }
    }

    /**
     * Add session pointer to send to approve item
     */
    public function actionAddSessionPointerToReview()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['apr_ids_arr'])) {
            $_SESSION['ap_to_review'] = array();
            $arr = $_POST['apr_ids_arr'];
            $i=1;
            foreach ($arr as $arr_item){
               $_SESSION['ap_to_review'][$i]= $arr_item;
               $_SESSION['ap_hard_approve'][$i]= $arr_item;
               $i++;
            }

        }

    }

    /**
     * Recreate PDF file for generated AP
     */
    public function actionRecreate()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['doc_id'])) {
            $doc_id = intval($_POST['doc_id']);
            $document = Documents::model()->findByPk($doc_id);

            if ($document->Origin == 'G') {

                $ap = Aps::model()->findByAttributes(array(
                    'Document_ID' => $doc_id
                ));

                //Aps::generatePdfFpdf($ap->AP_ID, $ap->Approved );
                Documents::pdfGeneration($ap->Document_ID,'AP',$ap->Approved);
                Audits::LogAction($ap->Document_ID,Audits::ACTION_REPDF);

            }
        }
    }

    /**
     * Mark AP as void (deleted)
     */
    public function actionMarkAsVoid()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['ap_id'])) {
            $ap_id = trim($_POST['ap_id']);
            Aps::MarkStagingItemAsVoid($ap_id);

            $this->redirect(Yii::app()->request->urlReferrer);
        }
    }

    /**
     * Creates empty AP without saving to database and generates temp PDF for it.
     */
    public function actionCreateForPreview()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['Aps'])) {

            $ap = new Aps();
            $ap->Invoice_Date = date('Y-m-d');
            //$ap->Invoice_Date = Helper::clientDateToDatabase(date('Y-m-d')); ????????
            $ckReqDet = new CkReqDetails();
            $ap->Invoice_Number = Aps::getNewAPNumber(Yii::app()->user->clientID,Yii::app()->user->projectID);
            $ckReqDet->Sign_Requested_By = Yii::app()->user->userID;

            $_POST['Aps']['Invoice_Due_Date'] = Helper::checkDate($_POST['Aps']['Invoice_Due_Date']);
            $_POST['CkReqDetails']['Rental_Begin'] = Helper::checkDate($_POST['CkReqDetails']['Rental_Begin']);
            $_POST['CkReqDetails']['Rental_End'] = Helper::checkDate($_POST['CkReqDetails']['Rental_End']);

            $ap->attributes = $_POST['Aps'];
            $ckReqDet->attributes = $_POST['CkReqDetails'];

            // set dists
            $distsToSave = array();
            $distsToSave = GlDistDetails::prepareDistsToSave($_POST['GlDistDetails']);
            $distsToSave = GlDistDetails::prepareAPDistsModelsArray($ap->AP_ID,$distsToSave);
            //$distsToSave = PoDists::preparePODistsArray()

            // generate dists.  array of models
            //$distsToSave = PoDists::preparePODistsArray($po->PO_ID,$distsToSave);

            $ap->AP_Approval_Value = Aps::NOT_READY_FOR_APPROVAL;
            $ap->Detail_1099 = Aps::DO_NOT_NEED_1099;

            $approvalRange = Aps::getUserApprovalRange();

            $file_array = Aps::generatePdfFpdfPreview($ap, $ckReqDet,$distsToSave,($approvalRange['user_appr_val'] == Pos::APPROVED));

            $file_id = FileCache::addToFileCache($file_array['filepath']);

            echo $file_id;

        }
    }

}