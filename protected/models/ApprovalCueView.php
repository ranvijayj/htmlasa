<?php

/**
 * This is the model class for table "approval_cue_view".
 *
 * The followings are the available columns in table 'approval_cue_view':
 * @property string $DocType
 * @property integer $ID
 * @property string $InvDate
 * @property string $DueDate
 * @property integer $DocID
 * @property string $DocCreated
 * @property string $Approval_Value
 * @property integer $Approved
 * @property string $PreviousAprValue
 * @property string $Amount
 * @property integer $VendorID
 * @property integer $OwnerID
 * @property string $OwnerFirst_Name
 * @property string $OwnerLast_Name
 * @property string $CompanyName
 * @property string $CompanyFed
 * @property string $Address1
 * @property string $Address2
 * @property string $City
 * @property string $State
 * @property string $ZIP
 * @property string $Country
 * @property integer $Client_ID
 * @property string $Project_Name
 * @property string $AdminCompanyName
 * @property string $AdminCompanyFed
 * @property string $AdminAddress1
 * @property string $AdminAddress2
 * @property string $AdminCity
 * @property string $AdminState
 * @property string $AdminZip
 * @property string $AdminCountry
 * @property integer $NextApproverUID
 * @property string $ApprName
 * @property string $ApprEmail
 * @property integer $User_Approval_Value
 */
