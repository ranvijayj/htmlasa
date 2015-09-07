<?php

/**
 * This is the model class for table "coa_structure".
 *
 * The followings are the available columns in table 'coa_structure':
 * @property integer $Project_ID
 * @property integer $COA_Prefix
 * @property integer $COA_Head
 * @property integer $COA_Modifier
 * @property integer $COA_Root
 * @property integer $COA_Conjun
 * @property integer $COA_Tail
 * @property integer $COA_Suffix
 * @property string $COA_Prefix_Val
 * @property string $COA_Modifier_Val
 * @property string $COA_Conjun_Val
 * @property string $COA_Suffix_Val
 * @property integer $COA_Allow_Manual_Coding
 * @property string $COA_Break_Character
 * @property integer $COA_Break_Number
 */
class CoaStructure extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'coa_structure';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Project_ID', 'required'),
			array('Project_ID, COA_Prefix, COA_Head, COA_Modifier, COA_Root, COA_Conjun, COA_Tail, COA_Suffix, COA_Allow_Manual_Coding, COA_Break_Number', 'numerical', 'integerOnly'=>true),
			array('COA_Prefix_Val, COA_Modifier_Val, COA_Conjun_Val, COA_Suffix_Val', 'length', 'max'=>9),
			array('COA_Break_Character', 'length', 'max'=>1),
			array('COA_Break_Character', 'nonalfanumeric'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Project_ID, COA_Prefix, COA_Head, COA_Modifier, COA_Root, COA_Conjun, COA_Tail, COA_Suffix, COA_Prefix_Val, COA_Modifier_Val, COA_Conjun_Val, COA_Suffix_Val, COA_Allow_Manual_Coding, COA_Break_Character, COA_Break_Number', 'safe', 'on'=>'search'),
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
			'Project_ID' => 'Project',
			'COA_Prefix' => 'Coa Prefix',
			'COA_Head' => 'Coa Head',
			'COA_Modifier' => 'Coa Modifier',
			'COA_Root' => 'Coa Root',
			'COA_Conjun' => 'Coa Conjun',
			'COA_Tail' => 'Coa Tail',
			'COA_Suffix' => 'Coa Suffix',
			'COA_Prefix_Val' => 'Coa Prefix Val',
			'COA_Modifier_Val' => 'Coa Modifier Val',
			'COA_Conjun_Val' => 'Coa Conjun Val',
			'COA_Suffix_Val' => 'Coa Suffix Val',
			'COA_Allow_Manual_Coding' => 'Coa Allow Manual Coding',
			'COA_Break_Character' => 'COA Break Character',
			'COA_Break_Number' => 'COA Break Number',
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

		$criteria->compare('Project_ID',$this->Project_ID);
		$criteria->compare('COA_Prefix',$this->COA_Prefix);
		$criteria->compare('COA_Head',$this->COA_Head);
		$criteria->compare('COA_Modifier',$this->COA_Modifier);
		$criteria->compare('COA_Root',$this->COA_Root);
		$criteria->compare('COA_Conjun',$this->COA_Conjun);
		$criteria->compare('COA_Tail',$this->COA_Tail);
		$criteria->compare('COA_Suffix',$this->COA_Suffix);
		$criteria->compare('COA_Prefix_Val',$this->COA_Prefix_Val,true);
		$criteria->compare('COA_Modifier_Val',$this->COA_Modifier_Val,true);
		$criteria->compare('COA_Conjun_Val',$this->COA_Conjun_Val,true);
		$criteria->compare('COA_Suffix_Val',$this->COA_Suffix_Val,true);
		$criteria->compare('COA_Allow_Manual_Coding',$this->COA_Allow_Manual_Coding);
		$criteria->compare('COA_Break_Character',$this->COA_Break_Character,true);
		$criteria->compare('COA_Break_Number',$this->COA_Break_Number);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CoaStructure the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public function nonalfanumeric($attribute) {

        if(ctype_alnum($this->$attribute))
            $this->addError($attribute, 'should be non alphanumerical');
    }
}
