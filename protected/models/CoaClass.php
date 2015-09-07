<?php

/**
 * This is the model class for table "coa_class".
 *
 * The followings are the available columns in table 'coa_class':
 * @property integer $COA_Class_ID
 * @property integer $Project_ID
 * @property string $Class_Shortcut
 * @property string $Class_Name
 * @property integer $Class_Sort_Order
 * @property integer $Class_Default
 */
class CoaClass extends CActiveRecord
{
    /**
     * Default class values
     */
    const DEFAULT_CLASS = 1;
    const NOT_DEFAULT_CLASS = 0;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'coa_class';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Project_ID, Class_Shortcut, Class_Sort_Order', 'required'),
			array('Project_ID, Class_Sort_Order, Class_Default', 'numerical', 'integerOnly'=>true),
			array('Class_Shortcut', 'length', 'max'=>3),
			array('Class_Name', 'length', 'max'=>50),
            array('Class_Shortcut', 'uniqueForProject'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('COA_Class_ID, Project_ID, Class_Shortcut, Class_Name, Class_Sort_Order, Class_Default', 'safe', 'on'=>'search'),
		);
	}

    /**
     * Check Class_Shortcut rule
     */
    public function uniqueForProject() {
        $condition = new CDbCriteria();
        $condition->condition = "t.Project_ID = '" . $this->Project_ID . "'";
        $condition->addCondition("t.COA_Class_ID != '" . $this->COA_Class_ID . "'");
        $condition->addCondition("t.Class_Shortcut = '" . $this->Class_Shortcut . "'");
        $coaClass = CoaClass::model()->find($condition);
        if($coaClass) {
            $this->addError('Class_Shortcut','Class must be unique for Project');
        }
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
			'COA_Class_ID' => 'Coa Class',
			'Project_ID' => 'Project',
			'Class_Shortcut' => 'Class Shortcut',
			'Class_Name' => 'Class Name',
			'Class_Sort_Order' => 'Class Sort Order',
			'Class_Default' => 'Class Default',
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

		$criteria->compare('COA_Class_ID',$this->COA_Class_ID);
		$criteria->compare('Project_ID',$this->Project_ID);
		$criteria->compare('Class_Shortcut',$this->Class_Shortcut,true);
		$criteria->compare('Class_Name',$this->Class_Name,true);
		$criteria->compare('Class_Sort_Order',$this->Class_Sort_Order);
		$criteria->compare('Class_Default',$this->Class_Default);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CoaClass the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * Get List of Classes for a defined project
     */
    public static function getCoaClassesList($project_id) {
        /*$coa_classes = CoaClass::model()->findAllByAttributes(
            array('Project_ID' => $project_id)
        );*/
        /*$sql = 'select COA_Class_ID,Class_Shortcut from coa_class where Project_ID='.$project_id;
        $list= Yii::app()->db->createCommand($sql)->queryAll();
        $result = array();
        foreach ($list as $item) {
            $result[] = $item['Class_Shortcut'];
        }
        return $result;*/

        return CoaClass::model()->findAllByAttributes(
            array('Project_ID' => $project_id)
        );

    }

}
