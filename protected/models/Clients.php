<?php

/**
 * This is the model class for table "clients".
 *
 * The followings are the available columns in table 'clients':
 * @property integer $Client_ID
 * @property integer $Client_Number
 * @property string $Client_Type
 * @property integer $Company_ID
 * @property string $Client_Approval_Amount_1
 * @property string $Client_Approval_Amount_2
 * @property string $Client_Logo_Name
 * @property string $Client_Logo
 */
class Clients extends CActiveRecord
{
    /**
     * Client active values
     */
    const NOT_ACTIVE = 0;
    const ACTIVE = 1;
    const ACTIVE_SINGLE = 2;


	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'clients';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Client_Number, Client_Type', 'required'),
			array('Client_Number, Company_ID, Client_Status', 'numerical', 'integerOnly'=>true),
			array('Client_Type', 'length', 'max'=>1),
			array('Client_Approval_Amount_1, Client_Approval_Amount_2', 'length', 'max'=>8),
			array('Client_Logo_Name', 'length', 'max'=>30),
			array('Client_Logo', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Client_ID, Client_Number, Client_Type, Company_ID, Client_Approval_Amount_1, Client_Approval_Amount_2, Client_Logo_Name, Client_Logo', 'safe', 'on'=>'search'),
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
            'company'=>array(self::BELONGS_TO, 'Companies', 'Company_ID'),
            'users'=>array(self::MANY_MANY, 'Users', 'users_client_list(Client_ID, User_ID)', 'with' => 'person'),
            'vendors_list'=>array(self::HAS_MANY, 'Vendors', 'Client_Client_ID', 'condition' => "Active_Relationship = '1'"),
            'projects'=>array(self::HAS_MANY, 'Projects', 'Client_ID'),
            'service_settings'=>array(self::HAS_ONE, 'ClientServiceSettings', 'Client_ID'),
            'pending_service_settings'=>array(self::HAS_ONE, 'PendingClientServiceSettings', 'Client_ID'),
            'service_payments'=>array(self::HAS_MANY, 'ServicePayments', 'Client_ID', 'order' => 'Payment_Date DESC'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'Client_ID' => 'Database generated unique Client number',
			'Client_Number' => 'Client Number',
			'Client_Type' => 'Client type',
            'Client_Status' => 'Client Status',
			'Company_ID' => 'Company ID from Companies table',
			'Client_Approval_Amount_1' => 'Client Approval Amount 1',
			'Client_Approval_Amount_2' => 'Client Approval Amount 2',
			'Client_Logo_Name' => 'Pseudo name',
			'Client_Logo' => 'Client Logo',
		);
	}

    /**
     * Activate client
     */
    public function activate()
    {
        $this->Client_Status = Clients::ACTIVE;
        $this->save();

        foreach ($this->users as $user) {
            Mail::sendClientStatusMail($user->person->Email, $this->company->Company_Name, $user->person->First_Name, $user->person->Last_Name, true);
        }
    }

    /**
     * Deactivate client
     */
    public function deactivate()
    {
        $this->Client_Status = Clients::NOT_ACTIVE;
        $this->save();

        foreach ($this->users as $user) {
            Mail::sendClientStatusMail($user->person->Email, $this->company->Company_Name, $user->person->First_Name, $user->person->Last_Name, false);
        }
    }

