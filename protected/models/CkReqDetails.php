<?php

/**
 * This is the model class for table "ck_req_details".
 *
 * The followings are the available columns in table 'ck_req_details':
 * @property integer $AP_Form_ID
 * @property integer $AP_ID
 * @property integer $CK_Req_Purchase
 * @property integer $CK_Req_Rental
 * @property string $Rental_Begin
 * @property string $Rental_End
 * @property integer $Sign_Requested_By
 * @property integer $Sign_Dept_Approval
 * @property integer $Sign_UPM_Executive
 * @property integer $Sign_Accounting
 */
class CkReqDetails extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'ck_req_details';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('AP_ID', 'required'),
			array('AP_ID, CK_Req_Purchase, CK_Req_Rental, Sign_Requested_By, Sign_Dept_Approval, Sign_UPM_Executive, Sign_Accounting, Sent_To_Apprvl', 'numerical', 'integerOnly'=>true),
			array('Rental_Begin, Rental_End', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('AP_Form_ID, AP_ID, CK_Req_Purchase, CK_Req_Rental, Rental_Begin, Rental_End, Sign_Requested_By, Sign_Dept_Approval, Sign_UPM_Executive, Sign_Accounting, Sent_To_Apprvl', 'safe', 'on'=>'search'),
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
			'AP_Form_ID' => 'Ap Form',
			'AP_ID' => 'Ap',
			'CK_Req_Purchase' => 'Ck Req Purchase',
			'CK_Req_Rental' => 'Ck Req Rental',
			'Rental_Begin' => 'Rental Begin',
			'Rental_End' => 'Rental End',
            'Sign_Requested_By' => 'Requested By',
            'Sign_Dept_Approval' => 'Dept. Approval',
            'Sign_UPM_Executive' => 'UPM/Executive',
            'Sign_Accounting' => 'Accounting',
            'Sent_To_Apprvl' => 'Sent To Approval'
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

		$criteria->compare('AP_Form_ID',$this->AP_Form_ID);
		$criteria->compare('AP_ID',$this->AP_ID);
		$criteria->compare('CK_Req_Purchase',$this->CK_Req_Purchase);
		$criteria->compare('CK_Req_Rental',$this->CK_Req_Rental);
		$criteria->compare('Rental_Begin',$this->Rental_Begin,true);
		$criteria->compare('Rental_End',$this->Rental_End,true);
		$criteria->compare('Sign_Requested_By',$this->Sign_Requested_By);
		$criteria->compare('Sign_Dept_Approval',$this->Sign_Dept_Approval);
		$criteria->compare('Sign_UPM_Executive',$this->Sign_UPM_Executive);
		$criteria->compare('Sign_Accounting',$this->Sign_Accounting);
        $criteria->compare('Sent_To_Apprvl',$this->Sent_To_Apprvl);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CkReqDetails the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
