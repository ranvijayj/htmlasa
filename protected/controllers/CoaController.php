<?php

class CoaController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index', 'getcoalist', 'importcoa', 'export', 'updatecoaclasses', 'changedefaultclass',
                                 'getinplaceinput', 'updatecellvalue','GetCoaCodes','DeleteCoas','GetCoaCreateForm','CreateCoaEntry'),
                'expression'=>function() {
                    $users = array('admin', 'db_admin', 'client_admin');
                    $clientID = isset(Yii::app()->user->clientID) ? Yii::app()->user->clientID : 0;
                    $companyServiceLevel = ClientServiceSettings::getClientServiceSettings($clientID);
                    $tier_settings = TiersSettings::agregateTiersSettings($companyServiceLevel->Service_Level_ID);
                    if (isset(Yii::app()->user->id)
                        && in_array(Yii::app()->user->id, $users)
                        && $companyServiceLevel
                        && $tier_settings['coas']
                        && $companyServiceLevel->Active_To >= date('Y-m-d')) {
                        return true;
                    }
                    return false;
                },
			),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('index', 'getcoalist', 'importcoa', 'export', 'updatecoaclasses', 'changedefaultclass',
                    'getinplaceinput', 'updatecellvalue'),
                'expression'=>function() {
                        $users = array('approver');
                        $clientID = isset(Yii::app()->user->clientID) ? Yii::app()->user->clientID : 0;
                        $companyServiceLevel = ClientServiceSettings::getClientServiceSettings($clientID);
                        $tier_settings = isset(Yii::app()->user->tier_settings) ? Yii::app()->user->tier_settings : null ;//array of aggregated settings for current user

                        if (isset(Yii::app()->user->id)
                            && in_array(Yii::app()->user->id, $users)
                            && $companyServiceLevel
                            && $tier_settings['coas']
                            && $companyServiceLevel->Active_To >= date('Y-m-d')
                            && UsersClientList::getUserApprovalValue(Yii::app()->user->userID ,Yii::app()->user->clientID) >=50
                            ) {
                            return true;
                        }
                        return false;
                    },
            ),
            array('allow',
                    'actions'=>array('GetCoaCodes'),
                    'expression'=>function() {
                            $users = array('data_entry_clerk','user','approver');
                            $clientID = isset(Yii::app()->user->clientID) ? Yii::app()->user->clientID : 0;
                            $companyServiceLevel = ClientServiceSettings::getClientServiceSettings($clientID);
                            $tier_settings = isset(Yii::app()->user->tier_settings) ? Yii::app()->user->tier_settings : null ;//array of aggregated settings for current user
                            if (isset(Yii::app()->user->id)
                                && in_array(Yii::app()->user->id, $users)
                                && $companyServiceLevel

                                && $tier_settings['coas']
                                && $companyServiceLevel->Active_To >= date('Y-m-d')) {
                                return true;
                            }
                            return false;
                        },
                ),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Main action
	 */
	public function actionIndex()
	{
        Coa::checkProject();

        // get COA structure
        $coaStructure = Coa::getProjectCoaStructure(Yii::app()->user->projectID);
        //$coaDefaultClass = Coa::getProjectCoaDefaultClass(Yii::app()->user->projectID);
        $coaDefaultClass = null;
        $coaClasses = Coa::getProjectCoaClasses(Yii::app()->user->projectID);

        // process settings form
        if (isset($_POST['coa_settings_form'])) {
            $coaStructure->attributes = $_POST['CoaStructure'];
            if ($coaStructure->validate()) {
                $coaStructure->save();
                Projects::updateCoaParams(Yii::app()->user->projectID, $coaStructure->COA_Allow_Manual_Coding, $coaStructure->COA_Break_Character, $coaStructure->COA_Break_Number);
                Yii::app()->user->setFlash('success', "Changes saved!");
            }

            $coaClasses = Coa::updateCoaClasses(Yii::app()->user->projectID, $_POST["CoaClass"], $coaClasses);

            $coaDefaultClass = Coa::updateCoaDefaultClass(Yii::app()->user->projectID, intval($_POST['default_coa_class']));
        }


        // process import form
        if (isset($_POST['import_coa_form']) && isset($_SESSION['imported_coas'])) {
            if (count($_POST["CoaClass"]) > 0) {
                $coaClasses = Coa::importCoaClasses(Yii::app()->user->projectID, $_POST["CoaClass"]);
            }


            Coa::importUniqueCOAs(Yii::app()->user->clientID, Yii::app()->user->projectID, $_SESSION['imported_coas']);
            unset($_SESSION['imported_coas']);
            unset($_POST['import_coa_form']);
            Yii::app()->user->setFlash('success', "Chart of Accounts have been imported!");
        }

        // process copy COAs from another project form
        if (isset($_POST['coa_copy_form'])) {
            $project_to_id = intval($_POST['project_to_copy']);
            $client_id = intval($_POST['client_to_copy']);
            if ($project_to_id == Yii::app()->user->projectID) {
                Yii::app()->user->setFlash('success', "Please choose another project to copy!");
            } else {
                $coaStructure = Coa::copyProjectCoaStructure(Yii::app()->user->projectID, $project_to_id);
                $coaClasses = Coa::copyProjectCoaClasses(Yii::app()->user->projectID, $project_to_id, count($coaClasses));
                Coa::copyCOAs(Yii::app()->user->clientID, Yii::app()->user->projectID, $client_id, $project_to_id);
                Yii::app()->user->setFlash('success', "Chart of Accounts have been copied!");
            }
        }

        // get Projest COAs
        //$COAs = Coa::getClientsCOAs(Yii::app()->user->clientID, Yii::app()->user->projectID, $coaDefaultClass);
        $COAs = Coa::getClientsCOAs(Yii::app()->user->clientID, Yii::app()->user->projectID, null);
        $this->render('index', array(
            'COAs' => $COAs,
            'coaStructure' => $coaStructure,
            'coaClasses' => $coaClasses,
            'coaDefaultClass' =>$coaDefaultClass,
        ));
	}


    /**
     *Get codes for Dists table
     */
    public function actionGetCoaCodes()
    {
        if (Yii::app()->request->isAjaxRequest ) {
            $search_string= strval($_POST['search_string']);
            if (Yii::app()->user->projectID =='all') die("project id = ".Yii::app()->user->projectID);
            $sql='select distinct COA_Acct_Number,Coa_name from coa where Client_ID='.Yii::app()->user->clientID . ' and Project_ID='.Yii::app()->user->projectID;

            if($search_string !='') {
                $sql.=' and ( COA_Acct_Number like("'.$search_string.'%") or Coa_name like("'.$search_string.'%") )';
            }
            //var_dump($sql);die;
            $list= Yii::app()->db->createCommand($sql)->queryAll();

            foreach ($list as $items){

                $result[]= $items["COA_Acct_Number"].' - '. $items["Coa_name"];
            }

            echo CJSON::encode($result);
        }
    }
    /**
     * Get COAs list with necessary sorting
     */
    public function actionGetCOAList()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['sort_type']) && isset($_POST['sort_direction'])) {
            $sortType = trim($_POST['sort_type']);
            $sortDirection = trim($_POST['sort_direction']);
            if (intval($_POST['coa_class'])) {
                $coaDefaultClass = CoaClass::model()->findByPk(intval($_POST['coa_class']));
            }
            //$coaDefaultClass = Coa::getProjectCoaDefaultClass(Yii::app()->user->projectID); //don't delete . can be usfull
            //

            $coaClasses = Coa::getProjectCoaClasses(Yii::app()->user->projectID);
            $COAs = Coa::getClientsCOAs(Yii::app()->user->clientID, Yii::app()->user->projectID, $coaDefaultClass, $sortType . ' ' . $sortDirection);
            $this->renderPartial('coa_list', array(
                'COAs' => $COAs,
                'coaClasses' => $coaClasses,
            ));
        }
    }

    /**
     * Import COAs from Excel
     */
    public function actionImportCOA() {
        error_reporting(0);
        if (isset($_FILES)) {
            // create user's folder
            if (!file_exists('protected/data/current_uploads_files/' . Yii::app()->user->userID)) {
                mkdir('protected/data/current_uploads_files/' . Yii::app()->user->userID, 0777);
            }

            if (!file_exists('protected/data/current_uploads_files/' . Yii::app()->user->userID . '/' . date('Y-m-d'))) {
                mkdir('protected/data/current_uploads_files/' . Yii::app()->user->userID  . '/' . date('Y-m-d'), 0777);
            }

            if ($_FILES['userfile']['name'] != '') {
                if ($_FILES['userfile']['error'] == 0) {
                    $pathParts = explode('.', $_FILES['userfile']['name']);
                    $mimeType = $_FILES['userfile']['type'];
                    if (isset($pathParts[1])) {
                        $extension = strtolower($pathParts[(count($pathParts) - 1)]);
                        if ($extension == 'xls' || $extension == 'xlsx' || $extension == 'csv') {
                            $filepath = 'protected/data/current_uploads_files/' . Yii::app()->user->userID . '/'  . date('Y-m-d') . '/' . $_FILES['userfile']['name'];
                            move_uploaded_file($_FILES['userfile']['tmp_name'], $filepath);

                            $existingClasses = array();
                            $coaClasses = Coa::getProjectCoaClasses(Yii::app()->user->projectID);
                            foreach($coaClasses as $coaClass) {
                                $existingClasses[] = $coaClass->Class_Shortcut;
                            }

                            // parse Excel and get COAs list

                            $COAs = Coa::parseImportExcel($filepath);
                            $COAs = Coa::prepareCOAListToImportForm($COAs, $existingClasses);

                            $this->renderPartial('build_coa_list_for_import', array(
                                'COAs' => $COAs,
                                'countClasses' => count($coaClasses),
                            ));

                            // save COAs list to session to import after approval
                            $_SESSION['imported_coas'] = $COAs;

                            @unlink($filepath);
                            die;
                        } else {
                            echo Documents::ERROR_INVALID_EXTENSION;
                        }
                    } else {
                        echo Documents::ERROR_INVALID_EXTENSION;
                    }
                } else if ($_FILES['userfile']['error'] == 1) {
                    echo Documents::ERROR_BIG_FILE_SIZE;
                } else {
                    echo Documents::ERROR_LOADING;
                }
            } else {
                echo Documents::ERROR_INVALID_FILE_NAME;
            }
        }
    }

    /**
     * Export COAs to Excel
     */
    public function actionExport() {
        error_reporting(0);
        Coa::checkProject();
        $COAs = Coa::getClientsCOAs(Yii::app()->user->clientID, Yii::app()->user->projectID);
        Coa::exportCOAs($COAs);
    }

    /**
     * Update COA classes
     */
    public function actionUpdateCOAClasses()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['classID']) && isset($_POST['coas'])) {
            $classID = intval($_POST['classID']);
            $class = CoaClass::model()->findByAttributes(array(
                'Project_ID' => Yii::app()->user->projectID,
                'COA_Class_ID' => $classID,
            ));

            if ($class !== null && is_array($_POST['coas'])) {
                foreach($_POST['coas'] as $coaID) {
                    $coaID = intval($coaID);
                    $coa = Coa::model()->findByAttributes(array(
                        'Project_ID' => Yii::app()->user->projectID,
                        'COA_ID' => $coaID,
                    ));
                    if ($coa !== null) {
                        $coa->COA_Class_ID = $class->COA_Class_ID;
                        if ($coa->validate()) {
                            $coa->save();
                        }
                    }
                }
            }
        }
    }

    /**
     * Change default COA class
     */
    public function actionChangeDefaultClass()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['classID'])) {
            $classID = intval($_POST['classID']);
            $class = CoaClass::model()->findByAttributes(array(
                'Project_ID' => Yii::app()->user->projectID,
                'COA_Class_ID' => $classID,
            ));

            if ($class !== null) {
                Coa::updateCoaDefaultClass(Yii::app()->user->projectID, $classID);
            }
        }
    }

    /**
     * Get in place input html
     */
    public function actionGetInPlaceInput()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['coaID']) && isset($_POST['cellType'])) {
            $coaID = intval($_POST['coaID']);
            $cell = trim($_POST['cellType']);

            $enabledCells = array('COA_Name', 'COA_Acct_Number', 'COA_Budget');

            $coa = Coa::model()->findByAttributes(array(
                'Project_ID' => Yii::app()->user->projectID,
                'COA_ID' => $coaID,
            ));

            if ($coa !== null && isset($coa->$cell) && in_array($cell, $enabledCells)) {
                $maxlength = 15;
                if ($cell == 'COA_Name') {
                    $maxlength = 70;
                } else if ($cell == 'COA_Acct_Number') {
                    $maxlength = 63;
                }
                echo '<input type="text" value="' . $coa->$cell . '" maxlength="' . $maxlength . '" class="in_place_input" name="in_place_input">';
            }
        }
    }

    /**
     * Update cell value by Ajax query
     */
    public function actionUpdateCellValue()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['coaID']) && isset($_POST['cellType']) && isset($_POST['value'])) {
            $coaID = intval($_POST['coaID']);
            $cell = trim($_POST['cellType']);
            $value = trim($_POST['value']);

            $enabledCells = array('COA_Name', 'COA_Acct_Number', 'COA_Budget');

            $coa = Coa::model()->findByAttributes(array(
                'Project_ID' => Yii::app()->user->projectID,
                'COA_ID' => $coaID,
            ));

            if ($cell == 'COA_Budget') {
                $value = preg_replace('/\,/', '.', $value);
            }

            if ($coa !== null && isset($coa->$cell) && in_array($cell, $enabledCells)) {
                $previousValue = $coa->$cell;
                $valueToShow = $value;
                $coa->$cell = $value;
                $coa->COA_Current_Total = Coa::getCurrentTotal(Yii::app()->user->projectID, $coa->COA_Acct_Number, $coa->COA_Budget);
                if ($coa->validate()) {
                    $coa->save();
                } else {
                    $valueToShow = $previousValue;
                }

                if ($cell == 'COA_Name') {
                    echo Helper::cutText(14, 130, 10,$valueToShow);
                } else if ($cell == 'COA_Acct_Number') {
                    echo Helper::cutText(14, 170, 20, $valueToShow);
                } else if ($cell == 'COA_Budget') {
                    echo '<span class="left">$</span><span>' . Helper::cutText(14, 90, 13, number_format($valueToShow, 2)) .'</span>';
                }
            }
        }
    }

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Images the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
        $id = intval($id);
		$model=Images::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Images $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='images-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

    public function actionDeleteCoas()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['coas'])) {
            $deleted_count =0;
            $coas_array = $_POST['coas'];

            foreach ($coas_array as $key =>$coa_id) {
                $coa = Coa::model()->findByPk($coa_id);

                if (!$coa->COA_Used) {
                    $coa_class_id = $coa->class->COA_Class_ID;

                    $coas_of_same_class = Coa::model()->findAllByAttributes(array(
                            'COA_Class_ID' => $coa_class_id
                        )
                    );

                    if ( count($coas_of_same_class)==1 ) {
                        $coa_class = CoaClass::model()->findByPk($coa_class_id);
                        $coa_class->delete();
                    };

                    $coa->delete();
                    $deleted_count++;

                }

            }

            echo $deleted_count;
        }
    }


    public function actionGetCoaCreateForm()
    {
        if (Yii::app()->request->isAjaxRequest) {

            $this->renderPartial('create_form',array(
                'coa'=> new Coa(),
                'coa_classes' => CoaClass::getCoaClassesList(Yii::app()->user->projectID)
                )
            );

        }
    }

    public function actionCreateCoaEntry()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['Coa']) && isset($_POST['CoaClass'])) {

            $result = true;
            $class_id = intval($_POST['CoaClass']['COA_Class_ID']);
            $name = strval($_POST['Coa']['COA_Name']);
            $acctNumber = intval($_POST['Coa']['COA_Acct_Number']);
            $budget = floatval($_POST['Coa']['COA_Budget']);
            //1 need to constract imported_coas array in order to have possibility use the same method as during coa importing.

            $coaClass = CoaClass::model()->findByPk($class_id);

            $coa_to_import[] = array(
                'class'=>$coaClass->Class_Shortcut,
                'name'=>$name,
                'acctNumber'=>$acctNumber,
                'budget'=>$budget,
                'validBudget'=>true,
                'newClass'=>false
            );

            //select all previous coa_acct_numbers
            $list_items = Coa::getAllCoasAcctNumbers(Yii::app()->user->clientID,Yii::app()->user->projectID);

            $result_coa_model = Coa::addSingleCOA(Yii::app()->user->clientID,Yii::app()->user->projectID, $coa_to_import[0],$list_items);



            if (!$result_coa_model->hasErrors()) {
                Yii::app()->user->setFlash('success', "Chart of Accounts have been imported!");
                $result = 'success';
            } else {
                Yii::app()->user->setFlash('success', "Chart of Accounts was not imported!");
                $result = $result_coa_model;
            }

            echo $result;

        }
    }

}
