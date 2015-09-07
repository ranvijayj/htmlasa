<?php

class PoController extends Controller
{
    /**
     * Layout color
     * @var string
     */
    public $layoutColor = "#0078C1";

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
            'postOnly + delete', // we only allow deletion via POST request
            //array('application.filters.AnalizeUrl')

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
                'actions'=>array('clearpotoreviewsession', 'getdocumentfileforgoogle'),
                'users'=>array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('index', 'detail', 'create','getlistbysearchquery', 'getcompanyinfo', 'addpoitemstosession', 'addnote',
                                 'printdocument', 'setdocidtoprintdocument', 'getpoprogress', 'approvepos', 'returnpo', 'setmarkeditems',
                                 'addsearchquerytosession', 'getvendorinfoblock', 'addfiletouploadsession', 'getdocumentfile',
                                 'changedocumenttype', 'uploadfile', 'addsessionpointertoapprove', 'updatePOTrackingNote','MarkAsVoid','AddSessionPointerToReview','GetNextBlockBySearchquery',
                                  'GetInPlaceInput','SavePoTrack','HardPosApprove','CreateForPreview','GetPOCreationForm'
                ),
                'expression'=>function() {
                    $users = array('admin','approver','user' ,'processor', 'db_admin', 'client_admin');
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
     * POs list
     */
    public function actionIndex()
	{
        //check if we need to change client/project
        $cid = intval($_GET['cid']);
        $pid = intval($_GET['pid']);
        $uid = intval($_GET['uid']);

        $client_change_array = Helper::checkUserClientProjectToSwitch($cid,$pid,$uid);


        if (!isset($_SESSION['last_pos_list_search']['query'])) {
            $_SESSION['last_pos_list_search']['query'] = '';
        }

        if (!isset($_SESSION['limiter'])) {
            $limit=Aps::DISPLAY_LIMIT;

        } else {$limit=$_SESSION['limiter'];}

        $searchQuery = $_SESSION['last_pos_list_search']['query'];
        $checkedTBABox = 1;

        //get POs list
        if (isset($_SESSION['last_pos_list_search']['options']) && count($_SESSION['last_pos_list_search']['options']) > 0) {
            $to_be_approved_count=Pos::getLastPOs(0, true);
            $poList = Pos::getListByQueryString($_SESSION['last_pos_list_search']['query'], $_SESSION['last_pos_list_search']['options'], $_SESSION['last_pos_list_search']['sort_options'], $this->paymentTypes);
            $_SESSION['last_pos_list_search']['query'] = '';
            $_SESSION['last_pos_list_search']['sort_options'] = array();
            $_SESSION['po_to_review'] = array();
        } else {
            $poList = Pos::getLastPOs(0, true);
            $to_be_approved_count=$poList;
            if (!$poList) {
                // if no items to approve, get last 50 items
                $poList = Pos::getLastPOs($limit);
                //die;
                $checkedTBABox = 0;
            }
        }

        //get last POs' notes
        $notes = Pos::getPOsNotes($poList);

        // define if buttons must be active or not
        $approvalButtonsClass = Pos::getApprovalButtonsClass($poList);
        $budgets = Pos::getCoaCurrentBudgetsByPoList($poList);

        $userSettings = UsersSettings::model()->findByAttributes(array(
            'User_ID' => Yii::app()->user->userID,
        ));

        $this->render('index', array(
            'poList' => $poList,
            'to_be_approved_count'=>$to_be_approved_count,
            'notes' => $notes,
            'approvalButtonsClass' => $approvalButtonsClass,
            'searchQuery' => $searchQuery,
            'checkedTBABox' => $checkedTBABox,
            'budgets' => $budgets,
            'userSettings' => $userSettings,
            'client_change_array'=>$client_change_array
        ));
	}

    /**
     * PO detail page action
     * @param int $page
     */
    public function actionDetail($page = 1)
    {

        $cs = Yii::app()->getClientScript();

        $cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/jupload/vendor/jquery.ui.widget.js');
        $cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/jupload/jquery.iframe-transport.js');
        $cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/jupload/jquery.fileupload.js');
        $cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/jupload/jquery.fileupload-process.js');


        // check PO to review
        if (!isset($_SESSION['po_to_review']) || count($_SESSION['po_to_review']) == 0) {
            $_SESSION['po_to_review'] = Pos::getPOsToApproveToSession();
            if (!isset($_SESSION['po_to_review']) || count($_SESSION['po_to_review']) == 0) {
                if (isset($_SESSION['last_pos_list_search']['options']) && $_SESSION['last_pos_list_search']['options']['search_option_to_be_approved'] == 1) {
                    $_SESSION['last_pos_list_search']['query'] = '';
                    $_SESSION['last_pos_list_search']['sort_options'] = array();
                    $_SESSION['po_to_review'] = array();
                    $_SESSION['last_pos_list_search']['options'] = array();
                }
                Yii::app()->user->setFlash('success', "Please choose POs to review!");
                $this->redirect('/po');
            }
        }

        $page = intval($page);
        $num_pages = count($_SESSION['po_to_review']);
        if ($page <= 0) {
            $page = 1;
        } else if ($page > $num_pages) {
            $page = $num_pages;
        }

        $docId = $_SESSION['po_to_review'][$page];

        $po = Pos::model()->with('vendor', 'document')->findByAttributes(array(
            'Document_ID' => $docId,
        ));

        // process PO Traking form
        $poError = '';
        $pmtsTracking = array();
        if (isset($_POST['PoPmtsTraking'])) {
            $pmtsTracking = $_POST['PoPmtsTraking'];
            $outBalance = Pos::checkPOBalance($po->PO_ID, $pmtsTracking['PO_Trkng_Pmt_Amt']);
            if ($pmtsTracking['PO_Trkng_Desc'] == '' || !preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $pmtsTracking['PO_Trkng_Inv_Date'])
                || $pmtsTracking['PO_Trkng_Pmt_Amt'] == '' || $pmtsTracking['PO_Trkng_Inv_Number'] == '') {
                $poError = 'All fields are required';
            } else if ($outBalance > 0) {
                $poError = 'This Invoice can not be added to PO Tracking Sheet. Balance will be out of by $' . number_format($outBalance, 2);
            } else {
                $pmtsTracking['PO_Trkng_Inv_Date'] = Helper::checkDate($pmtsTracking['PO_Trkng_Inv_Date']);
                Pos::addApPORelation($po, $pmtsTracking['PO_Trkng_Pmt_Amt'], $pmtsTracking['PO_Trkng_Inv_Date'], $pmtsTracking['PO_Trkng_Inv_Number'], $pmtsTracking['PO_Trkng_Desc']);
                if ($po->PO_Approval_Value == 100) {
                    LibraryDocs::addDocumentToFolder($po->Document_ID);
                }
                Yii::app()->user->setFlash('success', "Invoice have been successfully added to PO Tracking Sheet!");
                $pmtsTracking = array();
            }
        }

        // define if PO must be approved by user
        $mustBeApproved = false;

        // get user approval range
        $userApprovalRange = Aps::getUserApprovalRange();
        if ($po->PO_Approval_Value >= $userApprovalRange['prev_user_appr_val'] &&
            $po->PO_Approval_Value < $userApprovalRange['user_appr_val']) {
            $mustBeApproved = true;
        }

        if ($_SESSION['po_hard_approve'][$page]==$docId) {
            $hard_approval = true;
        }

        $company = '';
        $vendor = $po->vendor;
        if ($vendor) {
            $client = $vendor->client;
            $company = $client->company;
        }

        $document = $po->document;
        $user = $document->user;

        // get document's file
        $condition = new CDbCriteria();
        $condition->select = 'Mime_Type';
        $condition->condition = "Document_ID='" . $document->Document_ID . "'";
        $file = Images::model()->find($condition);

        // get notes
        $notes = Notes::model()->getDocumentNotes($po->Document_ID);

        // get PO formatting
        $poFormatting = PoFormatting::model()->findByAttributes(array(
            'Project_ID' => $po->document->Project_ID,
        ));

        $budgets = Pos::getCoaCurrentBudgets(array($po->PO_ID));

        // get PO Back Up
        $backUp = Documents::getBackupDoc($po->PO_Backup_Document_ID, false);

        // get PO Tracking list
        $condition = new CDbCriteria();
        $condition->condition = "t.PO_ID = '" . $po->PO_ID . "'";
        $condition->order = 't.PO_Trkng_Inv_Date ASC';
        $poTracks = PoPmtsTraking::model()->findAll($condition);

        $enableEditing = ($userApprovalRange['user_appr_val'] >= $po->PO_Approval_Value) && ($po->PO_Approved == 0);
        $dists=PoDists::model()->getPODists($po->PO_ID);

        $vendorsCP = array();
        if ($document) {
            //get vendors Shortcut
            $vendorsCP = Vendors::getClientVendorsShortcutList($document->Client_ID);
        }

        $this->render('detail', array(
            'page' => $page,
            'num_pages' => $num_pages,
            'company' => $company,
            'po' => $po,
            'user' => $user,
            'document' => $document,
            'notes' => $notes,
            'file' => $file,
            'mustBeApproved' => $mustBeApproved,
            'hard_approval'=>$hard_approval,
            'enableEditing'=>$enableEditing,
            'poFormatting' => $poFormatting,
            'backUp' => $backUp,
            'userApprovalRange' => $userApprovalRange,
            'poTracks' => $poTracks,
            'pmtsTracking' => $pmtsTracking,
            'poError' => $poError,
            'budgets' => $budgets,
            'dists' => $dists['dists'],
            'dists_empty' => $dists['empty'],
            'vendorsCP'=>$vendorsCP,
            'invalidDistsSum'=>''
        ));
    }

