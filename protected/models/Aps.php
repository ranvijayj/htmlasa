<?php

/**
 * This is the model class for table "aps".
 *
 * The followings are the available columns in table 'aps':
 * @property integer $AP_ID
 * @property integer $Document_ID
 * @property integer $Vendor_ID
 * @property string $AP_Approval_Value
 * @property integer $Previous_AP_A_Val
 * @property string $Invoice_Amount
 * @property string $Invoice_Number
 * @property string $Invoice_Date
 * @property string $Invoice_Reference
 * @property string $Invoice_Due_Date
 * @property integer $Detail_1099
 * @property string $Detail_1099_Box_Number
 * @property string $Export_Batch_ID
 * @property integer $Approved
 * @property integer $AP_Backup_Document_ID
 */
class Aps extends CActiveRecord
{
    const NEED_1099 = 1;
    const DO_NOT_NEED_1099 = 0;

    const NEED_1099_DEFAULT_NUMBER = 7;
    const DO_NOT_NEED_1099_DEFAULT_NUMBER = 0;

    const APPROVED = 100;
    const READY_FOR_APPROVAL = 1;
    const NOT_READY_FOR_APPROVAL = 0;

    const DISPLAY_LIMIT=50;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'aps';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Document_ID, Vendor_ID', 'required'),
			array('Document_ID, Vendor_ID, Detail_1099, Approved, AP_Backup_Document_ID, PO_ID, Export_Batch_ID', 'numerical', 'integerOnly'=>true),
			array('AP_Approval_Value, Invoice_Amount, Previous_AP_A_Val', 'length', 'max'=>13),
			array('Invoice_Number', 'length', 'max'=>45),
			array('Invoice_Reference', 'length', 'max'=>500),
			array('Detail_1099_Box_Number', 'length', 'max'=>2),
			array('Invoice_Date, Invoice_Due_Date', 'date', 'format' => 'yyyy-MM-dd'),
            array('AP_ID', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('AP_ID, Document_ID, Vendor_ID, AP_Approval_Value, Previous_AP_A_Val, Invoice_Amount, Invoice_Number, Invoice_Date, Invoice_Reference, Invoice_Due_Date, Detail_1099, Detail_1099_Box_Number, Export_Batch_ID, Approved, AP_Backup_Document_ID, PO_ID', 'safe', 'on'=>'search'),
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
            'vendor' => array(self::BELONGS_TO, 'Vendors', 'Vendor_ID'),
            'document' => array(self::BELONGS_TO, 'Documents', 'Document_ID'),
            'payments' => array(self::MANY_MANY, 'Payments', 'ap_payments(AP_ID, Payment_ID)'),
            'dists' => array(self::HAS_MANY, 'GlDistDetails', 'AP_ID'),
            'ck_req_detail' => array(self::HAS_ONE, 'CkReqDetails', 'AP_ID'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'AP_ID' => 'Database generated unique AP number',
			'Document_ID' => 'Document ID from Documents table',
			'Vendor_ID' => 'Vendor',
            'PO_ID' => 'PO ID',
			'AP_Approval_Value' => 'Ap Approval Value',
			'Previous_AP_A_Val' => 'Previous Ap A Val',
			'Invoice_Amount' => 'Invoice Amount',
			'Invoice_Number' => 'Invoice Number',
			'Invoice_Date' => 'Invoice Date',
			'Invoice_Reference' => 'Description',
			'Invoice_Due_Date' => 'Invoice Due Date',
			'Detail_1099' => 'Detail 1099',
			'Detail_1099_Box_Number' => 'Detail 1099 Box Number',
			'Export_Batch_ID' => 'Export batch ID',
			'Approved' => 'Approved stamp',
            'AP_Backup_Document_ID' => 'AP Backup Document ID'
		);
	}

    /**
     * On save event
     * @return bool
     */
    protected function beforeSave() {
        if (isset($this->AP_Approval_Value) && ($this->AP_Approval_Value === '')) {
            $this->AP_Approval_Value = null;
        }

        if (isset($this->Previous_AP_A_Val) && ($this->Previous_AP_A_Val == '' || $this->Previous_AP_A_Val == self::NOT_READY_FOR_APPROVAL)) {
            $this->Previous_AP_A_Val = null;
        }

        if (isset($this->Invoice_Amount) && ($this->Invoice_Amount == '' || $this->Invoice_Amount == self::NOT_READY_FOR_APPROVAL)) {
            $this->Invoice_Amount = null;
        }
        return parent::beforeSave();
    }

    /**
     * Get staging AP items
     * @return CActiveRecord[]
     */
    public static function getStagingItems()
    {
        // get staging POs
        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN documents ON documents.Document_ID=t.Document_ID
                            LEFT OUTER JOIN images ON images.Document_ID=t.Document_ID";
        $condition->addCondition("documents.Client_ID= '" . Yii::app()->user->clientID . "'");
        $condition->addCondition("documents.Project_ID= '" . Yii::app()->user->projectID . "'");

        if (Yii::app()->user->userType == "User") {
            $condition->addCondition("documents.User_ID= '" . Yii::app()->user->userID . "'");
        }

        $condition->addCondition("t.AP_Approval_Value = '" . self::NOT_READY_FOR_APPROVAL . "'");
        $condition->addCondition("images.Image_ID IS NULL");
        $condition->order = "AP_ID DESC";

        $aps = Aps::model()->with('vendor.client.company')->findAll($condition);

        return $aps;
    }

