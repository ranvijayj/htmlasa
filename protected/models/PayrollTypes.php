<?php

/**
 * This is the model class for table "payroll_types".
 *
 * The followings are the available columns in table 'payroll_types':
 * @property integer $Payroll_Type_ID
 * @property string $Title
 */
class PayrollTypes extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'payroll_types';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Title', 'required'),
			array('Title', 'length', 'max'=>10),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Payroll_Type_ID, Title', 'safe', 'on'=>'search'),
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
			'Payroll_Type_ID' => 'Payroll Type',
			'Title' => 'Title',
		);
	}

    /**
     * Get All Payroll Types List
     * @return array
     */
    public static function getPayrollTypesList()
    {
        $payrollTypes = array();

        $types = PayrollTypes::model()->findAll();

        if ($types) {
            foreach ($types as $type) {
                $payrollTypes[$type->Payroll_Type_ID] = $type->Title;
            }
        }

        return $payrollTypes;
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

		$criteria->compare('Payroll_Type_ID',$this->Payroll_Type_ID);
		$criteria->compare('Title',$this->Title,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PayrollTypes the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