    /**
     * Create PO from input data
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



        // check project id
        if (Yii::app()->user->projectID == 'all') {
            Yii::app()->user->setFlash('success', "Please select a specific Project for this process.");
            $this->redirect('/po');
        }

        // set PO_ID
        $poId = intval($id);

        // initiate variables
        $descDetails = array();
        $dists = array();
        for($i = 1; $i <= 6; $i++) {
            $dists[$i] = array(
                'PO_Dists_GL_Code' => '',
                'PO_Dists_Amount' => '',
                'PO_Dists_Description' => '',
            );
        }

        for($i = 1; $i <= 8; $i++) {
            $descDetails[$i] = array(
                'PO_Desc_Qty' => '',
                'PO_Desc_Desc' => '',
                'PO_Desc_Purchase_Rental' => 0,
                'PO_Desc_Budget_Line_Num' => '',
                'PO_Desc_Amount' => '',
            );
        }


        // check access to staging PO
        if ($poId != 0 && !Pos::hasStagingPOAccess($poId)) {
            $this->redirect('/po/create');
        }


        if ($poId == 0) {
            $po = new Pos();
            $po->PO_Date = date('Y-m-d');
            $po->PO_Number = Pos::getNewPoNumber();
            $po->Sign_Requested_By = Yii::app()->user->userID;
        } else {
            $po = Pos::model()->with('dists', 'decr_details', 'document')->findByPk($poId);
            $poDists = $po->dists;
            $poDecrDetails = $po->decr_details;

            // set description details
            foreach ($poDecrDetails as $key => $decr_detail) {
                $descDetails[$key + 1] = array(
                    'PO_Desc_Qty' => $decr_detail->PO_Desc_Qty,
                    'PO_Desc_Desc' => $decr_detail->PO_Desc_Desc,
                    'PO_Desc_Purchase_Rental' => $decr_detail->PO_Desc_Rental,
                    'PO_Desc_Budget_Line_Num' => $decr_detail->PO_Desc_Budget_Line_Num,
                    'PO_Desc_Amount' => $decr_detail->PO_Desc_Amount,
                );
            }

            // set dists
            foreach ($poDists as $key => $dist) {
                $dists[$key + 1] = array(
                    'PO_Dists_GL_Code' => $dist->PO_Dists_GL_Code,
                    'PO_Dists_Amount' => $dist->PO_Dists_Amount,
                    'Short_Hand' => $dist->Short_Hand,
                    'PO_Dists_Description' => $dist->PO_Dists_Description,
                );
            }
        }

        // process creation PO
        if (isset($_POST['Pos'])) {
            $po->attributes = $_POST['Pos'];

            // init variables
            $distsToSave = array();
            $detailsToSave = array();
            $subtotal = 0;
            $total = 0;

            // set description details
            $result = PoDescDetail::prepareDescDetails($_POST['PoDescDetail']);

            $detailsToSave = $result['detailsToSave'];
            $subtotal = $result['subtotal'];
            $total = $result['total'];
            
            // set dists
           $distsToSave = PoDists::prepareDistsToSave($_POST['PoDists']);

            // count total
            $total += round(floatval($po->PO_Tax), 2) + round(floatval($po->PO_Other_Chg), 2) + round(floatval($po->PO_Delivery_Chg), 2);
            $po->PO_Total = (string) round($total, 2);
            $po->PO_Subtotal = (string) round($subtotal, 2);
            $po->PO_Card_Last_4_Digits = isset($_POST['Pos']['PO_Card_Last_4_Digits']) ? $_POST['Pos']['PO_Card_Last_4_Digits'] : null;
            if (!$po->PO_Backup_Document_ID) {
                if (isset($_SESSION['last_uploaded_backup'])) {
                    $po->PO_Backup_Document_ID = $_SESSION['last_uploaded_backup'];
                    unset($_SESSION['last_uploaded_backup']);
                } else {
                    $po->PO_Backup_Document_ID = 0;
                }

            }
            if (!$po->Document_ID) {
                $po->Document_ID = 0;
            }

            // validate and save
            if ($po->validate()) {
                // begin transaction
                $transaction = Yii::app()->db->beginTransaction();
                try {
                    if ($po->Document_ID == 0) {
                        $document = new Documents();
                        $document->Document_Type = Documents::PO;
                        $document->Origin = 'G';
                        $document->User_ID = Yii::app()->user->userID;
                        $document->Client_ID = Yii::app()->user->clientID;
                        $document->Project_ID = Yii::app()->user->projectID;
                        $document->Created = date("Y-m-d H:i:s");
                        $document->save();

                        Audits::LogAction($document->Document_ID ,Audits::ACTION_CREATION);
                        $po->Document_ID = $document->Document_ID ;
                    }

                    $po->save();

                    // save descr. details
                    PoDescDetail::savePODetails($po->PO_ID, $detailsToSave);

                    //save dists
                    PoDists::savePODists($po->PO_ID, $distsToSave);

                    if (!isset($_SESSION['po_to_approve'])) {
                        Yii::app()->user->setFlash('success', "PO has been successfully saved!");
                    }
                    $transaction->commit();
                    $this->redirect('/po/create/' . $po->PO_ID);
                    die;
                } catch(Exception $e) {
                    $transaction->rollback();
                }
            }
        }

        $poFormatting = PoFormatting::model()->findByAttributes(array(
            'Project_ID' => Yii::app()->user->projectID,
        ));

        $backUp = Documents::getBackupDoc($po->PO_Backup_Document_ID);

        // get stading items
        $stagingItems = Pos::getStagingItems();

        // get client approvers
        $clientApprovers = UsersClientList::getClientApprovers();

        // get client vendors
        $vendors = Vendors::getClientVendorsShortcutList(Yii::app()->user->clientID);

        // get current vendor info
        $currentVendor = null;
        $vendorAdmin = null;
        if ($po->Vendor_ID != 0) {
            $currentVendor = Vendors::model()->with('client.company.adreses')->findByPk($po->Vendor_ID);

            $condition = UsersClientList::getClientAdminCondition($currentVendor->client->Client_ID);
            $vendorAdmin = UsersClientList::model()->with('user.person')->find($condition);
        }

        // get Sign_Requested_By user info
        $signRequestedByUser = Users::model()->with('person')->findByPk($po->Sign_Requested_By);


        $detailsError = '';
        $distsError = '';
        $hasErrors = false;
        // generate PO pdf and send to approve
        if (isset($_SESSION['po_to_approve']) && $_SESSION['po_to_approve'] == $poId) {
            //check po fields
            if ($po->Vendor_ID == '0') {
                $po->addError('Vendor_ID','Choose Vendor!');
                $hasErrors = true;
            }

            /*if ($po->PO_Account_Number == '') {
                $po->addError('PO_Account_Number','Account Num is required!');
                $hasErrors = true;
            }*/

