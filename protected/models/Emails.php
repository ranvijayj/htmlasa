<?php

/**
 * This is the model class for table "emails".
 *
 * The followings are the available columns in table 'emails':
 * @property integer $Email_ID
 * @property integer $Client_ID
 * @property integer $User_ID
 * @property integer $Project_ID
 * @property string $Email_Send_DateTime
 * @property string $Email_Address
 */
class Emails extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'emails';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Client_ID, User_ID, Project_ID, Email_Address', 'required'),
			array('Client_ID, User_ID, Project_ID', 'numerical', 'integerOnly'=>true),
			array('Email_Address', 'length', 'max'=>255),
			array('Email_Address', 'email'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Email_ID, Client_ID, User_ID, Project_ID, Email_Send_DateTime, Email_Address', 'safe', 'on'=>'search'),
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
			'Email_ID' => 'Email',
			'Client_ID' => 'Client',
			'User_ID' => 'User',
			'Project_ID' => 'Project',
			'Email_Send_DateTime' => 'Email Send Date Time',
			'Email_Address' => 'Email Address',
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

		$criteria->compare('Email_ID',$this->Email_ID);
		$criteria->compare('Client_ID',$this->Client_ID);
		$criteria->compare('User_ID',$this->User_ID);
		$criteria->compare('Project_ID',$this->Project_ID);
		$criteria->compare('Email_Send_DateTime',$this->Email_Send_DateTime,true);
		$criteria->compare('Email_Address',$this->Email_Address,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Emails the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public static function logEmailSending($client_id,$user_id,$project_id,$address) {
        $email = new Emails();
        $email->Client_ID = $client_id;
        $email->User_ID = $user_id;
        if (!is_int($project_id)) $project_id = 0;
        $email->Project_ID = $project_id;

        $email->Email_Address = $address;

        $email->save();

    }

}
