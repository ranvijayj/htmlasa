<?php

/**
 * This is the model class for table "po_formatting".
 *
 * The followings are the available columns in table 'po_formatting':
 * @property integer $PO_Form_ID
 * @property integer $Project_ID
 * @property string $PO_Format_Client_Name
 * @property string $PO_Format_Project_Name
 * @property string $PO_Format_Address
 * @property string $PO_Format_City_St_ZIP
 * @property string $PO_Format_Phone
 * @property string $PO_Format_Addl_Language
 * @property integer $PO_Format_Starting_Num
 * @property string $PO_Format_Job_Name
 * @property integer $PO_Format_Sig_Req
 */
class PoFormatting extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'po_formatting';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Project_ID, PO_Format_Client_Name, PO_Format_Project_Name', 'required'),
			array('Project_ID, PO_Format_Starting_Num, PO_Format_Sig_Req', 'numerical', 'integerOnly'=>true),
			array('PO_Format_Client_Name, PO_Format_Project_Name, PO_Format_Address, PO_Format_Phone, PO_Format_Job_Name', 'length', 'max'=>45),
			array('PO_Format_City_St_ZIP', 'length', 'max'=>100),
			array('PO_Format_Addl_Language', 'length', 'max'=>500),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('PO_Form_ID, Project_ID, PO_Format_Client_Name, PO_Format_Project_Name, PO_Format_Address, PO_Format_City_St_ZIP, PO_Format_Phone, PO_Format_Addl_Language, PO_Format_Starting_Num, PO_Format_Job_Name, PO_Format_Sig_Req', 'safe', 'on'=>'search'),
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
			'PO_Form_ID' => 'Po Form',
			'Project_ID' => 'Project',
			'PO_Format_Client_Name' => 'Client Name',
			'PO_Format_Project_Name' => 'Project Name',
			'PO_Format_Address' => 'Address',
			'PO_Format_City_St_ZIP' => 'City/St/Zip',
			'PO_Format_Phone' => 'Phone',
			'PO_Format_Addl_Language' => 'Add. Language',
			'PO_Format_Starting_Num' => 'Starting Num',
			'PO_Format_Job_Name' => 'Job Name',
			'PO_Format_Sig_Req' => 'Show Add. Language',
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

		$criteria->compare('PO_Form_ID',$this->PO_Form_ID);
		$criteria->compare('Project_ID',$this->Project_ID);
		$criteria->compare('PO_Format_Client_Name',$this->PO_Format_Client_Name,true);
		$criteria->compare('PO_Format_Project_Name',$this->PO_Format_Project_Name,true);
		$criteria->compare('PO_Format_Address',$this->PO_Format_Address,true);
		$criteria->compare('PO_Format_City_St_ZIP',$this->PO_Format_City_St_ZIP,true);
		$criteria->compare('PO_Format_Phone',$this->PO_Format_Phone,true);
		$criteria->compare('PO_Format_Addl_Language',$this->PO_Format_Addl_Language,true);
		$criteria->compare('PO_Format_Starting_Num',$this->PO_Format_Starting_Num);
		$criteria->compare('PO_Format_Job_Name',$this->PO_Format_Job_Name,true);
		$criteria->compare('PO_Format_Sig_Req',$this->PO_Format_Sig_Req);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PoFormatting the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
