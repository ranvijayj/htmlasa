<?php

/**
 * This is the model class for table "stripe_customers".
 *
 * The followings are the available columns in table 'stripe_customers':
 * @property integer $User_ID
 * @property string $Customer_ID
 */
class StripeCustomers extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'stripe_customers';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('User_ID, Customer_ID', 'required'),
			array('User_ID', 'numerical', 'integerOnly'=>true),
			array('Customer_ID', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('User_ID, Customer_ID', 'safe', 'on'=>'search'),
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
			'User_ID' => 'User',
			'Customer_ID' => 'Customer',
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

		$criteria->compare('User_ID',$this->User_ID);
		$criteria->compare('Customer_ID',$this->Customer_ID,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return StripeCustomers the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    protected function beforeSave () {
        $this->Customer_ID= Encryptor::encrypt($this->Customer_ID);
        return parent::beforeSave();
    }

    /**
     *
     */
    protected function afterFind () {
        $this->Customer_ID= Encryptor::decrypt($this->Customer_ID);
        return parent::afterFind();
    }


}
