<?php

/**
 * This is the model class for table "customers".
 *
 * The followings are the available columns in table 'customers':
 * @property integer $Customer_ID
 * @property integer $Vendor_Client_ID
 * @property integer $Client_Client_ID
 * @property string $Cust_ID_Shortcut
 * @property string $Cust_Name_ARprint
 * @property string $Cust_Default_GL
 * @property string $Cust_Default_GL_Note
 * @property string $Cust_Note_General
 * @property integer $Cust_Active_Relationship
 */
class Customers extends CActiveRecord
{
    /**
     * Relationship types
     */
    const ACTIVE_RELATIONSHIP = 1;
    const NOT_ACTIVE_RELATIONSHIP = 1;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'customers';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Vendor_Client_ID, Client_Client_ID', 'required'),
			array('Vendor_Client_ID, Client_Client_ID, Cust_Active_Relationship', 'numerical', 'integerOnly'=>true),
			array('Cust_ID_Shortcut', 'length', 'max'=>15),
			array('Cust_Name_ARprint', 'length', 'max'=>50),
			array('Cust_Default_GL, Cust_Default_GL_Note', 'length', 'max'=>40),
			array('Cust_Note_General', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Customer_ID, Vendor_Client_ID, Client_Client_ID, Cust_ID_Shortcut, Cust_Name_ARprint, Cust_Default_GL, Cust_Default_GL_Note, Cust_Note_General, Cust_Active_Relationship', 'safe', 'on'=>'search'),
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
            'client' => array(self::BELONGS_TO, 'Clients', 'Vendor_Client_ID'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'Customer_ID' => 'Customer',
			'Vendor_Client_ID' => 'Vendor Client',
			'Client_Client_ID' => 'Client Client',
			'Cust_ID_Shortcut' => 'Cust Id Shortcut',
			'Cust_Name_ARprint' => 'Cust Name Arprint',
			'Cust_Default_GL' => 'Cust Default Gl',
			'Cust_Default_GL_Note' => 'Cust Default Gl Note',
			'Cust_Note_General' => 'Cust Note General',
			'Cust_Active_Relationship' => 'Cust Active Relationship',
		);
	}


    public static function getClientCustomersList($clientID)
    {
        $customers = array();

        $condition = new CDbCriteria();
        $condition->condition = "t.Client_Client_ID = '" . $clientID . "'";
        $condition->addCondition("t.Cust_Active_Relationship = '" . self::ACTIVE_RELATIONSHIP . "'");
        $condition->order = 'company.Company_Name ASC';
        $customersRes = Customers::model()->with('client.company')->findAll($condition);

        if ($customersRes) {
            foreach ($customersRes as $customer) {
                $customers[$customer->Customer_ID] =  ($customer->Cust_ID_Shortcut ? $customer->Cust_ID_Shortcut . ' - ' : '') . $customer->client->company->Company_Name;
            }
        }

        return $customers;
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

		$criteria->compare('Customer_ID',$this->Customer_ID);
		$criteria->compare('Vendor_Client_ID',$this->Vendor_Client_ID);
		$criteria->compare('Client_Client_ID',$this->Client_Client_ID);
		$criteria->compare('Cust_ID_Shortcut',$this->Cust_ID_Shortcut,true);
		$criteria->compare('Cust_Name_ARprint',$this->Cust_Name_ARprint,true);
		$criteria->compare('Cust_Default_GL',$this->Cust_Default_GL,true);
		$criteria->compare('Cust_Default_GL_Note',$this->Cust_Default_GL_Note,true);
		$criteria->compare('Cust_Note_General',$this->Cust_Note_General,true);
		$criteria->compare('Cust_Active_Relationship',$this->Cust_Active_Relationship);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Customers the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