class ApprovalCueView extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'approval_cue_view';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('ID, DocID, Approved, VendorID, OwnerID, Client_ID, NextApproverUID, User_Approval_Value', 'numerical', 'integerOnly'=>true),
			array('DocType', 'length', 'max'=>2),
			array('DueDate', 'length', 'max'=>10),
			array('Approval_Value, PreviousAprValue, Amount', 'length', 'max'=>13),
			array('OwnerFirst_Name, OwnerLast_Name, Address1, Address2, City, Country, AdminAddress1, AdminAddress2, AdminCity, AdminCountry', 'length', 'max'=>45),
			array('CompanyName, AdminCompanyName, ApprEmail', 'length', 'max'=>80),
			array('CompanyFed, AdminCompanyFed', 'length', 'max'=>11),
			array('State, AdminState', 'length', 'max'=>4),
			array('ZIP, AdminZip', 'length', 'max'=>15),
			array('Project_Name', 'length', 'max'=>25),
			array('ApprName', 'length', 'max'=>91),
			array('InvDate, DocCreated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('DocType, ID, InvDate, DueDate, DocID, DocCreated, Approval_Value, Approved, PreviousAprValue, Amount, VendorID, OwnerID, OwnerFirst_Name, OwnerLast_Name, CompanyName, CompanyFed, Address1, Address2, City, State, ZIP, Country, Client_ID, Project_Name, AdminCompanyName, AdminCompanyFed, AdminAddress1, AdminAddress2, AdminCity, AdminState, AdminZip, AdminCountry, NextApproverUID, ApprName, ApprEmail, User_Approval_Value', 'safe', 'on'=>'search'),
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
			'DocType' => 'Doc Type',
			'ID' => 'ID',
			'InvDate' => 'Inv Date',
			'DueDate' => 'Due Date',
			'DocID' => 'Doc',
			'DocCreated' => 'Doc Created',
			'Approval_Value' => 'Approval Value',
			'Approved' => 'Approved',
			'PreviousAprValue' => 'Previous Apr Value',
			'Amount' => 'Amount',
			'VendorID' => 'Vendor',
			'OwnerID' => 'Owner',
			'OwnerFirst_Name' => 'Owner First Name',
			'OwnerLast_Name' => 'Owner Last Name',
			'CompanyName' => 'Company Name',
			'CompanyFed' => 'Company Fed',
			'Address1' => 'Address1',
			'Address2' => 'Address2',
			'City' => 'City',
			'State' => 'State',
			'ZIP' => 'Zip',
			'Country' => 'Country',
			'Client_ID' => 'Client',
			'Project_Name' => 'Project Name',
			'AdminCompanyName' => 'Admin Company Name',
			'AdminCompanyFed' => 'Admin Company Fed',
			'AdminAddress1' => 'Admin Address1',
			'AdminAddress2' => 'Admin Address2',
			'AdminCity' => 'Admin City',
			'AdminState' => 'Admin State',
			'AdminZip' => 'Admin Zip',
			'AdminCountry' => 'Admin Country',
			'NextApproverUID' => 'Next Approver Uid',
			'ApprName' => 'Appr Name',
			'ApprEmail' => 'Appr Email',
			'User_Approval_Value' => 'User Approval Value',
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

		$criteria->compare('DocType',$this->DocType,true);
		$criteria->compare('ID',$this->ID);
		$criteria->compare('InvDate',$this->InvDate,true);
		$criteria->compare('DueDate',$this->DueDate,true);
		$criteria->compare('DocID',$this->DocID);
		$criteria->compare('DocCreated',$this->DocCreated,true);
		$criteria->compare('Approval_Value',$this->Approval_Value,true);
		$criteria->compare('Approved',$this->Approved);
		$criteria->compare('PreviousAprValue',$this->PreviousAprValue,true);
		$criteria->compare('Amount',$this->Amount,true);
		$criteria->compare('VendorID',$this->VendorID);
		$criteria->compare('OwnerID',$this->OwnerID);
		$criteria->compare('OwnerFirst_Name',$this->OwnerFirst_Name,true);
		$criteria->compare('OwnerLast_Name',$this->OwnerLast_Name,true);
		$criteria->compare('CompanyName',$this->CompanyName,true);
		$criteria->compare('CompanyFed',$this->CompanyFed,true);
		$criteria->compare('Address1',$this->Address1,true);
		$criteria->compare('Address2',$this->Address2,true);
		$criteria->compare('City',$this->City,true);
		$criteria->compare('State',$this->State,true);
		$criteria->compare('ZIP',$this->ZIP,true);
		$criteria->compare('Country',$this->Country,true);
		$criteria->compare('Client_ID',$this->Client_ID);
		$criteria->compare('Project_Name',$this->Project_Name,true);
		$criteria->compare('AdminCompanyName',$this->AdminCompanyName,true);
		$criteria->compare('AdminCompanyFed',$this->AdminCompanyFed,true);
		$criteria->compare('AdminAddress1',$this->AdminAddress1,true);
		$criteria->compare('AdminAddress2',$this->AdminAddress2,true);
		$criteria->compare('AdminCity',$this->AdminCity,true);
		$criteria->compare('AdminState',$this->AdminState,true);
		$criteria->compare('AdminZip',$this->AdminZip,true);
		$criteria->compare('AdminCountry',$this->AdminCountry,true);
		$criteria->compare('NextApproverUID',$this->NextApproverUID);
		$criteria->compare('ApprName',$this->ApprName,true);
		$criteria->compare('ApprEmail',$this->ApprEmail,true);
		$criteria->compare('User_Approval_Value',$this->User_Approval_Value);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ApprovalCueView the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public static function getCueListByQueryString($queryString, $options,$sortOptions , $limit = 50)
    {
        $condition = new CDbCriteria();
        if (count($options) > 0 && trim($queryString) != '') {

            $countCond = 0;

            if ($options['search_option_id']) {
                $condition->compare('ID', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_com_name']) {
                $condition->compare('CompanyName', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_addr1']) {
                $condition->compare('Address1', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_addr2']) {
                $condition->compare('Address2', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_city']) {
                $condition->compare('City', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_state']) {
                $condition->compare('State', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_zip']) {
                $condition->compare('ZIP', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_country']) {
                $condition->compare('Country', $queryString, true, 'OR');
                $countCond++;
            }

            if (Yii::app()->user->projectID != 'all') {
                $condition->addCondition("t.DocProject_ID = '" . Yii::app()->user->projectID . "'");
            }

            $condition->addCondition("t.DocClient_ID = '" .Yii::app()->user->clientID . "'");

            $condition->order = $sortOptions['sort_by'] . " " . $sortOptions['sort_direction'];

            $cueApprList= ApprovalCueView::model()->findAll($condition);

        }  else  {

            $condition->order = $sortOptions['sort_by'] . " " . $sortOptions['sort_direction'];
            //$condition->order = "t.ApprName,t.DocCreated";

            if (Yii::app()->user->projectID != 'all') {
                $condition->addCondition("t.DocProject_ID = '" . Yii::app()->user->projectID . "'");
            //    $condition->addCondition("t.AdminCompanyName = '" . $company . "'");
            }

            $condition->addCondition("t.DocClient_ID = '" .Yii::app()->user->clientID . "'");

            $cueApprList= ApprovalCueView::model()->findAll($condition);
        }
        return $cueApprList;

    }


    public static function getUsersToNotifyForPendingApproval($query)
    {
        $condition=new CDbCriteria;
        $condition->select='ApprName,NextApproverUID';
        $condition->distinct=true;

        if($query) {
            $condition->addInCondition('DocID',$query['doc_ids'], 'AND');
            $condition->addInCondition('NextApproverUID',$query['control_ids'], 'AND');
            $ids=$query['doc_ids'];
            $cids=$query['control_ids'];
        }

        $rows = ApprovalCueView::model()->findAll($condition);

        $temp_arr = array();


        foreach ($rows as $row) {

            for($i=0;$i<count($cids);$i++){
                if($cids[$i]==$row->NextApproverUID){
                    $temp_arr[] = $ids[$i];
                }
            }

            $result=$result."<b>".$row->ApprName."</b> has items in the approval cycle:";
            $result=$result. "<div style='padding-left: 40px'>";

            $cond=new CDbCriteria;
            $cond->select='CompanyName,Project_Name';
            $cond->compare('NextApproverUID', $row->NextApproverUID);
            $cond->addInCondition('DocID',$temp_arr, 'AND');
            $cond->distinct=true;
            $cond->order = "CompanyName ASC";
            $companies = ApprovalCueView::model()->findAll($cond);

            foreach ($companies as $company) {
                    if(!is_null($company->CompanyName)) {
                        $result=$result.$company->CompanyName." for project  '".$company->Project_Name."'<br/>";
                    } else {$result=$result."Vendor not attached for project  '".$company->Project_Name."'<br/>";}
                }


            unset($temp_arr);

            unset($companies);
            $result=$result."</div>";

        }

        return $result;
    }


    public static function filterArray($model) {
        $res = array();
        $doc_id = 0;
        foreach ($model as $item) {
            //next approver value


        if ($doc_id != $item->DocID ) {

            $doc_id != $item->DocID;



            $sql = 'select min(ucl.User_Approval_Value) as min
                  from  users_client_list ucl
                  inner join users_project_list upl on (upl.User_ID = ucl.User_ID)
                  where ucl.Client_ID = '.$item->Client_ID.
                ' and upl.Project_ID = '.$item->DocProject_ID.
                ' and ucl.User_Approval_Value >'.$item->Approval_Value;


            $min_value = Yii::app()->db->createCommand($sql)->queryAll();
            $min_value = $min_value[0]['min'];
        }

        if ($item->User_Approval_Value == $min_value ) {
                $res[] = $item;
        }

        }
        return $res;
    }

    public static function getNextApprover($client_id,$project_id,$apr_value){
        $sql = 'select ucl.User_ID,ucl.User_Approval_Value
                  from  users_client_list ucl
                  inner join users_project_list upl on (upl.User_ID = ucl.User_ID)
                  where ucl.Client_ID = '.$client_id.
            ' and upl.Project_ID = '.$project_id.
            ' and ucl.User_Approval_Value >'.$apr_value.
            ' ';


        $min_value = Yii::app()->db->createCommand($sql)->queryAll();
        return $min_value;
    }
}
