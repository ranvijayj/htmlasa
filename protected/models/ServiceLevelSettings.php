<?php

/**
 * This is the model class for table "service_level_settings".
 *
 * The followings are the available columns in table 'service_level_settings':
 * @property integer $Service_Level_ID
 * @property string $Tier_Name
 * @property integer $Users_Count
 * @property integer $Projects_Count
 * @property integer $Storage_Count
 * @property string $Base_Fee
 * @property string $Additional_User_Fee
 * @property string $Additional_Project_Fee
 * @property string $Additional_Storage_Fee
 * @property integer $Trial_Period
 * @property integer $Description
 */
class ServiceLevelSettings extends CActiveRecord
{
    /**
     * Default values
     */
    const DEFAULT_SERVICE_LEVEL = 1;
    const DEFAULT_ADD_USERS = 1;
    const DEFAULT_ADD_PROJECTS = 0;
    const DEFAULT_ADD_STORAGE = 0;

    const MAX_USERS = 500;
    const MAX_PROJECTS = 100;
    const MAX_STORAGE_GB = 1000;

    static $storage_values = array('1','5','10','15','20','25','30','35','40','45','50','55','60','65','70','75','80','85','90','95','100',
        '105','110','115','120','125','130','135','140','145','150','155','160','165','170','175','180','185','190','195','200',
        '205','210','215','220','225','230','235','240','245','250','255','260','265','270','275','280','285','290','295','300',
        '305','310','315','320','325','330','335','340','345','350','355','360','365','370','375','380','385','390','395','400',
        '405','410','415','420','425','430','435','440','445','450','455','460','465','470','475','480','485','490','495','400',
        '505','510','515','520','525','530','535','540','545','550','555','560','565','570','575','580','585','590','595','500',
        '605','610','615','620','625','630','635','640','645','650','655','660','665','670','675','680','685','690','695','600',
        '705','710','715','720','725','730','735','740','745','750','755','760','765','770','775','780','785','790','795','700',
        '805','810','815','820','825','830','835','840','845','850','855','860','865','870','875','880','885','890','895','900',
        '905','910','915','920','925','930','935','940','945','950','955','960','965','970','975','980','985','990','995','1000',
    );
    //array of available values for storage field

    /**
     * List of protected pages and access to them for different Tier levels
     * @var array
     */
    static $serviceLevelProtectedPagesAccess = array(
        1 => array(
            'library',
            'data_entry' => array(
                'w9',
                'assign',
            ),
            'vendors'=>array(
                'index',
                'detail',
            ),
        ),
        2 => array(
            'library',
            'batches',
            'po',
            'coa',
            'vendors'=>array(
                'index',
                'detail',
                'manage'
            ),
            'w9'=>array(
                'index',
                'detail',
            ),
            'data_entry' => array(
                'w9',
                'po',
                'payroll',
                'je',
                'ar',
                'assign'
            ),

        ),
        3 => array( //AP Tier

            'batches',
            'vendors',
            'ap',
            'payments',
            'library',
            'coa'=>array(
                'index',
                'detail',
                'manage'
            ),
            'w9'=>array(
                'index',
                'detail',
            ),
            'data_entry' => array(
                'w9',
                'ap',
                'payments',
                'payroll',
                'je',
                'ar',
                'assign',
                //'po',
                //'pc',
                //'filing',
            ),
        ),
        4 => array( //PC Tier
            'library',
            'batches',
            'po',
            'ap',
            'payments',
            'coa',
            'pc',
            'vendors'=>array(
                'index',
                'detail',
                'manage'
            ),
            'data_entry' => array(
                'w9',
                'filing',
                'assign',
                'po',
                'ap',
                'payments',
                'pc',
                'payroll',
                'je',
                'ar',
            ),
        ),
    );

