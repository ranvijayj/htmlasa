<?php

/**
 * This is the model class for table "generators".
 *
 * The followings are the available columns in table 'generators':
 * @property integer $Gen_ID
 * @property integer $Gen_Type
 * @property integer $Gen_Next_Value
 * @property string $Description
 */
class Generators extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'generators';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Gen_Type, Gen_Next_Value, Description', 'required'),
			array('Gen_Type, Gen_Next_Value', 'numerical', 'integerOnly'=>true),
			array('Description', 'length', 'max'=>50),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Gen_ID, Gen_Type, Gen_Next_Value, Description', 'safe', 'on'=>'search'),
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
			'Gen_ID' => 'Gen',
			'Gen_Type' => 'Gen Type',
			'Gen_Next_Value' => 'Gen Next Value',
			'Description' => 'Description',
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

		$criteria->compare('Gen_ID',$this->Gen_ID);
		$criteria->compare('Gen_Type',$this->Gen_Type);
		$criteria->compare('Gen_Next_Value',$this->Gen_Next_Value);
		$criteria->compare('Description',$this->Description,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Generators the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    public  static  function updateNumberByType($type) {

        $gen = Generators::model()->findByAttributes(array(
           'Gen_Type'=>$type
        ));
        $gen->Gen_Next_Value++;
        $gen->save();

    }
}
