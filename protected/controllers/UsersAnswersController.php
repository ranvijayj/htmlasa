<?php

class UsersAnswersController extends Controller
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
				'actions'=>array('index','view'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('create','update','setanswers','GetEmails'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','delete','CheckAnswer'),
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
		$model=new UsersAnswers;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['UsersAnswers']))
		{
			$model->attributes=$_POST['UsersAnswers'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->Answer_ID));
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

		if(isset($_POST['UsersAnswers']))
		{
			$model->attributes=$_POST['UsersAnswers'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->Answer_ID));
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
		$dataProvider=new CActiveDataProvider('UsersAnswers');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new UsersAnswers('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['UsersAnswers']))
			$model->attributes=$_GET['UsersAnswers'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return UsersAnswers the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=UsersAnswers::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param UsersAnswers $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='users-answers-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}


    public function actionSetanswers() {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['answers']) && isset($_POST['questions']) ) {
            $user_id = Yii::app()->user->userID;

            $answers =UsersAnswers::model()->findAllByAttributes(array(
                'User_ID'=>$user_id
            ));

            if ($answers) {
                foreach ($answers as $answer)
                $answer->delete();
            }

            foreach ($_POST['questions'] as $key=>$value ) {
                $quest = UsersQuestions::model()->findByAttributes(array(
                    'Text'=> $value
                ));

                $answer = new UsersAnswers();
                $answer->Question_ID = $quest->Question_ID;
                $answer->User_ID = $user_id;
                $answer->Hashed_Answer = sha1(strval($_POST['answers'][$key]));
                $answer->Hint = UsersAnswers::makeHint(strval($_POST['answers'][$key]));
                $answer->save();
            }

            $users_settings = UsersSettings::model()->findByAttributes(array(
                'User_ID'=>$user_id
            ));
            $users_settings->Use_Device_Checking = 1;
            $users_settings->scenario = 'update';
            $users_settings->save();

            echo "success";

        }
    }

    public function actionCheckAnswer() {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['question_id']) && isset($_POST['answer']) && isset($_POST['user_id']) ) {

            $user_id = intval($_POST['user_id']);
            $question_id = intval($_POST['question_id']);


            $saved_answer =UsersAnswers::model()->findByAttributes(array(
                'User_ID'=>$user_id,
                'Question_ID'=>$question_id
            ));

            if ($saved_answer->Hashed_Answer == sha1(strval($_POST['answer']))) {
                echo 1;
            } else {
                echo 0;
            }
        }
    }

    public function actionGetEmails()
    {
        if (Yii::app()->request->isAjaxRequest ) {
            $search_string= strval($_POST['search_string']);


            //$sql='select distinct Email_Address from emails where Client_ID='.Yii::app()->user->clientID . ' and Project_ID='.Yii::app()->user->projectID.' and User_ID='.Yii::app()->user->userID;
            $sql='select distinct Email_Address from emails where User_ID='.Yii::app()->user->userID;

            if($search_string !='') {
                $sql.=' and Email_Address like("'.$search_string.'%")';
            }

            $list= Yii::app()->db->createCommand($sql)->queryAll();

            foreach ($list as $items){
                $result[]= $items["Email_Address"];
            }

            echo CJSON::encode($result);
        }
    }




}
