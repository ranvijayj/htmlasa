<?php

/**
 * This is the model class for table "users_answers".
 *
 * The followings are the available columns in table 'users_answers':
 * @property integer $Answer_ID
 * @property integer $Question_ID
 * @property string $Hashed_Answer
 * @property string $Hint
 * @property integer $User_ID
 */
class UsersAnswers extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'users_answers';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Question_ID, Hashed_Answer, Hint, User_ID', 'required'),
			array('Question_ID, User_ID', 'numerical', 'integerOnly'=>true),
			array('Hashed_Answer', 'length', 'max'=>100),
			array('Hint', 'length', 'max'=>50),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Answer_ID, Question_ID, Hashed_Answer, Hint, User_ID', 'safe', 'on'=>'search'),
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
            'questions' => array(self::HAS_ONE, 'UsersQuestions', array('Question_ID'=>'Question_ID')),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'Answer_ID' => 'Answer',
			'Question_ID' => 'Question',
			'Hashed_Answer' => 'Hashed Answer',
			'Hint' => 'Hint',
			'User_ID' => 'User',
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

		$criteria->compare('Answer_ID',$this->Answer_ID);
		$criteria->compare('Question_ID',$this->Question_ID);
		$criteria->compare('Hashed_Answer',$this->Hashed_Answer,true);
		$criteria->compare('Hint',$this->Hint,true);
		$criteria->compare('User_ID',$this->User_ID);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return UsersAnswers the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public static function CheckAnswers($answers_array,$user_id) {
        $result = 1;
        foreach ($answers_array as $key=>$value) {
            $right_answ = UsersAnswers::model()->findByAttributes(array(
                'Question_ID'=>$key,
                 'User_ID' => $user_id
            ));

            if (sha1($value)==$right_answ->Hashed_Answer) {
                $result= $result*1;
            } else {
                $result= $result*0;
            }
        }

        return $result;
    }

    public static function makeHint($str) {
        if (strlen($str)<3) {
            for ($i = 0 ; $i<strlen($str); $i++) {
                $str[$i]='*';
            }
        } else {
            for ($i = 1 ; $i<strlen($str)-1; $i++) {
                $str[$i]='*';
            }
        }

        return $str;
    }

    public static  function setUserQuestionsAnswers ($user_id,$answers_array) {

        $error = 0;

        foreach ($answers_array as $key=>$value ) {

        try{
            $answer = new UsersAnswers();
            $answer->Question_ID = $key;
            $answer->User_ID = $user_id;
            $answer->Hashed_Answer = sha1($value);
            $answer->Hint = UsersAnswers::makeHint($value);
            $answer->save();

        } catch (Exception $e) {
            $error = 1;
        }

        }
        return $error;
    }


}
