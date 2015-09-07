<?php

/**
 * This is the model class for table "support_requests".
 *
 * The followings are the available columns in table 'support_requests':
 * @property integer $Request_ID
 * @property string $User_Login
 * @property string $User_Email
 * @property string $User_Phone
 * @property integer $User_Message
 * @property integer $User_Device_ID
 * @property string $Created
 * @property string $Problem_Status
 * @property integer $Problem_Category
 */
class SupportRequests extends CActiveRecord
{


    /**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'support_requests';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('User_Login, User_Email, User_Device_ID, Problem_Category', 'required'),
			array('User_Message, User_Device_ID ', 'numerical', 'integerOnly'=>true),
			array('User_Login, User_Phone', 'length', 'max'=>30),
			array('Problem_Category', 'length', 'max'=>30),
			array('User_Email', 'email'),
			array('Problem_Status', 'length', 'max'=>1),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Request_ID, User_Login, User_Email, User_Phone, User_Message, User_Device_ID, Created, Problem_Status, Problem_Category', 'safe', 'on'=>'search'),
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


    public static function getNextSupportID(){

    }
    /**
     * @return array
     */
    public static function getProblemCategories(){
        $result = array(
            '1'=>'Login problem (Login from new device)',
            '2'=>'Login problem (I forgot my password)',
        );
        return $result;
    }
	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'Request_ID' => 'Request',
			'User_Login' => 'User Login',
			'User_Email' => 'User Email',
			'User_Phone' => 'User Phone',
			'User_Message' => 'User Message',
			'User_Device_ID' => 'User Device',
			'Created' => 'Created',
			'Problem_Status' => 'Problem Status',
			'Problem_Category' => 'Problem Category',
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

		$criteria->compare('Request_ID',$this->Request_ID);
		$criteria->compare('User_Login',$this->User_Login,true);
		$criteria->compare('User_Email',$this->User_Email,true);
		$criteria->compare('User_Phone',$this->User_Phone,true);
		$criteria->compare('User_Message',$this->User_Message);
		$criteria->compare('User_Device_ID',$this->User_Device_ID);
		$criteria->compare('Created',$this->Created,true);
		$criteria->compare('Problem_Status',$this->Problem_Status,true);
		$criteria->compare('Problem_Category',$this->Problem_Category);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return SupportRequests the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
