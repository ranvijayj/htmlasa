<?php

/**
 * This is the model class for table "documents".
 *
 * The followings are the available columns in table 'documents':
 * @property integer $Document_ID
 * @property string $Document_Type
 * @property integer $User_ID
 * @property string $Created
 * @property integer $Client_ID
 */
class Documents extends CActiveRecord
{
    /**
     * Uploading errors
     */
    const ERROR_INVALID_EXTENSION = 1;
    const ERROR_BIG_FILE_SIZE = 2;
    const ERROR_LOADING = 3;
    const ERROR_INVALID_FILE_NAME = 4;

    /**
     * Available doc types
     */
    const W9 = 'W9';
    const PO = 'PO';
    const AP = 'AP';
    const BU = 'BU';
    const PM = 'PM';
    const LB = 'LB';
    const GF = 'GF';
    const PR = 'PR';
    const PC = 'PC';
    const JE = 'JE';
    const AR = 'AR';

    /**
     * Available doc types array
     * @var array
     */
    static $availableDocTypes = array(
        self::W9,
        self::PO,
        self::AP,
        self::BU,
        self::PM,
        self::LB,
        self::GF,
        self::PR,
        self::PC,
        self::JE,
        self::AR,
    );

    /**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'documents';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('User_ID, Client_ID, Project_ID', 'numerical', 'integerOnly'=>true),
            array('Document_Type', 'length', 'max'=>2),
            array('Origin', 'length', 'max'=>1),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Document_ID, Document_Type, User_ID, Created, Client_ID, Project_ID, Origin', 'safe', 'on'=>'search'),
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
            'image'=>array(self::HAS_ONE, 'Images', 'Document_ID'),
            'client'=>array(self::BELONGS_TO, 'Clients', 'Client_ID', 'with'=>'company'),
            'user'=>array(self::BELONGS_TO, 'Users', 'User_ID'),
            'w9'=>array(self::HAS_ONE, 'W9', 'Document_ID'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'Document_ID' => 'Document',
			'Document_Type' => 'Document Type',
            'Origin' => 'Origin',
			'User_ID' => 'User',
			'Created' => 'Created',
            'Client_ID' => 'Client',
            'Project_ID' => 'Project'
		);
	}

    /**
     * Find user documents
     * @param $userId
     * @param $date
     * @return array|CActiveRecord|CActiveRecord[]|mixed|null
     */
    public function findUserDocuments($userId, $date = '')
    {
        $criteria=new CDbCriteria;
        $criteria->condition='User_ID=:User_ID';
        //$criteria->addCondition('Apporval_Value')

        if ($date != '') {
            $criteria->addCondition('Created>=:Created');
            $criteria->params=array(':User_ID'=>$userId, ':Created'=>$date);
        } else {
            $criteria->params=array(':User_ID'=>$userId);
        }

        $criteria->order = "Created DESC";
        $documents = $this->with('client','image')->findAll($criteria);

        return $documents;
    }

