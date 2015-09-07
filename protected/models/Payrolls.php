<?php

/**
 * This is the model class for table "payrolls".
 *
 * The followings are the available columns in table 'payrolls':
 * @property integer $Payroll_ID
 * @property integer $Document_ID
 * @property integer $Payroll_Type
 * @property string $Week_Ending
 * @property integer $Submitted
 * @property string $Version
 */
class Payrolls extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'payrolls';
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
			array('Document_ID, Submitted, Payroll_Type_ID', 'numerical', 'integerOnly'=>true),
			array('Version', 'length', 'max'=>50),
            array('Week_Ending', 'date', 'format' => 'yyyy-MM-dd'),
            array('Payroll_ID', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Payroll_ID, Document_ID, Payroll_Type_ID, Week_Ending, Submitted, Version', 'safe', 'on'=>'search'),
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
			'Payroll_ID' => 'Payroll',
			'Document_ID' => 'Document',
			'Payroll_Type_ID' => 'Payroll Type',
			'Week_Ending' => 'Week Ending',
			'Submitted' => 'Submitted',
			'Version' => 'Version',
		);
	}

    /**
     * Find PCs to entry data
     */
    public static function findPayrollsToEntry()
    {
        $queryString = $_SESSION['last_payr_to_entry_search']['query'];
        $options =  $_SESSION['last_payr_to_entry_search']['options'];

        $condition = new CDbCriteria();
        $condition->join = "inner JOIN documents ON documents.Document_ID=t.Document_ID";
        $condition->condition = "t.Payroll_Type_ID = '0'";
        $condition->addCondition("t.Week_Ending is null", 'OR');

        $countCond = 0;
        if (count($options) > 0 && trim($queryString) != '') {
            $search_condition = new CDbCriteria();
            $search_condition->join = 'LEFT JOIN payroll_types ON t.Payroll_Type_ID = payroll_types.Payroll_Type_ID ';


            if ($options['search_option_week_end']) {
                $search_condition->compare('t.Week_Ending', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_type']) {
                $search_condition->compare('payroll_types.Title', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_submitted']) {
                $search_condition->compare('t.Submitted', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_version']) {
                $search_condition->compare('t.Version', $queryString, true, 'OR');
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

        $payrolls = Payrolls::model()->findAll($condition);

        return $payrolls;
    }

    /**
     * Delete Payroll with rows in relative tables
     * @param $payrollID
     */
    public static function deletePayroll($payrollID)
    {
        $payroll = Payrolls::model()->with('document.image')->findByPk($payrollID);
        if ($payroll) {
            $document = $payroll->document;
            $image = $document->image;
            $image->delete();
            $document->delete();

            // delete thumbnail
            $filePath = 'protected/data/thumbs/' . $payroll->Document_ID . '.jpg';
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            // delete library links
            LibraryDocs::deleteDocumentLinks($payroll->Document_ID);

            $payroll->delete();
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

		$criteria->compare('Payroll_ID',$this->Payroll_ID);
		$criteria->compare('Document_ID',$this->Document_ID);
		$criteria->compare('Payroll_Type_ID',$this->Payroll_Type_ID);
		$criteria->compare('Week_Ending',$this->Week_Ending,true);
		$criteria->compare('Submitted',$this->Submitted);
		$criteria->compare('Version',$this->Version,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Payrolls the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * Resets search options to def values
     */
    public static function resetDataentrySearchOptions() {
        $_SESSION['last_payr_to_entry_search']['query'] = '';
        $_SESSION['last_payr_to_entry_search']['options'] = array(
            'search_option_week_end' => 1,
            'search_option_type' => 1,
            'search_option_submitted' => 0,
            'search_option_version' => 0
        );
    }

    /*
     * Sets session variables according to inputted search string
     */
    public static function initDataentrySearchOptions($post) {
        $queryString = trim($post['search_field']);
        $options = array(
            'search_option_week_end' => (isset($post['search_option_week_end']) ? 1 : 0),
            'search_option_type' => (isset($post['search_option_type']) ? 1 : 0),
            'search_option_submitted' => (isset($post['search_option_submitted']) ? 1 : 0),
            'search_option_version' => (isset($post['search_option_version']) ? 1 : 0),
        );


        // set last search query params to session
        $_SESSION['last_payr_to_entry_search']['query'] = $queryString;
        $_SESSION['last_payr_to_entry_search']['options'] = $options;

    }



}
