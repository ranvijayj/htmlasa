<?php

/**
 * This is the model class for table "po_pmts_traking".
 *
 * The followings are the available columns in table 'po_pmts_traking':
 * @property integer $PO_Trkng_ID
 * @property integer $PO_ID
 * @property double $PO_Trkng_Beg_Balance
 * @property string $PO_Trkng_Desc
 * @property string $PO_Trkng_Inv_Date
 * @property string $PO_Trkng_Inv_Number
 * @property double $PO_Trkng_Pmt_Amt
 */
class PoPmtsTraking extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'po_pmts_traking';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('PO_ID, PO_Trkng_Beg_Balance', 'required'),
			array('PO_ID', 'numerical', 'integerOnly'=>true),
			array('PO_Trkng_Beg_Balance, PO_Trkng_Pmt_Amt', 'numerical'),
			array('PO_Trkng_Desc, PO_Trkng_Inv_Number', 'length', 'max'=>45),
            array('PO_Trkng_Beg_Balance, PO_Trkng_Pmt_Amt', 'length', 'max'=>13),
			array('PO_Trkng_Inv_Date', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('PO_Trkng_ID, PO_ID, PO_Trkng_Beg_Balance, PO_Trkng_Desc, PO_Trkng_Inv_Date, PO_Trkng_Inv_Number, PO_Trkng_Pmt_Amt', 'safe', 'on'=>'search'),
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
			'PO_Trkng_ID' => 'Po Trkng',
			'PO_ID' => 'Po',
			'PO_Trkng_Beg_Balance' => 'Po Trkng Beg Balance',
			'PO_Trkng_Desc' => 'Po Trkng Desc',
			'PO_Trkng_Inv_Date' => 'Po Trkng Inv Date',
			'PO_Trkng_Inv_Number' => 'Po Trkng Inv Number',
			'PO_Trkng_Pmt_Amt' => 'Po Trkng Pmt Amt',
		);
	}

    /**
     * On save event
     * @return bool
     */
    protected function beforeSave() {
        if (isset($this->PO_Trkng_Beg_Balance) && $this->PO_Trkng_Beg_Balance == '') {
            $this->PO_Trkng_Beg_Balance = 0;
        }

        if (isset($this->PO_Trkng_Pmt_Amt) && ($this->PO_Trkng_Pmt_Amt == '' || $this->PO_Trkng_Pmt_Amt == 0)) {
            $this->PO_Trkng_Pmt_Amt = null;
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

		$criteria->compare('PO_Trkng_ID',$this->PO_Trkng_ID);
		$criteria->compare('PO_ID',$this->PO_ID);
		$criteria->compare('PO_Trkng_Beg_Balance',$this->PO_Trkng_Beg_Balance);
		$criteria->compare('PO_Trkng_Desc',$this->PO_Trkng_Desc,true);
		$criteria->compare('PO_Trkng_Inv_Date',$this->PO_Trkng_Inv_Date,true);
		$criteria->compare('PO_Trkng_Inv_Number',$this->PO_Trkng_Inv_Number,true);
		$criteria->compare('PO_Trkng_Pmt_Amt',$this->PO_Trkng_Pmt_Amt);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PoPmtsTraking the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public static function  getSumOfOtherTracks($po_id,$track_to_exclude) {
        $condition = new CDbCriteria();
        $condition->condition = "PO_ID = ". $po_id;
        $condition->addCondition(" PO_Trkng_ID <>".$track_to_exclude);

        $tracks = PoPmtsTraking::model()->findAll($condition);

        $sum = 0;
        foreach ($tracks as $track) {
            $sum = $sum + $track->PO_Trkng_Pmt_Amt;
        }

        return $sum;
    }
}
