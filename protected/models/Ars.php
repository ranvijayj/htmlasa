<?php

/**
 * This is the model class for table "ars".
 *
 * The followings are the available columns in table 'ars':
 * @property integer $AR_ID
 * @property integer $Document_ID
 * @property integer $Customer_ID
 * @property string $Company_Name
 * @property string $Invoice_Date
 * @property string $Invoice_Number
 * @property string $Invoice_Amount
 * @property string $Description
 * @property string $Terms
 */
class Ars extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'ars';
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
			array('Document_ID, Customer_ID', 'numerical', 'integerOnly'=>true),
			array('Company_Name', 'length', 'max'=>80),
			array('Invoice_Number', 'length', 'max'=>32),
			array('Invoice_Amount', 'length', 'max'=>13),
			array('Description', 'length', 'max'=>255),
			array('Terms', 'length', 'max'=>100),
            array('Invoice_Date', 'date', 'format' => 'yyyy-MM-dd'),
            array('AR_ID', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('AR_ID, Document_ID, Customer_ID, Company_Name, Invoice_Date, Invoice_Number, Invoice_Amount, Description, Terms', 'safe', 'on'=>'search'),
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
			'AR_ID' => 'Ar',
			'Document_ID' => 'Document',
			'Customer_ID' => 'Customer',
			'Company_Name' => 'Company Name',
			'Invoice_Date' => 'Invoice Date',
			'Invoice_Number' => 'Invoice Number',
			'Invoice_Amount' => 'Invoice Amount',
			'Description' => 'Description',
			'Terms' => 'Terms',
		);
	}

    /**
     * Find ARs to entry data
     */
    public static function findARsToEntry()
    {
        $queryString = $_SESSION['last_ar_to_entry_search']['query'];
        $options =  $_SESSION['last_ar_to_entry_search']['options'];

        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN documents ON documents.Document_ID=t.Document_ID";
        $condition->condition = "t.Invoice_Number = '0'";

        $countCond = 0;
        if (count($options) > 0 && trim($queryString) != '') {
            $search_condition = new CDbCriteria();


            if ($options['search_option_com_name']) {
                $search_condition->compare('t.Company_Name', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_inv_num']) {
                $search_condition->compare('t.Invoice_Number', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_inv_date']) {
                $search_condition->compare('t.Invoice_Date', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_inv_amount']) {
                $search_condition->compare('t.Invoice_Amount', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_descr']) {
                $search_condition->compare('t.Description', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_terms']) {
                $search_condition->compare('t.Terms', $queryString, true, 'OR');
                $countCond++;
            }
        }



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

        $condition->order = "documents.Created ASC";

        if( $countCond > 0 ) $condition->mergeWith($search_condition);

        $ars = Ars::model()->findAll($condition);

        return $ars;
    }

    /**
     * Delete AR with rows in relative tables
     * @param $arID
     */
    public static function deleteAR($arID)
    {
        $ar = Ars::model()->with('document.image')->findByPk($arID);
        if ($ar) {
            $document = $ar->document;
            $image = $document->image;
            $image->delete();
            $document->delete();

            // delete thumbnail
            $filePath = 'protected/data/thumbs/' . $ar->Document_ID . '.jpg';
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            // delete library links
            LibraryDocs::deleteDocumentLinks($ar->Document_ID);

            $ar->delete();
        }
    }

    /**
     * Get set of Company Names from ARs table for certain client
     * @param $clientID
     * @return array
     */
    public static function getPreviousCompanyNamesForClient($clientID)
    {
        $sql = "SELECT distinct `Company_Name`
                FROM `ars`
                LEFT JOIN `documents` ON `documents`.`Document_ID` = `ars`.`Document_ID`
                WHERE `documents`.`Client_ID` = '$clientID' AND `ars`.`Company_Name` IS NOT NULL";

        $connection=Yii::app()->db;
        $command=$connection->createCommand($sql);
        $compNames=$command->queryAll();
        return $compNames;
    }

    /**
     * Get set of Terms from ARs table for certain client
     * @param $clientID
     * @return array
     */
    public static function getPreviousTermsForClient($clientID)
    {
        $sql = "SELECT distinct `Terms`
                FROM `ars`
                LEFT JOIN `documents` ON `documents`.`Document_ID` = `ars`.`Document_ID`
                WHERE `documents`.`Client_ID` = '$clientID' AND `ars`.`Terms` IS NOT NULL";

        $connection=Yii::app()->db;
        $command=$connection->createCommand($sql);
        $compNames=$command->queryAll();
        return $compNames;
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

		$criteria->compare('AR_ID',$this->AR_ID);
		$criteria->compare('Document_ID',$this->Document_ID);
		$criteria->compare('Customer_ID',$this->Customer_ID);
		$criteria->compare('Company_Name',$this->Company_Name,true);
		$criteria->compare('Invoice_Date',$this->Invoice_Date,true);
		$criteria->compare('Invoice_Number',$this->Invoice_Number,true);
		$criteria->compare('Invoice_Amount',$this->Invoice_Amount,true);
		$criteria->compare('Description',$this->Description,true);
		$criteria->compare('Terms',$this->Terms,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Ars the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * Resets search options to def values
     */
    public static function resetDataentrySearchOptions() {
        $_SESSION['last_ar_to_entry_search']['query'] = '';
        $_SESSION['last_ar_to_entry_search']['options'] = array(
            'search_option_com_name' => 1,
            'search_option_inv_num' => 1,
            'search_option_inv_date' => 1,
            'search_option_inv_date' => 1,
            'search_option_descr' => 0,
            'search_option_terms' => 0,
        );
    }

    /*
     * Sets session variables according to inputted search string
     */
    public static function initDataentrySearchOptions($post) {
        $queryString = trim($post['search_field']);
        $options = array(
            'search_option_com_name' => (isset($post['search_option_com_name']) ? 1 : 0),
            'search_option_inv_num' => (isset($post['search_option_inv_num']) ? 1 : 0),
            'search_option_inv_date' => (isset($post['search_option_inv_date']) ? 1 : 0),
            'search_option_inv_amount' => (isset($post['search_option_inv_amount']) ? 1 : 0),
            'search_option_descr' => (isset($post['search_option_descr']) ? 1 : 0),
            'search_option_terms' => (isset($post['search_option_terms']) ? 1 : 0),
        );

        // set last search query params to session
        $_SESSION['last_ar_to_entry_search']['query'] = $queryString;
        $_SESSION['last_ar_to_entry_search']['options'] = $options;

    }


}
