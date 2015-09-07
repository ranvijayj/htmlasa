<?php

/**
 * This is the model class for table "storages".
 *
 * The followings are the available columns in table 'storages':
 * @property integer $Storage_ID
 * @property string $Storage_Name
 * @property integer $Project_ID
 * @property integer $Client_ID
 * @property integer $Year
 * @property integer $Created_By
 * @property integer $Row_Num
 * @property integer $Storage_Type
 * @property integer $Access_Type
 */
class Storages extends CActiveRecord
{
    /**
     * Storage types
     */
    const CABINET = 0;
    const SHELF = 1;

    /**
     * Access types
     */
    const HAS_ACCESS = 1;
    const NO_ACCESS = 0;

    /**
     * Cabinet Drawers
     * @var array
     */
    static $drawers = array(
        Sections::VENDOR_DOCUMENTS => array(
            'title' => 'Vendor',
            'sections' => array(),
            'status' => 'closed',
            'selected' => false,
        ),
        Sections::PAYROLL => array(
            'title' => 'Payroll',
            'sections' => array(),
            'status' => 'closed',
            'selected' => false,
        ),
        Sections::PATTY_CASH => array(
            'title' => 'Petty Cash',
            'sections' => array(),
            'status' => 'closed',
            'selected' => false,
        ),
        Sections::ACCOUNTS_RECEIVABLE => array(
            'title' => 'Accounts Receivable',
            'sections' => array(),
            'status' => 'closed',
            'selected' => false,
        ),
        Sections::JOURNAL_ENTRY => array(
            'title' => 'Journal Entry',
            'sections' => array(),
            'status' => 'closed',
            'selected' => false,
        ),
        Sections::GENERAL => array(
            'title' => 'General',
            'sections' => array(),
            'status' => 'closed',
            'selected' => false,
        ),
    );

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'storages';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Storage_Name, Project_ID, Client_ID, Created_By, Year', 'required'),
			array('Project_ID, Client_ID, Created_By, Row_Num, Storage_Type, Access_Type', 'numerical', 'integerOnly'=>true),
			array('Storage_Name', 'length', 'max'=>50),
            array('Year', 'numerical'),
            array('Year', 'length', 'max'=>4),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Storage_ID, Storage_Name, Project_ID, Client_ID, Created_By, Row_Num, Storage_Type, Access_Type', 'safe', 'on'=>'search'),
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
            'clients'=>array(self::BELONGS_TO, 'Clients', 'Client_ID'),
            'projects'=>array(self::BELONGS_TO, 'Projects', 'Project_ID'),
            'user'=>array(self::BELONGS_TO, 'Users', 'Created_By'),
            'sections'=>array(self::HAS_MANY, 'Sections', 'Storage_ID'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'Storage_ID' => 'Storage',
			'Storage_Name' => 'Title',
			'Project_ID' => 'Project',
			'Client_ID' => 'Client',
            'Year' => 'Year',
			'Created_By' => 'Created By',
			'Row_Num' => 'Row Num',
			'Storage_Type' => 'Storage Type',
            'Access_Type' => 'Access Type',
		);
	}

    /**
     * Get all project or client cabinets or shelves
     * @param $clientId
     * @param $projectId
     * @param $storageType
     * @param $year
     * @param string $sortDirection
     * @return array
     */
    public static function getProjectStorages($clientId, $projectId, $storageType, $year, $sortDirection = 'ASC')
    {
        // craete condition
        $condition = new CDbCriteria();
        $condition->condition = "(t.Created_By = '" . Yii::app()->user->userID . "' OR t.Access_Type = '" . self::HAS_ACCESS . "')";
        $condition->addCondition("t.Client_ID = '" . $clientId . "'");
        $condition->addCondition("t.Year = '" . $year . "'");
        if ($projectId != 'all') {
            $condition->addCondition("t.Project_ID = '" . $projectId . "'");
        }

        if ($storageType == 'cabinets') {
            $condition->addCondition("t.Storage_Type = '" . self::CABINET . "'");
        } elseif ($storageType == 'shelves') {
            $condition->addCondition("t.Storage_Type = '" . self::SHELF . "'");
        }

        $condition->order = "t.Storage_Name $sortDirection, sections.Section_Name $sortDirection, subsections.Subsection_Name $sortDirection";

        // get storages
        $storages = Storages::model()->with('sections.subsections')->findAll($condition);

        if ($storages) {
            // build tree array
            $result = array();
            foreach ($storages as $storage) {
                $storageStatus = 'closed';
                $storageSelected = false;
                $sections = array();

                // if cabinets put sections to drawers
                if ($storageType == 'cabinets') {
                    $sections = self::$drawers;
                    if ($sortDirection == 'DESC') {
                        krsort($sections);
                    } else {
                        ksort($sections);
                    }
                }

                if ($storage->sections) {
                    foreach ($storage->sections as $section) {
                        if ($section->Access_Type == self::HAS_ACCESS || $section->Created_By == Yii::app()->user->userID) {
                            $subsections = array();
                            $sectionStatus = 'closed';
                            $sectionSelected = false;
                            if ($section->subsections) {
                                foreach ($section->subsections as $subsection) {
                                    if ($subsection->Access_Type == self::HAS_ACCESS || $subsection->Created_By == Yii::app()->user->userID) {
                                        $subsectionSelected = false;

                                        if ($_SESSION['selected_item']['rowType'] == 'subsection' && $_SESSION['selected_item']['id'] == $subsection->Subsection_ID) {
                                            $subsectionSelected = true;
                                            $sectionStatus = 'opened';
                                            $storageStatus = 'opened';

                                            if (isset($sections[$section->Folder_Cat_ID]['status'])) {
                                                $sections[$section->Folder_Cat_ID]['status'] = 'opened';
                                            }
                                        }

                                        $subsections[] = array(
                                            'name' => $subsection->Subsection_Name,
                                            'id' => $subsection->Subsection_ID,
                                            'selected' => $subsectionSelected,
                                            'created' => $subsection->Created_By,
                                        );
                                    }
                                }
                            }

                            if ($_SESSION['selected_item']['rowType'] == 'section' && $_SESSION['selected_item']['id'] == $section->Section_ID) {
                                $sectionSelected = true;
                                $storageStatus = 'opened';

                                if (isset($sections[$section->Folder_Cat_ID]['status'])) {
                                    $sections[$section->Folder_Cat_ID]['status'] = 'opened';
                                }
                            }

                            if ($storageType == 'cabinets') {
                                $sections[$section->Folder_Cat_ID]['sections'][] = array(
                                    'subsections' => $subsections,
                                    'name' => $section->Section_Name,
                                    'id' => $section->Section_ID,
                                    'type' => $section->folder_type->Full_Name,
                                    'status' => $sectionStatus,
                                    'selected' => $sectionSelected,
                                    'created' => $section->Created_By,
                                );
                            } else {
                                $sections[] = array(
                                    'subsections' => $subsections,
                                    'name' => $section->Section_Name,
                                    'id' => $section->Section_ID,
                                    'type' => $section->folder_type->Full_Name,
                                    'status' => $sectionStatus,
                                    'selected' => $sectionSelected,
                                    'created' => $section->Created_By,
                                );
                            }
                        }
                    }
                }

                if ($_SESSION['selected_item']['rowType'] == 'storage' && $_SESSION['selected_item']['id'] == $storage->Storage_ID) {
                    $storageSelected = true;
                }

                // unset empty drawers
                if ($storageType == 'cabinets') {
                    foreach ($sections as $key => $drawer) {
                        if (count($drawer['sections']) == 0) {
                            unset($sections[$key]);
                        }
                    }
                }

                $result[] = array(
                    'sections' => $sections,
                    'name' => $storage->Storage_Name,
                    'id' => $storage->Storage_ID,
                    'status' => $storageStatus,
                    'selected' => $storageSelected,
                    'created' => $storage->Created_By,
                );
            }
            $storages = $result;
        }
        return $storages;
    }

    /**
     * Get client or project storages by query string
     * @param $clientId
     * @param $projectId
     * @param $storageType
     * @param $queryString
     * @param $year
     * @param string $sortDirection
     * @return array|CActiveRecord|mixed|null
     */
    public static function getProjectStoragesBySearchQuery($clientId, $projectId, $storageType, $queryString, $year, $sortDirection = 'ASC')
    {
        // craete condition
        $condition = new CDbCriteria();

        $condition->compare('t.Storage_Name', $queryString, true, 'OR');
        $condition->compare('sections.Section_Name', $queryString, true, 'OR');
        $condition->compare('subsections.Subsection_Name', $queryString, true, 'OR');

        $condition->addCondition ("(t.Created_By = '" . Yii::app()->user->userID . "' OR t.Access_Type = '" . self::HAS_ACCESS . "')");
        $condition->addCondition ("t.Client_ID = '" . $clientId . "'");
        $condition->addCondition("t.Year = '" . $year . "'");
        if ($projectId != 'all') {
            $condition->addCondition("t.Project_ID = '" . $projectId . "'");
        }

        if ($storageType == 'cabinets') {
            $condition->addCondition("t.Storage_Type = '" . self::CABINET . "'");
        } elseif ($storageType == 'shelves') {
            $condition->addCondition("t.Storage_Type = '" . self::SHELF . "'");
        }

        $condition->order = "t.Storage_Name $sortDirection, sections.Section_Name $sortDirection, subsections.Subsection_Name $sortDirection";

        // get storages
        $storages = Storages::model()->with('sections.subsections')->findAll($condition);

        if ($storages) {
            // build tree array
            $result = array();
            foreach ($storages as $storage) {
                $storageStatus = 'closed';
                $storageSelected = false;
                $sections = array();

                // if cabinets put sections to drawers
                if ($storageType == 'cabinets') {
                    $sections = self::$drawers;
                    if ($sortDirection == 'DESC') {
                        krsort($sections);
                    } else {
                        ksort($sections);
                    }
                }

                if ($storage->sections) {
                    foreach ($storage->sections as $section) {
                        if ($section->Access_Type == self::HAS_ACCESS || $section->Created_By == Yii::app()->user->userID) {
                            $subsections = array();
                            $sectionStatus = 'closed';
                            $sectionSelected = false;
                            if ($section->subsections) {
                                foreach ($section->subsections as $subsection) {
                                    if ($subsection->Access_Type == self::HAS_ACCESS || $subsection->Created_By == Yii::app()->user->userID) {
                                        $subsectionSelected = false;

                                        if (stripos($subsection->Subsection_Name, $queryString) !== false) {
                                            $subsectionSelected = true;
                                            $sectionStatus = 'opened';
                                            $storageStatus = 'opened';

                                            if (isset($sections[$section->Folder_Cat_ID]['status'])) {
                                                $sections[$section->Folder_Cat_ID]['status'] = 'opened';
                                            }
                                        }

                                        $subsections[] = array(
                                            'name' => $subsection->Subsection_Name,
                                            'id' => $subsection->Subsection_ID,
                                            'selected' => $subsectionSelected,
                                            'created' => $subsection->Created_By,
                                        );
                                    }
                                }
                            }

                            if (stripos($section->Section_Name, $queryString) !== false) {
                                $sectionSelected = true;
                                $storageStatus = 'opened';

                                if (isset($sections[$section->Folder_Cat_ID]['status'])) {
                                    $sections[$section->Folder_Cat_ID]['status'] = 'opened';
                                }
                            }

                            if ($storageType == 'cabinets') {
                                $sections[$section->Folder_Cat_ID]['sections'][] = array(
                                    'subsections' => $subsections,
                                    'name' => $section->Section_Name,
                                    'id' => $section->Section_ID,
                                    'type' => $section->folder_type->Full_Name,
                                    'status' => $sectionStatus,
                                    'selected' => $sectionSelected,
                                    'created' => $section->Created_By,
                                );
                            } else {
                                $sections[] = array(
                                    'subsections' => $subsections,
                                    'name' => $section->Section_Name,
                                    'id' => $section->Section_ID,
                                    'type' => $section->folder_type->Full_Name,
                                    'status' => $sectionStatus,
                                    'selected' => $sectionSelected,
                                    'created' => $section->Created_By,
                                );
                            }
                        }
                    }
                }

                if (stripos($storage->Storage_Name, $queryString) !== false) {
                    $storageSelected = true;
                }

                // unset empty drawers
                if ($storageType == 'cabinets') {
                    foreach ($sections as $key => $drawer) {
                        if (count($drawer['sections']) == 0) {
                            unset($sections[$key]);
                        } else {
                            if (stripos($drawer['title'], $queryString) !== false) {
                                $sections[$key]['selected'] = true;
                            }
                        }
                    }
                }

                $result[] = array(
                    'sections' => $sections,
                    'name' => $storage->Storage_Name,
                    'id' => $storage->Storage_ID,
                    'status' => $storageStatus,
                    'selected' => $storageSelected,
                    'created' => $storage->Created_By,
                );
            }
            $storages = $result;
        }
        return $storages;
    }

    /**
     * Create project shelve and cabinet
     * @param $projectId
     * @param $year
     */
    public static function createProjectStorages($projectId, $year)
    {
        $project = Projects::model()->findByPk($projectId);

        // check existing project cabinet
        $cabinet = Storages::model()->findByAttributes(array(
            'Storage_Type' => self::CABINET,
            'Created_By' => '0',
            'Project_ID' => $projectId,
            'Year' => $year,
        ));
        if (!$cabinet) {
            // if there is not cabinet for this project create it
            $cabinet = new Storages();
            $cabinet->Storage_Name = $project->Project_Name;
            $cabinet->Project_ID = $projectId;
            $cabinet->Client_ID = Yii::app()->user->clientID;
            $cabinet->Year = $year;
            $cabinet->Created_By = 0;
            $cabinet->Row_Num = 0;
            $cabinet->Storage_Type = self::CABINET;
            $cabinet->Access_Type = self::HAS_ACCESS;
            if ($cabinet->validate()) {
                $cabinet->save();
            }
        }

        // check existing project shelf
        $shelf = Storages::model()->findByAttributes(array(
            'Storage_Type' => self::SHELF,
            'Created_By' => '0',
            'Project_ID' => $projectId,
            'Year' => $year,
        ));
        if (!$shelf) {
            // if there is not cabinet for this project create it
            $shelf = new Storages();
            $shelf->Storage_Name = $project->Project_Name;
            $shelf->Project_ID = $projectId;
            $shelf->Client_ID = Yii::app()->user->clientID;
            $shelf->Year = $year;
            $shelf->Created_By = 0;
            $shelf->Row_Num = 0;
            $shelf->Storage_Type = self::SHELF;
            $shelf->Access_Type = self::HAS_ACCESS;
            if ($shelf->validate()) {
                $shelf->save();
            }
        }

        $countW9s = W9::getCountOfAvailableW9sOfYear($year);
        if ($countW9s > 0) {
            // also crete W9 book if it does not exist and there are Company's W9s
            $w9Binder = Sections::model()->findByAttributes(array(
                'Section_Type' => self::SHELF,
                'Created_By' => '0',
                'Storage_ID' => $shelf->Storage_ID,
                'Folder_Cat_ID' => Sections::W9_BOOK,
            ));

            if (!$w9Binder) {
                $w9Binder = new Sections();
                $w9Binder->Storage_ID = $shelf->Storage_ID;
                $w9Binder->Section_Name = "W9s";
                $w9Binder->Vendor_ID = 0;
                $w9Binder->Created_By = 0;
                $w9Binder->Section_Type = self::SHELF;
                $w9Binder->Folder_Cat_ID = Sections::W9_BOOK;
                $w9Binder->Access_Type = self::HAS_ACCESS;
                if ($w9Binder->validate()) {
                    $w9Binder->save();

                    $tab = new Subsections();
                    $tab->Section_ID = $w9Binder->Section_ID;
                    $tab->Subsection_Name = 'Tab 1';
                    $tab->Subsection_Type = self::SHELF;
                    $tab->Created_By = 0;
                    $tab->Access_Type = self::HAS_ACCESS;
                    $tab->save();
                }
            }
        }
    }


    /**
     * Get storages list by type and parent storage id
     * @param $clientId
     * @param $projectId
     * @param $storageType
     * @param $parentId
     * @param string $year
     * @return array
     */
    public static function getStoragesList($clientId, $projectId, $storageType, $parentId, $year = '')
    {
        $result = array();
        if ($storageType == 'storages') {
            // create condition
            $condition = new CDbCriteria();
            $condition->condition = "(t.Created_By = '" . Yii::app()->user->userID . "' OR t.Access_Type = '" . self::HAS_ACCESS . "')";
            $condition->addCondition("t.Client_ID = '" . $clientId . "'");
            $condition->addCondition("t.Year = '" . $year . "'");
            if ($projectId != 'all') {
                $condition->addCondition("t.Project_ID = '" . $projectId . "'");
            }

            if ($parentId == 1) {
                $condition->addCondition("t.Storage_Type = '" . self::CABINET . "'");
            } elseif ($parentId == 2) {
                $condition->addCondition("t.Storage_Type = '" . self::SHELF . "'");
            }

            $condition->order = "t.Storage_Name ASC";

            // get storages
            $storages = Storages::model()->findAll($condition);

            if ($storages) {
                foreach($storages as $storage) {
                    $result[$storage->Storage_ID] = $storage->Storage_Name;
                }
            }
        } elseif ($storageType == 'sections') {
            // create condition
            $condition = new CDbCriteria();
            $condition->condition = "(t.Created_By = '" . Yii::app()->user->userID . "' OR t.Access_Type = '" . self::HAS_ACCESS . "')";
            $condition->addCondition("t.Storage_ID = '" . $parentId . "'");
            $condition->order = "t.Section_Name ASC";

            // get storages
            $sections = Sections::model()->findAll($condition);

            $storage = Storages::model()->findByPk($parentId);

            if ($sections) {
                if ($storage->Storage_Type == self::CABINET) {
                    $result = self::$drawers;
                    foreach($sections as $section) {
                        $result[$section->Folder_Cat_ID]['sections'][$section->Section_ID] = $section->Section_Name;
                    }

                    foreach ($result as $key => $drawer) {
                        if (count($drawer['sections']) == 0) {
                            unset($result[$key]);
                        }
                    }
                } else {
                    foreach($sections as $section) {
                        $result[$section->Section_ID] = $section->Section_Name;
                    }
                }
            }
        } elseif ($storageType == 'subsections') {
            // create condition
            $condition = new CDbCriteria();
            $condition->condition = "(t.Created_By = '" . Yii::app()->user->userID . "' OR t.Access_Type = '" . self::HAS_ACCESS . "')";
            $condition->addCondition("t.Section_ID = '" . $parentId . "'");
            $condition->order = "t.Subsection_Name ASC";

            // get storages
            $subsections = Subsections::model()->findAll($condition);

            if ($subsections) {
                foreach($subsections as $subsection) {
                    $result[$subsection->Subsection_ID] = $subsection->Subsection_Name;
                }
            }
        }
        return $result;
    }

    /**
     * Check existing of Cabinets and shelves of user's projects
     * @param $year
     */
    public static function checkLibraryStoragesForProjects($year)
    {
        if (Yii::app()->user->projectID == 'all') {
            $projects = Projects::model()->findAllByAttributes(array(
                'Client_ID' => Yii::app()->user->clientID,
            ));

            if ($projects) {
                foreach ($projects as $project) {
                    Storages::createProjectStorages($project->Project_ID, $year);
                }
            }
        } else {
             Storages::createProjectStorages(Yii::app()->user->projectID, $year);
        }
    }

    /**
     * Check user's access to storage
     * @param $id
     * @param $rowType
     * @return bool
     */
    public static function hasAccess($id, $rowType)
    {
        $id = intval($id);
        if ($rowType == 'storage') {
            $condition = new CDbCriteria();
            $condition->condition = "t.Storage_ID = '" . $id . "'";
            $condition->addCondition("(t.Created_By = '" . Yii::app()->user->userID . "' OR t.Access_Type = '" . self::HAS_ACCESS . "')");
            $condition->addCondition("t.Client_ID = '" . Yii::app()->user->clientID . "'");
            if (Yii::app()->user->projectID != 'all') {
                $condition->addCondition("t.Project_ID = '" . Yii::app()->user->projectID . "'");
            }
            $storage = Storages::model()->find($condition);
            if ($storage) {
                return true;
            }
        } else if ($rowType == 'section') {
            $condition = new CDbCriteria();
            $condition->join = "LEFT JOIN storages ON storages.Storage_ID = t.Storage_ID";
            $condition->condition = "t.Section_ID = '" . $id . "'";
            $condition->addCondition("(storages.Created_By = '" . Yii::app()->user->userID . "' OR storages.Access_Type = '" . self::HAS_ACCESS . "')");
            $condition->addCondition("(t.Created_By = '" . Yii::app()->user->userID . "' OR t.Access_Type = '1')");
            $condition->addCondition("storages.Client_ID = '" . Yii::app()->user->clientID . "'");
            if (Yii::app()->user->projectID != 'all') {
                $condition->addCondition("storages.Project_ID = '" . Yii::app()->user->projectID . "'");
            }

            $section = Sections::model()->find($condition);

            if ($section) {
                return true;
            }
        } else if ($rowType == 'subsection') {
            $condition = new CDbCriteria();
            $condition->join = "LEFT JOIN sections ON sections.Section_ID = t.Section_ID
                                LEFT JOIN storages ON storages.Storage_ID = sections.Storage_ID";
            $condition->condition = "t.Subsection_ID = '" . $id . "'";
            $condition->addCondition("(storages.Created_By = '" . Yii::app()->user->userID . "' OR storages.Access_Type = '" . self::HAS_ACCESS . "')");
            $condition->addCondition("(sections.Created_By = '" . Yii::app()->user->userID . "' OR sections.Access_Type = '" . self::HAS_ACCESS . "')");
            $condition->addCondition("(t.Created_By = '" . Yii::app()->user->userID . "' OR t.Access_Type = '" . self::HAS_ACCESS . "')");
            $condition->addCondition("storages.Client_ID = '" . Yii::app()->user->clientID . "'");
            if (Yii::app()->user->projectID != 'all') {
                $condition->addCondition("storages.Project_ID = '" . Yii::app()->user->projectID . "'");
            }

            $subsection = Subsections::model()->find($condition);

            if ($subsection) {
                return true;
            }
        }

        return false;
    }

    /**
     * Delete Storage with sections and subsections
     * @param $id
     */
    public static function deleteStorage($id)
    {
        $storage = Storages::model()->with('sections.subsections')->findByPk($id);
        if ($storage) {
            if (count($storage->sections) > 0) {
                foreach($storage->sections as $section) {
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
            $storage->delete();
        }
    }

    /**
     * Get years list
     * @param $clientID
     * @param $projectID
     * @return CActiveRecord[]
     */
    public static function getYearsList($clientID, $projectID)
    {
        // create condition
        $condition = new CDbCriteria();
        $condition->select = 'distinct Year';
        $condition->condition = "(t.Created_By = '" . Yii::app()->user->userID . "' OR t.Access_Type = '" . self::HAS_ACCESS . "')";
        $condition->addCondition("t.Client_ID = '" . $clientID . "'");
        if ($projectID != 'all') {
            $condition->addCondition("t.Project_ID = '" . $projectID . "'");
        }
        $condition->order = "t.Year DESC";

        // get storages
        $years = Storages::model()->findAll($condition);
        return $years;
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

		$criteria->compare('Storage_ID',$this->Storage_ID);
		$criteria->compare('Storage_Name',$this->Storage_Name,true);
		$criteria->compare('Project_ID',$this->Project_ID);
		$criteria->compare('Client_ID',$this->Client_ID);
		$criteria->compare('Created_By',$this->Created_By);
		$criteria->compare('Row_Num',$this->Row_Num);
		$criteria->compare('Storage_Type',$this->Storage_Type);
        $criteria->compare('Access_Type',$this->Access_Type);
        $criteria->compare('Year',$this->Year);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Storages the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public static function setActiveItem($active_item){
        $_SESSION['selected_item']['rowType'] = 'section'
        & $_SESSION['selected_item']['id'] = $active_item;
    }
}