            if ($po->Payment_Type == '0' || $po->Payment_Type == null) {
                $po->addError('Payment_Type','Choose Payment Type!');
                $hasErrors = true;
            }

            if ($po->Payment_Type == 'CC' && $po->PO_Card_Last_4_Digits == '') {
                $po->addError('PO_Card_Last_4_Digits','Last 4 Digits are required!');
                $hasErrors = true;
            } else if ($po->Payment_Type == 'CC' && (!is_numeric($po->PO_Card_Last_4_Digits) || strlen($po->PO_Card_Last_4_Digits) != 4)) {
                $po->addError('PO_Card_Last_4_Digits','Invalid Last 4 Digits value!');
                $hasErrors = true;
            }

            /*if ($po->Sign_Dept_Approval == '0' || $po->Sign_UPM_Executive == '0' || $po->Sign_Accounting == '0') {
                $po->addError('Sign_Accounting','Choose signers!');
                $hasErrors = true;
            }*/


            // check dists
            if ($poDists) {
                $distsSum = 0;
                foreach ($poDists as $poDist) {
                    $distsSum += $poDist->PO_Dists_Amount;
                    if ($poDist->PO_Dists_Amount == '' || $poDist->PO_Dists_Amount == '0'
                        || $poDist->PO_Dists_Description == '' || $poDist->PO_Dists_Description == '-'
                        || $poDist->PO_Dists_GL_Code == '' || $poDist->PO_Dists_GL_Code == '-') {
                        $distsError = 'All fields in populated rows are required';
                        $hasErrors = true;
                    }
                }

                if ($distsSum != $po->PO_Total) {
                    $distsError = 'The sum of all the distribution line must equal to Total. The PO is out of balance by $' . abs($distsSum - $po->PO_Total);
                    $hasErrors = true;
                }
            } else {
                $distsError = 'At least one row of PO distribution is required';
                $hasErrors = true;
            }

