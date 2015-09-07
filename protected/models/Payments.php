<?php

/**
 * This is the model class for table "payments".
 *
 * The followings are the available columns in table 'payments':
 * @property integer $Payment_ID
 * @property integer $Vendor_ID
 * @property integer $Document_ID
 * @property string $Payment_Check_Date
 * @property integer $Payment_Check_Number
 * @property string $Payment_Amount
 * @property string $Void
 */
class Payments extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'payments';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Vendor_ID, Document_ID', 'required'),
			array('Vendor_ID, Document_ID, Account_Num_ID, Payment_Check_Number', 'numerical', 'integerOnly'=>true),
			array('Payment_Amount', 'numerical'),
			array('Payment_Amount', 'length', 'max'=>13),
			array('Payment_Check_Number', 'length', 'max'=>11),
			array('Void', 'boolean'),
			array('Payment_Check_Date', 'date', 'format' => 'yyyy-MM-dd'),
            array('Payment_ID', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Payment_ID, Vendor_ID, Document_ID, Account_Num_ID, Payment_Check_Date, Payment_Check_Number, Payment_Amount', 'safe', 'on'=>'search'),
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
            'aps'=>array(self::MANY_MANY, 'Aps', 'ap_payments(Payment_ID, AP_ID)'),
            'bank_account' => array(self::BELONGS_TO, 'BankAcctNums', 'Account_Num_ID'),
            'payment_invoices' => array(self::HAS_MANY, 'PaymentsInvoice', 'Payment_ID'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'Payment_ID' => 'Payment',
			'Vendor_ID' => 'Vendor',
			'Document_ID' => 'Document',
			'Payment_Check_Date' => 'Payment Check Date',
			'Payment_Check_Number' => 'Payment Check Number',
			'Payment_Amount' => 'Payment Amount',
            'Account_Num_ID' => 'Account Number',
            'Void'=>'This is void payment'
		);
	}

    /**
     * On save event
     * @return bool
     */
    protected function beforeSave () {
        if (isset($this->Payment_Amount) && ($this->Payment_Amount == '' || $this->Payment_Amount == 0)) {
            $this->Payment_Amount = null;
        }
        return parent::beforeSave();
    }

    /**
     * Get payments to entry data
     * @return CActiveRecord[]
     */
    public static function findPaymentsToEntry()
    {
        $queryString = $_SESSION['last_paym_to_entry_search']['query'];
        $options =  $_SESSION['last_paym_to_entry_search']['options'];

        $condition = new CDbCriteria();
        $search_condition = new CDbCriteria();

        $condition->join = "LEFT JOIN documents ON documents.Document_ID=t.Document_ID";
        $condition->condition = "t.Payment_Check_Number = '0' ";

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
            $condition->addCondition("documents.User_ID='" . Yii::app()->user->userID . "'");
        }

        if (Yii::app()->user->userType == Users::DATA_ENTRY_CLERK) {
            //adding condition to allow DEC see only documents of clients that he has access
            $cli_array = Clients::getClientsIDList(Yii::app()->user->userID);
            $condition->addInCondition('documents.Client_ID', $cli_array);
        }

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

            if ($options['search_option_pmt_num']) {
                $search_condition->compare('t.Payment_Check_Number', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_pmt_amount']) {
                $search_condition->compare('t.Payment_Amount', $queryString, true, 'OR');
                $countCond++;
            }

            if ($options['search_option_pmt_date']) {
                $search_condition->compare('t.Payment_Check_Date', $queryString, true, 'OR');
                $countCond++;
            }

        }

        if( $countCond > 0 ) $condition->mergeWith($search_condition);

        $payments = Payments::model()->findAll($condition);

        return $payments;
    }

    /**
     * Get Last Client Payments
     * @param int $limit
     * @return array|CActiveRecord|CActiveRecord[]|mixed|null
     */
    public static function getPaymentsList($limit = 50)
    {
        $payments = new Payments();

        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN documents ON documents.Document_ID=t.Document_ID";
        //$condition->condition = "t.Payment_Amount='0'";
        $condition->addCondition("documents.Client_ID='" . Yii::app()->user->clientID . "'");

        if (is_numeric(Yii::app()->user->projectID)) {
            $condition->addCondition("documents.Project_ID= '" . Yii::app()->user->projectID . "'");
        }

        if (Yii::app()->user->id == 'user') {
            $condition->addCondition("documents.User_ID= '" . Yii::app()->user->userID . "'");
        }

        $condition->order = "t.Payment_Check_Number DESC";
        $condition->limit = $limit;
        $paymentsList = $payments->with('vendor', 'bank_account')->findAll($condition);

        return $paymentsList;
    }


    /**
     * Get Last Client Payments to session
     * @return array
     */
    public static function getLastClientsPayment()
    {
        $lastPayment = array();

        $payments = new Payments();
        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN documents ON documents.Document_ID=t.Document_ID";
        $condition->addCondition("documents.Client_ID='" . Yii::app()->user->clientID . "'");

        if (is_numeric(Yii::app()->user->projectID)) {
            $condition->addCondition("documents.Project_ID= '" . Yii::app()->user->projectID . "'");
        }

        $condition->order = "t.Payment_ID DESC";
        $payment = $payments->find($condition);

        if ($payment) {
            $lastPayment[1] = $payment->Document_ID;
        }

        return $lastPayment;
    }

    /**
     * Add Payment-AP relationship
     * @param $payment
     * @param $paymentInvoice
     */
    public static function addAPPaymentRelationship($payment, $paymentInvoice)
    {
        $document = Documents::model()->findByPk($payment->Document_ID);

        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID";
        $condition->condition = "t.Vendor_ID=:vendorID";
        $condition->addCondition("t.Invoice_Amount=:invoice_Amount");
        $condition->addCondition("t.Invoice_Number=:invoice_Number");
        $condition->addCondition("documents.Project_ID = '" . $document->Project_ID . "'");
        $condition->params = array(
            ':vendorID' => $payment->Vendor_ID,
            ':invoice_Amount' => $paymentInvoice->Check_Invoice_Amount,
            ':invoice_Number' => $paymentInvoice->Check_Invoice_Number,
        );
        $ap = Aps::model()->find($condition);

        if ($ap) {
            $apPayment = ApPayments::model()->findByAttributes(array(
                'AP_ID' => $ap->AP_ID,
                'Payment_ID' => $payment->Payment_ID,
            ));

            if (!$apPayment) {
                $apPayment = new ApPayments();
                $apPayment->AP_ID = $ap->AP_ID;
                $apPayment->Payment_ID = $payment->Payment_ID;
                $apPayment->save();
                $ap->Payment_ID = $payment->Payment_ID;
                $ap->save();
            }

        }
    }

    /**
     * Get related Payment's AP
     * @param $payment
     * @param $invoiceNumber
     * @return Aps|null
     */
    public static function getPaymentInvoice($payment, $invoiceNumber)
    {
        $document = Documents::model()->findByPk($payment->Document_ID);

        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID";
        $condition->condition = "t.Vendor_ID=:vendorID";
        $condition->addCondition("t.Invoice_Number=:invoice_Number");
        $condition->addCondition("documents.Project_ID = '" . $document->Project_ID . "'");
        $condition->params = array(
            ':vendorID' => $payment->Vendor_ID,
            ':invoice_Number' => $invoiceNumber,
        );
        $ap = Aps::model()->find($condition);

        return $ap;
    }

    /**
     * Get related Payment's APs
     * @param $payment
     * @param $invoiceNumber
     * @return Aps|null
     */
    public static function getPaymentInvoices($payment, $invoiceNumber)
    {
        $document = Documents::model()->findByPk($payment->Document_ID);

        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID";
        $condition->condition = "t.Vendor_ID=:vendorID";
        $condition->addCondition("t.Invoice_Number=:invoice_Number");
        $condition->addCondition("documents.Project_ID = '" . $document->Project_ID . "'");
        $condition->params = array(
            ':vendorID' => $payment->Vendor_ID,
            ':invoice_Number' => $invoiceNumber,
        );
        $ap = Aps::model()->findAll($condition);

        return $ap;
    }

    /**
     * Get Payments list by query string and search options
     * @param $queryString
     * @param $options
     * @param $sortOptions
     * @param int $limit
     * @return array|CActiveRecord|mixed|null
     */
    public static function getListByQueryString($queryString, $options, $sortOptions, $limit = 50,$offset=0)
    {
        // get payments list condition
        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN documents ON documents.Document_ID=t.Document_ID
                            LEFT JOIN vendors ON vendors.Vendor_ID=t.Vendor_ID
                            LEFT JOIN clients ON vendors.Vendor_Client_ID=clients.Client_ID
                            LEFT JOIN companies ON clients.Company_ID=companies.Company_ID
                            LEFT JOIN company_addresses ON company_addresses.Company_ID = companies.Company_ID
                            LEFT JOIN addresses ON addresses.Address_ID = company_addresses.Address_ID
                            LEFT JOIN ap_payments ON ap_payments.Payment_ID = t.Payment_ID
                            LEFT JOIN aps ON aps.AP_ID = ap_payments.AP_ID";

        $countCond = 0;
        if (count($options) > 0 && trim($queryString) != '') {
            if ($options['search_option_payment_check_date']) {
                $condition->compare('t.Payment_Check_Date', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_payment_check_number']) {
                $condition->compare('t.Payment_Check_Number', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_payment_amount']) {
                $condition->compare('t.Payment_Amount', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_invoice_number']) {
                $condition->compare('aps.Invoice_Number', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_invoice_amount']) {
                $condition->compare('ap_payments.Invoice_Amount', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_invoice_date']) {
                $condition->compare('ap_payments.Invoice_Date', $queryString, true, 'OR');
                $countCond++;
            }
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
        }

        if ($countCond == 0 && trim($queryString) != '') {
            $condition->compare('t.Payment_Check_Date', $queryString, true, 'OR');
            $condition->compare('t.Payment_Check_Number', $queryString, true, 'OR');
            $condition->compare('t.Payment_Amount', $queryString, true, 'OR');
            $condition->compare('ap_payments.Invoice_Number', $queryString, true, 'OR');
            $condition->compare('ap_payments.Invoice_Amount', $queryString, true, 'OR');
            $condition->compare('ap_payments.Invoice_Date', $queryString, true, 'OR');
            $condition->compare('companies.Company_Fed_ID', $queryString, true, 'OR');
            $condition->compare('companies.Company_Name', $queryString, true, 'OR');
            $condition->compare('addresses.Address1', $queryString, true, 'OR');
            $condition->compare('addresses.City', $queryString, true, 'OR');
            $condition->compare('addresses.State', $queryString, true, 'OR');
            $condition->compare('addresses.ZIP', $queryString, true, 'OR');
        }

        //$condition->addCondition("t.Payment_Amount='0'");
        $condition->addCondition("documents.Client_ID='" . Yii::app()->user->clientID . "'");

        if (is_numeric(Yii::app()->user->projectID)) {
            $condition->addCondition("documents.Project_ID= '" . Yii::app()->user->projectID . "'");
        }

        if (Yii::app()->user->userType == UsersClientList::USER) {
            $condition->addCondition("documents.User_ID='" . Yii::app()->user->userID . "'");
        }

        if (count($options['bankAccounts']) > 0) {
            $condition->addInCondition('t.Account_Num_ID', $options['bankAccounts']);
        }

        $condition->order = $sortOptions['sort_by'] . " " . $sortOptions['sort_direction'];

        /*if (trim($queryString) == '') {
            $condition->limit = $limit;
        }*/
        $condition->limit = $limit;
        $condition->offset = $offset;

        $payments = Payments::model()->with('vendor', 'bank_account')->findAll($condition);

        if (!$payments) {
            $payments = array();
        }

        return $payments;
    }

    /**
     * Get client account numbers array
     * @param $clientId
     * @param $projectId
     * @return array
     */
    public static function getClientAccountNumbers($clientId, $projectId)
    {
        $acctNumbs = array();

        // get account numbers
        $bankAcctNumbs = BankAcctNums::model()->findAllByAttributes(array(
            'Client_ID' => $clientId,
            'Project_ID' => $projectId,
        ));

        // create result array
        if ($bankAcctNumbs) {
            foreach ($bankAcctNumbs as $bankAcctNumb) {
                $acctNumbs[$bankAcctNumb->Account_Num_ID] = $bankAcctNumb->Account_Name . ' / ' . $bankAcctNumb->Account_Number;
            }
        }

        return $acctNumbs;
    }

    /**
     * Delete Payment with rows in relative tables
     * @param $paymentId
     */
    public static function deletePayment($paymentId)
    {
        $payment = Payments::model()->with('document.image')->findByPk($paymentId);
        if ($payment) {
            $document = $payment->document;
            $image = $document->image;
            $image->delete();
            $document->delete();

            PaymentsInvoice::model()->deleteAllByAttributes(array(
                'Payment_ID' => $paymentId,
            ));

            ApPayments::model()->deleteAllByAttributes(array(
                'Payment_ID' => $paymentId,
            ));

            // delete thumbnail
            $filePath = 'protected/data/thumbs/' . $payment->Document_ID . '.jpg';
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            // delete library links
            LibraryDocs::deleteDocumentLinks($payment->Document_ID);

            $payment->delete();
        }
    }

    /**
     * Search project payments
     * @return CActiveDataProvider
     */
    public function searchProjectPayments()
    {
        $criteria=new CDbCriteria;

        $criteria->join = "LEFT JOIN documents ON documents.Document_ID=t.Document_ID";

        $criteria->compare('Payment_ID',$this->Payment_ID);
        $criteria->compare('Vendor_ID',$this->Vendor_ID);
        $criteria->compare('Document_ID',$this->Document_ID);
        $criteria->compare('Account_Num_ID',$this->Account_Num_ID);
        $criteria->compare('Payment_Check_Date',$this->Payment_Check_Date,true);
        $criteria->compare('Payment_Check_Number',$this->Payment_Check_Number);
        $criteria->compare('Payment_Amount',$this->Payment_Amount,true);

        $criteria->addCondition("documents.Client_ID='" . Yii::app()->user->clientID . "'");

        if (is_numeric(Yii::app()->user->projectID)) {
            $criteria->addCondition("documents.Project_ID='" . Yii::app()->user->projectID . "'");
        }

        $criteria->addCondition("t.Payment_Check_Number != '0'");

        $criteria->order = 't.Payment_Check_Date DESC';

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
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

		$criteria->compare('Payment_ID',$this->Payment_ID);
		$criteria->compare('Vendor_ID',$this->Vendor_ID);
		$criteria->compare('Document_ID',$this->Document_ID);
        $criteria->compare('Account_Num_ID',$this->Account_Num_ID);
		$criteria->compare('Payment_Check_Date',$this->Payment_Check_Date,true);
		$criteria->compare('Payment_Check_Number',$this->Payment_Check_Number);
		$criteria->compare('Payment_Amount',$this->Payment_Amount,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Payments the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * Resets search options to def values
     */
    public static function resetDataentrySearchOptions() {
        $_SESSION['last_paym_to_entry_search']['query'] = '';
        $_SESSION['last_paym_to_entry_search']['options'] = array(
            'search_option_com_name' => 1,
            'search_option_fed_id' => 1,
            'search_option_pmt_num' => 1,
            'search_option_pmt_amount' => 1,
            'search_option_pmt_date' => 0,

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
            'search_option_pmt_num' => (isset($post['search_option_pmt_num']) ? 1 : 0),
            'search_option_pmt_amount' => (isset($post['search_option_pmt_amount']) ? 1 : 0),
            'search_option_pmt_date' => (isset($post['search_option_inv_due_date']) ? 1 : 0),
        );

        // set last search query params to session
        $_SESSION['last_paym_to_entry_search']['query'] = $queryString;
        $_SESSION['last_paym_to_entry_search']['options'] = $options;

    }

    /**
     * Returns true if at list one invoices amount equal to payment amount
     * @param $amount
     * @param $invoices_array
     * @return bool
     */
    public static function checkInvoicesAmount($payment_amount,$invoices_array){
        $match = false;

        foreach ($invoices_array as $ap) {
            if  ($ap->Invoice_Amount == $payment_amount) {
                $match = true;
            }
        }

        return $match;
    }

}