    /**
     * Upload documents
     * @param $uploadType
     * @param bool $withoutMessage
     * @return int
     */
    public static function uploadDocuments($uploadType, $withoutMessage = false) {

        if(isset($_SESSION[$uploadType]) && count($_SESSION[$uploadType]) > 0) {
            $settings = UsersSettings::model()->findByAttributes(array(
                'User_ID' => Yii::app()->user->userID,
            ));

            //get default bank account
            $condition = new CDbCriteria();
            $condition->condition = "users_project_list.User_ID = '" . Yii::app()->user->userID . "'";
            $condition->addCondition("users_project_list.Client_ID = '" . Yii::app()->user->clientID . "'");
            $condition->addCondition("t.Account_Num_ID = '" . $settings->Default_Bank_Acct . "'");
            $condition->join = "LEFT JOIN projects ON projects.Project_ID = t.Project_ID
                                LEFT JOIN users_project_list ON users_project_list.Project_ID = t.Project_ID";
            $bankAcct = BankAcctNums::model()->with('client.company', 'project')->find($condition);
            $defaultBankAcct = 0;
            if ($bankAcct) {
                $defaultBankAcct = $settings->Default_Bank_Acct;
            }

            //get user to send email
            $person_to_email = false;
            if (Yii::app()->user->id != 'user' && Yii::app()->user->id != 'single_user') {
                $person_to_email = Users::model()->with('person')->findByPk(Yii::app()->user->userID);
            } else {
                $condition = new CDbCriteria();
                $condition->join = "LEFT JOIN users_client_list ON users_client_list.User_ID = t.User_ID";
                $condition->addInCondition('users_client_list.User_Type', array(UsersClientList::APPROVER, UsersClientList::PROCESSOR, UsersClientList::CLIENT_ADMIN));
                $condition->addInCondition('t.User_Type', array(Users::ADMIN, Users::DB_ADMIN, Users::DATA_ENTRY_CLERK), "OR");
                $condition->addCondition("users_client_list.Client_ID = '" . Yii::app()->user->clientID . "'");
                $person_to_email = Users::model()->with('person')->find($condition);
            }


            foreach ($_SESSION[$uploadType] as $key => $current_upload_file) {
                // check fed id
                if ($current_upload_file['doctype'] == self::W9) {
                    if (!preg_match('/^(\d{2}\-\d{7})|(\d{3}\-\d{2}\-\d{4})|(IN[-]\d{6})|(T0[-]\d{7})$/', $current_upload_file['fed_id'])) {
                        return 2;
                    }
                }
            }


            // insert documents into DB
            foreach ($_SESSION[$uploadType] as $key => $current_upload_file) {

                if (file_exists($current_upload_file['filepath'])) {
                    // create document

                    $document = new Documents();
                    $document->Document_Type = $current_upload_file['doctype'];
                    $document->User_ID = Yii::app()->user->userID;
                    $document->Client_ID = Yii::app()->user->clientID;
                    $document->Project_ID = Yii::app()->user->projectID;
                    $document->Created = date("Y-m-d H:i:s");
                    $document->save();
                    $new_doc_id=$document->Document_ID;

                    Audits::LogAction($document->Document_ID ,Audits::ACTION_UPLOAD);

                    // insert image
                    $image = new Images();
                    $imageData = addslashes(fread(fopen($current_upload_file['filepath'],"rb"),filesize($current_upload_file['filepath'])));
                    //$imageData = FileModification::ImageToPdfByFilePath($current_upload_file['filepath']);
                    $image->Document_ID = $document->Document_ID;
                    $image->Img = $imageData;
                    $image->File_Name = $current_upload_file['name'];
                    $image->Mime_Type = $current_upload_file['mimetype'];
                    $image->File_Hash = sha1_file($current_upload_file['filepath']);
                    $image->File_Size = intval(filesize($current_upload_file['filepath']));
                    $image->Pages_Count = FileModification::calculatePagesByPath($current_upload_file['filepath']);

                    $image->save();

                    $infile = @file_get_contents($current_upload_file['filepath'], FILE_BINARY);
                    if (($current_upload_file['mimetype'] == 'application/pdf' && $image->findPdfText($infile) == '')
                        || $current_upload_file['mimetype'] != 'application/pdf') {
                        Documents::crateDocumentThumbnail($current_upload_file['filepath'], 'thumbs', $current_upload_file['mimetype'], $document->Document_ID, 80);
                    }

                    // delete file from temporary catalog and from cache table
                    //unlink($current_upload_file['filepath']);
                    FileCache::deleteBothFromCacheById($current_upload_file['file_id']);

                    if ($current_upload_file['doctype'] == self::W9) {
                        // if document is W9
                        // get additional fields
                        $fedId = trim($current_upload_file['fed_id']);
                        $newCompanyName = trim($current_upload_file['company_name']);

                        // get company info
                        $company = Companies::model()->with('client')->findByAttributes(array(
                            'Company_Fed_ID' => $fedId,
                        ));

                        // create w9
                        $W9 = new W9();
                        $W9->Document_ID = $document->Document_ID;
                        $W9->W9_Owner_ID = Yii::app()->user->clientID;
                        $W9->Creator_ID = Yii::app()->user->userID;
                        $W9->Business_Name = trim($current_upload_file['bus_name']);
                        $W9->Tax_Class =  trim($current_upload_file['tax_name']);

                        // get user info
                        $user = Users::model()->with('person')->findByPk(Yii::app()->user->userID);

                        if ($company) {
                            // if company exisits
                            $client = $company->client;

                            //fill created company with dataentry values from session
                            Companies::fillWithSessionDataEntry($company,$current_upload_file);

                            $existingW9 = W9::model()->findByAttributes(array(
                                'Client_ID' => $client->Client_ID,
                                'W9_Owner_ID' => Yii::app()->user->clientID,
                            ));

                            if ($existingW9) {
                                $W9->Revision_ID = -1;
                            } else {
                                $W9->Revision_ID = 0;
                            }

                            $vendor = Vendors::model()->findByAttributes(array(
                                'Client_Client_ID' => Yii::app()->user->clientID,
                                'Vendor_Client_ID' => $client->Client_ID,
                            ));

                            if (isset($vendor->Active_Relationship) && $vendor->Active_Relationship == Vendors::NOT_ACTIVE_RELATIONSHIP) {
                                $vendor->Active_Relationship = Vendors::ACTIVE_RELATIONSHIP;
                                $vendor->save();
                            } else if (!$vendor && Yii::app()->user->clientID != 0 && Yii::app()->user->clientID != $client->Client_ID) {
                                $vendor = new Vendors();
                                $vendor->Vendor_ID_Shortcut = '';
                                $vendor->Vendor_Client_ID = $client->Client_ID;
                                $vendor->Client_Client_ID = Yii::app()->user->clientID;
                                $vendor->Vendor_Name_Checkprint = '';
                                $vendor->Vendor_1099 = '';
                                $vendor->Vendor_Default_GL = '';
                                $vendor->Vendor_Default_GL_Note = '';
                                $vendor->Vendor_Note_General = '';

                                $vendor->Vendor_Contact = trim($current_upload_file['contact']);
                                $vendor->Vendor_Phone = trim($current_upload_file['phone']);

                                $vendor->save();
                            }
                        } else {
                            //if company does not exists, create new company
                            $client = Companies::createEmptyCompany($fedId, $newCompanyName);
                            $company_model = Companies::model()->findByPk($client->Company_ID);
                            //fill created company with dataentry values from session
                            Companies::fillWithSessionDataEntry($company_model,$current_upload_file);

                            if (Yii::app()->user->clientID != 0) {
                                $vendor = new Vendors();
                                $vendor->Vendor_ID_Shortcut = '';
                                $vendor->Vendor_Client_ID = $client->Client_ID;
                                $vendor->Client_Client_ID = Yii::app()->user->clientID;
                                $vendor->Vendor_Name_Checkprint = '';
                                $vendor->Vendor_1099 = '';
                                $vendor->Vendor_Default_GL = '';
                                $vendor->Vendor_Default_GL_Note = '';
                                $vendor->Vendor_Note_General = '';

                                $vendor->Vendor_Contact = trim($current_upload_file['contact']);
                                $vendor->Vendor_Phone = trim($current_upload_file['phone']);

                                $vendor->save();
                            }

                            $W9->Revision_ID = 0;
                        }

                        // save w9
                        $W9->Client_ID = $client->Client_ID;
                        $W9->save();

                        if ($person_to_email) {
                            Mail::sendNewW9ForDataEntry($person_to_email->person->Email, $person_to_email->person->First_Name, $person_to_email->person->Last_Name);
                        }
                    } else if ($current_upload_file['doctype'] == self::AP) {
                        //create aps
                        $aps = new Aps();
                        $aps->Document_ID = $document->Document_ID;
                        $aps->Vendor_ID = 0;
                        $aps->PO_ID = 0;
                        $aps->AP_Approval_Value = Aps::NOT_READY_FOR_APPROVAL;
                        $aps->Invoice_Number = 0;
                        $aps->save();
                    } else if ($current_upload_file['doctype'] ==  self::PM) {
                        //create payment
                        $payment = new Payments();
                        $payment->Document_ID = $document->Document_ID;
                        $payment->Vendor_ID = 0;
                        $payment->Payment_Check_Number = 0;
                        $payment->Payment_Amount = 0;
                        if ($defaultBankAcct != 0) {
                            $payment->Account_Num_ID = $defaultBankAcct;
                        } else {
                            $payment->Account_Num_ID = 0;
                        }
                        $payment->save();
                    } else if ($current_upload_file['doctype'] == self::PO) {
                        //create pos
                        $po = new Pos();
                        $po->Document_ID = $document->Document_ID;
                        $po->Vendor_ID = 0;
                        $po->PO_Number = Pos::getNewPoNumber();
                        $po->PO_Date = date('Y-m-d');
                        $po->PO_Backup_Document_ID = 0;
                        $po->save();
                    } else if ($current_upload_file['doctype'] == self::PR) {
                        $payroll = new Payrolls();
                        $payroll->Document_ID = $document->Document_ID;
                        $payroll->save();
                    } else if ($current_upload_file['doctype'] == self::JE) {
                        $je = new Journals();
                        $je->Document_ID = $document->Document_ID;
                        $je->save();
                    } else if ($current_upload_file['doctype'] == self::PC) {
                        $pc = new Pcs();
                        $pc->Document_ID = $document->Document_ID;
                        $pc->save();
                    } else if ($current_upload_file['doctype'] == self::AR) {
                        $ar = new Ars();
                        $ar->Document_ID = $document->Document_ID;
                        $ar->save();
                    }
                }
                $arr[$current_upload_file['name']]['string']= Images::getAjaxStringForLastUploadSection($new_doc_id);
                $arr[$current_upload_file['name']]['key']=$key;

            }

            $_SESSION[$uploadType] = array();
            if (!$withoutMessage) {
                Yii::app()->user->setFlash('success', "Documents have been uploaded!");
            }
            return json_encode($arr);
        } else {
            $answer['empty']=1;
            return json_encode($answer);
        }
    }

