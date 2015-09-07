<?php

/**
 * This is the model class for table "ccs".
 *
 * The followings are the available columns in table 'ccs':
 * @property integer $CC_ID
 * @property integer $User_ID
 * @property string $CC_Name
 * @property string $CC_Type_ID
 * @property integer $Exp_Month
 * @property string $Exp_Year
 * @property string $CC_Number
 * @property string $Last4
 */
class Ccs extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'ccs';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('User_ID, CC_Name, Exp_Month, Exp_Year, CC_Number, CC_Type_ID', 'required'),
			array('User_ID, Exp_Month, CC_Type_ID, Exp_Year', 'numerical', 'integerOnly'=>true),
			array('CC_Name', 'length', 'max'=>26),
			array('Exp_Year', 'length', 'max'=>4),
            array('Exp_Year', 'length', 'min'=>4),
            array('CC_Type_ID', 'checkcctype'),
            array('Exp_Month', 'checkexpmonth'),
            array('CC_Number', 'checkccnumber'),
            array('Last4', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('CC_ID, User_ID, CC_Name, CC_Type_ID, Exp_Month, Exp_Year, CC_Number, Last4', 'safe', 'on'=>'search'),
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
            'type'=>array(self::BELONGS_TO, 'CcTypes', 'CC_Type_ID'),
		);
	}

    /**
     * On save event
     * @return bool
     */
    protected function beforeSave () {
        $this->Last4 = substr($this->CC_Number, strlen($this->CC_Number) - 4, 4);
        return parent::beforeSave();
    }

    /**
     * Validation rule for Credit Cart Type
     */
    public function checkCCType()
    {
        if($this->CC_Type_ID == 0) {
            $this->addError('CC_Type_ID','Chose Card Type');
        }
    }

    /**
     * Validation rule for ExpMonth
     */
    public function checkExpMonth()
    {
        if($this->Exp_Month == 0) {
            $this->addError('Exp_Month','Chose Exp. Month');
        }
    }

    /**
     * Validation rule for CC_Number
     */
    public function checkCCNumber()
    {
        if(!preg_match('/^\d{4}\-\d{4}\-\d{4}\-\d{4}$/', $this->CC_Number) && !preg_match('/^\d{16}$/', $this->CC_Number)) {
            $this->addError('CC_Number','Invalid Card Number');
        }
    }

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'CC_ID' => 'Cc',
			'User_ID' => 'User',
			'CC_Name' => 'Card Name',
			'CC_Type_ID' => 'Card Type',
			'Exp_Month' => 'Exp. Month',
			'Exp_Year' => 'Exp. Year',
			'CC_Number' => 'Card Number',
			'Last4' => 'Last 4 Digits',
		);
	}

    /**
     * Get User's credit card
     * @param $userID
     * @return Ccs|null
     */
    public static function getUserCreditCard($userID)
    {
        $cCard = self::model()->with('type')->findByAttributes(array(
            'User_ID' => $userID,
        ));
        return $cCard;
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

		$criteria->compare('CC_ID',$this->CC_ID);
		$criteria->compare('User_ID',$this->User_ID);
		$criteria->compare('CC_Name',$this->CC_Name,true);
		$criteria->compare('CC_Type_ID',$this->CC_Type_ID);
		$criteria->compare('Exp_Month',$this->Exp_Month);
		$criteria->compare('Exp_Year',$this->Exp_Year,true);
		$criteria->compare('CC_Number',$this->CC_Number,true);
		$criteria->compare('Last4',$this->Last4,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Ccs the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
