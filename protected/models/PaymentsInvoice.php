<?php

/**
 * This is the model class for table "payments_invoice".
 *
 * The followings are the available columns in table 'payments_invoice':
 * @property integer $Payments_Invoice_ID
 * @property integer $Payment_ID
 * @property string $Check_Invoice_Number
 * @property double $Check_Invoice_Amount
 */
class PaymentsInvoice extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'payments_invoice';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Payment_ID, Check_Invoice_Number, Check_Invoice_Amount', 'required'),
			array('Payment_ID', 'numerical', 'integerOnly'=>true),
			array('Check_Invoice_Amount', 'numerical'),
            array('Check_Invoice_Amount', 'length', 'max'=>13),
			array('Check_Invoice_Number', 'length', 'max'=>45),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Payments_Invoice_ID, Payment_ID, Check_Invoice_Number, Check_Invoice_Amount', 'safe', 'on'=>'search'),
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
            'payment' => array(self::BELONGS_TO, 'Payments', 'Payment_ID'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'Payments_Invoice_ID' => 'Payments Invoice',
			'Payment_ID' => 'Payment',
			'Check_Invoice_Number' => 'Check Invoice Number',
			'Check_Invoice_Amount' => 'Check Invoice Amount',
		);
	}

    /**
     * On save event
     * @return bool
     */
    protected function beforeSave () {
        if (isset($this->Check_Invoice_Amount) && ($this->Check_Invoice_Amount == '')) {
            $this->Check_Invoice_Amount = 0;
        }
        return parent::beforeSave();
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

		$criteria->compare('Payments_Invoice_ID',$this->Payments_Invoice_ID);
		$criteria->compare('Payment_ID',$this->Payment_ID);
		$criteria->compare('Check_Invoice_Number',$this->Check_Invoice_Number,true);
		$criteria->compare('Check_Invoice_Amount',$this->Check_Invoice_Amount);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PaymentsInvoice the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public static function getInvoicesDist($pay_id)
    {
        $dists= self::model()->findAllByAttributes(array(
            'Payment_ID' => $pay_id,
        ));
        $i=0;
        if($dists){
            foreach ($dists as $dist) {

                $return_array[$i]['Invoice_Number']=$dist->Check_Invoice_Number;
                $return_array[$i]['Invoice_Amount']=$dist->Check_Invoice_Amount;
                $i++;

            }
            $empty =false;
        } else {
            for($i = 1; $i <= 6; $i++) {
                $return_array[$i] = array(
                    'Invoice_Number' => '',
                    'Invoice_Amount' => '',
                );

            }
            $empty =true;
        }
        return array('empty'=>$empty,'dists'=>$return_array);
    }

    public static function deleteInvoicesByPayment($paymid) {

    }

    public static function adjustInvoicesType($array_of_invoices) {

        foreach ($array_of_invoices as $items) {
            if($items['Invoice_Amount']!=='') $items['Invoice_Amount'] = round(floatval($items['Invoice_Amount']),2);
            $result_array[] = $items;
        }

        return $result_array;
    }


}
