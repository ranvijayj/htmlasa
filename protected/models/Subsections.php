<?php

/**
 * This is the model class for table "subsections".
 *
 * The followings are the available columns in table 'subsections':
 * @property integer $Subsection_ID
 * @property integer $Section_ID
 * @property string $Subsection_Name
 * @property integer $Subsection_Type
 * @property integer $Created_By
 */
class Subsections extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'subsections';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Section_ID, Subsection_Name, Created_By', 'required'),
			array('Section_ID, Subsection_Type, Created_By, Access_Type', 'numerical', 'integerOnly'=>true),
			array('Subsection_Name', 'length', 'max'=>50),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Subsection_ID, Section_ID, Subsection_Name, Subsection_Type, Created_By, Access_Type', 'safe', 'on'=>'search'),
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
            'section'=>array(self::BELONGS_TO, 'Sections', 'Section_ID'),
            'documents'=>array(self::HAS_MANY, 'LibraryDocs', 'Subsection_ID'),
            'user'=>array(self::BELONGS_TO, 'Users', 'Created_By'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'Subsection_ID' => 'Subsection',
			'Section_ID' => 'Section',
			'Subsection_Name' => 'Title',
			'Subsection_Type' => 'Subsection Type',
			'Created_By' => 'Created By',
            'Access_Type' => 'Access Type',
		);
	}


    /**
     * Prepare subsections with documents to review
     * @param $subsections
     * @param $section
     * @param $year
     * @return array
     */
    public static function prepareSubsectionsToView($subsections, $section, $year)
    {
        $result = array();
        foreach ($subsections as $subsection) {
            if ($section->folder_type->Folder_Cat_ID == Sections::PURCHASE_ORDER_LOG && isset($_SESSION['sort_po_by_vendor_name'])) {
                $documents = LibraryDocs::sortDocumentsPosBinder($subsection->Subsection_ID, 'alpha', true);
            } else {
                $condition = new CDbCriteria();
                $condition->condition = 't.Access_Type = ' . Storages::HAS_ACCESS;
                $condition->addCondition("document.User_ID = '" . Yii::app()->user->userID . "'", 'OR');
                $condition->addCondition("t.Subsection_ID = '" . $subsection->Subsection_ID . "'");

                if (Yii::app()->user->id == 'user') {
                    $condition->addCondition("document.User_ID= '" . Yii::app()->user->userID . "'");
                }

                $condition->order = "t.Sort_Numb ASC";

                $documents = LibraryDocs::model()->with('document')->findAll($condition);
            }
            $documents = LibraryDocs::prepareDocumentsOfSubsectionToView($documents, $section->Vendor_ID, $section->Folder_Cat_ID, $year);

            $result[] = array(
                'subsection' => $subsection,
                'documents' => $documents,
            );
        }

        return $result;
    }

    /**
     * Delete subsection
     * @param $id
     */
    public static function deleteSubsection($id)
    {
        $subsection = Subsections::model()->findByPk($id);
        LibraryDocs::model()->deleteAllByAttributes(array(
            'Subsection_ID' => $subsection->Subsection_ID,
        ));
        $subsection->delete();
    }

    /**
     * Add subsection to section
     * @param $sectionID
     * @param $title
     * @param $storageType
     * @param $userID
     * @param $access
     */
    public static function addSubsectionToSection($sectionID, $title, $storageType, $userID, $access)
    {
        $sectionID = intval($sectionID);
        $title = trim($title);
        $userID = intval($userID);
        $access = intval($access);
        $storageType = intval($storageType);
        if ($sectionID > 0 && $title != '' && $userID >= 0 && $access >= 0 && $access <= 1 && $storageType >= 0 && $storageType <= 1) {
            $subsection = new Subsections();
            $subsection->Section_ID = $sectionID;
            $subsection->Subsection_Name = $title;
            $subsection->Subsection_Type = $storageType;
            $subsection->Created_By = $userID;
            $subsection->Access_Type = $access;
            if ($subsection->validate()) {
                $subsection->save();
            }
        }
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

		$criteria->compare('Subsection_ID',$this->Subsection_ID);
		$criteria->compare('Section_ID',$this->Section_ID);
		$criteria->compare('Subsection_Name',$this->Subsection_Name,true);
		$criteria->compare('Subsection_Type',$this->Subsection_Type);
		$criteria->compare('Created_By',$this->Created_By);
        $criteria->compare('Access_Type',$this->Access_Type);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Subsections the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
