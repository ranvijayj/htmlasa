<?php

/**
 * This is the model class for table "pending_client_service_settings".
 *
 * The followings are the available columns in table 'pending_client_service_settings':
 * @property integer $Client_ID
 * @property integer $Service_Level_ID
 * @property integer $Additional_Users
 * @property integer $Additional_Projects
 * @property integer $Additional_Storage
 * @property string $Fee
 * @property string $Fee_To_Upgrade
 * @property string $Pending_Active_To
 * @property string $Pending_Active_From
 * @property string $Approved

 *
 */
class PendingClientServiceSettings extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'client_service_settings_pending';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Client_ID, Service_Level_ID, Fee_To_Upgrade', 'required'),
			array('Client_ID, Additional_Users, Additional_Projects, Additional_Storage, Approved', 'numerical', 'integerOnly'=>true),
			array('Fee, Service_Level_ID, Fee_To_Upgrade', 'length', 'max'=>13),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Client_ID, Service_Level_ID, Additional_Users, Additional_Projects, Additional_Storage, Fee, Fee_To_Upgrade', 'safe', 'on'=>'search'),
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
			'Client_ID' => 'Client',
			'Service_Level_ID' => 'Service Level',
			'Additional_Users' => 'Additional Users',
			'Additional_Projects' => 'Additional Projects',
			'Additional_Storage' => 'Additional Storage',
			'Fee' => 'Fee',
			'Fee_To_Upgrade' => 'Fee To Upgrade',
		);
	}

    /**
     * Set Fee for current company service settings
     */
    public function setFee($tier_id_array)
    {
        $this->Fee = ClientServiceSettings::getSumFeeByValues(
            $tier_id_array,
            $this->Additional_Users,
            $this->Additional_Projects,
            $this->Additional_Storage
        );
    }

    /**
     * Check applicable of settings and apply them
     * @param $clientServiceSettings
     */
    public function checkSettings($clientServiceSettings)
    {
        $dateFrom = $clientServiceSettings->Active_To;
        if ($dateFrom < date('Y-m-d')) {
            $dateFrom = date('Y-m-d');
        }

        $currentAmountToUpgrade = $this->getCurrentAmountToUpgrade($dateFrom);

        // begin transaction
        $transaction = Yii::app()->db->beginTransaction();
        try {
            if ($currentAmountToUpgrade == 0) {
                $clientServiceSettings->Service_Level_ID = $this->Service_Level_ID;
                $clientServiceSettings->Additional_Users = $this->Additional_Users;
                $clientServiceSettings->Additional_Projects = $this->Additional_Projects;
                $clientServiceSettings->Additional_Storage = $this->Additional_Storage;
                $clientServiceSettings->Fee = $this->Fee;
                if ($clientServiceSettings->validate()) {
                    $clientServiceSettings->save();
                    Yii::app()->user->setFlash('success', "New settings have been applied!");
                }
                $this->delete();
            }
            $transaction->commit();
        } catch(Exception $e) {
            $transaction->rollback();
        }
    }

    /**
     * Check applicable of settings and apply them
     * @param $clientServiceSettings
     */
    public function checkAndApplySettings($clientServiceSettings)
    {
        $dateFrom = $clientServiceSettings->Active_To;
        if ($dateFrom < date('Y-m-d')) {
            $dateFrom = date('Y-m-d');
        }

        $currentAmountToUpgrade = $this->getCurrentAmountToUpgrade($dateFrom);

        // begin transaction
        $transaction = Yii::app()->db->beginTransaction();
        try {
            if ($currentAmountToUpgrade == 0) {
                $clientServiceSettings->Service_Level_ID = $this->Service_Level_ID;
                $clientServiceSettings->Additional_Users = $this->Additional_Users;
                $clientServiceSettings->Additional_Projects = $this->Additional_Projects;
                $clientServiceSettings->Additional_Storage = $this->Additional_Storage;
                $clientServiceSettings->Fee = $this->Fee;
                if ($clientServiceSettings->validate()) {
                    $clientServiceSettings->save();
                }
                $this->delete();
            } else if ($currentAmountToUpgrade < 0) {
                $activeTo = $this->getLongerExpirationDate($currentAmountToUpgrade, $dateFrom);

                $clientServiceSettings->Service_Level_ID = $this->Service_Level_ID;
                $clientServiceSettings->Additional_Users = $this->Additional_Users;
                $clientServiceSettings->Additional_Projects = $this->Additional_Projects;
                $clientServiceSettings->Additional_Storage = $this->Additional_Storage;
                $clientServiceSettings->Fee = $this->Fee;
                $clientServiceSettings->Active_To = $activeTo;
                if ($clientServiceSettings->validate()) {
                    $clientServiceSettings->save();
                }
                $this->delete();
            } else if ($this->checkOpportunityToPayByTime($dateFrom)) {
                $activeTo = $this->getPrevExpirationDate($currentAmountToUpgrade, $dateFrom);

                $clientServiceSettings->Service_Level_ID = $this->Service_Level_ID;
                $clientServiceSettings->Additional_Users = $this->Additional_Users;
                $clientServiceSettings->Additional_Projects = $this->Additional_Projects;
                $clientServiceSettings->Additional_Storage = $this->Additional_Storage;
                $clientServiceSettings->Fee = $this->Fee;
                $clientServiceSettings->Active_To = $activeTo;
                if ($clientServiceSettings->validate()) {
                    $clientServiceSettings->save();
                }
                $this->delete();
            }
            $transaction->commit();
        } catch(Exception $e) {
            $transaction->rollback();
        }
    }

    /**
     * Check opportunity to pay for new setting by time
     * @param $dateFrom
     * @return bool
     */
    public function checkOpportunityToPayByTime($dateFrom)
    {
        $currentAmountToUpgrade = $this->getCurrentAmountToUpgrade($dateFrom);
        $activeTo = $this->getPrevExpirationDate($currentAmountToUpgrade, $dateFrom);

        $datetime1 = date_create(date('Y-m-d'));
        $datetime2 = date_create($activeTo);
        $interval = date_diff($datetime1, $datetime2, true);
        $dateDifference = intval($interval->format('%R%a'));

        if ($dateDifference>=7 && $activeTo > date('Y-m-d')) {
            return true;
        }

        return false;
    }

    /**
     * Get current amount to upgrade
     * @param $dateFrom
     * @return float
     */
    public function getCurrentAmountToUpgrade($dateFrom)
    {
        $datetime1 = date_create(date('Y-m-d'));
        $datetime2 = date_create($dateFrom);
        $interval = date_diff($datetime1, $datetime2, true);
        $dateDifference = intval($interval->format('%R%a'));
        return round($this->Fee_To_Upgrade*$dateDifference/ServicePayments::DEFAULT_DAYS_IN_MONTH, 2);
    }



    public function getCurrentAmountToPay()
    {
        if($this->Approved) {
            $x =  round($this->Fee_To_Upgrade, 2);
            return $x;
        }

    }

    /**
     * Get longer expiration date
     * @param $currentAmountToUpgrade
     * @param $dateFrom
     * @return bool|string
     */
    public function getLongerExpirationDate($currentAmountToUpgrade, $dateFrom)
    {
        $addDays = ceil(abs($currentAmountToUpgrade)/($this->Fee/ServicePayments::DEFAULT_DAYS_IN_MONTH));
        $dateOb = date_create($dateFrom);
        date_add($dateOb, date_interval_create_from_date_string($addDays . ' days'));
        $activeTo = date_format($dateOb, 'Y-m-d');
        return $activeTo;
    }

    /**
     * Get prev expiration date
     * @param $currentAmountToUpgrade
     * @param $dateFrom
     * @return bool|string
     */
    public function getPrevExpirationDate($currentAmountToUpgrade, $dateFrom)
    {
        $minDays = ceil(abs($currentAmountToUpgrade)/($this->Fee/ServicePayments::DEFAULT_DAYS_IN_MONTH));
        $dateOb = date_create($dateFrom);
        date_add($dateOb, date_interval_create_from_date_string('-' . $minDays . ' days'));
        $activeTo = date_format($dateOb, 'Y-m-d');
        return $activeTo;
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

		$criteria->compare('Client_ID',$this->Client_ID);
		$criteria->compare('Service_Level_ID',$this->Service_Level_ID);
		$criteria->compare('Additional_Users',$this->Additional_Users);
		$criteria->compare('Additional_Projects',$this->Additional_Projects);
		$criteria->compare('Additional_Storage',$this->Additional_Storage);
		$criteria->compare('Fee',$this->Fee,true);
		$criteria->compare('Fee_To_Upgrade',$this->Fee_To_Upgrade,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PendingClientServiceSettings the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}


    /**
     * @param $client_id
     * @param $active_to
     * @param $tiers_str
     * @param $monthly_price
     * @param $users
     * @param $projects
     * @param $storage
     * @param string $active_from
     * @return CActiveRecord|PendingClientServiceSettings
     */
    public static function updateSettings($client_id,$active_to,$tiers_str,$monthly_price,$users,$projects,$storage,$active_from='') {
        $css = ClientServiceSettings::model()->findByPk($client_id);

        $datetime1 = $active_from ? date_create($active_from) : date_create(date('Y-m-d'));
        $datetime2 = date_create($active_to);
        $diff = date_diff($datetime1, $datetime2, true);
        $days_to_charge = $diff->days;

        //for previous settings
        $datetime_prev1 = date_create($css->Active_To);
        $datetime_prev2 = date_create($active_to);
        $diff = date_diff($datetime_prev1, $datetime_prev2, true);
        $days_to_charge_prev = $diff->days;

        $pcss = PendingClientServiceSettings::model()->findByPk($client_id);
        if (!$pcss) {
            $pcss = new PendingClientServiceSettings();
            $pcss->Client_ID = $client_id;
        }
        $added_price = ($monthly_price - $css->Fee);
        $added_price_til_the_end = floatval($added_price /30 * $days_to_charge);

        $base_price_till_the_end = floatval($css->Fee /30 * $days_to_charge_prev);

        $total_price_till_the_end = $added_price_til_the_end + $base_price_till_the_end;
        //$total_to_pay = floatval($monthly_price /30 * $days_to_charge);//price to pay till the end of period in usual way
        //$total_to_pay = floatval($css->Fee +$added_price_til_the_end);//price to pay till the end of period in usual way


            //we are recalculating pending settings only if it is not delayed settings.
            $pcss->Service_Level_ID = $tiers_str;
            $pcss->Additional_Users = $users ;
            $pcss->Additional_Projects	= $projects;
            $pcss->Additional_Storage = $storage;
            $pcss->Pending_Active_From = $active_from ? date('Y-m-d',strtotime($active_from)) : '0000-00-00';
            $pcss->Pending_Active_To = date('Y-m-d',strtotime($active_to));
        if (!$active_from) {
            $pcss->Fee = round($monthly_price,2);
            //$pcss->Fee_To_Upgrade = round($total_to_pay-$css->Fee,2);
            $pcss->Fee_To_Upgrade = round($total_price_till_the_end,2);
        } else {
            $pcss->Fee = round($monthly_price,2);
            $pcss->Fee_To_Upgrade = round($monthly_price,2);
        }
            $pcss->save();



            return $pcss;
    }

    /**Alternative version
     * @param $client_id
     * @param $active_to
     * @param $tiers_str
     * @param $monthly_price
     * @param $users
     * @param $projects
     * @param $storage
     * @param string $active_from
     * @return CActiveRecord|PendingClientServiceSettings
     */
    public static function updateSettingsAlternative($client_id,$active_to,$tiers_str,$new_monthly_price,$changed_fee,$add_users,$add_projects,$add_storage,$active_from='') {



        //3) write to pending client settings
        $pcss = PendingClientServiceSettings::model()->findByPk($client_id);

        if (!$pcss) {
            $pcss = new PendingClientServiceSettings();
            $pcss->Client_ID = $client_id;
        }
        $pcss->Service_Level_ID = $tiers_str;
        $pcss->Additional_Users = $add_users ;
        $pcss->Additional_Projects	= $add_projects;
        $pcss->Additional_Storage = $add_storage;
        $pcss->Pending_Active_From = $active_from ? date('Y-m-d',strtotime($active_from)) : '0000-00-00';
        $pcss->Pending_Active_To = date('Y-m-d',strtotime($active_to));
        $pcss->Fee = round($new_monthly_price,2);
        $pcss->Fee_To_Upgrade = round($changed_fee,2);
        $pcss->Approved =0;
        $pcss->save();

        return $pcss;
    }

    /**
     * Recalculate Pending Settings (based on number of day )
     * @param $pcss
     * @param $active_to
     * @param $monthly_price
     */
    public static function recalculateSettings($pcss,$active_to,$monthly_price) {

        $css = ClientServiceSettings::model()->findByPk($pcss->Client_ID);
        //for pending settings
        $datetime1 = date_create(date('Y-m-d'));
        $datetime2 = date_create($active_to);
        $diff = date_diff($datetime1, $datetime2, true);
        $days_to_charge = $diff->days;

        //for previous settings
        $datetime_prev1 = date_create($css->Active_To);
        $datetime_prev2 = date_create($active_to);
        $diff = date_diff($datetime_prev1, $datetime_prev2, true);
        $days_to_charge_prev = $diff->days;





        $added_price = ($monthly_price - $css->Fee);
        $added_price_til_the_end = floatval($added_price /30 * $days_to_charge);

        $base_price_till_the_end = floatval($css->Fee /30 * $days_to_charge_prev);

        $total_price_till_the_end = $added_price_til_the_end + $base_price_till_the_end;




        $pcss->Fee = round($monthly_price,2);
        $pcss->Fee_To_Upgrade = round($total_price_till_the_end,2);
        $pcss->save();

        }

    /**
     * Recalculate Pending Settings (based on number of months )
     * @param $pcss
     * @param $active_to
     * @param $monthly_price
     */
    public static function recalculateSettingsAlternative ($pcss) {

        $periods = Helper::calculatePeriodsBetweenDates(date('m/d/Y'),$pcss->Pending_Active_To);


        $new_price = $pcss->Fee*
        $added_price_til_the_end = floatval($added_price /30 * $days_to_charge);

        $base_price_till_the_end = floatval($css->Fee /30 * $days_to_charge_prev);

        $total_price_till_the_end = $added_price_til_the_end + $base_price_till_the_end;




        $pcss->Fee = round($monthly_price,2);
        $pcss->Fee_To_Upgrade = round($total_price_till_the_end,2);
        $pcss->save();

    }



    public static function calculateFee($active_to,$monthly_price) {

        $datetime1 = date_create(date('Y-m-d'));
        $datetime2 = date_create($active_to);
        $diff = date_diff($datetime1, $datetime2, true);

        $days_to_charge = $diff->days;

        $total_to_pay = number_format($monthly_price /30 * $days_to_charge,2);//price to pay till the end of period
    }


    /**
     * Sets pending settings field Approved to 1;
     * @param $client_id
     */
    public static function setApproved($client_id){
        $pcss = PendingClientServiceSettings::model()->findByPk($client_id);
        //var_dump($pcss);die;
        $pcss->Approved = 1;
        $pcss->save();

    }

    /**
     * Sets pending settings field Approved to 1;
     * @param $client_id
     */
    public function getSettings(){

        return array (
            'level' => $this->Service_Level_ID,
            'level_desc' => 'Tiers: '. $this->Service_Level_ID.'<br/> Active to :<br/>'.date('m/d/Y',strtotime($this->Pending_Active_To)),
            'users'=>$this->Additional_Users,
            'projects'=>$this->Additional_Projects,
            'storage'=>$this->Additional_Storage,
            'fee_to_upgrade'=>$this->Fee_To_Upgrade,
            'monthly_fee'=>$this->Fee
        );

    }


}
