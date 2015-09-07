<?php

/**
 * This is the model class for table "user_log".
 *
 * The followings are the available columns in table 'user_log':
 * @property integer Log_ID
 * @property integer $User_ID
 * @property string $Table_Name
 * @property integer $ROW_ID
 * @property string $Date_Time
 * @property integer $Event_Type
 */
class UserLog extends CActiveRecord
{
    /**
     * Events
     */
    const LOGIN = 0;
    const LOGOUT = 1;
    const INSERT = 2;
    const UPDATE = 3;
    const W9_VERIFIED_TO_0 = 4;
    const W9_VERIFIED_TO_1 = 5;


    /**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'user_log';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('User_ID, Date_Time', 'required'),
            array('ROW_ID', 'safe'),
			array('Event_Type, Log_ID', 'numerical', 'integerOnly'=>true),
			array('Table_Name', 'length', 'max'=>100),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Log_ID, User_ID, Table_Name, ROW_ID, Date_Time, Event_Type', 'safe', 'on'=>'search'),
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
            'event_type'=>array(self::BELONGS_TO, 'UserEvents', 'Event_Type'),
            'user'=>array(self::BELONGS_TO, 'Users', 'User_ID'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array (
            'Log_ID' => 'Log ID',
			'User_ID' => 'User ID from Users table',
			'Table_Name' => 'Identifies table where record was inserted or updated',
			'ROW_ID' => 'Contains primary key number of updated or inserted record.',
			'Date_Time' => 'Time stamp',
			'Event_Type' => 'Event type number from User_Events table',
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

        $criteria->compare('Log_ID',$this->Log_ID);
		$criteria->compare('User_ID',$this->User_ID);
		$criteria->compare('Table_Name',$this->Table_Name,true);
		$criteria->compare('ROW_ID',$this->ROW_ID);
		$criteria->compare('Date_Time',$this->Date_Time,true);
		$criteria->compare('Event_Type',$this->Event_Type);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
            'pagination'=>array(
                'pageSize'=>30,
            ),

        ));
	}

    /**
     * Creates new record in Users' log
     * @param $userId
     * @param $tableName
     * @param $rowId
     * @param $eventType
     */
    public static function createLogRecord($userId, $tableName, $rowId, $eventType)
    {
        $userEvent = new UserLog();
        $userEvent->Date_Time = date("Y-m-d H:i:s");
        $userEvent->Event_Type = $eventType;
        $userEvent->ROW_ID = $rowId;
        $userEvent->Table_Name = $tableName;
        $userEvent->User_ID = $userId;
        $userEvent->save();
    }

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return UserLog the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
