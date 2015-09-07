<?php

/**
 * This is the model class for table "users_client_list".
 *
 * The followings are the available columns in table 'users_client_list':
 * @property integer $User_ID
 * @property integer $Client_ID
 * @property integer User_Type
 * @property integer User_Approval_Value
 */
class UsersClientList extends CActiveRecord
{
    /**
     * User types
     */
    const CLIENT_ADMIN = 'Client Admin';
    const USER = 'User';
    const PROCESSOR = 'Processor';
    const APPROVER = 'Approver';

    /**
     * This array is used to check available user types
     * @var array
     */
    static $availableTypes = array(
        self::USER,
        self::CLIENT_ADMIN,
        self::APPROVER,
        self::PROCESSOR,
    );

    /**
     * User types who has client administration privileges
     */
    static $clientAdmins = array(Users::ADMIN, self::CLIENT_ADMIN, Users::DB_ADMIN);
    static $approvers = array(Users::ADMIN, self::CLIENT_ADMIN, Users::DB_ADMIN, UsersClientList::APPROVER);

    static $approversArray = array();
    static $approversIdValueArray = array();

    /**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'users_client_list';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('User_ID, Client_ID', 'required'),
			array('User_ID, Client_ID, User_Approval_Value', 'numerical', 'integerOnly'=>true),
            array('User_Type', 'length', 'max'=>50),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('User_ID, Client_ID, Admin, User_Approval_Value, User_Type', 'safe', 'on'=>'search'),
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
            'client'=>array(self::BELONGS_TO, 'Clients', 'Client_ID'),
            'user'=>array(self::BELONGS_TO, 'Users', 'User_ID'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'User_ID' => 'User',
			'Client_ID' => 'Client',
            'User_Approval_Value' => 'User Approval Value',
            'User_Type' => 'User Type',
		);
	}

    /**
     * Get next user to approve
     * @param $userApprovalRange
     * @return CActiveRecord
     */
    public static function getNextUserToApprove($userApprovalRange)
    {
        //get next user to approve
        $condition = new CDbCriteria();
        $condition->select = 'User_ID, User_Approval_Value';
        $condition->condition = "t.Client_ID='" . Yii::app()->user->clientID . "'";
        $condition->addCondition("t.User_Approval_Value > '" . $userApprovalRange['user_appr_val'] . "'");
        $condition->order = "t.User_Approval_Value ASC";
        $nextUser = UsersClientList::model()->find($condition);

        //get another users with the same approval value (for caces when there are two or more approvers with the same approval value)
        $condition->addCondition("t.User_Approval_Value = '" . $nextUser->User_Approval_Value . "'");
        $nextUsers = UsersClientList::model()->findAll($condition);

        return $nextUsers;
    }


    /**
     * Returns user who have to approve next. Used for Cue/
     * @param $currentApprovalValue
     * @return array|CActiveRecord|mixed|null
     */
    public static function getUserToApprove($currentApprovalValue)
    {
        //get next user to approve
        $condition = new CDbCriteria();
        $condition->select = 'User_ID, User_Approval_Value';
        $condition->condition = "t.Client_ID='" . Yii::app()->user->clientID . "'";
        $condition->addCondition("t.User_Approval_Value > '" . $currentApprovalValue . "'");
        $condition->order = "t.User_Approval_Value ASC";
        $nextUser = UsersClientList::model()->with('user.person')->find($condition);
        return $nextUser;
    }
    /**
     * Get previous user approval value
     * @param $approvalValue
     * @return CActiveRecord
     */
    public static function getPreviousUserApprovalValue($approvalValue)
    {
        $condition = new CDbCriteria();
        $condition->select = 'User_Approval_Value';
        $condition->condition = "t.Client_ID='" . Yii::app()->user->clientID . "'";
        $condition->addCondition("t.User_Approval_Value < '" . $approvalValue . "'");
        $condition->order = "t.User_Approval_Value DESC";
        $perviosUserApproval = UsersClientList::model()->find($condition);

        return $perviosUserApproval;
    }

