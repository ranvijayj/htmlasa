<?php

/**
 * This is the model class for table "sections".
 *
 * The followings are the available columns in table 'sections':
 * @property integer $Section_ID
 * @property integer $Storage_ID
 * @property integer $Vendor_ID
 * @property string $Section_Name
 * @property integer $Created_By
 * @property integer $Section_Type
 * @property integer $Folder_Cat_ID
 */
class Sections extends CActiveRecord
{
    /**
     * Folder types
     */
    const VENDOR_DOCUMENTS = 1;
    const PAYROLL = 2;
    const JOURNAL_ENTRY = 3;
    const PATTY_CASH = 4;
    const ACCOUNTS_RECEIVABLE = 5;
    const GENERAL = 6;
    const PURCHASE_ORDER_LOG = 7;
    const CHECK_LOG = 8;
    const W9_BOOK = 9;

    /**
     * Folder Categories names
     * @var array
     */
    static $folderCategoriesNames = array(
        self::VENDOR_DOCUMENTS => 'Vendor Folder',
        self::PAYROLL => 'Payroll Folder',
        self::PATTY_CASH => 'Petty Cash Folder',
        self::ACCOUNTS_RECEIVABLE => 'Accounts Receivable Folder',
        self::JOURNAL_ENTRY => 'Journal Entry Folder',
        self::GENERAL => 'General Folder',
        self::PURCHASE_ORDER_LOG => 'Purchase Order Log Binder',
        self::CHECK_LOG => 'Check Log Binder',
        self::W9_BOOK => 'W9 Binder',
    );

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'sections';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Storage_ID, Section_Name, Created_By', 'required'),
			array('Storage_ID, Created_By, Section_Type, Folder_Cat_ID, Access_Type, Vendor_ID', 'numerical', 'integerOnly'=>true),
			array('Section_Name', 'length', 'max'=>50),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Section_ID, Storage_ID, Section_Name, Created_By, Section_Type, Folder_Cat_ID, Access_Type, Vendor_ID', 'safe', 'on'=>'search'),
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
            'storage'=>array(self::BELONGS_TO, 'Storages', 'Storage_ID'),
            'subsections'=>array(self::HAS_MANY, 'Subsections', 'Section_ID'),
            'user'=>array(self::BELONGS_TO, 'Users', 'Created_By'),
            'folder_type'=>array(self::BELONGS_TO, 'FolderCategories', 'Folder_Cat_ID'),
            'vendor'=>array(self::BELONGS_TO, 'Vendors', 'Vendor_ID'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'Section_ID' => 'Section',
			'Storage_ID' => 'Storage',
            'Vendor_ID' => 'Vendor',
			'Section_Name' => 'Title',
			'Created_By' => 'Created By',
			'Section_Type' => 'Section Type',
			'Folder_Cat_ID' => 'Category',
            'Access_Type' => 'Access Type',
		);
	}

    /**
     * Get count of documents in storage
     * @param $id
     * @param $rowType
     * @return mixed
     */
    public static function getDocumentsCount($id, $rowType)
    {
        $where = "(`ld`.`Access_Type` = '" . Storages::HAS_ACCESS . "' OR `ds`.`User_ID` = '" . Yii::app()->user->userID ."') ";
        $where .= " AND `st`.`Client_ID` = '" . Yii::app()->user->clientID ."' ";
        if (Yii::app()->user->projectID != 'all') {
            $where .= " AND `st`.`Project_ID` = '" . Yii::app()->user->projectID ."' ";
        }

        if ($rowType == 'storage') {
            $where .= " AND `st`.`Storage_ID` = '" . $id ."' ";
        } else if ($rowType == 'section') {
            $where .= " AND `sc`.`Section_ID` = '" .$id ."' ";
        } else {
            $where .= " AND `ss`.`Subsection_ID` = '" . $id ."' ";
        }

        if (Yii::app()->user->id == 'user') {
            $where .= " AND `ds`.`User_ID` = '" . Yii::app()->user->userID . "'";
        }

        $query = "SELECT count(*) as count
                  FROM `library_docs` as ld
                  LEFT JOIN `documents` as ds ON `ds`.`Document_ID` = `ld`.`Document_ID`
                  LEFT JOIN `subsections` as ss ON `ss`.`Subsection_ID` = `ld`.`Subsection_ID`
                  LEFT JOIN `sections` as sc ON `sc`.`Section_ID` = `ss`.`Section_ID`
                  LEFT JOIN `storages` as st ON `st`.`Storage_ID` = `sc`.`Storage_ID`
                  WHERE $where";

        $connection=Yii::app()->db;
        $command=$connection->createCommand($query);
        $row=$command->queryRow();
        $count = $row['count'];

        // add unassigned W9s of Vendor folder and W9 book binder
        if ($rowType == 'storage') {
            $storage = Storages::model()->findByPk($id);
            if ($storage->Storage_Type == Storages::SHELF) {
                $count += W9::getCountOfAvailableW9sOfYear($storage->Year);
            } else {
                $condition = new CDbCriteria();
                $condition->condition = "t.Storage_ID = '" . $id . "'";
                $condition->addCondition("t.Folder_Cat_ID = '" . self::VENDOR_DOCUMENTS . "'");
                $vendorFolders = Sections::model()->findAll($condition);
                foreach ($vendorFolders as $vendorFolder) {
                    $w9 = W9::getCompanyW9Doc($vendorFolder->Vendor_ID);
                    if ($w9) {
                        $count++;
                    }
                }
            }
        } else if ($rowType == 'section') {
            $section = Sections::model()->with('storage')->findByPk($id);
            if ($section->Folder_Cat_ID == self::W9_BOOK) {
                $count += W9::getCountOfAvailableW9sOfYear($section->storage->Year);
            } else if ($section->Folder_Cat_ID == self::VENDOR_DOCUMENTS) {
                $w9 = W9::getCompanyW9Doc($section->Vendor_ID);
                if ($w9) {
                    $count++;
                }
            }
        } else {
            $subsection = Subsections::model()->with('user', 'section.storage')->findByPk($id);
            if ($subsection->section->Folder_Cat_ID == self::W9_BOOK && $subsection->Created_By == 0) {
                $count += W9::getCountOfAvailableW9sOfYear($subsection->section->storage->Year);
            } else if ($subsection->section->Folder_Cat_ID == self::VENDOR_DOCUMENTS && $subsection->Created_By == 0) {
                $w9 = W9::getCompanyW9Doc($subsection->section->Vendor_ID);
                if ($w9) {
                    $count++;
                }
            }
        }


        return $count;
    }

    /**
     * Create Vendor's folder for project
     * @param $projectId
     * @param $vendorId
     * @param $year
     * @return int
     */
    public static function createVendorFolder($projectId, $vendorId, $year)
    {
        // get cabinet
        $cabinet = self::getCabinetForFolderCreate($projectId, $year);

        // get vendor info
        $vendor = Vendors::model()->with('client.company')->findByPk($vendorId);

        $folder = Sections::model()->findByAttributes(array(
            'Section_Type' => Storages::CABINET,
            'Created_By' => '0',
            'Storage_ID' => $cabinet->Storage_ID,
            'Vendor_ID' => $vendorId,
            'Folder_Cat_ID' => self::VENDOR_DOCUMENTS,
        ));

        if (!$folder) {
            $folder = new Sections();
            $folder->Storage_ID = $cabinet->Storage_ID;
            $folder->Section_Name = substr($vendor->client->company->Company_Name,0,50);
            $folder->Vendor_ID = $vendorId;
            $folder->Created_By = 0;
            $folder->Section_Type = Storages::CABINET;
            $folder->Folder_Cat_ID = self::VENDOR_DOCUMENTS;
            $folder->Access_Type = Storages::HAS_ACCESS;
            if ($folder->validate()) {
                $folder->save();

                $panel = new Subsections();
                $panel->Section_ID = $folder->Section_ID;
                $panel->Subsection_Name = 'Panel 1';
                $panel->Subsection_Type = Storages::CABINET;
                $panel->Created_By = 0;
                $panel->Access_Type = Storages::HAS_ACCESS;
                $panel->save();
            }
        } else {
            $panel = Subsections::model()->findByAttributes(array(
                'Subsection_Type' => Storages::CABINET,
                'Created_By' => '0',
                'Section_ID' => $folder->Section_ID,
            ));
        }

        return $panel->Subsection_ID;
    }


    public static function getPayrollFolder($projectID, $year, $weekEnding)
    {
        $weekEndingNum = preg_replace('/\-/', '', $weekEnding);

        // get cabinet
        $cabinet = self::getCabinetForFolderCreate($projectID, $year);

        $folder = Sections::model()->findByAttributes(array(
            'Section_Type' => Storages::CABINET,
            'Created_By' => '0',
            'Storage_ID' => $cabinet->Storage_ID,
            'Folder_Cat_ID' => self::PAYROLL,
            'Vendor_ID' => $weekEndingNum, // we use Vendor_ID to keep $weekEndingNum for this type of category
        ));

        return $folder;
    }

    public static function getPayrollSubsection($projectID, $year, $weekEnding)
    {
        $weekEndingNum = preg_replace('/\-/', '', $weekEnding);

        // get cabinet
        $cabinet = self::getCabinetForFolderCreate($projectID, $year);

        $folder = Sections::model()->findByAttributes(array(
            'Section_Type' => Storages::CABINET,
            'Created_By' => '0',
            'Storage_ID' => $cabinet->Storage_ID,
            'Folder_Cat_ID' => self::PAYROLL,
            'Vendor_ID' => $weekEndingNum, // we use Vendor_ID to keep $weekEndingNum for this type of category
        ));

        if ($folder) {

            $panel = Subsections::model()->findByAttributes(array(
                'Subsection_Type' => Storages::CABINET,
                'Created_By' => '0',
                'Section_ID' => $folder->Section_ID,
            ));
        }

        return $panel;
    }

    public static function getJeSubsection($projectID, $year, $weekEnding)
    {
        $weekEndingNum = preg_replace('/\-/', '', $weekEnding);
        $dateParts = explode('-', $weekEnding);
        $month = trim($dateParts[1], '0');
        // get cabinet
        $cabinet = self::getCabinetForFolderCreate($projectID, $year);

        $folder = Sections::model()->findByAttributes(array(
            'Section_Type' => Storages::CABINET,
            'Created_By' => '0',
            'Storage_ID' => $cabinet->Storage_ID,
            'Folder_Cat_ID' => self::JOURNAL_ENTRY,
            'Vendor_ID' => $month
        ));

        if ($folder) {

            $panel = Subsections::model()->findByAttributes(array(
                'Subsection_Type' => Storages::CABINET,
                'Created_By' => '0',
                'Section_ID' => $folder->Section_ID,
            ));
        }

        return $panel;
    }

    public static function getArSubsection($projectID, $year, $weekEnding)
    {
        $weekEndingNum = preg_replace('/\-/', '', $weekEnding);
        $dateParts = explode('-', $weekEnding);
        $month = trim($dateParts[1], '0');

        // get cabinet
        $cabinet = self::getCabinetForFolderCreate($projectID, $year);

        $folder = Sections::model()->findByAttributes(array(
            'Section_Type' => Storages::CABINET,
            'Created_By' => '0',
            'Storage_ID' => $cabinet->Storage_ID,
            'Folder_Cat_ID' => self::ACCOUNTS_RECEIVABLE,
            'Vendor_ID' => $month
        ));

        if ($folder) {

            $panel = Subsections::model()->findByAttributes(array(
                'Subsection_Type' => Storages::CABINET,
                'Created_By' => '0',
                'Section_ID' => $folder->Section_ID,
            ));
        }

        return $panel;
    }


    /**
     * Create Payroll category folder
     * @param $projectID
     * @param $year
     * @param $weekEnding
     * @return int
     */
    public static function createPayrollFolder($projectID, $year, $weekEnding)
    {
        $weekEndingNum = preg_replace('/\-/', '', $weekEnding);

        // get cabinet
        $cabinet = self::getCabinetForFolderCreate($projectID, $year);

        $folder = Sections::model()->findByAttributes(array(
            'Section_Type' => Storages::CABINET,
            'Created_By' => '0',
            'Storage_ID' => $cabinet->Storage_ID,
            'Folder_Cat_ID' => self::PAYROLL,
            'Vendor_ID' => $weekEndingNum, // we use Vendor_ID to keep $weekEndingNum for this type of category
        ));

        $project = Projects::model()->findByPk($projectID);

        if (!$folder) {
            $folder = new Sections();
            $folder->Storage_ID = $cabinet->Storage_ID;
            $folder->Section_Name = "WE " . Helper::convertDate($weekEnding);
            $folder->Vendor_ID = $weekEndingNum;
            $folder->Created_By = 0;
            $folder->Section_Type = Storages::CABINET;
            $folder->Folder_Cat_ID = self::PAYROLL;
            $folder->Access_Type = Storages::HAS_ACCESS;
            if ($folder->validate()) {
                $folder->save();

                $panel = new Subsections();
                $panel->Section_ID = $folder->Section_ID;
                $panel->Subsection_Name = 'Panel 1';
                $panel->Subsection_Type = Storages::CABINET;
                $panel->Created_By = 0;
                $panel->Access_Type = Storages::HAS_ACCESS;
                $panel->save();
            }
        } else {
            $panel = Subsections::model()->findByAttributes(array(
                'Subsection_Type' => Storages::CABINET,
                'Created_By' => '0',
                'Section_ID' => $folder->Section_ID,
            ));
        }

        return $panel->Subsection_ID;
    }


    /**
     * Create Petty Cash category folder
     * @param $projectID
     * @param $year
     * @param $employeeName
     * @return int
     */
    public static function createPettyCashFolder($projectID, $year, $employeeName)
    {

        // get cabinet
        $cabinet = self::getCabinetForFolderCreate($projectID, $year);

        $folder = Sections::model()->findByAttributes(array(
            'Section_Type' => Storages::CABINET,
            'Created_By' => '0',
            'Storage_ID' => $cabinet->Storage_ID,
            'Folder_Cat_ID' => self::PATTY_CASH,
            'Section_Name' => $employeeName,
        ));

        if (!$folder) {
            $folder = new Sections();
            $folder->Storage_ID = $cabinet->Storage_ID;
            $folder->Section_Name = $employeeName;
            $folder->Vendor_ID = 0;
            $folder->Created_By = 0;
            $folder->Section_Type = Storages::CABINET;
            $folder->Folder_Cat_ID = self::PATTY_CASH;
            $folder->Access_Type = Storages::HAS_ACCESS;
            if ($folder->validate()) {
                $folder->save();

                $panel = new Subsections();
                $panel->Section_ID = $folder->Section_ID;
                $panel->Subsection_Name = 'Panel 1';
                $panel->Subsection_Type = Storages::CABINET;
                $panel->Created_By = 0;
                $panel->Access_Type = Storages::HAS_ACCESS;
                $panel->save();
            }
        } else {
            $panel = Subsections::model()->findByAttributes(array(
                'Subsection_Type' => Storages::CABINET,
                'Created_By' => '0',
                'Section_ID' => $folder->Section_ID,
            ));
        }

        return $panel->Subsection_ID;
    }

    /**
     * Get Cabinet for folder Create
     * @param $projectID
     * @param $year
     * @return CActiveRecord
     */
    public static function getCabinetForFolderCreate($projectID, $year)
    {
        // get cabinet
        $cabinet = Storages::model()->findByAttributes(array(
            'Storage_Type' => Storages::CABINET,
            'Created_By' => '0',
            'Project_ID' => $projectID,
            'Year' => $year,
        ));

        if (!$cabinet) {
            Storages::createProjectStorages($projectID, $year);

            // get cabinet
            $cabinet = Storages::model()->findByAttributes(array(
                'Storage_Type' => Storages::CABINET,
                'Created_By' => '0',
                'Project_ID' => $projectID,
                'Year' => $year,
            ));
        }

        return $cabinet;
    }

    /**
     * Get Shelf for folder Create
     * @param $projectID
     * @param $year
     * @return CActiveRecord
     */
    public static function getShelfForBinderCreate($projectID, $year)
    {
        // get shelf
        $shelf = Storages::model()->findByAttributes(array(
            'Storage_Type' => Storages::SHELF,
            'Created_By' => '0',
            'Project_ID' => $projectID,
            'Year' => $year,
        ));

        if (!$shelf) {
            Storages::createProjectStorages($projectID, $year);

            // get shelf
            $shelf = Storages::model()->findByAttributes(array(
                'Storage_Type' => Storages::SHELF,
                'Created_By' => '0',
                'Project_ID' => $projectID,
                'Year' => $year,
            ));
        }

        return $shelf;
    }

    /**
     * Create Accounts Receivable category folder
     * @param $projectID
     * @param $year
     * @param $arDate
     * @return int
     */
    public static function createAccountsReceivableFolder($projectID, $year, $arDate)
    {
        $date = substr($arDate, 0, 10);
        $dateParts = explode('-', $date);
        $arDateNum = trim($dateParts[1], '0');

        // get cabinet
        $cabinet = self::getCabinetForFolderCreate($projectID, $year);

        $folder = Sections::model()->findByAttributes(array(
            'Section_Type' => Storages::CABINET,
            'Created_By' => '0',
            'Storage_ID' => $cabinet->Storage_ID,
            'Folder_Cat_ID' => self::ACCOUNTS_RECEIVABLE,
            'Vendor_ID' => $arDateNum, // we use Vendor_ID to keep $arDateNum for this type of category
        ));

        $project = Projects::model()->findByPk($projectID);

        if (!$folder) {
            $time = strtotime($arDate);

            $folder = new Sections();
            $folder->Storage_ID = $cabinet->Storage_ID;
            $folder->Section_Name = "P" . date('m', $time) . " " . date('M', $time);
            $folder->Vendor_ID = $arDateNum;
            $folder->Created_By = 0;
            $folder->Section_Type = Storages::CABINET;
            $folder->Folder_Cat_ID = self::ACCOUNTS_RECEIVABLE;
            $folder->Access_Type = Storages::HAS_ACCESS;
            if ($folder->validate()) {
                $folder->save();

                $panel = new Subsections();
                $panel->Section_ID = $folder->Section_ID;
                $panel->Subsection_Name = 'Panel 1';
                $panel->Subsection_Type = Storages::CABINET;
                $panel->Created_By = 0;
                $panel->Access_Type = Storages::HAS_ACCESS;
                $panel->save();
            }
        } else {
            $panel = Subsections::model()->findByAttributes(array(
                'Subsection_Type' => Storages::CABINET,
                'Created_By' => '0',
                'Section_ID' => $folder->Section_ID,
            ));
        }

        return $panel->Subsection_ID;
    }


    /**
     * Create Journal Entry category folder
     * @param $projectID
     * @param $date
     * @return int
     */
    public static function createJournalEntryFolder($projectID, $date)
    {
        $year = substr($date, 0, 4);
        $dateParts = explode('-', $date);
        $month = trim($dateParts[1], '0');

        // get cabinet
        $cabinet = self::getCabinetForFolderCreate($projectID, $year);

        $folder = Sections::model()->findByAttributes(array(
            'Section_Type' => Storages::CABINET,
            'Created_By' => '0',
            'Storage_ID' => $cabinet->Storage_ID,
            'Folder_Cat_ID' => self::JOURNAL_ENTRY,
            'Vendor_ID' => intval($month), // for this category we keep month in Vendor_ID field
        ));

        if (!$folder) {
            $folder = new Sections();
            $folder->Storage_ID = $cabinet->Storage_ID;
            //$folder->Section_Name = "JE of " . date('M Y', time($date));
            $folder->Section_Name = "JE of " . date('M Y', strtotime($date));
            $folder->Vendor_ID = intval($month);
            $folder->Created_By = 0;
            $folder->Section_Type = Storages::CABINET;
            $folder->Folder_Cat_ID = self::JOURNAL_ENTRY;
            $folder->Access_Type = Storages::HAS_ACCESS;
            if ($folder->validate()) {
                $folder->save();

                $panel = new Subsections();
                $panel->Section_ID = $folder->Section_ID;
                $panel->Subsection_Name = 'Panel 1';
                $panel->Subsection_Type = Storages::CABINET;
                $panel->Created_By = 0;
                $panel->Access_Type = Storages::HAS_ACCESS;
                $panel->save();
            }
        } else {
            $panel = Subsections::model()->findByAttributes(array(
                'Subsection_Type' => Storages::CABINET,
                'Created_By' => '0',
                'Section_ID' => $folder->Section_ID,
            ));
        }

        return $panel->Subsection_ID;
    }


    /**
     * Create PO Log or Check Log binder for project
     * @param $projectId
     * @param $docType
     * @param $year
     * @return int
     */
    public static function createLogBinder($projectId, $docType, $year)
    {
        // get shelf
        $shelf = self::getShelfForBinderCreate($projectId, $year);

        if ($docType == Documents::PM) {
            $folderCatID = self::CHECK_LOG;
        } else if ($docType == Documents::PO) {
            $folderCatID = self::PURCHASE_ORDER_LOG;
        }

        $binder = Sections::model()->findByAttributes(array(
            'Section_Type' => Storages::SHELF,
            'Created_By' => '0',
            'Storage_ID' => $shelf->Storage_ID,
            'Folder_Cat_ID' => $folderCatID,
        ));

        if (!$binder) {
            $binder = new Sections();
            $binder->Storage_ID = $shelf->Storage_ID;
            if ($docType == Documents::PM) {
                $binder->Section_Name = 'Payments';
            } else if ($docType == Documents::PO) {
                $binder->Section_Name = 'Purchase orders';
            }
            $binder->Vendor_ID = 0;
            $binder->Created_By = 0;
            $binder->Section_Type = Storages::SHELF;
            $binder->Folder_Cat_ID = $folderCatID;
            $binder->Access_Type = Storages::HAS_ACCESS;
            if ($binder->validate()) {
                $binder->save();

                $tab = new Subsections();
                $tab->Section_ID = $binder->Section_ID;
                $tab->Subsection_Name = 'Tab 1';
                $tab->Subsection_Type = Storages::SHELF;
                $tab->Created_By = 0;
                $tab->Access_Type = Storages::HAS_ACCESS;
                $tab->save();
            }
        } else {
            $tab = Subsections::model()->findByAttributes(array(
                'Subsection_Type' => Storages::SHELF,
                'Created_By' => '0',
                'Section_ID' => $binder->Section_ID,
            ));
        }

        return $tab->Subsection_ID;
    }

    /**
     * Delete section with subsections
     * @param $id
     */
    public static function deleteSection($id)
    {
        $section = Sections::model()->with('subsections')->findByPk($id);
        if ($section) {
            if (count($section->subsections)) {
                foreach ($section->subsections as $subsection) {
                    LibraryDocs::model()->deleteAllByAttributes(array(
                        'Subsection_ID' => $subsection->Subsection_ID,
                    ));
                    $subsection->delete();
                }
            }
            $section->delete();
        }
    }

    /**
     * Delete section without subsections
     * @param $id
     */
    public static function deleteSectionSimple($id)
    {
        $section = Sections::model()->findByPk($id);
        if ($section) {
            if (count($section->subsections)) {
                foreach ($section->subsections as $subsection) {
                    LibraryDocs::model()->deleteAllByAttributes(array(
                        'Subsection_ID' => $subsection->Subsection_ID,
                    ));
                    $subsection->delete();
                }
            }
            $section->delete();
        }
    }

    /**
     * Delete sub section
     * @param $id
     */
    public static function deleteSubsection($section_id,$subsection_id)
    {
        $subsection = Subsections::model()->findByAttributes(array(
            'Subsection_ID' =>$subsection_id
        ));

        if ($subsection) {
                    LibraryDocs::model()->deleteAllByAttributes(array(
                        'Subsection_ID' => $subsection->Subsection_ID,
                    ));

                    $subsection->delete();

        }

        //delete section if it is now have no panels
        $section = Sections::model()->findByPk($section_id);
        if (!count($section->subsections)) {
            $section->delete();
        }
    }

    /**
     * Check document Accordance To Section
     * @param $sectionID
     * @param $docId
     * @return int
     */
    public static function checkDocumentAccordanceToSection($sectionID, $docId)
    {
        $result = 0;
        $document = Documents::model()->findByPk($docId);
        $section = Sections::model()->with('folder_type')->findByPk($sectionID);

        if ($document && $section) {
            if (stripos($section->folder_type->Category_Doc_Types, $document->Document_Type) !== false) {
                $result = 1;
            }
        }

        return $result;
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

		$criteria->compare('Section_ID',$this->Section_ID);
		$criteria->compare('Storage_ID',$this->Storage_ID);
		$criteria->compare('Section_Name',$this->Section_Name,true);
		$criteria->compare('Created_By',$this->Created_By);
		$criteria->compare('Section_Type',$this->Section_Type);
		$criteria->compare('Folder_Cat_ID',$this->Folder_Cat_ID);
        $criteria->compare('Access_Type',$this->Access_Type);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Sections the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
