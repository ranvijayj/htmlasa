<?php

class SiteController extends Controller
{
    public $layout='//layouts/column1';
    public $login_model = false;
    public $register_model = false;
    public $forgot_password_model = false;

    /**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

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
                'actions'=>array('index', 'error', 'contact', 'testforw9', 'login', 'captcha', 'page','CheckDeviceBeforeLogin'),
                'users'=>array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('GetUserClientsList', 'ChangeClient', 'GetUserInfo', 'logout','progressbar','GetTextProgressbar',
                    'changedisplaylimit','ChangeVendorsLeftDisplayLimit','ChangeVendorsRightDisplayLimit','Relogin','CollectClientInfo'),
                'users'=>array('@'),
            ),
            array('deny', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('register', 'forgotpassword'),
                'users'=>array('@'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('register', 'forgotpassword'),
                'users'=>array('*'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
        /*
        $users = Users::model()->findAll();
        foreach ($users as $user) {
            $userClientRows = UsersClientList::model()->findAllByAttributes(array(
                'User_ID' => $user->User_ID,
            ));
            foreach ($userClientRows as $userClientRow) {
                if ($user->User_Type == Users::ADMIN || $user->User_Type == Users::DB_ADMIN || $userClientRow->Admin == 1) {
                    $userClientRow->User_Type = 'Client Admin';
                    $userClientRow->User_Approval_Value = 100;
                } else if ($user->User_Type == UsersClientList::APPROVER)  {
                    $userClientRow->User_Type = UsersClientList::APPROVER;
                    if ($userClientRow->User_Approval_Value < 2) {
                        $userClientRow->User_Approval_Value = 2;
                    }
                } else if ($user->User_Type == Users::DATA_ENTRY_CLERK) {
                    $userClientRow->User_Type = UsersClientList::USER;
                    $userClientRow->User_Approval_Value = 0;
                } else {
                    $userClientRow->User_Type = $user->User_Type;
                    $userClientRow->User_Approval_Value = 0;
                }
                $userClientRow->save();
            }

            if ($user->User_Type != Users::ADMIN && $user->User_Type != Users::DB_ADMIN && $user->User_Type != Users::DATA_ENTRY_CLERK)  {
                $user->User_Type = "User";
                $user->save();
            }
        }

         $condition = new CDbCriteria();
        $condition->condition = 'images.File_Size = 0';
        $condition->join = 'LEFT JOIN images ON images.Document_ID = t.Document_ID';
        $docs = Documents::model()->findAll($condition);
        foreach ($docs as $doc) {
            $image = $doc->image;
            if ($image instanceof Images && $image->File_Size == 0) {
                $image->File_Size = intval(strlen($image->Img));
                if ($image->validate()) {
                    $image->save();
                }
            }
            unset($image);
        }
        */

        $this->layout='//layouts/home';
		// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'

        //if super device  - authentificate user
        //$device = UsersDevices::getSuperDevInstance($device_cookie,$user_id,$model->timezoneOffset,$model->resolution);

        //reset password
        /*$users = Users::model()->findAll();
        foreach ($users as $user) {
            $user->User_Pwd = md5('111');
            $user->save();
        }*/



