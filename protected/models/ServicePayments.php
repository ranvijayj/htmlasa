<?php

/**
 * This is the model class for table "service_payments".
 *
 * The followings are the available columns in table 'service_payments':
 * @property integer $Payment_ID
 * @property integer $Client_ID
 * @property string $Payment_Date
 * @property string $Payment_Amount
 * @property string $Payment_Number
 */
class ServicePayments extends CActiveRecord
{
    const DEFAULT_DAYS_IN_MONTH = 30;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'service_payments';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Client_ID, Payment_Date, Payment_Amount', 'required'),
			array('Client_ID', 'numerical', 'integerOnly'=>true),
			array('Payment_Amount', 'length', 'max'=>13),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Payment_ID, Client_ID, Payment_Date, Payment_Amount, Payment_Number', 'safe', 'on'=>'search'),
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
            'client'=>array(self::BELONGS_TO, 'Clients', 'Client_ID'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'Payment_ID' => 'Payment',
			'Client_ID' => 'Client',
			'Payment_Date' => 'Payment Date',
			'Payment_Amount' => 'Payment Amount',
		);
	}


    /**
     * Adding a payment without applying settings and without notification
     * Used only for admin menu
     * @param $clientID
     * @param $amount
     * @param $date
     * @param string $paydoc_number
     */
    public static function addJustPayment($clientID, $amount, $date, $paydoc_number='auto')
    {

        //$client = Clients::model()->with('service_settings', 'pending_service_settings','company.adreses')->findByPk($clientID);

        //if ($client && $amount > 0 && preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $date)) {

            $transaction = Yii::app()->db->beginTransaction();
            try {
                $payment = new ServicePayments();
                $payment->Client_ID = $clientID;
                $payment->Payment_Date = $date;
                $payment->Payment_Amount = $amount;
                $payment->Payment_Number = $paydoc_number;

                if ($payment->validate()) {
                    $payment->save();


                }
                $transaction->commit();
                return array(
                    'Payment_Date'=>$payment->Payment_Date,
                    'Payment_Amount'=>$payment->Payment_Amount
                );
            } catch(Exception $e) {
                $transaction->rollback();
                return '';
            }
      //  }
    }

                    /**
     * Add client Payment, apply new settings and update 'Active To' date
     * @param $clientID
     * @param $amount
     * @param $date
     */
    public static function addClientPayment($clientID, $amount, $date, $monthly_payment,$paydoc_number='auto')
    {

        $client = Clients::model()->with('service_settings', 'pending_service_settings','company.adreses')->findByPk($clientID);
        //$user = Users::model()->with('person')->findByPk(Yii::app()->user->userID);
        //$uid = UsersClientList::model()->findByAttributes(array('Client_ID'=>$clientID))->User_ID;
        $user = Users::model()->with('person')->findByPk(Yii::app()->user->userID);

        $sumToPay=$amount;

        //$sum_settings = ServiceLevelSettings::getSummarySettings($clientID);


        if ($client && $amount > 0 && preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $date)) {
            $pendingSettings = $client->pending_service_settings;
            $currentSettings = $client->service_settings;

            $dcss = DelayedClientServiceSettings::model()->findByPk(Yii::app()->user->clientID);
            if($dcss) {$dcss->delete();}

            $sum_settings = ClientServiceSettings::getBaseTierValues(explode(',',$pendingSettings->Service_Level_ID));
            //variables for email notification, not for logic
            $settingsBefore = "Level : ".$currentSettings->Service_Level_ID." Users:".$currentSettings->Additional_Users." Projects:".$currentSettings->Additional_Projects."  Storage : ". $currentSettings->Additional_Storage;
            $dateBefore=date_format(date_create($currentSettings->Active_To),'Y-m-d');
            $tierNameBefore=ServiceLevelSettings::model()->findByPk($currentSettings->Service_Level_ID)->Tier_Name;


            $settings_are_delayed = $pendingSettings->Pending_Active_From=='0000-00-00' ? 0 : 1;
            // begin transaction
            $transaction = Yii::app()->db->beginTransaction();
            try {
                $payment = new ServicePayments();
                $payment->Client_ID = $clientID;
                $payment->Payment_Date = $date;
                $payment->Payment_Amount = $amount;
                $payment->Payment_Amount = $paydoc_number;



                if ($payment->validate()) {
                    // save payment
                    $payment->save();
                    //echo "Payment validated and saved\n";
                    $dateFrom = $currentSettings->Active_To;
                    if ($dateFrom < date('Y-m-d')) {
                        $dateFrom = date('Y-m-d');
                    }

                    // apply new settings if necessary
                    if ($pendingSettings && $amount >= $pendingSettings->Fee_To_Upgrade && !$monthly_payment && !$settings_are_delayed) {
                        $currentSettings->Service_Level_ID = $pendingSettings->Service_Level_ID;
                        $currentSettings->Additional_Users = $pendingSettings->Additional_Users-$sum_settings['Users_Count'];
                        $currentSettings->Additional_Projects = $pendingSettings->Additional_Projects;//-$sum_settings['Projects_Count'];
                        $currentSettings->Additional_Storage = $pendingSettings->Additional_Storage;//-$sum_settings['Storage_Count'];
                        $currentSettings->Active_To = $pendingSettings->Pending_Active_To;
                        $currentSettings->Fee = $pendingSettings->Fee;
                        $currentSettings->save();

                        $user_client_settings = $client->service_settings;
                        $user_tier_settings = TiersSettings::agregateTiersSettings($user_client_settings->Service_Level_ID);
                        Yii::app()->user->setState('tier_settings', $user_tier_settings);

                       // $amount -= $pendingSettings->getCurrentAmountToUpgrade($dateFrom);

                        $pendingSettings->delete();
                    }

                    if ($pendingSettings && $amount >= $pendingSettings->Fee_To_Upgrade && !$monthly_payment && $settings_are_delayed) {
                        ///
                        DelayedClientServiceSettings::createDelayedFromPending($pendingSettings,$clientID,$sum_settings['Users_Count'],
                            $sum_settings['Projects_Count'],$sum_settings['Storage_Count']
                        );
                       /* $dcss->Additional_Users = $pendingSettings->Additional_Users-$sum_settings['Users_Count'];
                        $dcss->Additional_Projects = $pendingSettings->Additional_Projects-$sum_settings['Projects_Count'];
                        $dcss->Additional_Storage = $pendingSettings->Additional_Storage-$sum_settings['Storage_Count'];*/



                    }

                    // update Active_To date
                    if ( $monthly_payment) {
                        /*$addDays = ceil($amount/($currentSettings->Fee/self::DEFAULT_DAYS_IN_MONTH));
                        $dateOb = date_create($dateFrom);
                        date_add($dateOb, date_interval_create_from_date_string($addDays . ' days'));
                        $activeTo = date_format($dateOb, 'Y-m-d');*/
                        $number_of_periods = floor($amount/$currentSettings->Fee);
                        $activeTo = strtotime(date("m/d/Y", strtotime($dateFrom)) . " +".$number_of_periods." month");


                        $currentSettings->Active_To = date('Y-m-d',$activeTo);
                        $currentSettings->save();

                        if ( $pendingSettings ) {
                            //we need to update active_to date in the pending settings.
                            $pendingSettings->Pending_Active_To = date('Y-m-d',$activeTo);
                            $pendingSettings->save();
                        }
                    }

                $transaction->commit();

                //variables for email notification, not for logic
                $email=$user->person->Email;
                $settingsAfter = "Level : ".$currentSettings->Service_Level_ID." Users:".$currentSettings->Additional_Users." Projects:".$currentSettings->Additional_Projects."  Storage : ". $currentSettings->Additional_Storage;
                $dateAfter=date_format(date_create($currentSettings->Active_To),'Y-m-d');
                $tierNameAfter=ServiceLevelSettings::model()->findByPk($currentSettings->Service_Level_ID)->Tier_Name;
                $company_name=$client->company->Company_Name;

                Mail::notifyAdminAboutStripePaymentExecuted($email,$company_name,$settingsBefore,$settingsAfter,$tierNameBefore,$tierNameAfter,$dateBefore,$dateAfter,$sumToPay);

                } else {  Yii::app()->user->setFlash('error', "ServicePayments validation error!");  }
            } catch(Exception $e) {
                $transaction->rollback();
            }
        }
    }

    /**
     * Generate invoice
     * @param $clientID
     * @param $amount
     */
    public static function generateInvoice($clientID, $userID, $amount)
    {

       $client = Clients::model()->with('company.adreses')->findByPk($clientID);

       $user = Users::model()->with('person')->findByPk($userID);


        $settingsToPay= PendingClientServiceSettings::model()->findByAttributes(array(
            'Client_ID'=>$clientID,
            'Approved'=>1
            )
        );


        if($settingsToPay) {
            //$str = ServiceLevelSettings::getSummaryName($settingsToPay->Service_Level_ID);

            $sum_settings = ServiceLevelSettings::getSummarySettings($clientID);
            $tierName=$sum_settings['Tier_Name'];

            $data['service']['total_users'] = $settingsToPay->Additional_Users ;
            $data['service']['total_projects'] = $settingsToPay->Additional_Projects ;
            $data['service']['total_storage'] = $settingsToPay->Additional_Storage ;

            $data['service']['added_users'] = $settingsToPay->Additional_Users - $sum_settings['Users_Count'];
            $data['service']['added_projects'] = $settingsToPay->Additional_Projects ;
            $data['service']['added_storage'] = $settingsToPay->Additional_Storage ;

            $active_to = $settingsToPay->Pending_Active_To;
        }
        else {
            $settingsToPay= ClientServiceSettings::model()->findByPk($clientID);
            $sum_settings = ServiceLevelSettings::getSummarySettings($clientID);
            $tierName=$sum_settings['Tier_Name'];

            $data['service']['total_users'] = $settingsToPay->Additional_Users + $sum_settings['Users_Count'];
            $data['service']['total_projects'] = $settingsToPay->Additional_Projects + $sum_settings['Projects_Count'];
            $data['service']['total_storage'] = $settingsToPay->Additional_Storage + $sum_settings['Storage_Count'];

            $data['service']['added_users'] = $settingsToPay->Additional_Users;
            $data['service']['added_projects'] = $settingsToPay->Additional_Projects;
            $data['service']['added_storage'] = $settingsToPay->Additional_Storage;

            $active_to = $settingsToPay->Active_To;

        }

        $summary_sl_settings = ServiceLevelSettings::getSummarySettings($clientID);

        $data['company_to']['company_name']=$client->company->Company_Name;
        $data['company_to']['street']=$client->company->adreses[0]->Address1;
        $data['company_to']['city']=$client->company->adreses[0]->City."  ".$client->company->adreses[0]->ZIP;;
        $data['company_to']['country']=$client->company->adreses[0]->Country;
        $data['company_to']['email']=$user->person->Email;
        $data['company_to']['phone']=$user->person->Mobile_Phone;

        $data['invoice']['number']=Aps::generateInvoiceNumber($clientID);
        $data['invoice']['date']=date("M d, Y",time());
        $data['invoice']['due_date']=date("M d, Y",time());
        $data['invoice']['due_date']=date("M d, Y",time());
        $data['invoice']['amount_due']=$amount;

        $data['service']['service_name']=$tierName;


        /*$data['service']['Users']=$summary_sl_settings['Additional_Users']+$summary_sl_settings['Users_Count'];
        $data['service']['Projects']=$summary_sl_settings['Additional_Projects']+$summary_sl_settings['Projects_Count'];
        $data['service']['Storage']=$summary_sl_settings['Additional_Storage']+$summary_sl_settings['Storage_Count'];*/
        $data['service']['active_to'] = $active_to;




        // get content for pdf
        $content = Yii::app()->controller->renderPartial('application.views.myaccount.invoice_template', array(
            'data'=>$data,
        ), true);

        $fileName = date("Y_m_d_H_i_s") . '.pdf';
        Yii::import('ext.html2pdf.HTML2PDF');
        $html2pdf = new HTML2PDF('P', 'A4', 'en');
        $html2pdf->writeHTML($content);
        $html2pdf->Output($fileName, 'I');
        die;
    }

    /**
     * Search Client payments
     * @return CActiveDataProvider
     */
    public function searchClientPayments()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('Payment_ID',$this->Payment_ID);
        $criteria->compare('Client_ID',$this->Client_ID);
        $criteria->compare('Payment_Date',$this->Payment_Date, true);
        $criteria->compare('Payment_Amount',$this->Payment_Amount, true);

        $criteria->addCondition("t.Client_ID='" . Yii::app()->user->clientID . "'");

        $criteria->order = 't.Payment_Date DESC';

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
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

		$criteria->compare('Payment_ID',$this->Payment_ID);
		$criteria->compare('Client_ID',$this->Client_ID);
		$criteria->compare('Payment_Date',$this->Payment_Date,true);
		$criteria->compare('Payment_Amount',$this->Payment_Amount,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ServicePayments the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