    /**
     * Check user's access to document
     * @param $docId
     * @return bool
     */
    public static function hasAccess($docId) {
        $docId = intval($docId);
        if (Yii::app()->user->userType == Users::DB_ADMIN || Yii::app()->user->userType == Users::ADMIN || Yii::app()->user->userType == Users::DATA_ENTRY_CLERK) {
        //if (Yii::app()->user->userType == Users::DB_ADMIN || Yii::app()->user->userType == Users::ADMIN ) {
            //if user is system admin or data entry clerk, he has access to all documents
            return true;
        } else {
            //get document
            $document = Documents::model()->findByPk($docId);
            if ($document) {
                //if document exists
                // check access to different kins of documents
                if ($document->Document_Type == self::W9) {
                    $existW9 = W9::model()->findByAttributes(array(
                        'Document_ID' => $docId,
                        'W9_Owner_ID' => Yii::app()->user->clientID,
                    ));
                    if ($existW9) {
                        return true;
                    }
                } else if ($document->Document_Type ==  self::PM) {
                    if (($document->User_ID == Yii::app()->user->userID) ||
                        ($document->Client_ID == Yii::app()->user->clientID && !is_numeric(Yii::app()->user->projectID)) ||
                        ($document->Client_ID == Yii::app()->user->clientID && is_numeric(Yii::app()->user->projectID) && $document->Project_ID == Yii::app()->user->projectID)) {
                        return true;
                    }
                } else if ($document->Document_Type == self::AP) {
                    if (($document->User_ID == Yii::app()->user->userID) ||
                        ($document->Client_ID == Yii::app()->user->clientID && !is_numeric(Yii::app()->user->projectID)) ||
                        ($document->Client_ID == Yii::app()->user->clientID && is_numeric(Yii::app()->user->projectID) && $document->Project_ID == Yii::app()->user->projectID)) {
                        return true;
                    }
                } else if ($document->Document_Type == self::PO) {
                    if (($document->User_ID == Yii::app()->user->userID) ||
                        ($document->Client_ID == Yii::app()->user->clientID && !is_numeric(Yii::app()->user->projectID)) ||
                        ($document->Client_ID == Yii::app()->user->clientID && is_numeric(Yii::app()->user->projectID) && $document->Project_ID == Yii::app()->user->projectID)) {
                        return true;
                    }
                } else if ($document->Document_Type == self::BU) {
                    if (($document->User_ID == Yii::app()->user->userID) ||
                        ($document->Client_ID == Yii::app()->user->clientID && !is_numeric(Yii::app()->user->projectID)) ||
                        ($document->Client_ID == Yii::app()->user->clientID && is_numeric(Yii::app()->user->projectID) && $document->Project_ID == Yii::app()->user->projectID)) {
                        return true;
                    }
                } else if ($document->Document_Type == self::LB) {
                    if (($document->User_ID == Yii::app()->user->userID) ||
                        ($document->Client_ID == Yii::app()->user->clientID && !is_numeric(Yii::app()->user->projectID)) ||
                        ($document->Client_ID == Yii::app()->user->clientID && is_numeric(Yii::app()->user->projectID) && $document->Project_ID == Yii::app()->user->projectID)) {
                        return true;
                    }
                } else if ($document->Document_Type == self::GF) {
                    if (($document->User_ID == Yii::app()->user->userID) ||
                        ($document->Client_ID == Yii::app()->user->clientID && !is_numeric(Yii::app()->user->projectID)) ||
                        ($document->Client_ID == Yii::app()->user->clientID && is_numeric(Yii::app()->user->projectID) && $document->Project_ID == Yii::app()->user->projectID)) {
                        return true;
                    }
                } else if ($document->Document_Type == self::PR) {
                    if (($document->User_ID == Yii::app()->user->userID) ||
                        ($document->Client_ID == Yii::app()->user->clientID && !is_numeric(Yii::app()->user->projectID)) ||
                        ($document->Client_ID == Yii::app()->user->clientID && is_numeric(Yii::app()->user->projectID) && $document->Project_ID == Yii::app()->user->projectID)) {
                        return true;
                    }
                } else if ($document->Document_Type == self::JE) {
                    if (($document->User_ID == Yii::app()->user->userID) ||
                        ($document->Client_ID == Yii::app()->user->clientID && !is_numeric(Yii::app()->user->projectID)) ||
                        ($document->Client_ID == Yii::app()->user->clientID && is_numeric(Yii::app()->user->projectID) && $document->Project_ID == Yii::app()->user->projectID)) {
                        return true;
                    }
                } else if ($document->Document_Type == self::PC) {
                    if (($document->User_ID == Yii::app()->user->userID) ||
                        ($document->Client_ID == Yii::app()->user->clientID && !is_numeric(Yii::app()->user->projectID)) ||
                        ($document->Client_ID == Yii::app()->user->clientID && is_numeric(Yii::app()->user->projectID) && $document->Project_ID == Yii::app()->user->projectID)) {
                        return true;
                    }
                } else if ($document->Document_Type == self::AR) {
                    if (($document->User_ID == Yii::app()->user->userID) ||
                        ($document->Client_ID == Yii::app()->user->clientID && !is_numeric(Yii::app()->user->projectID)) ||
                        ($document->Client_ID == Yii::app()->user->clientID && is_numeric(Yii::app()->user->projectID) && $document->Project_ID == Yii::app()->user->projectID)) {
                        return true;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * Get PO's BackUP
     * @param int $backupDocumentID
     * @param bool $useSession
     * @return array
     */
    public static function getBackupDoc($backupDocumentID, $useSession = true)
    {
        $backUp = array(
            'document' => false,
            'file' => false,
        );

        if ($backupDocumentID == 0 && isset($_SESSION['last_uploaded_backup']) && $useSession) {
            $backUp['document'] = Documents::model()->findByPk($_SESSION['last_uploaded_backup']);
        } elseif ($backupDocumentID != 0) {
            $backUp['document'] = Documents::model()->findByPk($backupDocumentID);
        }

        if ($backUp['document']) {
            // get document's file
            $condition = new CDbCriteria();
            $condition->select = 'Mime_Type';
            $condition->condition = "Document_ID='" . $backUp['document']->Document_ID . "'";
            $backUp['file'] = Images::model()->find($condition);
        }

        return $backUp;
    }

    /**
     * Delete document with rows in relative tables
     * @param $documentId
     */
    public static function deleteDocument($documentId) {
        $document = Documents::model()->findByPk($documentId);
        if ($document) {
            if ($document->Document_Type == self::W9) {
                $w9s = W9::model()->findAllByAttributes(array(
                    'Document_ID' => $documentId,
                ));
                foreach ($w9s as $w9) {
                    W9::deleteW9($w9->W9_ID);
                }
            } else if ($document->Document_Type == self::AP) {
                $ap = Aps::model()->findByAttributes(array(
                    'Document_ID' => $documentId,
                ));
                if ($ap) {
                    Aps::deleteAP($ap->AP_ID);
                }
            } else if ($document->Document_Type ==  self::PM) {
                $payment = Payments::model()->findByAttributes(array(
                    'Document_ID' => $documentId,
                ));
                if ($payment) {
                    Payments::deletePayment($payment->Payment_ID);
                }
            } else if ($document->Document_Type == self::PO) {
                $po = Pos::model()->findByAttributes(array(
                    'Document_ID' => $documentId,
                ));
                if ($po) {
                    Pos::deletePO($po->PO_ID);
                }
            } else if ($document->Document_Type == self::PC) {
                $pc = Pcs::model()->findByAttributes(array(
                    'Document_ID' => $documentId,
                ));
                if ($pc) {
                    Pcs::deletePC($pc->PC_ID);
                }
            } else if ($document->Document_Type == self::AR) {
                $ar = Ars::model()->findByAttributes(array(
                    'Document_ID' => $documentId,
                ));
                if ($ar) {
                    Ars::deleteAR($ar->AR_ID);
                }
            } else if ($document->Document_Type == self::PR) {
                $payroll = Payrolls::model()->findByAttributes(array(
                    'Document_ID' => $documentId,
                ));
                if ($payroll) {
                    Payrolls::deletePayroll($payroll->Payroll_ID);
                }
            }  else {
                $image = $document->image;
                $image->delete();
                $document->delete();

                // delete thumbnail
                $filePath = 'protected/data/thumbs/' . $documentId . '.jpg';
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }

                // delete library links
                LibraryDocs::deleteDocumentLinks($documentId);
            }
        }
    }

    /**
     * Creates document thumbnail
     * @param $docPath
     * @param $thumbDirectory
     * @param $mimeType
     * @param $docId
     * @param int $thumbsizeX
     * @param int $thumbsizeY
     * @param int $pageNo
     * @return bool|string
     */
    public static function crateDocumentThumbnail($docPath, $thumbDirectory,  $mimeType, $docId, $thumbsizeX = 100, $thumbsizeY = 130, $pageNo = 0)
    {
        /*
        $original = $_SERVER['DOCUMENT_ROOT'] . '/' .$docPath;		// Path to PDF files on the server
        $imagesPath = $_SERVER['DOCUMENT_ROOT'] . '/protected/data/';		            // Path to your images directory on the server
        $thumbsPath = $imagesPath . 'thumbs/';				// Path to image thumbnails directory
        $extension = '.jpg';
        $thumbnail = $thumbsPath . $docId . $extension;	    // Use $docId for the thumbnail .jpg file name

        if ($mimeType == 'application/pdf') {
            $original = $original . "[$pageNo]";
        }

        $im = new imagick($original);
        $im->setImageFormat("jpg");
        $im->resizeImage($thumbsizeX, $thumbsizeY, 1,0);
        $thumbnailBody = $im->getImageBlob();

        file_put_contents($thumbnail, $thumbnailBody);

        if (!file_exists($thumbnail)) {
            return false;
        }

        return true;
        */

        $original = Yii::getPathOfAlias('webroot') . '/' . $docPath;		// Path to PDF files on the server
        $imagesPath = Yii::getPathOfAlias('webroot') . '/protected/data/'; // Path to your images directory on the server
        $thumbsPath = $imagesPath . $thumbDirectory . '/';				// Path to image thumbnails directory

        // Constants and Defaults
        $theWMType = 'jpg:';
        $extension = '.jpg';
        $thumbnail = $thumbsPath . $docId . $extension;	    // Use $docId for the thumbnail .jpg file name

        if ($mimeType == 'application/pdf') {
            $original = $original . "[$pageNo]";
        }

        if (strpos($original, ' ') !== false) {
            $original = "\"" . $original . "\"";
        }

        $wmCmd = "convert $original";
        if ($thumbsizeX != 0) {
            $wmCmd .= " -resize " . $thumbsizeX . "x" . $thumbsizeY;
        }
        $wmCmd .= " $theWMType$thumbnail";
        $result = exec($wmCmd);

        // A little error-checking overkill
        if (!file_exists($thumbnail)) {
            return false;
        } else {
            return $thumbnail;
        }
    }

    /**
     * Creates PDF document thumbnail
     * @param $docPath
     * @param $thumbDirectory
     * @param $docId
     * @param int $thumbsizeX
     * @param int $thumbsizeY
     * @return bool|string
     */
    public static function cratePDFThumbnail($docPath, $thumbDirectory,  $docId, $thumbsizeX = 400, $thumbsizeY = 600, $batch=false)
    {

        $imagesPath = Yii::getPathOfAlias('webroot') . '/protected/data/'; // Path to your images directory on the server
        $thumbsPath = $imagesPath . $thumbDirectory;				// Path to image thumbnails directory

        $temp_file_path = $thumbsPath . '/' . ($batch ? 'batch_' : '') . $docId ;

        /*  Pdf convertion Variant 1*/
        $im = new Imagick(Yii::getPathOfAlias('webroot') . '/' . $docPath);
        $im->setImageColorspace(255);
        $im->setResolution($thumbsizeX, $thumbsizeY);
        $im->setCompression(Imagick::COMPRESSION_JPEG);
        $im->setCompressionQuality(60);
        $im->setImageFormat('jpeg');
        $im->writeImage($temp_file_path);
        $im->clear();
        $im->destroy();

        /*
         * Pdf convertion Variant 2 Works with better quality. Needs exec rights and
         **/
        /*$path_to_extracted_file = Yii::getPathOfAlias('webroot') . '/' . $docPath;
        exec('pdftoppm '.$path_to_extracted_file.' -jpeg -scale-to-x 600 -scale-to-y -1 '.$temp_file_path);
        $temp_file_path=$temp_file_path.'-1.jpg';
        exec('chmod 755 '. $temp_file_path);
        */

        // A little error-checking overkill
        if (!file_exists($temp_file_path)) {
            return false;
        } else {
            return $temp_file_path;
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

		$criteria->compare('Document_ID',$this->Document_ID);
		$criteria->compare('Document_Type',$this->Document_Type);
		$criteria->compare('User_ID',$this->User_ID);
		$criteria->compare('Created',$this->Created,true);
        $criteria->compare('Client_ID',$this->Client_ID);
        $criteria->compare('Project_ID',$this->Project_ID);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Documents the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}


    /**
     * This method is used for checking access rights for users before deleting
     * documents
     * @param $doc_id
     * @param $doc_type
     * @param $user_id
     * @param $client_id
     * @return bool
     */
    public static function hasDeletePermission($doc_id, $doc_type, $user_id, $client_id)
    {

        $result = false;
        $userClList = UsersClientList::model()->findByAttributes(array(
           'User_ID'=>$user_id,
           'Client_ID'=>$client_id,
        ));

        $doc_object = Documents::model()->findByPk($doc_id);

        $approved = false;
        if ($doc_type == Documents::AP) {
            $ap = Aps::model()->findByAttributes(array(
                'Document_ID' => $doc_id,
            ));
            if ($ap && $ap->AP_Approval_Value == Aps::APPROVED) {
                $approved = true;
            }
        } else if ($doc_type == Documents::PO) {
            $po = Pos::model()->findByAttributes(array(
                'Document_ID' => $doc_id,
            ));
            if($po && $po->PO_Approval_Value == Pos::APPROVED){
                $approved = true;
            }
        }

        //check if current user is ClientAdmin or owner for the document
         if($doc_object && !$approved && ($userClList->hasClientAdminPrivileges() || $doc_object->User_ID == $user_id)) {
             $result=true;
         }

        return $result;
    }

    public static function getDocListForDelete($countPerPage, $page)
    {
        $sql = self::getDocsForDeleteCondition();
        $sql=$sql." LIMIT " . ($page - 1) . ", $countPerPage";
        $row = Yii::app()->db->createCommand($sql)->queryAll();
        return $row;
    }


    public static function getCountDocsForDelete()
    {
        $sql = self::getDocsForDeleteCondition(true);
        $count = Yii::app()->db->createCommand($sql)->queryScalar();
        return $count;
    }

    /**
     * this functions only prepares sql for above function  getCountDocsForDelete()
     * @param bool $count needed if we wants count  rows of result only
     * @return string
     */
    public static function getDocsForDeleteCondition($count = false)
    {
        if ($count) {
            $sql="SELECT count(*) as count";
        } else {
            $sql="SELECT  d.Document_ID,d.Document_Type,i.File_Name,i.Mime_Type,d.Created,u.User_Login,p.First_Name, p.Last_Name,
                ap.Approved,ap.AP_Approval_Value,po.PO_Approved,po.PO_Approval_Value
            ";
        }


        $sql .="    FROM documents as d
                    right join  images as i on d.Document_ID=i.Document_ID
                    left join  users as u on d.User_ID=u.User_ID
                    left join  persons as p on u.Person_ID=p.Person_ID
                    left join  aps as ap on  ap.Document_ID=d.Document_ID
                    left join  pos as po on   po.Document_ID=d.Document_ID
                    where (ap.Approved!=1 or ap.Approved is null)
                            and (ap.AP_Approval_Value!=100 or ap.AP_Approval_Value is null)
                            and (po.PO_Approved!=1 or po.PO_Approved is null)
                            and (po.PO_Approval_Value!=100 or po.PO_Approval_Value is null)  ";

        //cheking user for client-admin priveleges

        //  1.create instanse of current user
        $user=UsersClientList::model()->findByAttributes(array(
            'User_ID'=>Yii::app()->user->userID,
            'Client_ID'=>Yii::app()->user->clientID,
        ));

        //  2. check for clientadmin priveleges

        if(!$user->hasClientAdminPrivileges()) {
            $sql=$sql." and d.User_ID=".Yii::app()->user->userID ;
        }

        if(Yii::app()->user->projectID != 'all') {
            $sql=$sql." and d.Project_ID=".Yii::app()->user->projectID ;
        }


        $sql=$sql." order by d.Created desc ";
       // var_dump($sql);die;
        return $sql;
    }

    /**
     * Get list of documents that can be deleted. Used for Ajax request
     * @param $queryString
     * @param $options
     * @param $sortOptions
     * @param int $limit
     * @return CActiveRecord[]
     */
    public static function getDeleteDocListByQueryString($queryString, $options,$sortOptions , $limit = 50)
    {

        $sql="SELECT  d.Document_ID,d.Document_Type,i.File_Name,i.Mime_Type,d.Created,u.User_Login,p.First_Name, p.Last_Name,
                ap.Approved,ap.AP_Approval_Value,po.PO_Approved,po.PO_Approval_Value
            ";

        $sql .="    FROM documents as d
                    right join  images as i on d.Document_ID=i.Document_ID
                    left join  users as u on d.User_ID=u.User_ID
                    left join  persons as p on u.Person_ID=p.Person_ID
                    left join  aps as ap on  ap.Document_ID=d.Document_ID
                    left join  pos as po on   po.Document_ID=d.Document_ID
                    where (ap.Approved!=1 or ap.Approved is null)
                            and (ap.AP_Approval_Value!=100 or ap.AP_Approval_Value is null)
                            and (po.PO_Approved!=1 or po.PO_Approved is null)
                            and (po.PO_Approval_Value!=100 or po.PO_Approval_Value is null)  ";
        //cheking user for client-admin priveleges

        //  1.create instanse of current user
        $user=UsersClientList::model()->findByAttributes(array(
            'User_ID'=>Yii::app()->user->userID,
            'Client_ID'=>Yii::app()->user->clientID,
        ));
        //  2. check for clientadmin priveleges
        if(!$user->hasClientAdminPrivileges()) {
            $sql=$sql." and d.User_ID=".Yii::app()->user->userID ;
        }

        if(Yii::app()->user->projectID != 'all') {
            $sql=$sql." and d.Project_ID=".Yii::app()->user->projectID ;
        }


        if (count($options) > 0 && trim($queryString) != '') {


            $countCond = 0;

            if ($options['search_option_filename']) {
                $sql=$sql." and i.File_Name like('%".$queryString."%') ";
            }
            if ($options['search_option_doctype']) {
                $sql=$sql." and lower(d.Document_Type) like('%".$queryString."%')";
            }
            if ($options['search_option_date']) {
                $sql=$sql." and d.Created>=' ".$queryString." ' ";
            }
            if ($options['search_option_createdby']) {
                $sql=$sql." and (lower(u.User_Login) like('%".$queryString."%') or upper(p.First_Name) like('%".$queryString."%') or upper(p.Last_Name) like('%".$queryString."%')) ";
            }
            if ($options['search_option_modified']) {

            }
        }

        if($sortOptions['sort_by']!='' && $sortOptions['sort_direction']!='') {
            $sql = $sql." order by ".$sortOptions['sort_by'] . " " . $sortOptions['sort_direction'];
        } else {$sql = $sql." order by d.Created desc"; }

        $sql=$sql." limit ".$limit;


        $row = Yii::app()->db->createCommand($sql)->queryAll();
        return $row;

    }
    /**
     * Get one last Document's note
     * @param $docid
     * @return array
     */
    public static function getLastNoteById($docid)
    {

        $note = new Notes();
        $condition = new CDbCriteria();
        $condition->condition = "Client_ID='" . Yii::app()->user->clientID . "'";
        $condition->addCondition("Document_ID = '" . $docid . "'");
        $condition->addCondition("Company_ID='0'");
        $condition->order = "Created DESC";
        $ap_note = $note->find($condition);

        if ($ap_note) {
            $comment = $ap_note->Comment;

        } else {
            $comment = '';
        }


        return $comment;
    }

    public static function updateApprovalValue($doc_id,$approval_value) {

        $document = Documents::model()->findByPk($doc_id);

        if ($document->Document_Type == 'PO') {

            $po = Pos::model()->findByAttributes(array(
                'Document_ID' => $doc_id
            ));

            $po->PO_Approval_Value = $approval_value;
            $po->save();
        }

        if ($document->Document_Type == 'AP') {

            $ap = Aps::model()->findByAttributes(array(
                'Document_ID' => $doc_id
            ));

            $ap->AP_Approval_Value = $approval_value;
            $ap->save();
        }

    }

    /**
     * Function checks document approval value and returns true if it is <2
     * it also returns true for non AP and PO
     * @param $doc_model
     * @return boolean
     */
    public static function checkReassigmentPossibility($doc_model) {
        if($doc_model->Document_Type == 'AP' || $doc_model->Document_Type == 'AP') {
            if($doc_model->Document_Type == 'AP') {
                $ap =Aps::model()->findByAttributes(array(
                    'Document_ID' => $doc_model->Document_ID
                ));

                if ($ap->AP_Approval_Value < 2 && $ap->Approved==0) {
                    $result = true;
                } else {
                    $result = false;
                }
            }

            if($doc_model->Document_Type == 'PO') {
                $po =Pos::model()->findByAttributes(array(
                    'Document_ID' => $doc_model->Document_ID
                ));

                if ($po->PO_Approval_Value < 2 && $po->Approved == 0) {
                    $result = true;
                } else {
                    $result = false;
                }
            }

        } else {
            $result = true;
        }


        return $result;
    }

    public static function pdfGeneration ($doc_id,$doc_type, $approved) {

        $pdf_array = FileModification::generatePdfFpdf($doc_id,$doc_type,$approved);

        $result = Images::fileToDatabase($doc_id,$pdf_array['path'],$pdf_array['pages']);

        if (file_exists($pdf_array['path'])) {
            @unlink($pdf_array['path']);
        }


    }

}
