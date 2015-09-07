<?php

/**
 * This is the model class for table "persons".
 *
 * The followings are the available columns in table 'persons':
 * @property integer $Person_ID
 * @property string $First_Name
 * @property string $Last_Name
 * @property string $Email
 * @property string $Mobile_Phone
 * @property string $Direct_Phone
 * @property string $Direct_Fax
 */
class Persons extends CActiveRecord
{

    public $Email_Confirmation;

    /**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'persons';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('First_Name, Last_Name, Email', 'required'),
			array('First_Name, Last_Name', 'checkCharacters'),
			array('First_Name, Last_Name', 'length', 'max'=>45),
			array('Email', 'length', 'max'=>80),
            array('Email_Confirmation', 'required'),
            array('Email,Email_Confirmation', 'email'),
            array('Email_Confirmation', 'compare','compareAttribute'=>'Email','message'=>'Emails do not match - please re-enter'),
			array('Mobile_Phone, Direct_Phone, Direct_Fax', 'length', 'max'=>30),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Person_ID, First_Name, Last_Name, Email, Mobile_Phone, Direct_Phone, Direct_Fax', 'safe', 'on'=>'search'),
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
            'adresses'=>array(self::MANY_MANY, 'Addresses', 'person_addresses(Person_ID, Address_ID)'),
            'user'=>array(self::HAS_ONE, 'Users', 'Person_ID'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'Person_ID' => 'Database generated unique Person number',
			'First_Name' => 'First Name',
			'Last_Name' => 'Last Name',
			'Email' => 'Email address',
			'Email_Confirmation' => 'Email Confirm.',
			'Mobile_Phone' => 'Person mobile phone',
			'Direct_Phone' => 'Direct line phone',
			'Direct_Fax' => 'Direct line fax',
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

		$criteria->compare('Person_ID',$this->Person_ID);
		$criteria->compare('First_Name',$this->First_Name,true);
		$criteria->compare('Last_Name',$this->Last_Name,true);
		$criteria->compare('Email',$this->Email,true);
		$criteria->compare('Mobile_Phone',$this->Mobile_Phone,true);
		$criteria->compare('Direct_Phone',$this->Direct_Phone,true);
		$criteria->compare('Direct_Fax',$this->Direct_Fax,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Persons the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    public function checkCharacters($attribute) {

            $str = $this->$attribute;

            if ($str) {
                //$pattern = '/^(?=.*[a-zA-Z0-9]).{5,}$/';
                $pattern = "/[a-zA-Z\.\-\'\"()]/";
                $str = preg_replace($pattern,'',$str);
                if (strlen(trim($str))>0) {
                    $this->addError($attribute, 'Field can contain only letters, quotes and minus sign' );
                }
            }

    }

}
