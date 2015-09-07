<?php

/**
 * This is the model class for table "client_service_settings".
 *
 * The followings are the available columns in table 'client_service_settings':
 * @property integer $Client_ID
 * @property integer $Service_Level_ID
 * @property integer $Additional_Users
 * @property integer $Additional_Projects
 * @property integer $Additional_Storage
 * @property string $Fee
 * @property string $Active_To
 * @property string $Locked
 */
class ClientServiceSettings extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'client_service_settings';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Client_ID, Active_To', 'required'),
			array('Client_ID, Additional_Users, Additional_Projects, Additional_Storage', 'numerical', 'integerOnly'=>true),
			array('Fee,Service_Level_ID', 'length', 'max'=>13),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Client_ID, Service_Level_ID, Additional_Users, Additional_Projects, Additional_Storage, Fee, Active_To', 'safe', 'on'=>'search'),
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
            'service_level'=>array(self::BELONGS_TO, 'ServiceLevelSettings', 'Service_Level_ID'),
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
			'Active_To' => 'Active To',
		);
	}

    /**
     * Set Fee for current company service settings
     */
    public function setFee()
    {
        $this->Fee = self::getFeeByValues(
            $this->Service_Level_ID,
            $this->Additional_Users,
            $this->Additional_Projects,
            $this->Additional_Storage
        );
    }

    /**
     * Calculate total Fee by level settings and additional params
     * @param $serviceLevelID
     * @param $addUsers
     * @param $addProjects
     * @param $addStorage
     * @return array|int|mixed|null
     */
    public static function getFeeByValues($serviceLevelID, $addUsers, $addProjects, $addStorage)
    {
        $serviceLevelID = strval($serviceLevelID);
        $levels_array = explode(',',$serviceLevelID);
        $addUsers = intval($addUsers);
        $addProjects = intval($addProjects);
        $addStorage = intval($addStorage);

        $fee = self::CalculateBaseFee($levels_array);
        $base_level_settings = ClientServiceSettings::getBaseTierValues($levels_array);
        //$serviceLevel = ServiceLevelSettings::model()->findByPk(1);

        $fee += $addUsers*$base_level_settings['Max_Add_User_Fee'] + $addProjects*$base_level_settings['Max_Add_Project_Fee'] + $addStorage*$base_level_settings['Max_Add_Storage_Fee'];


        return $fee;
    }


    public static function Calculation($users,$projects,$storage,$active_to,$tiers_str,$client_id,$active_from='') {

        $css = ClientServiceSettings::model()->findByPk($client_id);
        if ($css->CheckMinMax($users,$projects,$storage)) {

            $tiers_str_before = $css->Service_Level_ID;
            $tiers_arr_before = explode(',',$tiers_str_before);
            //we need to divide inputted settings into base and additional
            $base_level_settings_before = ClientServiceSettings::getBaseTierValues($tiers_arr_before);
            $base_level_settings_after = ClientServiceSettings::getBaseTierValues(explode(',',$tiers_str));

            $levels_check_sum_before = ClientServiceSettings::getLevelsCheckSum($tiers_arr_before);
            $levels_check_sum_after = ClientServiceSettings::getLevelsCheckSum(explode(',',$tiers_str));

            $count_sum_before = $css->Additional_Users + $css->Additional_Projects + $css->Additional_Storage + $base_level_settings_before['Users_Count'] + $base_level_settings_before['Projects_Count'] + $base_level_settings_before['Storage_Count'] + $levels_check_sum_before;
            $count_sum_after = $users + $projects + $storage + $levels_check_sum_after;// + $base_level_settings_after['Users_Count'] + $base_level_settings_after['Projects_Count'] + $base_level_settings_after['Storage_Count'];
            $service_level_grown =($count_sum_after > $count_sum_before) ? 1 : 0;
            $active_to_date_changed = (strtotime($css->Active_To)< strtotime($active_to)) ? 1 : 0;
            $service_level_grown = $active_to_date_changed ? 1 : $service_level_grown;

            $base_fee_after = ClientServiceSettings::CalculateBaseFee(explode(',',$tiers_str));

            //extra(additional)
            $add_users = intval($users) - $base_level_settings_after['Users_Count'];//- $css->Additional_Users ;
            $add_users = ( $add_users > 0 ) ? $add_users : 0;

            $add_projects = $projects - $base_level_settings_after['Projects_Count'];//-$css->Additional_Projects ;
            $add_projects = ( $add_projects > 0 ) ? $add_projects : 0;

            $add_storage = $storage - $base_level_settings_after['Storage_Count'];//-$css->Additional_Storage ;
            $add_storage = ( $add_storage > 0 ) ? $add_storage : 0;

            $add_fee = $add_users * $base_level_settings_after['Max_Add_User_Fee'] + $add_projects * $base_level_settings_after['Max_Add_Project_Fee'] + $add_storage * $base_level_settings_after['Max_Add_Storage_Fee'];
            $new_monthly_price = $base_fee_after + $add_fee;

            $old_add_fee  = $css->Additional_Users * $base_level_settings_after['Max_Add_User_Fee'] + $css->Additional_Projects * $base_level_settings_after['Max_Add_Project_Fee'] + $css->Additional_Storage * $base_level_settings_after['Max_Add_Storage_Fee'];
            $add_fee_changed = $old_add_fee -$add_fee;
            $old_monthly_price = $css->Fee;

            //1) If active_to changed, we need to calculate how many months(periods)
            $periods_for_current = Helper::calculatePeriodsBetweenDates(date('m/d/Y',strtotime($css->Active_To)),$active_to);
            $periods_for_added = Helper::calculatePeriodsBetweenDates(date('m/d/Y'),$active_to);

            //2) Multiply changes on the amount of periods
            $changed_fee = round(floatval($new_monthly_price) - floatval($old_monthly_price),2)*$periods_for_added;
            $changed_fee = ($changed_fee > 0) ? $changed_fee : 0;

            //for cases when period changed and additional settings changed
            if (!$active_from && $active_to_date_changed) {
                $changed_fee = $changed_fee + $new_monthly_price*$periods_for_current;
            }

            return array(
                'new_monthly_price'=>$new_monthly_price,
                'changed_fee'=>$changed_fee,
                'periods'=>$periods,
                'service_level_grown'=>$service_level_grown,
                'add_users'=>$add_users,
                'add_projects'=>$add_projects,
                'add_storage'=>$add_storage,

            );

        } else {
            return false;
        }


    }



    /**
     * Calculate total Fee by level settings and additional params from set of tier levels
     * @param $serviceLevelID
     * @param $addUsers
     * @param $addProjects
     * @param $addStorage
     * @return array|int|mixed|null
     */
    public static function getSumFeeByValues($tier_levels_array, $addUsers, $addProjects, $addStorage)
    {

        $base_user_number = 1;//ServiceLevelSettings::getBaseUsersNumbers($tier_levels_array);
        $base_storage_volume = $base_user_number;//should be replaced by calculated value if more then 1 GP for tier allowed
        $base_project_number = $base_user_number;//should be replaced by calculated value if more then 1 project for tier allowed

        $addStorage = intval($addStorage)>0 ? intval($addStorage)-$base_storage_volume : intval($addStorage);
        $addProjects = intval($addProjects)>0 ? intval($addProjects)-$base_project_number : intval($addProjects);
        $addUsers = intval($addUsers)>0 ? intval($addUsers)-$base_user_number : intval($addUsers);
        $fee = 0;
        foreach ($tier_levels_array as $id) {
            $serviceLevel = ServiceLevelSettings::model()->findByPk($id);
            if ($serviceLevel) {
                $fee += $serviceLevel->Base_Fee + $addUsers*$serviceLevel->Additional_User_Fee +
                    $addProjects*$serviceLevel->Additional_Project_Fee + intval($addStorage)*$serviceLevel->Additional_Storage_Fee;
            }
        }

        return $fee;
    }

    public static function getBaseTierValues($tier_levels_array)
    {
        $users_num = 0;
        $projects_num = 0;
        $storage_num = 0;

        //prices
        $add_users_price = 0;
        $add_projects_price = 0;
        $add_storage_price = 0;

        foreach ($tier_levels_array as $id) {
            $serviceLevel = ServiceLevelSettings::model()->findByPk($id);
            if ($serviceLevel) {
                /*$users_num += $serviceLevel->Users_Count;
                $projects_num += $serviceLevel->Projects_Count;
                $storage_num += $serviceLevel->Storage_Count;*/
                $users_num = $serviceLevel->Users_Count;
                $projects_num = $serviceLevel->Projects_Count;
                $storage_num = $serviceLevel->Storage_Count;

                //calculate max price of tier set
                $add_users_price = ($add_users_price < $serviceLevel->Additional_User_Fee) ? $serviceLevel->Additional_User_Fee : $add_users_price;
                $add_projects_price = ($add_projects_price < $serviceLevel->Additional_Project_Fee) ? $serviceLevel->Additional_Project_Fee : $add_projects_price;
                $add_storage_price = ($add_storage_price < $serviceLevel->Additional_Storage_Fee) ? $serviceLevel->Additional_Storage_Fee : $add_storage_price;
            }



        }

        return array(
            'Users_Count'=>$users_num,
            'Projects_Count'=>$projects_num,
            'Storage_Count'=>$storage_num,
            'Max_Add_User_Fee'=>$add_users_price,
            'Max_Add_Project_Fee'=>$add_projects_price,
            'Max_Add_Storage_Fee'=>$add_storage_price,
        );
    }

    public static function CalculateBaseFee($tier_levels_array)
    {
        $fee = 0;
        $w9_exist =false; //if this flag is true we have to cons W9 Tier value from the result price;
        $w9_price = 0;
        foreach ($tier_levels_array as $id) {
            $serviceLevel = ServiceLevelSettings::model()->findByPk($id);

            if( $serviceLevel->Service_Level_ID == 1) {
                $w9_exist =true;
                $w9_price = $serviceLevel->Base_Fee;
            }
            if ($serviceLevel) {
                $fee += $serviceLevel->Base_Fee;
            }

        }
        if ($w9_exist && $fee>$w9_price) {$fee = $fee - $w9_price;} //prices higher than w9 price we have to reduce, because w9 service is included to other levels
        return $fee;
    }

    /**
     * Check necessity to show monthly payment block
     */
    public function checkShowMonthlyPaymentAlert()
    {
        $datetime1 = date_create(date('Y-m-d'));
        $datetime2 = date_create($this->Active_To);
        $interval = date_diff($datetime1, $datetime2, true);
        $dateDifference = intval($interval->format('%R%a'));
        if (($dateDifference <= 10 && $this->Active_To > date('Y-m-d')) || $this->Active_To <= date('Y-m-d')) {
            return true;
        }
        return false;
    }

    /**
     * Add client service settings for new clients
     * @param $clientID
     * @param bool $trial
     * @return ClientServiceSettings
     */
    public static function addClientServiceSettings($clientID, $trial = true)
    {
        $clientID = intval($clientID);

        $settings = self::model()->findByAttributes(array(
            'Client_ID' => $clientID,
        ));

        if (!$settings) {
            $settings = new ClientServiceSettings();
            $settings->Client_ID = $clientID;
            $settings->Service_Level_ID = ServiceLevelSettings::DEFAULT_SERVICE_LEVEL;
            $settings->Additional_Users = ServiceLevelSettings::DEFAULT_ADD_USERS;


            $settings->Active_To = date('Y-m-d');
            //$settings->Active_To = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') + 5, date('Y'))); //current days + 5 days
            /*$settings->Fee = ClientServiceSettings::getFeeByValues(
                ServiceLevelSettings::DEFAULT_SERVICE_LEVEL,
                ServiceLevelSettings::DEFAULT_ADD_USERS,
                ServiceLevelSettings::DEFAULT_ADD_PROJECTS,
                ServiceLevelSettings::DEFAULT_ADD_STORAGE
            );*/
            $settings->Fee = ClientServiceSettings::getSumFeeByValues(
                explode(',',$settings->Service_Level_ID), // tier levels array
                $settings->Additional_Users,
                $settings->Additional_Projects,
                $settings->Additional_Storage
            );
            $settings->save();
        }

        if ($trial && $settings->Active_To <= date('Y-m-d')) {
            $addDays = $settings->service_level->Trial_Period;
            $dateOb = date_create(date('Y-m-d'));
            date_add($dateOb, date_interval_create_from_date_string($addDays . ' days'));
            $activeTo = date_format($dateOb, 'Y-m-d');
            $settings->Active_To = $activeTo;
            $settings->save();
        }

        return $settings;
    }

    /**
     * Get client service settings
     * @param $clientID
     * @return ClientServiceSettings
     */
    public static function getClientServiceSettings($clientID)
    {
        $clientID = intval($clientID);

        $settings = self::model()->findByAttributes(array(
            'Client_ID' => $clientID,
        ));
        if (!$settings) {
            $settings = new ClientServiceSettings();
            $settings->Client_ID = $clientID;
            $settings->Active_To = date('Y-m-d');
            $settings->Fee = ClientServiceSettings::getFeeByValues(
                ServiceLevelSettings::DEFAULT_SERVICE_LEVEL,
                ServiceLevelSettings::DEFAULT_ADD_USERS,
                ServiceLevelSettings::DEFAULT_ADD_PROJECTS,
                ServiceLevelSettings::DEFAULT_ADD_STORAGE
            );
            $settings->save();
        }
        return $settings;
    }

    /**
     * Get available Storage for Client
     * @param $clientID
     * @return int
     */
    public static function getAvailableStorage($clientID)
    {
        $clientID = intval($clientID);
        $availableStorage = 0;

        $clientServiceSettings = self::model()->with('service_level')->findByAttributes(array(
            'Client_ID' => $clientID
        ));

        if ($clientServiceSettings) {
            $availableStorage = $clientServiceSettings->Additional_Storage + $clientServiceSettings->service_level->Storage_Count;
        }

        return $availableStorage;
    }

    /**
     * Get available count of Users for Client
     * @param $clientID
     * @return int
     */
    public static function getAvailableUsersCount($clientID)
    {
        $clientID = intval($clientID);
        $availableUsersCount = 0;

        $clientServiceSettings = self::model()->with('service_level')->findByAttributes(array(
            'Client_ID' => $clientID,
        ));

        if ($clientServiceSettings) {
            $availableUsersCount = $clientServiceSettings->Additional_Users + $clientServiceSettings->service_level->Users_Count;
        }

        return $availableUsersCount;
    }

    /**
     * Get available count of Projects for Client
     * @param $clientID
     * @return int
     */
    public static function getAvailableProjectsCount($clientID)
    {
        $clientID = intval($clientID);
        $availableProjectsCount = 0;

        $clientServiceSettings = self::model()->with('service_level')->findByAttributes(array(
            'Client_ID' => $clientID,
        ));

        if ($clientServiceSettings) {
            $availableProjectsCount = $clientServiceSettings->Additional_Projects + $clientServiceSettings->service_level->Projects_Count;
        }

        return $availableProjectsCount;
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
		$criteria->compare('Active_To',$this->Active_To,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ClientServiceSettings the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public static function AddThreeDays($client_id) {

        $css = ClientServiceSettings::model()->findByPk($client_id);
    //    $pcss = PendingClientServiceSettings::model()->findByPk($css->Client_ID);

        $current_date_exp = $css->Active_To;
        $three_days_add = date('Y-m-d',strtotime("+3 day", strtotime($current_date_exp)));



    //    $css->Service_Level_ID = $pcss->Service_Level_ID;
    //    $css->Additional_Users = $pcss->Additional_Users - $summary_settings['Users_Count'];
    //    $css->Additional_Projects = $pcss->Additional_Projects - $summary_settings['Projects_Count'];
    //    $css->Additional_Storage = $pcss->Additional_Storage - $summary_settings['Storage_Count'];
    //    $css->Active_To = $pcss->Pending_Active_To;
          $css->Active_To = $three_days_add;

    //    $css->Fee = $pcss->Fee;

          $css->Locked = date('Y-m-d'); //means that clients settings were locked on this day, and from this date we will count trial period
          $css->save();

    //    $pcss->delete();

    }

    public static function  checkUploadPossibility($filesize) {
        $availableStorage = ClientServiceSettings::getAvailableStorage(Yii::app()->user->clientID);
        $usedStorage = Images::getUsedStorage(Yii::app()->user->clientID);

        if (($usedStorage + $filesize/(1024*1024*1024)) > $availableStorage) {
            return false;
        } else {
            return true;
        }
    }

    public function recalculate() {

        $base_fee_prev = ClientServiceSettings::CalculateBaseFee(explode(',',$this->Service_Level_ID));

        $add_fee_prev = ClientServiceSettings::CalculatePrevAddFee($this->Additional_Users,$this->Additional_Projects,$this->Additional_Storage);
        $monthly_price_prev = $base_fee_prev + $add_fee_prev;

        $this->Fee = $monthly_price_prev;
        $this->save();

    }

    public function CheckMinMax($users,$projects,$storage){
        $result = true;

        $summary_sl_settings = ServiceLevelSettings::getSummarySettings(Yii::app()->user->clientID);

        if ($users < $summary_sl_settings['Used_Users'] || $users>ServiceLevelSettings::MAX_USERS) $result = false;
        if ($projects < $summary_sl_settings['Used_Projects'] || $projects>ServiceLevelSettings::MAX_PROJECTS) $result = false;
        if ($storage < $summary_sl_settings['Used_Storage'] || $storage>ServiceLevelSettings::MAX_STORAGE_GB) $result = false;

        return $result;
    }

    public static function CheckMinMaxValues($users,$projects,$storage){
        $result = true;

        $summary_sl_settings = ServiceLevelSettings::getSummarySettings(Yii::app()->user->clientID);

        if ($users < $summary_sl_settings['Used_Users'] || $users>ServiceLevelSettings::MAX_USERS) $result = false;
        if ($projects < $summary_sl_settings['Used_Projects'] || $projects>ServiceLevelSettings::MAX_PROJECTS) $result = false;
        if ($storage < $summary_sl_settings['Used_Storage'] || $storage>ServiceLevelSettings::MAX_STORAGE_GB) $result = false;

        return $result;
    }

    public static function getLevelsCheckSum($levels_array){
        $check_sum = 1;
        foreach ($levels_array as $item) {
            $check_sum = $check_sum * 2;
        }
        return $check_sum;
    }

    public static function CalculatePrevAddFee($users,$projects,$storage){

        $storage_index = ServiceLevelSettings::getStorageIndexByValue($storage);
        return $users*9.95+($projects)*4.95+($storage_index)*7.95;
    }

    public static function CalculateAddFee($users,$projects,$storage){


        $storage_index = ServiceLevelSettings::getStorageIndexByValue($storage);

        return $users*9.95+($projects-1)*4.95+($storage_index)*7.95;
    }





}
