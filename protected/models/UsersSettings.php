<?php

/**
 * This is the model class for table "users_settings".
 *
 * The followings are the available columns in table 'users_settings':
 * @property integer $Settings_ID
 * @property integer $User_ID
 * @property integer $Notification
 * @property string $Default_Doc_Type
 * @property integer $Default_Project
 * @property integer $Default_Bank_Acct
 * @property string $Default_Export_Type
 * @property string $Default_Export_Format
 * @property integer $Automatic_CC_Charge
 * @property integer $Use_Device_Checking
 * @property integer $Default_W9_Share_Type
 */

class UsersSettings extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
    static $w9ShareTypes = array(
        0 => 'Viewing W9',
        1 =>'Viewing W9 and granting access for W9 to other companies.',
    );

    public function tableName()
	{
		return 'users_settings';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('User_ID', 'required'),
			array('User_ID, Notification, Default_Project, Default_Bank_Acct, Automatic_CC_Charge', 'numerical', 'integerOnly'=>true),
			array('Default_Doc_Type', 'length', 'max'=>2),
			array('Use_Device_Checking','numerical', 'integerOnly'=>true),
			array('Default_Export_Type', 'length', 'max'=>5),
			array('Due_Date_Terms,Default_W9_Share_Type', 'numerical', 'integerOnly'=>true),
			array('Default_Export_Format', 'length', 'max'=>10),
            array('Automatic_CC_Charge', 'checkStripeCustomer','except' => 'newClientScenario,update'),   //for new user stripe-checking is not performed
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Settings_ID, User_ID, Notification, Default_Doc_Type, Default_Project, Default_Bank_Acct, Default_Export_Type, Default_Export_Format, Automatic_CC_Charge, Due_Date_Terms', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'Settings_ID' => 'Settings',
			'User_ID' => 'User',
			'Notification' => 'Notification',
			'Default_Doc_Type' => 'Default Doc Type',
			'Default_Project' => 'Default Project',
			'Default_Bank_Acct' => 'Default Bank Acct',
			'Default_Export_Type' => 'Default Export Type',
			'Default_Export_Format' => 'Default Export Format',
			'Automatic_CC_Charge' => 'Automatic Cc Charge',
            'Due_Date_Terms' => 'Due Date Terms',
		);
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

		$criteria->compare('Settings_ID',$this->Settings_ID);
		$criteria->compare('User_ID',$this->User_ID);
		$criteria->compare('Notification',$this->Notification);
		$criteria->compare('Default_Doc_Type',$this->Default_Doc_Type,true);
		$criteria->compare('Default_Project',$this->Default_Project);
		$criteria->compare('Default_Bank_Acct',$this->Default_Bank_Acct);
		$criteria->compare('Default_Export_Type',$this->Default_Export_Type,true);
		$criteria->compare('Default_Export_Format',$this->Default_Export_Format,true);
		$criteria->compare('Automatic_CC_Charge',$this->Automatic_CC_Charge);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return UsersSettings the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}


    public  function  checkStripeCustomer()
    {
        $stripeCustomer = StripeCustomers::model()->findByPk(Yii::app()->user->userID);
        if(!$stripeCustomer) {
           $this->addError('Automatic_CC_Charge','You have no CC account yet');
        }


    }
}
