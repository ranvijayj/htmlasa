<?php

/**
 * This is the model class for table "cc_types".
 *
 * The followings are the available columns in table 'cc_types':
 * @property integer $CC_Type_ID
 * @property string $CC_Type
 * @property string $Description
 */
class CcTypes extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'cc_types';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('CC_Type, Description', 'required'),
			array('CC_Type', 'length', 'max'=>12),
			array('Description', 'length', 'max'=>80),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('CC_Type_ID, CC_Type, Description', 'safe', 'on'=>'search'),
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
			'CC_Type_ID' => 'Cc Type',
			'CC_Type' => 'Cc Type',
			'Description' => 'Description',
		);
	}

    /**
     * Get Credit Card types options
     */
    public static function getCCTypesOptions()
    {
        $ccTypesOptions = array();
        $ccTypes = self::model()->findAll();
        foreach($ccTypes as $ccType) {
            $ccTypesOptions[$ccType->CC_Type_ID] = $ccType->Description;
        }
        return $ccTypesOptions;
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

		$criteria->compare('CC_Type_ID',$this->CC_Type_ID);
		$criteria->compare('CC_Type',$this->CC_Type,true);
		$criteria->compare('Description',$this->Description,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CcTypes the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
