<?php

/**
 * This is the model class for table "tiers_settings".
 *
 * The followings are the available columns in table 'tiers_settings':
 * @property integer $Tier_ID
 * @property string $Tier_Name
 * @property string $data_entry
 * @property string $po
 * @property string $ap
 * @property string $payments
 * @property string $pc
 * @property string $w9
 * @property string $coas
 * @property string $vendors
 * @property string $batches
 * @property string $library
 */
class TiersSettings extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'tiers_settings';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Tier_ID, Tier_Name', 'required'),
			array('Tier_ID', 'numerical', 'integerOnly'=>true),
			array('Tier_Name, po, ap, payments, pc, w9, coas, vendors, batches, library', 'length', 'max'=>20),
			array('data_entry', 'length', 'max'=>100),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Tier_ID, Tier_Name, data_entry, po, ap, payments, pc, w9, coas, vendors, batches, library', 'safe', 'on'=>'search'),
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
			'Tier_ID' => 'Tier',
			'Tier_Name' => 'Tier Name',
			'data_entry' => 'Data Entry',
			'po' => 'Po',
			'ap' => 'Ap',
			'payments' => 'Payments',
			'pc' => 'Pc',
			'w9' => 'W9',
			'coas' => 'Coas',
			'vendors' => 'Vendors',
			'batches' => 'Batches',
			'library' => 'Library',
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

		$criteria->compare('Tier_ID',$this->Tier_ID);
		$criteria->compare('Tier_Name',$this->Tier_Name,true);
		$criteria->compare('data_entry',$this->data_entry,true);
		$criteria->compare('po',$this->po,true);
		$criteria->compare('ap',$this->ap,true);
		$criteria->compare('payments',$this->payments,true);
		$criteria->compare('pc',$this->pc,true);
		$criteria->compare('w9',$this->w9,true);
		$criteria->compare('coas',$this->coas,true);
		$criteria->compare('vendors',$this->vendors,true);
		$criteria->compare('batches',$this->batches,true);
		$criteria->compare('library',$this->library,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return TiersSettings the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * Returns aggregate settings for comma separated tiers ids
     * @param $tiers
     * @return null
     */
    public static function agregateTiersSettings ($tiers){
        $tiers_ids = explode(',',$tiers);
        $sum_settings_array = null;

        foreach ($tiers_ids as $tier_id) {
            if ($sum_settings_array) {
                //concatenation here
                $next_settings_array =  TiersSettings::model()->findByAttributes(array(
                    'Tier_ID'=> $tier_id
                ))->attributes;

                foreach ($next_settings_array as $field_name=>$value) {
                    if ($value) {
                        $sum_settings_array[$field_name] =
                                array_merge(
                                        $sum_settings_array[$field_name],
                                        array_diff( explode(',',$value), $sum_settings_array[$field_name] )
                                );
                    }
                }



            }  else {

                $settings = TiersSettings::model()->findByAttributes(array(
                   'Tier_ID'=> $tier_id
                ))->attributes;

                $settings = $settings ? $settings : array();

                foreach ($settings as $field_name=>$value) {
                    if ($value) {
                        $sum_settings_array[$field_name] = explode(',',$value);
                    } else {
                        $sum_settings_array[$field_name] = array();
                    }
                }
            }
        }
        //this param is html that used for dropdown in upload page
        $sum_settings_array['docsHtml']= self::htmlListFromArray($sum_settings_array['docs']);
        return $sum_settings_array;
    }

    /**
     * Returns html select for documents passed as an array
     * @param $arr
     * @return string
     */
    public static function htmlListFromArray($arr) {
        $names_array = array(
                "PO"=>'Purchase Order',
                "BU"=>'Backup',
                "LB"=>'Library',
                "GF"=>'General',
                "PR"=>'Payroll',
                "JE"=>'Journal Entry',
                "AR"=>'Accounts Receivable',
                "W9"=>'W9',
                "AP"=>'Accounts Payable',
                "PM"=>'Payment',
                "PC"=>'Petty Cash (Expense)'
        );

        $html = '<ul class="width150">';
        $arr = $arr ? $arr : array();
        foreach($arr as $item) {
            $html.='<li data-doc-type="'.$item.'">'.$names_array[$item].'</li>';
        }
        $html .= '</ul>';

        return $html;
    }

    /**
     * Returns number of documents in the tier # id
     * @param $id
     * @return CDbDataReader|mixed|string
     */
    public static function CheckTierLevelUsage($id) {

        $condition = new CDbCriteria();
        $condition->condition = "t.Client_ID=".Yii::app()->user->clientID;

        switch ($id) {
            case 2 :
                //we need to check if in system present PO items
                $condition->addCondition("t.Document_Type='PO'");
                $count  = Documents::model()->count($condition);
                break;
            case 3 :
                //we need to check if in system present AP and Payment items
                $condition->addCondition("t.Document_Type in ('AP','PM')");
                $count  = Documents::model()->count($condition);
                break;
            case 4 :
                //we need to check if in system present PC items
                $condition->addCondition("t.Document_Type ='PC' ");
                $count  = Documents::model()->count($condition);
                break;
                }
        return $count;
    }

}
