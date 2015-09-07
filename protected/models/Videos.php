<?php

/**
 * This is the model class for table "Videos".
 *
 * The followings are the available columns in table 'Videos':
 * @property integer $Video_ID
 * @property string $Video_Title
 * @property string $Video_Log_Line
 * @property string $Video_Desc
 * @property string $Link_Title
 * @property string $Video_URL
 * @property string $Video_Password
 * @property integer $Sort_Order
 * @property integer $Visibility
 * @property integer $Clients_Client_ID
 * @property integer $Project_ID
 */
class Videos extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'Videos';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Video_Title, Video_Log_Line, Video_Desc, Link_Title, Video_URL, Video_Password, Sort_Order, Visibility', 'required'),
			array('Sort_Order, Visibility, Clients_Client_ID, Project_ID', 'numerical', 'integerOnly'=>true),
			array('Video_Title, Video_Log_Line', 'length', 'max'=>30),
			array('Video_Desc', 'length', 'max'=>500),
			array('Link_Title', 'length', 'max'=>20),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Video_ID, Video_Title, Video_Log_Line, Video_Desc, Link_Title, Video_URL, Video_Password, Sort_Order, Visibility, Clients_Client_ID, Project_ID', 'safe', 'on'=>'search'),
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
			'Video_ID' => 'Video',
			'Video_Title' => 'Video Title',
			'Video_Log_Line' => 'Video Log Line',
			'Video_Desc' => 'Video Desc',
			'Link_Title' => 'Link Title',
			'Video_URL' => 'Video Url',
			'Video_Password' => 'Video Password',
			'Sort_Order' => 'Sort Order',
			'Visibility' => 'Visibility',
			'Clients_Client_ID' => 'Clients Client',
			'Project_ID' => 'Project',
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

		$criteria->compare('Video_ID',$this->Video_ID);
		$criteria->compare('Video_Title',$this->Video_Title,true);
		$criteria->compare('Video_Log_Line',$this->Video_Log_Line,true);
		$criteria->compare('Video_Desc',$this->Video_Desc,true);
		$criteria->compare('Link_Title',$this->Link_Title,true);
		$criteria->compare('Video_URL',$this->Video_URL,true);
		$criteria->compare('Video_Password',$this->Video_Password,true);
		$criteria->compare('Sort_Order',$this->Sort_Order);
		$criteria->compare('Visibility',$this->Visibility);
		$criteria->compare('Clients_Client_ID',$this->Clients_Client_ID);
		$criteria->compare('Project_ID',$this->Project_ID);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Videos the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public static function getVideoListByQueryString($queryString,$options){

        $visibility_array = Videos::getRightsForVideo();
        $condition = new CDbCriteria();

        if (count($options) > 0 && trim($queryString) != '') {
            $countCond = 0;

            if ($options['search_option_title']) {
                $condition->compare('Video_Title',$queryString,true,'OR');

            }
            if ($options['search_option_log_line']) {
                $condition->compare('Video_Log_Line',$queryString,true,'OR');
            }
            if ($options['search_option_description']) {
                $condition->compare('Video_Desc',$queryString,true,'OR');
            }
            if ($options['search_option_link_name']) {
                $condition->compare('Link_Title',$queryString,true,'OR');
            }

        }

        $condition->addInCondition('Visibility', $visibility_array);

        $videos = Videos::model()->findAll($condition);

        return $videos;

    }

    public static function getRightsForVideo(){
        //$users = array('admin', 'data_entry_clerk', 'approver', 'processor', 'db_admin', 'client_admin');
        if (Yii::app()->user->id == 'data_entry_clerk') {
            $result_arr = array(1,3);
        } else {
            $result_arr = array(1,2,4,5);
        }
        return $result_arr;
    }
}
