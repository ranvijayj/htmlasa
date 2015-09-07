<?php

/**
 * This is the model class for table "w9".
 *
 * The followings are the available columns in table 'w9':
 * @property integer $W9_ID
 * @property integer $Document_ID
 * @property integer $Client_ID
 * @property integer $W9_Data_Entry
 * @property string Business_Name
 * @property string $Tax_Class
 * @property integer $Exempt
 * @property string $Account_Nums
 * @property integer $Signed
 * @property string $Signature_Date
 * @property integer $Revision_ID
 * @property integer $Verified
 */
class W9 extends CActiveRecord
{
    /**
     * Access types
     */
    const HAS_ACCESS = 1;
    const NO_ACCESS = 0;

    /**
     * Verified values
     */
    const VERIFIED = 1;
    const NOT_VERIFIED = 0;

    /**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'w9';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Document_ID, Client_ID, W9_Owner_ID', 'required'),
			array('Document_ID, Client_ID, W9_Data_Entry, Exempt, Signed, Verified, W9_Owner_ID, Access_Type', 'numerical', 'integerOnly'=>true),
			array('Tax_Class, Account_Nums', 'length', 'max'=>50),
			array('Account_Nums', 'length', 'max'=>25),
            array('Business_Name', 'length', 'max'=>45),
            array('Revision_ID', 'length', 'max'=>6),
			array('W9_ID, Signature_Date', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('W9_ID, Document_ID, Client_ID, W9_Data_Entry, Business_Name, Tax_Class, Exempt, Account_Nums, Signed, Signature_Date, Revision_ID, Verified', 'safe', 'on'=>'search'),
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
            'document'=>array(self::BELONGS_TO, 'Documents', 'Document_ID'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'W9_ID' => 'Database generated unique W9 number',
			'Document_ID' => 'Document ID from Documents table',
            'Business_Name' => 'Business Name',
			'Client_ID' => 'Vendor ID from Vendors table',
            'W9_Owner_ID' => 'W9 Owner',
            'Access_Type' => 'Access Type',
			'W9_Data_Entry' => 'W9 Data Entry',
			'Tax_Class' => 'Tax Class',
			'Exempt' => 'Exempt',
			'Account_Nums' => "Account Num's",
			'Signed' => 'Signed',
			'Signature_Date' => 'Signature Date',
			'Revision_ID' => 'Revision ID from W9_Revisions table',
			'Verified' => 'Verified',
		);
	}

    /**
     * Find W9s to data entry module by search query
     * If search query is blank returns all W9s to data entry
     * @param $queryString
     * @param $options
     * @return array|CActiveRecord|mixed|null
     */
    public function findW9ToEntry($queryString, $options)
    {
        $w9s = array();
        $w9s_to_approve = array();

        // get aps to approve
        $condition = new CDbCriteria();
        $condition_for_temp = new CDbCriteria();
        $condition->join = "LEFT JOIN documents ON documents.Document_ID=t.Document_ID
                            LEFT JOIN clients ON t.Client_ID=clients.Client_ID
                            LEFT JOIN companies ON clients.Company_ID=companies.Company_ID
                            LEFT JOIN company_addresses ON company_addresses.Company_ID = companies.Company_ID
                            LEFT JOIN addresses ON addresses.Address_ID = company_addresses.Address_ID";
        $countCond = 0;

        $condition_for_temp->addCondition("companies.Temp_Fed_ID_Flag is null");

        if (count($options) > 0 && trim($queryString) != '') {
            if ($options['search_option_fed_id']) {
                $condition->compare('companies.Company_Fed_ID', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_com_name']) {
                $condition->compare('companies.Company_Name', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_addr1']) {
                $condition->compare('addresses.Address1', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_addr2']) {
                $condition->compare('addresses.Address2', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_city']) {
                $condition->compare('addresses.City', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_state']) {
                $condition->compare('addresses.State', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_zip']) {
                $condition->compare('addresses.ZIP', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_country']) {
                $condition->compare('addresses.Country', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_phone']) {
                $condition->compare('addresses.Phone', $queryString, true, 'OR');
                $countCond++;
            }
        }

        if ($countCond == 0 && trim($queryString) != '') {
            $condition->compare('companies.Company_Fed_ID', $queryString, true, 'OR');
            $condition->compare('companies.Company_Name', $queryString, true, 'OR');
            $condition->compare('addresses.Address1', $queryString, true, 'OR');
            $condition->compare('addresses.Address2', $queryString, true, 'OR');
            $condition->compare('addresses.City', $queryString, true, 'OR');
            $condition->compare('addresses.State', $queryString, true, 'OR');
            $condition->compare('addresses.ZIP', $queryString, true, 'OR');
            $condition->compare('addresses.Country', $queryString, true, 'OR');
            $condition->compare('addresses.Phone', $queryString, true, 'OR');
        }

        if (Yii::app()->user->userType == Users::ADMIN || Yii::app()->user->userType == Users::DATA_ENTRY_CLERK || Yii::app()->user->userType == Users::DB_ADMIN) {
            $criteria = clone $condition;
            $criteria->addCondition("t.Revision_ID<='0'");
            $criteria->addCondition("t.Verified='" . self::NOT_VERIFIED . "'");
            $criteria->order = "documents.Created ASC";
            $criteria->mergeWith($condition_for_temp);
            $w9s_clients = $this->findAll($criteria);




            $criteria = clone $condition;
            $criteria->addCondition("documents.Client_ID>'0'");
            $criteria->addCondition("t.Revision_ID>'0'");
            $criteria->addCondition("t.W9_Data_Entry!='" . Yii::app()->user->userID . "'");
            $criteria->addCondition("t.Verified='" . self::NOT_VERIFIED . "'");
            $criteria->order = "documents.Created ASC";


            if (Yii::app()->user->userType == Users::DATA_ENTRY_CLERK) {
                //adding condition to allow DEC see only documents of clients that he has access
                $cli_array = Clients::getClientsIDList(Yii::app()->user->userID);
                $criteria->addInCondition('documents.Client_ID', $cli_array);
            }

            $criteria->mergeWith($condition_for_temp);
            $w9s = $this->findAll($criteria);

            foreach($w9s_clients as $key => $value) {
                $w9s[] = $value;
            }
        } else if (Yii::app()->user->userType == Users::USER) {
            $criteria = clone $condition;
            //commented out 22.01.2015 as there is no sense to connect to users_client_list table
            //$criteria->join .= ' LEFT JOIN users_client_list ON documents.Client_ID=users_client_list.Client_ID';
            $criteria->addCondition("t.Revision_ID<='0'");
            //commented out 22.01.2015 as there is no sense to connect to users_client_list table
            //$criteria->addCondition("users_client_list.User_ID='" . Yii::app()->user->userID . "'");
            $criteria->addCondition("t.Verified='" . self::NOT_VERIFIED . "'");

            //added instead of connection to users_client_list table
            $criteria->addCondition("documents.Client_ID='" . Yii::app()->user->clientID . "'");
            $criteria->addCondition("documents.Project_ID='" . Yii::app()->user->projectID . "'");

            $criteria->addCondition("documents.User_ID='".Yii::app()->user->userID."'");
            $criteria->order = "documents.Created ASC";

            $criteria->mergeWith($condition_for_temp);

            $w9s = $this->findAll($criteria);

        } else {
            $criteria = clone $condition;
            //commented out 22.01.2015 as there is no sense to connect to users_client_list table
            //$criteria->join .= ' LEFT JOIN users_client_list ON documents.Client_ID=users_client_list.Client_ID';
            $criteria->addCondition("t.Revision_ID<='0'");
            //commented out 22.01.2015 as there is no sense to connect to users_client_list table
            //$criteria->addCondition("users_client_list.User_ID='" . Yii::app()->user->userID . "'");
            $criteria->addCondition("t.Verified='" . self::NOT_VERIFIED . "'");

            //added instead of connection to users_client_list table
            $criteria->addCondition("documents.Client_ID='" . Yii::app()->user->clientID . "'");
            $criteria->addCondition("documents.Project_ID='" . Yii::app()->user->projectID . "'");

            $criteria->order = "documents.Created ASC";

            $criteria->mergeWith($condition_for_temp);

            $w9s = $this->findAll($criteria);
        }

        return $w9s;
    }

    /**
     * Add to log W9.Verified change value
     * @param $w9Id
     * @param $verified
     */
    public static function logVerifiedChange($w9Id, $verified)
    {
        if ($verified == self::VERIFIED) {
            UserLog::createLogRecord(Yii::app()->user->userID, 'w9', $w9Id, UserLog::W9_VERIFIED_TO_1);
        } else if ($verified == self::NOT_VERIFIED) {
            UserLog::createLogRecord(Yii::app()->user->userID, 'w9', $w9Id, UserLog::W9_VERIFIED_TO_0);
        }
    }

    /**
     * Delete W9 with rows in relative tables
     * @param $w9Id
     */
    public static function deleteW9($w9Id)
    {
        $w9 = W9::model()->with('document.image')->findByPk($w9Id);
        if ($w9) {
            $document = $w9->document;

            $documentW9s = W9::model()->findAllByAttributes(array(
                'Document_ID' => $document->Document_ID,
            ));

            if (count($documentW9s) <= 1) {
                $image = $document->image;
                if ($image) $image->delete();
                $document->delete();
            }

            // delete thumbnail
            $filePath = 'protected/data/thumbs/' . $w9->Document_ID . '.jpg';
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            // delete library links
            LibraryDocs::deleteDocumentLinks($w9->Document_ID);

            $w9->delete();
        }
    }

    /**
     * Share W9 to other company
     * @param $clientId
     * @param $access
     */
    public function share($clientId, $access)
    {
        $nW9 = W9::model()->findByAttributes(array(
            'Document_ID' => $this->Document_ID,
            'W9_Owner_ID' => $clientId,
        ));

        if ($nW9 === null) {
            $nW9 = new W9();
            $nW9->Document_ID = $this->Document_ID;
            $nW9->Client_ID = $this->Client_ID;
            $nW9->Business_Name = $this->Business_Name;
            $nW9->W9_Owner_ID = $clientId;
            $nW9->Access_Type = $access;
            $nW9->W9_Data_Entry = $this->W9_Data_Entry;
            $nW9->Tax_Class = $this->Tax_Class;
            $nW9->Exempt = $this->Exempt;
            $nW9->Account_Nums = $this->Account_Nums;
            $nW9->Signed = $this->Signed;
            $nW9->Signature_Date = $this->Signature_Date;
            $nW9->Revision_ID = $this->Revision_ID;
            $nW9->Verified = $this->Verified;
            if ($nW9->validate()) {
                $nW9->save();
            }
        }
    }

    /**
     * Get last company's w9 document of owner
     * @param $clientID
     * @param $docNumber
     * @return array|CActiveRecord|mixed|null
     */
    public static function getCompanyW9Doc($clientID, $docNumber = 1)
    {
        $clientID = intval($clientID);
        $condition = new CDbCriteria();
        $condition->select = "t.*, concat(IF(SUBSTR(Revision_ID, 4, 2)>=60, concat('19',SUBSTR(Revision_ID, 4, 2)), concat('20',SUBSTR(Revision_ID, 4, 2))), SUBSTR(Revision_ID, 2, 2)) revision";
        $condition->join = "LEFT JOIN w9 ON w9.Document_ID=t.Document_ID";
        $condition->condition = "w9.Client_ID='" . $clientID . "'";
        $condition->addCondition("t.Document_Type='" . Documents::W9 . "'");
        $condition->addCondition("w9.Revision_ID>='0'");
        $condition->addCondition("w9.W9_Owner_ID='" .  Yii::app()->user->clientID . "'");
        $condition->order = "revision DESC, t.Created DESC";
        $condition->offset = $docNumber - 1;
        $condition->limit = 1;
        $lastDocument = Documents::model()->find($condition);

        return $lastDocument;
    }

    /**
     * Get count of W9 uploaded in certain year
     * @param $year
     * @return array|int|mixed|null
     */
    public static function getCountOfAvailableW9sOfYear($year)
    {
        $year = intval($year);
        $condition = new CDbCriteria();
        $condition->select = "count(*) as 	Client_ID";
        $condition->join = "LEFT JOIN w9 ON w9.Document_ID=t.Document_ID";
        $condition->addCondition("t.Document_Type='" . Documents::W9 . "'");
        $condition->addCondition("t.Created LIKE '$year%'");
        $condition->addCondition("w9.Revision_ID>='0'");
        $condition->addCondition("w9.W9_Owner_ID='" .  Yii::app()->user->clientID . "'");
        $condition->limit = 1;
        $countDocs = Documents::model()->find($condition);

        if ($countDocs) {
            $countDocs = $countDocs->Client_ID;
        } else {
            $countDocs = 0;
        }

        return $countDocs;
    }


    public static function getAvailableW9sOfYear($year)
    {
        $year = intval($year);
        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN w9 ON w9.Document_ID=t.Document_ID";
        $condition->addCondition("t.Document_Type='" . Documents::W9 . "'");
        $condition->addCondition("t.Created LIKE '$year%'");
        $condition->addCondition("w9.Revision_ID>='0'");
        $condition->addCondition("w9.W9_Owner_ID='" .  Yii::app()->user->clientID . "'");
        $w9Docs = Documents::model()->findAll($condition);

        return $w9Docs;
    }

    /**
     * Get last company's w9 of owner
     * @param $clientID
     * @param null $ownerID
     * @param int $docNumber
     * @return array|CActiveRecord|mixed|null
     */
    public static function getCompanyW9($clientID, $ownerID = null, $docNumber = 1)
    {
        $clientID = intval($clientID);
        $condition = new CDbCriteria();
        $condition->select = "t.*, concat(IF(SUBSTR(Revision_ID, 4, 2)>=60, concat('19',SUBSTR(Revision_ID, 4, 2)), concat('20',SUBSTR(Revision_ID, 4, 2))), SUBSTR(Revision_ID, 2, 2)) revision";
        $condition->condition = "t.Client_ID='" . $clientID . "'";
        $condition->addCondition("t.Revision_ID>='0'");
        $condition->addCondition("t.W9_Owner_ID='" .  ($ownerID ? $ownerID : Yii::app()->user->clientID) . "'");
        $condition->order = "revision DESC, t.W9_ID DESC";
        $condition->offset = $docNumber - 1;
        $condition->limit = 1;
        $lastW9 = W9::model()->find($condition);
        return $lastW9;
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

		$criteria->compare('W9_ID',$this->W9_ID);
		$criteria->compare('Document_ID',$this->Document_ID);
		$criteria->compare('Client_ID',$this->Client_ID);
		$criteria->compare('W9_Data_Entry',$this->W9_Data_Entry);
		$criteria->compare('Tax_Class',$this->Tax_Class,true);
        $criteria->compare('Business_Name',$this->Business_Name,true);
		$criteria->compare('Exempt',$this->Exempt);
		$criteria->compare('Account_Nums',$this->Account_Nums,true);
		$criteria->compare('Signed',$this->Signed);
		$criteria->compare('Signature_Date',$this->Signature_Date,true);
		$criteria->compare('Revision_ID',$this->Revision_ID);
		$criteria->compare('Verified',$this->Verified);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return W9 the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public static function getGetNextTempFedIDNumber($type) {
        $gen = Generators::model()->findByAttributes(array(
            'Gen_Type'=>$type
        ));

        if ($type==1) {$prefix = 'IN-';}
        if ($type==2) {$prefix = 'T0-';}

        return $prefix.str_pad($gen->Gen_Next_Value, 7, '0', STR_PAD_LEFT);
    }

    public static function createNewFromSessionData($current_upload_file,$client){

        if (file_exists($current_upload_file['filepath'])) {
            // create document

            $document = new Documents();
            $document->Document_Type = $current_upload_file['doctype'];
            $document->User_ID = Yii::app()->user->userID;
            $document->Client_ID = Yii::app()->user->clientID;
            $document->Project_ID = Yii::app()->user->projectID;
            $document->Created = date("Y-m-d H:i:s");
            $document->save();
            $new_doc_id=$document->Document_ID;

            Audits::LogAction($document->Document_ID ,Audits::ACTION_UPLOAD);

            // insert image
            $image = new Images();
            $imageData = addslashes(fread(fopen($current_upload_file['filepath'],"rb"),filesize($current_upload_file['filepath'])));
            //$imageData = FileModification::ImageToPdfByFilePath($current_upload_file['filepath']);
            $image->Document_ID = $document->Document_ID;
            $image->Img = $imageData;
            $image->File_Name = $current_upload_file['name'];
            $image->Mime_Type = $current_upload_file['mimetype'];
            $image->File_Hash = sha1_file($current_upload_file['filepath']);
            $image->File_Size = intval(filesize($current_upload_file['filepath']));
            $image->Pages_Count = FileModification::calculatePagesByPath($current_upload_file['filepath']);

            $image->save();

            $infile = @file_get_contents($current_upload_file['filepath'], FILE_BINARY);
            if (($current_upload_file['mimetype'] == 'application/pdf' && $image->findPdfText($infile) == '')
                || $current_upload_file['mimetype'] != 'application/pdf') {
                Documents::crateDocumentThumbnail($current_upload_file['filepath'], 'thumbs', $current_upload_file['mimetype'], $document->Document_ID, 80);
            }

            // delete file from temporary catalog
            unlink($current_upload_file['filepath']);
        }

        $fedId = trim($current_upload_file['fed_id']);
        $newCompanyName = trim($current_upload_file['company_name']);

        // get company info
        $company = Companies::model()->with('client')->findByAttributes(array(
            'Company_Fed_ID' => $fedId,
        ));

        // create w9
        $W9 = new W9();
        $W9->Document_ID = $document->Document_ID;
        $W9->W9_Owner_ID = Yii::app()->user->clientID;
        $W9->Creator_ID = Yii::app()->user->userID;
        $W9->Business_Name = trim($current_upload_file['bus_name']);
        $W9->Tax_Class =  trim($current_upload_file['tax_name']);

        // get user info
        $user = Users::model()->with('person')->findByPk(Yii::app()->user->userID);

        if ($company) {
            // if company exisits
            $client = $company->client;

            //fill created company with dataentry values from session
            Companies::fillWithSessionDataEntry($company,$current_upload_file);

            $existingW9 = W9::model()->findByAttributes(array(
                'Client_ID' => $client->Client_ID,
                'W9_Owner_ID' => Yii::app()->user->clientID,
            ));

            if ($existingW9) {
                $W9->Revision_ID = -1;
            } else {
                $W9->Revision_ID = 0;
            }

            $vendor = Vendors::model()->findByAttributes(array(
                'Client_Client_ID' => Yii::app()->user->clientID,
                'Vendor_Client_ID' => $client->Client_ID,
            ));

            if (isset($vendor->Active_Relationship) && $vendor->Active_Relationship == Vendors::NOT_ACTIVE_RELATIONSHIP) {
                $vendor->Active_Relationship = Vendors::ACTIVE_RELATIONSHIP;
                $vendor->save();
            } else if (!$vendor && Yii::app()->user->clientID != 0 && Yii::app()->user->clientID != $client->Client_ID) {
                $vendor = new Vendors();
                $vendor->Vendor_ID_Shortcut = '';
                $vendor->Vendor_Client_ID = $client->Client_ID;
                $vendor->Client_Client_ID = Yii::app()->user->clientID;
                $vendor->Vendor_Name_Checkprint = '';
                $vendor->Vendor_1099 = '';
                $vendor->Vendor_Default_GL = '';
                $vendor->Vendor_Default_GL_Note = '';
                $vendor->Vendor_Note_General = '';
                $vendor->save();
            }
        } else {
            //if company does not exists, create new company

            $company_model = Companies::model()->findByPk($client->Company_ID);
            //fill created company with dataentry values from session
            Companies::fillWithSessionDataEntry($company_model,$current_upload_file);

            if (Yii::app()->user->clientID != 0) {
                $vendor = new Vendors();
                $vendor->Vendor_ID_Shortcut = '';
                $vendor->Vendor_Client_ID = $company_model->Company_ID;
                $vendor->Client_Client_ID = Yii::app()->user->clientID;
                $vendor->Vendor_Name_Checkprint = '';
                $vendor->Vendor_1099 = '';
                $vendor->Vendor_Default_GL = '';
                $vendor->Vendor_Default_GL_Note = '';
                $vendor->Vendor_Note_General = '';
                $vendor->save();
            }

            $W9->Revision_ID = 0;
        }

        // save w9
        $W9->Client_ID = $client->Client_ID;
        $W9->save();

        return $W9;

    }

    public static function getW9ByClientID($client_id) {

        $client = Clients::model()->findByPk($client_id);
        //0) find company of a client
        $company = Companies::model()->findByPk($client->Company_ID);

        //1) find current fed_id
        $cur_fed_id = $company->Company_Fed_ID;

        //2) find w9 with this fed id
        $sql = "select companies.Company_Fed_ID, companies.Company_ID, companies.Company_Name,
                addresses.Address1,  addresses.City,  addresses.State,  addresses.ZIP,
                t.* from w9 t
                LEFT JOIN documents ON documents.Document_ID=t.Document_ID
                LEFT JOIN clients ON t.Client_ID=clients.Client_ID
                LEFT JOIN companies ON clients.Company_ID=companies.Company_ID
                LEFT JOIN company_addresses ON company_addresses.Company_ID = companies.Company_ID
                LEFT JOIN addresses ON addresses.Address_ID = company_addresses.Address_ID
                where
                (documents.Client_ID>'0')   AND (companies.Company_Fed_ID LIKE '".$cur_fed_id."')";


        $list= Yii::app()->db->createCommand($sql)->queryAll();

        return $list;
    }
}
