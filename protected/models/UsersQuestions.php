<?php

/**
 * This is the model class for table "users_questions".
 *
 * The followings are the available columns in table 'users_questions':
 * @property integer $Question_ID
 * @property string $Text
 */
class UsersQuestions extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'users_questions';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Text', 'required'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Question_ID, Text', 'safe', 'on'=>'search'),
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
			'Question_ID' => 'Question',
			'Text' => 'Text',
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

		$criteria->compare('Question_ID',$this->Question_ID);
		$criteria->compare('Text',$this->Text,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return UsersQuestions the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public static function getUserQuestions ($user_id){

        $answers = UsersAnswers::model()->with('questions')->findAllByAttributes(array(
            'User_ID'=>$user_id
        ));

        $questions = array();
        foreach ($answers as $answer) {
            $questions[]= array('Question_ID'=>$answer->questions->Question_ID,'Text'=>$answer->questions->Text,'Hint'=>$answer->Hint);
        }

        if (count($questions)==3) {
            return $questions;
        } else {return array('0'=>array('Question_ID'=>0),'1'=>array('Question_ID'=>1),'2'=>array('Question_ID'=>2));}


    }

    public static function getCountUserQuestions ($user_id){

        $answers = UsersAnswers::model()->with('questions')->findAllByAttributes(array(
            'User_ID'=>$user_id
        ));

        $questions = array();
        foreach ($answers as $answer) {
            $questions[]= array('Question_ID'=>$answer->questions->Question_ID,'Text'=>$answer->questions->Text,'Hint'=>$answer->Hint);
        }

        return count($questions);



    }

    public static function getAllQuestions (){
        $questions = UsersQuestions::model()->findAll();
        $result = array();
        foreach ($questions as $question) {
            $result[$question->Question_ID]= $question->Text;
        }

        return $result;

    }

    public static function getAllQuestionsWithEmpty (){
        $questions = UsersQuestions::model()->findAll();
        $result[-1] = 'Select question';
        foreach ($questions as $question) {
            $result[$question->Question_ID]= $question->Text;
        }

        return $result;

    }
}