    /**
     * Get user's clients list except current client
     * @return array
     */
    public static function getOtherUserClients()
    {
        $userClients = array();

        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN users_client_list ON users_client_list.Client_ID = t.Client_ID";
        $condition->condition = "users_client_list.User_ID = '" . Yii::app()->user->userID . "'";
        $condition->addCondition("t.Client_ID != '" . Yii::app()->user->clientID . "'");
        $condition->addCondition("t.Client_Status = '" . self::ACTIVE . "'");
        $clients = Clients::model()->with('company')->findAll($condition);

        foreach ($clients as $client) {
            $userClients[$client->Client_ID] = CHtml::encode($client->company->Company_Name);
        }

        return $userClients;
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

		$criteria->compare('Client_ID',$this->Client_ID);
		$criteria->compare('Client_Number',$this->Client_Number);
		$criteria->compare('Client_Type',$this->Client_Type,true);
		$criteria->compare('Company_ID',$this->Company_ID);
        $criteria->compare('Client_Status',$this->Client_Status);
		$criteria->compare('Client_Approval_Amount_1',$this->Client_Approval_Amount_1,true);
		$criteria->compare('Client_Approval_Amount_2',$this->Client_Approval_Amount_2,true);
		$criteria->compare('Client_Logo_Name',$this->Client_Logo_Name,true);
		$criteria->compare('Client_Logo',$this->Client_Logo,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}


    public function getClientsProjects() {

        $project_list = array();
        $projects = $this->projects;


        foreach ($projects as $project) {

            $project_list[] = array(
                'Project_ID'=>$project->Project_ID,
                'Project_Name'=>$project->Project_Name
            );

        }
        return $project_list;
    }

    /**
     * Get clients list to registration form
     * @param bool $onlyActiveClients
     * @return array
     */
    public function getClientsList($onlyActiveClients = false) {
        $clientsList = array();
        $criteria=new CDbCriteria;
        $criteria->condition="Client_Number!='0'";

        if ($onlyActiveClients) {
            $criteria->addCondition("Client_Status = '" . self::ACTIVE . "'");
        }

        $criteria->order = "company.Company_Name ASC";
        $clients = $this->with('company')->findAll($criteria);
        foreach ($clients as $client) {
            if ($client && $client->company) {
                $clientsList[$client->Client_ID] = ($client->Client_Logo_Name) ? strval($client->Client_Logo_Name) : $client->company->Company_Name;
            }
        }
        return $clientsList;
    }


    public static function getClientsIDList($user_id) {

        $result = array();
        $user = Users::model()->with('clients')->findByPk($user_id);
        $clients = $user->clients;

        foreach ($clients as $client) {
            $result[] = $client->Client_ID;
        }

        return $result;
    }



    public function getClientsListByCompanyName($search_string,$onlyActiveClients = false) {
        $clientsList = array();
        $criteria=new CDbCriteria;
        $criteria->condition="Client_Number!='0'";

        if ($onlyActiveClients) {
            $criteria->addCondition("Client_Status = '" . self::ACTIVE . "'");
        }

        if ( Yii::app()->user->id != 'db_admin') {
            //$criteria->addCondition("Client_ID = '" .  . "'");
            $criteria->join = "LEFT JOIN users_client_list ON users_client_list.Client_ID = t.Client_ID";
            $criteria->condition = "users_client_list.User_ID = '" . Yii::app()->user->userID . "'";
        }

        $criteria->addSearchCondition('company.Company_Name',$search_string);


        $criteria->order = "company.Company_Name ASC";
        $clients = $this->with('company')->findAll($criteria);
        foreach ($clients as $client) {
            if ($client && $client->company) {
                $clientsList[$client->Client_ID] = $client->company->Company_Name;
            }
        }
        return $clientsList;
    }

    public static function ClientDocumentsCount($client_id,$projects_array=null) {

        if (!$projects_array) {
                $sql='select documents.Document_Type,count(documents.Document_ID) as Total from documents
                      left join images on (documents.Document_ID = images.Document_ID)
                      where documents.Client_ID = '.$client_id.'
                      group by documents.Document_Type
                      order by Total desc';

        } else if (is_array($projects_array)) {
            $sql='select documents.Document_Type,count(documents.Document_ID) as Total from documents
                  left join images on (documents.Document_ID = images.Document_ID)
                      where documents.Client_ID = '.$client_id.'
                      and documents.Project_ID in ('.implode(",", $projects_array).')
                      group by documents.Document_Type
                      order by Total desc';
        }

        //var_dump($sql);die;

        $list= Yii::app()->db->createCommand($sql)->queryAll();
        return $list;


    }

    public static function ClientFilesCount($client_id, $projects_array ,$origin=false) {

         $sql='select Mime_Type,sum(File_Size) as MB,count(images.Image_ID) as FilesCount,sum(images.Pages_Count) as PagesCount from images
                left join documents on (documents.Document_ID = images.Document_ID)
                where documents.Client_ID = '.$client_id;
         if ($origin) $sql .= ' and documents.Origin="'.$origin.'" ';

        $sql .= ' and documents.Project_ID in ('.implode(",",$projects_array).')';

        $sql .=' group by Mime_Type
                order by FilesCount desc';

        //var_dump($sql);die;
        $list= Yii::app()->db->createCommand($sql)->queryAll();
        return $list;


    }

    /**
     * Function resets values of client and project to the first found for given user
     * @param $user_id
     */
    public static function resetClientProjectToFirst ($user_id) {
        //find companies(clients) available for user
        $users_clients = Clients::getClientsIDList($user_id);
        //set up session variable to the first client in the list
        $_SESSION['last_client'] = $users_clients[0];

        //find projects of client
        $projects = Projects::getUserProjects($user_id, $users_clients[0]);

        $_SESSION['last_project'] = $projects[0]->Project_ID;


    }

    /**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Clients the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
