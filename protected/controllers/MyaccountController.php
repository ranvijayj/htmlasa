<?php

class MyaccountController extends Controller
{
    public $layout='//layouts/column2';

    /**
     * Client admins
     * @var array
     */
    public $clientAdmins = array();

    /**
     * New company form
     */
    public $new_company = false;

    /**
     * New bank account info
     * @var mixed
     */
    public $new_bank_account = false;

    /**
     * New project info
     * @var mixed
     */
    public $new_project = false;

    /**
     * Project PO Formatting
     * @var mixed
     */
    public $po_formatting = false;

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
                'actions'=>array('index', 'requesttojoincompany', 'getfiltereddocumentslist',
                                 'reassigndocumentsclients', 'getuserfile','testmail'),//  delete testmail
                'users'=>array('@'),
            ),
            array('allow',
                'actions'=>array('removeuser', 'ApproveUsers', 'rejectusers', 'AddUser', 'FindUserByEmail', 'getbankacctinfo',
                'getprojectinfo', 'getusersprojectslist', 'approveusersprojects', 'getuserclientprojects', 'getuserprojectnumber',
                'getservicelevelsettings', 'updatecompanyservicelevel', 'invoicetopayment', 'applyservises', 'rejectservises',
                'uploadservicepayment', 'checkcreditcard', 'checkstripecustomer','UnsetDeviceCheck','SetDeviceCheck','CheckTierLevelUsage',
                'UpdateUsersSettings','UpdateUsersSettingsNew','ApplyPendingService','LogToHistory','UpdateUsersSettingsManual','updateUsersApprovalValues'
                ),
                'users'=>array('admin', 'user', 'approver', 'data_entry_clerk', 'db_admin', 'processor', 'client_admin'),
            ),
            array('allow',
                'actions'=>array('UpdateUsersSettings','ApplyPendingService','LogToHistory','UpdateUsersSettingsManual','updateUsersApprovalvalues'),
                'users'=>array('db_admin', 'client_admin','admin'),
            ),

            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    /**
     * Test function
     * @todo: Delete from production
     *
     */
    public function actionTestMail()
    {

        $firstName='Smith';
        $lastName='John';
        $company='Big Company';
        $tierName='W9 Tier';
        $date='30.07.2015';
        $amount='315.89';

        $template = MailTemplates::model()->findByPk(3019);
        $templateBody = $template->Message_Body;
        $templateTitle = $template->Title;

        $replacedValues = array(
            '{{first_name}}' => $firstName,
            '{{last_name}}' => $lastName,
            '{{date}}' => $date,
            '{{tier_name}}' => $tierName,
            '{{company}}' => CHtml::encode($company),
            '{{service_level_link}}' => Yii::app()->config->get('SITE_URL') . '/myaccount?tab=service',
            '{{pay_sum}}'=>$amount
        );

        $templateBody = Helper::replaceMatches($templateBody, $replacedValues);
        echo $templateBody."<br/><br/><br/><br/>";



        $template = MailTemplates::model()->findByPk(3018);
        $templateBody = $template->Message_Body;
        $templateTitle = $template->Title;

        $replacedValues = array(
            '{{first_name}}' => $firstName,
            '{{last_name}}' => $lastName,
            '{{date}}' => $date,
            '{{tier_name}}' => $tierName,
            '{{company}}' => CHtml::encode($company),
            '{{service_level_link}}' => Yii::app()->config->get('SITE_URL') . '/myaccount?tab=service',
            '{{pay_sum}}'=>$amount
        );

        $templateBody = Helper::replaceMatches($templateBody, $replacedValues);
        echo $templateBody."<br/><br/><br/><br/>";



    }
    /**
     * Index action
     * @param string $tab
     * @param int $users_page
     * @param int $payments_page
     */
    public function actionIndex($tab = 'profile', $users_page = 1, $payments_page = 1)
    {

        $client_admin = false;
        $usersToApprove = array();

        // get current user
        $user = Users::model()->with('person', 'settings')->findByPk(Yii::app()->user->userID);

        // find user-client relationship
        $userCientRow = UsersClientList::model()->findByAttributes(array(
            'User_ID'=>Yii::app()->user->userID,
            'Client_ID'=>Yii::app()->user->clientID,
        ));

        $person = $user->person;
        $person_adresses = $person->adresses;
        $person_adress = $person_adresses[0];
        $person_adress->setScenario('required_fields');
        $user_settings = $user->settings;

        if ($userCientRow) {
            $bankAccountNums = BankAcctNums::getClientAccountNumbers();
            $projects = Projects::getClientUserProjects($client_admin);

            // if user links to company
            if ($userCientRow->hasClientAdminPrivileges()) {
                $client_admin = true;
            }

            if ($client_admin) {
                $usersToApprove = UsersToApprove::model()->getClientUsersToApprove(Yii::app()->user->clientID);
                $bankAccountNums['add'] = 'Add An Account';
                $projects['add'] = 'Add Project';
            }

            //check if
            //if in the system there is at least one approver with apr value =100
            if($client_admin && !UsersClientList::countFinalApprovers(Yii::app()->user->clientID) ) {
                $show_usr_appr_form = true;
            }

            // get company info
            $client = Clients::model()->with('company', 'users', 'service_settings', 'pending_service_settings', 'projects')->findByPk(Yii::app()->user->clientID);
            $company = $client->company;
            if ($company) {
                $company_adresses = $company->adreses;
                $company_adress = $company_adresses[0];
                $company_adress->setScenario('required_fields');
            }


            // get client's users list
            $client_users = $client->users;
            foreach ($client_users as $key => $cuser) {
                $uClRow = UsersClientList::model()->findByAttributes(array(
                    'User_ID'=>$cuser->User_ID,
                    'Client_ID'=>Yii::app()->user->clientID,
                ));
                //$this->clientAdmins[$cuser->User_ID] = $uClRow->hasClientAdminPrivileges() ? 1 : 0;
                $this->clientAdmins[$cuser->User_ID] = $uClRow->User_Type=='Client Admin' ? 1 : 0;
            }
            usort($client_users, array($this, 'sortClientUsers'));
        } else {
            // if user does not link to any company
            $company = new Companies();
            $company_adress = new Addresses();
            $client_users = array();
            $bankAccountNums = array();
            $projects = array();
        }

        // get user's documents
        $documents = new Documents();
        $userDocuments = $documents->findUserDocuments(Yii::app()->user->userID, date("Y-m-d"));
        $userProjects = Projects::getUserProjects(Yii::app()->user->userID);

        // payments
        $p_model = new ServicePayments('search');
        $p_model->unsetAttributes();  // clear any default values

        // processing profile form
        $password_form = new PasswordForm;
        if (isset($_POST['profile_form'])) {
            $oldEmail = $person->Email;

            $person->attributes = $_POST['Persons'];
            $person_adress->attributes = $_POST['Addresses'];
            $password_form->attributes = $_POST['PasswordForm'];
            $person_validate = $person->validate();
            $person_adress_validate = $person_adress->validate();
            $person->validate();
            if ($person_validate && $person_adress_validate && $_POST['PasswordForm']['oldPass'] == ''
                && $_POST['PasswordForm']['newPass'] == '' && $_POST['PasswordForm']['newPass2'] == '') {
                $person->save();
                $person_adress->save();
                Yii::app()->user->userInfo =  $person->First_Name . ' ' . $person->Last_Name;
                Yii::app()->user->setFlash('success', "Changes Saved!");

                if ($oldEmail != $person->Email) {
                    Mail::sendEmailChangeConfirmation($oldEmail, $person->First_Name, $person->Last_Name);
                }
            }

            if (($_POST['PasswordForm']['oldPass'] != ''
                    || $_POST['PasswordForm']['newPass'] != '' || $_POST['PasswordForm']['newPass2'] != '') && $password_form->validate()) {
                $person->save();
                $person_adress->save();
                Yii::app()->user->userInfo =  $person->First_Name . ' ' . $person->Last_Name;
                $user->User_Pwd = md5($_POST['PasswordForm']['newPass']);
                $user->save();

                Yii::app()->user->setFlash('success', "Your password has been successfully changed!");

                if ($oldEmail != $person->Email) {
                    Mail::sendEmailChangeConfirmation($oldEmail, $person->First_Name, $person->Last_Name);
                }
            }
        }

        // processing company form
        if (isset($_POST['company_form']) && $client_admin) {
            $company->attributes = $_POST['Companies'];
            $company_adress->attributes = $_POST['Addresses'];
            $client->Client_Logo_Name = $_POST['Clients']['Client_Logo_Name'];
            if ($company->validate() && $company_adress->validate()) {
                $company->save();
                $client->save();
                $company_adress->save();
                Yii::app()->user->clientInfo = $company->Company_Name;
                Yii::app()->user->setFlash('success', "Changes Saved!");
            }
        }

        // processing user settings form
        if (isset($_POST['settings_form'])) {
            $user_settings->attributes = $_POST['UsersSettings'];
            if ($user_settings->validate()) {
                $user_settings->save();
                Yii::app()->user->setFlash('success', "Changes Saved!");
            }
        }

        // processing new company form
        $newCompanyForm = new NewCompanyForm();
        $show_new_company_form = false;
        if (isset($_POST['NewCompanyForm'])) {
            $newCompanyForm->attributes=$_POST['NewCompanyForm'];
            if ($newCompanyForm->validate()) {
                // begin transaction
                $transaction = Yii::app()->db->beginTransaction();
                try {
                    // set company info
                    $new_comp_name = $newCompanyForm->Company_Name;
                    $new_comp_fed_id = $newCompanyForm->Fed_ID;
                    $new_comp_adr = $newCompanyForm->Address1;
                    $new_comp_city = $newCompanyForm->City;
                    $new_comp_state = $newCompanyForm->State;
                    $new_comp_zip = $newCompanyForm->ZIP;

                    // create new company
                    $new_client = new Clients;
                    $new_company = new Companies;
                    $new_project = new Projects;
                    $new_companyAdreses = new CompanyAddresses;
                    $new_usersClientList = new UsersClientList;
                    $new_usersProjectList = new UsersProjectList;

                    $new_company->Company_Name = $new_comp_name;
                    $new_company->Company_Fed_ID = $new_comp_fed_id;
                    $new_company->save();

                    $new_company_adress = new Addresses;
                    $new_company_adress->Address1 = $new_comp_adr;
                    $new_company_adress->City = $new_comp_city;
                    $new_company_adress->State = $new_comp_state;
                    $new_company_adress->ZIP = $new_comp_zip;
                    $new_company_adress->save();

                    $new_companyAdreses->Company_ID = $new_company->Company_ID;
                    $new_companyAdreses->Address_ID = $new_company_adress->Address_ID;
                    $new_companyAdreses->save();

                    $new_client->Company_ID = $new_company->Company_ID;
                    $new_client->Client_Type = 1;
                    $new_client->Client_Number = 1;
                    $new_client->Client_Status = Clients::ACTIVE;
                    $new_client->save();

                    $new_usersClientList->User_ID = Yii::app()->user->userID;
                    $new_usersClientList->Client_ID = $new_client->Client_ID;
                    $new_usersClientList->User_Type = UsersClientList::CLIENT_ADMIN;
                    $new_usersClientList->User_Approval_Value = Aps::APPROVED;
                    $new_usersClientList->save();

                    //create client project
                    $new_project->Client_ID = $new_client->Client_ID;
                    $new_project->Project_Name = "Corporate";
                    $new_project->Project_Description = "Description of the Project";
                    $new_project->PO_Starting_Number = Projects::DEFAULT_PO_STARTING_NUMBER;
                    $new_project->Ck_Req_Starting_Numb = Projects::DEFAULT_CKRQ_STARTING_NUMBER;
                    $new_project->save();

                    ClientServiceSettings::addClientServiceSettings($new_client->Client_ID, true);

                    // user-project relationship
                    $new_usersProjectList->Client_ID = $new_client->Client_ID;
                    $new_usersProjectList->User_ID = Yii::app()->user->userID;
                    $new_usersProjectList->Project_ID = $new_project->Project_ID;
                    $new_usersProjectList->save();

                    // set user's fields
                    if ($user->Default_Project == 0) {
                        $user->Default_Project = $new_project->Project_ID;
                        $user->save();
                        if (Yii::app()->user->clientID == 0) {
                            // if user was not linked to any company, relogin him
                            $_SESSION['last_client'] = $new_client->Client_ID;
                            $_SESSION['last_project'] = $new_project->Project_ID;
                            $loginForm = new LoginForm;
                            $loginForm->relogin();
                            $transaction->commit();
                            Yii::app()->user->setFlash('success', "Company has been created!");
                            $this->redirect('/myaccount');
                        }
                    }

                    $transaction->commit();
                } catch(Exception $e) {
                    $transaction->rollback();
                }

                Yii::app()->user->setFlash('success', "Company has been created!");
            } else {
                $show_new_company_form = true;
                $this->new_company = $newCompanyForm;
            }
        }

        // add or edit new BankAcctNums
        $acctId = 0;
        $bankAccount = false;
        $show_bank_account_form = false;
        if (isset($_POST['BankAcctNums']) && isset($_POST['acct_id']) && $client_admin) {
            $acctId = intval($_POST['acct_id']);

            if ($acctId > 0) {
                $bankAccount = BankAcctNums::model()->findByAttributes(array(
                    'Client_ID' => Yii::app()->user->clientID,
                    'Project_ID' => Yii::app()->user->projectID,
                    'Account_Num_ID' => $acctId,
                ));
            } elseif ($acctId == 0) {
                $bankAccount = new BankAcctNums();
            }

            if ($bankAccount) {
                $linkedPayments = Payments::model()->findByAttributes(array(
                    'Account_Num_ID' => $bankAccount->Account_Num_ID,
                ));

                if (!$linkedPayments) {
                    $bankAccount->attributes = $_POST['BankAcctNums'];
                    $bankAccount->Client_ID = Yii::app()->user->clientID;
                    $bankAccount->Project_ID = Yii::app()->user->projectID;

                    if ($bankAccount->validate()) {
                        $bankAccount->save();
                        $bankAccountNums = BankAcctNums::getClientAccountNumbers();
                        $bankAccountNums['add'] = 'Add An Account';
                        Yii::app()->user->setFlash('success', "Bank Account has been saved!");
                    } else {
                        $show_bank_account_form = true;
                        $this->new_bank_account = $bankAccount;
                    }
                } else {
                    Yii::app()->user->setFlash('success', "This bank account has already been assigned to a payment and cannot be edited!");
                }
            } else {
                Yii::app()->user->setFlash('success', "You don't have permissions to edit this Bank Account!");
            }
        }

        // add or edit new Project
        $projectId = 0;
        $project = false;
        $show_projects_form = false;
        if (isset($_POST['Projects']) && isset($_POST['current_project_id']) && $client_admin) {
            $projectId = intval($_POST['current_project_id']);

            if ($projectId > 0) {
                $project = Projects::model()->findByAttributes(array(
                    'Client_ID' => Yii::app()->user->clientID,
                    'Project_ID' => $projectId,
                ));
            } elseif ($projectId == 0) {
                $project = new Projects();
            }

            if ($project) {
                $condition = new CDbCriteria();
                $condition->condition = "t.Project_ID=:Project_ID";
                $condition->addCondition("t.Document_Type != '" . Documents::W9 . "'");
                $condition->params = array('Project_ID' => $projectId);
                $linkedDocuments = Documents::model()->find($condition);

                if (!$linkedDocuments) {
                    $project->attributes = $_POST['Projects'];
                    $project->Client_ID = Yii::app()->user->clientID;

                    if ($project->validate()) {
                        $project->save();

                        Projects::assignProjectUsers($project->Project_ID);

                        $projects = Projects::getClientUserProjects(true);
                        $projects['add'] = 'Add Project';
                        Yii::app()->user->setFlash('success', "Project has been saved!");
                    } else {
                        $show_projects_form = true;
                        $this->new_project = $project;
                    }
                } else {
                    Yii::app()->user->setFlash('success', "This project has already been assigned to a PO, AP or Payment and cannot be edited!");
                }
            } else {
                Yii::app()->user->setFlash('success', "You don't have permissions to edit this Project!");
            }
        }

        // edit Project PO formatting
        $poFormattingId = 0;
        $poFormatting = false;
        $show_po_formatting_form = false;
        if (isset($_POST['PoFormatting']) && isset($_POST['current_po_formatting_id']) && $client_admin) {
            $poFormattingId = intval($_POST['current_po_formatting_id']);
            $poFormatting = PoFormatting::model()->findByPk($poFormattingId);
            if ($poFormatting) {
                $poFormatting->attributes = $_POST['PoFormatting'];
                if ($poFormatting->validate()) {
                    $poFormatting->save();
                    Yii::app()->user->setFlash('success', "Project PO Formatting has been saved!");
                } else {
                    $show_po_formatting_form = true;
                    $this->po_formatting = $poFormatting;
                }
            } else {
                Yii::app()->user->setFlash('success', "You don't have permissions to edit this Project PO Formatting!");
            }
        }

        $all_user_projects = Projects::getAllUserProjectsList(Yii::app()->user->userID, true);
        $all_user_bank_accounts = BankAcctNums::getAllUserBankAcctsForSettingsTab();

        // get credit card information
        $ccTypes = CcTypes::getCCTypesOptions();
        $cCard = Ccs::getUserCreditCard(Yii::app()->user->userID);
        if (!$cCard) {
            $cCard = new Ccs();
        }

        // if isset Credit Card form
        if (isset($_POST['ccform']) && isset($_POST['Ccs'])) {
            $cCard->setAttributes($_POST['Ccs']);
            $cCard->User_ID = Yii::app()->user->userID;
            if ($cCard->validate()) {
                $cCard->save();
                Yii::app()->user->setFlash('success', "Changes Saved!");
            }
        }

        // get client service level settings
        $client_service_settings = $client->service_settings;
        //recalculate
        if ($client_service_settings) $client_service_settings->recalculate();
        //$pending_client_service_settings = $client->pending_service_settings;
        $pending_client_service_settings = PendingClientServiceSettings::model()->findByAttributes(array(
           'Client_ID'=> $client->Client_ID,
            'Approved'=>1
        ));


        if ($pending_client_service_settings && $pending_client_service_settings->Pending_Active_From == '0000-00-00') {
            //price recalculation only for real (not delayed settings)
            $active_to = $pending_client_service_settings->Pending_Active_To;
            $monthly_payment = $pending_client_service_settings->Fee;

            //PendingClientServiceSettings::recalculateSettings($pending_client_service_settings,$active_to,$monthly_payment);
            //PendingClientServiceSettings::recalculateSettingsAlternative($pending_client_service_settings);
        }

        $dcss = DelayedClientServiceSettings::model()->findByPk(Yii::app()->user->clientID);
        if ($dcss) {
            DelayedClientServiceSettings::checkDate(date('Y-m-d'),$client->Client_ID);
            //Yii::app()->user->setFlash('success','Be aware: you have delayed settings, that will be applied '.$dcss->Active_From);
            //$this->redirect('/myaccount?tab=service');
        }

        if (!$client_service_settings) {
            $client_service_settings = ClientServiceSettings::addClientServiceSettings($client->Client_ID, true);
        }

        // update Fee if general settings were changed
        //$client_service_settings->setFee();
        //$client_service_settings->save();

        //variable to display payment block based on 10 days or not
        $timeis_up = 0;
        if ($client_service_settings->checkShowMonthlyPaymentAlert()) {$timeis_up = 1;}


        $serviceLevels = ServiceLevelSettings::model()->findAll();

        $expirationDate = $client_service_settings->Active_To;
        if ($expirationDate < date('Y-m-d')) {
            $expirationDate = date('Y-m-d');
        }

        $amountToPay = 0;
        if ($pending_client_service_settings) {
            //$amountToPay = $pending_client_service_settings->getCurrentAmountToUpgrade($expirationDate);
            $amountToPay = $pending_client_service_settings->getCurrentAmountToPay();
        }

        //check limiting of users and projects
        $availableUsersCount = ClientServiceSettings::getAvailableUsersCount($client->Client_ID);
        $availableProjectsCount = ClientServiceSettings::getAvailableProjectsCount($client->Client_ID);
        $enableAddUser = true;
        $enableAddProject = true;
        if ($availableUsersCount <= count($client->users)) {
            $enableAddUser = false;
        }
        if ($availableProjectsCount <= count($client->projects)) {
            $enableAddProject = false;
        }

        $users_questions =  UsersQuestions::getUserQuestions(Yii::app()->user->userID);
        $summary_sl_settings = ServiceLevelSettings::getSummarySettings(Yii::app()->user->clientID);


        $this->render('index' , array(
            'client'=> $client,
            'client_users' => $client_users,
            'p_model' => $p_model,
            'person' => $person,
            'person_adress' => $person_adress,
            'company' => $company,
            'company_adress' => $company_adress,
            'password_form' => $password_form,
            'client_admin' => $client_admin,
            'users_page' => $users_page,
            'payments_page' => $payments_page,
            'usersToApprove' => $usersToApprove,
            'userDocuments' => $userDocuments,
            'user_clients' => $user->clients,
            'tab' => $tab,
            'show_new_company_form' => $show_new_company_form,
            'show_bank_account_form' => $show_bank_account_form,
            'show_projects_form' => $show_projects_form,
            'show_usr_appr_form' => $show_usr_appr_form,
            'user_settings' => $user_settings,
            'bankAccountNums' => $bankAccountNums,
            'acctId' => $acctId,
            'projects' => $projects,
            'projectId' => $projectId,
            'userProjects' => $userProjects,
            'all_user_projects' => $all_user_projects,
            'all_user_bank_accounts' =>$all_user_bank_accounts,
            'poFormattingId' => $poFormattingId,
            'show_po_formatting_form' => $show_po_formatting_form,
            'client_service_settings' => $client_service_settings,
            'pending_client_service_settings' => $pending_client_service_settings,
            'delayed_client_service_settings' => $dcss,
            'serviceLevels' => $serviceLevels,
            'amountToPay' => $amountToPay,
            'expirationDate' => $expirationDate,
            'enableAddUser' => $enableAddUser,
            'enableAddProject' => $enableAddProject,
            'ccTypes' => $ccTypes,
            'cCard' => $cCard,
            'user_role'=>Yii::app()->user->ID,
            'users_questions'=>$users_questions,
            'timeis_up'=>$timeis_up,
            'summary_sl_settings'=>$summary_sl_settings,
            'client'=>$client,

        ));
    }

    /**
     * Send request to clients admin to join company
     * @param $join_fed_id
     */
    public function actionRequestToJoinCompany($join_fed_id)
    {
        // get company info
        $company = Companies::model()->with('client')->findByAttributes(array(
            'Company_Fed_ID' => $join_fed_id,
        ));

        if ($company) {
            // if company exists
            $userClList = UsersClientList::model()->findByAttributes(array(
                'Client_ID' => $company->client->Client_ID,
                'User_ID' => Yii::app()->user->userID,
            ));

            if ($userClList) {
                // if user is already in the client's list of the company
                Yii::app()->user->setFlash('success', "You are in the client's list of this company!");
            } else {
                // send request
                $userToAppr = UsersToApprove::model()->findByAttributes(array(
                    'User_ID' => Yii::app()->user->userID,
                    'Client_ID' => $company->client->Client_ID,
                ));

                if (!$userToAppr) {
                    $usersToApprove = new UsersToApprove();
                    $usersToApprove->User_ID = Yii::app()->user->userID;
                    $usersToApprove->New_Client = UsersToApprove::OLD_CLIENT;
                    $usersToApprove->Client_ID = $company->client->Client_ID;
                    $usersToApprove->Approved_By_Client_Admin = UsersToApprove::APPR_BY_CLIENT_ADMIN;

                    $condition = UsersClientList::getClientAdminCondition($usersToApprove->Client_ID);
                    $userClientList = UsersClientList::model()->findAll($condition);

                    $user = Users::model()->with('person')->findByPk(Yii::app()->user->userID);

                    if ($userClientList) {
                        $usersToApprove->Approved_By_Admin = UsersToApprove::APPR_BY_ADMIN;
                        foreach ($userClientList as $clientAdm) {
                            $clientAdmin = Users::model()->with('person')->findByPk($clientAdm->User_ID);
                            $emailSuccess = Mail::sendClientOfUserRegistrationMail($clientAdmin->person->Email, $clientAdmin->person->First_Name, $clientAdmin->person->Last_Name,$user->person->First_Name,$user->person->Last_Name,$company->Company_Name);
                        }
                    } else {
                        $usersToApprove->Approved_By_Admin = UsersToApprove::NOT_APPR_BY_ADMIN;
                        Mail::sendUserRegistrationRequest();
                    }
                    $usersToApprove->save();
                }
                Yii::app()->user->setFlash('success', "Request was sent!");
            }
        } else {
            $_SESSION['show_req_to_join'] = array(
                'fed_id' => $join_fed_id,
                'message' => 'Company ID does not exist'
            );
        }
        $this->redirect('/myaccount?tab=profile');
    }

    /**
     * Get user's filtered documents list action
     */
    public function actionGetFilteredDocumentsList()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['date'])) {
            $userId = Yii::app()->user->userID;

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

            $userInfo = Users::model()->with('clients')->findByPk($userId);

            $documents = new Documents();
            $userDocuments = $documents->findUserDocuments($userId, $date);
            $userProjects = Projects::getUserProjects(Yii::app()->user->userID);

            $this->renderPartial('filtered_documents_list' , array(
                'userDocuments' => $userDocuments,
                'userProjects' => $userProjects,
                'userInfo' => $userInfo,
            ));
        }
    }

    /**
     * Reassign document's client action
     */
    public function actionReassignDocumentsClients()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['docs'])) {
            $userID = Yii::app()->user->userID;
            $docs = $_POST['docs'];

            // change document's clients
            foreach ($docs as $doc) {
                $docID = intval($doc[0]);
                $clientID = intval($doc[1]);
                $projectID = intval($doc[2]);
                if ($docID == 0) {
                    $this->redirect('/myaccount?tab=doc_reassign');
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
     * Removes users from client list action
     */
    public function actionRemoveUser($id)
    {
        $id = intval($id);
        $client_admin = false;
        $userCientRow = UsersClientList::model()->findByAttributes(array(
            'User_ID'=>Yii::app()->user->userID,
            'Client_ID'=>Yii::app()->user->clientID,
        ));

        // check client admin
        if ($userCientRow->hasClientAdminPrivileges()) {
            $client_admin = true;
        }

        if ($client_admin) {
            // if current user is client admin

            $client = Clients::model()->with('users')->findByPk(Yii::app()->user->clientID);
            $client_users = $client->users;
            foreach ($client_users as $key => $cuser) {
                $uClRow = UsersClientList::model()->findByAttributes(array(
                    'User_ID'=>$cuser->User_ID,
                    'Client_ID'=>Yii::app()->user->clientID,
                ));
                $this->clientAdmins[$cuser->User_ID] = $uClRow->hasClientAdminPrivileges() ? 1 : 0;
            }
            //if ($this->clientAdmins[$id] != UsersClientList::CLIENT_ADMIN && $id > 0 || Yii::app()->user->id =='db_admin') {
            if ($this->clientAdmins[$id] != 1 && $id > 0 || Yii::app()->user->id =='db_admin') {
                // if user to delete is not client admin
                $relationRow = UsersClientList::model()->findByAttributes(array(
                    'User_ID' => $id,
                    'Client_ID' => Yii::app()->user->clientID,
                ));

                $relationRow->delete();

                $condition = new CDbCriteria();
                $condition->condition = "Client_ID = '" . Yii::app()->user->clientID . "'";
                $condition->addCondition("User_ID = '" . $id . "'");
                UsersProjectList::model()->deleteAll($condition);

                //if you try to delete himself from users of company you are curently logged in - you will be silently relogined.
                //and as company/project will be choosed first client/project you are connected to.
                if ($id == Yii::app()->user->userID) {
                    Clients::resetClientProjectToFirst($id);
                    $login = new LoginForm();
                    $login->relogin();
                    $relogin_flag = true;
                } else {

                    $client_under_which_user_is_logged = Yii::app()->session->getUserClient($id);


                    if ($client_under_which_user_is_logged && $client_under_which_user_is_logged == Yii::app()->user->clientID) {
                        //if user that was removed from comany is now logged under this company we forcing his logout.
                        Yii::app()->session->destroySessionByUserID($id);
                    }


                }


                 $user = Users::model()->with('projects')->findByPk($id);
                 $defaultProject = Projects::model()->findByPk($user->Default_Project);

                 if ($user->projects && $defaultProject && $defaultProject->Client_ID == Yii::app()->user->clientID) {
                        $user->Default_Project = $user->projects[0]->Project_ID;
                 } else if ($defaultProject && $defaultProject->Client_ID == Yii::app()->user->clientID) {
                        $user->Default_Project = 0;
                 }

                 $user->save();

                    $emailSuccess = Mail::sendRemoveUserFromClientMail($user->person->Email, $user->person->First_Name, $user->person->Last_Name, $client->company->Company_Name);
                if ($relogin_flag) {
                    Yii::app()->user->setFlash('success', "User has been removed successfully! Caution: company and project were changed!");
                    $this->redirect('/');

                } else {
                    Yii::app()->user->setFlash('success', "User has been removed successfully!");
                }


            } else {
                // if user to delete is client admin
                Yii::app()->user->setFlash('error', "You don't have permission for this action!");
            }
        } else {
            Yii::app()->user->setFlash('success', "You don't have permission for this action!");
        }
        $this->redirect('/myaccount?tab=man_users');
    }

    /**
     * Add approves users to client list
     */
    public function actionApproveUsers()
    {
        $client_admin = false;
        $user = Users::model()->findByPk(Yii::app()->user->userID);
        $userCientRow = UsersClientList::model()->findByAttributes(array(
            'User_ID'=>Yii::app()->user->userID,
            'Client_ID'=>Yii::app()->user->clientID,
        ));

        // check client admin
        if ($userCientRow->hasClientAdminPrivileges()) {
            $client_admin = true;
        }
        if ($client_admin) {
            // if current user is client admin

            $client = Clients::model()->with('company')->findByPk(Yii::app()->user->clientID);

            // approve users
            if (isset($_REQUEST['usertoupprove'])) {
                foreach ($_REQUEST['usertoupprove'] as $id => $value) {
                    $id = intval($id);

                    $userToApprove = UsersToApprove::model()->with('user')->findByAttributes(array(
                        'id' => $id,
                        'Client_ID' => Yii::app()->user->clientID,
                    ));

                    //var_dump($userToApprove); die;

                    if ($userToApprove) {
                        // begin transaction
                        $transaction = Yii::app()->db->beginTransaction();
                        try {
                            $user_row = $userToApprove->user;

                            $usersClientList = new UsersClientList;
                            $usersClientList->User_ID = $userToApprove->User_ID;
                            $usersClientList->Client_ID = $userToApprove->Client_ID;
                            if (in_array($user_row->User_Type, UsersClientList::$clientAdmins)) {
                                $usersClientList->User_Type = UsersClientList::CLIENT_ADMIN;
                                $usersClientList->User_Approval_Value = Aps::APPROVED;
                            } else {
                                $usersClientList->User_Type = UsersClientList::USER;
                                $usersClientList->User_Approval_Value = 0;
                            }
                            $usersClientList->save();

                            $projectId = 0;
                            if (is_numeric(Yii::app()->user->projectID)) {
                                $projectId = Yii::app()->user->projectID;
                            } else {
                                $projects = Projects::model()->findByAttributes(array(
                                    'Client_ID' => Yii::app()->user->clientID,
                                ));
                                $projectId = $projects->Project_ID;
                            }

                            $usersProjectList = new UsersProjectList;
                            $usersProjectList->User_ID = $userToApprove->User_ID;
                            $usersProjectList->Client_ID = $userToApprove->Client_ID;
                            $usersProjectList->Project_ID = $projectId;
                            $usersProjectList->save();

                            $user_row->Active = Users::ACTIVE;
                            if ($user_row->User_Pwd == md5('temp100')) {
                                $password = Helper::generatePassword();
                                $user_row->User_Pwd = md5($password);
                                Mail::sendUserRegistrationMail($user_row->person->Email, $user_row->User_Login, $password, $user_row->person->First_Name, $user_row->person->Last_Name, $client->company->Company_Name);
                            } else {
                                Mail::sendAddUserToClientMail($user_row->person->Email, $user_row->person->First_Name, $user_row->person->Last_Name, $client->company->Company_Name);
                            }

                            if ($user_row->Default_Project == 0) {
                                $user_row->Default_Project = $projectId;
                            }

                            $user_row->save();
                            $userToApprove->delete();
                            $transaction->commit();
                        } catch(Exception $e) {
                            $transaction->rollback();
                        }
                    }
                }
            }
            Yii::app()->user->setFlash('success', "Users have been successfully added! Please assign projects for this users.");
            $this->redirect('/myaccount?tab=man_users');
        } else {
            Yii::app()->user->setFlash('success', "You don't have permission for this action!");
            $this->redirect('/myaccount?tab=man_users');
        }
    }

    /**
     * Add users to client list
     */
    public function actionAddUser($id)
    {
        $id = intval($id);
        $client_admin = false;
        $user = Users::model()->findByPk(Yii::app()->user->userID);
        $userCientRow = UsersClientList::model()->findByAttributes(array(
            'User_ID'=>Yii::app()->user->userID,
            'Client_ID'=>Yii::app()->user->clientID,
        ));

        // check client admin
        if ($userCientRow->hasClientAdminPrivileges()) {
            $client_admin = true;
        }

        if ($client_admin) {
            // if current user is client admin

            $check = UsersClientList::model()->findByAttributes(array(
                'User_ID' => $id,
                'Client_ID' => Yii::app()->user->clientID,
            ));
            if ($check) {
                Yii::app()->user->setFlash('success', "This user is already in the list!");
                $this->redirect('/myaccount?tab=man_users');
            } else {
                // add new user to client's list

                $usersToApprove = UsersToApprove::model()->findByAttributes(array(
                    'User_ID'=>$id,
                ));

                $projectId = 0;
                if (is_numeric(Yii::app()->user->projectID)) {
                    $projectId = Yii::app()->user->projectID;
                } else {
                    $projects = Projects::model()->findByAttributes(array(
                        'Client_ID' => Yii::app()->user->clientID,
                    ));
                    $projectId = $projects->Project_ID;
                }

                $client = Clients::model()->with('company')->findByPk(Yii::app()->user->clientID);
                if ($usersToApprove && $usersToApprove->Approved_By_Admin == UsersToApprove::NOT_APPR_BY_ADMIN) {
                    Yii::app()->user->setFlash('success', "You don't have permission to add this user. User must be perviosly approved by system admin.");
                    $this->redirect('/myaccount?tab=man_users');
                } else if ($usersToApprove && $usersToApprove->Approved_By_Admin == UsersToApprove::APPR_BY_ADMIN) {
                    // begin transaction
                    $transaction = Yii::app()->db->beginTransaction();
                    try {
                        $userToAdd = Users::model()->with('person')->findByPk($id);

                        $usersClientList = new UsersClientList;
                        $usersClientList->User_ID = $id;
                        $usersClientList->Client_ID = Yii::app()->user->clientID;
                        if (in_array($userToAdd->User_Type, UsersClientList::$clientAdmins)) {
                            $usersClientList->User_Type = UsersClientList::CLIENT_ADMIN;
                            $usersClientList->User_Approval_Value = Aps::APPROVED;
                        } else {
                            $usersClientList->User_Type = UsersClientList::USER;
                            $usersClientList->User_Approval_Value = 0;
                        }
                        $usersClientList->save();

                        $usersProjectList = new UsersProjectList;
                        $usersProjectList->User_ID = $id;
                        $usersProjectList->Client_ID = Yii::app()->user->clientID;
                        $usersProjectList->Project_ID = $projectId;
                        $usersProjectList->save();

                        $usersToApprove = UsersToApprove::model()->findByAttributes(array(
                            'User_ID'=>$id,
                            'Client_ID'=>Yii::app()->user->clientID,
                        ));

                        if ($usersToApprove) {
                            $usersToApprove->delete();
                        }

                        $userToAdd->Active = Users::ACTIVE;
                        if ($userToAdd->Default_Project == 0) {
                            $userToAdd->Default_Project = $projectId;
                        }

                        if ($userToAdd->User_Pwd == md5('temp100')) {
                            $password = Helper::generatePassword();
                            $userToAdd->User_Pwd = md5($password);
                            Mail::sendUserRegistrationMail($userToAdd->person->Email, $userToAdd->User_Login, $password, $userToAdd->person->First_Name, $userToAdd->person->Last_Name, $client->company->Company_Name);
                        } else {
                            Mail::sendAddUserToClientMail($userToAdd->person->Email, $userToAdd->person->First_Name, $userToAdd->person->Last_Name, $client->company->Company_Name);
                        }
                        $userToAdd->save();
                        Yii::app()->user->setFlash('success', "User has been successfully added! Please assign projects for this user.");
                        $transaction->commit();
                    } catch(Exception $e) {
                        $transaction->rollback();
                    }

                    $this->redirect('/myaccount?tab=man_users');
                } else if ($id > 0) {
                    // begin transaction
                    $transaction = Yii::app()->db->beginTransaction();
                    try {
                        $userToAdd = Users::model()->with('person')->findByPk($id);

                        $usersClientList = new UsersClientList;
                        $usersClientList->User_ID = $id;
                        $usersClientList->Client_ID = Yii::app()->user->clientID;
                        if (in_array($userToAdd->User_Type, UsersClientList::$clientAdmins)) {
                            $usersClientList->User_Type = UsersClientList::CLIENT_ADMIN;
                            $usersClientList->User_Approval_Value = Aps::APPROVED;
                        } else {
                            $usersClientList->User_Type = UsersClientList::USER;
                            $usersClientList->User_Approval_Value = 0;
                        }
                        $usersClientList->save();

                        $usersProjectList = new UsersProjectList;
                        $usersProjectList->User_ID = $id;
                        $usersProjectList->Client_ID = Yii::app()->user->clientID;
                        $usersProjectList->Project_ID = $projectId;
                        $usersProjectList->save();

                        $userToAdd = Users::model()->with('person')->findByPk($id);
                        if ($userToAdd->Default_Project == 0) {
                            $userToAdd->Default_Project = $projectId;
                        }

                        $userToAdd->Active = Users::ACTIVE;
                        Mail::sendAddUserToClientMail($userToAdd->person->Email, $userToAdd->person->First_Name, $userToAdd->person->Last_Name, $client->company->Company_Name);
                        $userToAdd->save();
                        Yii::app()->user->setFlash('success', "User has been successfully added! Please assign projects for this user.");
                        $transaction->commit();
                    } catch(Exception $e) {
                        $transaction->rollback();
                    }
                    $this->redirect('/myaccount?tab=man_users');
                } else {
                    Yii::app()->user->setFlash('success', "You don't have permission to add this user.");
                    $this->redirect('/myaccount?tab=man_users');
                }
            }
        } else {
            Yii::app()->user->setFlash('success', "You don't have permission for this action!");
            $this->redirect('/myaccount?tab=man_users');
        }
    }

    /**
     * Get client's bank account info
     */
    public function actionGetBankAcctInfo()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['acctId'])) {
            $acctId = intval($_POST['acctId']);
            $info = '';
            $editingAccess = 0;

            $bankAcct = BankAcctNums::model()->findByAttributes(array(
                'Client_ID' => Yii::app()->user->clientID,
                'Account_Num_ID' => $acctId,
            ));

            if ($bankAcct) {
                $userClientRow = UsersClientList::model()->findByAttributes(array(
                    'User_ID' => Yii::app()->user->userID,
                    'Client_ID' => Yii::app()->user->clientID,
                ));

                $info = $this->renderPartial('bank_acct_info' , array(
                    'bankAcct' => $bankAcct,
                    'userClientRow' => $userClientRow,
                ), true);

                $linkedPayments = Payments::model()->findByAttributes(array(
                    'Account_Num_ID' => $bankAcct->Account_Num_ID,
                ));

                if (!$linkedPayments) {
                    $editingAccess = 1;
                }
            }

            echo CJSON::encode(array(
                'info' => $info,
                'editingAccess' => $editingAccess,
            ));
        }
    }

    /**
     * Get client's project info
     */
    public function actionGetProjectInfo()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['projectId'])) {
            $projectId = intval($_POST['projectId']);
            $info = '';
            $editingAccess = 0;

            $project = Projects::model()->findByAttributes(array(
                'Client_ID' => Yii::app()->user->clientID,
                'Project_ID' => $projectId,
            ));

            if ($project) {
                $userClientRow = UsersClientList::model()->findByAttributes(array(
                    'User_ID' => Yii::app()->user->userID,
                    'Client_ID' => Yii::app()->user->clientID,
                ));

                $poFormatting = PoFormatting::model()->findByAttributes(array(
                    'Project_ID' => $project->Project_ID,
                ));

                if (!$poFormatting) {
                    $poFormatting = new PoFormatting();
                    $poFormatting->Project_ID = $project->Project_ID;
                    $poFormatting->PO_Format_Client_Name = $project->client->company->Company_Name;
                    $poFormatting->PO_Format_Project_Name = $project->Project_Name;
                    $poFormatting->PO_Format_Address = $project->client->company->adreses[0]->Address1;
                    $poFormatting->PO_Format_City_St_ZIP = Helper::createFullAddressLine('', $project->client->company->adreses[0]->City, $project->client->company->adreses[0]->State, $project->client->company->adreses[0]->ZIP);
                    $poFormatting->PO_Format_Phone = $project->client->company->adreses[0]->Phone;
                    $poFormatting->PO_Format_Sig_Req = 0;
                    $poFormatting->save();
                }

                $info = $this->renderPartial('project_info' , array(
                    'project' => $project,
                    'userClientRow' => $userClientRow,
                    'poFormatting' => $poFormatting,
                ), true);

                $condition = new CDbCriteria();
                $condition->condition = "t.Project_ID=:Project_ID";
                $condition->addCondition("t.Document_Type != '" . Documents::W9 . "'");
                $condition->params = array('Project_ID' => $projectId);
                $linkedDocuments = Documents::model()->find($condition);

                if (!$linkedDocuments) {
                    $editingAccess = 1;
                }
            }

            echo CJSON::encode(array(
                'info' => $info,
                'editingAccess' => $editingAccess,
                'currentPoFormatting' => $poFormatting->PO_Form_ID,
            ));
        }
    }

    /**
     * Get user's client project
     */
    public function actionGetUserClientProjects()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['clientId'])) {
            $clientId = intval($_POST['clientId']);

            $userProjects = Projects::getUserProjects(Yii::app()->user->userID, $clientId);

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
     * Get User project number
     */
    public function actionGetUserProjectNumber()
    {
        if (Yii::app()->request->isAjaxRequest) {
            echo Yii::app()->user->projectID;
        }
    }

    /**
     * Get user's projects list
     */
    public function actionGetUsersProjectsList()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['userId'])) {
            $userId = intval($_POST['userId']);

            $client_admin = false;
            $userCientRow = UsersClientList::model()->findByAttributes(array(
                'User_ID'=>Yii::app()->user->userID,
                'Client_ID'=>Yii::app()->user->clientID,
            ));


            // check client admin
            if ($userCientRow->hasClientAdminPrivileges()) {
                $client_admin = true;
            }

            //check user
            $userClientRow = UsersClientList::model()->findByAttributes(array(
                'User_ID' => $userId,
                'Client_ID' => Yii::app()->user->clientID,
            ));

            $nativetype=Users::model()->findByPk($userId)->getUserNativeType();


            if ($userClientRow && $client_admin) {
                $clientProjects = Projects::model()->findAllByAttributes(array(
                    'Client_ID' => Yii::app()->user->clientID,
                ));

                $userProjectsList = UsersProjectList::model()->findAllByAttributes(array(
                    'Client_ID' => Yii::app()->user->clientID,
                    'User_ID' => $userId,
                ));

                $userProjects = array();
                if ($userProjectsList) {
                    foreach($userProjectsList as $project) {
                        $userProjects[] = $project->Project_ID;
                    }
                }

                $this->renderPartial('users_projects' , array(
                    'userProjects' => $userProjects,
                    'clientProjects' => $clientProjects,
                    'userId' => $userId,
                    'userClientRow' => $userClientRow,
                    'nativetype'=>$nativetype,
                ));
            }
        }
    }

    /**
     * Find users by email
     */
    public function actionFindUserByEmail()
    {
        $users = array();
        $message = '';
        $exist = false;
        $client_admin = false;
        $user = Users::model()->findByPk(Yii::app()->user->userID);
        $userCientRow = UsersClientList::model()->findByAttributes(array(
            'User_ID'=>Yii::app()->user->userID,
            'Client_ID'=>Yii::app()->user->clientID,
        ));

        // check client admin
        if ($userCientRow->hasClientAdminPrivileges()) {
            $client_admin = true;
        }

        if ($client_admin && Yii::app()->request->isAjaxRequest && isset($_POST['email'])) {
            // if current user is client admin and is ajax request

            $email = $_POST['email'];
            $users = Persons::model()->with('user')->findAllByAttributes(array(
                'Email'=>$email,
            ));

            if (count($users) > 1) {
                foreach ($users as $key => $user) {
                    $check = UsersClientList::model()->findByAttributes(array(
                        'User_ID' => $user->user->User_ID,
                        'Client_ID' => Yii::app()->user->clientID,
                    ));

                    if ($check) {
                        unset($users[$key]);
                    }
                }

                if (count($users) == 0) {
                    $exist = true;
                    $message = "Users with email '$email' are already in the list!";
                }
            } else if (count($users) == 1) {
                $check = UsersClientList::model()->findByAttributes(array(
                    'User_ID' => $users[0]->user->User_ID,
                    'Client_ID' => Yii::app()->user->clientID,
                ));

                if ($check) {
                    $exist = true;
                    $message = "User with email '$email' is already in the list!";
                }
            } else {
                $message = 'No users with email: ' . $email;
            }
        }

        $this->renderPartial('founded_users' , array(
            'users' => $users,
            'message' => $message,
            'exist' => $exist,
        ));
    }

    /**
     * Reject users
     */
    public function actionRejectUsers()
    {
        $client_admin = false;
        $user = Users::model()->findByPk(Yii::app()->user->userID);
        $userCientRow = UsersClientList::model()->findByAttributes(array(
            'User_ID'=>Yii::app()->user->userID,
            'Client_ID'=>Yii::app()->user->clientID,
        ));

        // check client admin
        if ($userCientRow->hasClientAdminPrivileges()) {
            $client_admin = true;
        }

        if ($client_admin) {
            // if current user is client admin

            $client = Clients::model()->with('company')->findByPk(Yii::app()->user->clientID);

            //reject users
            if (isset($_REQUEST['usertoupprove'])) {
                foreach ($_REQUEST['usertoupprove'] as $id => $value) {
                    $id = intval($id);

                    $userToApprove = UsersToApprove::model()->with('user')->findByAttributes(array(
                        'id' => $id,
                        'Client_ID' => Yii::app()->user->clientID,
                    ));

                    if  ($userToApprove) {
                        $user = $userToApprove->user;
                        Mail::sendRejectMail($user->person->Email, $user->person->First_Name, $user->person->Last_Name, $client->company->Company_Name);
                        $userToApprove->delete();
                    }
                }
            }
            Yii::app()->user->setFlash('success', "Users were rejected!");
        } else {
            Yii::app()->user->setFlash('success', "You don't have permission for this action!");
        }
        $this->redirect('/myaccount?tab=man_users');
    }

    /*
     * Approve User's projects
     */
    public function actionApproveUsersProjects()
    {
        $client_admin = false;
        $userCientRow = UsersClientList::model()->findByAttributes(array(
            'User_ID'=>Yii::app()->user->userID,
            'Client_ID'=>Yii::app()->user->clientID,
        ));

        // check client admin
        if ($userCientRow->hasClientAdminPrivileges()) {
            $client_admin = true;
        }

        if ($client_admin) {
            // if current user is client admin

            //approve user projects
            if (isset($_POST['projecttoupprove']) && isset($_POST['user_id']) && isset($_POST['UsersClientList'])) {
                $userId = intval($_POST['user_id']);
                $userType = trim($_POST['UsersClientList']['User_Type']);
                $approverValue = isset($_POST['UsersClientList']['User_Approval_Value']) ? intval($_POST['UsersClientList']['User_Approval_Value']) : 0;

                //check user
                $userClientRow = UsersClientList::model()->findByAttributes(array(
                    'User_ID' => $userId,
                    'Client_ID' => Yii::app()->user->clientID,
                ));

                if ($userClientRow) {
                    $newProjects = array();
                    // check projects
                    foreach ($_POST['projecttoupprove'] as $id => $value) {
                        $projectId = intval($id);

                        $project = Projects::model()->findByAttributes(array(
                            'Project_ID' => $projectId,
                            'Client_ID' => Yii::app()->user->clientID,
                        ));

                        if  ($project) {
                            $newProjects[] = $projectId;
                        }
                    }

                    if (in_array($userType, UsersClientList::$availableTypes)) {
                        //here we need to check if at least one client admin remains in system
                        $userClientRow->changeUserType($userType);
                        $approverValuePrev = $userClientRow->User_Approval_Value;
                        $approverValueNew = UsersClientList::checkUserApprovalValue($userId, Yii::app()->user->clientID, $approverValue);

                        $userClientRow->User_Approval_Value = $approverValueNew;

                        //nex block can be used for checking if in system there is at least one approver with apr value =100
                        /*if ( $approverValuePrev == Aps::APPROVED && $approverValueNew!=Aps::APPROVED && UsersClientList::isLastApprover($userClientRow->Client_ID,$userClientRow->User_ID) ) {
                            Yii::app()->user->setFlash('error', "At least one Approver must have an Approval value of 100.");
                            $this->redirect('/myaccount?tab=man_users');
                            die();
                        } */


                            $userClientRow->save();



                    }

                    if (count($newProjects) > 0) {
                        $condition = new CDbCriteria();
                        $condition->condition = "Client_ID = '" . Yii::app()->user->clientID . "'";
                        $condition->addCondition("User_ID = '" . $userId . "'");
                        UsersProjectList::model()->deleteAll($condition);

                        if ($userClientRow->hasClientAdminPrivileges()) {
                            // if user has client admin privileges than assign all client projects to him
                            $client = Clients::model()->with('projects')->findByPk(Yii::app()->user->clientID);
                            $clientProjects = $client->projects;
                            // set user's projects
                            foreach ($clientProjects as $project) {
                                $usersProject = new UsersProjectList();
                                $usersProject->Client_ID = Yii::app()->user->clientID;
                                $usersProject->Project_ID = $project->Project_ID;
                                $usersProject->User_ID = $userId;
                                $usersProject->save();
                            }
                        } else {
                            // set user's projects
                            foreach ($newProjects as $newProject) {
                                $usersProject = new UsersProjectList();
                                $usersProject->Client_ID = Yii::app()->user->clientID;
                                $usersProject->Project_ID = $newProject;
                                $usersProject->User_ID = $userId;
                                $usersProject->save();
                            }
                        }
                    }
                }
            }
            Yii::app()->user->setFlash('success', "User's Projects and Type have been saved!");
        } else {
            Yii::app()->user->setFlash('success', "You don't have permission for this action!");
        }
        $this->redirect('/myaccount?tab=man_users');
    }

    /**
     * Sort users
     * @param $a
     * @param $b
     * @return int
     */
    public function sortClientUsers($a, $b)
    {
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
     * Get user's file html
     */
    public function actionGetUserFile()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['docId'])) {
            $doc_id = intval($_POST['docId']);
            if ($doc_id > 0 && Documents::hasAccess($doc_id)) {
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
     * Update Company Service Level
     */
    public function actionUpdateCompanyServiceLevel()
    {
        // get user-client relationship
        $userClientRow = UsersClientList::model()->findByAttributes(array(
            'User_ID'=>Yii::app()->user->userID,
            'Client_ID'=>Yii::app()->user->clientID,
        ));

        // if isset form and user is client-admin
        if (isset($_POST['ClientServiceSettings']) && $userClientRow->hasClientAdminPrivileges()) {
            $clientServiceSettings = ClientServiceSettings::model()->findByAttributes(array(
                'Client_ID'=>Yii::app()->user->clientID,
            ));

            $pendingClientServiceSettings = PendingClientServiceSettings::model()->findByAttributes(array(
                'Client_ID'=>Yii::app()->user->clientID,
            ));

            if (!$pendingClientServiceSettings) {
                $pendingClientServiceSettings = new PendingClientServiceSettings();
                $pendingClientServiceSettings->Client_ID = Yii::app()->user->clientID;
            }

            $pendingClientServiceSettings->setAttributes($_POST['ClientServiceSettings']);
            //$pendingClientServiceSettings->setFee();
            $pendingClientServiceSettings->Fee_To_Upgrade = $pendingClientServiceSettings->Fee - $clientServiceSettings->Fee;

            if ($pendingClientServiceSettings->validate()) {
                $pendingClientServiceSettings->save();
                $pendingClientServiceSettings->checkSettings($clientServiceSettings);
            }
        }

        $this->redirect('/myaccount?tab=service');
    }

    /**
     * Apply new service settings
     */
    public function actionApplyServises()
    {
        // get user-client relationship
        $userClientRow = UsersClientList::model()->findByAttributes(array(
            'User_ID'=>Yii::app()->user->userID,
            'Client_ID'=>Yii::app()->user->clientID,
        ));

        // if user is client-admin
        if ($userClientRow->hasClientAdminPrivileges()) {
            $clientServiceSettings = ClientServiceSettings::model()->findByAttributes(array(
                'Client_ID'=>Yii::app()->user->clientID,
            ));

            $pendingClientServiceSettings = PendingClientServiceSettings::model()->findByAttributes(array(
                'Client_ID'=>Yii::app()->user->clientID,
            ));

            if ($pendingClientServiceSettings) {
                $pendingClientServiceSettings->checkAndApplySettings($clientServiceSettings);
                Yii::app()->user->setFlash('success', "New settings have been applied!");
            }
        }

        $this->redirect('/myaccount?tab=service');
    }

    /**
     * Reject new service settings
     */
    public function actionRejectServises()
    {
        // get user-client relationship
        $userClientRow = UsersClientList::model()->findByAttributes(array(
            'User_ID'=>Yii::app()->user->userID,
            'Client_ID'=>Yii::app()->user->clientID,
        ));

        // if user is client-admin
        if ($userClientRow->hasClientAdminPrivileges()) {
            $pendingClientServiceSettings = PendingClientServiceSettings::model()->findByAttributes(array(
                'Client_ID'=>Yii::app()->user->clientID,
            ));

            if ($pendingClientServiceSettings) {
                $pendingClientServiceSettings->delete();
                Yii::app()->user->setFlash('success', "New settings have been rejected!");
            }
        }

        $this->redirect('/myaccount?tab=service');
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
     * Generate invoice to payment
     */
    public function actionInvoiceToPayment($amount)
    {
        // get user-client relationship
        $userClientRow = UsersClientList::model()->findByAttributes(array(
            'User_ID'=>Yii::app()->user->userID,
            'Client_ID'=>Yii::app()->user->clientID,
        ));

        // if user is client-admin
        if ($userClientRow->hasClientAdminPrivileges()) {
            $amount = floatval($amount);
            if ($amount > 0) {
                ServicePayments::generateInvoice(Yii::app()->user->clientID, Yii::app()->user->userID, $amount);
            }
        }
        $this->redirect('/myaccount?tab=service');
    }

    /**
     * Upload and send payment to support email
     */
    public function actionUploadServicePayment()
    {
        if (isset($_FILES)) {
            // create user's folder
            if (!file_exists('protected/data/current_uploads_files/' . Yii::app()->user->userID)) {
                mkdir('protected/data/current_uploads_files/' . Yii::app()->user->userID, 0777);
            }

            if (!file_exists('protected/data/current_uploads_files/' . Yii::app()->user->userID . '/' . date('Y-m-d'))) {
                mkdir('protected/data/current_uploads_files/' . Yii::app()->user->userID  . '/' . date('Y-m-d'), 0777);
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

                            $client = Clients::model()->with('company')->findByPk(Yii::app()->user->clientID);

                            $filepath = 'protected/data/current_uploads_files/' . Yii::app()->user->userID . '/' . date('Y-m-d'). '/' . $_FILES['userfile']['name'];
                            move_uploaded_file($_FILES['userfile']['tmp_name'], $filepath);

                            //send document
                            Mail::sendServicePayment($_FILES['userfile']['name'], $filepath, $client->company->Company_Name, $client->company->Company_Fed_ID);

                            //delete file
                            @unlink($filepath);
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
     * Check existing of User's credit Card
     */
    public function actionCheckCreditCard()
    {
        if (Yii::app()->request->isAjaxRequest) {
            $cc = Ccs::getUserCreditCard(Yii::app()->user->userID);
            if ($cc) {
                echo 1;
            } else {
                echo 0;
            }
        }
    }

    /**
     * Check if if user has Stripe customer-id or not
     */
    public function actionCheckStripeCustomer()
    {
        if (Yii::app()->request->isAjaxRequest) {
            $stripeCustomer = StripeCustomers::model()->findByPk(Yii::app()->user->userID);
            $result = array(
                'success' => 0,
                'ccInfo' => '',
            );

            if ($stripeCustomer) {
                Yii::import("ext.stripe.lib.Stripe", true);


                try{
                    Stripe::setApiKey(Yii::app()->config->get('STRIPE_SECRET_KEY'));

                    Stripe::setApiVersion("2014-06-17");

                    $customer = Stripe_Customer::retrieve($stripeCustomer->Customer_ID);

                    if(isset($customer->cards["data"][0]['id']) && is_string($customer->cards["data"][0]['id'])) {
                        $result['ccInfo'] = $this->renderPartial('cc_info' , array(
                            'cardInfo' => $customer->cards["data"][0],
                        ), true);
                        $result['success'] = 1;
                    }
                } catch (Exception $e) {
                    $result['success'] = 0;
                }
            }

            echo CJSON::encode($result);
        }
    }

    public function actionUnsetDeviceCheck(){
        if (Yii::app()->request->isAjaxRequest ) {

            if( isset($_POST['login']) && strval($_POST['login'])!='' ) {
               $user_id =  Users::model()->findByAttributes(array(
                   'User_Login'=>strval($_POST['login'])
               ))->User_ID;
            } else {
                $user_id = Yii::app()->user->userID;
            }

            $users_settings = UsersSettings::model()->findByAttributes(array(
                'User_ID'=>$user_id
            ));
            $users_settings->Use_Device_Checking = 0;
            $users_settings->scenario = 'update';
            $users_settings->save();

        }
    }

    public function actionSetDeviceCheck(){
            if (Yii::app()->request->isAjaxRequest ) {
            //finding user - current or by login
            if( isset($_POST['login']) && strval($_POST['login'])!='' ) {
                   $user_id =  Users::model()->findByAttributes(array(
                   'User_Login'=>strval($_POST['login'])))->User_ID;
            } else {
                   $user_id = Yii::app()->user->userID;
            }
                $count_questions = UsersQuestions::getCountUserQuestions($user_id);
                if ($count_questions==3)
                {
                    $users_settings = UsersSettings::model()->findByAttributes(array(
                        'User_ID'=>$user_id
                    ));


                    $users_settings->Use_Device_Checking = 1;
                    $users_settings->scenario = 'update';
                    $users_settings->save();
                    echo 'saved';
                } else {
                    echo 'not saved';
                }

            }
    }

    public function actionGetuserQuestion(){
        if (isset($_POST['UsersSettings'])) {
            try {
            $user_settings = UsersSettings::model()->findByPk(Yii::app()->user->userID);
            $user_settings->Use_Device_Checking = intval($_POST['UsersSettings']['Use_Device_Checking']);
            $user_settings->save();

            Yii::app()->user->setFlash('success', "Use_Device_Checking successfully changed");
            } catch (Exception $e) {
                Yii::app()->user->setFlash('error', "You don't have permission for this action!");
            }
            $this->redirect('/myaccount?tab=security');
        }
    }

    /**
     * Shows how many documents present in selected Tier
     */
    public function actionCheckTierLevelUsage (){
        if (Yii::app()->request->isAjaxRequest && isset($_POST['tier_level']) ) {
            $id = intval($_POST['tier_level']);
            $result = TiersSettings::CheckTierLevelUsage($id);
            echo $result;
        }

    }

    public function actionUpdateUsersSettingsManual (){
        if (Yii::app()->request->isAjaxRequest && isset($_POST['three_days_add'])) {
            //we need to allow user use the system three more days until payment comes.
            //ClientServiceSettings::AddThreeDays(Yii::app()->user->clientID);
        }
    }

    public function actionUpdateUsersSettingsNew (){

        if (Yii::app()->request->isAjaxRequest && isset($_POST['active_to']) && count($_POST['tiers'])>0 ) {
            $users= intval($_POST['users']);
            $projects= intval($_POST['projects']);
            $storage= intval($_POST['storage']);
            $active_to = strval($_POST['active_to']);
            $tiers_arr_after = $_POST['tiers'];

            if (ClientServiceSettings::CheckMinMaxValues($users,$projects,$storage)) {
                //previous settings and prices
                $css = ClientServiceSettings::model()->findByPk(Yii::app()->user->clientID);

                $base_fee_prev =  ClientServiceSettings::CalculateBaseFee(explode(',',$css->Service_Level_ID));

                $add_fee_prev = ClientServiceSettings::CalculatePrevAddFee($css->Additional_Users,$css->Additional_Projects,$css->Additional_Storage);
                $monthly_price_prev = $base_fee_prev + $add_fee_prev;

                //new settings and prices
                $base_fee_new = ClientServiceSettings::CalculateBaseFee($tiers_arr_after);

                $add_fee_new = ClientServiceSettings::CalculateAddFee($users,$projects,$storage);

                $monthly_price_new = $base_fee_new + $add_fee_new;


                //analizing Active_To dates and calculating differences
                $periods_for_base = Helper::calculatePeriodsBetweenDates(date('m/d/Y',strtotime($css->Active_To)),$active_to);
                $periods_for_added = Helper::calculatePeriodsBetweenDates(date('m/d/Y'),$active_to);


                if ( $periods_for_base != 0 ) { //it means that active_to date changed
                    $add_price_diff = ($add_fee_new - $add_fee_prev) * ($periods_for_added-$periods_for_base);
                    $base_price_difference = ($base_fee_new - $base_fee_prev) * $periods_for_base;
                    $changed_fee = $add_price_diff + $base_price_difference;
                    if ($changed_fee < 0) $changed_fee = 0;
                    $changed_fee = $changed_fee  +  $monthly_price_new * $periods_for_base;
                } else {
                    $add_price_diff = ($add_fee_new - $add_fee_prev) * $periods_for_added;
                    $base_price_difference = $base_fee_new - $base_fee_prev;
                    $changed_fee = $add_price_diff + $base_price_difference;
                }

                //calculating pending settings
                if ($changed_fee > 0) {

                    $pcss = PendingClientServiceSettings::model()->findByPk(Yii::app()->user->clientID);
                    if (!$pcss) {
                        $pcss = new PendingClientServiceSettings();
                        $pcss->Client_ID = Yii::app()->user->clientID;
                    }
                    $pcss->Service_Level_ID = implode(',',$tiers_arr_after);
                    $pcss->Additional_Users = $users ;
                    $pcss->Additional_Projects	= $projects-1;
                    $pcss->Additional_Storage = $storage-1;
                    $pcss->Pending_Active_From = $active_from ? date('Y-m-d',strtotime($active_from)) : '0000-00-00';
                    $pcss->Pending_Active_To = date('Y-m-d',strtotime($active_to));
                    $pcss->Fee = round($monthly_price_new,2);
                    $pcss->Fee_To_Upgrade = round($changed_fee,2);
                    $pcss->Approved =0;
                    $pcss->save();

                    echo CJSON::encode($pcss->getSettings());
                } else {
                    //delayed settings
                    $active_from = date('m/d/Y',strtotime($css->Active_To));

                    $date_array = ServiceLevelSettings::getNextActiveToList($css->Active_To);
                    $active_to = ($active_from == $active_to) ? $date_array[1] : $active_to;

                    $dcss = DelayedClientServiceSettings::model()->findByPk(Yii::app()->user->clientID);
                    if (!$dcss) {
                        $dcss = new DelayedClientServiceSettings();
                        $dcss->Client_ID = Yii::app()->user->clientID;
                    }
                    $dcss->Service_Level_ID = implode(',',$tiers_arr_after);
                    $dcss->Additional_Users =$users ;;
                    $dcss->Additional_Projects =  $projects-1;
                    $dcss->Additional_Storage = $storage-1;;
                    $dcss->Active_To = date('Y-m-d',strtotime($active_to));
                    $dcss->Active_From = date('Y-m-d',strtotime($active_from));
                    $dcss->Fee = round($monthly_price_new,2);

                    $dcss->save();
                    $this->redirect('/myaccount/?tab=settings');

                }

            }

        }
    }


    public function actionUpdateUsersSettings (){

        if (Yii::app()->request->isAjaxRequest && isset($_POST['active_to']) && count($_POST['tiers'])>0 ) {

            $users= intval($_POST['users']);
            $projects= intval($_POST['projects']);
            $storage= intval($_POST['storage']);
            $active_to = strval($_POST['active_to']);


            $tiers_arr_after = is_array($_POST['tiers']) ? $_POST['tiers'] : array();
            $tiers_str_after = implode(',',$tiers_arr_after);



            $calculation_result = ClientServiceSettings::Calculation($users,$projects,$storage,$active_to,$tiers_str_after,Yii::app()->user->clientID);

                if ($calculation_result && $calculation_result['service_level_grown'] ){
                    $new_monthly_price = $calculation_result['new_monthly_price'];
                    $add_users = $calculation_result['add_users'];
                    $add_projects = $calculation_result['add_projects'];
                    $add_storage = $calculation_result['add_storage'];
                    $changed_fee = $calculation_result['changed_fee'];

                    $pcss = PendingClientServiceSettings::updateSettingsAlternative (Yii::app()->user->clientID,$active_to,$tiers_str_after,$new_monthly_price,$changed_fee,$add_users,$add_projects,$add_storage);

                    echo CJSON::encode($pcss->getSettings());

                } else if ($calculation_result && !$calculation_result['service_level_grown'] ) {

                    $css = ClientServiceSettings::model()->findByPk(Yii::app()->user->clientID);
                    //we need to calculate delayed settings
                    $active_from = date('m/d/Y',strtotime($css->Active_To));

                    //recalculate using active from date
                    $calculation_result = ClientServiceSettings::Calculation($users,$projects,$storage,$active_to,$tiers_str_after,Yii::app()->user->clientID,$active_from);
                    $new_monthly_price = $calculation_result['new_monthly_price'];
                    $add_users = $calculation_result['add_users'];
                    $add_projects = $calculation_result['add_projects'];
                    $add_storage = $calculation_result['add_storage'];
                    $changed_fee = $calculation_result['changed_fee'];


                    $date_array = ServiceLevelSettings::getNextActiveToList($css->Active_To);
                    $active_to = ($active_from == $active_to) ? $date_array[1] : $active_to;

                    //settings update needed for purposes when date changed - > price should be recalculated
                    DelayedClientServiceSettings::createDelayedFromData(Yii::app()->user->clientID,$tiers_str_after,$add_users,$add_projects,$add_storage,$active_to,$active_from,$new_monthly_price);
                    //$pcss = PendingClientServiceSettings::updateSettings(Yii::app()->user->clientID,$active_to,$tiers_str_after,$new_monthly_price,$users,$projects,$storage,$active_from);
                    $pcss = PendingClientServiceSettings::updateSettingsAlternative (Yii::app()->user->clientID,$active_to,$tiers_str_after,$new_monthly_price,$changed_fee,$add_users,$add_projects,$add_storage,$active_from);

                    echo CJSON::encode($pcss->getSettings());

                }

            } else {die;}
    }


    public function actionApplyPendingService() {
        if (Yii::app()->request->isAjaxRequest ) {
            PendingClientServiceSettings::setApproved(Yii::app()->user->clientID);
        }
    }

    public function actionLogToHistory() {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['action']) ) {

            History::Log(intval($_POST['action']),date('Y-m-d H:i:s'),Yii::app()->user->userLogin,Yii::app()->user->clientID);

        }
    }

    public function actionUpdateUsersApprovalValues() {

        if (isset($_POST['users'])) {

            $users = $_POST['users'];


            //change approval values
            foreach ($users as $userID => $approvalValue) {
                $userID = intval($userID);
                if ($userID == 0) {
                    $this->redirect('/admin?tab=us_appr_value');
                    die;
                }

                $userToClient = UsersClientList::model()->findByAttributes(array(
                    'User_ID' => $userID,
                    'Client_ID' => Yii::app()->user->clientID,
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
                    $userToClient->save();

                }
            }

            Yii::app()->user->setFlash('success', "Approval values have been successfully updated!");
            $this->redirect(Yii::app()->request->urlReferrer);
        } else {
            Yii::app()->user->setFlash('success', "Approval values have not been updated!");
            $this->redirect(Yii::app()->request->urlReferrer);
        }
    }

}