    /**
     * List of available doc types for different Tier levels
     * @var array
     */
    static $serviceLevelAvailableDocTypes = array(
        1 => array( //W9 Tier
            'docs' => array(
                Documents::W9,
                Documents::LB,
            ),
            'docsHtml' => '<ul class="width150">
                                <li data-doc-type="W9">W9</li>
                                <li data-doc-type="LB">Library</li>
                            </ul>',
        ),
        2 => array( //PO Tier
            'docs' => array(
                Documents::W9,
                Documents::PO,
                Documents::BU,
                Documents::LB,
                Documents::GF,
                Documents::PR,
                Documents::JE,
                Documents::AR,
            ),
            'docsHtml' => '<ul class="width150">

                                <li data-doc-type="W9">W9</li>
                                <li data-doc-type="PO">Purchase Order</li>
                                <li data-doc-type="BU">Backup</li>
                                <li data-doc-type="LB">Library</li>
                                <li data-doc-type="GF">General</li>
                                <li data-doc-type="PR">Payroll</li>
                                <li data-doc-type="JE">Journal Entry</li>
                                <li data-doc-type="AR">Accounts Receivable</li>
                            </ul>',
        ),
        3 => array( //AP Tier
            'docs' => array(
                Documents::W9,
                Documents::AP,
                Documents::BU,
                Documents::PM,
                Documents::LB,
                Documents::GF,
                Documents::PR,
                Documents::JE,
                Documents::AR,
            ),
            'docsHtml' => '<ul class="width150">

                                <li data-doc-type="W9">W9</li>
                                <li data-doc-type="AP">Accounts Payable</li>
                                <li data-doc-type="BU">Backup</li>
                                <li data-doc-type="PM">Payment</li>
                                <li data-doc-type="LB">Library</li>
                                <li data-doc-type="GF">General</li>
                                <li data-doc-type="PR">Payroll</li>
                                <li data-doc-type="JE">Journal Entry</li>
                                <li data-doc-type="AR">Accounts Receivable</li>
                            </ul>',
        ),
        4 => array(  //PC Tier
            'docs' => array(
                Documents::W9,
                Documents::PO,
                Documents::AP,
                Documents::BU,
                Documents::PM,
                Documents::LB,
                Documents::GF,
                Documents::PR,
                Documents::PC,
                Documents::JE,
                Documents::AR,
            ),
            'docsHtml' => '<ul class="width150">
                                <li data-doc-type="AP">Accounts Payable</li>
                                <li data-doc-type="W9">W9</li>
                                <li data-doc-type="PO">Purchase Order</li>
                                <li data-doc-type="BU">Backup</li>
                                <li data-doc-type="PM">Payment</li>
                                <li data-doc-type="LB">Library</li>
                                <li data-doc-type="GF">General</li>
                                <li data-doc-type="PR">Payroll</li>
                                <li data-doc-type="PC">Petty Cash (Expense)</li>
                                <li data-doc-type="JE">Journal Entry</li>
                                <li data-doc-type="AR">Accounts Receivable</li>
                            </ul>',
        ),
    );

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'service_level_settings';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Tier_Name, Users_Count, Projects_Count, Storage_Count, Base_Fee, Additional_User_Fee, Additional_Project_Fee, Additional_Storage_Fee, Trial_Period', 'required'),
			array('Users_Count, Projects_Count, Storage_Count, Trial_Period', 'numerical', 'integerOnly'=>true),
			array('Tier_Name', 'length', 'max'=>25),
			array('Base_Fee, Additional_User_Fee, Additional_Project_Fee, Additional_Storage_Fee', 'length', 'max'=>13),
            array('Description', 'length', 'max'=>500),
            array('Service_Level_ID', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Service_Level_ID, Description, Tier_Name, Users_Count, Projects_Count, Storage_Count, Base_Fee, Additional_User_Fee, Additional_Project_Fee, Additional_Storage_Fee, Trial_Period', 'safe', 'on'=>'search'),
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
     * Get array of service levels for building selects
     */
    public static function getServiceLevelsOptionsList()
    {
        $serviceLevelsList = array();
        $condition = new CDbCriteria();
        $condition->condition = "Service_Level_ID != 4";
        $serviceLevels = self::model()->findAll($condition);
        foreach ($serviceLevels as $serviceLevel) {
            $serviceLevelsList[] = array(
                "Tier_ID" => $serviceLevel->Service_Level_ID,
                "Tier_Name" => $serviceLevel->Tier_Name,
                "Users_Count" => $serviceLevel->Users_Count,
                "Projects_Count" => $serviceLevel->Projects_Count,
                "Storage_Count" => $serviceLevel->Storage_Count,
                "Base_Fee" => $serviceLevel->Base_Fee
            );
        }
        return $serviceLevelsList;
    }


    /**
     * Returns next 12 months beginning from current
     * @param $current_active_to
     * @return array
     */
    public static function getNextActiveToList($current_active_to) {
        $new_dates_array = array(date("m/d/Y", strtotime($current_active_to)));

        for ($i=1;$i<=12;$i++) {

            $new_value = strtotime(date("m/d/Y", strtotime($current_active_to)) . " +".$i." month");

            $new_dates_array[] = date("m/d/Y", $new_value);
        }
        return $new_dates_array;
    }

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'Service_Level_ID' => 'Service Level',
			'Tier_Name' => 'Tier Name',
			'Users_Count' => 'Users Count',
			'Projects_Count' => 'Projects Count',
			'Storage_Count' => 'Storage Count',
			'Base_Fee' => 'Base Fee',
			'Additional_User_Fee' => 'Additional User Fee',
			'Additional_Project_Fee' => 'Additional Project Fee',
			'Additional_Storage_Fee' => 'Additional Storage Fee',
			'Trial_Period' => 'Trial Period',
            'Description' => 'Description',
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

		$criteria->compare('Service_Level_ID',$this->Service_Level_ID);
		$criteria->compare('Tier_Name',$this->Tier_Name,true);
		$criteria->compare('Users_Count',$this->Users_Count);
		$criteria->compare('Projects_Count',$this->Projects_Count);
		$criteria->compare('Storage_Count',$this->Storage_Count);
		$criteria->compare('Base_Fee',$this->Base_Fee,true);
		$criteria->compare('Additional_User_Fee',$this->Additional_User_Fee,true);
		$criteria->compare('Additional_Project_Fee',$this->Additional_Project_Fee,true);
		$criteria->compare('Additional_Storage_Fee',$this->Additional_Storage_Fee,true);
		$criteria->compare('Trial_Period',$this->Trial_Period);
        $criteria->compare('Description',$this->Description, true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ServiceLevelSettings the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}




    public static function getSummaryName($levels) {
        $i= 0;$result ='';$limiter='';
        $tiers_array = explode(',',$levels);
        foreach ($tiers_array as $id) {
            $settings = ServiceLevelSettings::model()->findByPk($id);
            if ($i != 0) {$limiter = ', ';}
            $result .= $limiter.$settings->Tier_Name;
            $i++;
        }
        return $result;
    }



    /**
     * Returns array of summarized settings for current client
     * @return array Tier_Name,Users_Count, Projects_Count, Storage_Count, Base_Fee, Additional_Users,Additional_Projects,Additional_Storage,Additional_Fee
     */
    public static function getSummarySettings($client_id) {
        $result = array();
        $i= 0;
        $limiter ='';
        $client_id = $client_id ? $client_id :Yii::app()->user->clientID;

        $css = ClientServiceSettings::model()->findByPk($client_id);
        $result['Tiers_Str'] = $css->Service_Level_ID;
        $tiers_array = explode(',',$result['Tiers_Str']);
        foreach ($tiers_array as $id) {
            $settings = ServiceLevelSettings::model()->findByPk($id);

            if ($i != 0) {$limiter = ', ';}
            if ($settings->Service_Level_ID == 1) {
                $w9_exist =true;
                $w9_price = $settings->Base_Fee;
                $result['levels_checksum']+=1;
            }
            if ($settings->Service_Level_ID == 2) {
                $result['levels_checksum']+=5;
            }
            if ($settings->Service_Level_ID == 3) {
                $result['levels_checksum']+=10;
            }if ($settings->Service_Level_ID == 4) {
                $result['levels_checksum']+=20;
            }

            $result['Tier_Name'] .= $limiter.$settings->Tier_Name;
            $i++;
         }

        $add_users_price = $settings->Additional_User_Fee;
        $add_projects_price = $settings->Additional_Project_Fee;
        $add_storage_price = $settings->Additional_Storage_Fee;

        $result['Base_Fee'] =  ClientServiceSettings::CalculateBaseFee($tiers_array);
        $result['Users_Count'] = $settings->Users_Count;
        $result['Projects_Count'] = $settings->Projects_Count;
        $result['Storage_Count'] = $settings->Storage_Count;

            $result['Additional_Users'] =$css->Additional_Users;
            $result['Additional_Projects'] =$css->Additional_Projects;
            $result['Additional_Storage'] =$css->Additional_Storage;
            $result['Storage_Index'] = ServiceLevelSettings::getStorageIndexByValue($css->Additional_Storage);
            $result['Additional_Fee'] = $add_users_price * $css->Additional_Users + $add_projects_price*($css->Additional_Projects) + $add_storage_price* ($result['Storage_Index']);

            //$result['Additional_Storage'] =$settings->	Additional_Storage;

        $users = UsersClientList::model()->findAllByAttributes(array(
            'Client_ID'=>$client_id
        ));
        $result['Used_Users'] =count($users);

        $result['Used_Projects'] = Projects::clientProjectUsage();
        $result['Used_Storage'] = ceil(Images::getUsedStorage($client_id));


        return $result;
    }

    /**
     * Calculates index of given value in the array of available values
     * @param $value
     * @return int
     */
    public static function getStorageIndexByValue($value) {

        for ($i=0;$i<count(self::$storage_values);$i++) {

            if ( $value == self::$storage_values[$i] || ($value > self::$storage_values[$i-1] && $value < self::$storage_values[$i]) ) {
                $value = $i;
                break;
            }

        }

        return $value;
    }

}
