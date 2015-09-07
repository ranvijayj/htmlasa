<?php

/**
 * This is the model class for table "po_desc_detail".
 *
 * The followings are the available columns in table 'po_desc_detail':
 * @property integer $PO_Desc_ID
 * @property integer $PO_ID
 * @property integer $PO_Desc_Qty
 * @property integer $PO_Desc_Desc
 * @property integer $PO_Desc_Purchase
 * @property integer $PO_Desc_Rental
 * @property string $PO_Desc_Budget_Line_Num
 * @property double $PO_Desc_Amount
 */
class PoDescDetail extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'po_desc_detail';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('PO_ID, PO_Desc_Qty, PO_Desc_Desc, PO_Desc_Purchase, PO_Desc_Rental, PO_Desc_Amount', 'required'),
			array('PO_ID, PO_Desc_Qty, PO_Desc_Purchase, PO_Desc_Rental', 'numerical', 'integerOnly'=>true),
			array('PO_Desc_Amount', 'numerical'),
			array('PO_Desc_Budget_Line_Num', 'length', 'max'=>20),
            array('PO_Desc_Desc', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('PO_Desc_ID, PO_ID, PO_Desc_Qty, PO_Desc_Desc, PO_Desc_Purchase, PO_Desc_Rental, PO_Desc_Budget_Line_Num, PO_Desc_Amount', 'safe', 'on'=>'search'),
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
			'PO_Desc_ID' => 'Po Desc',
			'PO_ID' => 'Po',
			'PO_Desc_Qty' => 'Po Desc Qty',
			'PO_Desc_Desc' => 'Po Desc Desc',
			'PO_Desc_Purchase' => 'Po Desc Purchase',
			'PO_Desc_Rental' => 'Po Desc Rental',
			'PO_Desc_Budget_Line_Num' => 'Po Desc Budget Line Num',
			'PO_Desc_Amount' => 'Po Desc Amount',
		);
	}

    /**
     * On save event
     * @return bool
     */
    protected function beforeSave() {
        if (isset($this->PO_Desc_Amount) && $this->PO_Desc_Amount == '') {
            $this->PO_Desc_Amount = 0;
        }

        return parent::beforeSave();
    }

    /**
     * Save PO Details
     * @param $poId
     * @param $detailsToSave
     * @return bool
     */
    public static function savePODetails($poId, $detailsToSave)
    {
        PoDescDetail::model()->deleteAllByAttributes(array(
            'PO_ID' => $poId,
        ));

        $detailsToSave = array_slice($detailsToSave,0, 50, true); // only 50 items allowed
        foreach ($detailsToSave as $detailToSave) {
            $newDetail = new PoDescDetail();
            $newDetail->PO_ID = $poId;

            if ($detailToSave['PO_Desc_Qty'] == '') {
                $newDetail->PO_Desc_Qty = 0;
            } else {
                $newDetail->PO_Desc_Qty = intval($detailToSave['PO_Desc_Qty']);
            }

            if ($detailToSave['PO_Desc_Desc'] == '') {
                $newDetail->PO_Desc_Desc = '-';
            } else {
                $newDetail->PO_Desc_Desc = $detailToSave['PO_Desc_Desc'];
            }

            if ($detailToSave['PO_Desc_Purchase_Rental'] == 0) {
                $newDetail->PO_Desc_Purchase = 1;
                $newDetail->PO_Desc_Rental = 0;
            } else {
                $newDetail->PO_Desc_Purchase = 0;
                $newDetail->PO_Desc_Rental = 1;
            }

            if ($detailToSave['PO_Desc_Amount'] == '' || $detailToSave['PO_Desc_Amount'] == 0) {
                $newDetail->PO_Desc_Amount = 0;
            } else {
                $newDetail->PO_Desc_Amount = number_format(floatval($detailToSave['PO_Desc_Amount']), 2, '.', '');
            }

            if ($detailToSave['PO_Desc_Budget_Line_Num'] == '') {
                $newDetail->PO_Desc_Budget_Line_Num = null;
            } else {
                $newDetail->PO_Desc_Budget_Line_Num = $detailToSave['PO_Desc_Budget_Line_Num'];
            }


            if ($newDetail->validate()) {
                $newDetail->save();
                $result = true;
            } else {
                $result = false;
            }

        }
        return $result;
    }

    /**
     * Generates from array of dests  array of dest's models. Used for generating PO document without saving metadata to database.
     * @param $poId
     * @param $detailsToSave
    */

    public static function preparePODetailsArray($poId, $detailsToSave)
    {
        $resultArray = array();

        $detailsToSave = array_slice($detailsToSave,0, 50, true); // only 50 items allowed
        foreach ($detailsToSave as $detailToSave) {
            $newDetail = new PoDescDetail();
            $newDetail->PO_ID = $poId;

            if ($detailToSave['PO_Desc_Qty'] == '') {
                $newDetail->PO_Desc_Qty = 0;
            } else {
                $newDetail->PO_Desc_Qty = intval($detailToSave['PO_Desc_Qty']);
            }

            if ($detailToSave['PO_Desc_Desc'] == '') {
                $newDetail->PO_Desc_Desc = '-';
            } else {
                $newDetail->PO_Desc_Desc = $detailToSave['PO_Desc_Desc'];
            }

            if ($detailToSave['PO_Desc_Purchase_Rental'] == 0) {
                $newDetail->PO_Desc_Purchase = 1;
                $newDetail->PO_Desc_Rental = 0;
            } else {
                $newDetail->PO_Desc_Purchase = 0;
                $newDetail->PO_Desc_Rental = 1;
            }

            if ($detailToSave['PO_Desc_Amount'] == '' || $detailToSave['PO_Desc_Amount'] == 0) {
                $newDetail->PO_Desc_Amount = 0;
            } else {
                $newDetail->PO_Desc_Amount = number_format(floatval($detailToSave['PO_Desc_Amount']), 2, '.', '');
            }

            if ($detailToSave['PO_Desc_Budget_Line_Num'] == '') {
                $newDetail->PO_Desc_Budget_Line_Num = null;
            } else {
                $newDetail->PO_Desc_Budget_Line_Num = $detailToSave['PO_Desc_Budget_Line_Num'];
            }


            array_push($resultArray,$newDetail);
        }

        return $resultArray;
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

		$criteria->compare('PO_Desc_ID',$this->PO_Desc_ID);
		$criteria->compare('PO_ID',$this->PO_ID);
		$criteria->compare('PO_Desc_Qty',$this->PO_Desc_Qty);
		$criteria->compare('PO_Desc_Desc',$this->PO_Desc_Desc);
		$criteria->compare('PO_Desc_Purchase',$this->PO_Desc_Purchase);
		$criteria->compare('PO_Desc_Rental',$this->PO_Desc_Rental);
		$criteria->compare('PO_Desc_Budget_Line_Num',$this->PO_Desc_Budget_Line_Num,true);
		$criteria->compare('PO_Desc_Amount',$this->PO_Desc_Amount);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PoDescDetail the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public static function prepareDescDetails($post) {
        $descDetails = array();
        $detailsToSave = array();
        $subtotal = 0;
        $total = 0;


        foreach ($post as $key => $decr_detail) {
            $descDetails[$key] = array(
                'PO_Desc_Qty' => $decr_detail['PO_Desc_Qty'],
                'PO_Desc_Desc' => $decr_detail['PO_Desc_Desc'],
                'PO_Desc_Purchase_Rental' => $decr_detail['PO_Desc_Purchase_Rental'],
                'PO_Desc_Budget_Line_Num' => $decr_detail['PO_Desc_Budget_Line_Num'],
                'PO_Desc_Amount' => $decr_detail['PO_Desc_Amount'],
            );

            if ($decr_detail['PO_Desc_Qty'] != '' || $decr_detail['PO_Desc_Desc'] != ''
                || $decr_detail['PO_Desc_Budget_Line_Num'] != '' || $decr_detail['PO_Desc_Amount'] != '') {
                $detailsToSave[] = $decr_detail;

                $subtotal += intval($decr_detail['PO_Desc_Qty']) * round(floatval($decr_detail['PO_Desc_Amount']), 2);
                $total += intval($decr_detail['PO_Desc_Qty']) * round(floatval($decr_detail['PO_Desc_Amount']), 2);
            }
        }

        return array(
            'detailsToSave'=>$detailsToSave,
            'subtotal'>$subtotal,
            'total'=>$total
        );


    }


    public static function getPoDescDetails($po_id) {

        $po_id = intval($po_id);

        return self::model()->findAllByAttributes(array(
           'PO_ID'=>$po_id
        ));

    }

    public static function fromModelToArray($po) {
        $poDecrDetails = $po->decr_details;

        // set description details
        foreach ($poDecrDetails as $key => $decr_detail) {
            $descDetails[$key + 1] = array(
                'PO_Desc_Qty' => $decr_detail->PO_Desc_Qty,
                'PO_Desc_Desc' => $decr_detail->PO_Desc_Desc,
                'PO_Desc_Purchase_Rental' => $decr_detail->PO_Desc_Rental,
                'PO_Desc_Budget_Line_Num' => $decr_detail->PO_Desc_Budget_Line_Num,
                'PO_Desc_Amount' => $decr_detail->PO_Desc_Amount,
            );
        }

        for ($i=count($descDetails)+1;$i<8;$i++) {

            $descDetails[$i] = array(
                'PO_Desc_Qty' => '',
                'PO_Desc_Desc' => '',
                'PO_Desc_Purchase_Rental' => '',
                'PO_Desc_Budget_Line_Num' => '',
                'PO_Desc_Amount' => '',
            );

        }

        return $descDetails;
    }


}
