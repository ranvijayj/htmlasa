<?php

/**
 * This is the model class for table "pos".
 *
 * The followings are the available columns in table 'pos':
 * @property integer $PO_ID
 * @property integer $Document_ID
 * @property integer $Vendor_ID
 * @property integer $PO_Number
 * @property string $PO_Account_Number
 * @property string $PO_Date
 * @property double $PO_Subtotal
 * @property double $PO_Total
 * @property double $PO_Tax
 * @property double $PO_Delivery_Chg
 * @property double $PO_Other_Chg
 * @property string $Payment_Type
 * @property string $PO_Card_Last_4_Digits
 * @property integer $PO_Approval_Value
 * @property integer $PO_Previous_PO_Val
 * @property integer $PO_Approved
 * @property integer $PO_Backup_Document_ID
 * @property integer $Export_Batch_ID
 * @property integer $Sign_Requested_By
 * @property integer $Sign_Dept_Approval
 * @property integer $Sign_UPM_Executive
 * @property integer $Sign_Accounting
 * @property integer $PO_Pmts_Tracking_Note
 */
class Pos extends CActiveRecord
{
    const APPROVED = 100;
    const READY_FOR_APPROVAL = 1;
    const NOT_READY_FOR_APPROVAL = 0;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'pos';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Document_ID, PO_Date, PO_Backup_Document_ID', 'required'),
			array('Document_ID, Vendor_ID, PO_Number, PO_Approval_Value, PO_Previous_PO_Val, PO_Approved, PO_Backup_Document_ID, Sign_Requested_By, Sign_Dept_Approval, Sign_UPM_Executive, Sign_Accounting, Export_Batch_ID', 'numerical', 'integerOnly'=>true),
			array('PO_Subtotal, PO_Total, PO_Tax, PO_Delivery_Chg, PO_Other_Chg', 'numerical'),
            array('PO_Subtotal, PO_Total, PO_Tax, PO_Delivery_Chg, PO_Other_Chg', 'length', 'max'=>13),
			array('PO_Account_Number', 'length','max'=>25,'allowEmpty'=>true),
			array('Payment_Type', 'length', 'max'=>2),
			array('PO_Card_Last_4_Digits', 'length', 'max'=>4),
            array('PO_ID, PO_Pmts_Tracking_Note', 'length', 'max'=>500),
            // The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('PO_ID, Document_ID, Vendor_ID, PO_Number, PO_Account_Number, PO_Date, PO_Subtotal, PO_Total, PO_Tax, PO_Delivery_Chg, PO_Other_Chg, Payment_Type, PO_Card_Last_4_Digits, PO_Approval_Value, PO_Previous_PO_Val, PO_Approved, PO_Backup_Document_ID, Sign_Requested_By, Sign_Dept_Approval, Sign_UPM_Executive, Sign_Accounting, Export_Batch_ID', 'safe', 'on'=>'search'),
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
            'dists' => array(self::HAS_MANY, 'PoDists', 'PO_ID'),
            'decr_details' => array(self::HAS_MANY, 'PoDescDetail', 'PO_ID'),
        );
    }


	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
            'PO_ID' => 'Po',
            'Document_ID' => 'Document',
            'Vendor_ID' => 'Vendor',
            'PO_Number' => 'Po Number',
            'PO_Account_Number' => 'Account Num',
            'PO_Date' => 'Po Date',
            'PO_Subtotal' => 'Subtotal',
            'PO_Total' => 'Total',
            'PO_Tax' => 'Tax',
            'PO_Delivery_Chg' => 'Delivery Chg',
            'PO_Other_Chg' => 'Other Chg',
            'Payment_Type' => 'Payment Type',
            'PO_Card_Last_4_Digits' => 'Last 4 Digits',
            'PO_Approval_Value' => 'Po Approval Value',
            'PO_Previous_PO_Val' => 'Po Previous Po Val',
            'PO_Approved' => 'Po Approved',
            'PO_Backup_Document_ID' => 'PO Backup Document ID',
            'Export_Batch_ID' => 'Export Batch ID',
			'Sign_Requested_By' => 'Requested By',
			'Sign_Dept_Approval' => 'Dept. Approval',
			'Sign_UPM_Executive' => 'UPM/Executive',
			'Sign_Accounting' => 'Accounting',
            'PO_Pmts_Tracking_Note' => 'PO Pmts Tracking Note',
		);
	}

    /**
     * On save event
     * @return bool
     */
    protected function beforeSave () {
        if (isset($this->PO_Tax) && ($this->PO_Tax == '' || $this->PO_Tax == 0)) {
            $this->PO_Tax = null;
        }

        if (isset($this->PO_Delivery_Chg) && ($this->PO_Delivery_Chg == '' || $this->PO_Delivery_Chg == 0)) {
            $this->PO_Delivery_Chg = null;
        }

        if (isset($this->PO_Other_Chg) && ($this->PO_Other_Chg == '' || $this->PO_Other_Chg == 0)) {
            $this->PO_Other_Chg = null;
        }

        if (isset($this->PO_Subtotal) && ($this->PO_Subtotal == '' || $this->PO_Subtotal == 0)) {
            $this->PO_Subtotal = null;
        }

        if (isset($this->PO_Total) && ($this->PO_Total == '' || $this->PO_Total == 0)) {
            $this->PO_Total = null;
        }

        return parent::beforeSave();
    }

    /**
     * Get last POs to PO List page
     * @param int $limit
     * @param bool $toBeApproved
     * @return array|CActiveRecord|CActiveRecord[]|mixed|null
     */
    public static function getLastPOs($limit = 50, $toBeApproved = false)
    {
        $userApprovalRange = Aps::getUserApprovalRange();

        // get pos
        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN documents ON documents.Document_ID=t.Document_ID
                            LEFT OUTER JOIN images ON images.Document_ID=t.Document_ID";
        $condition->condition = "documents.Client_ID='" . Yii::app()->user->clientID . "'";

        if (is_numeric(Yii::app()->user->projectID)) {
            $condition->addCondition("documents.Project_ID= '" . Yii::app()->user->projectID . "'");
        }



        $condition->addCondition("images.Image_ID IS NOT NULL");

        if ($toBeApproved) {
            $condition->addCondition("t.PO_Approval_Value < '" . $userApprovalRange['user_appr_val'] . "'");
            $condition->addCondition("t.PO_Approval_Value >= '" . $userApprovalRange['prev_user_appr_val'] . "'");
            $condition->addCondition("t.Vendor_ID != '0'");
            $condition->addCondition("t.PO_Total IS NOT NULL");
            //$condition->addCondition("t.PO_Account_Number IS NOT NULL"); commented out 13.10.2014 according to page 6 of ASAAP Revisions 2014-10-04.pdf

        } else {
            $condition->limit = $limit;
        }

        if (Yii::app()->user->userType == Users::USER) {
            $condition->addCondition("documents.User_ID='".Yii::app()->user->userID."'");

        }
        $condition->order = "documents.Created DESC";
        $pos = Pos::model()->with('vendor')->findAll($condition);

        if (!$pos) {
            $pos = array();
        }

        return $pos;
    }

    /**
     * Get PO list by query string and search options to certain user
     * @param $queryString
     * @param $options
     * @param $sortOptions
     * @param $paymentTypes
     * @param int $limit
     * @return array|CActiveRecord|mixed|null
     */
    public static function getListByQueryString($queryString, $options, $sortOptions, $paymentTypes, $limit = 50,$offset=0)
    {
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $queryString)) {
            $queryString = Helper::convertDateToServer($queryString);
        }

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
            if ($options['search_option_po_number']) {
                $condition->compare('t.PO_Number', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_po_date']) {
                $condition->compare('t.PO_Date', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_po_total']) {
                $condition->compare('t.PO_Total', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_po_acct_number']) {
                $condition->compare('t.PO_Account_Number', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_payment_type']) {
                $foundPaymentTypes = array();
                foreach ($paymentTypes as $key => $paymentType) {
                    if (strpos($paymentType, $queryString) !== false) {
                        $foundPaymentTypes[] = $key;
                    }
                }
                $condition->addInCondition('t.Payment_Type', $foundPaymentTypes, 'OR');
                $countCond++;
            }
            if ($options['search_option_last_digits']) {
                $condition->compare('t.PO_Card_Last_4_Digits', $queryString, true, 'OR');
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


        }

        if ($countCond == 0 && trim($queryString) != '') {
            $condition->compare('companies.Company_Fed_ID', $queryString, true, 'OR');
            $condition->compare('companies.Company_Name', $queryString, true, 'OR');
            $condition->compare('t.PO_Number', $queryString, true, 'OR');
            $condition->compare('t.PO_Date', $queryString, true, 'OR');
            $condition->compare('t.PO_Total', $queryString, true, 'OR');
            $condition->compare('t.PO_Account_Number', $queryString, true, 'OR');
            $condition->compare('t.Payment_Type', $queryString, true, 'OR');
            $condition->compare('t.PO_Card_Last_4_Digits', $queryString, true, 'OR');
            $condition->compare('addresses.Address1', $queryString, true, 'OR');
            $condition->compare('addresses.Address2', $queryString, true, 'OR');
            $condition->compare('addresses.City', $queryString, true, 'OR');
            $condition->compare('addresses.State', $queryString, true, 'OR');
            $condition->compare('addresses.ZIP', $queryString, true, 'OR');
            $condition->compare('addresses.Country', $queryString, true, 'OR');
            $condition->compare('addresses.Phone', $queryString, true, 'OR');
        }

        $condition->addCondition("documents.Client_ID= '" . Yii::app()->user->clientID . "'");

        if (is_numeric(Yii::app()->user->projectID)) {
            $condition->addCondition("documents.Project_ID= '" . Yii::app()->user->projectID . "'");
        }

        $condition->addCondition("images.File_Name != ''");

        if ($options['search_option_to_be_approved']) {
            $condition->addCondition("t.PO_Approval_Value < '" . $userApprovalRange['user_appr_val'] . "'");
            $condition->addCondition("t.PO_Approval_Value >= '" . $userApprovalRange['prev_user_appr_val'] . "'");
            $condition->addCondition("t.Vendor_ID != '0'");
            $condition->addCondition("t.PO_Total IS NOT NULL");
            $condition->addCondition("t.PO_Account_Number IS NOT NULL");
        }

        if ($options['search_option_to_be_batched']) {
            $condition->addCondition("t.PO_Approval_Value = '" . self::APPROVED . "'");
            $condition->addCondition("t.Export_Batch_ID = 0");
            $condition->addCondition("t.Vendor_ID != '0'");// to exclude Voided items
            $limit = 0;
        }

        if (Yii::app()->user->userType == UsersClientList::USER) {
            $condition->addCondition("documents.User_ID='" . Yii::app()->user->userID . "'");
        }

        //if (!$options['search_option_to_be_approved'] && trim($queryString) == '') {
           // $condition->limit = $limit;
        //}

        $condition->limit = $limit;
        $condition->offset = $offset;


        $condition->order = $sortOptions['sort_by'] . " " . $sortOptions['sort_direction'];


        $pos = Pos::model()->with('vendor')->findAll($condition);

        if (!$pos) {
            $pos = array();
        }

        return $pos;
    }

    /**
     * Get POs to enter data
     * @return CActiveRecord[]
     */
    public static function findPOToEntry()
    {
        $queryString = $_SESSION['last_po_to_entry_search']['query'];
        $options =  $_SESSION['last_po_to_entry_search']['options'];

        $condition = new CDbCriteria();

        $condition->join = "RIGHT JOIN documents ON documents.Document_ID=t.Document_ID";
        $condition->addCondition("t.PO_Total IS NULL");
        $condition->addCondition("t.Vendor_ID='0'", 'OR');
        $condition->addCondition("t.PO_Account_Number IS NULL", 'OR');
        $condition->addCondition("t.PO_Approval_Value ='" . Pos::NOT_READY_FOR_APPROVAL . "'", 'OR');
        $condition->addCondition("t.Sign_Requested_By='0'");

        $condition->addCondition("t.PO_Approval_Value!='100'", 'AND');//this condition for Voided POs - allows hide them from DataEntry

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

            if ($options['search_option_account_num']) {
                $search_condition->compare('t.PO_Account_Number', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_subtotal']) {
                $search_condition->compare('t.PO_Subtotal', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_tax']) {
                $search_condition->compare('t.PO_Tax', $queryString, true, 'OR');
                $countCond++;
            }


            if ($options['search_option_deliv_chg']) {
                $search_condition->compare('t.PO_Delivery_Chg', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_other_chg']) {
                $search_condition->compare('t.PO_Other_Chg', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_total']) {
                $search_condition->compare('t.PO_Total', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_paym_type']) {
                $search_condition->compare('t.Payment_Type', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_4digits']) {
                $search_condition->compare('t.PO_Card_Last_4_Digits', $queryString, true, 'OR');
                $countCond++;
            }


        }

        if (Yii::app()->user->userType == UsersClientList::PROCESSOR || Yii::app()->user->userType == UsersClientList::APPROVER
            || Yii::app()->user->userType == UsersClientList::CLIENT_ADMIN) {
            $condition->addCondition("documents.Client_ID='" . Yii::app()->user->clientID . "'");
        }

        if (Yii::app()->user->userType == UsersClientList::PROCESSOR || ((Yii::app()->user->userType == UsersClientList::APPROVER
            || Yii::app()->user->userType == UsersClientList::CLIENT_ADMIN) && is_numeric(Yii::app()->user->projectID))) {
            $condition->addCondition("documents.Project_ID='" . Yii::app()->user->projectID . "'");
        }
        if (Yii::app()->user->userType == Users::USER) {
            $condition->addCondition("documents.User_ID='".Yii::app()->user->userID."'");
        }
        if (Yii::app()->user->userType == Users::DATA_ENTRY_CLERK) {
            //adding condition to allow DEC see only documents of clients that he has access
            $cli_array = Clients::getClientsIDList(Yii::app()->user->userID);
            $condition->addInCondition('documents.Client_ID', $cli_array);
        }

        $condition->order = "documents.Created ASC";
        if( $countCond > 0 ) $condition->mergeWith($search_condition);
        $pos = Pos::model()->findAll($condition);

        return $pos;
    }

    /**
     * Get last PO's notes
     * @param $poList
     * @return array
     */
    public static function getPOsNotes($poList)
    {
        $notes = array();
        foreach($poList as $po) {
            $note = new Notes();
            $condition = new CDbCriteria();
            $condition->condition = "Client_ID='" . Yii::app()->user->clientID . "'";
            $condition->addCondition("Document_ID = '" . $po->Document_ID . "'");
            $condition->addCondition("Company_ID='0'");
            $condition->order = "Created DESC";
            $po_note = $note->find($condition);

            if ($po_note) {
                $comment = $po_note->Comment;
                $notes[$po->PO_ID] = $comment;
            } else {
                $notes[$po->PO_ID] = '';
            }
        }

        return $notes;
    }

    /**
     * Approve PO
     * @param $docId
     * @param $userApprovalRange
     */
    public static function approvePO($docId, $userApprovalRange)
    {
        //check document
        $document = Documents::model()->with('client')->findByAttributes(array(
            'Client_ID' => Yii::app()->user->clientID,
            'Document_ID' => $docId,
        ));

        if ($document) {
            //get AP
            $po = Pos::model()->with('dists', 'document')->findByAttributes(array(
                'Document_ID' => $docId,
            ));

            if ($po) {
                if ($po->PO_Approval_Value >= $userApprovalRange['prev_user_appr_val'] &&
                    $po->PO_Approval_Value < $userApprovalRange['user_appr_val']) {
                    // set PO_Approval_Value and save
                    $po->PO_Previous_PO_Val = $po->PO_Approval_Value;
                    $po->PO_Approval_Value = $userApprovalRange['user_appr_val'];

                    if ($userApprovalRange['user_appr_val'] == Pos::APPROVED) {
                        $po->PO_Approved = 1;

                        LibraryDocs::addDocumentToFolder($po->Document_ID);
                        LibraryDocs::addDocumentToBinder($po->Document_ID);
                    }

                    $po->save();

                    Audits::LogAction($docId,Audits::ACTION_APPROVAL);

                    // regenerate pdf
                    if ($document->Origin == 'G') {
                        Documents::pdfGeneration($po->Document_ID,'PO',($userApprovalRange['user_appr_val'] == Pos::APPROVED));

                        Audits::LogAction($po->Document_ID,Audits::ACTION_REPDF);
                    }


                    if ($userApprovalRange['user_appr_val'] == Pos::APPROVED) {
                        $po->updateCoaCurrentBudget();
                    }
                }

                // find and unset doc from session
                Helper::removeDocumentFromViewSession($docId, 'po_to_review');
            }
        }
    }

    /**
     * Approve PO jumping over the queue
     * @param $docId
     * @param $userApprovalRange
     */
    public static function HardPOApprove($docId, $userApprovalRange)
    {
        //check document
        $document = Documents::model()->with('client')->findByAttributes(array(
            'Client_ID' => Yii::app()->user->clientID,
            'Document_ID' => $docId,
        ));

        if ($document) {
            //get AP
            $po = Pos::model()->with('dists', 'document')->findByAttributes(array(
                'Document_ID' => $docId,
            ));

            if ($po) {
                if ($po->PO_Approval_Value < $userApprovalRange['user_appr_val']) {
                    // set PO_Approval_Value and save
                    $po->PO_Previous_PO_Val = $po->PO_Approval_Value;
                    $po->PO_Approval_Value = $userApprovalRange['user_appr_val'];

                    if ($userApprovalRange['user_appr_val'] == Pos::APPROVED) {
                        $po->PO_Approved = 1;

                        LibraryDocs::addDocumentToFolder($po->Document_ID);
                        LibraryDocs::addDocumentToBinder($po->Document_ID);
                    }

                    $po->save();

                    Audits::LogAction($docId,Audits::ACTION_APPROVAL);

                    // regenerate pdf
                    if ($document->Origin == 'G') {
                        Documents::pdfGeneration($po->Document_ID,'PO',($userApprovalRange['user_appr_val'] == Pos::APPROVED));
                        Audits::LogAction($po->Document_ID,Audits::ACTION_REPDF);
                    }

                    if ($userApprovalRange['user_appr_val'] == Pos::APPROVED) {
                        $po->updateCoaCurrentBudget();
                    }
                }

                // find and unset doc from session
                Helper::removeDocumentFromViewSession($docId, 'po_to_review');
                Helper::removeDocumentFromViewSession($docId, 'po_hard_approve');
            }
        }
    }

    /**
     * Get approval buttons class
     * @param $poList
     * @return string
     */
    public static function getApprovalButtonsClass($poList)
    {
        // get user approval range
        $userApprovalRange = Aps::getUserApprovalRange();
        $class='not_active_button';
        foreach($poList as $po) {
            if ($po->PO_Approval_Value >= $userApprovalRange['prev_user_appr_val'] &&
                $po->PO_Approval_Value < $userApprovalRange['user_appr_val']) {
                $class='button';
                break;
            }
        }
        return $class;
    }

    /**
     * Get number for new PO
     * @return int
     */
    public static function getNewPoNumber()
    {
        $lastNumber = 0;

        $condition = new CDbCriteria();
        $condition->select = "max(t.PO_Number) as PO_Number";
        $condition->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID";
        $condition->condition = "documents.Client_ID = '" . Yii::app()->user->clientID . "'";
        $condition->addCondition("documents.Project_ID = '" . Yii::app()->user->projectID . "'");
        $lastPo = Pos::model()->find($condition);

        if ($lastPo->PO_Number) {
            $lastNumber = $lastPo->PO_Number + 1;
        } else {
            $project = Projects::model()->findByPk(Yii::app()->user->projectID);
            $lastNumber = $project->PO_Starting_Number;
        }

        return $lastNumber;
    }

    /**
     * Get list POs to approve to detail page
     * @return array
     */
    public static function getPOsToApproveToSession()
    {
        $posToApprove = array();

        $userApprovalRange = Aps::getUserApprovalRange();

        // get aps to approve
        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN documents ON documents.Document_ID=t.Document_ID";
        $condition->addCondition("documents.Client_ID= '" . Yii::app()->user->clientID . "'");

        if (is_numeric(Yii::app()->user->projectID)) {
            $condition->addCondition("documents.Project_ID= '" . Yii::app()->user->projectID . "'");
        }

        $condition->addCondition("t.PO_Approval_Value < '" . $userApprovalRange['user_appr_val'] . "'");
        $condition->addCondition("t.PO_Approval_Value >= '" . $userApprovalRange['prev_user_appr_val'] . "'");
        $condition->addCondition("t.Vendor_ID != '0'");
        $condition->addCondition("t.PO_Total IS NOT NULL");
        $condition->addCondition("t.PO_Account_Number IS NOT NULL");
        $condition->order = "documents.Created DESC";

        $pos = Pos::model()->findAll($condition);

        if ($pos) {
            $i = 1;
            foreach ($pos as $po) {
                $posToApprove[$i] = $po->Document_ID;
                $i++;
            }
        }

        return $posToApprove;
    }

    /**
     * Check user's access to PO staging item
     * @param $poId
     * @return bool
     */
    public static function hasStagingPOAccess($poId)
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

        $condition->addCondition("t.PO_Approval_Value = '" . Pos::NOT_READY_FOR_APPROVAL . "'");
        $condition->addCondition("t.PO_ID = '" . $poId . "'");
        $condition->addCondition("images.Image_ID IS NULL");
        $po = Pos::model()->find($condition);

        if ($po) {
            $has_access = true;
        }

        return $has_access;
    }

    /**
     * Check user's access to PO item
     * @param $poId
     * @return bool
     */
    public static function hasPOAccess($poId)
    {
        $has_access = false;

        $condition = new CDbCriteria();
        $condition->join = "LEFT OUTER JOIN documents ON documents.Document_ID=t.Document_ID
                            LEFT OUTER JOIN images ON images.Document_ID=t.Document_ID";
        $condition->addCondition("documents.Client_ID= '" . Yii::app()->user->clientID . "'");

        if (Yii::app()->user->projectID != 'all') {
            $condition->addCondition("documents.Project_ID= '" . Yii::app()->user->projectID . "'");
        }

        $condition->addCondition("t.PO_ID = '" . $poId . "'");
        $po = Pos::model()->find($condition);

        if ($po) {
            $has_access = true;
        }

        return $has_access;
    }

    /**
     * Get staging PO items
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

        $condition->addCondition("t.PO_Approval_Value = '" . Pos::NOT_READY_FOR_APPROVAL . "'");
        $condition->addCondition("images.Image_ID IS NULL");
        $condition->order = "PO_ID DESC";

        $pos = Pos::model()->with('vendor.client.company')->findAll($condition);

        return $pos;
    }

    /**
     * Mark staging item as void (delete item)
     * @return CActiveRecord[]
     */
    public static function MarkStagingItemAsVoid($po_id)
    {
        // get staging POs

        if ($po_id != 0) {

            $po=Pos::model()->findByPk($po_id);


            $po->Vendor_ID = 0;
            $po->PO_Approval_Value = 100;
            $po->PO_Previous_PO_Val = 1;
            $po->PO_Approved = 1;
            $po->Sign_Requested_By = 0;
            $po->Sign_Dept_Approval = 0;
            $po->Sign_Accounting = 0;

            if ($po->validate()) {
                // begin transaction
                $transaction = Yii::app()->db->beginTransaction();
                try {
                    Pos::generatePdfAddVoid($po->Document_ID);
                    $po->save();
                    $transaction->commit();
                } catch(Exception $e) {
                    $transaction->rollback();
                }
        }
    }
  }


    /**
     * Makes Document (Po or AP) "voided" - writes across upper side words: "VOID void"
     * @param $doc_id
     * @return mixed|void
     */
    public static function generatePdfAddVoid($doc_id)
    {


            $return_array=FileModification::prepareFile($doc_id);

            if($return_array) {
                    if($return_array['ext']!='pdf'){
                        $return_array = FileModification::ImageToPdf($return_array['path_to_dir'],$return_array['filename'],$return_array['ext']);
                    }

                    if(!$return_array['error']) {
                        $return_array=FileModification::appendVoidText($return_array['path_to_dir'],$return_array['filename']);
                        $result['success'] = true;
                    } else {
                        $result['success'] = false;
                        $result['error'] = "File was not rendered.";
                    }


            } else {
                $return_array=FileModification::createEmpty();

            }
            if(!$result['error']) {

                $return_array = FileModification::writeToBase($return_array['path_to_dir'],$return_array['filename'],'application/pdf',$doc_id);
                $result['success'] = true;
            } else {
                $result['success'] = false;
                $result['error_message'] = "File was not rendered.";
            }

        return $return_array;

    }

 /*   public static function appendApprovalInfoToPdf($doc_id){
        $return_array=FileModification::prepareFile($doc_id);

        return_array=FileModification::appendApprovalSignature($return_array['path_to_dir'],$return_array['filename']);

        $return_array = FileModification::writeToBase($return_array['path_to_dir'],$return_array['filename'],'application/pdf',$doc_id);
 }*/

    /**
     * Generate or regenerate PDF for PO
     * @param $poId
     * @param bool $approved
     */
    public static function generatePdf($poId, $approved = false)
    {
        // get PO
        $po = Pos::model()->with('dists', 'decr_details', 'document')->findByPk($poId);

        // get PO dists
        $poDists = $po->dists;

        // get PO details
        $poDecrDetails = $po->decr_details;

        // get PO formatting
        $poFormatting = PoFormatting::model()->findByAttributes(array(
            'Project_ID' => $po->document->Project_ID,
        ));

        // get Sign_Requested_By user info
        $signRequestedByUser = Users::model()->with('person')->findByPk($po->Sign_Requested_By);

        $aproval_detail_list = Audits::getApprovalDetailList($po->Document_ID);

        // get current vendor info
        $currentVendor = Vendors::model()->with('client.company.adreses')->findByPk($po->Vendor_ID);

        $condition = UsersClientList::getClientAdminCondition($currentVendor->client->Client_ID);
        $vendorAdmin = UsersClientList::model()->with('user.person')->find($condition);

        // get content for pdf
        $content = Yii::app()->controller->renderPartial('application.views.po.po_template', array(
            'po' => $po,
            'poFormatting' => $poFormatting,
            'poDecrDetails' => $poDecrDetails,
            'poDists' => $poDists,
            'currentVendor' => $currentVendor,
            'vendorAdmin' => $vendorAdmin,
            'signRequestedByUser' => $signRequestedByUser,

            'aproval_detail_list' => $aproval_detail_list,
            'approved' => $approved,
            'paymentTypes' => array(
                'OA' => 'On Account',
                'CC' => 'Credit Card',
                'DP' => 'Deposit',
                'CK' => 'Payment Check',
                'PC' => 'Petty Cash',
            ),
        ), true);

        $fileName = 'protected/data/generated_po/' . Yii::app()->user->userID . '-' . date("Y_m_d_H_i_s") . '.pdf';
        Yii::import('ext.html2pdf.HTML2PDF');
        $html2pdf = new HTML2PDF('P', 'A4', 'en');
        $html2pdf->writeHTML($content);//TO LONG TIME!!!!!! NEEDS OPTIMISATION
        $html2pdf->Output($fileName, 'F');

        // insert or update image image
        $image = Images::model()->findByAttributes(array(
            'Document_ID' => $po->Document_ID,
        ));

        if (!$image) {
            $image = new Images();
        }

        $imageData = addslashes(fread(fopen($fileName,"rb"), filesize($fileName)));
        $image->Document_ID = $po->Document_ID;
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
     * Generate for fake PO using FPDF library without saving to database.
     * @param $poId
     * @param bool $approved
     */
    public static function generatePdfFpdfPreview($po, $detailsToSave,$distsToSave,$approved = false)
    {

        // get PO dists
        $poDists = $distsToSave;

        // get PO details
        $poDecrDetails = $detailsToSave;

        // get PO formatting
        $poFormatting = PoFormatting::model()->findByAttributes(array(
            'Project_ID' => Yii::app()->user->projectID,
        ));

        // get Sign_Requested_By user info
        $signRequestedByUser = Users::model()->with('person')->findByPk($po->Sign_Requested_By);

        $aproval_detail_list = Audits::getApprovalDetailList($po->Document_ID);

        // get current vendor info
        $currentVendor = Vendors::model()->with('client.company.adreses')->findByPk($po->Vendor_ID);

        $condition = UsersClientList::getClientAdminCondition($currentVendor->client->Client_ID);
        $vendorAdmin = UsersClientList::model()->with('user.person')->find($condition);

        $paymentTypes = array(
            'OA' => 'On Account',
            'CC' => 'Credit Card',
            'DP' => 'Deposit',
            'CK' => 'Payment Check',
            'PC' => 'Petty Cash',
        );


        $pdf = new FpdfPo('P','mm','Letter');

        $pdf->AddFont('HelveticaB','','helveticab.php');
        $pdf->AddFont('Courier','','courier.php');
        $pdf->AddFont('CourierB','','courierb.php');
        $pdf->SetAutoPageBreak(true, 10);

        $pdf->setVariables($po,$poFormatting,$poDecrDetails,$poDists,$currentVendor,$vendorAdmin,$signRequestedByUser,$aproval_detail_list,$approved,$paymentTypes);
        $pdf->AliasNbPages();
        $pdf->setPageNo(1);
        //$pdf->AliasNbPages();
        $pdf->AddPage('P');
        $pdf->SetFont('Helvetica','',13.5);
        $pdf->SetXY(5,10);
        $pdf->PrintContent();

        //$path=Helper::createDirectory('batches');// creates directory "protected/data/batches" if not exists
        $fileName = 'PoTempPdf'.date('Y-m-d h:i:s').'.pdf';
        $filepath = Helper::createDirectory('po');
        $filepath = Helper::createDirectory('po/'.Yii::app()->user->clientID);
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


    /**
     * Delete PO with rows in relative tables
     * @param $poId
     */
    public static function deletePO($poId)
    {
        $po = Pos::model()->with('document.image')->findByPk($poId);
        if ($po) {
            $document = $po->document;
            $image = $document->image;
            $image->delete();
            $document->delete();

            PoDists::model()->deleteAllByAttributes(array(
                'PO_ID' => $poId,
            ));

            PoDescDetail::model()->deleteAllByAttributes(array(
                'PO_ID' => $poId,
            ));

            PoPmtsTraking::model()->deleteAllByAttributes(array(
                'PO_ID' => $poId,
            ));

            // delete thumbnail
            $filePath = 'protected/data/thumbs/' . $po->Document_ID . '.jpg';
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            // delete library links
            LibraryDocs::deleteDocumentLinks($po->Document_ID);

            $po->delete();
        }
    }

    /**
     * Check PO balance
     * @param $poID
     * @param $invAmount
     * @return int
     */
    public static function checkPOBalance($poID, $invAmount)
    {
        $outBalance = 0;
        $po = Pos::model()->findByPk($poID);
        if ($po) {
            $condition = new CDbCriteria();
            $condition->select = 'sum(PO_Trkng_Pmt_Amt) as PO_Trkng_Pmt_Amt';
            $condition->condition = "t.PO_ID = '" . $poID . "'";
            $invSumRes = PoPmtsTraking::model()->find($condition);
            if ($invSumRes) {
                $diff = $po->PO_Total - $invSumRes->PO_Trkng_Pmt_Amt - $invAmount;
            } else {
                $diff = $po->PO_Total - $invAmount;
            }

            if ($diff < 0) {
                $outBalance = abs($diff);
            }
        }
        return $outBalance;
    }

    /**
     * Add relation between AP and PO
     * @param $po
     * @param $invAmount
     * @param $invDate
     * @param $invNumber
     * @param $invReference
     */
    public static function addApPORelation($po, $invAmount, $invDate, $invNumber, $invReference)
    {
        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN documents ON documents.Document_ID=t.Document_ID";
        $condition->condition = "t.Vendor_ID = '" . $po->Vendor_ID ."'";
        $condition->addCondition("t.Invoice_Amount = $invAmount");
        $condition->addCondition("t.Invoice_Number = '$invNumber'");
        $condition->addCondition("t.Invoice_Date = '$invDate'");
        $condition->addCondition("documents.Project_ID = '" . $po->document->Project_ID . "'");
        $relatedAP = Aps::model()->find($condition);

        if ($relatedAP !== null) {
            if ($relatedAP->PO_ID != 0) {
                $condition = new CDbCriteria();
                $condition->condition = "PO_ID = '" . $relatedAP->PO_ID . "'";
                $condition->addCondition("PO_Trkng_Inv_Date = '$invDate'");
                $condition->addCondition("PO_Trkng_Pmt_Amt = $invAmount");
                $condition->addCondition("PO_Trkng_Inv_Number = '$invNumber'");
                PoPmtsTraking::model()->deleteAll($condition);
            }

            $relatedAP->PO_ID = $po->PO_ID;
            $relatedAP->save();
        }

        $poTrack = new PoPmtsTraking();
        $poTrack->PO_ID = $po->PO_ID;
        $poTrack->PO_Trkng_Beg_Balance = $po->PO_Total;
        $poTrack->PO_Trkng_Desc = $invReference;
        $poTrack->PO_Trkng_Inv_Date = $invDate;
        $poTrack->PO_Trkng_Inv_Number = $invNumber;
        $poTrack->PO_Trkng_Pmt_Amt = $invAmount;
        if ($poTrack->validate()) {
            $poTrack->save();
        }
    }

    /**
     * Add relation between AP and PO
     * @param $poID
     * @param $invAmount
     * @param $invDate
     * @param $invNumber
     */
    public static function removeApPORelation($poID, $invAmount, $invDate, $invNumber)
    {
        if ($poID != 0) {
            $condition = new CDbCriteria();
            $condition->condition = "PO_ID = '" . $poID . "'";
            $condition->addCondition("PO_Trkng_Inv_Date = '$invDate'");
            $condition->addCondition("PO_Trkng_Pmt_Amt = $invAmount");
            $condition->addCondition("PO_Trkng_Inv_Number = '$invNumber'");
            PoPmtsTraking::model()->deleteAll($condition);
        }
    }

    /**
     * Update COA current budget for all Dists
     */
    public function updateCoaCurrentBudget()
    {
        foreach($this->dists as $dist) {
            $coa = Coa::model()->findByAttributes(array(
                'COA_Acct_Number' => $dist->PO_Dists_GL_Code_Full,
                'Project_ID' => $this->document->Project_ID,
            ));
            if ($coa) {
                $coa->COA_Current_Total = Coa::getCurrentTotal($this->document->Project_ID, $dist->PO_Dists_GL_Code_Full, $coa->COA_Budget);
                if ($coa->validate()) {
                    $coa->save();
                }
            }
        }
    }

    /**
     * Get COA current Budgets by PO list
     * @param $poList
     * @return array
     */
    public static function getCoaCurrentBudgetsByPoList($poList)
    {
        $poIds = array();
        $budgets = array();

        foreach ($poList as $po) {
            $poIds[] = $po->PO_ID;
        }

        if (count($poIds) > 0) {
            $budgets = Pos::getCoaCurrentBudgets($poIds);
        }

        return $budgets;
    }

    /**
     * Get COA budgets by PO Ids
     * @param $poIds
     * @return mixed
     */
    public static function getCoaCurrentBudgets($poIds)
    {
        $budgets = array();

        $sql = "SELECT `po_dists`.`PO_ID`, `coa`.`COA_Current_Total`, `po_dists`.`PO_Dists_GL_Code`
                FROM `po_dists`
                LEFT JOIN `pos` ON `pos`.`PO_ID` = `po_dists`.`PO_ID`
                LEFT JOIN `documents` ON `documents`.`Document_ID` = `pos`.`Document_ID`
                LEFT JOIN `coa` ON `coa`.`COA_Acct_Number` = `po_dists`.`PO_Dists_GL_Code_Full` AND `coa`.`Project_ID` = `documents`.`Project_ID`
                WHERE `po_dists`.`PO_ID` IN (" . implode(',', $poIds) . ")";

        $connection=Yii::app()->db;
        $command=$connection->createCommand($sql);
        $budgetsRes=$command->queryAll();

        foreach($budgetsRes as $budgetRes) {
            if ($budgetRes['COA_Current_Total'] !== null) {
                $budgets[$budgetRes['PO_ID']][$budgetRes['PO_Dists_GL_Code']] = ($budgetRes['COA_Current_Total'] > 0 ? '+' : '') . number_format($budgetRes['COA_Current_Total'], 2);
            }
        }

        return $budgets;
    }

    /**
     * Get total sum of set of POs
     * @param $documents
     * @return float
     */
    public static function getTotalsSum($documents)
    {
        $sql = "SELECT sum(PO_Total) as total_sum
                FROM pos
                LEFT JOIN documents as doc ON doc.Document_ID = pos.Document_ID
                WHERE doc.Document_ID IN (" . implode(',', $documents) . ")";
        $connection = Yii::app()->db;
        $command = $connection->createCommand($sql);
        $totalSum = $command->queryScalar();
        return $totalSum;
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

		$criteria->compare('PO_ID',$this->PO_ID);
		$criteria->compare('Document_ID',$this->Document_ID);
		$criteria->compare('Vendor_ID',$this->Vendor_ID);
		$criteria->compare('PO_Number',$this->PO_Number);
		$criteria->compare('PO_Account_Number',$this->PO_Account_Number,true);
		$criteria->compare('PO_Date',$this->PO_Date,true);
		$criteria->compare('PO_Subtotal',$this->PO_Subtotal);
		$criteria->compare('PO_Total',$this->PO_Total);
		$criteria->compare('PO_Tax',$this->PO_Tax);
		$criteria->compare('PO_Delivery_Chg',$this->PO_Delivery_Chg);
		$criteria->compare('PO_Other_Chg',$this->PO_Other_Chg);
		$criteria->compare('Payment_Type',$this->Payment_Type,true);
		$criteria->compare('PO_Card_Last_4_Digits',$this->PO_Card_Last_4_Digits,true);
		$criteria->compare('PO_Approval_Value',$this->PO_Approval_Value);
		$criteria->compare('PO_Previous_PO_Val',$this->PO_Previous_PO_Val);
		$criteria->compare('PO_Approved',$this->PO_Approved);
		$criteria->compare('PO_Backup_Document_ID',$this->PO_Backup_Document_ID);
        $criteria->compare('Export_Batch_ID',$this->Export_Batch_ID);
		$criteria->compare('Sign_Requested_By',$this->Sign_Requested_By);
		$criteria->compare('Sign_Dept_Approval',$this->Sign_Dept_Approval);
		$criteria->compare('Sign_UPM_Executive',$this->Sign_UPM_Executive);
		$criteria->compare('Sign_Accounting',$this->Sign_Accounting);
        $criteria->compare('PO_Pmts_Tracking_Note',$this->PO_Pmts_Tracking_Note);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Pos the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}


    /**
     * Resets search options to def values
     */
    public static function resetDataentrySearchOptions() {
        $_SESSION['last_po_to_entry_search']['query'] = '';
        $_SESSION['last_po_to_entry_search']['options'] = array(
            'search_option_com_name' => 1,
            'search_option_fed_id' => 1,
            'search_option_account_num' => 0,
            'search_option_subtotal' => 0,
            'search_option_tax' => 0,
            'search_option_deliv_chg' => 0,
            'search_option_other_chg' => 0,
            'search_option_total' => 0,
            'search_option_paym_type' => 0,
            'search_option_4digits' => 0,
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
            'search_option_account_num' => (isset($post['search_option_account_num']) ? 1 : 0),
            'search_option_subtotal' => (isset($post['search_option_subtotal']) ? 1 : 0),
            'search_option_tax' => (isset($post['search_option_tax']) ? 1 : 0),
            'search_option_deliv_chg' => (isset($post['search_option_deliv_chg']) ? 1 : 0),
            'search_option_other_chg' => (isset($post['search_option_other_chg']) ? 1 : 0),
            'search_option_total' => (isset($post['search_option_total']) ? 1 : 0),
            'search_option_paym_type' => (isset($post['search_option_paym_type']) ? 1 : 0),
            'search_option_4digits' => (isset($post['search_option_4digits']) ? 1 : 0),
        );

        // set last search query params to session
        $_SESSION['last_po_to_entry_search']['query'] = $queryString;
        $_SESSION['last_po_to_entry_search']['options'] = $options;

    }

    public static function notifyNextUsers($nextUsers,$userApprovalRange){
        if ($nextUsers) {
            $project = Projects::model()->findByPk(Yii::app()->user->projectID);
            foreach ($nextUsers as $nextUser ) {
                $nextUserId = $nextUser->User_ID;
                $nextUserApprovalValue = $nextUser->User_Approval_Value;

                $user = Users::model()->with('settings','person')->findByPk($nextUserId);

                // send notification

                // get pos to approve to next users
                $condition = new CDbCriteria();
                $condition->join = "LEFT JOIN documents ON documents.Document_ID=t.Document_ID";
                $condition->condition = "documents.Client_ID='" . Yii::app()->user->clientID . "'";
                $condition->addCondition("documents.Project_ID = '" . Yii::app()->user->projectID . "'");
                $condition->addCondition("t.PO_Approval_Value < '" . $nextUserApprovalValue . "'");
                $condition->addCondition("t.PO_Approval_Value >= '" . $userApprovalRange['user_appr_val'] . "'");
                $condition->addCondition("t.PO_Approval_Value != '0'");
                $pos = Pos::model()->find($condition);
                if ($pos) {
                    $client = Clients::model()->findByPk(Yii::app()->user->clientID);
                    $clientsToApprove = array($client->company->Company_Name.' - '.$project->Project_Name.'');

                    Mail::sendPendingApprovalDocumentsNotification(!$user->settings->Notification,$user,$clientsToApprove, Documents::PO,$client,$project);
                }
            }
        }
    }

}
