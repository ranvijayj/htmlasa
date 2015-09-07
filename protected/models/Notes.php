<?php

/**
 * This is the model class for table "notes".
 *
 * The followings are the available columns in table 'notes':
 * @property integer $Note_ID
 * @property integer $Document_ID
 * @property integer $User_ID
 * @property integer $Company_ID
 * @property integer $Client_ID
 * @property string $Comment
 * @property string $Created
 */
class Notes extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'notes';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Document_ID, User_ID, Company_ID, Client_ID, Created', 'required'),
			array('Document_ID, User_ID, Company_ID, Client_ID', 'numerical', 'integerOnly'=>true),
			array('Comment', 'length', 'max'=>250),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Note_ID, Document_ID, User_ID, Company_ID, Client_ID, Comment, Created', 'safe', 'on'=>'search'),
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
            'user'=>array(self::BELONGS_TO, 'Users', 'User_ID', 'with' => 'person'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'Note_ID' => 'Database generated unique Note number',
			'Document_ID' => 'Document ID from Documents table',
			'User_ID' => 'Uaer ID from Users table',
			'Company_ID' => 'Company',
			'Client_ID' => 'Client',
			'Comment' => 'Comment or question',
			'Created' => 'When the note was created',
		);
	}

    /**
     * Gets company notes for all in a client group to review
     */
    public function getCompanyClientsNotes($comId)
    {
        $comId = intval($comId);

        $condition = new CDbCriteria();
        $condition->condition = "Client_ID='" . Yii::app()->user->clientID . "'";
        $condition->addCondition("Document_ID = '0'");
        $condition->addCondition("Company_ID='" . $comId . "'");
        $condition->order = "Created DESC";

        $notes = $this->with('user')->findAll($condition);

        return $notes;
    }


    /**
     * Get documents notes
     * @param $documentId
     * @return array|CActiveRecord|CActiveRecord[]|mixed|null
     */
    public function getDocumentNotes($documentId)
    {
        $documentId = intval($documentId);

        $condition = new CDbCriteria();
        $condition->condition = "Client_ID='" . Yii::app()->user->clientID . "'";
        $condition->addCondition("Document_ID = '" . $documentId . "'");
        $condition->addCondition("Company_ID='0'");
        $condition->order = "Created DESC";

        $notes = $this->with('user')->findAll($condition);

        return $notes;
    }


    public static  function getClientsNotes($client_id,$projects_array)
    {
        $sql='select count(notes.Note_ID) as CountNotes, sum(char_length(notes.Comment)) as CommentsLength
        from notes
        left join documents on (documents.Document_ID = notes.Document_ID)
        where notes.Client_ID ='.$client_id.' and
        documents.Project_ID in ('.implode(",",$projects_array).')
         ';
        //var_dump($sql);die;
        $noteslist = Yii::app()->db->createCommand($sql)->queryAll();

        return $noteslist;
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

		$criteria->compare('Note_ID',$this->Note_ID);
		$criteria->compare('AP_ID',$this->AP_ID);
		$criteria->compare('User_ID',$this->User_ID);
		$criteria->compare('Company_ID',$this->Company_ID);
		$criteria->compare('Client_ID',$this->Client_ID);
		$criteria->compare('Comment',$this->Comment,true);
		$criteria->compare('Created',$this->Created,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Notes the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
