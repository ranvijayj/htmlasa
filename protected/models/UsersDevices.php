<?php

/**
 * This is the model class for table "users_devices".
 *
 * The followings are the available columns in table 'users_devices':
 * @property integer $Device_ID
 * @property integer $User_ID
 * @property integer $Mobile
 * @property string $OS
 * @property integer $Browser
 * @property string $IP
 * @property string $TymeZone
 * @property string $ScreenSize
 * @property string $Language
 * @property string $Fonts
 * @property integer $Trusted
 * @property integer $Logged
 * @property integer $Last_Logged
 * @property string $MOB_Hash
 */
class UsersDevices extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'users_devices';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('User_ID, OS, IP, Browser,TymeZone, ScreenSize, MOB_Hash', 'required'),
			array('User_ID, Mobile,  Trusted, Logged', 'numerical', 'integerOnly'=>true),
			array('Browser', 'length', 'max'=>100),
			array('OS', 'length', 'max'=>50),
			array('IP', 'length', 'max'=>40),
			array('TymeZone', 'length', 'max'=>30),
			array('ScreenSize', 'length', 'max'=>14),
			array('Language', 'length', 'max'=>10),
			array('MOB_Hash', 'length', 'max'=>100),
			array('Fonts', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Device_ID, User_ID, Mobile, OS, Browser, IP, TymeZone, ScreenSize, Language, Fonts, Trusted, Logged, MOB_Hash', 'safe', 'on'=>'search'),
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
			'Device_ID' => 'Device',
			'User_ID' => 'User',
			'Mobile' => 'Mobile',
			'OS' => 'Os',
			'Browser' => 'Browser',
			'IP' => 'Ip',
			'TymeZone' => 'Tyme Zone',
			'ScreenSize' => 'Screen Size',
			'Language' => 'Language',
			'Fonts' => 'Fonts',
			'Trusted' => 'Trusted',
			'Logged' => 'Logged',
			'MOB_Hash' => 'Mob Hash',
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

		$criteria->compare('Device_ID',$this->Device_ID);
		$criteria->compare('User_ID',$this->User_ID);
		$criteria->compare('Mobile',$this->Mobile);
		$criteria->compare('OS',$this->OS,true);
		$criteria->compare('Browser',$this->Browser);
		$criteria->compare('IP',$this->IP,true);
		$criteria->compare('TymeZone',$this->TymeZone,true);
		$criteria->compare('ScreenSize',$this->ScreenSize,true);
		$criteria->compare('Language',$this->Language,true);
		$criteria->compare('Fonts',$this->Fonts,true);
		$criteria->compare('Trusted',$this->Trusted);
		$criteria->compare('Logged',$this->Logged);
		$criteria->compare('MOB_Hash',$this->MOB_Hash,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return UsersDevices the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public static function addCurrentDeviceToList($user_id,$timezone,$resolution,$remote=false) {

        $browser = Helper::getBrowser();

        $os = Helper::getOs();

        $is_mobile = 0;
        if (Helper::getMobileDetect()->isMobile() || Helper::getMobileDetect()->isTablet() || Helper::checkMobile()) $is_mobile = 1;

        $str = $is_mobile.' '.$os.' '.$browser['name'].' '.$browser['version'];

        $device = UsersDevices::model()->findByAttributes(array(
                'User_ID'=>$user_id,
                'MOB_Hash' =>sha1($str)
        ));


        if (!$device) {
            $device = new UsersDevices();

        }
                $device->User_ID = $user_id;
                $device->Mobile = $is_mobile;
                $device->Browser = $browser['name'].' '.$browser['version'];
                $device->OS = $os;
                $device->IP = Helper::getIp();
                $device->TymeZone = $timezone;
                $device->ScreenSize = $resolution;
                $device->MOB_Hash = sha1($str);
                $device->Trusted = 1;
                $device->Logged = 1;
                if ($remote) {
                    $device->Remote_Login = 1;
                    $device->Trusted = 0;
                    $device->Logged = 0;
                }
                $device->save();


        return  $device->Device_ID;
    }

    /**
     * Updates device params
     * @param $user_id
     * @param $timezone
     * @param $resolution
     */
    public static function updateLastLoggedTime($user_id,$timezone,$resolution) {
        $device = self::getSmartDevInstance($_COOKIE['device_hash'],$user_id,$timezone,$resolution);

        if (!$device) {
            $device = new UsersDevices();
        }

        $browser = Helper::getBrowser();
        $os = Helper::getOs();
        $is_mobile = 0;
        if (Helper::getMobileDetect()->isMobile() || Helper::getMobileDetect()->isTablet() || Helper::checkMobile()) $is_mobile = 1;
        $str = $is_mobile.' '.$os.' '.$browser['name'].' '.$browser['version'];

                $device->User_ID = $user_id;
                $device->Mobile = $is_mobile;
                $device->Browser = $browser['name'].' '.$browser['version'];
                $device->OS = $os;
                $device->IP = Helper::getIp();
                $device->TymeZone = $timezone;
                $device->ScreenSize = $resolution;
                $device->MOB_Hash = sha1($str);
                $device->Trusted = 1;
                $device->Logged = 1;
                $device->Last_Logged = time();
                $device->save();
    }


    public static function getSmartDevInstance($device_cookie,$user_id,$timezone,$resolution) {

        $device = self::getDevBasedOnCookie($device_cookie,$user_id);
        if (!$device){
            $device = self::getDevBasedOnBrowser($user_id,$timezone,$resolution);
        }

        return $device;

    }

    public static function getSuperDevInstance($device_cookie,$user_id,$timezone,$resolution) {

        $device = self::getSuperDevBasedOnCookie($device_cookie);
        if (!$device){
            $device = self::getSuperDevBasedOnBrowser($user_id,$timezone,$resolution);
        }

        return $device;

    }

    public static function getDevBasedOnCookie($device_cookie,$user_id) {

        $device = UsersDevices::model()->findByAttributes(array(
            'MOB_Hash' =>$device_cookie,
            'Trusted'=>1,
            'User_ID'=>$user_id
        ));

        return $device;

    }

    public static function getSuperDevBasedOnCookie($device_cookie) {

        $device = UsersDevices::model()->findByAttributes(array(
            'MOB_Hash' =>$device_cookie,
            'Super_Login'=>1
        ));

        return $device;

    }


    public static function getDevHash($resolution='') {
        $browser = Helper::getBrowser();
        $os = Helper::getOs();
        $is_mobile = 0;
        if (Helper::getMobileDetect()->isMobile() || Helper::getMobileDetect()->isTablet() || Helper::checkMobile()) $is_mobile = 1;
        $str = $is_mobile.' '.$os.' '.$browser['name'].' '.$browser['version'];
        return sha1($str);
    }

    public static function getDevBasedOnBrowser($user_id,$timezone,$resolution) {

        $hash = self::getDevHash($resolution);

        $device = UsersDevices::model()->findByAttributes(array(
            'User_ID'=>$user_id,
            'MOB_Hash' =>$hash,
            'Trusted'=>1
        ));

        if ($device) {
            return $device;
        } else {
            return false;
        }
    }

    public static function getSuperDevBasedOnBrowser($user_id,$timezone,$resolution) {

        $hash = self::getDevHash($resolution);

        $device = UsersDevices::model()->findByAttributes(array(
            'User_ID'=>$user_id,
            'MOB_Hash' =>$hash,
            'Super_Login'=>1
        ));

        if ($device) {
            return $device;
        } else {
            return false;
        }
    }

    public static function getUsersDeviceList($user_id) {
        $devices = UsersDevices::model()->findAllByAttributes(array(
            'User_ID'=>$user_id,
        ));
        $result = array();
        foreach ($devices as $device) {
            $result []  = array(
                //'Lastlogin'=>date('Y-m-d h:i:s',$device->Last_Logged-$device->TymeZone),
                'Lastlogin'=>Helper::convertDateFromIntClient($device->Last_Logged),
                'LastloginServer'=>Helper::convertDateFromIntServer($device->Last_Logged),
                'IP'=>$device->IP,
                'DeviceType'=>$device->OS.' '.$device->Browser,
                'Device_ID'=>$device->Device_ID,
                'Mobile'=>$device->Mobile,
                'Logged'=>$device->Logged,
                'Hash'=>$device->MOB_Hash
            );
        }

        return $result;
    }



}
