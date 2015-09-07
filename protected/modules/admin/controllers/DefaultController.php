<?php

class DefaultController extends Controller
{
    public $layout='//layouts/column2';
    public $clientAdmins = array();
    public $approvers = array();
    public $approvalValue = array();

    /**
     * Available user types
     * @var array
     */
    public $userTypes = array(
        'User' => UsersClientList::USER,
        'Approver' => UsersClientList::APPROVER,
        'Processor' => UsersClientList::PROCESSOR,
        'ClAdmin' => UsersClientList::CLIENT_ADMIN,
        'Admin' => Users::ADMIN,
        'DBAdmin' => Users::DB_ADMIN,
        'DEC' => Users::DATA_ENTRY_CLERK,
    );

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
            array('allow',
                'actions'=>array('index', 'getcompanyinfo', 'getfiltereduserstoapprovelist', 'approveusers',
                    'getfilteredclientslist', 'getclientuserslist', 'changeclientadmins', 'getfiltereduserslist',
                    'assignusertoclient', 'getclientuserslistapprvalue', 'updateusersapprovalvalues', 'getfiltereduserstoactivelist',
                    'setactiveusers', 'finduserbylogin', 'getfiltereddocumentslist', 'reassigndocumentsclients', 'getdocumentfile',
                    'getuserfile', 'getfiltereduserstotypelist', 'settypeusers', 'getclientsprojectslist', 'getuserclientprojects',
                    'getfilteredemptycompanieslist', 'getemptycompanyinfo', 'generateletter', 'checkuserapprovalvalue',
                    'getimageviewblock', 'getusertypeinfo', 'getclientactiveinfo', 'getfilteredclientstoactivelist', 'setactiveclients',
                    'updateservicelevelsettings', 'getcompanyservicelevelsettings', 'getservicelevelsettings', 'updatecompanyservicelevel',
                    'addcompanypayment','ManageExistingUsersList'
                ),
                'users'=>array('admin', 'db_admin'),
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    /**
     * Index action
     * @param string $tab
     */
    public function actionIndex($tab = 'cl_adm_change')
    {
        //get users to approve
        $usersToApprove = UsersToApprove::model()->with('user')->findAllByAttributes(array(
            'Approved_By_Admin' => UsersToApprove::NOT_APPR_BY_ADMIN,
        ));

        if ($_SESSION['admin_active_tab']) {
            $tab = $_SESSION['admin_active_tab'];
        }
        //get clients list
        $clientsList = Clients::model()->getClientsList();

        //get service level settings
        $serviceLevelSettings = ServiceLevelSettings::model()->findAll();

        //get service requests for support
        $support_requests = SupportRequests::model()->findAllByAttributes(array(
           'Problem_Status'=>'W'
        ));


        $auto_loaded_tabs = $this->generateTabsForAutoLoad(); //array of views and additional variables



        $this->render('index' , array(
            'tab' => $tab,
            'usersToApprove' => $usersToApprove,
            'support_requests'=>$support_requests,
            'clientsList' => $clientsList,
            'serviceLevelSettings' => $serviceLevelSettings,
            'auto_loaded_tabs'=>$auto_loaded_tabs
        ));
    }

    /**
     * Get company info for users to approve tab
     */
    public function actionGetCompanyInfo()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['type'])) {
            if ($_POST['type'] != '') {
                $show = false;
                $view_name = 'users_to_approve_company';
                $companyInfo = array();
                $id = intval($_POST['userId']);

                // get user to approve
                $usersToApprove = UsersToApprove::model()->with('client', 'user')->findByPk($id);
                $client = $usersToApprove->client;
                $user = $usersToApprove->user;

                // get company info
                $company = $client->company;
                $adreses = $company->adreses;
                $adress = $adreses[0];
                if ($company && $adress) {
                    $show = true;

                    if ($usersToApprove->New_Client == UsersToApprove::NEW_CLIENT) {
                        $companyInfo['add_text'] = 'Wants to add new company:';
                    } else {
                        $companyInfo['add_text'] = 'Wants to join existing company:';
                    }

                    $companyInfo['name'] = $company->Company_Name;
                    $companyInfo['fed_id'] = $company->Company_Fed_ID;
                    $companyInfo['adr'] = $adress->Address1;
                    $companyInfo['city'] = $adress->City;
                    $companyInfo['state'] = $adress->State;
                    $companyInfo['zip'] = $adress->ZIP;

                    $companyInfo['client_admins'] = '';
                    $companyInfo['come_from'] = '';
                    $companyInfo['client_active'] = 'Client is not active now';
                    $companyInfo['company_activated'] = "This company will be automatically activated after user approval";

                    // check existing of client-admins of company
                    if ($usersToApprove->New_Client == UsersToApprove::OLD_CLIENT && !UsersClientList::checkClientForAdmins($client->Client_ID)) {
                        $companyInfo['client_admins'] = "This company doesn't have Client Admin";
                        if ($user->User_Pwd == md5('temp100')) {
                            $companyInfo['come_from'] = 'User came from registration form';
                        } else {
                            $companyInfo['come_from'] = 'User was previously in the system and asks for being added to the company by "Request to join company"';
                        }
                    }

                    $view_name = 'users_to_approve_company';
                }

                if ( $usersToApprove->client->Client_Type === '2' ){
                    //single user without company
                    $show = true;
                    $companyInfo['add_text'] = 'Single user without company';

                    $companyInfo['name'] = 'not defined';
                    $companyInfo['fed_id'] ='not defined';
                    $companyInfo['adr'] = 'not defined';
                    $companyInfo['city'] = 'not defined';
                    $companyInfo['state'] = 'not defined';
                    $companyInfo['zip'] = 'not defined';

                    $companyInfo['client_admins'] = '';
                    $companyInfo['come_from'] = '';
                    $companyInfo['client_active'] = 'This is a single user without company';
                    $companyInfo['company_activated'] = "This company will be automatically activated after user approval";



                    if ($user->User_Pwd == md5('temp100')) {
                        $companyInfo['come_from'] = 'User came from registration form';
                    } else {
                        $companyInfo['come_from'] = 'User was previously in the system and asks for being added to the company by "Request to join company"';
                    }
                    $view_name = 'users_to_approve_without_company';
                }


                $this->renderPartial($view_name , array(
                    'show' => $show,
                    'companyInfo' => $companyInfo,
                    'new_client' => $usersToApprove->New_Client,
                    'active_client' => $client->Client_Status,
                ));
            }
            die;
        }
    }

    /**
     * Change client admins action
     */
    public function actionChangeClientAdmins()
    {
        if (isset($_GET['clientID'])) {

            //check client id
            $clientID = intval($_GET['clientID']);
            if ($clientID == 0) {
                $this->redirect('/admin');
                die;
            }

            // get client with users
            $client = Clients::model()->with('users', 'company')->findByPk($clientID);
            $client_users = $client->users;
            $company = $client->company;
            $userTypes = array();

            if ($client_users) {
                foreach ($client_users as $key => $cuser) {
                    $uClRow = UsersClientList::model()->findByAttributes(array(
                        'User_ID'=>$cuser->User_ID,
                        'Client_ID'=>$clientID,
                    ));
                    $this->clientAdmins[$cuser->User_ID] = $uClRow->hasClientAdminPrivileges() ? 1 : 0;
                    $userTypes[$cuser->User_ID] = $uClRow->User_Type;
                }
            }

            // change admins
            foreach ($_GET as $id => $type) {
                if ($id != 'clientID' && is_numeric($id) && isset($userTypes[$id]) && isset($this->userTypes[$type])) {
                    if ($userTypes[$id] != $this->userTypes[$type]) {
                        $user = Users::model()->with('person')->findByPk($id);
                        $userToClient = UsersClientList::model()->findByAttributes(array(
                            'User_ID' => $id,
                            'Client_ID' => $clientID,
                        ));
                        if (in_array($this->userTypes[$type], UsersClientList::$clientAdmins)) {
                            $userToClient->User_Type = $this->userTypes[$type];

                            // check company
                            if ($company->Auth_Url !== NULL || $company->Auth_Code !== NULL) {
                                $company->Auth_Url = NULL;
                                $company->Auth_Code = NULL;
                                $company->save();
                            }

                            $mailSuccess = Mail::sendClientAssignAdminMail($user->person->Email, $user->person->First_Name, $user->person->Last_Name, $client->company->Company_Name, true);
                            Projects::assignClientAdminProjects($userToClient->User_ID, $userToClient->Client_ID);
                        } else {
                            $userToClient->User_Type = $this->userTypes[$type];

                            $condition = UsersClientList::getClientAdminCondition($clientID);
                            $userToClientAdmin = UsersClientList::model()->find($condition);

                            if ($userToClientAdmin) {
                                $currentAdmin = Users::model()->with('person')->findByPk($userToClientAdmin->User_ID);
                                $currentAdminEmail = $currentAdmin->person->Email;
                            } else {
                                $currentAdminEmail = false;
                            }

                            $mailSuccess = Mail::sendClientAssignAdminMail($user->person->Email, $user->person->First_Name, $user->person->Last_Name, $client->company->Company_Name, false, $currentAdminEmail);
                        }
                        $userToClient->save();
                        $userToClient->User_Approval_Value = UsersClientList::checkUserApprovalValue($id, $clientID, $userToClient->User_Approval_Value);
                        $userToClient->save();
                    }
                }
            }

            Yii::app()->user->setFlash('success', "Client User Types have been successfully changed!");
        } else {
            Yii::app()->user->setFlash('success', "Client User Types were not changed!");
        }
        $this->redirect('/admin');
    }

    /**
     * Assign user to client
     */
    public function actionAssignUserToClient()
    {
        if (isset($_GET['clientID']) && isset($_GET['userID']) && isset($_GET['projectID'])) {
            $clientID = intval($_GET['clientID']);
            $projectID = intval($_GET['projectID']);
            $userID = intval($_GET['userID']);

            //check input data
            if ($clientID <= 0 || $userID <= 0 || $projectID <= 0) {
                $this->redirect('/admin?tab=us_cl_assign');
                die;
            }

            $userToAdd = Users::model()->with('person')->findByPk($userID);

            // check user to project row
            $userToProject = UsersProjectList::model()->findByAttributes(array(
                'User_ID' => $userID,
                'Client_ID' => $clientID,
                'Project_ID' => $projectID,
            ));

            if ($userToProject) {
                Yii::app()->user->setFlash('success', "User is already assigned to selected project!");
                $this->redirect('/admin?tab=us_cl_assign');
                die;
            }

            // check user to client row
            $userToClient = UsersClientList::model()->findByAttributes(array(
                'User_ID' => $userID,
                'Client_ID' => $clientID,
            ));

            if (!$userToClient) {
                // add user-client relationship
                $usersClientList = new UsersClientList;
                $usersClientList->User_ID = $userID;
                $usersClientList->Client_ID = $clientID;
                if (in_array($userToAdd->User_Type, UsersClientList::$clientAdmins)) {
                    $usersClientList->User_Type = UsersClientList::CLIENT_ADMIN;
                    $usersClientList->User_Approval_Value = Aps::APPROVED;
                } else {
                    $usersClientList->User_Type = UsersClientList::USER;
                    $usersClientList->User_Approval_Value = Aps::NOT_READY_FOR_APPROVAL;
                }
                $usersClientList->save();
            }

            // add user-project relationship
            $usersProjectList = new UsersProjectList;
            $usersProjectList->User_ID = $userID;
            $usersProjectList->Client_ID = $clientID;
            $usersProjectList->Project_ID = $projectID;
            $usersProjectList->save();

            $userToAdd->Active = Users::ACTIVE;
            if ($userToAdd->Default_Project == 0) {
                $userToAdd->Default_Project = $projectID;
            }

            $client = Clients::model()->with('company')->findByPk($clientID);
            if ($userToAdd->User_Pwd == md5('temp100')) {
                $password = Helper::generatePassword();
                $userToAdd->User_Pwd = md5($password);
                Mail::sendUserRegistrationMail($userToAdd->person->Email, $userToAdd->User_Login, $password, $userToAdd->person->First_Name, $userToAdd->person->Last_Name, $client->company->Company_Name);
            } else {
                Mail::sendAddUserToClientMail($userToAdd->person->Email, $userToAdd->person->First_Name, $userToAdd->person->Last_Name, $client->company->Company_Name);
            }

            $userToAdd->save();

            $usersToApprove = UsersToApprove::model()->findByAttributes(array(
                'User_ID'=>$userID,
                'Client_ID'=>$clientID,
            ));

            if ($usersToApprove) {
                $usersToApprove->delete();
            }

            $usersToApprove = UsersToApprove::model()->findAllByAttributes(array(
                'User_ID'=>$userID,
            ));

            if (count($usersToApprove) > 0) {
                foreach($usersToApprove as $userToApprove) {
                    if ($userToApprove->Approved_By_Admin == UsersToApprove::NOT_APPR_BY_ADMIN) {
                        $userToApprove->Approved_By_Admin = UsersToApprove::APPR_BY_ADMIN;
                        $userToApprove->save();
                    }
                }
            }

            Yii::app()->user->setFlash('success', "User has been successfully added to project's list!");
        } else {
            Yii::app()->user->setFlash('success', "User was not added to project's list! Try again.");
        }
        $this->redirect('/admin?tab=us_cl_assign');
    }

    /**
     * Reassign document's client action
     */
    public function actionReassignDocumentsClients()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['userId']) && isset($_POST['docs'])) {
            $userID = intval($_POST['userId']);
            $docs = $_POST['docs'];

            //check input data
            if ($userID == 0) {
                $this->redirect('/admin?tab=doc_reassign');
                die;
            }

            // change document's clients
            foreach ($docs as $doc) {
                $docID = intval($doc[0]);
                $clientID = intval($doc[1]);
                $projectID = intval($doc[2]);
                if ($docID == 0) {
                    $this->redirect('/admin?tab=doc_reassign');
                    die;
                }

                $userToProject = UsersProjectList::model()->findByAttributes(array(
                    'User_ID' => $userID,
                    'Client_ID' => $clientID,
                    'Project_ID' => $projectID,
                ));

                $document = Documents::model()->with('w9')->findByPk($docID);
                if ($userToProject && $document->User_ID == $userID) {
                    $document->Client_ID = $clientID;
                    $document->Project_ID = $projectID;
                    $document->save();
                }
            }
            Yii::app()->user->setFlash('success', "Documents have been reassigned");
        } else {
            Yii::app()->user->setFlash('success', "You didn't choose the documents!");
        }
    }

    /**
     * Update user's approval values action
     */
    public function actionUpdateUsersApprovalValues()
    {
        if (isset($_POST['clientID']) && isset($_POST['users'])) {
            $clientID = intval($_POST['clientID']);
            $users = $_POST['users'];

            //check input data
            if ($clientID == 0) {
                $this->redirect('/admin?tab=us_appr_value');
                die;
            }

            //change approval values
            foreach ($users as $userID => $approvalValue) {
                $userID = intval($userID);
                if ($userID == 0) {
                    $this->redirect('/admin?tab=us_appr_value');
                    die;
                }

                $userToClient = UsersClientList::model()->findByAttributes(array(
                    'User_ID' => $userID,
                    'Client_ID' => $clientID,
                ));


                if ($userToClient) {
                    $previousApprovalValue = intval($userToClient->User_Approval_Value);
                    $approvalValue = intval($approvalValue);

                    if ($approvalValue > Aps::APPROVED) {
                        $approvalValue = Aps::APPROVED;
                    } else if ($approvalValue < Aps::NOT_READY_FOR_APPROVAL) {
                        $approvalValue = Aps::NOT_READY_FOR_APPROVAL;
                    }

                    $userToClient->User_Approval_Value = $approvalValue;
                    if ( $previousApprovalValue == Aps::APPROVED && $approvalValue!=Aps::APPROVED && UsersClientList::isLastApprover($userToClient->Client_ID,$userToClient->User_ID) ) {
                        Yii::app()->user->setFlash('success', "At least one Approver must have an Approval value of 100.");
                        //$this->redirect('/admin?tab=us_appr_value');
                    }

                    $userToClient->save();




                }
            }

            Yii::app()->user->setFlash('success', "Approval values have been successfully updated!");
            $this->redirect('/admin?tab=us_appr_value');
        } else {
            Yii::app()->user->setFlash('success', "Approval values have not been updated!");
            $this->redirect('/admin?tab=us_appr_value');
        }
    }

    /**
     * Set active users action
     */
    public function actionSetActiveUsers()
    {
        if (count($_GET) > 0) {
            foreach ($_GET as $userID => $activeValue) {
                $userID = intval($userID);
                $activeValue = intval($activeValue);

                //check input data
                if ($userID == 0) {
                    $this->redirect('/admin?tab=user_act_mgmt');
                    die;
                }

                // get user
                $user = Users::model()->findByPk($userID);

                // change active value
                if ($user->Active != $activeValue) {
                    if ($activeValue == Users::NOT_ACTIVE) {
                        $user->Active = $activeValue;
                        $user->save();
                    } else if ($activeValue == Users::ACTIVE) {
                        $userProjects = UsersProjectList::model()->findAllByAttributes(array(
                            'User_ID' => $userID,
                        ));
                        if (count($userProjects) > 0 && $user->Default_Project == 0) {
                            $user->Default_Project = $userProjects[0]->Project_ID;
                        }
                        $user->Active = $activeValue;
                        $user->save();
                    }
                }
            }

            Yii::app()->user->setFlash('success', "Active values have been successfully updated!");
        } else {
            Yii::app()->user->setFlash('success', "You don't choose the users!");
        }
        $this->redirect('/admin?tab=user_act_mgmt');
    }

    /**
     * Set active clients action
     */
    public function actionSetActiveClients()
    {
        if (count($_GET) > 0) {
            foreach ($_GET as $clientID => $activeValue) {
                $clientID = intval($clientID);
                $activeValue = intval($activeValue);

                //check input data
                if ($clientID == 0) {
                    $this->redirect('/admin?tab=client_act_mgmt');
                    die;
                }

                // get client
                $client = Clients::model()->with('users', 'company', 'service_settings')->findByPk($clientID);

                // change active value
                if ($client->Client_Status != $activeValue) {
                    if ($activeValue == Clients::NOT_ACTIVE) {
                        $client->deactivate();
                    } else if ($activeValue == Clients::ACTIVE) {
                        $client->activate();
                        $clientServiceSettings = $client->service_settings;
                        if (!$clientServiceSettings || $clientServiceSettings->Active_To <= date('Y-m-d')) {
                            ClientServiceSettings::addClientServiceSettings($client->Client_ID, true);
                        }
                    }
                }
            }

            Yii::app()->user->setFlash('success', "Active values have been successfully updated!");
        } else {
            Yii::app()->user->setFlash('success', "You don't choose the users!");
        }
        $this->redirect('/admin?tab=client_act_mgmt');
    }

    /**
     * Set users' type action
     */
    public function actionSetTypeUsers()
    {
        if (count($_GET) > 0) {
            foreach ($_GET as $userID => $typeValue) {
                $userID = intval($userID);

                //check input data
                if ($userID == 0 || !isset($this->userTypes[$typeValue])) {
                    $this->redirect('/admin?tab=user_type_mgmt');
                    die;
                }

                $typeValue = $this->userTypes[$typeValue];

                // get user
                $user = Users::model()->findByPk($userID);


                if ($typeValue == 'DB Admin') {
                    //we need to check it

                    //if current user is
                    if (Yii::app()->user->id == 'admin') {
                        Yii::app()->user->setFlash('error', "Admin can't change his user type to 'DBAdmin'");
                    } else {
                        $user->User_Type = $typeValue;
                        $user->save();
                        Yii::app()->user->setFlash('success', "Users' types have been successfully updated!");
                    }
                } else {
                    $user->User_Type = $typeValue;
                    $user->save();
                    Yii::app()->user->setFlash('success', "Users' types have been successfully updated!");
                }




            }


        } else {
            Yii::app()->user->setFlash('success', "You don't choose the users!");
        }
        $this->redirect('/admin?tab=user_type_mgmt');
    }

    /**
     * Get client's users list
     */
    public function actionGetClientUsersList()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['clientId'])) {
            $clientId = $_POST['clientId'];
            $client = Clients::model()->with('company', 'users')->findByPk($clientId);
            $client_users = $client->users;
            $userTypes = array();
            if ($client_users) {
                foreach ($client_users as $key => $cuser) {
                    $uClRow = UsersClientList::model()->findByAttributes(array(
                        'User_ID'=>$cuser->User_ID,
                        'Client_ID'=>$clientId,
                    ));
                    $this->clientAdmins[$cuser->User_ID] = $uClRow->hasClientAdminPrivileges() ? 1 : 0;

                    if ($uClRow->User_Type == UsersClientList::CLIENT_ADMIN) {
                        $userTypes[$cuser->User_ID] = 'Cl. Admin';
                    } else {
                        $userTypes[$cuser->User_ID] = $uClRow->User_Type;
                    }
                }
                usort($client_users, array($this, 'sortClientUsers'));
            }

            $this->renderPartial('client_users_list' , array(
                'client_users' => $client_users,
                'userTypes' => $userTypes,
            ));
        }
    }

    /**
     * Get client's users list to approval value tab
     */
    public function actionGetClientUsersListApprValue()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['clientId'])) {
            $clientId = $_POST['clientId'];



            $_SESSION['tabs_to_auto_load']['client_users_list_appr_value'] = array('client_id'=> $clientId);
            $_SESSION['admin_active_tab']='us_appr_value';



            //$approvers_array = $this->getApproversArray($clientId);
            $approvers_array = UsersClientList::getApproversArray($clientId);
            $this->renderPartial('client_users_list_appr_value' , array(
              //  'client_users' => $client_users,
                'approvers_array'=>$approvers_array,
               // 'admins_array'=>$admins_array,
            ));
        }
    }

    /**
     * Get user's filtered documents list action
     */
    public function actionGetFilteredDocumentsList()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['userId']) && isset($_POST['date'])) {
            $userId = intval($_POST['userId']);

            if ($_POST['date'] != '') {
                $dateArr = explode('/', $_POST['date']);
                if (count($dateArr) == 3) {
                    $date = Helper::convertDateToServerTimezone($_POST['date']);
                } else {
                    $date = '';
                }
            } else {
                $date = '';
            }

            $userInfo = Users::model()->with('clients.company')->findByPk($userId);

            $documents = new Documents();
            $userDocuments = $documents->findUserDocuments($userId, $date);
            $userProjects = Projects::getUserProjects($userId);

            $this->renderPartial('filtered_documents_list' , array(
                'userDocuments' => $userDocuments,
                'userProjects' => $userProjects,
                'userInfo' => $userInfo,
            ));
        }
    }

    /**
     * Get user's client project
     */
    public function actionGetUserClientProjects()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['userId']) && isset($_POST['clientId'])) {
            $userId = intval($_POST['userId']);
            $clientId = intval($_POST['clientId']);

            $userProjects = Projects::getUserProjects($userId, $clientId);

            $list = $this->renderPartial('user_client_projects' , array(
                'userProjects' => $userProjects,
            ), true);

            $projectID = 0;
            $projectName = 'No projects';
            if (count($userProjects) > 0) {
                foreach ($userProjects as $id => $project) {
                    $projectID = $id;
                    $projectName = $project;
                    break;
                }
            }

            $result = array(
                'list' => $list,
                'projectID' => $projectID,
                'projectName' => $projectName,
            );

            echo CJSON::encode($result);
        }
    }

    /**
     * Get filtered clients list by company name
     */
    public function actionGetFilteredClientsList()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['companyName'])) {
            $companyName = $_POST['companyName'];

            $clientsList = array();
            $criteria=new CDbCriteria;
            $criteria->condition='t.Client_Number!=0';
            if (trim($_POST['companyName']) != '') {
                $criteria->compare('company.Company_Name',$companyName,true);
            }
            $criteria->order = "company.Company_Name ASC";
            $clients = Clients::model()->with('company')->findAll($criteria);
            foreach ($clients as $client) {
                if ($client && $client->company) {
                    $clientsList[$client->Client_ID] = $client->company->Company_Name;
                }
            }

            $this->renderPartial('filtered_clients_list' , array(
                'clientsList' => $clientsList,
            ));
        }
    }

    /**
     * Get client's projects list
     */
    public function actionGetClientsProjectsList()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['clientID'])) {
            $clientID = intval($_POST['clientID']);

            $projectsList = array();
            $criteria=new CDbCriteria;
            $criteria->condition="t.Client_ID='" . $clientID . "'";
            $criteria->order = "t.Project_Name ASC";
            $projects = Projects::model()->findAll($criteria);
            foreach ($projects as $project) {
                if ($project) {
                    $projectsList[$project->Project_ID] = $project->Project_Name;
                }
            }

            $this->renderPartial('clients_projects_list' , array(
                'projectsList' => $projectsList,
                'clientID' => $clientID,
            ));
        }
    }

    /**
     * Get filtered users list by lastname
     */
    public function actionGetFilteredUsersList()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['lastname'])) {
            $lastname = strtolower(trim($_POST['lastname']));

            if ($lastname != '') {
                /*getFilteredUsersByLastName(false)*/
                $criteria=new CDbCriteria;
                if ($lastname != '*') {
                    $criteria->compare('Last_Name', $lastname, true);
                }
                $criteria->order = 'Last_Name ASC';
                $all_users = Persons::model()->with('user')->findAll($criteria);
                $find = true;
            } else {
                $all_users = array();
                $find = false;
            }
            //var_dump($all_users); die;
            $this->renderPartial('filtered_users_list' , array(
                'all_users' => $all_users,
                'find' => $find,
            ));
        }
    }

    /**
     * Get existing users list by client and/or project
     */
    public function actionManageExistingUsersList()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['client_id'])) {


            $user_to_delete = intval($_POST['user_to_delete']);
            $client_id = intval($_POST['client_id']);
            $project_id = intval($_POST['project_id']);

            if ($user_to_delete) {

                // for the selected client UserClientList relation
                $ucl_selected = UsersClientList::model()->findByAttributes(array(
                    'User_ID'=>Yii::app()->user->userID,
                    'Client_ID'=>$client_id,
                ));

                if ($ucl_selected) {
                    //if current user has relation to selected company
                    if ($ucl_selected->hasClientAdminPrivileges()) {
                        //if current user is client admin for selected company
                        $current_is_admin_for_selected = true;
                    }
                }

                $client = Clients::model()->with('company', 'users', 'projects')->findByPk($client_id);
                $client_users = $client->users;


                if ($current_is_admin_for_selected || Yii::app()->user->id =='db_admin' ) {
                    // if user to delete is not client admin
                    $relationRow = UsersClientList::model()->findByAttributes(array(
                        'User_ID' => $user_to_delete,
                        'Client_ID' => $client_id,
                    ));
                    if ($relationRow) {
                        $relationRow->delete();
                    }

                    $condition = new CDbCriteria();
                    $condition->condition = "Client_ID = '" . $client_id . "'";
                    $condition->addCondition("User_ID = '" . $user_to_delete . "'");
                    UsersProjectList::model()->deleteAll($condition);

                    $user = Users::model()->with('projects')->findByPk($user_to_delete);

                    $defaultProject = Projects::model()->findByPk($user->Default_Project);
                    if ($user->projects && $defaultProject && $defaultProject->Client_ID == Yii::app()->user->clientID) {
                        $user->Default_Project = $user->projects[0]->Project_ID;
                    } else if ($defaultProject && $defaultProject->Client_ID == Yii::app()->user->clientID) {
                        $user->Default_Project = 0;
                    }
                    $user->save();
                    $emailSuccess = Mail::sendRemoveUserFromClientMail($user->person->Email, $user->person->First_Name, $user->person->Last_Name, $client->company->Company_Name);

                    Yii::app()->user->setFlash('success', "User has been removed successfully!");
                } else {
                    // if user to delete is client admin
                    Yii::app()->user->setFlash('success', "You don't have permission for this action!");
                }


            }

            $client = Clients::model()->with('company', 'users', 'projects')->findByPk($client_id);

            // get client's users list
            $client_users = $client->users;
            foreach ($client_users as $key => $cuser) {
                $uClRow = UsersClientList::model()->findByAttributes(array(
                    'User_ID'=>$cuser->User_ID,
                    'Client_ID'=>Yii::app()->user->clientID,
                ));
                //$this->clientAdmins[$cuser->User_ID] = $uClRow->hasClientAdminPrivileges() ? 1 : 0;

                $result_array[] = array(
                    'User_ID'=>$cuser->User_ID,
                    'Client_ID'=>$client_id,
                    'Project_ID'=>$project_id,
                    'First_Name'=>$cuser->person->First_Name,
                    'Last_Name'=>$cuser->person->Last_Name,
                    'Client_Admin'=>$uClRow->User_Type=='Client Admin' ? 1 : 0,
                );
            }




            //var_dump($all_users); die;
            $this->renderPartial('existing_users_list' , array(
                'all_users' => $result_array,
            ));
        }
    }

    /**
     * Get filtered users list by lastname to users_active_mgmt tab
     */
    public function actionGetFilteredUsersToActiveList()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['lastname'])) {
            $lastname = strtolower(trim($_POST['lastname']));

            if ($lastname != '') {
                /*getFilteredUsersByLastName(false)*/
                $criteria=new CDbCriteria;
                if ($lastname != '*') {
                    $criteria->compare('Last_Name', $lastname, true);
                }
                $criteria->order = 'Last_Name ASC';
                $all_users = Persons::model()->with('user')->findAll($criteria);
                $find = true;
            } else {
                $all_users = array();
                $find = false;
            }
            $this->renderPartial('filtered_users_to_active_list' , array(
                'all_users' => $all_users,
                'find' => $find,
            ));
        }
    }

    /**
     * Get filtered clients list by company name to clients_active_mgmt tab
     */
    public function actionGetFilteredClientsToActiveList()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['clientname'])) {
            $clientname = strtolower(trim($_POST['clientname']));

            if ($clientname != '') {
                $criteria=new CDbCriteria;
                if ($clientname != '*') {
                    $criteria->compare('company.Company_Name', $clientname, true);
                }
                $criteria->order = 'company.Company_Name ASC';
                $all_clients = Clients::model()->with('company')->findAll($criteria);
                $find = true;
            } else {
                $all_clients = array();
                $find = false;
            }
            $this->renderPartial('filtered_clients_to_active_list' , array(
                'all_clients' => $all_clients,
                'find' => $find,
            ));
        }
    }

    /**
     * Get filtered users list by lastname to users_type_mgmt tab
     */
    public function actionGetFilteredUsersToTypeList()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['lastname'])) {
            $lastname = strtolower(trim($_POST['lastname']));

            if ($lastname != '') {
                $criteria=new CDbCriteria;
                if ($lastname != '*') {
                    $criteria->compare('Last_Name', $lastname, true);
                }
                $criteria->order = 'Last_Name ASC';
                $all_users = Persons::model()->with('user')->findAll($criteria);
                $find = true;
            } else {
                $all_users = array();
                $find = false;
            }
            $this->renderPartial('filtered_users_to_type_list' , array(
                'all_users' => $all_users,
                'find' => $find,
            ));
        }
    }

    /**
     * Get filtered users list by last name to approve users tab
     */
    public function actionGetFilteredUsersToApproveList()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['lastname'])) {
            $lastname = $_POST['lastname'];

            $condition = new CDbCriteria();
            $condition->condition = "t.Approved_By_Admin = '" . UsersToApprove::NOT_APPR_BY_ADMIN . "'";
            if (trim($_POST['lastname']) != '') {
                $condition->compare('person.Last_Name', $lastname, true);
            }

            $usersToApprove = UsersToApprove::model()->with('user.person')->findAll($condition);

            $this->renderPartial('filtered_users_to_approve_list' , array(
                'usersToApprove' => $usersToApprove,
            ));
        }
    }

    /**
     * Get Empty Companies List
     */
    public function actionGetFilteredEmptyCompaniesList()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['companyName'])) {
            $companyName = $_POST['companyName'];

            $companies = Companies::getEmptyCompanies($companyName);

            $this->renderPartial('empty_companies_list' , array(
                'companies' => $companies,
            ));
        }
    }

    /**
     * Get empty company info
     */
    public function actionGetEmptyCompanyInfo()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['companyId'])) {
            $companyId = intval($_POST['companyId']);
            if ($companyId > 0) {
                $company = Companies::model()->with('adreses')->findByPk($companyId);
                $addresses = $company->adreses;
                $address = $addresses[0];

                $this->renderPartial('empty_company_info' , array(
                    'company' => $company,
                    'address' => $address,
                ));
            }
        }
    }

    public function actionCheckUserApprovalValue()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['userId']) && isset($_POST['clientId']) && isset($_POST['value'])) {
            $userId = intval($_POST['userId']);
            $clientId = intval($_POST['clientId']);
            $value = intval($_POST['value']);

            // check value
            $value = UsersClientList::checkUserApprovalValue($userId, $clientId, $value);

            echo $value;
        }
    }

    /**
     * Generate letter for Company
     */
    public function actionGenerateLetter($id)
    {
        if ($id === 'all') {
            $companies = Companies::getEmptyCompanies('*', 50, true);
            $trans = array(' '=>'-', '/'=>'-', '\\'=>'-', '~'=>'-', '&'=>'-', '?'=>'-', ','=>'-', '"'=>'-', "'"=>'-');
            $html = '';

            foreach($companies['not_printed'] as $company) {
                // activate client
                $client = $company->client;
                $client->Client_Type = 1;
                $client->Client_Status = Clients::ACTIVE;
                $client->Client_Number = $client->Client_ID;
                $client->save();

                $html .= Helper::getCompanyTemplate($company->Company_ID) . '<br/><br/>';
            }

            foreach($companies['printed'] as $company) {
                // activate client
                $client = $company->client;
                $client->Client_Type = 1;
                $client->Client_Status = Clients::ACTIVE;
                $client->Client_Number = $client->Client_ID;
                $client->save();

                $html .= Helper::getCompanyTemplate($company->Company_ID) . '<br/><br/>';
            }

            $fileName = trim(strtr(strtolower('letter for all companies.pdf'), $trans));
            Yii::import('ext.html2pdf.HTML2PDF');
            $html2pdf = new HTML2PDF('L', 'A6', 'en');
            $html2pdf->writeHTML($html);
            $html2pdf->Output($fileName);
        } else if(is_numeric($id)) {
            $id = intval($id);
            $trans = array(' '=>'-', '/'=>'-', '\\'=>'-', '~'=>'-', '&'=>'-', '?'=>'-', ','=>'-', '"'=>'-', "'"=>'-');
            $company = Companies::model()->with('client')->findByPk($id);

            // activate client
            $client = $company->client;
            $client->Client_Type = 1;
            $client->Client_Status = Clients::ACTIVE;
            $client->Client_Number = $client->Client_ID;
            $client->save();

            $templateBody = Helper::getCompanyTemplate($id);
            if ($templateBody) {
                $fileName = trim(strtr(strtolower('Letter for ' . $company->Company_Name . '.pdf'), $trans));
                Yii::import('ext.html2pdf.HTML2PDF');
                $html2pdf = new HTML2PDF('L', 'A6', 'en');
                $html2pdf->writeHTML($templateBody);
                $html2pdf->Output($fileName);
            } else {
                Yii::app()->user->setFlash('success', "Letter for this company can not be created!");
                $this->redirect('/admin?tab=empty_companies');
            }
        } else {
            $this->redirect('/admin?tab=empty_companies');
        }
    }

    /**
     * Approve users action
     */
    public function actionApproveUsers()
    {
        if (isset($_GET['users']) && isset($_GET['userTypes'])) {
            foreach ($_GET['users'] as $id => $value) {
                if (is_numeric($id) && ($value == 'Y' || $value == 'N') && isset($_GET['userTypes'][$id]) && isset($this->userTypes[$_GET['userTypes'][$id]])) {
                    // get user type
                    $userType = $this->userTypes[$_GET['userTypes'][$id]];
                    $id = intval($id);

                    //get users to approve row
                    $usersToApprove = UsersToApprove::model()->with('user', 'client')->findByPk($id);
                    if ($usersToApprove && $value == 'Y') {
                        //approve user
                        if ($usersToApprove->New_Client == UsersToApprove::NEW_CLIENT) {
                            $client = $usersToApprove->client;
                            $client->Client_Type = 1;
                            $client->Client_Status = Clients::ACTIVE;
                            $client->Client_Number = $client->Client_ID;
                            $client->save();

                            ClientServiceSettings::addClientServiceSettings($client->Client_ID, true);

                            $password = Helper::generatePassword();
                            $user = $usersToApprove->user;
                            $user->Active = Users::ACTIVE;
                            $user->User_Pwd = md5($password);
                            $user->save();

                            $userClientRelation = UsersClientList::model()->findByAttributes(array(
                                'User_ID' =>$user->User_ID,
                                'Client_ID' =>$client->Client_ID,
                            ));

                            if ($userClientRelation) {
                                $userClientRelation->User_Type = $userType;

                                if (in_array($userType, UsersClientList::$clientAdmins)) {
                                    $userClientRelation->User_Approval_Value = Aps::APPROVED;
                                } else if ($userType == UsersClientList::APPROVER) {
                                    $userClientRelation->User_Approval_Value = Aps::READY_FOR_APPROVAL + 1;
                                } else {
                                    $userClientRelation->User_Approval_Value = 0;
                                }
                                $userClientRelation->save();
                            }

                            $emailSuccess = Mail::sendRegistrationMail($user->person->Email, $user->User_Login,$password, $user->person->First_Name, $user->person->Last_Name, $client->company->Company_Name);
                            $usersToApprove->delete();
                        } else {
                            $usersToApprove->Approved_By_Admin = UsersToApprove::APPR_BY_ADMIN;
                            $usersToApprove->save();

                            $client = $usersToApprove->client;
                            $client->Client_Type = 1;
                            $client->Client_Status = Clients::ACTIVE;
                            $client->Client_Number = $client->Client_ID;
                            $client->save();

                            $user = $usersToApprove->user;

                            if ($user->User_Pwd == md5('temp100')) {
                                // if user requested to join company by registration form
                                $password = Helper::generatePassword();
                                $user->Active = Users::ACTIVE;
                                $user->User_Pwd = md5($password);
                                $user->Default_Project = 0;
                                $user->save();

                                Mail::sendUserRegistrationMail($user->person->Email, $user->User_Login, $password, $user->person->First_Name, $user->person->Last_Name, $client->company->Company_Name);
                            } else {
                                // if user requested to join company and there is not client-admins
                                $user->Active = Users::ACTIVE;
                                $user->save();
                            }

                            $condition = UsersClientList::getClientAdminCondition($usersToApprove->Client_ID);

                            $client_admins_list = UsersClientList::model()->findAll($condition); //this is client admins for current client

                            if ($client_admins_list) {
                                // if client has client-admins
                                foreach ($client_admins_list as $clientAdm) {
                                    $clientAdmin = Users::model()->with('person')->findByPk($clientAdm->User_ID);
                                    $emailSuccess = Mail::sendClientOfUserRegistrationMail($clientAdmin->person->Email, $clientAdmin->person->First_Name, $clientAdmin->person->Last_Name, $user->person->First_Name, $user->person->Last_Name, $client->company->Company_Name);
                                }

                            }



                                // if client hasn't client admins
                                $usersClientList = UsersClientList::model()->findByAttributes(array(
                                   'Client_ID' => $usersToApprove->Client_ID,
                                    'User_ID' => $usersToApprove->User_ID
                                ));

                                if (!$usersClientList) {
                                    $usersClientList = new UsersClientList;
                                }

                                $usersClientList->User_ID = $usersToApprove->User_ID;
                                $usersClientList->Client_ID = $usersToApprove->Client_ID;
                                $usersClientList->User_Type = $userType;

                                if (in_array($userType, UsersClientList::$clientAdmins)) {
                                    $usersClientList->User_Approval_Value = Aps::APPROVED;
                                } else if ($userType == UsersClientList::APPROVER) {
                                    $usersClientList->User_Approval_Value = Aps::READY_FOR_APPROVAL + 1;
                                } else {
                                    $usersClientList->User_Approval_Value = 0;
                                }

                                $usersClientList->save();

                                $project = Projects::model()->findByAttributes(array(
                                    'Client_ID' => $usersToApprove->Client_ID,
                                ));

                                $usersProjectList = new UsersProjectList;
                                $usersProjectList->User_ID = $usersToApprove->User_ID;
                                $usersProjectList->Client_ID = $usersToApprove->Client_ID;
                                $usersProjectList->Project_ID =  $project->Project_ID;
                                $usersProjectList->save();

                                if ($user->Default_Project == 0) {
                                    $user->Default_Project = $project->Project_ID;
                                }
                                $user->save();

                                $usersToApprove->delete();

                                Mail::sendAddUserToClientMail($user->person->Email, $user->person->First_Name, $user->person->Last_Name, $client->company->Company_Name);

                        }
                    } else if ($usersToApprove && $value == 'N') {
                        //reject user
                        $user = $usersToApprove->user;
                        $client = $usersToApprove->client;

                        if ($usersToApprove->New_Client == UsersToApprove::NEW_CLIENT) {
                            $company = $client->company;
                            $addresses = $company->adreses;

                            //delete company addresses
                            foreach ($addresses as $address) {
                                $companyAddress = CompanyAddresses::model()->findByAttributes(array(
                                    'Company_ID' => $company->Company_ID,
                                    'Address_ID' => $address->Address_ID,
                                ));

                                if ($companyAddress) {
                                    $companyAddress->delete();
                                }
                                $address->delete();
                            }

                            $company->delete();

                            $usersClientList = UsersClientList::model()->findByAttributes(array(
                                'User_ID' => $user->User_ID,
                                'Client_ID' => $client->Client_ID,
                            ));

                            if ($usersClientList) {
                                $usersClientList->delete();
                            }

                            $usersToApprove->delete();
                            $client->delete();
                        } else {
                            $usersToApprove->delete();
                        }

                        if ($user->User_Pwd == md5('temp100')) {
                            $user->Active = Users::NOT_ACTIVE;
                            $user->Default_Project = 0;
                            $user->save();

                            Mail::sendRejectUserByAdminMail($user->person->Email, $user->person->First_Name, $user->person->Last_Name);
                        } else {
                            Mail::sendRejectMail($user->person->Email, $user->person->First_Name, $user->person->Last_Name, $client->company->Company_Name);
                        }
                    }
                }
            }

            Yii::app()->user->setFlash('success', "Users have been successfully approved/rejected!");
        } else {
            Yii::app()->user->setFlash('success', "Users was not approved/rejected!");
        }
        $this->redirect('/admin?tab=reg_requests');
    }

    /**
     * Find users by login action
     */
    public function actionFindUserByLogin()
    {
        $message = '';
        $user = '';
        if (Yii::app()->request->isAjaxRequest && isset($_POST['login'])) {
            $login = $_POST['login'];
            $user = Users::model()->with('person')->findByAttributes(array(
                'User_Login'=>$login,
            ));

            if (!$user) {
                $message = 'No users with login: ' . $login;
            }
        }

        $this->renderPartial('founded_users' , array(
            'user' => $user,
            'message' => $message,
        ));
    }

    /**
     * Return necessary file
     */
    public function actionGetDocumentFile($doc_id)
    {
        $doc_id = intval($doc_id);

        if ($doc_id > 0) {
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

    /**
     * Get user's file html
     */
    public function actionGetUserFile()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['docId'])) {
            $doc_id = intval($_POST['docId']);
            if ($doc_id > 0) {
                $image = Images::model()->findByAttributes(array(
                    'Document_ID' => $doc_id,
                ));

                $this->renderPartial('user_file_html' , array(
                    'image' => $image,
                ));
            }
        }
    }

    /**
     * Get user info to Users Type tab
     */
    public function actionGetUserTypeInfo()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['userId']) && isset($_POST['tab'])) {
            $userId = intval($_POST['userId']);
            $tab = trim($_POST['tab']);
            if ($userId > 0) {
                $user = Users::model()->with('projects.client.company')->findByPk($userId);
                $projects = Projects::getAllUserProjectsList($userId);
                $this->renderPartial('user_to_type_info' , array(
                    'user' => $user,
                    'projects'=> $projects,
                    'tab' => $tab,
                ));
            }
        }
    }

    /**
     * Get client info to Clients Active tab
     */
    public function actionGetClientActiveInfo()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['clientId'])) {
            $clientId = intval($_POST['clientId']);
            if ($clientId > 0) {
                $client = Clients::model()->with('company', 'projects')->findByPk($clientId);
                $projects = $client->projects;
                $this->renderPartial('client_to_active_info' , array(
                    'user' => $client,
                    'company' => $client->company,
                    'projects'=> $projects,
                    'address' => $client->company->adreses[0],
                ));
            }
        }
    }

    /**
     * Get image view block
     */
    public function actionGetImageViewBlock()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['docId'])) {
            $doc_id = intval($_POST['docId']);
            if ($doc_id > 0) {
                $image = Images::model()->findByAttributes(array(
                    'Document_ID' => $doc_id,
                ));

                $this->renderPartial('image_view' , array(
                    'image' => $image,
                ));
            }
        }
    }

    /**
     * Update Service Level Settings
     */
    public function actionUpdateServiceLevelSettings()
    {
        if (isset($_POST['ServiceLevelSettings']) && is_array($_POST['ServiceLevelSettings'])) {

            foreach ($_POST['ServiceLevelSettings'] as  $settings) {
                $serviceLevelSettings = ServiceLevelSettings::model()->findByPk($settings['Service_Level_ID']);
                if (!$serviceLevelSettings) {
                    $serviceLevelSettings = new ServiceLevelSettings();
                }

                $serviceLevelSettings->setAttributes($settings);

                if ($serviceLevelSettings->validate()) {
                    $serviceLevelSettings->save();
                }
            }
            Yii::app()->user->setFlash('success', "Service Level Settings have been saved!");
        }
        $this->redirect('/admin?tab=service_settings');
    }

    /**
     * Get company service level settings
     */
    public function actionGetCompanyServiceLevelSettings()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['clientId'])) {
            $clientId = intval($_POST['clientId']);
            $client = Clients::model()->with('service_settings', 'service_payments')->findByPk($clientId);
            $serviceLevels = ServiceLevelSettings::getServiceLevelsOptionsList();

            if ($client) {
                $settings = $client->service_settings;

                if (!$settings) {
                    $settings = ClientServiceSettings::addClientServiceSettings($client->Client_ID);
                }

                $_SESSION['tabs_to_auto_load']['client_service_level_settings'] = array('client_id'=> $client->Client_ID);
                $_SESSION['admin_active_tab']='service_level';

                $pending_client_service_settings = PendingClientServiceSettings::model()->findByAttributes(array(
                    'Client_ID'=> $client->Client_ID,
                    'Approved'=>1
                ));

            if ($pending_client_service_settings && $pending_client_service_settings->Pending_Active_From == '0000-00-00') {
                    //price recalculation only for real (not delayed settings)
                    $active_to = $pending_client_service_settings->	Pending_Active_To;
                    $monthly_payment = $pending_client_service_settings->Fee;
                    //PendingClientServiceSettings::recalculateSettings($pending_client_service_settings,$active_to,$monthly_payment);
            }
                //update Fee if general settings were changed
                //$settings->setFee();
                //$settings->save();
            $items =  ServiceLevelSettings::getServiceLevelsOptionsList();
            $summary_sl_settings = ServiceLevelSettings::getSummarySettings($clientId);
            $dcss = DelayedClientServiceSettings::model()->findByPk($clientId);

                $this->renderPartial('client_service_level_settings' , array(
                    'client' => $client,
                    'settings' => $settings,
                    'payments' => $client->service_payments,
                    'serviceLevels' => $serviceLevels,
                    'items'=>$items,
                    'summary_sl_settings'=>$summary_sl_settings,
                    'pending_client_service_settings'=>$pending_client_service_settings,
                    'dcss'=>$dcss
                ));
            }
        }
    }

    /**
     * Get service settings
     */
    public function actionGetServiceLevelSettings()
    {
        if (Yii::app()->request->isAjaxRequest) {
            $serviceLevelsList = array();
            $serviceLevels = ServiceLevelSettings::model()->findAll();
            foreach ($serviceLevels as $serviceLevel) {
                $serviceLevelsList[$serviceLevel->Service_Level_ID] = $serviceLevel;
            }

            echo CJSON::encode($serviceLevelsList);
        }
    }

    /**
     * Update company service level and add payment
     */
    public function actionUpdateCompanyServiceLevel()
    {
        if (isset($_POST['ClientServiceSettings']) && is_array($_POST['ClientServiceSettings'])
            && isset($_POST['Tiers']) && is_array($_POST['Tiers'])
        ) {
            $client_id = intval($_POST['ClientServiceSettings']['Client_ID']);
            $css = ClientServiceSettings::model()->findByPk($client_id);

            $pcss = PendingClientServiceSettings::model()->findByPk($client_id);
            if ($pcss) {$pending_fee = $pcss->Fee;}

            $client = $css->client;
        //1) add payment
            $amount = floatval($_POST['add_payment_amount']);
            $date = trim($_POST['add_payment_date']);
            $number = trim($_POST['add_payment_number']);
            $date = Helper::checkDate($date);

            $users = intval($_POST['ClientServiceSettings']['Additional_Users']);
            $projects = intval($_POST['ClientServiceSettings']['Additional_Projects']);
            $storage = intval($_POST['ClientServiceSettings']['Additional_Storage']);

            $min_max_is_valid = ClientServiceSettings::CheckMinMaxValues($users,$projects,$storage);

            if ($min_max_is_valid) {
                if ($client && $amount > 0 && preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $date)) {
                    $result = ServicePayments::addJustPayment($client_id, $amount, $date,$number,'');
                }
            }



        //2) update settings

            if ($css && $client && $min_max_is_valid) {
                $_POST['ClientServiceSettings']['Active_To'] = Helper::checkDate($_POST['ClientServiceSettings']['Active_To']);

                $css->setAttributes($_POST['ClientServiceSettings']);
                //$css->Additional_Storage--;
                //$companyServiceSettings->Service_Level_ID = implode(',',$_POST['Tiers']);

                //$fee = ClientServiceSettings::Calculation($css->Additional_Users,$css->Additional_Projects,$css->Additional_Storage,$css->Active_To,$css->Service_Level_ID,$client_id);

                //new settings and prices
                $base_fee_new = ClientServiceSettings::CalculateBaseFee(explode(',',$css->Service_Level_ID));

                $add_fee_new = ClientServiceSettings::CalculateAddFee($css->Additional_Users,$css->Additional_Projects,$css->Additional_Storage);

                $fee = $base_fee_new + $add_fee_new;
                $css->Additional_Storage--;
                /**$fee = $amount ? $amount :
                    ClientServiceSettings::getFeeByValues(
                        $companyServiceSettings->Service_Level_ID,
                        $companyServiceSettings->Additional_Users,
                        $companyServiceSettings->Additional_Projects,
                        $companyServiceSettings->Additional_Storage
                    );
                 */

                //$periods = Helper::calculatePeriodsBetweenDates(date('m/d/Y'),$companyServiceSettings->Active_To);

                $css->Fee = $fee; //* $periods;

                if ($css->validate()) {
                    $css->save();
                    if ($pcss) $pcss->delete();
                    if ($css->Active_To >= date('Y-m-d') && $client->Client_Status == Clients::NOT_ACTIVE) {
                        $client->activate();
                    }

                    $user_client_settings = $client->service_settings;
                    $user_tier_settings = TiersSettings::agregateTiersSettings($user_client_settings->Service_Level_ID);
                    Yii::app()->user->setState('tier_settings', $user_tier_settings);
                }
            }

        Yii::app()->user->setFlash('success', "Settings added");
        $this->redirect('/admin?tab=service');
    }
    }

    /**
     * Add Company service level payment
     */
    public function actionAddCompanyPayment()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['amount']) && isset($_POST['clientID']) && isset($_POST['date'])) {
            $result = '';
            $amount = floatval($_POST['amount']);
            $clientID = intval($_POST['clientID']);
            $date = trim($_POST['date']);
            $number = trim($_POST['number']);

            $date = Helper::checkDate($date);

            $client = Clients::model()->findByPk($clientID);

            if ($client && $amount > 0 && preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $date)) {
                $result = ServicePayments::addJustPayment($clientID, $amount, $date,$number,'');
            }
        echo CJSON::encode($result);

        }

    }

    /**
     * Sort users
     * @param $a
     * @param $b
     * @return int
     */
    public function sortClientUsers($a, $b) {
        if ($this->clientAdmins[$a->User_ID] == $this->clientAdmins[$b->User_ID]) {
            return strnatcmp($a->person->Last_Name, $b->person->Last_Name);
        } else if ($this->clientAdmins[$a->User_ID] == 1) {
            return -1;
        } else if ($this->clientAdmins[$b->User_ID] == 1) {
            return 1;
        } else {
            return -1;
        }
    }

    /**
     * Sort users by approval value
     * @param $a
     * @param $b
     * @return int
     */
    public function sortClientUsersByApprovalValue($a, $b) {
        if ($this->approvalValue[$a->User_ID] > $this->approvalValue[$b->User_ID]) {
            return -1;
        } else if ($this->approvalValue[$a->User_ID] < $this->approvalValue[$b->User_ID]) {
            return 1;
        }  else {
            return -1;
        }
    }

    /**
     * Sort users by last name
     * @param $a
     * @param $b
     * @return int
     */
    public function sortUsers($a, $b) {
        return strnatcmp($a->person->Last_Name, $b->person->Last_Name);
    }

    /*
     * Analyzes session and generates tabs for Admin panel
     */
    public function generateTabsForAutoLoad (){

        if ( $_SESSION['tabs_to_auto_load']['client_service_level_settings']) {

                $client_id_to_rewiev = intval($_SESSION['tabs_to_auto_load']['client_service_level_settings']['client_id']);

                $client = Clients::model()->with('service_settings', 'service_payments','company')->findByPk($client_id_to_rewiev);
                if ($client) {
                    $serviceLevels = ServiceLevelSettings::getServiceLevelsOptionsList();
                    $settings = $client->service_settings;
                    $items =  ServiceLevelSettings::getServiceLevelsOptionsList();
                    $summary_sl_settings = ServiceLevelSettings::getSummarySettings($client_id_to_rewiev);
                    $dcss = DelayedClientServiceSettings::model()->findByPk($client_id_to_rewiev);
                    $pending_client_service_settings = PendingClientServiceSettings::model()->findByAttributes(array(
                        'Client_ID'=> $client->Client_ID,
                        'Approved'=>1
                    ));

                    $view_data =  $this->renderPartial('client_service_level_settings' , array(
                        'client' => $client,
                        'settings' => $settings,
                        'payments' => $client->service_payments,
                        'serviceLevels' => $serviceLevels,
                        'items'=>$items,
                        'summary_sl_settings'=>$summary_sl_settings,
                        'pending_client_service_settings'=>$pending_client_service_settings,
                        'dcss'=>$dcss
                    ),true);

                    $return_array['client_service_level_settings']['auto_loaded_data'] = $view_data;
                    $return_array['client_service_level_settings']['client'] = $client;
                }
        }

        if ( $_SESSION['tabs_to_auto_load']['client_users_list_appr_value']) {
            $client_id_to_rewiev = intval($_SESSION['tabs_to_auto_load']['client_users_list_appr_value']['client_id']);
            $approvers_array = UsersClientList::getApproversArray($client_id_to_rewiev);

            $view_data =  $this->renderPartial('client_users_list_appr_value' , array(
                         'approvers_array'=>$approvers_array,
            ),true);

            $return_array['client_users_list_appr_value']['auto_loaded_data'] = $view_data;
            $return_array['client_users_list_appr_value']['client'] = Clients::model()->with('company', 'users')->findByPk($client_id_to_rewiev);;
        }

        return $return_array;
    }

}