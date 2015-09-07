<?php

/**
 * This is the model class for table "remote_processing".
 *
 * The followings are the available columns in table 'remote_processing':
 * @property integer $PR_ID
 * @property integer $Client_ID
 * @property string $Export_Path
 * @property string $Export_Filename
 * @property double $TimeSpend
 * @property double $SizeData
 * @property double $SizeBook
 * @property integer $PagesBook
 * @property string $Created
 * @property double $Payment
 * @property integer $PaymentDate
 * @property double $AnalogPayment
 * @property integer $AnalogPaymentDate
 */
class RemoteProcessing extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'remote_processing';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Client_ID, Export_Path, Export_Filename, TimeSpend, SizeData, SizeBook, PagesBook', 'required'),
			array('Client_ID, PagesBook, PaymentDate, AnalogPaymentDate', 'numerical', 'integerOnly'=>true),
			array('TimeSpend, SizeData, SizeBook, Payment, AnalogPayment', 'numerical'),
			array('Export_Path, Export_Filename', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('PR_ID, Client_ID, Export_Path, Export_Filename, TimeSpend, SizeData, SizeBook, PagesBook, Created, Payment, PaymentDate, AnalogPayment, AnalogPaymentDate', 'safe', 'on'=>'search'),
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
			'PR_ID' => 'Pr',
			'Client_ID' => 'Client',
			'Export_Path' => 'Export Path',
			'Export_Filename' => 'Export Filename',
			'TimeSpend' => 'Time Spend',
			'SizeData' => 'Size Data',
			'SizeBook' => 'Size Book',
			'PagesBook' => 'Pages Book',
			'Created' => 'Created',
			'Payment' => 'Payment',
			'PaymentDate' => 'Payment Date',
			'AnalogPayment' => 'Analog Payment',
			'AnalogPaymentDate' => 'Analog Payment Date',
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

		$criteria->compare('PR_ID',$this->PR_ID);
		$criteria->compare('Client_ID',$this->Client_ID);
		$criteria->compare('Export_Path',$this->Export_Path,true);
		$criteria->compare('Export_Filename',$this->Export_Filename,true);
		$criteria->compare('TimeSpend',$this->TimeSpend);
		$criteria->compare('SizeData',$this->SizeData);
		$criteria->compare('SizeBook',$this->SizeBook);
		$criteria->compare('PagesBook',$this->PagesBook);
		$criteria->compare('Created',$this->Created,true);
		$criteria->compare('Payment',$this->Payment);
		$criteria->compare('PaymentDate',$this->PaymentDate);
		$criteria->compare('AnalogPayment',$this->AnalogPayment);
		$criteria->compare('AnalogPaymentDate',$this->AnalogPaymentDate);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return RemoteProcessing the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public static function digitalBookPayed($model){

        $client = Clients::model()->with('company')->findByPk(Yii::app()->user->clientID);

        Mail::notifyAdminAboutBookPayment($model->PR_ID,$model->Payment,$model->Export_Filename,Helper::formatBytes($model->SizeBook),$model->PagesBook,$client->company->Company_Name);

    }

    public static function analogBookPayed($model,$quality,$pages_per_sheets,$copies){

        $client = Clients::model()->with('company')->findByPk(Yii::app()->user->clientID);

        Mail::notifyAdminAboutAnalogBookPayment($model->PR_ID,$model->AnalogPayment,$model->Export_Filename,Helper::formatBytes($model->SizeBook),$model->PagesBook,$quality,$pages_per_sheets,$copies,$client->company->Company_Name);

    }

    public static function CalculatePages($origing,$cli_id) {

        set_time_limit(200);
        $sum = 0;
        $condition = new CDbCriteria();
        if ($origing != '') $condition->condition = "Origin='" . $origing . "'";
        $condition->addCondition("Client_ID = '" . $cli_id . "'");

        $documents = Documents::model()->findAll($condition);

        require_once(Yii::app()->basePath.'/extensions/Fpdf/fpdf.php');
        require_once(Yii::app()->basePath.'/extensions/Fpdi/fpdi.php');

        foreach($documents as $document) {
            $pages = FileModification::calculatePagesByDocID($document->Document_ID);

            if ($pages > 1)
            {
                $image = Images::model()->findByAttributes(array(
                    'Document_ID'=>$document->Document_ID
                ));
                if ($image ) {
                    $image->Pages_Count = $pages;
                    $image->save();
                }

            }


            $sum += $pages;

        }

        return $sum;

    }



    public static function CalculateBookPaySums ($rp_id,$only_for_payed=false)
    {
        $criteria = new CDbCriteria();

        $criteria->condition = "t.PR_ID = " . $rp_id ;
        $criteria->addCondition('Client_ID =' . Yii::app()->user->clientID);
        if ($only_for_payed) {
            $criteria->addCondition ("t.Payment is null");
        }



        $rp_item = RemoteProcessing::model()->find($criteria);

        if($rp_item) {
            $rp_settings = RemoteProcessingSettings::model()->find();

            $size = ($rp_item->SizeData + $rp_item->SizeBook)/pow(1024, 3);
            $item_price = floatval( ($size*$rp_settings->DigitalSizeCost) + ($rp_item->TimeSpend*$rp_settings->DigitalTimeCost)  +($rp_item->PagesBook * $rp_settings->DigitalPageCost) +$rp_settings->SetupFee);
            $item_price = round($item_price,2);
            $default_paper_cost =  floatval( ($rp_item->PagesBook * $rp_settings->AnalogColouredPageCost) );
            $default_paper_cost = round($default_paper_cost,2);


                return array(
                    'pdf_prise'=>$item_price,
                    'paper_default_price'=>$default_paper_cost
                );
        }


    }


}
