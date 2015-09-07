<?php

/**
 * This is the model class for table "pcs".
 *
 * The followings are the available columns in table 'pcs':
 * @property integer $PC_ID
 * @property integer $Document_ID
 * @property string $Employee_Name
 * @property string $Envelope_Date
 * @property string $Envelope_Number
 * @property string $Envelope_Total
 */
class Pcs extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'pcs';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Document_ID', 'required'),
			array('Document_ID', 'numerical', 'integerOnly'=>true),
			array('Employee_Name', 'length', 'max'=>100),
			array('Envelope_Number', 'length', 'max'=>32),
			array('Envelope_Total', 'length', 'max'=>13),
            array('Envelope_Date', 'date', 'format' => 'yyyy-MM-dd'),
            array('PC_ID', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('PC_ID, Document_ID, Employee_Name, Envelope_Date, Envelope_Number, Envelope_Total', 'safe', 'on'=>'search'),
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
            'document' => array(self::BELONGS_TO, 'Documents', 'Document_ID'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'PC_ID' => 'Pc',
			'Document_ID' => 'Document',
			'Employee_Name' => 'Employee Name',
			'Envelope_Date' => 'Envelope Date',
			'Envelope_Number' => 'Envelope Number',
			'Envelope_Total' => 'Envelope Total',
		);
	}

    /**
     * Find PCs to entry data
     */
    public static function findPCsToEntry()
    {
        $queryString = $_SESSION['last_pc_to_entry_search']['query'];
        $options =  $_SESSION['last_pc_to_entry_search']['options'];

        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN documents ON documents.Document_ID=t.Document_ID";
        $condition->condition = "t.Envelope_Number = '0'";

        if (Yii::app()->user->userType == UsersClientList::PROCESSOR || Yii::app()->user->userType == UsersClientList::APPROVER
            || Yii::app()->user->userType == UsersClientList::CLIENT_ADMIN) {
            $condition->addCondition("documents.Client_ID='" . Yii::app()->user->clientID . "'");
        }

        if (Yii::app()->user->userType == UsersClientList::PROCESSOR || ((Yii::app()->user->userType == UsersClientList::APPROVER
            || Yii::app()->user->userType == UsersClientList::CLIENT_ADMIN) && is_numeric(Yii::app()->user->projectID))) {
            $condition->addCondition("documents.Project_ID='" . Yii::app()->user->projectID . "'");
        }
        if (Yii::app()->user->userType == UsersClientList::USER
            && is_numeric(Yii::app()->user->projectID)) {
            $condition->addCondition("documents.Project_ID='" . Yii::app()->user->projectID . "'");
            $condition->addCondition("documents.User_ID='" . Yii::app()->user->userID . "'");
        }

        if (Yii::app()->user->userType == Users::DATA_ENTRY_CLERK) {
            //adding condition to allow DEC see only documents of clients that he has access
            $cli_array = Clients::getClientsIDList(Yii::app()->user->userID);
            $condition->addInCondition('documents.Client_ID', $cli_array);
        }

        $countCond = 0;
        if (count($options) > 0 && trim($queryString) != '') {
            $search_condition = new CDbCriteria();


            if ($options['search_option_emp_name']) {
                $search_condition->compare('t.Employee_Name', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_env_num']) {
                $search_condition->compare('t.Envelope_Number', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_env_total']) {
                $search_condition->compare('t.Envelope_Total', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_env_date']) {
                $search_condition->compare('t.Envelope_Date', $queryString, true, 'OR');
                $countCond++;
            }
        }


        $condition->order = "documents.Created ASC";

        if( $countCond > 0 ) $condition->mergeWith($search_condition);

        $pcs = Pcs::model()->findAll($condition);

        return $pcs;
    }

    /**
     * Get Last Client PCs
     * @param int $limit
     * @return array|CActiveRecord|CActiveRecord[]|mixed|null
     */
    public static function getPCsList($limit = 50)
    {
        $pcs = new Pcs();

        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN documents ON documents.Document_ID=t.Document_ID";
        //$condition->condition = "t.Envelope_Total='0'";
        $condition->addCondition("documents.Client_ID='" . Yii::app()->user->clientID . "'");

        if (is_numeric(Yii::app()->user->projectID)) {
            $condition->addCondition("documents.Project_ID= '" . Yii::app()->user->projectID . "'");
        }

        $condition->order = "t.Employee_Name ASC";
        $condition->limit = $limit;
        $paymentsList = $pcs->findAll($condition);

        return $paymentsList;
    }

    /**
     * Get PCs list by query string and search options
     * @param $queryString
     * @param $options
     * @param $sortOptions
     * @param int $limit
     * @return array|CActiveRecord|mixed|null
     */
    public static function getListByQueryString($queryString, $options, $sortOptions, $limit = 50)
    {
        // get pcs list condition
        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN documents ON documents.Document_ID=t.Document_ID";

        $countCond = 0;
        if (count($options) > 0 && trim($queryString) != '') {
            if ($options['search_option_employee_name']) {
                $condition->compare('t.Employee_Name', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_envelope_number']) {
                $condition->compare('t.Envelope_Number', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_envelope_total']) {
                $condition->compare('t.Envelope_Total', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_envelope_date']) {
                $condition->compare('t.Envelope_Date', $queryString, true, 'OR');
                $countCond++;
            }
        }

        if ($countCond == 0 && trim($queryString) != '') {
            $condition->compare('t.Employee_Name', $queryString, true, 'OR');
            $condition->compare('t.Envelope_Number', $queryString, true, 'OR');
            $condition->compare('t.Envelope_Total', $queryString, true, 'OR');
            $condition->compare('t.Envelope_Date', $queryString, true, 'OR');
        }

        //$condition->addCondition("t.Payment_Amount='0'");
        $condition->addCondition("documents.Client_ID='" . Yii::app()->user->clientID . "'");

        if (is_numeric(Yii::app()->user->projectID)) {
            $condition->addCondition("documents.Project_ID= '" . Yii::app()->user->projectID . "'");
        }

        $condition->order = $sortOptions['sort_by'] . " " . $sortOptions['sort_direction'];

        if (trim($queryString) == '') {
            $condition->limit = $limit;
        }

        $pcs = Pcs::model()->findAll($condition);

        return $pcs;
    }

    /**
     * Get library folder names for Data Entry autocomplete
     * @param $projectID
     * @return array
     */
    public static  function getLibraryFolderNames($projectID)
    {
        $folderNames = array();

        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN storages ON storages.Storage_ID = t.Storage_ID";
        $condition->condition = "t.Folder_Cat_ID = '" . Sections::PATTY_CASH . "'";
        $condition->addCondition("storages.Project_ID = '" . $projectID . "'");
        $folders = Sections::model()->findAll($condition);

        foreach ($folders as $folder) {
            $folderNames[] = CHtml::encode($folder->Section_Name);
        }

        $folderNames = array_unique($folderNames);

        return $folderNames;
    }

    /**
     * Delete PC with rows in relative tables
     * @param $pcID
     */
    public static function deletePC($pcID)
    {
        //$pc = Pos::model()->with('document.image')->findByPk($pcID);
        $pc = Pcs::model()->with('document.image')->findByPk($pcID);

        if ($pc) {

            $document = $pc->document;
            $image = $document->image;

            $image->delete();
            $document->delete();



            // delete thumbnail
            $filePath = 'protected/data/thumbs/' . $pc->Document_ID . '.jpg';
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            // delete library links
            LibraryDocs::deleteDocumentLinks($pc->Document_ID);

            $pc->delete();
        }
    }

    /**
     * Get Last Client PCs to session
     * @return array
     */
    public static function getLastClientsPCs()
    {
        $lastPCs = array();

        $pcsCl = new Pcs();
        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN documents ON documents.Document_ID=t.Document_ID";
        $condition->addCondition("documents.Client_ID='" . Yii::app()->user->clientID . "'");

        if (is_numeric(Yii::app()->user->projectID)) {
            $condition->addCondition("documents.Project_ID= '" . Yii::app()->user->projectID . "'");
        }

        $condition->order = "t.Employee_Name ASC";
        $condition->limit = 50;

        $pcs = $pcsCl->findAll($condition);

        $i = 1;
        foreach ($pcs as $pc) {
            $lastPCs[$i] = $pc->Document_ID;
            $i++;
        }

        return $lastPCs;
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

		$criteria->compare('PC_ID',$this->PC_ID);
		$criteria->compare('Document_ID',$this->Document_ID);
		$criteria->compare('Employee_Name',$this->Employee_Name,true);
		$criteria->compare('Envelope_Date',$this->Envelope_Date,true);
		$criteria->compare('Envelope_Number',$this->Envelope_Number,true);
		$criteria->compare('Envelope_Total',$this->Envelope_Total,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Pcs the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * Resets search options to def values
     */
    public static function resetDataentrySearchOptions() {
        $_SESSION['last_pc_to_entry_search']['query'] = '';
        $_SESSION['last_pc_to_entry_search']['options'] = array(
            'search_option_emp_name' => 1,
            'search_option_env_num' => 0,
            'search_option_env_total' => 1,
            'search_option_env_date' => 0,
        );
    }

    /*
     * Sets session variables according to inputted search string
     */
    public static function initDataentrySearchOptions($post) {
        $queryString = trim($post['search_field']);
        $options = array(
            'search_option_emp_name' => (isset($post['search_option_emp_name']) ? 1 : 0),
            'search_option_env_num' => (isset($post['search_option_env_num']) ? 1 : 0),
            'search_option_env_total' => (isset($post['search_option_env_total']) ? 1 : 0),
            'search_option_env_date' => (isset($post['search_option_env_date']) ? 1 : 0),

        );

        // set last search query params to session
        $_SESSION['last_pc_to_entry_search']['query'] = $queryString;
        $_SESSION['last_pc_to_entry_search']['options'] = $options;

    }

}
