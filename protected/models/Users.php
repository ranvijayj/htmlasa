<?php

/**
 * This is the model class for table "users".
 *
 * The followings are the available columns in table 'users':
 * @property integer $User_ID
 * @property integer $Default_Project
 * @property string $User_Login
 * @property string $User_Pwd
 * @property integer $Person_ID
 * @property string $User_Icon
 * @property integer $Active
 * @property string $User_Type
 * @property string $Last_Login
 * @property string $Last_IP
 */
class Users extends CActiveRecord
{
    /**
     * User types
     */
    const USER = 'User';
    const DB_ADMIN = 'DB Admin';
    const DATA_ENTRY_CLERK = 'Data Entry Clerk';
    const ADMIN = 'Admin';
    const SINGLE_USER = 'Single User';

    /**
     * User active values
     */
    const ACTIVE = 1;
    const NOT_ACTIVE = 0;

    const NEED_NOTIFICATION = 1;

    /**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'users';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Default_Project, User_Login, User_Pwd', 'required'),
			array('Default_Project, Person_ID, Active', 'numerical', 'integerOnly'=>true),
			array('User_Login', 'length', 'max'=>30),
            array('User_Login', 'unique'),
			array('User_Pwd', 'length', 'max'=>40),
			array('Last_IP', 'length', 'max'=>15),
            array('Last_Login', 'check_date'),
			array('User_Icon, Last_Login, User_Type', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('User_ID, Default_Project, User_Login, User_Pwd, Person_ID, User_Icon, Active, User_Type, Last_Login, Last_IP, person', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array (
            'person'=>array(self::BELONGS_TO, 'Persons', 'Person_ID', 'with' => 'adresses'),
            'clients'=>array(self::MANY_MANY, 'Clients', 'users_client_list(User_ID, Client_ID)', 'with' => 'company', 'condition' => "Client_Status = '1'"),
            'projects'=>array(self::MANY_MANY, 'Projects', 'users_project_list(User_ID, Project_ID)'),
            'events'=>array(self::HAS_MANY, 'UserLog', 'User_ID'),
            'settings'=>array(self::HAS_ONE, 'UsersSettings', 'User_ID'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'User_ID' => 'User ID',
			'Default_Project' => 'Default Project',
			'User_Login' => 'Login',
			'User_Pwd' => 'User Pwd',
			'Person_ID' => 'Person',
			'User_Icon' => 'User Icon',
			'Active' => 'Active',
			'User_Type' => 'User Type',
			'Last_Login' => 'Last Login',
			'Last_IP' => 'Last Ip',
		);
	}

    /**
     * Get Authenticate Info
     * @param $login
     * @param $password
     * @return CActiveRecord|null authorization info
     */
    public function getAuthenticateInfo($login, $password,$device_hash)
    {
        $user = $this->with('person')->find('User_Login=:login AND Active=' . self::ACTIVE,
            array(':login'=>$login));
        if ($user) {
            if ($user->User_Pwd == md5($password)) {
                return $user;
            } else {
                Mail::sendFailedLoginAttempt($user->person->Email, $login, $password, $user->person->First_Name, $user->person->Last_Name);
                return null;
            }
        }
    }

    /**
     * Returns native user type
     * @param $uid
     * @return array|mixed|null
     */
    public  function  getUserNativeType()
    {

            return $this->User_Type;

    }

    /**
     * Check Fed ID rule
     */
    public function check_date() {
        if($this->Last_Login != null) {
           if (!preg_match('/^\d{4}\-\d{2}\-\d{2}\s\d{2}\:\d{2}\:\d{2}$/', $this->Last_Login)) {
               $this->addError('Last_Login','The format of Last Login is invalid');
           }
        }
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

        $criteria->compare('First_Name',$this->person, true);
        $criteria->compare('Last_Name',$this->person, true, 'OR');
        $criteria->compare('Email',$this->person, true, 'OR');
        $criteria->compare('Mobile_Phone',$this->person, true, 'OR');
        $criteria->compare('Direct_Phone',$this->person, true, 'OR');
        $criteria->compare('Direct_Fax',$this->person, true, 'OR');
		$criteria->compare('User_ID',$this->User_ID);
		$criteria->compare('Default_Project',$this->Default_Project);
		$criteria->compare('User_Login',$this->User_Login,true);
		$criteria->compare('User_Pwd',$this->User_Pwd,true);
		$criteria->compare('User_Icon',$this->User_Icon,true);
		$criteria->compare('Active',$this->Active);
		$criteria->compare('User_Type',$this->User_Type);
		$criteria->compare('Last_Login',$this->Last_Login,true);
		$criteria->compare('Last_IP',$this->Last_IP,true);
        $criteria->with = array('person');

        $sort = new CSort;
        $sort->attributes = array(
            'person' => array(
                'asc' => 'First_Name, Last_Name, Email',
                'desc' => 'First_Name DESC, Last_Name DESC, Email DESC',
            ),
            '*',
        );

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
            'sort'=>$sort,
            'pagination'=>array(
                'pageSize'=>30,
            ),

        ));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Users the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
