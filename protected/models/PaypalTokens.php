<?php

/**
 * This is the model class for table "paypal_tokens".
 *
 * The followings are the available columns in table 'paypal_tokens':
 * @property integer $Token_ID
 * @property string $Token
 * @property integer $Client_ID
 * @property string $Amount
 */
class PaypalTokens extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'paypal_tokens';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Token, Client_ID, Amount', 'required'),
			array('Token_ID, Client_ID', 'numerical', 'integerOnly'=>true),
			array('Token', 'length', 'max'=>45),
			array('Amount', 'length', 'max'=>13),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Token_ID, Token, Client_ID, Amount', 'safe', 'on'=>'search'),
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
			'Token_ID' => 'Token',
			'Token' => 'Token',
			'Client_ID' => 'Client',
			'Amount' => 'Amount',
		);
	}

    /**
     * Get PayPal Token Row for Client
     * @param $clientID
     * @return PaypalTokens
     */
    public static function getPayPalToken($clientID)
    {
        $payPalToken = self::model()->findByAttributes(array(
            'Client_ID' => $clientID,
        ));

        if (!$payPalToken) {
            $payPalToken = new PaypalTokens();
            $payPalToken->Client_ID = $clientID;
        }

        return $payPalToken;
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

		$criteria->compare('Token_ID',$this->Token_ID);
		$criteria->compare('Token',$this->Token,true);
		$criteria->compare('Client_ID',$this->Client_ID);
		$criteria->compare('Amount',$this->Amount,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PaypalTokens the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