    /**
     * Generate or regenerate PDF for AP
     * @param $apId
     * @param bool $approved
     * @deprecated since 26.05.2015
     *
     */
    public static function generatePdf($apId, $approved = false)
    {
        // get AP
        $ap = Aps::model()->with('dists', 'document', 'ck_req_detail')->findByPk($apId);

        $ckReqDet = $ap->ck_req_detail;

        // get PO dists
        $apDists = $ap->dists;

        // get PO formatting
        $poFormatting = PoFormatting::model()->findByAttributes(array(
            'Project_ID' => $ap->document->Project_ID,
        ));

        // get Sign_Requested_By user info
        $signRequestedByUser = Users::model()->with('person')->findByPk($ckReqDet->Sign_Requested_By);

        $aproval_detail_list = Audits::getApprovalDetailList($ap->Document_ID);
        // get current vendor info
        $currentVendor = Vendors::model()->with('client.company.adreses')->findByPk($ap->Vendor_ID);

        $condition = UsersClientList::getClientAdminCondition($currentVendor->client->Client_ID);
        $vendorAdmin = UsersClientList::model()->with('user.person')->find($condition);

        // get content for pdf
        $content = Yii::app()->controller->renderPartial('application.views.ap.ap_template', array(
            'ap' => $ap,
            'ckReqDet' => $ckReqDet,
            'poFormatting' => $poFormatting,
            'apDists' => $apDists,
            'currentVendor' => $currentVendor,
            'vendorAdmin' => $vendorAdmin,
            'signRequestedByUser' => $signRequestedByUser,

            'aproval_detail_list' => $aproval_detail_list,

            'approved' => $approved,
        ), true);

        $fileName = Helper::createDirectory('generated_po');
        $fileName = $fileName.'/' . Yii::app()->user->userID . '-' . date("Y_m_d_H_i_s") . '.pdf';
        Yii::import('ext.html2pdf.HTML2PDF');
        $html2pdf = new HTML2PDF('P', 'A4', 'en');
        $html2pdf->writeHTML($content);
        $html2pdf->Output($fileName, 'F');

        // insert or update image image
        $image = Images::model()->findByAttributes(array(
            'Document_ID' => $ap->Document_ID,
        ));

        if (!$image) {
            $image = new Images();
        }

        $imageData = addslashes(fread(fopen($fileName,"rb"),filesize($fileName)));
        $image->Document_ID = $ap->Document_ID;
        $image->Img = $imageData;
        $image->File_Name = Yii::app()->user->userID . '-' . date("Y_m_d_H_i_s") . '.pdf';
        $image->Mime_Type = 'application/pdf';
        $image->File_Hash = sha1_file($fileName);
        $image->File_Size = intval(filesize($fileName));
        $image->Pages_Count = FileModification::calculatePagesByPath($fileName);
        $image->save();

        if (file_exists($fileName)) {
            @unlink($fileName);
        }
    }




    /**
     * Get last APs to AP List page
     * @param int $limit
     * @param bool $toBeApproved
     * @return Aps[]
     */
    public static function getLastAps($limit, $toBeApproved = false)
    {
        $userApprovalRange = Aps::getUserApprovalRange();

        // get aps
        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN documents ON documents.Document_ID=t.Document_ID
                            LEFT OUTER JOIN images ON images.Document_ID=t.Document_ID";
        $condition->condition = "documents.Client_ID='" . Yii::app()->user->clientID . "'";

        if (is_numeric(Yii::app()->user->projectID)) {
            $condition->addCondition("documents.Project_ID= '" . Yii::app()->user->projectID . "'");
        }

        if (Yii::app()->user->id == 'user') {
            $condition->addCondition("documents.User_ID= '" . Yii::app()->user->userID . "'");
        }

        $condition->addCondition("images.Image_ID IS NOT NULL");

        if ($toBeApproved) {
            $condition->addCondition("t.AP_Approval_Value < '" . $userApprovalRange['user_appr_val'] . "'");
            $condition->addCondition("t.AP_Approval_Value >= '" . $userApprovalRange['prev_user_appr_val'] . "'");
            $condition->addCondition("t.Vendor_ID != '0'");
            $condition->addCondition("t.Invoice_Amount IS NOT NULL");
            $condition->addCondition("t.Invoice_Number != '0'");
            $condition->addCondition("t.Invoice_Date IS NOT NULL");
            //$condition->limit = $limit;
        } else {

           if($limit!=0) {
               //var_dump($limit);die;
               $condition->limit = $limit;
           }
        }

        $condition->order = "documents.Created DESC";

        $aps = Aps::model()->with('vendor')->findAll($condition);


        if (!$aps) {
            $aps = array();
        }

        return $aps;
    }

