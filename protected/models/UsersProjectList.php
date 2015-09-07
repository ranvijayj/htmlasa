<?php

/**
 * This is the model class for table "users_project_list".
 *
 * The followings are the available columns in table 'users_project_list':
 * @property integer $User_ID
 * @property integer $Project_ID
 * @property integer $Client_ID
 */
class UsersProjectList extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'users_project_list';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('User_ID, Project_ID, Client_ID', 'required'),
			array('User_ID, Project_ID, Client_ID', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('User_ID, Project_ID, Client_ID', 'safe', 'on'=>'search'),
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
            'project' => array(self::BELONGS_TO, 'Projects', 'Project_ID'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'User_ID' => 'User',
			'Project_ID' => 'Project',
			'Client_ID' => 'Client',
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
	public static function getProjectsByClientID($client_id) {
        $sql='select distinct(users_project_list.Project_ID) as Project_ID,projects.Project_Name from users_project_list
              left join projects on (users_project_list.Project_ID = projects.Project_ID)
              where users_project_list.Client_ID ='.$client_id;

        //var_dump($sql);die;
        $list= Yii::app()->db->createCommand($sql)->queryAll();
        return $list;

    }


    public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('User_ID',$this->User_ID);
		$criteria->compare('Project_ID',$this->Project_ID);
		$criteria->compare('Client_ID',$this->Client_ID);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return UsersProjectList the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
