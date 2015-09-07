<?php

/**
 * This is the model class for table "error_log".
 *
 * The followings are the available columns in table 'error_log':
 * @property string $Error_Log_ID
 * @property string $Error_DateTime
 * @property string $Session_ID
 * @property string $User_Login
 * @property string $Application
 * @property string $Error_Category
 * @property string $Error_Msg
 * @property integer $LogLevel_Severity
 */
class ErrorLog extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'error_log';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Error_DateTime, Error_Msg', 'required'),
			array('Session_ID', 'length', 'max'=>50),
            array('Application, Error_Category', 'length', 'max'=>255),
			array('User_Login', 'length', 'max'=>30),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Error_Log_ID, Error_DateTime, Session_ID, User_Login, Application, Error_Category, Error_Msg, LogLevel_Severity', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'Error_Log_ID' => 'Database generated unique Error number',
			'Error_DateTime' => 'Error Date Time',
			'Session_ID' => 'Session',
			'User_Login' => 'User Login',
			'Application' => 'Application',
			'Error_Category' => 'Error Category',
			'Error_Msg' => 'Error Msg',
			'LogLevel_Severity' => 'Log Level Severity',
		);
	}


    /**
     * Creates new record in Errors log
     * @param $error_category
     * @param $msg
     * @param $errLevel
     */
    public static function createLogRecord($error_category, $msg, $errLevel)
    {
        $errorLog = new ErrorLog();
        $errorLog->Error_DateTime = date("Y-m-d H:i:s");
        if (session_id()) {
            $errorLog->Session_ID = session_id();
        }
        if (isset(Yii::app()->user->userID)) {
            $errorLog->User_Login = Yii::app()->user->userID;
        }
        $errorLog->Application = $_SERVER['HTTP_USER_AGENT'];
        $errorLog->Error_Category = $error_category;
        $errorLog->Error_Msg = $msg;
        $errorLog->LogLevel_Severity = $errLevel;
        $errorLog->save();
    }


	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('Error_Log_ID',$this->Error_Log_ID,true);
		$criteria->compare('Error_DateTime',$this->Error_DateTime,true);
		$criteria->compare('Session_ID',$this->Session_ID,true);
		$criteria->compare('User_Login',$this->User_Login,true);
		$criteria->compare('Application',$this->Application,true);
		$criteria->compare('Error_Category',$this->Error_Category,true);
		$criteria->compare('Error_Msg',$this->Error_Msg,true);
		$criteria->compare('LogLevel_Severity',$this->LogLevel_Severity);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ErrorLog the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
