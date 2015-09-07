<?php

/**
 * This is the model class for table "client_service_settings_delayed".
 *
 * The followings are the available columns in table 'client_service_settings_delayed':
 * @property integer $Client_ID
 * @property string $Service_Level_ID
 * @property integer $Additional_Users
 * @property integer $Additional_Projects
 * @property integer $Additional_Storage
 * @property string $Fee
 * @property string $Active_To
 */
class DelayedClientServiceSettings extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'client_service_settings_delayed';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Client_ID, Service_Level_ID, Active_To', 'required'),
			array('Client_ID, Additional_Users, Additional_Projects, Additional_Storage', 'numerical', 'integerOnly'=>true),
			array('Service_Level_ID', 'length', 'max'=>11),
			array('Fee', 'length', 'max'=>13),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Client_ID, Service_Level_ID, Additional_Users, Additional_Projects, Additional_Storage, Fee, Active_To, Active_From', 'safe', 'on'=>'search'),
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
		$criteria->compare('Service_Level_ID',$this->Service_Level_ID,true);
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
	 * @return DelayedClientServiceSettings the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public static function checkDate($date,$client_id){

        $dcss = self::model()->findByPk($client_id);
        if ($dcss) {
            if (strtotime($date) >= strtotime($dcss->Active_From)) {
                $currentSettings = ClientServiceSettings::model()->findByPk($client_id);
                $currentSettings->Service_Level_ID = $dcss->Service_Level_ID;
                $currentSettings->Additional_Users = $dcss->Additional_Users;
                $currentSettings->Additional_Projects = $dcss->Additional_Projects;
                $currentSettings->Additional_Storage = $dcss->Additional_Storage;
                $currentSettings->Active_To = $dcss->Active_To;
                $currentSettings->Fee = $dcss->Fee;
                $currentSettings->save();

                $dcss->delete();
            }
        }

    }

    public static function createDelayedFromPending($pendingSettings,$client_id,$base_users,$base_projects,$base_storage) {
        $dcss = DelayedClientServiceSettings::model()->findByPk($client_id);
        if (!$dcss) {
            $dcss = new DelayedClientServiceSettings();
            $dcss->Client_ID = $client_id;
        }

        $dcss->Service_Level_ID = $pendingSettings->Service_Level_ID;
        $dcss->Additional_Users = $pendingSettings->Additional_Users-$base_users;
        $dcss->Additional_Projects = $pendingSettings->Additional_Projects-$base_projects;
        $dcss->Additional_Storage = $pendingSettings->Additional_Storage-$base_storage;
        $dcss->Active_To = $pendingSettings->Pending_Active_To;
        $dcss->Active_From = $pendingSettings->Pending_Active_From;
        $dcss->Fee = $pendingSettings->Fee;

        $dcss->save();
        $pendingSettings->delete();
    }

    public static function createDelayedFromData($client_id,$service_level,$additional_users,$additional_projects,$additional_storage,$active_to,$active_from,$fee) {
        $dcss = DelayedClientServiceSettings::model()->findByPk($client_id);
        if (!$dcss) {
            $dcss = new DelayedClientServiceSettings();
            $dcss->Client_ID = $client_id;
        }
        $dcss->Service_Level_ID = $service_level;
        $dcss->Additional_Users =$additional_users;
        $dcss->Additional_Projects = $additional_projects;
        $dcss->Additional_Storage = $additional_storage;
        $dcss->Active_To = date('Y-m-d',strtotime($active_to));
        $dcss->Active_From = date('Y-m-d',strtotime($active_from));
        $dcss->Fee = $fee;

        $dcss->save();
    }

}
