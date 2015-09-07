<?php

/**
 * This is the model class for table "folder_categories".
 *
 * The followings are the available columns in table 'folder_categories':
 * @property integer $Folder_Cat_ID
 * @property string $Category_Doc_Types
 * @property string $Full_Name
 * @property integer $Catgory_Type
 */
class FolderCategories extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'folder_categories';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Category_Doc_Types, Full_Name, Catgory_Type', 'required'),
			array('Catgory_Type', 'numerical', 'integerOnly'=>true),
			array('Category_Doc_Types', 'length', 'max'=>50),
			array('Full_Name', 'length', 'max'=>45),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Folder_Cat_ID, Category_Doc_Types, Full_Name, Catgory_Type', 'safe', 'on'=>'search'),
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
			'Folder_Cat_ID' => 'Folder Cat',
			'Category_Doc_Types' => 'Category Doc Types',
			'Full_Name' => 'Full Name',
			'Catgory_Type' => 'Catgory Type',
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

		$criteria->compare('Folder_Cat_ID',$this->Folder_Cat_ID);
		$criteria->compare('Category_Doc_Types',$this->Category_Doc_Types,true);
		$criteria->compare('Full_Name',$this->Full_Name,true);
		$criteria->compare('Catgory_Type',$this->Catgory_Type);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return FolderCategories the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
