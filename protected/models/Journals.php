<?php

/**
 * This is the model class for table "journals".
 *
 * The followings are the available columns in table 'journals':
 * @property integer $JE_ID
 * @property integer $Document_ID
 * @property string $JE_Date
 * @property string $JE_Number
 * @property string $JE_Transaction_ID
 * @property string $JE_Desc
 */
class Journals extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'journals';
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
			array('JE_Number, JE_Transaction_ID', 'length', 'max'=>45),
			array('JE_Desc', 'length', 'max'=>255),
			array('JE_Date', 'safe'),
            array('JE_ID', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('JE_ID, Document_ID, JE_Date, JE_Number, JE_Transaction_ID, JE_Desc', 'safe', 'on'=>'search'),
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
			'JE_ID' => 'Je',
			'Document_ID' => 'Document',
			'JE_Date' => 'Je Date',
			'JE_Number' => 'Je Number',
			'JE_Transaction_ID' => 'Je Transaction',
			'JE_Desc' => 'Je Desc',
		);
	}

    /**
     * Find JEs to entry data
     */
    public static function findJEsToEntry()
    {
        $queryString = $_SESSION['last_je_to_entry_search']['query'];
        $options =  $_SESSION['last_je_to_entry_search']['options'];

        $condition = new CDbCriteria();
        $condition->join = "right JOIN documents ON documents.Document_ID=t.Document_ID";
        $condition->condition = "t.JE_Number = '0'";

        $countCond = 0;
        if (count($options) > 0 && trim($queryString) != '') {
            $search_condition = new CDbCriteria();

            if ($options['search_option_jdate']) {
                $search_condition->compare('t.JE_Date', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_jnumber']) {
                $search_condition->compare('t.JE_Number', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_transaction_num']) {
                $search_condition->compare('t.JE_Transaction_ID', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_desc']) {
                $search_condition->compare('t.JE_Desc', $queryString, true, 'OR');
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

        $jes = Journals::model()->findAll($condition);

        return $jes;
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

		$criteria->compare('JE_ID',$this->JE_ID);
		$criteria->compare('Document_ID',$this->Document_ID);
		$criteria->compare('JE_Date',$this->JE_Date,true);
		$criteria->compare('JE_Number',$this->JE_Number,true);
		$criteria->compare('JE_Transaction_ID',$this->JE_Transaction_ID,true);
		$criteria->compare('JE_Desc',$this->JE_Desc,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Journals the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * Resets search options to def values
     */
    public static function resetDataentrySearchOptions() {
        $_SESSION['last_je_to_entry_search']['query'] = '';
        $_SESSION['last_je_to_entry_search']['options'] = array(
            'search_option_jdate' => 1,
            'search_option_jnumber' => 0,
            'search_option_transaction_num' => 0,
            'search_option_desc' => 1
        );
    }


    /*
     * Sets session variables according to inputted search string
     */
    public static function initDataentrySearchOptions($post) {
        $queryString = trim($post['search_field']);
        $options = array(
            'search_option_jdate' => (isset($post['search_option_jdate']) ? 1 : 0),
            'search_option_jnumber' => (isset($post['search_option_jnumber']) ? 1 : 0),
            'search_option_transaction_num' => (isset($post['search_option_transaction_num']) ? 1 : 0),
            'search_option_desc' => (isset($post['search_option_desc']) ? 1 : 0),

        );

        // set last search query params to session
        $_SESSION['last_je_to_entry_search']['query'] = $queryString;
        $_SESSION['last_je_to_entry_search']['options'] = $options;

    }

}
