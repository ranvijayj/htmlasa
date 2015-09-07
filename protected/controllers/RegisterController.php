<?php

class RegisterController extends Controller
{
    public $layout='//layouts/home';
    public $register_model_as_client_admin = false;
    public $company = false;

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
                'actions'=>array('index'),
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
	public function actionIndex($auth_url)
	{
        $company = Companies::model()->with('client')->findByAttributes(array(
            'Auth_Url' => $auth_url,
        ));

        // check company
        if (!$company) {
            throw new CHttpException('404','Unable to resolve the request "register/' . $auth_url . '". ');
        }

        // check existing of client-admins for this company
        $condition = UsersClientList::getClientAdminCondition($company->client->Client_ID);
        $userClientList = UsersClientList::model()->find($condition);

        if ($userClientList) {
            $company->Auth_Code = NULL;
            $company->Auth_Url = NULL;
            $company->save();
            Yii::app()->user->setFlash('success', "This company has admins!");
            $this->redirect('/');
        }

        $model = new RegisterAsClientAdminForm;

        // collect user input data
        if(isset($_POST['RegisterAsClientAdminForm']))
        {
            //check register form
            $model->attributes=$_POST['RegisterAsClientAdminForm'];
            $model->Client_ID = $company->client->Client_ID;
            if($model->validate()) {
                // begin transaction
                $transaction = Yii::app()->db->beginTransaction();
                try {
                    // create objects
                    $person = new Persons;
                    $user = new Users;
                    $person_adress = new Addresses;
                    $usersClientList = new UsersClientList;
                    $usersProjectList = new UsersProjectList;
                    $personAdreses = new PersonAddresses;
                    $userSettings = new UsersSettings;
                    $project = Projects::model()->findByAttributes(array(
                        'Client_ID' => $company->client->Client_ID,
                    ));

                    // save person address
                    $person_adress->save();

                    // create person
                    $person->First_Name = $_POST['RegisterAsClientAdminForm']['First_Name'];
                    $person->Last_Name = $_POST['RegisterAsClientAdminForm']['Last_Name'];
                    $person->Email = $_POST['RegisterAsClientAdminForm']['Email'];
                    $person->save();

                    // person-address relationship
                    $personAdreses->Address_ID = $person_adress->Address_ID;
                    $personAdreses->Person_ID = $person->Person_ID;
                    $personAdreses->save();

                    $user->Default_Project = $project->Project_ID;

                    $password = Helper::generatePassword();

                    // create user
                    $user->User_Login = $_POST['RegisterAsClientAdminForm']['User_Login'];
                    $user->User_Pwd = md5($password);
                    $user->Person_ID = $person->Person_ID;
                    $user->Active = Users::ACTIVE;
                    $user->save();

                    // user-client relationship
                    $usersClientList->User_ID = $user->User_ID;
                    $usersClientList->Client_ID = $company->client->Client_ID;
                    $usersClientList->User_Type = UsersClientList::CLIENT_ADMIN;
                    $usersClientList->User_Approval_Value = Aps::APPROVED;
                    $usersClientList->save();

                    // user-project relationship
                    $usersProjectList->Client_ID = $company->client->Client_ID;
                    $usersProjectList->User_ID = $user->User_ID;
                    $usersProjectList->Project_ID = $project->Project_ID;
                    $usersProjectList->save();

                    $userSettings->User_ID = $user->User_ID;
                    $userSettings->save();

                    $company->Auth_Code = NULL;
                    $company->Auth_Url = NULL;
                    $company->save();

                    ClientServiceSettings::addClientServiceSettings($company->client->Client_ID, true);

                    Mail::sendRegistrationMail($user->person->Email, $user->User_Login,$password, $user->person->First_Name, $user->person->Last_Name, $company->Company_Name);
                    Yii::app()->user->setFlash('success', "You have been successfully registered. Login and password have been sent to your email.");
                    $transaction->commit();
                    $this->redirect('/');
                } catch(Exception $e) {
                    $transaction->rollback();
                }
            }
        }

        $this->register_model_as_client_admin = $model;
        $this->company = $company->Company_Name;
        // display the login form
        $this->render('index',array(
                'showregistermodal' => true,
            )
        );
	}
}