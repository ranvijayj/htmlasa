<?php

/**
 * This is the model class for table "mails_to_delivery".
 *
 * The followings are the available columns in table 'mails_to_delivery':
 * @property integer $Message_ID
 * @property string $Email
 * @property string $Subject
 * @property string $Body
 * @property string $Headers
 */
class MailsToDelivery extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'mails_to_delivery';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Email, Subject, Body, Headers', 'required'),
			array('Email', 'length', 'max'=>255),
			array('Subject', 'length', 'max'=>100),
			array('Headers', 'length', 'max'=>128),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Message_ID, Email, Subject, Body, Headers', 'safe', 'on'=>'search'),
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
			'Message_ID' => 'Message',
			'Email' => 'Email',
			'Subject' => 'Subject',
			'Body' => 'Body',
			'Headers' => 'Headers',
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

		$criteria->compare('Message_ID',$this->Message_ID);
		$criteria->compare('Email',$this->Email,true);
		$criteria->compare('Subject',$this->Subject,true);
		$criteria->compare('Body',$this->Body,true);
		$criteria->compare('Headers',$this->Headers,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return MailsToDelivery the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    public static function add($email, $subject, $message, $headers) {
        $mail_to_deliv = new MailsToDelivery();
        $mail_to_deliv->Email = $email;
        $mail_to_deliv->Subject = $subject;
        $mail_to_deliv->Body = $message;
        $mail_to_deliv->Headers = $headers;
        $mail_to_deliv->save();

    }
}
