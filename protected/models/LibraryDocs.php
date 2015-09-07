<?php

/**
 * This is the model class for table "library_docs".
 *
 * The followings are the available columns in table 'library_docs':
 * @property integer $Library_Doc_ID
 * @property integer $Document_ID
 * @property integer $Subsection_ID
 * @property integer Sort_Numb
 * @property integer $Access_Type
 */
class LibraryDocs extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'library_docs';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Document_ID, Subsection_ID', 'required'),
			array('Library_Doc_ID, Document_ID, Subsection_ID, Access_Type, Sort_Numb', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Library_Doc_ID, Document_ID, Subsection_ID, Access_Type, Sort_Numb', 'safe', 'on'=>'search'),
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
            'subsection'=>array(self::BELONGS_TO, 'Subsections', 'Section_ID'),
            'document'=>array(self::BELONGS_TO, 'Documents', 'Document_ID'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'Library_Doc_ID' => 'Library Doc',
			'Document_ID' => 'Document',
			'Subsection_ID' => 'Subsection',
			'Access_Type' => 'Access Type',
            'Sort_Numb' => 'Sort Number',
		);
	}

    /**
     * Delete links for certain document
     * @param $documentId
     */
    public static function deleteDocumentLinks($documentId)
    {
        $documentId = intval($documentId);

        $condition = new CDbCriteria();
        $condition->condition = "Document_ID = '" . $documentId . "'";
        LibraryDocs::model()->deleteAll($condition);
    }

    /**
     * Add document to folder
     * @param $docId
     * @param null $vendorID
     */
    public static function addDocumentToFolder($docId, $vendorID = null)
    {
        $document = Documents::model()->findByPk($docId);
        if ($document) {
            $year = substr($document->Created, 0, 4);
            Storages::createProjectStorages($document->Project_ID, $year);
            $subsectionId = 0;
            if ($document->Document_Type ==  Documents::PM) {
                $payment = Payments::model()->findByAttributes(array(
                    'Document_ID' => $docId,
                ));

                $year = substr($payment->Payment_Check_Date, 0, 4);

                $subsectionId = Sections::createVendorFolder($document->Project_ID, $payment->Vendor_ID, $year);
            } elseif ($document->Document_Type == Documents::AP) {
                $ap = Aps::model()->findByAttributes(array(
                    'Document_ID' => $docId,
                ));

                $year = substr($ap->Invoice_Date, 0, 4);

                $subsectionId = Sections::createVendorFolder($document->Project_ID, $ap->Vendor_ID, $year);
                if ($ap->AP_Backup_Document_ID != 0) {
                    $bu = LibraryDocs::model()->findByAttributes(array(
                        'Document_ID' => $ap->AP_Backup_Document_ID,
                        'Subsection_ID' => $subsectionId,
                    ));

                    if (!$bu) {
                        $libDoc = new LibraryDocs();
                        $libDoc->Document_ID = $ap->AP_Backup_Document_ID;
                        $libDoc->Subsection_ID = $subsectionId;
                        $libDoc->Access_Type = Storages::HAS_ACCESS;
                        $libDoc->Sort_Numb = 0;
                        if ($libDoc->validate()) {
                            $libDoc->save();
                        }
                    }
                }
            } elseif ($document->Document_Type == Documents::PO) {
                $po = Pos::model()->findByAttributes(array(
                    'Document_ID' => $docId,
                ));

                $year = substr($po->PO_Date, 0, 4);

                $subsectionId = Sections::createVendorFolder($document->Project_ID, $po->Vendor_ID, $year);
                if ($po->PO_Backup_Document_ID != 0) {
                    $bu = LibraryDocs::model()->findByAttributes(array(
                        'Document_ID' => $po->PO_Backup_Document_ID,
                        'Subsection_ID' => $subsectionId,
                    ));

                    if (!$bu) {
                        $libDoc = new LibraryDocs();
                        $libDoc->Document_ID = $po->PO_Backup_Document_ID;
                        $libDoc->Subsection_ID = $subsectionId;
                        $libDoc->Access_Type = Storages::HAS_ACCESS;
                        $libDoc->Sort_Numb = 0;
                        if ($libDoc->validate()) {
                            $libDoc->save();
                        }
                    }
                }
            } elseif ($document->Document_Type == Documents::BU) {
                //get po or ap date
                $ap = Aps::model()->findByAttributes(array(
                    'AP_Backup_Document_ID' => $docId,
                ));
                if ($ap) {
                    $year = substr($ap->Invoice_Date, 0, 4);
                } else {
                    $po = Pos::model()->findByAttributes(array(
                        'PO_Backup_Document_ID' => $docId,
                    ));
                    if ($po) {
                        $year = substr($po->PO_Date, 0, 4);
                    }
                }
                $subsectionId = Sections::createVendorFolder($document->Project_ID, $vendorID, $year);
            } elseif ($document->Document_Type == Documents::PR) {
                $payroll = Payrolls::model()->findByAttributes(array(
                    'Document_ID' => $document->Document_ID,
                ));

                $year = substr($payroll->Week_Ending, 0, 4);

                $subsectionId = Sections::createPayrollFolder($document->Project_ID, $year, $payroll->Week_Ending);
            } elseif ($document->Document_Type == Documents::JE) {
                $je = Journals::model()->findByAttributes(array(
                    'Document_ID' => $document->Document_ID,
                ));

                $subsectionId = Sections::createJournalEntryFolder($document->Project_ID, $je->JE_Date);
            } elseif ($document->Document_Type == Documents::PC) {
                $pc = Pcs::model()->findByAttributes(array(
                    'Document_ID' => $document->Document_ID,
                ));

                $year = substr($pc->Envelope_Date, 0, 4);

                $subsectionId = Sections::createPettyCashFolder($document->Project_ID, $year, $pc->Employee_Name);
            } elseif ($document->Document_Type == Documents::AR) {
                $ar = Ars::model()->findByAttributes(array(
                    'Document_ID' => $document->Document_ID,
                ));

                $year = substr($ar->Invoice_Date, 0, 4);

                $subsectionId = Sections::createAccountsReceivableFolder($document->Project_ID, $year, $ar->Invoice_Date);
            }

            $libDoc = LibraryDocs::model()->findByAttributes(array(
                'Document_ID' => $docId,
                'Subsection_ID' => $subsectionId,
            ));

            if (!$libDoc) {
                $libDoc = new LibraryDocs();
                $libDoc->Document_ID = $docId;
                $libDoc->Subsection_ID = $subsectionId;
                $libDoc->Access_Type = Storages::HAS_ACCESS;
                $libDoc->Sort_Numb = 0;
                if ($libDoc->validate()) {
                    $libDoc->save();
                }
            }

            LibraryDocs::sortDocumentsInSubsection($subsectionId);
        }
    }

    /**
     * Add document to binder
     * @param $docId
     */
    public static function addDocumentToBinder($docId)
    {
        $document = Documents::model()->findByPk($docId);
        if ($document) {
            $year = substr($document->Created, 0, 4);
            Storages::createProjectStorages($document->Project_ID, $year);
            $subsectionId = 0;
            if ($document->Document_Type == Documents::PM) {
                $payment = Payments::model()->findByAttributes(array(
                    'Document_ID' => $docId,
                ));
                $year = substr($payment->Payment_Check_Date, 0, 4);
                $subsectionId = Sections::createLogBinder($document->Project_ID, $document->Document_Type, $year);
            } elseif ($document->Document_Type == Documents::PO) {
                $po = Pos::model()->findByAttributes(array(
                    'Document_ID' => $docId,
                ));
                $year = substr($po->PO_Date, 0, 4);
                $subsectionId = Sections::createLogBinder($document->Project_ID, $document->Document_Type, $year);
                if ($po->PO_Backup_Document_ID != 0) {
                    $bu = LibraryDocs::model()->findByAttributes(array(
                        'Document_ID' => $po->PO_Backup_Document_ID,
                        'Subsection_ID' => $subsectionId,
                    ));
                    if (!$bu) {
                        $libDoc = new LibraryDocs();
                        $libDoc->Document_ID = $po->PO_Backup_Document_ID;
                        $libDoc->Subsection_ID = $subsectionId;
                        $libDoc->Access_Type = Storages::HAS_ACCESS;
                        $libDoc->Sort_Numb = 0;
                        if ($libDoc->validate()) {
                            $libDoc->save();
                        }
                    }
                }
            }

            $libDoc = LibraryDocs::model()->findByAttributes(array(
                'Document_ID' => $docId,
                'Subsection_ID' => $subsectionId,
            ));

            if (!$libDoc) {
                $libDoc = new LibraryDocs();
                $libDoc->Document_ID = $docId;
                $libDoc->Subsection_ID = $subsectionId;
                $libDoc->Access_Type = Storages::HAS_ACCESS;
                $libDoc->Sort_Numb = 0;
                if ($libDoc->validate()) {
                    $libDoc->save();
                }
            }

            LibraryDocs::sortDocumentsInSubsection($subsectionId);
        }
    }

    /**
     * Sort documents in Tab or Panel
     * @param $subsectionId
     */
    public static function sortDocumentsInSubsection($subsectionId)
    {
        $subsection = Subsections::model()->with('section')->findByPk($subsectionId);
        $section = $subsection->section;

        if ($section->Folder_Cat_ID == Sections::VENDOR_DOCUMENTS) {
            LibraryDocs::sortDocumentsInVendorFolder($subsectionId);
        } else if ($section->Folder_Cat_ID == Sections::PAYROLL) {
            LibraryDocs::sortDocumentsPayrollFolder($subsectionId);
        } else if ($section->Folder_Cat_ID == Sections::JOURNAL_ENTRY) {
            LibraryDocs::sortDocumentsJournalEntryFolder($subsectionId);
        } else if ($section->Folder_Cat_ID == Sections::PATTY_CASH) {
            LibraryDocs::sortDocumentsPettyCashFolder($subsectionId);
        } else if ($section->Folder_Cat_ID == Sections::ACCOUNTS_RECEIVABLE) {
            LibraryDocs::sortDocumentsAccountsReceivableFolder($subsectionId);
        } else if ($section->Folder_Cat_ID == Sections::GENERAL) {
            LibraryDocs::sortDocumentsGeneralFolder($subsectionId);
        } else if ($section->Folder_Cat_ID == Sections::PURCHASE_ORDER_LOG) {
            LibraryDocs::sortDocumentsPosBinder($subsectionId);
        } else if ($section->Folder_Cat_ID == Sections::CHECK_LOG) {
            LibraryDocs::sortDocumentsPaymentsBinder($subsectionId);
        } else if ($section->Folder_Cat_ID == Sections::W9_BOOK) {
            LibraryDocs::sortDocumentsW9Binder($subsectionId);
        }
    }

    /**
     * Sort documents in Pos binder
     * @param $subsectionId
     * @param string $sortType
     * @param bool $return
     * @return array
     */
    public static function sortDocumentsPosBinder($subsectionId, $sortType = 'num', $return = false)
    {
        $docsQueue = array();
        $addedDocs = array(0=>'0');

        $condition = new CDbCriteria();
        $condition->condition = "t.Subsection_ID = '$subsectionId'";

        // get Pos
        $criteria = clone $condition;
        $criteria->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID
                           LEFT JOIN pos ON pos.Document_ID = documents.Document_ID
                           LEFT JOIN vendors ON pos.Vendor_ID = vendors.Vendor_ID
                           LEFT JOIN clients ON clients.Client_ID = vendors.Vendor_Client_ID
                           LEFT JOIN companies ON companies.Company_ID = clients.Company_ID";
        $criteria->addCondition("documents.Document_Type = '" . Documents::PO . "'");
        if ($sortType == 'alpha') {
            $criteria->order = "companies.Company_Name ASC, pos.PO_Number DESC";
        } else {
            $criteria->order = "pos.PO_Number DESC";
        }
        $pos = LibraryDocs::model()->findAll($criteria);
        foreach ($pos as $po) {
            $docsQueue[] = $po;
            $addedDocs[] = $po->Document_ID;

            // get BU
            $criteria = clone $condition;
            $criteria->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID
                              LEFT JOIN pos ON pos.PO_Backup_Document_ID = documents.Document_ID";
            $criteria->addCondition("documents.Document_Type = '" . Documents::BU . "'");
            $criteria->addCondition("pos.Document_ID = '" . $po->Document_ID . "'");
            $bu = LibraryDocs::model()->find($criteria);

            if ($bu) {
                $docsQueue[] = $bu;
                $addedDocs[] = $bu->Document_ID;
            }
        }

        // get other docs
        $criteria = clone $condition;
        $criteria->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID";
        $criteria->addCondition("t.Document_ID NOT IN ('" . implode("','", $addedDocs) . "')");
        $criteria->order = "documents.Created ASC";
        $docs = LibraryDocs::model()->findAll($criteria);
        foreach ($docs as $doc) {
            $docsQueue[] = $doc;
        }

        if (!$return) {
            // set documents order nums
            $i = 1;
            foreach($docsQueue as $doc) {
                $doc->Sort_Numb = $i;
                $doc->save();
                $i++;
            }
        }

        return $docsQueue;
    }

    /**
     * Sort documents in Payments binder
     * @param $subsectionId
     * @return array
     */
    public static function sortDocumentsPaymentsBinder($subsectionId)
    {
        $docsQueue = array();
        $addedDocs = array(0=>'0');

        $condition = new CDbCriteria();
        $condition->condition = "t.Subsection_ID = '$subsectionId'";

        // get Pos
        $criteria  = clone $condition;
        $criteria->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID
                           LEFT JOIN payments ON payments.Document_ID = documents.Document_ID
                           LEFT JOIN bank_acct_nums ON bank_acct_nums.Account_Num_ID = payments.Account_Num_ID";

        $criteria->addCondition("documents.Document_Type = '" . Documents::PM . "'");
        $criteria->order = "bank_acct_nums.Account_Number DESC, payments.Payment_Check_Number DESC";

        $payments = LibraryDocs::model()->findAll($criteria);
        foreach ($payments as $payment) {
            $docsQueue[] = $payment;
            $addedDocs[] = $payment->Document_ID;
        }

        // get other docs
        $criteria = clone $condition;
        $criteria->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID";
        $criteria->addCondition("t.Document_ID NOT IN ('" . implode("','", $addedDocs) . "')");
        $criteria->order = "documents.Created ASC";
        $docs = LibraryDocs::model()->findAll($criteria);
        foreach ($docs as $doc) {
            $docsQueue[] = $doc;
        }

        // set documents order nums
        $i = 1;
        foreach($docsQueue as $doc) {
            $doc->Sort_Numb = $i;
            $doc->save();
            $i++;
        }
    }

    /**
     * Sort documents in W9 book binder
     * @param $subsectionId
     */
    public static function sortDocumentsW9Binder($subsectionId)
    {
        $docsQueue = array();

        // get other docs
        $criteria = new CDbCriteria();
        $criteria->condition = "t.Subsection_ID = '$subsectionId'";
        $criteria->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID";
        $criteria->order = "documents.Created DESC";
        $docs = LibraryDocs::model()->findAll($criteria);
        foreach ($docs as $doc) {
            $docsQueue[] = $doc;
        }

        // set documents order nums
        $i = 1;
        foreach($docsQueue as $doc) {
            $doc->Sort_Numb = $i;
            $doc->save();
            $i++;
        }
    }

    /**
     * Sort documents in payroll folder
     * @param $subsectionId
     */
    public static function sortDocumentsPayrollFolder($subsectionId)
    {
        $docsQueue = array();
        $addedDocs = array(0=>'0');

        $condition = new CDbCriteria();
        $condition->condition = "t.Subsection_ID = '$subsectionId'";
        $condition->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID
                            LEFT JOIN payrolls ON documents.Document_ID = payrolls.Document_ID
                            LEFT JOIN payroll_types ON payroll_types.Payroll_Type_ID = payrolls.Payroll_Type_ID";
        $condition->order = "payroll_types.Title ASC, payrolls.Version ASC, documents.Created ASC";

        // get Pos
        $criteria  = clone $condition;
        $criteria->addCondition("documents.Document_Type = '" . Documents::PR . "'");

        $prs = LibraryDocs::model()->findAll($criteria);
        foreach ($prs as $pr) {
            $docsQueue[] = $pr;
            $addedDocs[] = $pr->Document_ID;
        }

        // get other docs
        $criteria = clone $condition;
        $criteria->addCondition("t.Document_ID NOT IN ('" . implode("','", $addedDocs) . "')");
        $docs = LibraryDocs::model()->findAll($criteria);
        foreach ($docs as $doc) {
            $docsQueue[] = $doc;
        }

        // set documents order nums
        $i = 1;
        foreach($docsQueue as $doc) {
            $doc->Sort_Numb = $i;
            $doc->save();
            $i++;
        }
    }

    /**
     * Sort documents in Petty Cash folder
     * @param $subsectionId
     */
    public static function sortDocumentsPettyCashFolder($subsectionId)
    {
        $docsQueue = array();
        $addedDocs = array(0=>'0');

        $condition = new CDbCriteria();
        $condition->condition = "t.Subsection_ID = '$subsectionId'";
        $condition->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID
                            LEFT JOIN pcs ON documents.Document_ID = pcs.Document_ID";
        $condition->order = "pcs.Envelope_Number ASC, documents.Created ASC";

        // get Pos
        $criteria  = clone $condition;
        $criteria->addCondition("documents.Document_Type = '" . Documents::PC . "'");

        $pcs = LibraryDocs::model()->findAll($criteria);
        foreach ($pcs as $pc) {
            $docsQueue[] = $pc;
            $addedDocs[] = $pc->Document_ID;
        }

        // get other docs
        $criteria = clone $condition;
        $criteria->addCondition("t.Document_ID NOT IN ('" . implode("','", $addedDocs) . "')");
        $docs = LibraryDocs::model()->findAll($criteria);
        foreach ($docs as $doc) {
            $docsQueue[] = $doc;
        }

        // set documents order nums
        $i = 1;
        foreach($docsQueue as $doc) {
            $doc->Sort_Numb = $i;
            $doc->save();
            $i++;
        }
    }

    /**
     * Sort documents in General folder
     * @param $subsectionId
     */
    public static function sortDocumentsGeneralFolder($subsectionId)
    {
        $docsQueue = array();

        $condition = new CDbCriteria();
        $condition->condition = "t.Subsection_ID = '$subsectionId'";
        $condition->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID";
        $condition->order = "documents.Created ASC";

        $docs = LibraryDocs::model()->findAll($condition);
        foreach ($docs as $doc) {
            $docsQueue[] = $doc;
        }

        // set documents order nums
        $i = 1;
        foreach($docsQueue as $doc) {
            $doc->Sort_Numb = $i;
            $doc->save();
            $i++;
        }
    }

    /**
     * Sort documents in Accounts Receivable folder
     * @param $subsectionId
     */
    public static function sortDocumentsAccountsReceivableFolder($subsectionId)
    {
        $docsQueue = array();
        $addedDocs = array(0=>'0');

        $condition = new CDbCriteria();
        $condition->condition = "t.Subsection_ID = '$subsectionId'";
        $condition->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID
                            LEFT JOIN ars ON documents.Document_ID = ars.Document_ID";
        $condition->order = "ars.Invoice_Number DESC, documents.Created DESC";

        // get Pos
        $criteria  = clone $condition;
        $criteria->addCondition("documents.Document_Type = '" . Documents::AR . "'");

        $ars = LibraryDocs::model()->findAll($criteria);
        foreach ($ars as $ar) {
            $docsQueue[] = $ar;
            $addedDocs[] = $ar->Document_ID;
        }

        // get other docs
        $criteria = clone $condition;
        $criteria->addCondition("t.Document_ID NOT IN ('" . implode("','", $addedDocs) . "')");
        $docs = LibraryDocs::model()->findAll($criteria);
        foreach ($docs as $doc) {
            $docsQueue[] = $doc;
        }

        // set documents order nums
        $i = 1;
        foreach($docsQueue as $doc) {
            $doc->Sort_Numb = $i;
            $doc->save();
            $i++;
        }
    }

    /**
     * Sort documents in Journal Entry Folder
     * @param $subsectionId
     */
    public static function sortDocumentsJournalEntryFolder($subsectionId)
    {
        $docsQueue = array();
        $addedDocs = array(0=>'0');

        $condition = new CDbCriteria();
        $condition->condition = "t.Subsection_ID = '$subsectionId'";
        $condition->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID";
        $condition->order = "documents.Created DESC";

        // get Pos
        $criteria  = clone $condition;
        $criteria->addCondition("documents.Document_Type = '" . Documents::JE . "'");

        $jes = LibraryDocs::model()->findAll($criteria);
        foreach ($jes as $je) {
            $docsQueue[] = $je;
            $addedDocs[] = $je->Document_ID;
        }

        // get other docs
        $criteria = clone $condition;
        $criteria->addCondition("t.Document_ID NOT IN ('" . implode("','", $addedDocs) . "')");
        $docs = LibraryDocs::model()->findAll($criteria);
        foreach ($docs as $doc) {
            $docsQueue[] = $doc;
        }

        // set documents order nums
        $i = 1;
        foreach($docsQueue as $doc) {
            $doc->Sort_Numb = $i;
            $doc->save();
            $i++;
        }
    }

    /**
     * Sort Documents in Panel of Vendor's folder
     * @param $subsectionId
     */
    public static function sortDocumentsInVendorFolder($subsectionId)
    {
        $condition = new CDbCriteria();
        $condition->condition = "t.Subsection_ID = '$subsectionId'";

        // get payments
        $criteria = clone $condition;
        $criteria->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID
                           LEFT JOIN payments ON payments.Document_ID = documents.Document_ID";
        $criteria->addCondition("documents.Document_Type = '" . Documents::PM . "'");
        $criteria->order = "payments.Payment_Check_Number DESC";
        $payments = LibraryDocs::model()->findAll($criteria);

        $docsQueue = array();
        $addedDocs = array(0=>'0');
        foreach ($payments as $payment) {
            $docsQueue[] = $payment;
            $addedDocs[] = $payment->Document_ID;

            // get Pos
            $criteria = clone $condition;
            $criteria->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID
                               LEFT JOIN pos ON pos.Document_ID = documents.Document_ID
                               LEFT JOIN aps ON aps.PO_ID = pos.PO_ID
                               LEFT JOIN ap_payments ON ap_payments.AP_ID = aps.AP_ID
                               LEFT JOIN payments ON ap_payments.Payment_ID = payments.Payment_ID";
            $criteria->addCondition("documents.Document_Type = '" . Documents::PO . "'");
            $criteria->addCondition("payments.Document_ID = '" . $payment->Document_ID . "'");
            $criteria->order = "pos.PO_Number DESC";
            $pos = LibraryDocs::model()->findAll($criteria);
            foreach ($pos as $po) {
                // get Aps
                $criteria = clone $condition;
                $criteria->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID
                                   LEFT JOIN aps ON aps.Document_ID = documents.Document_ID
                                   LEFT JOIN pos ON aps.PO_ID = pos.PO_ID";
                $criteria->addCondition("documents.Document_Type = '" . Documents::AP . "'");
                $criteria->addCondition("pos.Document_ID = '" . $po->Document_ID . "'");
                $criteria->order = "aps.Invoice_Number DESC";
                $aps = LibraryDocs::model()->findAll($criteria);

                foreach ($aps as $ap) {
                    $docsQueue[] = $ap;
                    $addedDocs[] = $ap->Document_ID;

                    // get AP's BUs
                    $criteria = clone $condition;
                    $criteria->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID
                                       LEFT JOIN aps ON aps.AP_Backup_Document_ID = documents.Document_ID";
                    $criteria->addCondition("documents.Document_Type = '" . Documents::BU . "'");
                    $criteria->addCondition("aps.Document_ID = '" . $ap->Document_ID . "'");
                    $bu = LibraryDocs::model()->find($criteria);
                    if ($bu) {
                        $docsQueue[] = $bu;
                        $addedDocs[] = $bu->Document_ID;
                    }
                }

                $docsQueue[] = $po;
                $addedDocs[] = $po->Document_ID;

                // get PO's BUs
                $criteria = clone $condition;
                $criteria->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID
                                   LEFT JOIN pos ON pos.PO_Backup_Document_ID = documents.Document_ID";
                $criteria->addCondition("documents.Document_Type = '" . Documents::BU . "'");
                $criteria->addCondition("pos.Document_ID = '" . $po->Document_ID . "'");
                $bu = LibraryDocs::model()->find($criteria);

                if ($bu) {
                    $docsQueue[] = $bu;
                    $addedDocs[] = $bu->Document_ID;
                }
            }

            // get payment aps without POs
            $criteria = clone $condition;
            $criteria->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID
                               LEFT JOIN aps ON aps.Document_ID = documents.Document_ID
                               LEFT JOIN ap_payments ON ap_payments.AP_ID = aps.AP_ID
                               LEFT JOIN payments ON ap_payments.Payment_ID = payments.Payment_ID";
            $criteria->addCondition("documents.Document_Type = '" . Documents::AP . "'");
            $criteria->addCondition("payments.Document_ID = '" . $payment->Document_ID . "'");
            $criteria->addCondition("t.Document_ID NOT IN ('" . implode("','", $addedDocs) . "')");
            $criteria->order = "aps.Invoice_Number DESC";
            $aps = LibraryDocs::model()->findAll($criteria);

            foreach ($aps as $ap) {
                $docsQueue[] = $ap;
                $addedDocs[] = $ap->Document_ID;

                // get AP's BUs
                $criteria = clone $condition;
                $criteria->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID
                                   LEFT JOIN aps ON aps.AP_Backup_Document_ID = documents.Document_ID";
                $criteria->addCondition("documents.Document_Type = '" . Documents::BU . "'");
                $criteria->addCondition("aps.Document_ID = '" . $ap->Document_ID . "'");
                $bu = LibraryDocs::model()->find($criteria);
                if ($bu) {
                    $docsQueue[] = $bu;
                    $addedDocs[] = $bu->Document_ID;
                }
            }
        }

        // add ap-po blocks without payments
        $criteria = clone $condition;
        $criteria->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID
                           LEFT JOIN pos ON pos.Document_ID = documents.Document_ID";
        $criteria->addCondition("documents.Document_Type = '" . Documents::PO . "'");
        $criteria->addCondition("t.Document_ID NOT IN ('" . implode("','", $addedDocs) . "')");
        $criteria->order = "pos.PO_Number DESC";
        $pos = LibraryDocs::model()->findAll($criteria);
        foreach ($pos as $po) {
            // get Aps
            $criteria = clone $condition;
            $criteria->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID
                               LEFT JOIN aps ON aps.Document_ID = documents.Document_ID
                               LEFT JOIN pos ON aps.PO_ID = pos.PO_ID";
            $criteria->addCondition("documents.Document_Type = '" . Documents::AP . "'");
            $criteria->addCondition("pos.Document_ID = '" . $po->Document_ID . "'");
            $criteria->order = "aps.Invoice_Number DESC";
            $aps = LibraryDocs::model()->findAll($criteria);

            foreach ($aps as $ap) {
                $docsQueue[] = $ap;
                $addedDocs[] = $ap->Document_ID;

                // get AP's BUs
                $criteria = clone $condition;
                $criteria->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID
                                       LEFT JOIN aps ON aps.AP_Backup_Document_ID = documents.Document_ID";
                $criteria->addCondition("documents.Document_Type = '" . Documents::BU . "'");
                $criteria->addCondition("aps.Document_ID = '" . $ap->Document_ID . "'");
                $bu = LibraryDocs::model()->find($criteria);
                if ($bu) {
                    $docsQueue[] = $bu;
                    $addedDocs[] = $bu->Document_ID;
                }
            }

            $docsQueue[] = $po;
            $addedDocs[] = $po->Document_ID;

            // get BU
            $criteria = clone $condition;
            $criteria->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID
                               LEFT JOIN pos ON pos.PO_Backup_Document_ID = documents.Document_ID";
            $criteria->addCondition("documents.Document_Type = '" . Documents::BU . "'");
            $criteria->addCondition("pos.Document_ID = '" . $po->Document_ID . "'");
            $bu = LibraryDocs::model()->find($criteria);

            if ($bu) {
                $docsQueue[] = $bu;
                $addedDocs[] = $bu->Document_ID;
            }
        }

        // get single aps
        $criteria = clone $condition;
        $criteria->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID
                           LEFT JOIN aps ON aps.Document_ID = documents.Document_ID";
        $criteria->addCondition("documents.Document_Type = '" . Documents::AP . "'");
        $criteria->addCondition("t.Document_ID NOT IN ('" . implode("','", $addedDocs) . "')");
        $criteria->order = "aps.Invoice_Number DESC";
        $aps = LibraryDocs::model()->findAll($criteria);
        foreach ($aps as $ap) {
            $docsQueue[] = $ap;
            $addedDocs[] = $ap->Document_ID;

            // get AP's BUs
            $criteria = clone $condition;
            $criteria->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID
                               LEFT JOIN aps ON aps.AP_Backup_Document_ID = documents.Document_ID";
            $criteria->addCondition("documents.Document_Type = '" . Documents::BU . "'");
            $criteria->addCondition("aps.Document_ID = '" . $ap->Document_ID . "'");
            $bu = LibraryDocs::model()->find($criteria);
            if ($bu) {
                $docsQueue[] = $bu;
                $addedDocs[] = $bu->Document_ID;
            }
        }

        // get other docs
        $criteria = clone $condition;
        $criteria->join = "LEFT JOIN documents ON documents.Document_ID = t.Document_ID";
        $criteria->addCondition("t.Document_ID NOT IN ('" . implode("','", $addedDocs) . "')");
        $criteria->order = "documents.Created ASC";
        $docs = LibraryDocs::model()->findAll($criteria);
        foreach ($docs as $doc) {
            $docsQueue[] = $doc;
        }

        // set documents order nums
        $i = 1;
        foreach($docsQueue as $doc) {
            $doc->Sort_Numb = $i;
            $doc->save();
            $i++;
        }
    }

    /**
     * Prepare documents of subsection to view
     * @param $documents
     * @param $vendorId
     * @param $sectionFolderCategory
     * @param $year
     * @return array
     */
    public static function prepareDocumentsOfSubsectionToView($documents, $vendorId, $sectionFolderCategory, $year)
    {
        $result = array();

        // if section is W9 Book, add all available for company W9s (they are not in LibraryDocs)
        if ($sectionFolderCategory == Sections::W9_BOOK) {
            $w9Docs = W9::getAvailableW9sOfYear($year);
            foreach ($w9Docs as $w9Doc) {
                $result[] = $w9Doc;
            }
        }

        foreach($documents as $document) {
            $result[] = $document->document;
        }

        // if section is Vendor Documents, add last available for company W9s of this vendor (it is not in LibraryDocs)
        if ($vendorId != 0 && $sectionFolderCategory == Sections::VENDOR_DOCUMENTS) {
            $vendor = Vendors::model()->with('client')->findByPk($vendorId);
            if ($vendor) {
                $document = W9::getCompanyW9Doc($vendor->client->Client_ID);
                if ($document) {
                    $result[] = $document;
                }
            }
        }

        return $result;
    }

    /**
     * Get unassigned LB and GF documents
     * @param $clientID
     * @param $projectID
     * @param $userID
     * @param $year
     * @return CActiveRecord[]
     */
    public static function getUnassignedLibraryDocuments($clientID, $projectID, $userID, $year)
    {
        $condition = new CDbCriteria();
        $condition->join = "LEFT OUTER JOIN library_docs ON library_docs.Document_ID=t.Document_ID";
        $condition->condition = "t.Client_ID='" . $clientID . "'";
        $condition->addCondition("t.Project_ID='" . $projectID . "'");
        $condition->addCondition("t.User_ID='" . $userID . "'");
        $condition->addCondition("t.Created LIKE '" . $year . "-%'");
        $condition->addInCondition('t.Document_Type', array(Documents::LB, Documents::GF));
        $condition->addCondition("library_docs.Document_ID IS NULL");
        $condition->order = "t.Created ASC";
        $documents = Documents::model()->with(array(
            'image'=>array(
                'select'=>'File_Name',
            ),
        ))->findAll($condition);

        return $documents;
    }


    /**
     * Get unassigned LB and GF documents
     * @param $clientID
     * @param $projectID
     * @param $userID
     * @param $year
     * @return CActiveRecord[]
     */
    public static function getUnassignedFromSession()
    {
        foreach ($_SESSION['unassigned_items'] as $key=>$value) {
            $ids_array[] = $key;
        }

        $condition = new CDbCriteria();
        $condition->join = "LEFT OUTER JOIN library_docs ON library_docs.Document_ID=t.Document_ID";
        $condition->condition = "library_docs.Document_ID IS NULL";
        $condition->addInCondition('t.Document_ID', $ids_array);

        $condition->order = "t.Created ASC";
        $documents = Documents::model()->with(array(
            'image'=>array(
                'select'=>'File_Name',
            ),
        ))->findAll($condition);

        return $documents;
    }


    /**
     * Assign LB or GF document to subsection
     * @param $subsId
     * @param $docId
     * @param $access
     */
    public static function assignLBDocumentToSubsection($subsId, $docId, $access)
    {
        $subsId = intval($subsId);
        $docId = intval($docId);
        $access = intval($access);
        if ($subsId > 0 && $docId > 0 && $access <= 1) {
            $libDoc = LibraryDocs::model()->findByAttributes(array(
                'Document_ID' => $docId,
                'Subsection_ID' => $subsId,
            ));
            if (!$libDoc) {
                $condition = new CDbCriteria();
                $condition->select = 'max(Sort_Numb) as Sort_Numb';
                $condition->condition = "t.Subsection_ID = '" . $subsId . "'";
                $sortNumRes = LibraryDocs::model()->find($condition);
                if ($sortNumRes) {
                    $sortNum = intval($sortNumRes->Sort_Numb) + 1;
                } else {
                    $sortNum = 0;
                }

                $libDoc = new LibraryDocs();
                $libDoc->Access_Type = $access;
                $libDoc->Document_ID = $docId;
                $libDoc->Subsection_ID = $subsId;
                $libDoc->Sort_Numb = $sortNum;
                if ($libDoc->validate()) {
                    $libDoc->save();
                }
            }
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

		$criteria->compare('Library_Doc_ID',$this->Library_Doc_ID);
		$criteria->compare('Document_ID',$this->Document_ID);
		$criteria->compare('Subsection_ID',$this->Subsection_ID);
		$criteria->compare('Access_Type',$this->Access_Type);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return LibraryDocs the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
     public static function updateSesStorageToReview($section_id,$subsection_id) {
         $_SESSION['storage_to_review'] = array(
             'id' => $section_id,
             'type' => 'section',
         );
     }

}
