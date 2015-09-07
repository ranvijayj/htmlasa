<?php

/**
 * This is the model class for table "coa".
 *
 * The followings are the available columns in table 'coa':
 * @property integer $COA_ID
 * @property integer $Client_ID
 * @property string $COA_Acct_Number
 * @property integer $Project_ID
 * @property string $COA_Name
 * @property integer $COA_Class_ID
 * @property string $COA_Budget
 */
class Coa extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'coa';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Client_ID, COA_Acct_Number, Project_ID, COA_Budget', 'required'),
			array('Client_ID, Project_ID, COA_Class_ID', 'numerical', 'integerOnly'=>true),
            array('COA_Budget, COA_Current_Total', 'numerical'),
			array('COA_Acct_Number', 'length', 'max'=>63),
			array('COA_Name', 'length', 'max'=>70),
			array('COA_Budget, COA_Current_Total', 'length', 'max'=>13),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('COA_ID, Client_ID, COA_Acct_Number, Project_ID, COA_Name, COA_Class_ID, COA_Budget, COA_Current_Total', 'safe', 'on'=>'search'),
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
            'class' => array(self::BELONGS_TO, 'CoaClass', 'COA_Class_ID'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'COA_ID' => 'Coa',
			'Client_ID' => 'Client',
			'COA_Acct_Number' => 'Coa Acct Number',
			'Project_ID' => 'Project',
			'COA_Name' => 'Coa Name',
			'COA_Class_ID' => 'Coa Class',
			'COA_Budget' => 'Coa Budget',
            'COA_Current_Total' => 'COA Current Total',
		);
	}

    /**
     * On save event
     * @return bool
     */
    protected function beforeSave () {
        if (isset($this->COA_Budget) && $this->COA_Budget == '') {
            $this->COA_Budget = 0;
        }
        return parent::beforeSave();
    }

    /**
     * Checks Project (If Project == all then redirect on previous page)
     */
    public static function checkProject()
    {
        if (Yii::app()->user->projectID == 'all') {
            Yii::app()->user->setFlash('success', "Please select a specific Project. This function cannot be executed on All Projects.");
            Yii::app()->controller->redirect((isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] && (strpos($_SERVER['HTTP_REFERER'], '/coa') === false)) ? $_SERVER['HTTP_REFERER'] : '/');
            Yii::app()->end();
        }
    }

    /**
     * Get clients COAs
     * @param $clientID
     * @param $projectID
     * @param $coaDefaultClass
     * @param string $sortBy
     * @return Coa[]
     */
    public static function getClientsCOAs($clientID, $projectID, $coaDefaultClass = null, $sortBy = 'class.Class_Sort_Order, t.COA_Acct_Number ASC')
    {
        $clientID = intval($clientID);
        $projectID = trim($projectID);

        //condition for def class
        $condition = new CDbCriteria();
        $condition->condition = "t.Client_ID = '" . $clientID . "'";
        if (isset($coaDefaultClass->COA_Class_ID)) {
            $condition->addCondition("t.COA_Class_ID = '" . $coaDefaultClass->COA_Class_ID . "'");
        }
        if ($projectID !== 'all') {
            $condition->addCondition("t.Project_ID = '" . intval($projectID) . "'");
        }
        $condition->order = $sortBy;

        //for other coas (not default)
        /*
        $condition2 = new CDbCriteria();
        $condition2->condition = "t.Client_ID = '" . $clientID . "'";

        if (isset($coaDefaultClass->COA_Class_ID)) {
            $condition2->addCondition("t.COA_Class_ID != '" . $coaDefaultClass->COA_Class_ID . "'");
        }
        if ($projectID !== 'all') {
            $condition2->addCondition("t.Project_ID = '" . intval($projectID) . "'");
        }


        $condition2->order = $sortBy;



        $COAs2 = Coa::model()->with('class')->findAll($condition2);


        return array_merge($COAs,$COAs2);*/
        $COAs = Coa::model()->with('class')->findAll($condition);
        return $COAs;
    }

    /**
     * Get Project's COA structure rules (create COA structure with default values if it is not set)
     * @param $projectID
     * @return CoaStructure
     */
    public static function getProjectCoaStructure($projectID)
    {
        $projectID = intval($projectID);
        $coaStructure = CoaStructure::model()->findByAttributes(array(
            'Project_ID' => $projectID,
        ));
        if ($coaStructure === null) {
            $coaStructure = new CoaStructure();
            $coaStructure->Project_ID = $projectID;
            $coaStructure->COA_Head = '';
            $coaStructure->COA_Root = '';
            $coaStructure->COA_Tail = '';
            if ($coaStructure->validate()) {
                $coaStructure->save();
            }
        }
        return $coaStructure;
    }

    /**
     * Copy COA structure from another project
     * @param $projectID
     * @param $projectFromCopyId
     * @return CoaStructure
     */
    public static function copyProjectCoaStructure($project_from_id, $project_to_id)
    {
        $project_from_id = intval($project_from_id);
        $project_to_id = intval($project_to_id);

        $coaStructure = CoaStructure::model()->findByAttributes(array(
            'Project_ID' => $project_from_id,
        ));

        $coaStructureTo = CoaStructure::model()->findByAttributes(array(
            'Project_ID' => $project_to_id,
        ));
        if (!$coaStructureTo) {
            $coaStructureTo = new CoaStructure();
        }


        if ($coaStructure !== null && $coaStructureTo !== null) {
            $coaStructureTo = clone $coaStructure;
            $coaStructureTo->Project_ID = $project_to_id;
            $coaStructureTo->setPrimaryKey($project_to_id);
            if ($coaStructureTo->validate()) {
                $coaStructureTo->save();

                Projects::updateCoaParams($project_to_id, $coaStructure->COA_Allow_Manual_Coding, $coaStructure->COA_Break_Character, $coaStructure->COA_Break_Number);
            }
        }

        return $coaStructureTo;
    }

    /**
     * Get Project's default COA class
     * @param $projectID
     * @return CoaClass
     */
    public static function getProjectCoaDefaultClass($projectID)
    {
        $projectID = intval($projectID);
        $coaDefaultClass = CoaClass::model()->findByAttributes(array(
            'Project_ID' => $projectID,
            'Class_Default' => CoaClass::DEFAULT_CLASS,
        ));
        if ($coaDefaultClass == null) {
            $coaClasses = Coa::getProjectCoaClasses($projectID);
            $coaDefaultClass = $coaClasses[0];
            $coaDefaultClass->Class_Default = CoaClass::DEFAULT_CLASS;
            if ($coaDefaultClass->validate()) {
                $coaDefaultClass->save();
            }
        }

        return $coaDefaultClass;
    }

    /**
     * Update Project's default COA class
     * @param $projectID
     * @param $defaultCoaClass
     * @return CoaClass
     */
    public static function updateCoaDefaultClass($projectID, $defaultCoaClass)
    {
        $projectID = intval($projectID);
        $defaultCoaClass = intval($defaultCoaClass);

        $condition = new CDbCriteria();
        $condition->condition = "Project_ID = '" . $projectID . "'";
        CoaClass::model()->updateAll(array(
            'Class_Default' => CoaClass::NOT_DEFAULT_CLASS,
        ), $condition);

        $condition->addCondition("COA_Class_ID = '" . $defaultCoaClass . "'");
        $coaClass = CoaClass::model()->find($condition);
        if ($coaClass) {
            $coaClass->Class_Default = CoaClass::DEFAULT_CLASS;
            if ($coaClass->validate()) {
                $coaClass->save();
            }
        }

        $coaDefaultClass = Coa::getProjectCoaDefaultClass($projectID);
        return $coaDefaultClass;
    }

    /**
     * Get Project's COA classes (create standard COA classes if they are not set)
     * @param $projectID
     * @return CoaClass[]
     */
    public static function getProjectCoaClasses($projectID)
    {
        $standartClasses = array(
            //0 => array('Class_Shortcut' => '', 'Class_Name' => 'Select class', 'Class_Default' => 404),
            1 => array('Class_Shortcut' => 'AST', 'Class_Name' => 'Asset', 'Class_Default' => CoaClass::DEFAULT_CLASS),
            2 => array('Class_Shortcut' => 'LIB', 'Class_Name' => 'Liability', 'Class_Default' => CoaClass::NOT_DEFAULT_CLASS),
            3 => array('Class_Shortcut' => 'CAP', 'Class_Name' => 'Capital', 'Class_Default' => CoaClass::NOT_DEFAULT_CLASS),
            4 => array('Class_Shortcut' => 'INC', 'Class_Name' => 'Income', 'Class_Default' => CoaClass::NOT_DEFAULT_CLASS),
            5 => array('Class_Shortcut' => 'COG', 'Class_Name' => 'Cost Of Goods Sold', 'Class_Default' => CoaClass::NOT_DEFAULT_CLASS),
            6 => array('Class_Shortcut' => 'EXP', 'Class_Name' => 'Expense', 'Class_Default' => CoaClass::NOT_DEFAULT_CLASS),
            7 => array('Class_Shortcut' => 'ATL', 'Class_Name' => 'Above The Line', 'Class_Default' => CoaClass::NOT_DEFAULT_CLASS),
            8 => array('Class_Shortcut' => 'BTL', 'Class_Name' => 'Below The Line', 'Class_Default' => CoaClass::NOT_DEFAULT_CLASS),
            9 => array('Class_Shortcut' => 'PST', 'Class_Name' => 'Post', 'Class_Default' => CoaClass::NOT_DEFAULT_CLASS),
            10 => array('Class_Shortcut' => 'OTH', 'Class_Name' => 'Other', 'Class_Default' => CoaClass::NOT_DEFAULT_CLASS),
        );

        $projectID = intval($projectID);
        $condition = new CDbCriteria();
        $condition->condition = "t.Project_ID = '" . $projectID . "'";
        $condition->order = "t.Class_Sort_Order ASC, t.Class_Name ASC";
        $coaClasses = CoaClass::model()->findAll($condition);

        if (count($coaClasses) == 0) {
            foreach ($standartClasses as $num => $standartClass) {
                $coaClass = new CoaClass();
                $coaClass->Project_ID = $projectID;
                $coaClass->Class_Shortcut = $standartClass['Class_Shortcut'];
                $coaClass->Class_Name = $standartClass['Class_Name'];
                $coaClass->Class_Sort_Order = $num;
                $coaClass->Class_Default = $standartClass['Class_Default'];
                if ($coaClass->validate()) {
                    $coaClass->save();
                }
            }

            $coaClasses = CoaClass::model()->findAll($condition);
        }

        return $coaClasses;
    }

    /**
     * Update Project's COA classes
     * @param $projectID
     * @param $classesToUpdate
     * @param $coaClasses
     * @return CoaClass[]
     */
    public static function updateCoaClasses($projectID, $classesToUpdate, $coaClasses)
    {
        $projectID = intval($projectID);
        $defaultClass = null;
        foreach ($classesToUpdate as $num => $classToUpdate) {
            if ($classToUpdate['COA_Class_ID'] == 'new_item') {
                $newCoaClass = new CoaClass();
                $newCoaClass->Class_Sort_Order = $num;
                $newCoaClass->Class_Shortcut = $classToUpdate['Class_Shortcut'];
                $newCoaClass->Class_Name = $classToUpdate['Class_Name'];
                $newCoaClass->Project_ID = $projectID;
                if ($newCoaClass->validate()) {
                    $newCoaClass->save();
                }

                if (!$defaultClass) {
                    $defaultClass = $newCoaClass;
                }
            } else if (is_numeric($classToUpdate['COA_Class_ID'])) {
                foreach ($coaClasses as $key => $coaClass) {
                    if ($classToUpdate['COA_Class_ID'] == $coaClass->COA_Class_ID) {
                        if ($classToUpdate['Class_Sort_Order'] != '') {
                            $coaClass->Class_Sort_Order = $classToUpdate['Class_Sort_Order'];
                        }

                        if (strlen($classToUpdate['Class_Shortcut']) > 0 && strlen($classToUpdate['Class_Shortcut']) <= 3) {
                            $coaClass->Class_Shortcut = $classToUpdate['Class_Shortcut'];
                        }

                        $coaClass->Class_Name = $classToUpdate['Class_Name'];

                        if ($coaClass->validate()) {
                            $coaClass->save();
                        }

                        if (!$defaultClass) {
                            $defaultClass = $coaClasses[$key];
                        }
                        unset($coaClasses[$key]);
                    }
                }
            }


        }

        foreach ($coaClasses as $coaClass) {
            $condition = new CDbCriteria();
            $condition->condition = "Project_ID = '" . $projectID . "'";
            $condition->addCondition("COA_Class_ID = '" . $coaClass->COA_Class_ID . "'");
            Coa::model()->updateAll(array(
                'COA_Class_ID' => $defaultClass->COA_Class_ID,
            ), $condition);
            $coaClass->delete();
        }

        $coaClasses = Coa::getProjectCoaClasses($projectID);
        return $coaClasses;
    }

    /**
     * Copy COA classes from another project
     * @param $projectID
     * @param $projectFromCopyId
     * @param $countClasses
     * @return CoaClass[]
     */
    public static function copyProjectCoaClasses($project_id, $project_copy_to_id, $countClasses)
    {
        $project_id = intval($project_id);
        $project_copy_to_id = intval($project_copy_to_id);
        $classesToCopy = Coa::getProjectCoaClasses($project_id);
        foreach ($classesToCopy as $classToCopy) {
            $newCoaClass = CoaClass::model()->findByAttributes(array(
                'Project_ID' => $project_copy_to_id,
                'Class_Shortcut' => $classToCopy->Class_Shortcut,
            ));

            if ($newCoaClass === null) {
                $countClasses++;
                $newCoaClass = new CoaClass();
                $newCoaClass->Class_Sort_Order = $countClasses;
            }

            $newCoaClass->Class_Shortcut = $classToCopy->Class_Shortcut;
            $newCoaClass->Class_Name = $classToCopy->Class_Name;
            $newCoaClass->Project_ID = $project_copy_to_id;
            if ($newCoaClass->validate()) {
                $newCoaClass->save();
            }
        }

        $coaClasses = Coa::getProjectCoaClasses($project_copy_to_id);
        return $coaClasses;
    }

    /**
     * Import Project's COA classes
     * @param $projectID
     * @param $classesToImport
     * @return CoaClass[]
     */
    public static function importCoaClasses($projectID, $classesToImport)
    {
        /*$all_amount=count($classesToImport);
        $i=0;

        $pb= ProgressBar::init();
        $pb->step(5);*/


        $projectID = intval($projectID);
        foreach ($classesToImport as $num => $classToImport) {

            $newCoaClass = CoaClass::model()->findByAttributes(array(
                'Project_ID' => $projectID,
                'Class_Shortcut' => $classToImport['Class_Shortcut'],
            ));

            if ($newCoaClass === null) {
                $newCoaClass = new CoaClass();
            }

            $newCoaClass->Class_Sort_Order = $num;
            $newCoaClass->Class_Shortcut = $classToImport['Class_Shortcut'];
            $newCoaClass->Class_Name = $classToImport['Class_Name'];
            $newCoaClass->Project_ID = $projectID;
            if ($newCoaClass->validate()) {
                $newCoaClass->save();
            }

        }

        $coaClasses = Coa::getProjectCoaClasses($projectID);
        return $coaClasses;
    }

    /**
     * Parse Imported Excel
     * @param $filepath
     * @return array
     */
    public static function parseImportExcel($filepath)
    {
        spl_autoload_unregister(array('YiiBase','autoload'));
        Yii::import("ext.phpexcel.Classes.PHPExcel.IOFactory", true);

        /*
        $objPHPExcel = PHPExcel_IOFactory::load($filepath);
        $objPHPExcel->setActiveSheetIndex(0);
        $aSheet = $objPHPExcel->getActiveSheet();

        $array = array();
        foreach($aSheet->getRowIterator() as $row){
            $cellIterator = $row->getCellIterator();
            $item = array();
            foreach($cellIterator as $cell) {
                array_push($item, $cell->getCalculatedValue());
            }
            array_push($array, $item);
        }*/

        try {

            //$inputFileType='Excel5';
            //$objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = PHPExcel_IOFactory::load($filepath);
            $array = array();

            //File type identification
            //$inputFileType = PHPExcel_IOFactory::identify($filepath);
            //die( 'File '.pathinfo($filepath,PATHINFO_BASENAME).' has been identified as an '.$inputFileType.' file<br />');

          //$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
          //var_dump($sheetData);die;

            $objWorksheet = $objPHPExcel->getSheet(0);
            $highestRow = $objWorksheet->getHighestRow();
            //var_dump($highestRow);
            $highestColumn = $objWorksheet->getHighestColumn();

            $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
            $highestColumnIndex = 4;
            //var_dump($highestColumnIndex);

            //$info= $objWorksheet->
            //var_dump($info);die;
            /*echo $highestColumn;
            echo "<br>";
            echo $highestRow;
            die;*/

            for ($row = 1; $row <= $highestRow; ++$row) {
                $item = array();
                for ($col = 0; $col <= $highestColumnIndex; ++$col) {
                    $data = $objWorksheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
                    array_push($item, $data);
                }
                array_push($array, $item);
            }


            unset($objWorksheet);
            $objPHPExcel->disconnectWorksheets();
            unset($objPHPExcel);
        } catch (Exception $e) {
            return 'Caught exception: '.$e->getMessage();

        }

        spl_autoload_register(array('YiiBase','autoload'));
        //var_dump($array);die;
        return $array;
    }

    /**
     * Export COAs to Excel
     * @param $COAs
     */
    public static function exportCOAs($COAs)
    {
        $project = Projects::model()->findByPk(Yii::app()->user->projectID);
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel.Classes');

        spl_autoload_unregister(array('YiiBase','autoload'));

        include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $phpexcel = new PHPExcel();
        $page = $phpexcel->setActiveSheetIndex(0);
        /*$page->setCellValue("A1", "Class");
        $page->setCellValue("B1", "Description");
        $page->setCellValue("C1", "Account Number");
        $page->setCellValue("D1", "Budget");*/

        $page->setCellValue("A1", "Description");
        $page->setCellValue("B1", "Account Number");
        $page->setCellValue("C1", "Class");
        $page->setCellValue("D1", "Budget");


        $page->getStyle('A1')->getFont()->setBold(true);
        $page->getStyle('B1')->getFont()->setBold(true);
        $page->getStyle('C1')->getFont()->setBold(true);
        $page->getStyle('D1')->getFont()->setBold(true);
        $page->getColumnDimension('A')->setAutoSize(true);
        $page->getColumnDimension('B')->setAutoSize(true);
        $page->getColumnDimension('C')->setAutoSize(true);
        $page->getColumnDimension('D')->setAutoSize(true);

        $i = 3;
        foreach ($COAs as $COA) {
            /*$page->setCellValue("A" . $i, $COA->class->Class_Shortcut);
            $page->setCellValue("B" . $i, $COA->COA_Name);
            $page->setCellValue("C" . $i, $COA->COA_Acct_Number);
            $page->setCellValue("D" . $i, $COA->COA_Budget);*/

            $page->setCellValue("A" . $i, $COA->COA_Name);
            $page->setCellValue("B" . $i, $COA->COA_Acct_Number);
            $page->setCellValue("C" . $i, $COA->class->Class_Shortcut);
            $page->setCellValue("D" . $i, $COA->COA_Budget);


            $i++;
        }

        $page->setTitle(date('Y_m_d'));
        $objWriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'COA_' . date('Y_m_d') . '.xlsx' . '"');
        header('Cache-Control: max-age=0');

        $objWriter->save('php://output');
        die;
    }


    /**
     * Prepare COA List to import form
     * @param $COAs
     * @param $existingClasses
     * @return array
     */
    public static function prepareCOAListToImportForm($COAs, $existingClasses)
    {

        $result = array();
        $i = 1;
        foreach ($COAs as $COA) {
            //var_dump($COA);die;
            $shortcut = isset($COA[2]) ? $COA[2] : null;
            $name = isset($COA[0]) ? $COA[0] : null;
            $acctNumber = isset($COA[1]) ? $COA[1] : null;
            $budget = isset($COA[3]) ? $COA[3] : null;
            $budget = preg_replace('/\,/', '.', $budget);

            if (strlen($shortcut) > 0 && strlen($shortcut) <= 3 && strlen($name) > 0 && strlen($name) <= 70 &&
                strlen($acctNumber) > 0 && strlen($acctNumber) <= 63 && strlen($budget) > 0) {
                if (is_numeric($budget)) {
                    $budget = number_format($budget, 2, '.', '');
                }
                $result[$i] = array('class' => $shortcut, 'name' => $name, 'acctNumber' => $acctNumber, 'budget' => $budget, 'validBudget' => is_numeric($budget), 'newClass' => !in_array($shortcut, $existingClasses));
            }
            $i++;
        }

        return $result;
    }


    /**
     * Import COAs skipping same value and updating changed
     * @param $clientID
     * @param $projectID
     * @param $importedCoas
     */
    public static function importUniqueCOAs($clientID, $projectID, $importedCoas)
    {
        $all_amount=count($importedCoas);
        $i=0;
        ProgressBar::toZero();
        $pb= ProgressBar::init();


        //1.2 select from database all acctNumbers
        $list_items = Coa::getAllCoasAcctNumbers(Yii::app()->user->clientID,Yii::app()->user->projectID);

        foreach ($importedCoas as $imported_item) {

            $result_coa_model = self::addSingleCOA($clientID, $projectID, $imported_item,$list_items);

            /**
             * * Next block used for progress bar animation only
             * As COAs importing about 75% percents of whole COA Importing -so we multiply on 75 (not on 100)
             */
                $i++;
                $percent=intval($i/$all_amount*100);
                session_start();
                $_SESSION['progress']=$percent;
                session_write_close();
            //end of block
        }

    }


    /**
     * Import COAs skipping same value and updating changed
     * @param $clientID
     * @param $projectID
     * @param $importedCoas
     */
    public static function addSingleCOA($clientID, $projectID, $singleCoa,$list_existing_items)
    {
            $result = true;
            try {
                if (in_array($singleCoa['acctNumber'], $list_existing_items)) {
                    //such item exists - need to be checked for unique or not
                    $existing_coa = Coa::model()->findByAttributes(array(
                        'COA_Acct_Number' => $singleCoa['acctNumber'],
                        'Client_ID' => $clientID,
                        'Project_ID' => $projectID
                    ));

                    $coaClass = CoaClass::model()->findByAttributes(array(
                        'Project_ID' => $projectID,
                        'Class_Shortcut' => $singleCoa['class'],
                    ));

                    if ($coaClass->COA_Class_ID == $existing_coa->COA_Class_ID &&
                        $singleCoa['budget'] == $existing_coa->COA_Budget && $existing_coa->COA_Name == $singleCoa['name']
                    ) {

                    } else {

                        $existing_coa->COA_Class_ID = $coaClass->COA_Class_ID;

                        $existing_coa->COA_Budget = $singleCoa['budget'];
                        $existing_coa->COA_Name = $singleCoa['name'];

                        $existing_coa->save();
                        $result = $existing_coa;
                    }


                } else {
                    //item with such number doesn't exists - need to be added to system

                    $coaClass = CoaClass::model()->findByAttributes(array(
                        'Project_ID' => $projectID,
                        'Class_Shortcut' => $singleCoa['class'],
                    ));


                    $coa = new Coa();
                    $coa->Client_ID = $clientID;
                    $coa->COA_Acct_Number = $singleCoa['acctNumber'];
                    $coa->Project_ID = $projectID;
                    $coa->COA_Name = $singleCoa['name'];
                    $coa->COA_Class_ID = $coaClass->COA_Class_ID;
                    $coa->COA_Budget = $singleCoa['budget'];
                    $coa->COA_Current_Total = Coa::getCurrentTotal($projectID, $singleCoa['acctNumber'], $singleCoa['budget']);

                    $coa->save();
                    $result = $coa;
                }
            } catch (Exception $e) {
                $result = false;
            }

        return $result;
    }




    public static function  getCoaCodesForDropDown(){
        $sql='select distinct COA_Acct_Number,Coa_name from coa where Client_ID='.Yii::app()->user->clientID . ' and Project_ID='.Yii::app()->user->projectID;
        $list= Yii::app()->db->createCommand($sql)->queryAll();
        foreach ($list as $items){
            $result[]= $items["COA_Acct_Number"].' - '. $items["Coa_name"];
        }
        return $result;

    }

    /**
     * Copy COAs from another project
     * @param $clientID
     * @param $projectID
     * @param $clientFromCopyId
     * @param $projectFromCopyId
     */
    public static function copyCOAs($clientID, $projectID, $client_to_id, $project_to_id)
    {
        $clientID  = intval($clientID);
        $projectID  = intval($projectID);
        $project_to_id  = intval($project_to_id);
        $client_to_id = intval($client_to_id);

        $coasToCopy = Coa::getClientsCOAs($clientID, $projectID);

        foreach($coasToCopy as $coaToCopy) {
            $coaClass = CoaClass::model()->findByAttributes(array(
                'Project_ID' => $project_to_id,
                'Class_Shortcut' => $coaToCopy->class->Class_Shortcut,
            ));

            if ($coaClass) {
                $coa = Coa::model()->findByAttributes(array(
                    'Project_ID' => $project_to_id,
                    'COA_Acct_Number' => $coaToCopy->COA_Acct_Number,
                ));

                if (!$coa) {
                    $coa = new Coa();
                    $coa->COA_Budget = 0;
                    $coa->COA_Current_Total = 0;
                }

                $coa->Client_ID = $client_to_id;
                $coa->COA_Acct_Number = $coaToCopy->COA_Acct_Number;
                $coa->Project_ID = $project_to_id;
                $coa->COA_Name = $coaToCopy->COA_Name;
                $coa->COA_Class_ID = $coaClass->COA_Class_ID;

                if ($coa->validate()) {
                    $coa->save();
                }
            }
        }
    }

    public static function checkCoaNumber($projectID, $userInput){

        if (strlen($userInput)<=63 || strlen(Coa::constructCoaNumber($projectID, $userInput)<=63) ) {
            return true;
        } else {return false;}

    }

    public static function constructCoaNumber($projectID, $userInput){

        $projectID = intval($projectID);
        $coaStructure = Coa::getProjectCoaStructure($projectID);
        //if ($coaStructure->COA_Break_Number > 0 && (strpos($userInput,$coaStructure->COA_Break_Character))){
            $prefix=$head=$modifier=$root=$conjun=$tail=$suffix = '';

            $prefix = $coaStructure->COA_Prefix > 0 ? $coaStructure->COA_Prefix_Val : '';
            $modifier = $coaStructure->COA_Modifier > 0 ? $coaStructure->COA_Modifier_Val : '';
            $conjun = $coaStructure->COA_Conjun > 0 ? $coaStructure->COA_Conjun_Val : '';
            $suffix = $coaStructure->COA_Suffix > 0 ? $coaStructure->COA_Suffix_Val : '';

            $inputParts = explode($coaStructure->COA_Break_Character,$userInput);

            foreach ($inputParts as $inputPart) {
                if ($coaStructure->COA_Head > 0 && $head == '') {
                    $head = $inputPart;
                } else if ($coaStructure->COA_Root > 0 && $root == '') {
                    $root = $inputPart;
                } else if ($coaStructure->COA_Tail > 0 && $tail == '') {
                    $tail = $inputPart;
                }
            }

            $coaAccountNumb = $prefix.$head.$modifier.$root.$conjun.$tail.$suffix;

           // var_dump($coaAccountNumb);
           // var_dump($coaStructure);die;

        //} else {
        //    $coaAccountNumb = $userInput;
        //}
        return $coaAccountNumb;

    }

    /**
     * Get COA_Acct_Number by user input and COA structure settings
     * @param $projectID
     * @param $userInput
     * @return string
     */
    public static function getCoaAcctNumbByUserInput($projectID, $userInput)
    {
        $projectID = intval($projectID);
        $coaStructure = Coa::getProjectCoaStructure($projectID);
        $coaAccountNumb = '';
        if ($coaStructure !== null && $coaStructure->COA_Allow_Manual_Coding != 1) {
            if ($coaStructure->COA_Break_Number > 0 && (strpos($userInput,$coaStructure->COA_Break_Character) === false || $coaStructure->COA_Break_Character == '')) {
                $coaAccountNumb = $userInput;
            } else {
                $prefix=$head=$modifier=$root=$conjun=$tail=$suffix = '';
                $prefix = $coaStructure->COA_Prefix > 0 ? $coaStructure->COA_Prefix_Val : '';
                $modifier = $coaStructure->COA_Modifier > 0 ? $coaStructure->COA_Modifier_Val : '';
                $conjun = $coaStructure->COA_Conjun > 0 ? $coaStructure->COA_Conjun_Val : '';
                $suffix = $coaStructure->COA_Suffix > 0 ? $coaStructure->COA_Suffix_Val : '';

                if ($coaStructure->COA_Break_Character == '' || $coaStructure->COA_Break_Number == 0) {
                    $inputParts = array($userInput);
                } else {
                    $inputParts = explode($coaStructure->COA_Break_Character,$userInput);
                }

                foreach ($inputParts as $inputPart) {
                    if ($coaStructure->COA_Head > 0 && $head == '') {
                        $head = $inputPart;
                    } else if ($coaStructure->COA_Root > 0 && $root == '') {
                        $root = $inputPart;
                    } else if ($coaStructure->COA_Tail > 0 && $tail == '') {
                        $tail = $inputPart;
                    }
                }

                $coaAccountNumb = $prefix.$head.$modifier.$root.$conjun.$tail.$suffix;
            }
        } else {
            $coaAccountNumb = $userInput;
        }

        return $coaAccountNumb;
    }

    /**
     * Get current budget of COA by Acct. num and Project
     * @param $projectId
     * @param $acctNum
     * @param $budget
     * @return float
     */
    public static function getCurrentTotal($projectId, $acctNum, $budget)
    {
        $sql = "SELECT sum(PO_Dists_Amount) as po_sum
                FROM `po_dists`
                LEFT JOIN `pos` ON `pos`.`PO_ID` = `po_dists`.`PO_ID`
                LEFT JOIN `documents` ON `documents`.`Document_ID` = `pos`.`Document_ID`
                WHERE `po_dists`.`PO_Dists_GL_Code_Full` = '$acctNum' AND `documents`.`Project_ID` = '$projectId'
                AND `pos`.`PO_Approval_Value` = '" . Pos::APPROVED ."'";

        $connection=Yii::app()->db;
        $command=$connection->createCommand($sql);
        $po_sum=$command->queryRow();
        $currentBudget = $budget - $po_sum['po_sum'];
        return $currentBudget;
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

		$criteria->compare('COA_ID',$this->COA_ID);
		$criteria->compare('Client_ID',$this->Client_ID);
		$criteria->compare('COA_Acct_Number',$this->COA_Acct_Number,true);
		$criteria->compare('Project_ID',$this->Project_ID);
		$criteria->compare('COA_Name',$this->COA_Name,true);
		$criteria->compare('COA_Class_ID',$this->COA_Class_ID);
		$criteria->compare('COA_Budget',$this->COA_Budget,true);
        $criteria->compare('COA_Current_Total',$this->COA_Current_Total,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Coa the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public static function getAllCoasAcctNumbers ($client_id,$project_id) {

        $list_items = array();
        $sql='select COA_Acct_Number as acctNumber from coa where Client_ID='.$client_id . ' and Project_ID='.$project_id;
        $list= Yii::app()->db->createCommand($sql)->queryAll();

        foreach ($list as $items){
            $list_items[]=$items['acctNumber'];
        }

        return $list_items;

    }
}
