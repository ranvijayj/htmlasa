<?php

class SupportrequestsController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

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
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index','view','Request'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('create','update'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','delete','GetRequestInfo','MarkSolved'),
				'users'=>array('admin','db_admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new SupportRequests;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['SupportRequests']))
		{
			$model->attributes=$_POST['SupportRequests'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->Request_ID));
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['SupportRequests']))
		{
			$model->attributes=$_POST['SupportRequests'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->Request_ID));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		$this->loadModel($id)->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('SupportRequests');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new SupportRequests('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['SupportRequests']))
			$model->attributes=$_GET['SupportRequests'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

    public function actionAdd()
	{

        $model=new SupportRequests;

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if(isset($_POST['support_requests_form']))
        {
            $model->attributes=$_POST['support_requests_form'];
            if($model->save())
                $this->redirect(array('view','id'=>$model->Request_ID));
        }

        $this->render('create',array(
            'model'=>$model,
        ));
	}
    public function actionRequest()
	{
        if (Yii::app()->request->isAjaxRequest && isset($_POST['requesttype']) && isset($_POST['login']) ) {

            //collect user info
            $user = Users::model()->findByAttributes(array(
                'User_Login'=>strval($_POST['login']),
            ));
            if ($user) {
                $user_id = $user->User_ID;
                //collect device info
                $dev_id =  UsersDevices::addCurrentDeviceToList($user_id, intval($_POST['timezone']), strval($_POST['resolution']),true);
                //write to base
                $sup_req = SupportRequests::model()->findByAttributes(array(
                        'User_Login'=>strval($_POST['login']),
                        'Problem_Status'=>'W',
                        'User_Device_ID'=>$dev_id
                    )
                );

                if (!$sup_req) $sup_req = new SupportRequests();

                $sup_req->User_Login =  strval($_POST['login']);
                $sup_req->User_Email =  $user->person->Email;
                $sup_req->User_Phone =  $user->person->Mobile_Phone;

                $sup_req->User_Device_ID = $dev_id;
                $sup_req->Problem_Category =  'Device unknown login problem';

                $sup_req->save();


                $this->renderPartial('view',array(
                    'request_id'=>$sup_req->Request_ID,
                ));


            }
        }
	}


    public function actionGetRequestInfo()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['request_id']) ) {


            //get support_request info
            $sup_req = SupportRequests::model()->findByPk(intval($_POST['request_id']));


            //get user questions
            $user = Users::model()->findByAttributes(array(
                'User_Login'=>strval($_POST['login']),
            ));
            $user_id = $user->User_ID;
            $users_questions =   UsersQuestions::getUserQuestions($user_id);

            $users_settings = UsersSettings::model()->findByAttributes(array(
                'User_ID'=>$user_id
            ));


            $device= UsersDevices::model()->findByAttributes(array(
                'Device_ID'=>$sup_req->User_Device_ID,
                'Remote_Login'=>1
            ));

            //return json to js

            $this->renderPartial('admin_side_view',array(
                'sup_req'=>$sup_req,
                'users_questions'=>$users_questions,
                'device'=>$device,
                'users_settings'=>$users_settings,
                'user_id'=>$user_id
            ));


        }

    }

    public function actionMarkSolved()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['request_id']) ) {


            //get support_request info
            $sup_req = SupportRequests::model()->findByPk(intval($_POST['request_id']));
            $sup_req->Problem_Status = 'R';
            $sup_req->save();



        }

    }


	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return SupportRequests the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=SupportRequests::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param SupportRequests $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='support-requests-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