    /**
     * Get user approval range between user approval value
     * and previous user approval value
     * @return array
     */
    public static function getUserApprovalRange()
    {
        // get user approval value
        $userToClient = UsersClientList::model()->findByAttributes(array(
            'User_ID' => Yii::app()->user->userID,
            'Client_ID' => Yii::app()->user->clientID,
        ));

        //get pervios user approval value
        $condition = new CDbCriteria();
        $condition->select = 'User_Approval_Value';
        $condition->condition = "t.Client_ID='" . Yii::app()->user->clientID . "'";
        $condition->addCondition("t.User_Approval_Value < '" . $userToClient->User_Approval_Value . "'");
        $condition->order = "t.User_Approval_Value DESC";
        $perviosUserApproval = UsersClientList::model()->find($condition);

        if (isset($perviosUserApproval->User_Approval_Value) && $perviosUserApproval->User_Approval_Value >= self::READY_FOR_APPROVAL) {
            $LastApproverValue = $perviosUserApproval->User_Approval_Value;
        } else {
            $LastApproverValue = self::READY_FOR_APPROVAL;
        }

        return array(
            'user_appr_val' => $userToClient->User_Approval_Value,
            'prev_user_appr_val' => $LastApproverValue,
        );
    }

    /**
     * Get invoices to enter data
     * @return Aps[]
     */
    public static function findAPToEntry()
    {
        $queryString = $_SESSION['last_ap_to_entry_search']['query'];
        $options =  $_SESSION['last_ap_to_entry_search']['options'];

        $condition = new CDbCriteria();


        $condition->join = "LEFT JOIN documents ON documents.Document_ID=t.Document_ID
                            LEFT OUTER JOIN images ON images.Document_ID=t.Document_ID";
        $condition->condition = "t.Invoice_Number = '0'";
        $condition->addCondition("t.Vendor_ID='0'", 'OR');
        $condition->addCondition("t.AP_Approval_Value='0'", 'OR');

        $condition->addCondition("t.AP_Approval_Value!='100'", 'AND'); //this condition for Voided APs - allows hide them from DataEntry


        if (Yii::app()->user->userType == UsersClientList::PROCESSOR || Yii::app()->user->userType == UsersClientList::APPROVER
            || Yii::app()->user->userType == UsersClientList::CLIENT_ADMIN) {
            $condition->addCondition("documents.Client_ID='" . Yii::app()->user->clientID . "'");
        }

        if (Yii::app()->user->userType == UsersClientList::PROCESSOR || ((Yii::app()->user->userType == UsersClientList::APPROVER
            || Yii::app()->user->userType == UsersClientList::CLIENT_ADMIN) && is_numeric(Yii::app()->user->projectID))) {
            $condition->addCondition("documents.Project_ID='" . Yii::app()->user->projectID . "'");
        }
        if (Yii::app()->user->userType == UsersClientList::USER
                     && is_numeric(Yii::app()->user->projectID)) {
            $condition->addCondition("documents.Project_ID='" . Yii::app()->user->projectID . "'");
            $condition->addCondition("documents.User_ID='" . Yii::app()->user->userID . "'");
        }

        if (Yii::app()->user->userType == Users::DATA_ENTRY_CLERK) {
            //condition to allow DEC see only documents of clients that he has access
            $cli_array = Clients::getClientsIDList(Yii::app()->user->userID);
            $condition->addInCondition('documents.Client_ID', $cli_array);
        }


        $condition->addCondition("images.Image_ID IS NOT NULL");
        $condition->order = "documents.Created ASC";

        $countCond = 0;
        if (count($options) > 0 && trim($queryString) != '') {
            $search_condition = new CDbCriteria();
            $search_condition->join = 'LEFT JOIN vendors ON t.Vendor_ID = vendors.Vendor_ID ';
            $search_condition->join .= 'LEFT JOIN companies ON vendors.Vendor_Client_ID = companies.Company_ID ';

            if ($options['search_option_com_name']) {
                $search_condition->compare('companies.Company_Name', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_fed_id']) {
                $search_condition->compare('companies.Company_Fed_ID', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_invoice_num']) {
                $search_condition->compare('t.Invoice_Number', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_inv_date']) {
                $search_condition->compare('t.Invoice_Date', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_inv_due_date']) {
                $search_condition->compare('t.Invoice_Due_Date', $queryString, true, 'OR');
                $countCond++;
            }


            if ($options['search_option_amount']) {
                $search_condition->compare('t.Invoice_Amount', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_description']) {
                $search_condition->compare('t.Invoice_Reference', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_1099_type']) {
                $search_condition->compare('t.Detail_1099_Box_Number', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_po_number']) {
                $search_condition->compare('t.AP_Backup_Document_ID', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_4digits']) {
                $search_condition->compare('t.PO_Card_Last_4_Digits', $queryString, true, 'OR');
                $countCond++;
            }


        }

        if( $countCond > 0 ) $condition->mergeWith($search_condition);
        $aps = Aps::model()->findAll($condition);

        return $aps;
    }

    /**
     * Check user's access to AP staging item
     * @param $apId
     * @return bool
     */
    public static function hasStagingAPAccess($apId)
    {
        $has_access = false;

        $condition = new CDbCriteria();
        $condition->join = "LEFT OUTER JOIN documents ON documents.Document_ID=t.Document_ID
                            LEFT OUTER JOIN images ON images.Document_ID=t.Document_ID";
        $condition->addCondition("documents.Client_ID= '" . Yii::app()->user->clientID . "'");
        $condition->addCondition("documents.Project_ID= '" . Yii::app()->user->projectID . "'");

        if (Yii::app()->user->userType == "User") {
            $condition->addCondition("documents.User_ID= '" . Yii::app()->user->userID . "'");
        }

        $condition->addCondition("t.AP_Approval_Value = '" . self::NOT_READY_FOR_APPROVAL . "'");
        $condition->addCondition("t.AP_ID = '" . $apId . "'");
        $condition->addCondition("images.Image_ID IS NULL");
        $ap = Aps::model()->find($condition);

        if ($ap) {
            $has_access = true;
        }

        return $has_access;
    }

    /**
     * Check user's access to AP item
     * @param $apId
     * @return bool
     */
    public static function hasAPAccess($apId)
    {
        $has_access = false;

        $condition = new CDbCriteria();
        $condition->join = "LEFT OUTER JOIN documents ON documents.Document_ID=t.Document_ID
                            LEFT OUTER JOIN images ON images.Document_ID=t.Document_ID";
        $condition->addCondition("documents.Client_ID= '" . Yii::app()->user->clientID . "'");

        if (Yii::app()->user->projectID != 'all') {
            $condition->addCondition("documents.Project_ID= '" . Yii::app()->user->projectID . "'");
        }

        $condition->addCondition("t.AP_ID = '" . $apId . "'");
        $ap = Aps::model()->find($condition);

        if ($ap) {
            $has_access = true;
        }

        return $has_access;
    }

    /**
     * Get AP list by query string and search options to certain user
     * @param string $queryString
     * @param array $options
     * @param array $sortOptions
     * @param int $limit
     * @return Aps[]
     */
    public static function getListByQueryString($queryString, $options, $sortOptions, $limit = 0,$offset=0)
    {
        $userApprovalRange = Aps::getUserApprovalRange();

        // get aps to approve
        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN documents ON documents.Document_ID=t.Document_ID
                            LEFT JOIN images ON images.Document_ID=t.Document_ID
                            LEFT JOIN vendors ON vendors.Vendor_ID=t.Vendor_ID
                            LEFT JOIN clients ON vendors.Vendor_Client_ID=clients.Client_ID
                            LEFT JOIN companies ON clients.Company_ID=companies.Company_ID
                            LEFT JOIN company_addresses ON company_addresses.Company_ID = companies.Company_ID
                            LEFT JOIN addresses ON addresses.Address_ID = company_addresses.Address_ID";


        $countCond = 0;
        if (count($options) > 0 && trim($queryString) != '') {
            if ($options['search_option_fed_id']) {
                $condition->compare('companies.Company_Fed_ID', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_com_name']) {
                $condition->compare('companies.Company_Name', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_addr1']) {
                $condition->compare('addresses.Address1', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_addr2']) {
                $condition->compare('addresses.Address2', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_city']) {
                $condition->compare('addresses.City', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_state']) {
                $condition->compare('addresses.State', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_zip']) {
                $condition->compare('addresses.ZIP', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_country']) {
                $condition->compare('addresses.Country', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_phone']) {
                $condition->compare('addresses.Phone', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_batch']) {
                $condition->compare('t.Export_Batch_ID', $queryString, false, 'OR');
                $countCond++;
            }
            if ($options['search_option_invoice_number']) {
                $condition->compare('t.Invoice_Number', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_invoice_amount']) {
                $condition->compare('t.Invoice_Amount', $queryString, false, 'OR');
                $countCond++;
            }
            if ($options['search_option_date']) {
                $condition->compare('t.Invoice_Date', $queryString, true, 'OR');
                $condition->compare('t.Invoice_Date', date('Y-m-d',strtotime($queryString)), true, 'OR');
                $countCond++;
            }
        }

        if ($countCond == 0 && trim($queryString) != '') {
            $condition->compare('companies.Company_Fed_ID', $queryString, true, 'OR');
            $condition->compare('companies.Company_Name', $queryString, true, 'OR');
            $condition->compare('addresses.Address1', $queryString, true, 'OR');
            $condition->compare('addresses.Address2', $queryString, true, 'OR');
            $condition->compare('addresses.City', $queryString, true, 'OR');
            $condition->compare('addresses.State', $queryString, true, 'OR');
            $condition->compare('addresses.ZIP', $queryString, true, 'OR');
            $condition->compare('addresses.Country', $queryString, true, 'OR');
            $condition->compare('addresses.Phone', $queryString, true, 'OR');
            $condition->compare('t.Export_Batch_ID', $queryString, false, 'OR');
        }

        $condition->addCondition("documents.Client_ID= '" . Yii::app()->user->clientID . "'");

        if (is_numeric(Yii::app()->user->projectID)) {
            $condition->addCondition("documents.Project_ID= '" . Yii::app()->user->projectID . "'");
        }

        if (Yii::app()->user->id == 'user') {
            $condition->addCondition("documents.User_ID= '" . Yii::app()->user->userID . "'");
        }

        $condition->addCondition("images.File_Name != ''");

        if ($options['search_option_to_be_approved']) {
            $condition->addCondition("t.AP_Approval_Value < '" . $userApprovalRange['user_appr_val'] . "'");
            $condition->addCondition("t.AP_Approval_Value >= '" . $userApprovalRange['prev_user_appr_val'] . "'");
            $condition->addCondition("t.Vendor_ID != '0'");
            $condition->addCondition("t.Invoice_Amount IS NOT NULL");
            $condition->addCondition("t.Invoice_Number != '0'");
            $condition->addCondition("t.Invoice_Date IS NOT NULL");
        }

        if ($options['search_option_to_be_batched']) {
            $condition->addCondition("t.AP_Approval_Value = '" . self::APPROVED . "'");
            $condition->addCondition("t.Export_Batch_ID = 0");
            $condition->addCondition("t.Vendor_ID != '0'");// to exclude Voided items
            $limit = 0;
        }

        //limit was removed according to the Tim's letter from 22.09.2014
        /*if (!$options['search_option_to_be_approved'] && trim($queryString) == '') {
            $condition->limit = $limit;
        }*/

        $condition->order = $sortOptions['sort_by'] . " " . $sortOptions['sort_direction'];

        $condition->limit = $limit;
        $condition->offset = $offset;

        $aps = Aps::model()->with('vendor')->findAll($condition);

        if (!$aps) {
            $aps = array();
        }

        return $aps;
    }

    /**
     * Get last AP's notes
     * @param $apList
     * @return array
     */
    public static function getAPsNotes($apList)
    {
        $notes = array();
        foreach($apList as $ap) {
            $note = new Notes();
            $condition = new CDbCriteria();
            $condition->condition = "Client_ID='" . Yii::app()->user->clientID . "'";
            $condition->addCondition("Document_ID = '" . $ap->Document_ID . "'");
            $condition->addCondition("Company_ID='0'");
            $condition->order = "Created DESC";
            $ap_note = $note->find($condition);

            if ($ap_note) {
                $comment = $ap_note->Comment;
                $notes[$ap->AP_ID] = $comment;
            } else {
                $notes[$ap->AP_ID] = '';
            }
        }

        return $notes;
    }

    public static function getLastNotesByApprovalValue($doc_id)
    {
        $apr_val = 50;
        /*$sql = 'SELECT distinct a.Document_ID,a.User_ID,a.Company_ID,a.Client_ID, count(a.Note_ID),max(a.Created),b.Comment,u.User_Approval_Value
                FROM notes as a
                left join notes as b on (b.Note_ID = a.Note_ID)
                left join users_client_list as u on (u.User_ID = a.User_ID and u.Client_ID = a.Client_ID)
                where u.User_Approval_Value>='.$apr_val.' and a.Document_ID='.$doc_id.'
                group by a.Document_ID,a.User_ID,a.Company_ID,a.Client_ID';
        */

        $sql = 'SELECT distinct a.Document_ID,a.User_ID,a.Company_ID,a.Client_ID,a.Comment,a.Created, u.User_Approval_Value
                FROM notes as a
                inner join
                    (
                        SELECT distinct  Document_ID,User_ID,Company_ID,Client_ID, max(a.Created) as Created, max(Note_ID) as Note_ID
                        FROM notes as a
                        where  a.Document_ID='.$doc_id.'
                        group by a.Document_ID,a.User_ID,a.Company_ID,a.Client_ID
                        ) as b  on (a.Note_ID = b.Note_ID )

                left join users_client_list as u on (u.User_ID = a.User_ID and u.Client_ID = a.Client_ID)

                where u.User_Approval_Value>='.$apr_val.' and a.Document_ID='.$doc_id;

        $connection = Yii::app()->db;
        $command = $connection->createCommand($sql);
        $notes = $command->queryAll();

        return $notes;
    }



    /**
     * Get last AP notes
     * @param $apList
     * @return array
     */
    public static function getAPNotes($doc_id)
    {
        $notes = array();

            $note = new Notes();
            $condition = new CDbCriteria();
            $condition->condition = "Client_ID='" . Yii::app()->user->clientID . "'";
            $condition->addCondition("Document_ID = '" . $doc_id . "'");
            $condition->addCondition("Company_ID='0'");
            $condition->order = "Created DESC";
            $ap_note = $note->findAll($condition);



        return $ap_note;
    }

    /**
     * Get last AP notes
     * @param $apList
     * @return array
     */
    /*public static function getNotesForBatchReport($doc_id)
    {
        $notes = array();

        $note = new Notes();
        $condition = new CDbCriteria();
        $condition->condition = "Client_ID='" . Yii::app()->user->clientID . "'";
        $condition->addCondition("Document_ID = '" . $doc_id . "'");
        $condition->addCondition("Company_ID='0'");
        $condition->order = "Created DESC";
        $ap_notes = $note->findAll($condition);
        foreach ($ap_notes as $note) {

            $userClientRow = UsersClientList::model()->findByAttributes(array(
                'User_ID' => $note->User_ID,
                'Client_ID' => $note->Client_ID,
            ));
            if ($userClientRow->User_Approval_Value >= 50 ) {
                $result_array[] = array(
                    'UserFirstname'=>$note->user->person->First_Name,
                );
            }

        }



        return $ap_notes;
    }*/

    /**
     * Get approval buttons class
     * @param $apList
     * @return string
     */
    public static function getApprovalButtonsClass($apList)
    {
        // get user approval range
        $userApprovalRange = Aps::getUserApprovalRange();
        $class='not_active_button';
        foreach($apList as $ap) {
            if ($ap->AP_Approval_Value >= $userApprovalRange['prev_user_appr_val'] &&
                $ap->AP_Approval_Value < $userApprovalRange['user_appr_val']) {
                $class='button';
                break;
            }
        }
        return $class;
    }


    /**
     * Get list Aps to approve to detail page
     * @return array
     */
    public static function getAPsToApproveToSession()
    {
        $apsToApprove = array();

        $userApprovalRange = Aps::getUserApprovalRange();

        // get aps to approve
        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN documents ON documents.Document_ID=t.Document_ID";
        $condition->addCondition("documents.Client_ID= '" . Yii::app()->user->clientID . "'");

        if (is_numeric(Yii::app()->user->projectID)) {
            $condition->addCondition("documents.Project_ID= '" . Yii::app()->user->projectID . "'");
        }

        $condition->addCondition("t.AP_Approval_Value < '" . $userApprovalRange['user_appr_val'] . "'");
        $condition->addCondition("t.AP_Approval_Value >= '" . $userApprovalRange['prev_user_appr_val'] . "'");
        $condition->addCondition("t.Vendor_ID != '0'");
        $condition->addCondition("t.Invoice_Amount IS NOT NULL");
        $condition->addCondition("t.Invoice_Number != '0'");
        $condition->addCondition("t.Invoice_Date IS NOT NULL");
        $condition->order = "documents.Created DESC";

        $aps = Aps::model()->findAll($condition);

        if ($aps) {
            $i = 1;
            foreach ($aps as $ap) {
                $apsToApprove[$i] = $ap->Document_ID;
                $i++;
            }
        }

        return $apsToApprove;
    }

    /**
     * Approve AP
     * @param $docId
     * @param $userApprovalRange
     */
    public static function approveAP($docId, $userApprovalRange)
    {
        //check document
        $document = Documents::model()->with('client')->findByAttributes(array(
            'Client_ID' => Yii::app()->user->clientID,
            'Document_ID' => $docId,
        ));

        if ($document) {
            //get AP
            $ap = Aps::model()->with('ck_req_detail')->findByAttributes(array(
                'Document_ID' => $docId,
            ));

            if ($ap) {
                if ($ap->AP_Approval_Value >= $userApprovalRange['prev_user_appr_val'] &&
                    $ap->AP_Approval_Value < $userApprovalRange['user_appr_val']) {
                    // set AP_Approval_Value and save
                    $ap->Previous_AP_A_Val = $ap->AP_Approval_Value;
                    $ap->AP_Approval_Value = $userApprovalRange['user_appr_val'];

                    if ($userApprovalRange['user_appr_val'] == self::APPROVED) {
                        $ap->Approved = 1;
                        LibraryDocs::addDocumentToFolder($ap->Document_ID);
                    }

                    $ap->save();
                    Audits::LogAction($docId,Audits::ACTION_APPROVAL);

                    // regenerate pdf
                    if ($document->Origin == 'G') {
                        //Aps::generatePdfFpdf($ap->AP_ID, ($userApprovalRange['user_appr_val'] == Aps::APPROVED));
                        Documents::pdfGeneration($ap->Document_ID,'AP',($userApprovalRange['user_appr_val'] == Aps::APPROVED));

                        Audits::LogAction($ap->Document_ID,Audits::ACTION_REPDF);
                    }



                }

                // find and unset doc from session
                Helper::removeDocumentFromViewSession($docId, 'ap_to_review');
            }
        }
    }

    /**
     * Approve AP
     * @param $docId
     * @param $userApprovalRange
     */
    public static function HardApApprove($docId, $userApprovalRange)
    {
        //check document
        $document = Documents::model()->with('client')->findByAttributes(array(
            'Client_ID' => Yii::app()->user->clientID,
            'Document_ID' => $docId,
        ));

        if ($document) {
            //get AP
            $ap = Aps::model()->with('ck_req_detail')->findByAttributes(array(
                'Document_ID' => $docId,
            ));

            if ($ap) {
                if ($ap->AP_Approval_Value < $userApprovalRange['user_appr_val']) {
                    // set AP_Approval_Value and save
                    $ap->Previous_AP_A_Val = $ap->AP_Approval_Value;
                    $ap->AP_Approval_Value = $userApprovalRange['user_appr_val'];

                    if ($userApprovalRange['user_appr_val'] == self::APPROVED) {
                        $ap->Approved = 1;
                        LibraryDocs::addDocumentToFolder($ap->Document_ID);
                    }

                    $ap->save();
                    Audits::LogAction($docId,Audits::ACTION_APPROVAL);

                    // regenerate pdf
                    if ($document->Origin == 'G') {
                        //Aps::generatePdfFpdf($ap->AP_ID, ($userApprovalRange['user_appr_val'] == Aps::APPROVED));
                        Documents::pdfGeneration($ap->Document_ID,'AP',($userApprovalRange['user_appr_val'] == Aps::APPROVED));
                        Audits::LogAction($ap->Document_ID,Audits::ACTION_REPDF);
                    }



                }

                // find and unset doc from session
                Helper::removeDocumentFromViewSession($docId, 'ap_to_review');
                Helper::removeDocumentFromViewSession($docId, 'ap_hard_approve');
            }
        }
    }



    /**
     * Get total sum of set of APs
     * @param $documents
     * @return float
     */
    public static function getTotalsSum($documents)
    {
        $sql = "SELECT sum(Invoice_Amount) as total_sum
                FROM aps
                LEFT JOIN documents as doc ON doc.Document_ID = aps.Document_ID
                WHERE doc.Document_ID IN (" . implode(',', $documents) . ")";
        $connection = Yii::app()->db;
        $command = $connection->createCommand($sql);
        $totalSum = $command->queryScalar();
        return $totalSum;
    }

    /**
     * Get new AP number
     * @return string
     */
    public static function getNewAPNumber($client_id,$project_id)
    {
        $apNumber = 0;

        $condition = new CDbCriteria();
        $condition->select = 'MAX(CAST(SUBSTRING(t.Invoice_Number, 3, length(t.Invoice_Number)-2) AS UNSIGNED))  as Invoice_Number';
        $condition->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID";
        $condition->condition = "Invoice_Number REGEXP 'CR[[:alnum:]]+'";
        $condition->addCondition("documents.Client_ID = '" . $client_id . "'");
        $condition->addCondition("documents.Project_ID = '" . $project_id . "'");
        $ap = Aps::model()->find($condition);

        if ($ap->Invoice_Number) {
            $invoiceNumber = intval(trim($ap->Invoice_Number, 'CR'));
            $apNumber = 'CR' . ($invoiceNumber + 1);
        } else {
            $project = Projects::model()->findByPk($project_id);
            $apNumber = 'CR' . $project->Ck_Req_Starting_Numb;
        }

        return $apNumber;
    }

    /**
     * Delete AP with rows in relative tables
     * @param $apId
     */
    public static function deleteAP($apId)
    {
        $ap = Aps::model()->with('document.image')->findByPk($apId);
        if ($ap) {
            $document = $ap->document;
            $image = $document->image;
            $image->delete();
            $document->delete();

            ApPayments::model()->deleteAllByAttributes(array(
                'AP_ID' => $apId,
            ));

            GlDistDetails::model()->deleteAllByAttributes(array(
                'AP_ID' => $apId,
            ));

            // delete thumbnail
            $filePath = 'protected/data/thumbs/' . $ap->Document_ID . '.jpg';
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            // delete library links
            LibraryDocs::deleteDocumentLinks($ap->Document_ID);

            $ap->delete();
        }
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

		$criteria->compare('AP_ID',$this->AP_ID);
		$criteria->compare('Document_ID',$this->Document_ID);
		$criteria->compare('Vendor_ID',$this->Vendor_ID);
        $criteria->compare('PO_ID',$this->PO_ID);
		$criteria->compare('AP_Approval_Value',$this->AP_Approval_Value,true);
		$criteria->compare('Previous_AP_A_Val',$this->Previous_AP_A_Val);
		$criteria->compare('Invoice_Amount',$this->Invoice_Amount,true);
		$criteria->compare('Invoice_Number',$this->Invoice_Number,true);
		$criteria->compare('Invoice_Date',$this->Invoice_Date,true);
		$criteria->compare('Invoice_Reference',$this->Invoice_Reference,true);
		$criteria->compare('Invoice_Due_Date',$this->Invoice_Due_Date,true);
		$criteria->compare('Detail_1099',$this->Detail_1099);
		$criteria->compare('Detail_1099_Box_Number',$this->Detail_1099_Box_Number,true);
		$criteria->compare('Export_Batch_ID',$this->Export_Batch_ID);
		$criteria->compare('Approved',$this->Approved);
        $criteria->compare('AP_Backup_Document_ID',$this->AP_Backup_Document_ID);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Aps the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public static function generateInvoiceNumber($clientId) {
       /* $sql = "SELECT COUNT(*) FROM service_payments where Client_ID='".$clientId."'";

        $num = Yii::app()->db->createCommand($sql)->queryScalar();*/
        $criteria = new CDbCriteria();
        $criteria->condition = 't.Client_ID = '.$clientId;
        //$criteria->group = 'UserId';
        $num = ServicePayments::Model()->count($criteria);
        $res=10000+$num;
        return $res;

    }

    /**
     * Resets search options to def values
     */
    public static function resetDataentrySearchOptions() {
        $_SESSION['last_ap_to_entry_search']['query'] = '';
        $_SESSION['last_ap_to_entry_search']['options'] = array(
            'search_option_com_name' => 1,
            'search_option_fed_id' => 1,
            'search_option_invoice_num' => 1,
            'search_option_inv_date' => 1,
            'search_option_inv_due_date' => 0,
            'search_option_amount' => 0,
            'search_option_description' => 0,
            'search_option_1099_type' => 0,
            'search_option_po_number' => 0,
        );
    }


    /*
     * Sets session variables according to inputted search string
     */
    public static function initDataentrySearchOptions($post) {
        $queryString = trim($post['search_field']);
        $options = array(

            'search_option_com_name' => (isset($post['search_option_com_name']) ? 1 : 0),
            'search_option_fed_id' => (isset($post['search_option_fed_id']) ? 1 : 0),
            'search_option_invoice_num' => (isset($post['search_option_invoice_num']) ? 1 : 0),
            'search_option_inv_date' => (isset($post['search_option_inv_date']) ? 1 : 0),
            'search_option_inv_due_date' => (isset($post['search_option_inv_due_date']) ? 1 : 0),
            'search_option_amount' => (isset($post['search_option_amount']) ? 1 : 0),
            'search_option_description' => (isset($post['search_option_description']) ? 1 : 0),
            'search_option_1099_type' => (isset($post['search_option_1099_type']) ? 1 : 0),
            'search_option_po_number' => (isset($post['search_option_po_number']) ? 1 : 0),

        );

        // set last search query params to session
        $_SESSION['last_ap_to_entry_search']['query'] = $queryString;
        $_SESSION['last_ap_to_entry_search']['options'] = $options;

    }


    public static function notifyNextUsers($nextUsers,$userToClient){
        if ($nextUsers) {
            $project = Projects::model()->findByPk(Yii::app()->user->projectID);
            foreach ($nextUsers as $nextUser) {
                $nextUserId = $nextUser->User_ID;
                $nextUserApprovalValue = $nextUser->User_Approval_Value;

                $user = Users::model()->with('settings','person')->findByPk($nextUserId);

                // send notification

                // get aps to approve to next users
                $condition = new CDbCriteria();
                $condition->join = "LEFT JOIN documents ON documents.Document_ID=t.Document_ID";
                $condition->condition = "documents.Client_ID='" . Yii::app()->user->clientID . "'";
                $condition->addCondition("documents.Project_ID = '" . Yii::app()->user->projectID . "'");
                $condition->addCondition("t.AP_Approval_Value < '" . $nextUserApprovalValue . "'");
                $condition->addCondition("t.AP_Approval_Value >= '" . $userToClient->User_Approval_Value . "'");
                $condition->addCondition("t.AP_Approval_Value != '0'");
                $aps = Aps::model()->find($condition);
                if ($aps) {
                    $client = Clients::model()->findByPk(Yii::app()->user->clientID);
                    $clientsToApprove = array($client->company->Company_Name.' - '.$project->Project_Name);

                    Mail::sendPendingApprovalDocumentsNotification(!$user->settings->Notification,$user, $clientsToApprove, Documents::AP,$client,$project);
                }
            }
        }
    }

    /**
     * Mark staging item as void (delete item)
     * @return CActiveRecord[]
     */
    public static function MarkStagingItemAsVoid($ap_id)
    {
        // get staging POs
        if ($ap_id != 0) {

            $ap=Aps::model()->findByPk($ap_id);


            $ap->Vendor_ID = 0;
            $ap->AP_Approval_Value = 100;
            $ap->Previous_AP_A_Val = 1;
            $ap->Approved = 1;
            //$ap->Invoice_Amount = 0; ????

            if ($ap->validate()) {
                // begin transaction
                $transaction = Yii::app()->db->beginTransaction();
                try {
                    Pos::generatePdfAddVoid($ap->Document_ID);
                    $ap->save();
                    $transaction->commit();
                } catch(Exception $e) {
                    $transaction->rollback();
                }
            }
        }
    }

    /**
     * Generate PDF for fake AP using FPDF library without saving to database.
     * @param $poId
     * @param bool $approved
     */
    public static function generatePdfFpdfPreview($ap, $ckRequest, $apDists,$approved = false)
    {
        // get PO formatting
        $poFormatting = PoFormatting::model()->findByAttributes(array(
            'Project_ID' => Yii::app()->user->projectID,
        ));

        // get Sign_Requested_By user info
        $signRequestedByUser = Users::model()->with('person')->findByPk($ckRequest->Sign_Requested_By);

        $aproval_detail_list = Audits::getApprovalDetailList($ap->Document_ID);
        // get current vendor info
        $currentVendor = Vendors::model()->with('client.company.adreses')->findByPk($ap->Vendor_ID);

        $condition = UsersClientList::getClientAdminCondition($currentVendor->client->Client_ID);
        $vendorAdmin = UsersClientList::model()->with('user.person')->find($condition);

        $pdf = new FpdfAp('P','mm','Letter');

        $pdf->AddFont('HelveticaB','','helveticab.php');
        $pdf->AddFont('Courier','','courier.php');
        $pdf->AddFont('CourierB','','courierb.php');
        $pdf->SetAutoPageBreak(true, 10);

        $pdf->setVariables($ap,$poFormatting,$ckRequest,$apDists,$currentVendor,$vendorAdmin,$signRequestedByUser,$aproval_detail_list,$approved);
        $pdf->AliasNbPages();
        $pdf->setPageNo(1);
        //$pdf->AliasNbPages();
        $pdf->AddPage('P');
        $pdf->SetFont('Helvetica','',13.5);
        $pdf->SetXY(5,10);
        $pdf->PrintContent();

        //$path=Helper::createDirectory('batches');// creates directory "protected/data/batches" if not exists
        $fileName = 'ApTempPdf'.date('Y-m-d h:i:s').'.pdf';
        $filepath = Helper::createDirectory('ap');
        $filepath = Helper::createDirectory('ap/'.Yii::app()->user->clientID);
        $filepath.= '/'.$fileName;
        $pdf->Output($filepath, 'F');
        //$pdf->Output();

        $last_page = $pdf->custom_page_num;
        $pdf->Close();


        return array(
            'filename'=>$fileName,
            'filepath'=>$filepath
        );
    }





}
