<?php

/**
 * This is the model class for table "audits".
 *
 * The followings are the available columns in table 'audits':
 * @property integer $aid
 * @property integer $Document_ID
 * @property string $Event_Type
 * @property integer $Event_User_ID
 * @property integer $User_Appr_Value
 * @property string $Event_Date
 */
class Audits extends CActiveRecord
{
    const ACTION_CREATION = 'Created';
    const ACTION_UPLOAD = 'Uploaded';
    const ACTION_REUPLOAD = 'Re-uploaded';

    const ACTION_AUTOSAVE = 'DataEntry autosaved';

    const ACTION_SAVE = 'DataEntry complete';
    const ACTION_DE_SAVE = 'DE from detail';

    const ACTION_APPROVAL = 'Approved';
    const ACTION_REVERT = 'Returned';
    const ACTION_PDF = 'PDF generated';
    const ACTION_REPDF = 'PDF regenerated';
    const ACTION_LIBRARY = 'Added to library';

    const ACTION_NOTE = 'Note added';

    const ACTION_ROTATE = 'File Rotated';







    /**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'audits';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Document_ID, Event_Type, Event_User_ID, User_Appr_Value, Event_Date', 'required'),
			array('Document_ID, Event_User_ID, User_Appr_Value', 'numerical', 'integerOnly'=>true),
			array('Event_Type', 'length', 'max'=>50),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Audits_ID, Document_ID, Event_Type, Event_User_ID, User_Appr_Value, Event_Date', 'safe', 'on'=>'search'),
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
			'aid' => 'Aid',
			'Document_ID' => 'Document',
			'Event_Type' => 'Event Type',
			'Event_User_ID' => 'Event User',
			'User_Appr_Value' => 'User Appr Value',
			'Event_Date' => 'Event Date',
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

		$criteria->compare('aid',$this->aid);
		$criteria->compare('Document_ID',$this->Document_ID);
		$criteria->compare('Event_Type',$this->Event_Type,true);
		$criteria->compare('Event_User_ID',$this->Event_User_ID);
		$criteria->compare('User_Appr_Value',$this->User_Appr_Value);
		$criteria->compare('Event_Date',$this->Event_Date,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Audits the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public static  function LogAction ($doc_id,$event_type) {

        $ucl =UsersClientList::model()->findByAttributes(array(
            'User_ID' => Yii::app()->user->userID,
            'Client_ID' => Yii::app()->user->clientID,
        ));

        $audit = new Audits();
        $audit->Document_ID = $doc_id;
        $audit->Event_Type = $event_type;
        $audit->Event_User_ID = Yii::app()->user->userID;
        $audit->User_Appr_Value = $ucl->User_Approval_Value;
        $audit->Event_Date = date("Y-m-d H:i:s");
        $audit->save();


        //list of actions that cause update of FileCache

        $events_for_cashe_update = array(self::ACTION_PDF,self::ACTION_REPDF,self::ACTION_REVERT,self::ACTION_REUPLOAD,self::ACTION_ROTATE);

        if (in_array($event_type,$events_for_cashe_update)) {
            FileCache::updateFileInCache($doc_id);
        }


    }


    public static function getApprovalDetailList($doc_id){

        $audits = Audits::model()->findAllByAttributes(
            array('Document_ID'=>$doc_id,
                'Event_Type'=>array(Audits::ACTION_APPROVAL)
            )
        );

        $result_array = array();

        foreach ($audits as $item) {

                $user = Users::model()->findByPk($item->Event_User_ID);
                $result_array[]= array(
                    'name' => $user->person->First_Name.' '.$user->person->Last_Name,
                    'formatted_name' => $user->person->Last_Name.', '.$user->person->First_Name,
                    'date' => Helper::convertDate($item->Event_Date),
                    'value' => $item->User_Appr_Value
                );
        }

        return $result_array;
    }

    public static function getApprovalDetailList2($doc_id){

        $condition = new CDbCriteria();


        $condition->condition = "User_Appr_Value>=2 ";
        $condition->addCondition('Document_ID='.$doc_id.'', 'AND');
        $condition->addCondition('Event_Type="'.Audits::ACTION_APPROVAL.'"', 'AND');
        $audits = Audits::model()->findAll($condition);

        $result_array = array();

        foreach ($audits as $item) {

            $user = Users::model()->findByPk($item->Event_User_ID);
            $result_array[]= array(
                'name' => $user->person->First_Name.' '.$user->person->Last_Name,
                'formatted_name' => $user->person->Last_Name.', '.$user->person->First_Name,
                'date' => Helper::convertDate($item->Event_Date),
                'value' => $item->User_Appr_Value
            );
        }

        return $result_array;
    }

    /**public static function printOldStyle() {
        $doc_id = intval($_GET['doc']);
        $audit_mode = strval($_GET['action']);

        if ($audit_mode != '') {
            $audits = Audits::model()->findAllByAttributes(
                array('Document_ID'=>$doc_id,
                    'Event_Type'=>$audit_mode
                )
            );
        } else {
            $audits = Audits::model()->findAllByAttributes(
                array('Document_ID'=>$doc_id,
                )
            );
        }
        $document = Documents::model()->findByPk($doc_id);


        $doc_name = Images::model()->findByAttributes(array(
            'Document_ID'=>$doc_id
        ))->File_Name;

        $this->renderPartial('application.views.documents.print_audit', array(
            'audits' => $audits,
            'doc_name'=>$doc_name,
            'document'=>$document,
            'doc_id'=>$doc_id,
        ));

    }*/

    public static function prepareAuditForPrint($doc_id) {

        $audits = Audits::model()->findAllByAttributes(
            array('Document_ID'=>$doc_id,
            )
        );


        $pdf = new FpdfForAudit('P','mm','Letter');
        $pdf->AddFont('ARIALN','','ARIALN.php');
        $pdf->SetMargins(12.7,12.7);
        $pdf->SetRightMargin(12.7);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->setVariabled($doc_id,$audits);
        $pdf->AliasNbPages();
        $pdf->AddPage('P');
        $pdf->PrintContent();
        //for($i=1;$i<=40;$i++) $pdf->Cell(0,10,'Printing line number '.$i,0,1);

        $path=Helper::createDirectory('batches');// creates directory "protected/data/batches" if not exists
        chdir($path);
        $fileName ="Audit". date("Y_m_d_H_i_s") . '.pdf';
        $pdf->Output($fileName, 'I');
    }
}