    /**
     * Get previous user to approve
     * @param $approvalValue
     * @return CActiveRecord
     */
    public static function getPreviousUserToApprove($approvalValue)
    {
        $condition = new CDbCriteria();
        $condition->select = 'User_ID';
        $condition->condition = "t.Client_ID='" . Yii::app()->user->clientID . "'";
        $condition->addCondition("t.User_Approval_Value < '" . $approvalValue . "'");
        $condition->order = "t.User_Approval_Value DESC";
        $previousUser = UsersClientList::model()->find($condition);

        return $previousUser;
    }

    /**
     * Get client's approvers
     * @return array client's approvers
     */
    public static function getClientApprovers()
    {
        $clientApprovers = array(
            0 => 'Choose',
        );

        $condition = new CDbCriteria();
        $condition->condition = "t.Client_ID='" . Yii::app()->user->clientID . "'";
        $condition->addCondition("t.User_Approval_Value > '" . Aps::READY_FOR_APPROVAL . "'");
        $condition->order = "t.User_Approval_Value DESC";
        $approvers = UsersClientList::model()->with('user.person')->findAll($condition);

        if ($approvers) {
            foreach ($approvers as $approver) {
                $user = $approver->user;
                $person = $user->person;
                $clientApprovers[$user->User_ID] = $person->First_Name . ' ' . $person->Last_Name;
            }
        }

        return $clientApprovers;
    }

    /**
     * Check client for existing of client-admins
     * @param $clientId
     * @return bool
     */
    public static function checkClientForAdmins($clientId) {
        $clientId = intval($clientId);
        $admin = UsersClientList::model()->find(self::getClientAdminCondition($clientId));

        if ($admin) {
            return true;
        }

        return false;
    }

    /**
     * Get client admin condition
     * @param $clientID
     * @return CDbCriteria
     */
    public static function getClientAdminCondition($clientID)
    {
        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN users as u ON u.User_ID = t.User_ID";
        $condition->condition = "u.User_Type = '" . Users::ADMIN . "'";
        $condition->addCondition("u.User_Type = '" . Users::DB_ADMIN . "'", 'OR');
        $condition->addCondition("t.User_Type = '" . self::CLIENT_ADMIN . "'", 'OR');
        $condition->addCondition("t.Client_ID = '" . $clientID . "'");
        return $condition;
    }

    /**
     * Check user for client-admin privileges
     * @return bool
     */
    public function hasClientAdminPrivileges()
    {
        $user = Users::model()->findByPk($this->User_ID);
        if (in_array($user->User_Type, self::$clientAdmins) || $this->User_Type == self::CLIENT_ADMIN) {
            return true;
        }
        return false;
    }

    /**
     * Check user for client-admin privileges
     * @return bool
     */
    public function hasApproverPrivileges()
    {

        if (in_array($this->User_Type, self::$approvers) || $this->User_Type == self::CLIENT_ADMIN) {
            return true;
        }
        return false;
    }


    /**
     * Check user approval value and update it if is not valid
     * @param $userId
     * @param $clientId
     * @param $value
     * @return int
     */
    public static function checkUserApprovalValue($userId, $clientId, $value)
    {
        $value = intval($value);

        if ($value > Aps::APPROVED) {
            $value = Aps::APPROVED;
        } else if ($value < Aps::NOT_READY_FOR_APPROVAL) {
            $value = Aps::NOT_READY_FOR_APPROVAL;
        }

        // get user
        $user = Users::model()->findByPk($userId);
        $userClientRow = UsersClientList::model()->findByAttributes(array(
            'User_ID' => $userId,
            'Client_ID' => $clientId,
        ));

        // if user exist
        if ($user && $userClientRow) {
            if (($userClientRow->User_Type == UsersClientList::APPROVER || $user->User_Type == Users::ADMIN
                    || $user->User_Type == Users::DB_ADMIN || $userClientRow->User_Type == UsersClientList::CLIENT_ADMIN) && $value < 2) {
                $value = 2;
            } elseif (!($userClientRow->User_Type == UsersClientList::APPROVER || $user->User_Type == Users::ADMIN
                    || $user->User_Type == Users::DB_ADMIN|| $userClientRow->User_Type == UsersClientList::CLIENT_ADMIN) && $value > Aps::NOT_READY_FOR_APPROVAL) {
                $value = Aps::NOT_READY_FOR_APPROVAL;
            }
        }

        return $value;
    }