        $this->render('index');
        
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
            //var_dump($error); die;
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}

	/**
	 * Displays the contact page
	 */
	public function actionContact()
	{
        $this->redirect('/');
		$model=new ContactForm;
		if(isset($_POST['ContactForm']))
		{
			$model->attributes=$_POST['ContactForm'];
			if($model->validate())
			{
				$name='=?UTF-8?B?'.base64_encode($model->name).'?=';
				$subject='=?UTF-8?B?'.base64_encode($model->subject).'?=';
				$headers="From: $name <{$model->email}>\r\n".
					"Reply-To: {$model->email}\r\n".
					"MIME-Version: 1.0\r\n".
					"Content-Type: text/plain; charset=UTF-8";

				mail(Yii::app()->config->get('ADMIN_EMAIL'),$subject,$model->body,$headers);
				Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}

    /**
     * Verify if have a Company’s W9
     */
    public function actionTestForW9()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['fed_id']) && isset($_POST['company_name']) && isset($_POST['address'])
            && isset($_POST['login']) && isset($_POST['password'])) {
            // set input parameters
            $fed_id = trim($_POST['fed_id']);
            $company_name = trim($_POST['company_name']);
            $address = trim($_POST['address']);
            $login = trim($_POST['login']);
            $password = trim($_POST['password']);

            if (!Yii::app()->user->id) {
                // if user is unauthorized
                $user = Users::model()->with('clients')->findByAttributes(array(
                    'User_Login' => $login,
                    'User_Pwd' => $password,
                    'Active' => Users::ACTIVE,
                ));

                if ($user === null) {
                    // if invalid login and password
                    $result = array(
                        'success' => false,
                        'fed_id' => $fed_id,
                        'company_name' => $company_name,
                        'address' => $address,
                        'error' => 2,
                    );
                } else {
                    // if valid login and password
                    $clients = $user->clients;
                    if (count($clients) > 0) {
                        foreach ($clients as $client) {
                            $result =  Companies::model()->testForW9($fed_id, $company_name, $address, $client->Client_ID);
                            if ($result['success']) {
                                break;
                            }
                        }
                    } else {
                        $result = array(
                            'success' => false,
                            'fed_id' => $fed_id,
                            'company_name' => $company_name,
                            'address' => $address,
                            'error' => 1,
                        );
                    }
                }
            } else {
                // if user is authorized
                $result =  Companies::model()->testForW9($fed_id, $company_name, $address, Yii::app()->user->clientID);
            }
            echo CJSON::encode($result);
        }
    }


    /**
     *
     */
    public function actionProgressbar()
    {
        if (Yii::app()->request->isAjaxRequest ) {

            $result = array(
                'success' => 1,
                'progress' => ProgressBar::returnStatus()
            );

            echo CJSON::encode($result);
        }
    }

    /**
     *
     */
    public function actionGetTextProgressbar()
    {
        if (Yii::app()->request->isAjaxRequest ) {

            $result = array(
                'status' => ProgressBar::returnStatus(),
                'state' => ProgressBar::returnState()
            );

            echo CJSON::encode($result);
        }
    }


    /**
     * Changes display limit of items to be shown on POList APList VendorList PaymentList pages
     */
    public function actionChangeDisplayLimit()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['limiter_checkbox']) ) {
            $bol_checkbox=$_POST['limiter_checkbox'];

            if($bol_checkbox=='true') {

                $_SESSION['limiter']=Aps::DISPLAY_LIMIT;
            } else {
                $_SESSION['limiter']=0;
            }

        }
        echo $_SESSION['limiter'];
    }

    /**
     * Changes display limit of items to be shown on VendorManagement page right side
     */
    public function actionChangeVendorsRightDisplayLimit()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['limiter_checkbox_right'])) {

            if($_POST['limiter_checkbox_right']=='true') {

                $_SESSION['limiter_vendor_right']=Aps::DISPLAY_LIMIT;
            } else {
                $_SESSION['limiter_vendor_righ']=0;
            }

        }
        echo $_SESSION['limiter_vendor_right'];
    }


    /**
     * Changes display limit of items to be shown on VendorManagement page right side
     */
    public function actionChangeVendorsLeftDisplayLimit()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['limiter_checkbox_left'])) {

            if($_POST['limiter_checkbox_left']=='true') {

                $_SESSION['limiter_vendor_left']=Aps::DISPLAY_LIMIT;
            } else {
                $_SESSION['limiter_vendor_left']=0;
            }

        }
        echo $_SESSION['limiter_vendor_left'];
    }

        /**
	 * Displays the login page
	 */
	public function actionLogin()
	{
        $this->layout='//layouts/home';
		$model = new LoginForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='loginform')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LoginForm']))
		{
            $model->attributes=$_POST['LoginForm'];

            $user = Users::model()->findByAttributes(array(
                'User_Login'=>$model->username
            ));
            $user_id = $user->User_ID;
            $user_settings = UsersSettings::model()->findByAttributes(array(
                'User_ID'=>$user_id
            ));


			// validate user input and redirect to the previous page if valid
			if($model->validate()) {
                    //
                    //check if current device exists in  list
                if($user_settings->Use_Device_Checking) {

                    $device_cookie = $_COOKIE['devise_hash'];
                    $device = UsersDevices::getSmartDevInstance($device_cookie,$user_id,$model->timezoneOffset,$model->resolution);

                    if (!$device && !isset($_POST['answers']) ) {
                        //redirecting to extra login form
                        $user_questions = UsersQuestions::getUserQuestions($user_id);
                        $this->render('index',
                            array(
                                'model'=>$model,
                                'showextraloginmodal' => true,
                                'users_questions'=> $user_questions
                            )
                        );
                        die;

                    } else if (!$device && isset($_POST['answers'])) {

                        $check_answers = UsersAnswers::CheckAnswers($_POST['answers'],$user_id);

                        if ($check_answers) {
                            UsersDevices::addCurrentDeviceToList($user_id, $model->timezoneOffset, $model->resolution);
                        } else {
                            //redirecting to extra login form again
                            $user_questions =   UsersQuestions::getUserQuestions($user_id);
                            $this->render('index',
                                array(
                                    'model'=>$model,
                                    'showextraloginmodal' => true,
                                    'users_questions'=> $user_questions,
                                    'answers_errors'=>"Some answers doesn't match with the previously saved answers.",
                                )
                            );
                            die;
                        }

                    }
                }
            }

            if ($model->login()) {
                    Yii::app()->request->cookies['devise_hash'] = new CHttpCookie('devise_hash', UsersDevices::getDevHash($model->resolution));
                    UsersDevices::updateLastLoggedTime($user_id,$model->timezoneOffset,$model->resolution);
                    //Yii::app()->request->cookies['devise_hash'] = new CHttpCookie('devise_hash', UsersDevices::getDevHash($model->resolution));
                    $this->redirect(Yii::app()->user->returnUrl);
            }
        }


        $this->login_model = $model;
		// display the login form
		$this->render('index',
            array(
                'model'=>$model,
                'showloginmodal' => true,
                'devise_hash'=>UsersDevices::getDevHash($model->resolution)
            )
        );
	}

    /**
     * Displays the login page
     */
    public function actionShowLogin()
    {
        $this->render('index',
            array(
                'model'=>$model,
                'showloginmodal' => true,
            )
        );
    }

    /**
     * Register action
     */
    public function actionRegister()
    {
        $answers = '';
        $this->layout='//layouts/home';
        if (isset($_POST['RegisterForm']) && $_POST['RegisterForm']['Client_ID'] === '0') {
            $model = new RegisterForm('newClientScenario');
        } else {
            $model = new RegisterForm;
        }

        // if it is ajax validation request
        if (isset($_POST['ajax']) && $_POST['ajax']==='users-register-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        // collect user input data
        if(isset($_POST['RegisterForm']))
        {
            //check register form
            $model->attributes=$_POST['RegisterForm'];

            $answers = $_POST['answers'];

            if($model->validate()) {
                // begin transaction

                $transaction = Yii::app()->db->beginTransaction();
                try {
                    // create objects

                    $person = new Persons;
                    $client = new Clients;
                    $project = new Projects('RegisterScenario');
                    $company = new Companies;
                    $user = new Users;
                    $person_adress = new Addresses;
                    $companyAdreses = new CompanyAddresses;
                    $usersClientList = new UsersClientList;
                    $usersProjectList = new UsersProjectList;
                    $usersToApprove = new UsersToApprove;
                    $personAdreses = new PersonAddresses;
                    $userSettings = new UsersSettings('newClientScenario');

                    // save person address
                    $person_adress->save();

                    // create person
                    $person->First_Name = $_POST['RegisterForm']['First_Name'];
                    $person->Last_Name = $_POST['RegisterForm']['Last_Name'];
                    $person->Email = $_POST['RegisterForm']['Email'];
                    $person->Email_Confirmation = $_POST['RegisterForm']['Email_Confirmation'];

                    $person->save();

                    // person-address relationship
                    $personAdreses->Address_ID = $person_adress->Address_ID;
                    $personAdreses->Person_ID = $person->Person_ID;
                    $personAdreses->save();

                    if ($_POST['RegisterForm']['Client_ID'] === '0') {
                        // if creates new company

                        //create new company
                        $company->Company_Name = $_POST['RegisterForm']['Company_Name'];
                        $company->Company_Fed_ID = $_POST['RegisterForm']['Fed_ID'];
                        $company->save();

                        // create company address
                        $company_adress = new Addresses;
                        $company_adress->Address1 = $_POST['RegisterForm']['Address1'];
                        $company_adress->City = $_POST['RegisterForm']['City'];
                        $company_adress->State = $_POST['RegisterForm']['State'];
                        $company_adress->ZIP = $_POST['RegisterForm']['ZIP'];
                        $company_adress->save();

                        // company-address relationship
                        $companyAdreses->Company_ID = $company->Company_ID;
                        $companyAdreses->Address_ID = $company_adress->Address_ID;
                        $companyAdreses->save();

                        // create new client
                        $client->Company_ID = $company->Company_ID;
                        $client->Client_Type = 0;
                        $client->Client_Number = 0;
                        $client->save();

                        //create client project
                        $project->Client_ID = $client->Client_ID;
                        $project->Project_Name = "Corporate";
                        $project->Project_Description = "Description of the Project";
                        $project->PO_Starting_Number = Projects::DEFAULT_PO_STARTING_NUMBER;
                        $project->Ck_Req_Starting_Numb = Projects::DEFAULT_CKRQ_STARTING_NUMBER;
                        $project->save();

                        // set user's default client
                        $user->Default_Project = $project->Project_ID;
                    } else if ($_POST['RegisterForm']['Client_ID'] === '-1') {
                        //create new company



                        // create new client
                        $client->Company_ID = -1;
                        $client->Client_Type = 2; //single user
                        $client->Client_Number = 0;
                        $client->save();

                        //create client project
                        $project->Client_ID = $client->Client_ID;
                        $project->Project_Name = "Individual";
                        $project->Project_Description = "Description of the Project";
                        $project->PO_Starting_Number = Projects::DEFAULT_PO_STARTING_NUMBER;
                        $project->Ck_Req_Starting_Numb = Projects::DEFAULT_CKRQ_STARTING_NUMBER;
                        $project->save();

                        // set user's default client
                        $user->Default_Project = $project->Project_ID;
                    }

                    else {
                        $user->Default_Project = 0;
                    }

                    // create user
                    $user->User_Login = $_POST['RegisterForm']['User_Login'];
                    $user->User_Pwd = md5('temp100');
                    $user->Person_ID = $person->Person_ID;
                    $user->save();



               if ($_POST['RegisterForm']['Client_ID'] === '0' || $_POST['RegisterForm']['Client_ID'] === '-1') {
                        // user-client relationship
                        $usersClientList->User_ID = $user->User_ID;
                        $usersClientList->Client_ID = $client->Client_ID;
                        $usersClientList->User_Type = UsersClientList::CLIENT_ADMIN;
                        $usersClientList->User_Approval_Value = Aps::APPROVED;
                        $usersClientList->save();
                        //die("inside cliennID===0 after userclientlist save save");
                        $usersToApprove->New_Client = UsersToApprove::NEW_CLIENT;
                        $usersToApprove->Client_ID = $client->Client_ID;

                        // user-project relationship
                        $usersProjectList->Client_ID = $client->Client_ID;
                        $usersProjectList->User_ID = $user->User_ID;
                        $usersProjectList->Project_ID = $project->Project_ID;
                        $usersProjectList->save();
                    } else {
                        $usersToApprove->Client_ID = $_POST['RegisterForm']['Client_ID'];
                        $usersToApprove->New_Client = UsersToApprove::OLD_CLIENT;

                    }

                    $usersToApprove->User_ID = $user->User_ID;
                    $usersToApprove->save();

                    //writing to database users answers on secret questions
                    if (UsersAnswers::setUserQuestionsAnswers($user->User_ID,$answers)) {
                        //adding current device to trusted list
                        UsersDevices::addCurrentDeviceToList($user->User_ID,'empty','empty');
                        //setting user settings to check device before login
                        $userSettings->Use_Device_Checking = 1;
                    }

                    $userSettings->User_ID = $user->User_ID;


                    $userSettings->save();
                    //  die("before  userSettings save");
                    Mail::sendUserRegistrationRequest();
                    Yii::app()->user->setFlash('success', "Registration request has been sent. You will be notified about the progress by email.");

                    $transaction->commit();

                    $this->redirect('/');
                } catch(Exception $e) {
                    $transaction->rollback();//die("exeption!");
                }
            }
        }

        $this->register_model = $model;
        // display the login form
        $this->render('index',array(
                'model'=>$model,
                'showregistermodal' => true,
                'answers'=> $answers
            )
        );
    }

    /**
     * Create and send new password action
     */
    public function actionForgotPassword()
    {
        $this->layout='//layouts/home';

        //create form model
        $model = new ForgotPasswordForm;

        if(isset($_POST['ForgotPasswordForm']))
        {
            // check form
            $model->attributes=$_POST['ForgotPasswordForm'];
            if($model->validate()) {

                // create and send new password
                if (trim($model->username) != '') {
                    $user = Users::model()->with('person')->findByAttributes(array(
                        'User_Login' => $model->username,
                    ));

                    $email = $user->person->Email;
                    $password = Helper::generatePassword();
                    $user->User_Pwd = md5($password);
                    $user->save();
                    $emailSuccess = Mail::sendNewPassword($email, $user->User_Login, $password, $user->person->First_Name, $user->person->Last_Name);
                } elseif (trim($model->email) != '') {
                    $persons = Persons::model()->with('user')->findAllByAttributes(array(
                        'Email' => $model->email,
                    ));

                    foreach($persons as $person) {
                        $user = $person->user;
                        $email = $person->Email;
                        $password = Helper::generatePassword();
                        $user->User_Pwd = md5($password);
                        $user->save();
                        $emailSuccess = Mail::sendNewPassword($email, $user->User_Login, $password, $person->First_Name, $person->Last_Name);
                    }
                }

                Yii::app()->user->setFlash('success', "Login and password have been sent to your email!");
                $this->redirect('/');
            }
        }

        $this->forgot_password_model = $model;

        $this->render('index',array(
                'model'=>$model,
                'showforgotpasswordmodal' => true,
            )
        );
    }

    /**
     * Get user's clients list to change client box
     */
    public function actionGetUserClientsList()
    {
        if (Yii::app()->request->isAjaxRequest && isset(Yii::app()->user->clientID)) {
            $user = Users::model()->with('clients')->findByPk(Yii::app()->user->userID);
            $clients = $user->clients;

            // get clients options
            $clientsHtml = $this->renderPartial('user_clients', array(
                'clients' => $clients,
                'currentClient' => Yii::app()->user->clientID,
            ), true);

            if (isset($_POST['client_id'])) {
                $clientId = intval($_POST['client_id']);
            } else {
                $clientId = Yii::app()->user->clientID;
            }

            $userClientRow = UsersClientList::model()->findByAttributes(array(
                'User_ID' => Yii::app()->user->userID,
                'Client_ID' => $clientId,
            ));

            //get projects
            if ($userClientRow && $userClientRow->hasClientAdminPrivileges()) {
                $projects = Projects::model()->findAllByAttributes(array(
                    'Client_ID' => $clientId,
                ));
            } else if ($clientId == 0 && count($clients) > 0) {
                $condition = new CDbCriteria();
                $condition->condition = "users_project_list.User_ID = '" . Yii::app()->user->userID . "'";
                $condition->addCondition("t.Client_ID = '" . $clients[0]->Client_ID . "'");
                $condition->join = "LEFT JOIN users_project_list ON users_project_list.Project_ID = t.Project_ID";
                $projects = Projects::model()->findAll($condition);
            } else {
                $condition = new CDbCriteria();
                $condition->condition = "users_project_list.User_ID = '" . Yii::app()->user->userID . "'";
                $condition->addCondition("t.Client_ID = '" . $clientId . "'");
                $condition->join = "LEFT JOIN users_project_list ON users_project_list.Project_ID = t.Project_ID";
                $projects = Projects::model()->findAll($condition);
            }


            $showAllProjects = false;
            if ($userClientRow && ($userClientRow->hasClientAdminPrivileges() || Yii::app()->user->userType == UsersClientList::APPROVER) && !isset($_POST['do_not_show_all_projects'])) {
                $showAllProjects = true;
            }

            // get projects options
            $projectsHtml = $this->renderPartial('user_client_projects', array(
                'projects' => $projects,
                'currentProject' => Yii::app()->user->projectID,
                'showAllProjects' => $showAllProjects,
            ), true);

            $result = array(
                'clients' => $clientsHtml,
                'projects' => $projectsHtml,
            );

            echo CJSON::encode($result);
        }
    }


    /**
     * Change client action
     */
    public function actionChangeClient()
    {
        if (isset($_GET['change_client_id']) && isset($_GET['change_project_id']) && isset(Yii::app()->user->clientID)) {
            $change_client_id = intval($_GET['change_client_id']);
            $change_project_id = trim($_GET['change_project_id']);

            // check input data
            if ($change_client_id == 0 || (!is_numeric($change_project_id) && $change_project_id != 'all')) {
                $this->redirect($_SERVER['HTTP_REFERER']);
            }

            $user = Users::model()->with('clients')->findByPk(Yii::app()->user->userID);
            $clients = $user->clients;

            // change client
            foreach($clients as $client) {
                if ($client->Client_ID == $change_client_id) {
                    $projectAssigned = false;
                    $userClientRow = UsersClientList::model()->findByAttributes(array(
                        'User_ID' => Yii::app()->user->userID,
                        'Client_ID' => $client->Client_ID,
                    ));

                    if ($change_project_id == 'all' && ($userClientRow->hasClientAdminPrivileges()|| Yii::app()->user->userType == UsersClientList::APPROVER)) {
                        $_SESSION['last_client'] = $change_client_id;
                        $_SESSION['last_project'] = 'all';
                        $projectAssigned = true;
                    } else {
                        //get projects
                        if ($userClientRow->hasClientAdminPrivileges()) {
                            $projects = Projects::model()->findAllByAttributes(array(
                                'Client_ID' => $client->Client_ID,
                            ));
                        } else {
                            $condition = new CDbCriteria();
                            $condition->condition = "users_project_list.User_ID = '" . Yii::app()->user->userID . "'";
                            $condition->addCondition("t.Client_ID = '" . $client->Client_ID . "'");
                            $condition->join = "LEFT JOIN users_project_list ON users_project_list.Project_ID = t.Project_ID";
                            $projects = Projects::model()->findAll($condition);
                        }


                        foreach($projects as $project) {
                            if ($project->Project_ID == $change_project_id) {
                                $_SESSION['last_client'] = $change_client_id;
                                $_SESSION['last_project'] = $project->Project_ID;
                                $projectAssigned = true;
                            }
                        }
                    }

             //       if ($projectAssigned ) {
                        $login = new LoginForm();
                        $login->relogin();

                        $_SESSION['pm_to_review'] = array();
                        $_SESSION['ap_to_review'] = array();
                        $_SESSION['w9_to_review'] = array();
                        $_SESSION['po_to_review'] = array();

                        Yii::app()->user->setFlash('success', "Company & Project has been changed.");
                    }
               // }
            }
        }
        $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function actionGetUserInfo()
    {
        if (Yii::app()->request->isAjaxRequest && isset(Yii::app()->user->userID)) {
            $user = Users::model()->with('person')->findByPk(Yii::app()->user->userID);
            $client = Clients::model()->with('company')->findByPk(Yii::app()->user->clientID);
            $project = Projects::model()->findByPk(Yii::app()->user->projectID);

            $userClientRow = UsersClientList::model()->findByAttributes(array(
                'User_ID' => Yii::app()->user->userID,
                'Client_ID' => Yii::app()->user->clientID,
            ));

            $this->renderPartial('user_info', array(
                'client' => $client,
                'user' => $user,
                'project' => $project,
                'userClientRow' => $userClientRow,
                'projectId' => Yii::app()->user->projectID,
                'clientId' => Yii::app()->user->clientID,
            ));
        }
    }

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
        // get user and settings
        $user = Users::model()->with('settings')->findByPk(Yii::app()->user->userID);
        $settings = $user->settings;

        if ($settings->Default_Project == 0) {
            if (Yii::app()->user->projectID == 'all') {
                $condition = new CDbCriteria();
                $condition->join = "LEFT JOIN users_project_list ON users_project_list.Project_ID = t.Project_ID";
                $condition->condition = "users_project_list.User_ID = '" . Yii::app()->user->userID . "'";
                $condition->addCondition("users_project_list.Client_ID = '" . Yii::app()->user->clientID . "'");
                $project = Projects::model()->find($condition);
                $user->Default_Project = $project->Project_ID;
            } else {
                $user->Default_Project = Yii::app()->user->projectID;
            }
        } else {
            $condition = new CDbCriteria();
            $condition->condition = "users_project_list.User_ID = '" . Yii::app()->user->userID . "'";
            $condition->addCondition("users_project_list.Project_ID = '" . $settings->Default_Project . "'");
            $condition->join = "LEFT JOIN users_project_list ON users_project_list.Project_ID = t.Project_ID";
            $project = Projects::model()->find($condition);
            if ($project) {
                $user->Default_Project = $settings->Default_Project;
            } else {
                $settings->Default_Project = 0;

                $condition = new CDbCriteria();
                $condition->condition = "users_project_list.User_ID = '" . Yii::app()->user->userID . "'";
                $condition->join = "LEFT JOIN users_project_list ON users_project_list.Project_ID = t.Project_ID";
                $project = Projects::model()->find($condition);
                if ($project) {
                    $user->Default_Project = $project->Project_ID;
                } else {
                    $user->Default_Project = 0;
                }
            }
        }
        $settings->save();
        $user->save();

        UserLog::createLogRecord(Yii::app()->user->userID, '', 0, 1);
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}

    /**
     * Log user out and redirect them to the same page but with another user credentials
     */
    public function actionRelogin()
    {
        // get user and settings
        $user = Users::model()->with('settings')->findByPk(Yii::app()->user->userID);
        $settings = $user->settings;

        if ($settings->Default_Project == 0) {
            if (Yii::app()->user->projectID == 'all') {
                $condition = new CDbCriteria();
                $condition->join = "LEFT JOIN users_project_list ON users_project_list.Project_ID = t.Project_ID";
                $condition->condition = "users_project_list.User_ID = '" . Yii::app()->user->userID . "'";
                $condition->addCondition("users_project_list.Client_ID = '" . Yii::app()->user->clientID . "'");
                $project = Projects::model()->find($condition);
                $user->Default_Project = $project->Project_ID;
            } else {
                $user->Default_Project = Yii::app()->user->projectID;
            }
        } else {
            $condition = new CDbCriteria();
            $condition->condition = "users_project_list.User_ID = '" . Yii::app()->user->userID . "'";
            $condition->addCondition("users_project_list.Project_ID = '" . $settings->Default_Project . "'");
            $condition->join = "LEFT JOIN users_project_list ON users_project_list.Project_ID = t.Project_ID";
            $project = Projects::model()->find($condition);
            if ($project) {
                $user->Default_Project = $settings->Default_Project;
            } else {
                $settings->Default_Project = 0;

                $condition = new CDbCriteria();
                $condition->condition = "users_project_list.User_ID = '" . Yii::app()->user->userID . "'";
                $condition->join = "LEFT JOIN users_project_list ON users_project_list.Project_ID = t.Project_ID";
                $project = Projects::model()->find($condition);
                if ($project) {
                    $user->Default_Project = $project->Project_ID;
                } else {
                    $user->Default_Project = 0;
                }
            }
        }
        $settings->save();
        $user->save();

        UserLog::createLogRecord(Yii::app()->user->userID, '', 0, 1);
        $redirect_url = $_SESSION['url_after_relogin'] ? $_SESSION['url_after_relogin'] : '/site/login';
        Yii::app()->user->logout();


        $this->redirect($redirect_url);
    }

    public function actionCollectClientInfo () {

        $cs = Yii::app()->getClientScript();
        $cs->registerScriptFile('https://www.google.com/jsapi');
        //1) define arrays
/*        $client = Clients::model()->with('company.adreses')->findByPk($cli_id);

        $users_client_list = UsersClientList::getClientsUsersArray($cli_id);

        $documents_count = Clients::ClientDocumentsCount($cli_id,$projects_array);
        $files_count = Clients::ClientFilesCount($cli_id,$projects_array);
        $U_files_count = Clients::ClientFilesCount($cli_id,$projects_array,'U');
        $G_files_count = Clients::ClientFilesCount($cli_id,$projects_array,'G');
        $vendors = count(Vendors::getClientVendorsShortcutList($cli_id));
        $coas = count(Coa::getClientsCOAs($cli_id,'all'));
        $notes = Notes::getClientsNotes($cli_id,$projects_array);
*/
        //$result = array(array("DocType","Quantity"));
        $result = array();
        array_push($result,array('DocType',1));
        $documents = Clients::ClientDocumentsCount(Yii::app()->user->clientID);
        foreach ($documents as $doc_type ){
            array_push($result,array($doc_type['Document_Type'], intval($doc_type['Total'])));
            //$result[] = array(strval($doc_type['Document_Type']) , intval($doc_type['Total'])) ;
            //$result[$doc_type['Document_Type'].'('.$doc_type['Total'].')'] = $doc_type['Total'];
        }
     //   $documents = array();
        //2) render views with this data
        $html = $this->renderPartial('charts',array(
            'documents' => $result
        ),true);

        echo $html;


    }

}
