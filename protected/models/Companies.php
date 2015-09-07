<?php

/**
 * This is the model class for table "companies".
 *
 * The followings are the available columns in table 'companies':
 * @property integer $Company_ID
 * @property string $Company_Name
 * @property string $Email
 * @property string $Company_Fed_ID
 * @property string $SSN
 * @property string $Business_NameW9
 * @property string $Auth_Code
 * @property string $Auth_Url
 */
class Companies extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'companies';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Company_Name, Company_Fed_ID', 'required'),
            array('Company_Fed_ID', 'check_fed_id'),
            array('SSN', 'check_ssn'),
			array('Business_NameW9', 'length', 'max'=>80),
            array('Auth_Code, Auth_Url', 'length', 'max'=>8),
            array('Company_Name, Email', 'length', 'max'=>90),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Company_ID, Company_Name, SSN, Email, Company_Fed_ID, Business_NameW9, Auth_Code, Auth_Url', 'safe', 'on'=>'search'),
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
            'adreses'=>array(self::MANY_MANY, 'Addresses', 'company_addresses(Company_ID, Address_ID)'),
            'client'=>array(self::HAS_ONE, 'Clients', 'Company_ID'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'Company_ID' => 'Company',
			'Company_Name' => 'Company Name',
			'Email' => 'Email',
            'Company_Fed_ID' => 'Company Fed_ID',
            'Business_NameW9' => 'Business Name',
            'SSN' => 'SSN',
            'Auth_Code' => 'Auth Code',
            'Auth_Url' => 'Auth Url',
		);
	}

    /**
     * Check Fed ID rule
     */
    public function check_fed_id() {
        $company = Companies::model()->find('Company_Fed_ID=:Fed_ID',
            array(':Fed_ID'=>$this->Company_Fed_ID));
        if($company != null && $company->Company_ID != $this->Company_ID) {
            $this->addError('Company_Fed_ID','Company with this Fed ID already exists');
        } else if (!preg_match('/^(\d{2}\-\d{7})|(\d{3}\-\d{2}\-\d{4})|(IN[-]\d{7})|(T0[-]\d{7})$/', $this->Company_Fed_ID)) {
            $this->addError('Company_Fed_ID','Invalid Fed ID, correct formatting: xx-xxxxxxx');
        }
    }

    /**
     * Check SSN rule
     */
    public function check_ssn() {
        if (!preg_match('/^(\d{2}\-\d{7})|(\d{3}\-\d{2}\-\d{4})$/', $this->SSN) && ($this->SSN != '' || $this->SSN != null)) {
            $this->addError('SSN','Invalid SSN, correct formatting: xxx-xx-xxxx');
        }
    }

    /**
     * Gets list of companies by query string
     * @param $queryString
     * @param $options
     * @param $sortOptions
     * @return array
     */
    public function getListByQueryString($queryString, $options, $sortOptions,$limit)
    {

        if (!get_magic_quotes_gpc()) {
            $queryString = addslashes(stripslashes($queryString));
        }

        if ($queryString != '') {
            $optionsArr = array();
            if ($options['search_option_fed_id']) {
                $optionsArr[] = "`companies`.`Company_Fed_ID` LIKE '%" . $queryString . "%'";
            }
            if ($options['search_option_com_name']) {
                $optionsArr[] = "`companies`.`Company_Name` LIKE '%" . $queryString . "%'";
            }
            if ($options['search_option_addr1']) {
                $optionsArr[] = "`addresses`.`Address1` LIKE '%" . $queryString . "%'";
            }
            if ($options['search_option_addr2']) {
                $optionsArr[] = "`addresses`.`Address2` LIKE '%" . $queryString . "%'";
            }
            if ($options['search_option_city']) {
                $optionsArr[] = "`addresses`.`City` LIKE '%" . $queryString . "%'";
            }
            if ($options['search_option_state']) {
                $optionsArr[] = "`addresses`.`State` LIKE '%" . $queryString . "%'";
            }
            if ($options['search_option_zip']) {
                $optionsArr[] = "`addresses`.`ZIP` LIKE '%" . $queryString . "%'";
            }
            if ($options['search_option_country']) {
                $optionsArr[] = "`addresses`.`Country` LIKE '%" . $queryString . "%'";
            }
            if ($options['search_option_phone']) {
                $optionsArr[] = "`addresses`.`Phone` LIKE '%" . $queryString . "%'";
            }


            $where = " `w9`.`Revision_ID` >= '0' ";

            if (count($optionsArr) > 0) {
                $where .=  ' AND (' . implode(' OR ', $optionsArr) . ')';
            } else {
                $where .= " AND (`companies`.`Company_Fed_ID` LIKE '%" . $queryString . "%' OR
                      `companies`.`Company_Name` LIKE '%" . $queryString . "%' OR
                      `addresses`.`Address1` LIKE '%" . $queryString . "%' OR
                      `addresses`.`Address2` LIKE '%" . $queryString . "%' OR
                      `addresses`.`City` LIKE '%" . $queryString . "%' OR
                      `addresses`.`State` LIKE '%" . $queryString . "%' OR
                      `addresses`.`ZIP` LIKE '%" . $queryString . "%' OR
                      `addresses`.`Country` LIKE '%" . $queryString . "%' OR
                      `addresses`.`Phone` LIKE '%" . $queryString . "%')";
            }

            $where .=  " AND `w9`.`W9_Owner_ID` = '" . Yii::app()->user->clientID . "'";

            if (Yii::app()->user->userType == UsersClientList::USER){
            $where .=  " AND `w9`.`Creator_ID` = '" . Yii::app()->user->userID . "'";
            }

            $sql = "SELECT distinct `w9`.`Client_ID`, `companies`.*, `addresses`.*  FROM
                   `w9`
                LEFT JOIN `clients` ON `clients`.`Client_ID` = `w9`.`Client_ID`
                LEFT JOIN `companies` ON `clients`.`Company_ID` = `companies`.`Company_ID`
                LEFT JOIN `company_addresses` ON `company_addresses`.`Company_ID` = `companies`.`Company_ID`
                LEFT JOIN `addresses` ON `addresses`.`Address_ID` = `company_addresses`.`Address_ID`
                WHERE $where".

                "ORDER BY " . $sortOptions['sort_by'] . " " . $sortOptions['sort_direction'];
                if($limit != 0) {$sql= $sql. " limit ".$limit;}


            //echo $sql; die;
            $connection=Yii::app()->db;
            $command=$connection->createCommand($sql);
            $rows=$command->queryAll();
        } else {
            $where = " `w9`.`Revision_ID` >= '0' ";
            $where .=  " AND `w9`.`W9_Owner_ID` = '" . Yii::app()->user->clientID . "'";

            if (Yii::app()->user->userType == UsersClientList::USER){
                $where .=  " AND `w9`.`Creator_ID` = '" . Yii::app()->user->userID . "'";
            }

            $sql = "SELECT distinct `w9`.`Client_ID`, `companies`.*, `addresses`.*  FROM
                   `w9`
                LEFT JOIN `clients` ON `clients`.`Client_ID` = `w9`.`Client_ID`
                LEFT JOIN `companies` ON `clients`.`Company_ID` = `companies`.`Company_ID`
                LEFT JOIN `company_addresses` ON `company_addresses`.`Company_ID` = `companies`.`Company_ID`
                LEFT JOIN `addresses` ON `addresses`.`Address_ID` = `company_addresses`.`Address_ID`
                WHERE $where".
                " ORDER BY `companies`.`Company_Name` ASC";

    
            if($limit != 0) {$sql= $sql. " limit ".$limit;}

            $connection=Yii::app()->db;
            $command=$connection->createCommand($sql);
            $rows=$command->queryAll();
        }

        return $rows;
    }

    /**
     * Test for available W9 of certain company
     * @param $fed_id
     * @param $company_name
     * @param $address
     * @param $clientID
     * @return array
     */
    public function testForW9($fed_id, $company_name, $address, $clientID)
    {
        $result = array(
            'success' => false,
            'fed_id' => $fed_id,
            'company_name' => $company_name,
            'address' => $address,
            'error' => 1,
        );

        $optionsArr = array();
        $company = false;
        $founded_by = '';

        // check w9 existing
        if (preg_match('/^(\d{2}\-\d{7})|(\d{3}\-\d{2}\-\d{4})$/', $fed_id)) {
            $queryString = $fed_id;
            if (!get_magic_quotes_gpc()) {
                $queryString = addslashes(stripslashes($queryString));
            }
            $optionsArr[] = "`companies`.`Company_Fed_ID` = '" . $queryString . "'";
            $company = Companies::findW9ForTest($optionsArr, $clientID);
            $founded_by = 'w9';
        }

        if (!$company && $company_name != '') {
            $optionsArr = array();
            $queryString = $company_name;
            if (!get_magic_quotes_gpc()) {
                $queryString = addslashes(stripslashes($queryString));
            }
            $optionsArr[] = "`companies`.`Company_Name` = '" . $queryString . "'";
            $company = Companies::findW9ForTest($optionsArr, $clientID);
            $founded_by = 'name';
        }

        if (!$company && $address != '') {
            $optionsArr = array();
            $queryString = $address;
            if (!get_magic_quotes_gpc()) {
                $queryString = addslashes(stripslashes($queryString));
            }
            $optionsArr[] = "`addresses`.`Address1` = '" . $queryString . "'";
            $optionsArr[] = "`addresses`.`Address2` = '" . $queryString . "'";
            $optionsArr[] = "`addresses`.`City` = '" . $queryString . "'";
            $optionsArr[] = "`addresses`.`State` = '" . $queryString . "'";
            $optionsArr[] = "`addresses`.`ZIP` = '" . $queryString . "'";
            $optionsArr[] = "`addresses`.`Country` = '" . $queryString . "'";
            $optionsArr[] = "`addresses`.`Phone` = '" . $queryString . "'";
            $company = Companies::findW9ForTest($optionsArr, $clientID);
            $founded_by = 'address';
        }

        //find company
        if ($company) {
            if ($founded_by == 'w9') {
                $result = array(
                    'success' => true,
                    'fed_id' => $company['Company_Fed_ID'],
                    'company_name' => $company['Company_Name'],
                    'address' => $company['Address1'],
                    'error' => 0,
                );
            } else {
                $result = array(
                    'success' => true,
                    'fed_id' => '',
                    'company_name' => $company['Company_Name'],
                    'address' => $company['Address1'],
                    'error' => 0,
                );
            }
        }

        return $result;
    }

    /**
     * Find company by search criteria for test for w9 box
     * @param $optionsArr
     * @param $clientID
     * @return array
     */
    public static function findW9ForTest($optionsArr, $clientID)
    {
        if (count($optionsArr) > 0) {
            $where = " `w9`.`Revision_ID` >= '0' ";
            $where .=  ' AND (' . implode(' OR ', $optionsArr) . ')';
            $where .=  " AND `w9`.`W9_Owner_ID` = '" . $clientID . "'";
            $sql = "SELECT distinct `w9`.`Client_ID`, `companies`.*, `addresses`.*  FROM
                          `w9`
                        LEFT JOIN `clients` ON `clients`.`Client_ID` = `w9`.`Client_ID`
                        LEFT JOIN `companies` ON `clients`.`Company_ID` = `companies`.`Company_ID`
                        LEFT JOIN `company_addresses` ON `company_addresses`.`Company_ID` = `companies`.`Company_ID`
                        LEFT JOIN `addresses` ON `addresses`.`Address_ID` = `company_addresses`.`Address_ID`
                        WHERE $where
                        ORDER BY `companies`.`Company_Name` ASC
                       ";
            //echo $sql; die;
            $connection=Yii::app()->db;
            $command=$connection->createCommand($sql);
            $company=$command->queryRow();
            return $company;
        }
    }

    /**
     * Get companies list to share w9
     * @return array
     */
    public static function getCompaniesToShareW9()
    {
        $companiesToShareW9 = array();

        $vendors = Vendors::getCompanyVendors('');
        foreach ($vendors as $vendor) {
            $companiesToShareW9[$vendor->client->Client_ID] = $vendor->client->company->Company_Name;
        }

        $user = Users::model()->with('clients.company')->findByPk(Yii::app()->user->userID);
        $clients = $user->clients;
        foreach ($clients as $clients) {
            $companiesToShareW9[$clients->Client_ID] = $clients->company->Company_Name;
        }

        asort($companiesToShareW9);

        return $companiesToShareW9;
    }

    /**
     * Get Companies which was created through uploading W9 and which haven't got client admin
     * @param $companyName
     * @param bool $limit
     * @param bool $not_printed
     * @return array
     */
    public static function getEmptyCompanies($companyName, $limit = false, $not_printed = false)
    {
        $companies = array(
            'not_printed' => array(),
            'printed' => array(),
        );

        if ($companyName != '') {
            // get companies for which wasn't printed letter
            $condition = new CDbCriteria();
            $condition->condition = "t.Auth_Code IS NOT NULL";
            $condition->addCondition("t.Auth_Url IS NULL");
            if ($companyName != '*') {
                $condition->compare('t.Company_Name', $companyName, true);
            }
            $condition->order = "t.Company_Name ASC";

            if ($limit) {
                $condition->limit = $limit;
            }

            $companies['not_printed'] = Companies::model()->with('client')->findAll($condition);

            if (!$not_printed) {
                // get companies for which was printed letter
                $condition = new CDbCriteria();
                $condition->condition = "t.Auth_Code IS NOT NULL";
                $condition->addCondition("t.Auth_Url IS NOT NULL");
                if ($companyName != '*') {
                    $condition->compare('t.Company_Name', $companyName, true);
                }
                $condition->order = "t.Company_Name ASC";

                if ($limit) {
                    $condition->limit = $limit;
                }

                $companies['printed'] = Companies::model()->findAll($condition);
            }
        }

        return $companies;
    }

    /**
     * Create Empty company without users
     * @param $fedId
     * @param $newCompanyName
     * @param $impVendorInfo
     * @return Clients
     */
    public static function createEmptyCompany($fedId, $newCompanyName, $impVendorInfo = null)
    {
        $client = new Clients;

        // begin transaction
        $transaction = Yii::app()->db->beginTransaction();
        try {
            $company = new Companies;
            $project = new Projects;
            $companyAdreses = new CompanyAddresses;
            //$usersClientList = new UsersClientList;

            $company->Company_Name = $newCompanyName;
            $company->Company_Fed_ID = $fedId;
            if (preg_match('/^(\d{2}\-\d{7})|(\d{3}\-\d{2}\-\d{4})$/', $company->Company_Fed_ID)) {
                //usual w9 do nothing
            }

            if (preg_match('/^(IN[-]\d{7})$/', $company->Company_Fed_ID)) {
            //international w9
                $company->Temp_Fed_ID_Flag = 'N';

            }
            if (preg_match('/^(T0[-]\d{7})$/', $company->Company_Fed_ID)) {
                //international w9
                $company->Temp_Fed_ID_Flag = 'T';
            }

            $company->Auth_Code = Helper::generatePassword();

            $company->save();


            $company_adress = new Addresses;

            if ($impVendorInfo) {
                $company_adress = new Addresses;
                $company_adress->Address1 = $impVendorInfo['address'];
                $company_adress->City = $impVendorInfo['city'];
                $company_adress->State = $impVendorInfo['state'];
                $company_adress->ZIP = $impVendorInfo['zip'];
                if ($company_adress->validate()) {
                    $company_adress->save();
                } else {
                    $company_adress = new Addresses;
                    $company_adress->save();
                }
            }

            $company_adress->save();

            $companyAdreses->Company_ID = $company->Company_ID;
            $companyAdreses->Address_ID = $company_adress->Address_ID ? $company_adress->Address_ID : 0;
            $companyAdreses->save();

            $client->Company_ID = $company->Company_ID;
            $client->Client_Type = 1;
            $client->Client_Number = 1;
            $client->save();

            //create client project
            $project->Client_ID = $client->Client_ID;
            $project->Project_Name = "Corporate";
            $project->Project_Description = "Description of the Project";
            $project->PO_Starting_Number = Projects::DEFAULT_PO_STARTING_NUMBER;
            $project->Ck_Req_Starting_Numb = Projects::DEFAULT_CKRQ_STARTING_NUMBER;
            $project->save();

            $transaction->commit();
        } catch(Exception $e) {
            $transaction->rollback();
        }

        return $client;
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

		$criteria->compare('Company_ID',$this->Company_ID);
		$criteria->compare('Company_Name',$this->Company_Name, true);
		$criteria->compare('Email',$this->Email, true);
        $criteria->compare('Company_Fed_ID',$this->Company_Fed_ID);
        $criteria->compare('SSN',$this->SSN);
        $criteria->compare('Business_NameW9',$this->Business_NameW9, true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Companies the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public static function fillWithSessionDataEntry($company_model,$current_upload_file){
        $adr = $company_model->adreses[0];
        $adr->Address1 = $current_upload_file['street_adr'];
        $adr->City = $current_upload_file['city'];
        $adr->State = $current_upload_file['state'];
        $adr->ZIP = $current_upload_file['zip'];
        $adr->save();


        $companyAdreses = CompanyAddresses::model()->findByAttributes(array(
            'Company_ID'=>$company_model->Company_ID
        ));
        $companyAdreses->Address_ID = $adr->Address_ID;
        $companyAdreses->save();
    }

    protected function afterSave() {
        parent::afterSave();
        if ($this->isNewRecord) {
            if ($this->Temp_Fed_ID_Flag == 'N') {
                Generators::updateNumberByType(1);
            }
            if ($this->Temp_Fed_ID_Flag == 'T') {
                Generators::updateNumberByType(2);
            }

        }
    }

    public static function getDataByName($name) {
        $result = array();
        $criteria = new CDbCriteria();
        $criteria->compare('Company_Name',$name,true);
        $companies = Companies::model()->with('adreses')->findAll($criteria);
        $i = 0;
        foreach ($companies as $company) {
            $result[$i]['Company_Name'] = Helper::truncLongWords($company->Company_Name,30);
            $result[$i]['Address1'] = $company->adreses[0]->Address1;
            $result[$i]['City'] = $company->adreses[0]->City;
            $result[$i]['State'] = $company->adreses[0]->State;
            $result[$i]['ZIP'] = $company->adreses[0]->ZIP;
            $result[$i]['Company_Fed_ID'] = $company->Company_Fed_ID;
            $i++;
        }

        return $result;
    }

    public static function checkExistByFedID($fed_id) {
        $company = self::model()->findByAttributes(array(
            'Company_Fed_ID'=>$fed_id
        ));
        if ($company) {return $company;}
        else {
            return false;
        }
    }


}