            // check details
            if ($poDecrDetails) {
                foreach ($poDecrDetails as $poDecrDetail) {
                    if ($poDecrDetail->PO_Desc_Qty == '' || $poDecrDetail->PO_Desc_Qty == '0' || $poDecrDetail->PO_Desc_Desc == ''
                        || $poDecrDetail->PO_Desc_Desc == '-'|| $poDecrDetail->PO_Desc_Amount == '' || $poDecrDetail->PO_Desc_Amount == '0') {
                        $detailsError = 'Qty, Description and Amount are required for all populated rows';
                        $hasErrors = true;
                        break;
                    }
                }
            } else {
                $detailsError = 'At least one row of PO details is required';
                $hasErrors = true;
            }

            if (!$hasErrors) {
                // get user approval range
                $approvalRange = Aps::getUserApprovalRange();

                $appr_level_was_changed = 0;

                // check approval range and set approval values
                if ($approvalRange['user_appr_val'] == Pos::APPROVED) {
                    $po->PO_Approval_Value = $approvalRange['user_appr_val'];
                    $po->PO_Previous_PO_Val = $approvalRange['prev_user_appr_val'];
                    $po->PO_Approved = 1;

                    $appr_level_was_changed=1;

                    LibraryDocs::addDocumentToFolder($po->Document_ID);
                    LibraryDocs::addDocumentToBinder($po->Document_ID);
                    Audits::LogAction($po->Document_ID,Audits::ACTION_LIBRARY);

                } else if ($approvalRange['user_appr_val'] > Pos::READY_FOR_APPROVAL) {
                    $po->PO_Approval_Value = $approvalRange['user_appr_val'];
                    $po->PO_Previous_PO_Val = $approvalRange['prev_user_appr_val'];
                    $appr_level_was_changed=1;
                } else {
                    $po->PO_Approval_Value = Pos::READY_FOR_APPROVAL;
                    $po->PO_Previous_PO_Val = Pos::READY_FOR_APPROVAL;
                }

                // begin transaction
                $transaction = Yii::app()->db->beginTransaction();
                try {
                    $po->save();

                    if ($appr_level_was_changed) Audits::LogAction($po->Document_ID ,Audits::ACTION_APPROVAL);

                    if ($approvalRange['user_appr_val'] == Pos::APPROVED) {
                        $po->updateCoaCurrentBudget();
                    }

                    // generate pdf
                    Documents::pdfGeneration($po->Document_ID,'PO',($approvalRange['user_appr_val'] == Pos::APPROVED));
                    Audits::LogAction($po->Document_ID,Audits::ACTION_PDF);

                    $transaction->commit();
                } catch(Exception $e) {
                    $transaction->rollback();
                }


                if ($approvalRange['user_appr_val'] != Pos::APPROVED) {
                    //get next user to approve
                    $nextUsers = UsersClientList::getNextUserToApprove($approvalRange);
                    Pos::notifyNextUsers($nextUsers,$approvalRange);

                }
                Yii::app()->user->setFlash('success', "PO has been successfully generated and sent to Approval cycle!");
                unset($_SESSION['po_to_approve']);
                $this->redirect('/po/create');
            } else {
                Yii::app()->user->setFlash('error', "Missing information. This item cannot be created.Please review the notes in red below.");
            }
            unset($_SESSION['po_to_approve']);
        } else if (isset($_SESSION['po_to_approve']) && $_SESSION['po_to_approve'] != $poId) {
            unset($_SESSION['po_to_approve']);
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
            'descDetails' => $descDetails,
            'po' => $po,
            'poFormatting' => $poFormatting,
            'poId' => $poId,
            'vendors' => $vendors,
            'currentVendor' => $currentVendor,
            'vendorAdmin' => $vendorAdmin,
            'signRequestedByUser' => $signRequestedByUser,
            'stagingItems' => $stagingItems,
            'backUp' => $backUp,
            'detailsError' => $detailsError,
            'distsError' => $distsError,
            'availableStorage' => $availableStorage,
            'usedStorage' => $usedStorage,
            'enableCreating' => $enableCreating,
            'disableCreatingMessage' => $disableCreatingMessage,
            'coaStructure'=>$coaStructure
        ));
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

            $this->renderPartial('po_vendor_info_block', array(
                'currentVendor' => $currentVendor,
                'vendorAdmin' => $vendorAdmin,
            ));
        }
    }

    /**
     * Add PO items to session action
     */
    public function actionAddPOItemsToSession()
    {
        if (isset($_POST['documents'])) {
            $_SESSION['po_to_review'] = array();
            $i = 1;
            foreach ($_POST['documents'] as $docId) {
                $docId = intval($docId);
                if ($docId > 0 && Documents::hasAccess($docId)) {
                    $document = Documents::model()->with('client')->findByAttributes(array(
                        'Client_ID' => Yii::app()->user->clientID,
                        'Document_ID' => $docId,
                    ));
                    $po = Pos::model()->findByAttributes(array(
                        'Document_ID' => $docId,
                    ));
                    if ($document && $po) {
                        $_SESSION['po_to_review'][$i] = $docId;
                        $i++;
                    }
                }
            }

            $this->redirect('/po/detail');
        }
    }

    /**
     * Clear $_SESSION['po_to_review'] if we go to details page directly
     */
    public function actionClearPoToReviewSession()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['clear'])) {
            $_SESSION['po_to_review'] = array();
            $_SESSION['last_pos_list_search']['query'] = '';
            $_SESSION['last_pos_list_search']['options'] = array();
            $_SESSION['last_pos_list_search']['sort_options'] = array();
        }
    }

    /**
     * Get POs list search query action
     */
    public function actionGetListBySearchQuery()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['query'])) {
            $poList = array();
            $batchedMode =false;

            if (!isset($_SESSION['marked_pos'])) {
                $_SESSION['marked_pos'] = array();
            }


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
                'search_option_po_number' => intval($_POST['search_option_po_number']),
                'search_option_po_date' => intval($_POST['search_option_po_date']),
                'search_option_po_total' => intval($_POST['search_option_po_total']),
                'search_option_po_acct_number' => intval($_POST['search_option_po_acct_number']),
                'search_option_payment_type' => intval($_POST['search_option_payment_type']),
                'search_option_last_digits' => intval($_POST['search_option_last_digits']),
                'search_option_phone' => intval($_POST['search_option_phone']),
                'search_option_batch' => intval($_POST['search_option_batch']),
                'search_option_to_be_batched' => intval($_POST['search_option_to_be_batched']),
                'search_option_limit' => intval($_POST['search_option_limit']),
            );

            $sortOptions = array(
                'sort_by' => $_POST['sort_type'],
                'sort_direction' => $_POST['sort_direction'],
            );

            if(!$options['search_option_limit']) {$limit=0;}
            else {$limit=Aps::DISPLAY_LIMIT;}

            if ($options['search_option_to_be_batched'] && Yii::app()->user->projectID === 'all') {
                $sortOptions = array(
                    'sort_by' => 'documents.Project_ID asc, '.$_POST['sort_type'],
                    'sort_direction' => $_POST['sort_direction'],
                );
                $batchedMode = true;
            }

            // get POs list
            $poList = Pos::getListByQueryString($queryString, $options, $sortOptions, $this->paymentTypes,$limit);



            //get last POs' notes
            $notes = Pos::getPOsNotes($poList);
            $budgets = Pos::getCoaCurrentBudgetsByPoList($poList);
            $posList = $this->renderPartial('polist', array(
                'poList' => $poList,
                'notes' => $notes,
                'markSelctd' => $_POST['mark_selected'],
                'budgets' => $budgets,
                'batchedMode'=>$batchedMode
            ), true);

            // define if buttons must be active or not
            $approvalButtonsClass = Pos::getApprovalButtonsClass($poList);

            $result = array(
                'count'=>count($poList),
                'html' => $posList,
                'btnsClass' => $approvalButtonsClass,
            );

            echo CJSON::encode($result);
        }
    }

    /**
     * for ajax append
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

            // get Vendors list
            $poList = Pos::getListByQueryString($queryString, $options, $sortOptions, $this->paymentTypes,$limit,$offset);

            $result['count']=count($poList);
            $result['html']=$this->renderPartial('application.views.po.polist', array(
                    'poList' => $poList,
                ),true
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
                'search_option_po_number' => intval($_POST['search_option_po_number']),
                'search_option_po_date' => intval($_POST['search_option_po_date']),
                'search_option_po_total' => intval($_POST['search_option_po_total']),
                'search_option_po_acct_number' => intval($_POST['search_option_po_acct_number']),
                'search_option_payment_type' => intval($_POST['search_option_payment_type']),
                'search_option_last_digits' => intval($_POST['search_option_last_digits']),
                'search_option_phone' => intval($_POST['search_option_phone']),
            );

            $sortOptions = array(
                'sort_by' => $_POST['sort_type'],
                'sort_direction' => $_POST['sort_direction'],
            );

            // set last search query params to session
            $_SESSION['last_pos_list_search']['query'] = $queryString;
            $_SESSION['last_pos_list_search']['options'] = $options;
            $_SESSION['last_pos_list_search']['sort_options'] = $sortOptions;
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
                $po = Pos::model()->with('vendor', 'document')->findByAttributes(array(
                    'Document_ID' => $docId,
                ));

                $document = $po->document;

                $poFormatting = PoFormatting::model()->findByAttributes(array(
                    'Project_ID' => $document->Project_ID,
                ));

                $company = null;

                $vendor = $po->vendor;
                if ($vendor) {
                    $client = $vendor->client;
                    $company = $client->company;
                }
                $document = $po->document;
                $user = $document->user;

                $userApprovalRange = Aps::getUserApprovalRange();
                //var_dump($userApprovalRange);die;
                $budgets = Pos::getCoaCurrentBudgets(array($po->PO_ID));

                $this->renderPartial('company_info_block', array(
                    'po' => $po,
                    'company' => $company,
                    'user' => $user,
                    'document' => $document,
                    'poFormatting' => $poFormatting,
                    'budgets' => $budgets,
                    'userApprovalRange'=>$userApprovalRange
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
                $_SESSION['marked_pos'] = $_POST['docs'];
            }
            if ($_POST['clearList'] == 1) {
                $_SESSION['marked_pos'] = array();
            }
            echo 1;
        }
    }

    /**
     * Approve necessary items
     */
    public static function actionApprovePos()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['docs'])) {
            // get user approval range
            $userApprovalRange = Aps::getUserApprovalRange();

            if (is_array($_POST['docs'])) {
                foreach($_POST['docs'] as $docId) {
                    $docId = intval($docId);
                    //approve document
                    Pos::approvePO($docId, $userApprovalRange);


                }
            } else {
                $docId = intval($_POST['docs']);
                //approve document
                Pos::approvePO($docId, $userApprovalRange);

            }

            //get next user to approve
            $nextUsers = UsersClientList::getNextUserToApprove($userApprovalRange);
            Pos::notifyNextUsers($nextUsers,$userApprovalRange);


            Yii::app()->user->setFlash('success', "POs have been approved!");
        }
    }


    /**
     * Approve necessary items skipping queue. Jumping over the queue.
     */
    public static function actionHardPosApprove()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['docs'])) {
            // get user approval range
            $userApprovalRange = Aps::getUserApprovalRange(); //for current action we are interested only in param $userApprovalRange['user_appr_val']

            if (is_array($_POST['docs'])) {
                foreach($_POST['docs'] as $docId) {
                    $docId = intval($docId);
                    //approve document
                    Pos::HardPOApprove($docId, $userApprovalRange);


                }
            } else {
                $docId = intval($_POST['docs']);
                //approve document
                Pos::HardPOApprove($docId, $userApprovalRange);

            }





            //get next user to approve
            $nextUsers = UsersClientList::getNextUserToApprove($userApprovalRange);
            Pos::notifyNextUsers($nextUsers,$userApprovalRange);

            Yii::app()->user->setFlash('success', "POs have been approved!");
        }
    }

    /**
     * Return PO to previous approver
     */
    public function actionReturnPo()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['doc'])) {

            $docId = intval($_POST['doc']);

            //check document
            $document = Documents::model()->with('client')->findByAttributes(array(
                'Client_ID' => Yii::app()->user->clientID,
                'Document_ID' => $docId,
            ));

            if ($document) {
                //get PO
                $po = Pos::model()->findByAttributes(array(
                    'Document_ID' => $docId,
                ));

                if ($po) {
                    //get previous user approval value
                    $perviosUserApproval = UsersClientList::getPreviousUserApprovalValue($po->PO_Previous_PO_Val);

                    if ($perviosUserApproval && $perviosUserApproval->User_Approval_Value) {
                        $LastApproverValue = $perviosUserApproval->User_Approval_Value;
                    } else {
                        $LastApproverValue = Pos::READY_FOR_APPROVAL;
                    }



                    // set AP_Approval_Value and save
                    $po->PO_Approval_Value = $po->PO_Previous_PO_Val;
                    $po->PO_Previous_PO_Val = $LastApproverValue;
                    $po->save();

                    Audits::LogAction($po->Document_ID,Audits::ACTION_REVERT.' to '.$po->PO_Approval_Value);

                    // find and unset doc from session
                    Helper::removeDocumentFromViewSession($docId, 'po_to_review');

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
                            $project = Projects::model()->findByPk(Yii::app()->user->projectID);

                            $client = Clients::model()->findByPk(Yii::app()->user->clientID);
                            $clientsToApprove = array($client->company->Company_Name.' - '.$project->Project_Name.'');

                            Mail::sendPendingApprovalDocumentsNotification(!$user->settings->Notification,$user, $clientsToApprove, Documents::PO,$client,$project);

                    }

                    Yii::app()->user->setFlash('success', "PO have been returned!");
                }
            }
        }
    }

    /**
     * Return PO_Approval_Value
     */
    public function actionGetPOProgress()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['docId'])) {
            $docId = intval($_POST['docId']);
            if ($docId > 0) {
                $po = Pos::model()->with('vendor', 'document')->findByAttributes(array(
                    'Document_ID' => $docId,
                ));

                echo $po->PO_Approval_Value;
            }
        }
    }

    /**
     * Print document action
     */
    public function actionPrintDocument() {
        $docId = trim($_SESSION['po_to_print']);

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
     * Set Doc ID of PO to print
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
                    $_SESSION['po_to_print'] = $docId;
                } else {
                    $_SESSION['po_to_print'] = '';
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
                            if (isset($_SESSION['po_upload_file']) && file_exists($_SESSION['po_upload_file']['filepath'])) {
                                @unlink($_SESSION['po_upload_file']['filepath']);
                            }

                            $_SESSION['po_upload_file']['name'] = $_FILES['userfile']['name'];
                            $_SESSION['po_upload_file']['filepath'] = $filepath;
                            $_SESSION['po_upload_file']['mimetype'] = $_FILES['userfile']['type'];
                            $_SESSION['po_upload_file']['doctype'] = Documents::BU;
                            $_SESSION['po_upload_file']['fed_id'] = '';
                            $_SESSION['po_upload_file']['company_name'] = '';
                            $_SESSION['po_upload_file']['complete'] = true;

                            move_uploaded_file($_FILES['userfile']['tmp_name'], $_SESSION['po_upload_file']['filepath']);

                            $detailsPage = false;
                            if (isset($_GET['page'])) {
                                $detailsPage = true;
                            }
                            $this->renderPartial('uploads_block', array(
                                'file' => $_SESSION['po_upload_file'],
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
        if ($_SESSION['po_upload_file']) {
            $fileData = fread(fopen($_SESSION['po_upload_file']['filepath'],"rb"),filesize($_SESSION['po_upload_file']['filepath']));
            header("Content-type: ". $_SESSION['po_upload_file']['mimetype']);
            echo $fileData;
            die;
        }
    }

    /**
     * Return necessary file
     */
    public function actionGetDocumentFileForGoogle($doc_id, $code)
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
                $fileData = fread(fopen($code,"rb"),filesize($code));
                header("Content-type: application/pdf");
                echo $fileData;
                die;
            }
        }
    }

    /**
     * Change document type
     */
    public function actionChangeDocumentType() {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['docType'])) {
            $docType = trim($_POST['docType']);
            if (isset($_SESSION['po_upload_file']) && ($docType == Documents::BU || $docType == Documents::W9)) {
                $_SESSION['po_upload_file']['doctype'] = $docType;
                $_SESSION['po_upload_file']['fed_id'] = '';
                $_SESSION['po_upload_file']['company_name'] = '';
                if ($docType == Documents::W9) {
                    $_SESSION['po_upload_file']['complete'] = false;
                } else {
                    $_SESSION['po_upload_file']['complete'] = true;
                }
            }
        }
    }

    /**
     * Upload Backup or W9
     */
    public function actionUploadFile() {

        if (Yii::app()->request->isAjaxRequest && isset($_POST['docType']) && (isset($_POST['poId']) || isset($_POST['docId']))
            && isset($_POST['fed_id']) && isset($_POST['comp_name'])) {
            $result = array(
                'success' => false,
                'back_up_block' => '',
                'vendors_list' => '',
            );

            $poId = isset($_POST['poId']) ? trim($_POST['poId']) : trim($_POST['docId']);
            $fed_id = trim($_POST['fed_id']);
            $comp_name = trim($_POST['comp_name']);
            $docType = trim($_POST['docType']);
            if (isset($_SESSION['po_upload_file']) && ($docType == Documents::BU || $docType == Documents::W9)) {
                $_SESSION['po_upload_file']['doctype'] = $docType;
                $_SESSION['po_upload_file']['fed_id'] = $fed_id;
                $_SESSION['po_upload_file']['company_name'] = $comp_name;
                $_SESSION['po_upload_file']['complete'] = true;
                if ($docType == Documents::W9) {
                    $_SESSION['po_upload_file_w9'][1] = $_SESSION['po_upload_file'];
                    Documents::uploadDocuments('po_upload_file_w9', true);
                    $result['success'] = true;
                    $result['vendors_list'] = $this->renderPartial('application.views.po.vendors_list', array(
                        'vendors' => Vendors::getClientVendorsShortcutList(Yii::app()->user->clientID),
                    ), true);
                } else {
                    $document = new Documents();
                    $document->Document_Type = $_SESSION['po_upload_file']['doctype'];
                    $document->User_ID = Yii::app()->user->userID;
                    $document->Client_ID = Yii::app()->user->clientID;
                    $document->Project_ID = Yii::app()->user->projectID;
                    $document->Created = date("Y-m-d H:i:s");
                    $document->save();

                    Audits::LogAction($document->Document_ID ,Audits::ACTION_UPLOAD);

                    // insert image
                    $image = new Images();
                    $imageData = addslashes(fread(fopen($_SESSION['po_upload_file']['filepath'],"rb"),filesize($_SESSION['po_upload_file']['filepath'])));
                    $image->Document_ID = $document->Document_ID;
                    $image->Img = $imageData;
                    $image->File_Name = $_SESSION['po_upload_file']['name'];
                    $image->Mime_Type = $_SESSION['po_upload_file']['mimetype'];
                    $image->File_Hash = sha1_file($_SESSION['po_upload_file']['filepath']);
                    $image->File_Size = intval(filesize($_SESSION['po_upload_file']['filepath']));
                    $image->Pages_Count = FileModification::calculatePagesByPath($_SESSION['po_upload_file']['filepath']);
                    $image->save();

                    // delete file from temporary catalog
                    @unlink($_SESSION['po_upload_file']['filepath']);

                    if ($poId == 0) {
                        $_SESSION['last_uploaded_backup'] = $document->Document_ID;
                    } else if(Pos::hasStagingPOAccess($poId) || Pos::hasPOAccess($poId)) {
                        $po = Pos::model()->findByPk($poId);
                        $po->PO_Backup_Document_ID = $document->Document_ID;
                        $po->save();
                        if (isset($_SESSION['last_uploaded_backup'])) {
                            unset($_SESSION['last_uploaded_backup']);
                        }

                        if ($po->PO_Approval_Value == Pos::APPROVED) {
                            LibraryDocs::addDocumentToFolder($document->Document_ID, $po->Vendor_ID);
                        }
                    }

                    $result['success'] = true;
                    $result['back_up_block'] = $this->renderPartial('application.views.po.tabs.back_up', array(
                        'backUp' => array(
                            'file' => $image,
                            'document' => $document,
                        ),
                    ), true);
                }
            }

            if (file_exists($_SESSION['po_upload_file']['filepath'])) {
                @unlink($_SESSION['po_upload_file']['filepath']);
            }
            unset($_SESSION['po_upload_file']);

            echo CJSON::encode($result);
        }
    }

    /**
     * Update PO tracking note
     */
    public function actionUpdatePOTrackingNote()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['note']) && isset($_POST['poId'])) {
            $note = trim($_POST['note']);
            $poId = trim($_POST['poId']);

            $po = Pos::model()->findByPk($poId);

            if ($po && Documents::hasAccess($po->Document_ID)) {
                $po->PO_Pmts_Tracking_Note = $note;
                if ($po->validate()) {
                    $po->save();
                }
            }
        }
    }

    /**
     * Add session pointer to send to approve item
     */
    public function actionAddSessionPointerToApprove()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['poId'])) {
            $poId = trim($_POST['poId']);
            if (Pos::hasStagingPOAccess($poId)) {
                $_SESSION['po_to_approve'] = $poId;
            }
        }
    }

    /**
     * Add session pointer to send to approve item
     */
    public function actionAddSessionPointerToReview()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['apr_ids_arr'])) {
            $_SESSION['po_to_review'] = array();
            $arr = $_POST['apr_ids_arr'];
            $i=1;
            foreach ($arr as $arr_item){
                $_SESSION['po_to_review'][$i]= $arr_item;
                $_SESSION['po_hard_approve'][$i]= $arr_item;
                $i++;
            }

        }

    }

    public function actionMarkAsVoid()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['po_id'])) {
            $po_id = trim($_POST['po_id']);

            //if (Pos::hasStagingPOAccess($po_id)) {
                Pos::MarkStagingItemAsVoid($po_id);
               // $this->redirect('/po/create');
            //}
        }
    }

    /**
     * Get in place input html
     */
    public function actionGetInPlaceInput()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['po_trk_id'])) {


            $poTracks = PoPmtsTraking::model()->findByPk(intval($_POST['po_trk_id']));


            $result_array = array();
            $result_array[] = array('PO_Trkng_Desc'=>'<input type="text" value="' . $poTracks->PO_Trkng_Desc . '" maxlength="' . 50 . '" class="in_place_input" name="in_place_input">');
            $result_array[] = array('PO_Trkng_Inv_Date'=>'<input type="text" value="' . $poTracks->PO_Trkng_Inv_Date . '" maxlength="' . 50 . '" class="in_place_input" name="in_place_input">');
            $result_array[] = array('PO_Trkng_Inv_Number'=>'<input type="text" value="' . $poTracks->PO_Trkng_Inv_Number . '" maxlength="' . 50 . '" class="in_place_input" name="in_place_input">');
            $result_array[] = array('PO_Trkng_Pmt_Amt'=>'<input type="text" value="' . $poTracks->PO_Trkng_Pmt_Amt . '" maxlength="' . 50 . '" class="in_place_input" name="in_place_input">');

            echo CJSON::encode($result_array);
        }
    }



    /**
     * Get in place input html
     */
    public function actionSavePoTrack()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['po_trk_id'])) {


            $poTracks = PoPmtsTraking::model()->findByPk(intval($_POST['po_trk_id']));
            $poTracks->PO_Trkng_Desc = strval($_POST['PO_Trkng_Desc']);
            $poTracks->PO_Trkng_Inv_Date = strval($_POST['PO_Trkng_Inv_Date']);
            $poTracks->PO_Trkng_Inv_Number = strval($_POST['PO_Trkng_Inv_Number']);


            $new_pmt_amnt = strval($_POST['PO_Trkng_Pmt_Amt']);
            $sum_of_other_tracks = PoPmtsTraking::getSumOfOtherTracks($poTracks->PO_ID,$poTracks->PO_Trkng_ID);

            if (($sum_of_other_tracks + $new_pmt_amnt) <= $poTracks->PO_Trkng_Beg_Balance ) {
                $poTracks->PO_Trkng_Pmt_Amt = $new_pmt_amnt;
            }

            $poTracks->save();

        }
    }

    /**
     * Recreate PDF file for generated PO
     */
    public function actionRecreate()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['doc_id'])) {
            $doc_id = intval($_POST['doc_id']);
            $document = Documents::model()->findByPk($doc_id);

            if ($document->Origin == 'G') {

                $po = Pos::model()->findByAttributes(array(
                    'Document_ID' => $doc_id
                ));

                Documents::pdfGeneration($po->Document_ID,'PO',$po->PO_Approved);
                Audits::LogAction($po->Document_ID,Audits::ACTION_REPDF);

            }

        }
    }

    /**
     * Creates empty PO without saving to database and generates temp PDF for it.
     */
    public function actionCreateForPreview()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['Pos'])) {

            $po = new Pos();
            $po->PO_Date = date('Y-m-d');
            $po->PO_Number = Pos::getNewPoNumber();
            $po->Sign_Requested_By = Yii::app()->user->userID;
            $po->attributes = $_POST['Pos'];


            // set description details
            $result = PoDescDetail::prepareDescDetails($_POST['PoDescDetail']);

            $detailsToSave = $result['detailsToSave'];
            $subtotal = $result['$subtotal'];
            $total = $result['total'];

            // generate descr. details array of models
            $detailsToSave = PoDescDetail::preparePODetailsArray($po->PO_ID, $detailsToSave);

            // set dists
            $distsToSave = PoDists::prepareDistsToSave($_POST['PoDists']);
            // generate dists.  array of models
            $distsToSave = PoDists::preparePODistsArray($po->PO_ID,$distsToSave);

            $total += round(floatval($po->PO_Tax), 2) + round(floatval($po->PO_Other_Chg), 2) + round(floatval($po->PO_Delivery_Chg), 2);
            $po->PO_Total = (string) round($total, 2);
            $po->PO_Subtotal = (string) round($subtotal, 2);
            $po->PO_Card_Last_4_Digits = isset($_POST['Pos']['PO_Card_Last_4_Digits']) ? $_POST['Pos']['PO_Card_Last_4_Digits'] : null;


            $approvalRange = Aps::getUserApprovalRange();
            $file_array = POs::generatePdfFpdfPreview($po, $detailsToSave,$distsToSave,($approvalRange['user_appr_val'] == Pos::APPROVED));

            $file_id = FileCache::addToFileCache($file_array['filepath']);

            echo $file_id;

        }
    }

    /**
     * Creates empty PO without saving to database and generates temp PDF for it.
     */
    public function actionGetPOCreationForm()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['po_id']) ) {

            $po_id = intval($_POST['po_id']);
            $po = Pos::model()->findByPk($po_id);

            if ($po) {
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

                //descriptions
                $descDetails = PoDescDetail::fromModelToArray($po);



                // get current vendor info
                $vendorAdmin = null;
                if ($po->Vendor_ID != 0) {

                    $condition = UsersClientList::getClientAdminCondition($client->Client_ID);
                    $vendorAdmin = UsersClientList::model()->with('user.person')->find($condition);
                }

                $vendors = array();
                if ($document) {
                    //get vendors Shortcut
                    $vendors = Vendors::getClientVendorsShortcutList($document->Client_ID);
                }

                $coaStructure = CoaStructure::model()->findByAttributes(array(
                    'Project_ID' => Yii::app()->user->projectID,
                ));

                $this->renderPartial('application.views.widgets.po_dataentry_full_widget', array(
                    'client'=>$client,
                    'company'=>$company,
                    'document' => $document,
                    'po' => $po,
                    'dists' => $dists['dists'],
                    'dists_empty' => $dists['empty'],
                    'descDetails' => $descDetails,
                    'vendors'=> $vendors,
                    'vendor'=> $vendor,
                    'vendorAdmin'=> $vendorAdmin,
                    'coaStructure'=>$coaStructure,
                    'return_url'=> $return_url
                ));
            }

        }
    }





}