<?php

/**
 * This is the model class for table "projects".
 *
 * The followings are the available columns in table 'projects':
 * @property integer $Project_ID
 * @property integer $Client_ID
 * @property string $Project_Name
 * @property string $Project_Description
 * @property string $Project_Prod_Number
 * @property integer $PO_Starting_Number
 * @property integer $COA_Manual_Coding
 * @property string $COA_Break_Character
 * @property integer $COA_Break_Number
 */
class Projects extends CActiveRecord
{
    const DEFAULT_PO_STARTING_NUMBER = 1000;
    const DEFAULT_CKRQ_STARTING_NUMBER = 1000;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'projects';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Client_ID, Project_Name, Project_Description, Ck_Req_Starting_Numb, PO_Starting_Number', 'required'),
			array('Client_ID, PO_Starting_Number, COA_Manual_Coding, COA_Break_Number, Ck_Req_Starting_Numb', 'numerical', 'integerOnly'=>true),
			array('Project_Name', 'length', 'max'=>25),
			array('Project_Description', 'length', 'max'=>125),
			array('Project_Prod_Number', 'length', 'max'=>30),
           // array('Project_Prod_Number', 'numerical'),
            //array('Project_Prod_Number', 'checkunique','except' => 'RegisterScenario'),
			array('COA_Break_Character', 'length', 'max'=>1),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Project_ID, Client_ID, Project_Name, Project_Description, Project_Prod_Number, PO_Starting_Number, COA_Manual_Coding, COA_Break_Character, COA_Break_Number, Ck_Req_Starting_Numb', 'safe', 'on'=>'search'),
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
        );
    }

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
            'Project_ID' => 'Project',
            'Client_ID' => 'Client',
            'Project_Name' => 'Project Name',
            'Project_Description' => 'Project Description',
            'Project_Prod_Number' => 'Project Number',
            'PO_Starting_Number' => 'PO Starting Number',
			'COA_Manual_Coding' => 'COA Manual Coding',
			'COA_Break_Character' => 'COA Break Character',
			'COA_Break_Number' => 'COA Break Number',
            'Ck_Req_Starting_Numb' => 'Check Request Starting Number',
		);
	}

    /**
     * Get Users Client Projects for My Account page
     * @param $client_admin
     * @return array
     */
    public static function getClientUserProjects($client_admin)
    {
        $projects = array();

        $projects[0] = 'Select Project';

        if ($client_admin || Yii::app()->user->userType == UsersClientList::APPROVER || Yii::app()->user->userType == UsersClientList::CLIENT_ADMIN ||
            Yii::app()->user->userType == Users::ADMIN ||  Yii::app()->user->userType == Users::DB_ADMIN) {
            $projectsList = Projects::model()->findAllByAttributes(array(
                'Client_ID' => Yii::app()->user->clientID,
            ));
        } else {
            $condition = new CDbCriteria();
            $condition->condition = "users_project_list.User_ID = '" . Yii::app()->user->userID . "'";
            $condition->addCondition("t.Client_ID = '" . Yii::app()->user->clientID . "'");
            $condition->join = "LEFT JOIN users_project_list ON users_project_list.Project_ID = t.Project_ID";
            $projectsList = Projects::model()->findAll($condition);
        }

        foreach($projectsList as $project) {
            $projects[$project->Project_ID] = $project->Project_Name;
        }

        return $projects;
    }

    /**
     * Get all user projects or projects of certain client
     * @param $userID
     * @param int $clientID
     * @return array
     */
    public static function getUserProjects($userID, $clientID = 0)
    {
        $clientID = intval($clientID);
        $userID = intval($userID);
        $userProjects = array();

        if ($clientID == 0) {
            $condition = new CDbCriteria();
            $condition->condition = "users_project_list.User_ID = '" . $userID . "'";
            $condition->join = "LEFT JOIN users_project_list ON users_project_list.Project_ID = t.Project_ID";
            $condition->order = "t.Project_Name ASC";
            $projects = Projects::model()->findAll($condition);

            if ($projects) {
                foreach ($projects as $project) {
                    $userProjects[$project->Client_ID][$project->Project_ID] = $project->Project_Name;
                }
            }
        } else {
            $condition = new CDbCriteria();
            $condition->condition = "users_project_list.User_ID = '" . $userID . "'";
            $condition->addCondition("t.Client_ID = '" . $clientID . "'");
            $condition->join = "LEFT JOIN users_project_list ON users_project_list.Project_ID = t.Project_ID";
            $condition->order = "t.Project_Name ASC";
            $projects = Projects::model()->findAll($condition);

            if ($projects) {
                foreach ($projects as $project) {
                    $userProjects[$project->Project_ID] = $project->Project_Name;
                }
            }
        }

        return $userProjects;
    }

    /**
     * Get all user projects for settings tab
     * @param $userId
     * @param bool $addFirstItem
     * @return array
     */
    public static function getAllUserProjectsList($userId, $addFirstItem = false)
    {
        if ($addFirstItem) {
            $userProjects = array(0 => "Use Last Login");
        } else {
            $userProjects = array();
        }

        $condition = new CDbCriteria();
        $condition->condition = "users_project_list.User_ID = '" . $userId . "'";
        $condition->join = "LEFT JOIN users_project_list ON users_project_list.Project_ID = t.Project_ID";
        $condition->order = "company.Company_Name ASC, t.Project_Name ASC";
        $projects = Projects::model()->with('client.company')->findAll($condition);

        foreach($projects as $project) {
            $userProjects[$project->Project_ID] = $project->client->company->Company_Name . ' / ' . $project->Project_Name;
        }

        return $userProjects;
    }

    /**
     * Assign project users, when project was edited or created
     * @param $projectId
     */
    public static function assignProjectUsers($projectId)
    {
        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN users ON users.User_ID = t.User_ID";
        $condition->condition = "t.User_Type = '" . UsersClientList::APPROVER . "'";
        $condition->addCondition("users.User_Type = '" . Users::ADMIN . "'", 'OR');
        $condition->addCondition("users.User_Type = '" . Users::DB_ADMIN . "'", 'OR');
        $condition->addCondition("t.User_Type = '" . UsersClientList::CLIENT_ADMIN . "'", 'OR');
        $condition->addCondition("t.Client_ID = '" . Yii::app()->user->clientID . "'");
        $clientAdminsAndApprovers = UsersClientList::model()->findAll($condition);

        foreach($clientAdminsAndApprovers as $clientAdminsAndApprover) {
            $userProject = UsersProjectList::model()->findByAttributes(array(
                'User_ID' => $clientAdminsAndApprover->User_ID,
                'Project_ID' => $projectId,
            ));

            if (!$userProject) {
                $userProject = new UsersProjectList();
                $userProject->User_ID = $clientAdminsAndApprover->User_ID;
                $userProject->Client_ID = Yii::app()->user->clientID;
                $userProject->Project_ID = $projectId;
                $userProject->save();
            }
        }
    }

    /**
     * Assign projects to new client admin
     * @param $userId
     * @param $clientId
     */
    public static function assignClientAdminProjects($userId, $clientId)
    {
        $condition = new CDbCriteria();
        $condition->condition = "t.Client_ID = '" . $clientId . "'";
        $projects = Projects::model()->findAll($condition);

        foreach($projects as $project) {
            $userProject = UsersProjectList::model()->findByAttributes(array(
                'User_ID' => $userId,
                'Project_ID' => $project->Project_ID,
            ));

            if (!$userProject) {
                $userProject = new UsersProjectList();
                $userProject->User_ID = $userId;
                $userProject->Client_ID = $clientId;
                $userProject->Project_ID = $project->Project_ID;
                $userProject->save();
            }
        }
    }

    /**
     * Update Project's COA parameters
     * @param $projectID
     * @param $coaAllowManualCoding
     * @param $coaBreakCharacter
     * @param $coaBreakNumber
     */
    public static function updateCoaParams($projectID, $coaAllowManualCoding, $coaBreakCharacter, $coaBreakNumber)
    {
        $project = Projects::model()->findByPk($projectID);
        if ($project) {
            $project->COA_Manual_Coding = $coaAllowManualCoding;
            $project->COA_Break_Character = $coaBreakCharacter;
            $project->COA_Break_Number =  $coaBreakNumber;
            if ($project->validate()) {
                $project->save();
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

		$criteria->compare('Project_ID',$this->Project_ID);
		$criteria->compare('Client_ID',$this->Client_ID);
		$criteria->compare('Project_Name',$this->Project_Name,true);
		$criteria->compare('Project_Description',$this->Project_Description,true);
		$criteria->compare('Project_Prod_Number',$this->Project_Prod_Number,true);
		$criteria->compare('PO_Starting_Number',$this->PO_Starting_Number);
		$criteria->compare('COA_Manual_Coding',$this->COA_Manual_Coding);
		$criteria->compare('COA_Break_Character',$this->COA_Break_Character,true);
		$criteria->compare('COA_Break_Number',$this->COA_Break_Number);
        $criteria->compare('Ck_Req_Starting_Numb',$this->Ck_Req_Starting_Numb);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     * Returns info array about used projects for current or specified client
     * @param $client_id
     * @return mixed
     */
    public static function clientProjectUsage ($client_id=''){

        if(!$client_id) $client_id=Yii::app()->user->clientID;
        $condition = new CDbCriteria();
        $condition->condition = "t.Client_ID=".$client_id;

        return  Projects::model()->count($condition);
    }

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Projects the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

   /* public function checkunique($attr)
	{
		$project = Projects::model()->findByAttributes(array(
           'Client_ID'=>Yii::app()->user->clientID,
            'Project_Prod_Number' => $this->$attr
        ));
        if ($project) {
            $this->addError($attr, 'Project number must be unique!');
        }
	}*/

    public static function getProjectNameByID($prID){
    $pr = Projects::model()->findByPk($prID);
        return $pr->Project_Name;
    }
}