    public static function getUserApprovalValue($userId, $clientId)
    {

        // get user
        $user = Users::model()->findByPk($userId);
        $userClientRow = UsersClientList::model()->findByAttributes(array(
            'User_ID' => $userId,
            'Client_ID' => $clientId,
        ));

        $value = $userClientRow->User_Approval_Value;

        return $value;
    }

    public static function getClientsUsersModels($clientId){

        $usersClientRows = UsersClientList::model()->findAllByAttributes(array(
            'Client_ID'=>$clientId
        ));
        return $usersClientRows;

    }

    public static function getClientsUsersArray($clientId)
    {

        $sql='select distinct(users_client_list.User_ID),users_client_list.User_Type as CL_User_Type,users_client_list.User_Approval_Value,
              users.User_Login,users.User_Type,
              persons.First_Name, persons.Last_Name, persons.Email
              from users_client_list

            left join users on (users.User_ID = users_client_list.User_ID)
            left join persons on (persons.Person_ID = users.Person_ID)

            where users_client_list.Client_ID = '.$clientId.'
            order by users_client_list.User_Approval_Value';

        $list= Yii::app()->db->createCommand($sql)->queryAll();
        return $list;


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

		$criteria->compare('User_ID',$this->User_ID);
		$criteria->compare('Client_ID',$this->Client_ID);
        $criteria->compare('User_Type',$this->User_Type);
        $criteria->compare('User_Approval_Value',$this->User_Approval_Value);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return UsersClientList the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public function changeUserType($new_type) {
        $operation_allowed = true;



        if ($this->User_Type=='Client Admin' && $new_type!='Client Admin' ) {
            $ucl = UsersClientList::model()->findAllByAttributes(array(
               'Client_ID'=>$this->Client_ID,
                'User_Type'=>'Client Admin'
            ));
            if (count($ucl)<=1) $operation_allowed = false;
        }

        if ($operation_allowed) $this->User_Type = $new_type;

    }

    public static function isLastApprover($clientId, $userId)
    {

        $a = 30;
        $sql='select users_client_list.User_ID
              from users_client_list
              where users_client_list.Client_ID = '.$clientId.' and users_client_list.User_ID<>'.$userId.' and users_client_list.User_Approval_Value=100';

        $list= Yii::app()->db->createCommand($sql)->queryAll();

        if (count($list)>=1){
            return false;
        } else {
            return true;
        }

    }

    public static function countFinalApprovers($clientId)
    {

    $sql='select users_client_list.User_ID from users_client_list where users_client_list.Client_ID = '.$clientId.' and users_client_list.User_Approval_Value=100';
    $list= Yii::app()->db->createCommand($sql)->query();
    return count($list);

    }


    public static function getApproversArray ($clientId) {
        $client = Clients::model()->with('company', 'users')->findByPk($clientId);
        $client_users = $client->users;
        $admins_array = array();
        $approvalValue = array();
        $approvers_array = array();


        if ($client_users) {

            //$_SESSION['tabs_to_auto_load']['client_users_list_appr_value'] = array('client_id'=> $client->Client_ID);
            //$_SESSION['admin_active_tab']='us_appr_value';


            foreach ($client_users as $key => $cuser) {

                $uClRow = UsersClientList::model()->findByAttributes(array(
                    'User_ID'=>$cuser->User_ID,
                    'Client_ID'=>$clientId,
                ));

                self::$approversIdValueArray[$cuser->User_ID] = $uClRow->User_Approval_Value;

                $state_appr = $uClRow->hasApproverPrivileges();

                if($state_appr) {
                    $approvers_array[] = array(
                        'user' =>$cuser,
                        'approval_value' =>$uClRow->User_Approval_Value
                    );
                }


            }

            self::$approversArray = $approvers_array;
            usort(self::$approversArray, 'self::sortClientUsersByApprovalValue');
            $approvers_array = self::$approversArray;


        }
        return $approvers_array;
    }

    /**
     * Sort users by approval value
     * @param $a
     * @param $b
     * @return int
     */
    public static function sortClientUsersByApprovalValue($a, $b) {
        if (self::$approversIdValueArray[$a['user']->User_ID] > self::$approversIdValueArray[$b['user']->User_ID]) {
            return -1;
        } else if (self::$approversIdValueArray[$a['user']->User_ID] < self::$approversIdValueArray[$b['user']->User_ID]) {
            return 1;
        }  else {
            return -1;
        }
    }




}
