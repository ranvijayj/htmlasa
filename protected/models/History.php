<?php

/**
 * This is the model class for table "history".
 *
 * The followings are the available columns in table 'history':
 * @property string $History_ID
 * @property integer $Event_Type_ID
 * @property string $Timestamp
 * @property string $User_Login
 * @property string $Client_ID
 * @property string $AP_ID
 */
class History extends CActiveRecord
{
	const PAYMENT_APPROVAL_EVENT = '101';


    /**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'history';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Event_Type_ID, Timestamp', 'required'),
			array('Event_Type_ID', 'numerical', 'integerOnly'=>true),
			array('User_Login', 'length', 'max'=>16),
			array('Client_ID, AP_ID', 'length', 'max'=>10),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('History_ID, Event_Type_ID, Timestamp, User_Login, Client_ID, AP_ID', 'safe', 'on'=>'search'),
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
			'History_ID' => 'History',
			'Event_Type_ID' => 'Event Type',
			'Timestamp' => 'Timestamp',
			'User_Login' => 'User Login',
			'Client_ID' => 'Client',
			'AP_ID' => 'Ap',
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

		$criteria->compare('History_ID',$this->History_ID,true);
		$criteria->compare('Event_Type_ID',$this->Event_Type_ID);
		$criteria->compare('Timestamp',$this->Timestamp,true);
		$criteria->compare('User_Login',$this->User_Login,true);
		$criteria->compare('Client_ID',$this->Client_ID,true);
		$criteria->compare('AP_ID',$this->AP_ID,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return History the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public static function Log($event,$datetime,$login,$client_id){

        $history = new History();
        $history->User_Login = $login;
        $history->Timestamp = $datetime;
        $history->Event_Type_ID = $event;
        $history->Client_ID = $client_id;
        $history->save();

    }

}
