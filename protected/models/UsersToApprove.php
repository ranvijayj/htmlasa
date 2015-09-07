<?php

/**
 * This is the model class for table "users_to_approve".
 *
 * The followings are the available columns in table 'users_to_approve':
 * @property integer $id
 * @property integer $User_ID
 * @property integer $New_Client
 * @property integer $Client_ID
 * @property integer $Approved_By_Admin
 * @property integer $Approved_By_Client_Admin
 */
class UsersToApprove extends CActiveRecord
{
    /**
     * Client types
     */
    const NEW_CLIENT = 1;
    const OLD_CLIENT = 0;

    /**
     * Approved by client-admin values
     */
    const APPR_BY_CLIENT_ADMIN = 1;
    const NOT_APPR_BY_CLIENT_ADMIN = 0;

    /**
     * Approved by admin values
     */
    const APPR_BY_ADMIN = 1;
    const NOT_APPR_BY_ADMIN = 0;

    /**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'users_to_approve';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('User_ID, New_Client, Client_ID', 'required'),
			array('User_ID, New_Client, Client_ID, Approved_By_Admin, Approved_By_Client_Admin', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, User_ID, New_Client, Client_ID, Approved_By_Admin, Approved_By_Client_Admin', 'safe', 'on'=>'search'),
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
            'user'=>array(self::BELONGS_TO, 'Users', 'User_ID', 'with' => 'person'),
            'client'=>array(self::BELONGS_TO, 'Clients', 'Client_ID', 'with' => 'company'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'User_ID' => 'User',
			'New_Client' => 'New Client',
			'Client_ID' => 'Client',
			'Approved_By_Admin' => 'Approved By Admin',
            'Approved_By_Client_Admin' => 'Approved By Client Admin',
		);
	}

    /**
     * Get clients users to approve, approved by admin before
     * @param $clientID
     * @return CActiveRecord[]
     */
    public function getClientUsersToApprove($clientID) {
        $usersToApprove = $this->with('user')->findAllByAttributes(array(
            'Client_ID'=>$clientID,
            'New_Client'=>self::OLD_CLIENT,
            'Approved_By_Admin'=>self::APPR_BY_ADMIN,
            'Approved_By_Client_Admin'=>self::APPR_BY_CLIENT_ADMIN,
        ));

        return $usersToApprove;
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

		$criteria->compare('id',$this->id);
		$criteria->compare('User_ID',$this->User_ID);
		$criteria->compare('New_Client',$this->New_Client);
		$criteria->compare('Client_ID',$this->Client_ID);
		$criteria->compare('Approved_By_Admin',$this->Approved_By_Admin);
        $criteria->compare('Approved_By_Client_Admin',$this->Approved_By_Client_Admin);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return UsersToApprove the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
