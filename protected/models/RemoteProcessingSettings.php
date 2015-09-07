<?php

/**
 * This is the model class for table "remote_processing_settings".
 *
 * The followings are the available columns in table 'remote_processing_settings':
 * @property integer $rps_id
 * @property double $SetupFee
 * @property double $DigitalTimeCost
 * @property double $DigitalSizeCost
 * @property double $DigitalPageCost
 * @property double $AnalogColouredPageCost
 * @property double $AnalogBWPageCost
 * @property double $AnalogMultiplier
 */
class RemoteProcessingSettings extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'remote_processing_settings';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('SetupFee, DigitalTimeCost, DigitalSizeCost, DigitalPageCost, AnalogColouredPageCost, AnalogBWPageCost, AnalogMultiplier', 'required'),
			array('SetupFee, DigitalTimeCost, DigitalSizeCost, DigitalPageCost, AnalogColouredPageCost, AnalogBWPageCost, AnalogMultiplier', 'numerical'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('rps_id, SetupFee, DigitalTimeCost, DigitalSizeCost, DigitalPageCost, AnalogColouredPageCost, AnalogBWPageCost, AnalogMultiplier', 'safe', 'on'=>'search'),
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
			'rps_id' => 'Rps',
			'SetupFee' => 'Setup Fee',
			'DigitalTimeCost' => 'Digital Time Cost',
			'DigitalSizeCost' => 'Digital Size Cost',
			'DigitalPageCost' => 'Digital Page Cost',
			'AnalogColouredPageCost' => 'Analog Coloured Page Cost',
			'AnalogBWPageCost' => 'Analog Bwpage Cost',
			'AnalogMultiplier' => 'Analog Multiplier',
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

		$criteria->compare('rps_id',$this->rps_id);
		$criteria->compare('SetupFee',$this->SetupFee);
		$criteria->compare('DigitalTimeCost',$this->DigitalTimeCost);
		$criteria->compare('DigitalSizeCost',$this->DigitalSizeCost);
		$criteria->compare('DigitalPageCost',$this->DigitalPageCost);
		$criteria->compare('AnalogColouredPageCost',$this->AnalogColouredPageCost);
		$criteria->compare('AnalogBWPageCost',$this->AnalogBWPageCost);
		$criteria->compare('AnalogMultiplier',$this->AnalogMultiplier);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return RemoteProcessingSettings the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
