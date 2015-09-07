<?php

/**
 * This is the model class for table "bank_acct_nums".
 *
 * The followings are the available columns in table 'bank_acct_nums':
 * @property integer $Account_Num_ID
 * @property integer $Client_ID
 * @property string $Account_Number
 * @property string $Account_Name
 * @property string $Bank_Name
 * @property string $Bank_Routing
 * @property string $Bank_SWIFT
 */
class BankAcctNums extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'bank_acct_nums';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
            array('Client_ID, Project_ID, Account_Number, Account_Name, Bank_Name', 'required'),
			array('Client_ID, Project_ID', 'numerical', 'integerOnly'=>true),
			array('Account_Number, Account_Name, Bank_Name, Bank_Routing, Bank_SWIFT', 'length', 'max'=>45),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Account_Num_ID, Client_ID, Project_ID, Account_Number, Account_Name, Bank_Name, Bank_Routing, Bank_SWIFT', 'safe', 'on'=>'search'),
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
            'project'=>array(self::BELONGS_TO, 'Projects', 'Project_ID'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'Account_Num_ID' => 'Account Num',
			'Client_ID' => 'Client',
            'Project_ID' => 'Project',
			'Account_Number' => 'Account Number',
			'Account_Name' => 'Account Name',
			'Bank_Name' => 'Bank Name',
			'Bank_Routing' => 'Bank Routing',
			'Bank_SWIFT' => 'SWIFT',
		);
	}


    /**
     * Get client's bank accounts
     * @param int $lastNumberDigits
     * @param bool $withInitItem
     * @return mixed
     */
    public static function getClientAccountNumbers($lastNumberDigits = 0, $withInitItem = true)
    {
        $bankAccountNums = array();

        if ($withInitItem) {
            $bankAccountNums[0] = 'Select an Account';
        }

        if (Yii::app()->user->projectID == 'all') {
            $bankAccounts = BankAcctNums::model()->findAllByAttributes(array(
                'Client_ID' => Yii::app()->user->clientID,
            ));
        } else {
            $bankAccounts = BankAcctNums::model()->findAllByAttributes(array(
                'Client_ID' => Yii::app()->user->clientID,
                'Project_ID' => Yii::app()->user->projectID,
            ));
        }


        if ($bankAccounts) {
            foreach ($bankAccounts as $bankAccount) {
                if ($lastNumberDigits == 0 || (strlen($bankAccount->Account_Number) <= $lastNumberDigits)) {
                    $bankAccountNums[$bankAccount->Account_Num_ID] = $bankAccount->Account_Name . ' / ' . $bankAccount->Account_Number;
                } else {
                    $bankAccountNums[$bankAccount->Account_Num_ID] = $bankAccount->Account_Name . ' / ' . substr($bankAccount->Account_Number, strlen($bankAccount->Account_Number) - $lastNumberDigits, $lastNumberDigits);
                }
            }
        }

        return $bankAccountNums;
    }

    /**
     * Get all user projects for settings tab
     * @return array
     */
    public static function getAllUserBankAcctsForSettingsTab()
    {
        $userProjects = array(0 => "No Default Account");

        $condition = new CDbCriteria();
        $condition->condition = "users_project_list.User_ID = '" . Yii::app()->user->userID . "'";
        $condition->join = "LEFT JOIN projects ON projects.Project_ID = t.Project_ID
                            LEFT JOIN users_project_list ON users_project_list.Project_ID = t.Project_ID";
        $condition->order = "company.Company_Name ASC, project.Project_Name ASC, t.Account_Name ASC";
        $bankAccts = BankAcctNums::model()->with('client.company', 'project')->findAll($condition);

        foreach($bankAccts as $bankAcct) {
            $userProjects[$bankAcct->Account_Num_ID] = $bankAcct->client->company->Company_Name . ' / ' . $bankAcct->project->Project_Name . ' / ' . $bankAcct->Account_Name . ' / ' . $bankAcct->Account_Number;
        }

        return $userProjects;
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

		$criteria->compare('Account_Num_ID',$this->Account_Num_ID);
		$criteria->compare('Client_ID',$this->Client_ID);
        $criteria->compare('Project_ID',$this->Project_ID);
		$criteria->compare('Account_Number',$this->Account_Number,true);
		$criteria->compare('Account_Name',$this->Account_Name,true);
		$criteria->compare('Bank_Name',$this->Bank_Name,true);
		$criteria->compare('Bank_Routing',$this->Bank_Routing,true);
		$criteria->compare('Bank_SWIFT',$this->Bank_SWIFT,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return BankAcctNums the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
