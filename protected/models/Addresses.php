<?php

/**
 * This is the model class for table "addresses".
 *
 * The followings are the available columns in table 'addresses':
 * @property integer $Address_ID
 * @property string $Address1
 * @property string $Address2
 * @property string $City
 * @property string $State
 * @property string $ZIP
 * @property string $Country
 * @property string $Phone
 * @property string $Fax
 */
class Addresses extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'addresses';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Address1, Address2, City, Country', 'length', 'max'=>45),
			array('Address1, Address2', 'checkCharacters','mode'=>'address'),
			array('City', 'checkCharacters','mode'=>'city'),
			array('State', 'length', 'max'=>45),
			array('ZIP', 'length', 'max'=>15),
			array('Phone, Fax', 'length', 'max'=>30),
            array('Address1, City, State, ZIP', 'required', 'on'=>'required_fields'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Address_ID, Address1, Address2, City, State, ZIP, Country, Phone, Fax', 'safe', 'on'=>'search'),
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
			'Address_ID' => 'Database generated unique Address number',
			'Address1' => 'Street Address',
			'Address2' => 'Address line 2',
			'City' => 'City',
			'State' => 'State',
			'ZIP' => 'Postal Code',
			'Country' => 'Country',
			'Phone' => 'Phone',
			'Fax' => 'Fax',
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

		$criteria->compare('Address_ID',$this->Address_ID);
		$criteria->compare('Address1',$this->Address1,true);
		$criteria->compare('Address2',$this->Address2,true);
		$criteria->compare('City',$this->City,true);
		$criteria->compare('State',$this->State,true);
		$criteria->compare('ZIP',$this->ZIP,true);
		$criteria->compare('Country',$this->Country,true);
		$criteria->compare('Phone',$this->Phone,true);
		$criteria->compare('Fax',$this->Fax,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Addresses the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public function checkCharacters($attribute,$params) {
        if ($params['mode'] == 'address') {
            $str = $this->$attribute;

            if ($str) {
                //$pattern = '/^(?=.*[a-zA-Z0-9]).{5,}$/';
                $pattern = "/[0-9]/";
                $str = preg_replace($pattern,'',$str);
                if (strlen(trim($str))==0) {
                    $this->addError($attribute, 'Field can\'t contain only digits!' );
                }
            }
        }
        if ($params['mode'] == 'city') {
            $str = $this->$attribute;

            if ($str) {
                //$pattern = '/^(?=.*[a-zA-Z0-9]).{5,}$/';
                $pattern = "/[a-zA-Z\.\-]/";
                $str = preg_replace($pattern,'',$str);
                if (strlen(trim($str))>0) {
                    $this->addError($attribute, 'Only letters, dots and minus sign allowed!' );
                }
            }
        }


    }
}
