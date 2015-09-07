<?php

class LibraryController extends Controller
{
    const ORGANIZE_PAGE = 1;

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
                'actions'=>array('index', 'gettreebysearchquery', 'checkstoragedocuments', 'viewstorage', 'getdocumentview',
                                 'getactionsbuttons', 'getlibraryform', 'getiteminfo', 'getduplicatedocumentform', 'getstorages',
                                 'checkdocumentaccordancetosection', 'duplicatedocument', 'setposorting', 'changeyear',
                                 'organize', 'getaccessdropdown', 'setaccesstodoc','setUnassignedToSession'),
                'expression'=>function() {
                    $users = array('admin', 'user', 'approver', 'processor', 'db_admin', 'client_admin');
                    $clientID = isset(Yii::app()->user->clientID) ? Yii::app()->user->clientID : 0;
                    $companyServiceLevel = ClientServiceSettings::getClientServiceSettings($clientID);
                    $tier_settings = isset(Yii::app()->user->tier_settings) ? Yii::app()->user->tier_settings : null ;//array of aggregated settings for current user
                    if (isset(Yii::app()->user->id)
                        && in_array(Yii::app()->user->id, $users)
                        && $companyServiceLevel
                        && $tier_settings['library']
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
     * Index action
     * Payments list
     */
    public function actionIndex()
	{
        $year = date('Y');
        if (isset($_SESSION['library_current_year'])) {
            $year = $_SESSION['library_current_year'];
        }

        Storages::checkLibraryStoragesForProjects($year);

        if (!isset($_SESSION['selected_item'])) {
            $_SESSION['selected_item'] = array(
                'id' => 0,
                'rowType' => '',
            );
        }

        // if isset form data
        $showLibraryForm = false;
        $formContent = '';
        if (isset($_GET['activeTab'])) {
            $activeTab = $_GET['activeTab'];
        } else {
            $activeTab = 'tab1';}

        if (isset($_POST['library_form'])) {
            $rowType = trim($_POST['rowType']);
            $action = trim($_POST['action']);

            $redirect_url = trim($_POST['back_url']);

            $id = 0;
            if (isset($_POST['id'])) {
                $id = intval($_POST['id']);
            }

            $storageType = intval($_POST['storage']);

            if ($storageType == Storages::SHELF) {
                $activeTab = 'tab2';
            }

            if ($rowType == 'storage') {
                if ($action == 'add' && Yii::app()->user->projectID != 'all') {
                    // create new storage
                    $storage = new Storages();

                    // set variables
                    $storage->attributes = $_POST['Storages'];
                    $storage->Project_ID = Yii::app()->user->projectID;
                    $storage->Client_ID = Yii::app()->user->clientID;
                    $storage->Year = $year;
                    $storage->Created_By = Yii::app()->user->userID;
                    $storage->Storage_Type = $storageType;

                    // check variables
                    if ($storage->validate()) {
                        $storage->save();
                        $_SESSION['selected_item'] = array(
                            'id' => $storage->Storage_ID,
                            'rowType' => $rowType,
                        );
                        Yii::app()->user->setFlash('success', ($storage->Storage_Type == Storages::SHELF ? "Shelf" : "Cabinet") . " has been created!");
                        $this->redirect('/library');
                    } else {
                        $showLibraryForm = true;
                        $formContent = $this->renderPartial('storage_form', array(
                            'storage' => $storage,
                            'action' => $action,
                            'id' => $id,
                            'storageType' => $storageType,
                            'rowType' => $rowType,
                        ), true);
                    }
                } else if ($action == 'edit') {
                    // edit storage
                    if (Storages::hasAccess($id, $rowType)) {
                        $storage = Storages::model()->findByPk($id);

                        // set variables
                        $storage->Storage_Name = $_POST['Storages']['Storage_Name'];
                        if (isset($_POST['Storages']['Access_Type']) && $storage->Created_By == Yii::app()->user->userID) {
                            $storage->Access_Type = $_POST['Storages']['Access_Type'];
                        }

                        // check variables
                        if ($storage->validate()) {
                            $storage->save();
                            Yii::app()->user->setFlash('success', "Changes saved!");

                            $_SESSION['selected_item'] = array(
                                'id' => $storage->Storage_ID,
                                'rowType' => $rowType,
                            );

                            $this->redirect('/library');

                        } else {
                            $showLibraryForm = true;
                            $formContent = $this->renderPartial('storage_form', array(
                                'storage' => $storage,
                                'action' => $action,
                                'id' => $id,
                                'storageType' => $storageType,
                                'rowType' => $rowType,
                            ), true);
                        }

                        $_SESSION['selected_item'] = array(
                            'id' => $storage->Storage_ID,
                            'rowType' => $rowType,
                        );

                    }
                }
            } else if ($rowType == 'section') {
                if ($action == 'add') {
                    if (Storages::hasAccess($id, 'storage')) {
                        // create new section
                        $section = new Sections();

                        // set variables
                        $section->attributes = $_POST['Sections'];

                        if ($storageType == Storages::CABINET) {
                            $section->Folder_Cat_ID = Sections::GENERAL;
                        }

                        $section->Storage_ID = $id;
                        $section->Created_By = Yii::app()->user->userID;
                        $section->Section_Type = $storageType;

                        // check variables
                        if ($section->validate()) {
                            $section->save();
                            $_SESSION['selected_item'] = array(
                                'id' => $section->Section_ID,
                                'rowType' => $rowType,
                            );
                            for ($i = 1; $i <= intval($_POST['count_of_subsections']); $i++) {
                                Subsections::addSubsectionToSection($section->Section_ID, (($storageType == Storages::SHELF) ? 'Tab' : 'Panel') . ' ' . $i, $storageType, Yii::app()->user->userID, $_POST['Sections']['Access_Type']);
                            }
                            Yii::app()->user->setFlash('success', ($section->Section_Type == Storages::SHELF ? "Binder" : "Folder") . " has been created!");
                            $this->redirect('/library');

                        } else {
                            $showLibraryForm = true;
                            $formContent = $this->renderPartial('section_form', array(
                                'section' => $section,
                                'action' => $action,
                                'id' => $id,
                                'storageType' => $storageType,
                                'rowType' => $rowType,
                            ), true);
                            $_SESSION['selected_item'] = array(
                                'id' => $section->Storage_ID,
                                'rowType' => 'storage',
                            );
                        }
                    }
                } else if ($action == 'edit') {
                    // edit section
                    if (Storages::hasAccess($id, $rowType)) {
                        $section = Sections::model()->findByPk($id);

                        // set variables
                        $section->Section_Name = $_POST['Sections']['Section_Name'];
                        if (isset($_POST['Sections']['Access_Type']) && $section->Created_By == Yii::app()->user->userID) {
                            $section->Access_Type = $_POST['Sections']['Access_Type'];
                        }

                        // check variables
                        if ($section->validate()) {
                            $section->save();
                            Yii::app()->user->setFlash('success', "Changes saved!");
                            $this->redirect('/library');
                        } else {
                            $showLibraryForm = true;
                            $formContent = $this->renderPartial('section_form', array(
                                'section' => $section,
                                'action' => $action,
                                'id' => $id,
                                'storageType' => $storageType,
                                'rowType' => $rowType,
                            ), true);
                        }

                        $_SESSION['selected_item'] = array(
                            'id' => $section->Section_ID,
                            'rowType' => $rowType,
                        );
                    }
                }
            } else if ($rowType == 'subsection') {
                if ($action == 'add') {
                    if (Storages::hasAccess($id, 'section')) {
                        // create new subsection
                        $subsection = new Subsections();

                        // set variables
                        $subsection->attributes = $_POST['Subsections'];

                        $subsection->Section_ID = $id;
                        $subsection->Created_By = Yii::app()->user->userID;
                        $subsection->Subsection_Type = $storageType;

                        // check variables
                        if ($subsection->validate()) {
                            $subsection->save();
                            $_SESSION['selected_item'] = array(
                                'id' => $subsection->Subsection_ID,
                                'rowType' => $rowType,
                            );
                            Yii::app()->user->setFlash('success', ($subsection->Subsection_Type == Storages::SHELF ? "Tab" : "Panel") . " has been created!");
                            $this->redirect('/library');die();
                        } else {
                            $showLibraryForm = true;
                            $formContent = $this->renderPartial('subsection_form', array(
                                'subsection' => $subsection,
                                'action' => $action,
                                'id' => $id,
                                'storageType' => $storageType,
                                'rowType' => $rowType,
                            ), true);
                            $_SESSION['selected_item'] = array(
                                'id' => $subsection->Section_ID,
                                'rowType' => 'section',
                            );
                        }
                    }
                } else if ($action == 'edit') {
                    // edit subsection
                    if (Storages::hasAccess($id, $rowType)) {
                        $subsection = Subsections::model()->findByPk($id);

                        // set variables
                        $subsection->Subsection_Name = $_POST['Subsections']['Subsection_Name'];
                        if (isset($_POST['Subsections']['Access_Type']) && $subsection->Created_By == Yii::app()->user->userID) {
                            $subsection->Access_Type = $_POST['Subsections']['Access_Type'];
                        }

                        // check variables
                        if ($subsection->validate()) {
                            $subsection->save();
                            Yii::app()->user->setFlash('success', "Changes saved!");
                            $this->redirect('/library');die();
                        } else {
                            $showLibraryForm = true;
                            $formContent = $this->renderPartial('subsection_form', array(
                                'subsection' => $subsection,
                                'action' => $action,
                                'id' => $id,
                                'storageType' => $storageType,
                                'rowType' => $rowType,
                            ), true);
                        }

                        $_SESSION['selected_item'] = array(
                            'id' => $subsection->Subsection_ID,
                            'rowType' => $rowType,
                        );
                    }
                }
            }
            if ($redirect_url) $this->redirect($redirect_url.'?tab='.$activeTab);
        }

        $cabinets = Storages::getProjectStorages(Yii::app()->user->clientID, Yii::app()->user->projectID, 'cabinets', $year);
        $shelves = Storages::getProjectStorages(Yii::app()->user->clientID, Yii::app()->user->projectID, 'shelves', $year);
        $yearsList = Storages::getYearsList(Yii::app()->user->clientID, Yii::app()->user->projectID);
        $batchesList = Batches::getListByQueryString('',array('sort_by'=>'Batch_ID', 'sort_direction'=>'DESC'));


        $_SESSION['selected_item'] = array(
            'id' => 0,
            'rowType' => '',
        );

        $notes = array();

        $this->render('index', array(
            'cabinets' => $cabinets,
            'shelves' => $shelves,
            'notes' => $notes,
            'showLibraryForm' => $showLibraryForm,
            'formContent' => $formContent,
            'activeTab' => $activeTab,
            'yearsList' => $yearsList,
            'year' => $year,
            'batchesList'=>$batchesList,
        ));
	}

    /**
     * View folder documents action
     */
    public function actionViewStorage()
    {
        $year = date('Y');
        if (isset($_SESSION['library_current_year'])) {
            $year = $_SESSION['library_current_year'];
        }
        $this->layout='//layouts/library';

        if (!isset($_SESSION['storage_to_review'])) {
            Yii::app()->user->setFlash('success', "Please choose folder to review!");
            $this->redirect('/library');
        }

        $storage = null;
        $section = null;
        $subsections = array();
        $activeTab = 0;
        if ($_SESSION['storage_to_review']['type'] == 'section') {
            $section = Sections::model()->with('subsections', 'storage')->findByPk($_SESSION['storage_to_review']['id']);
            if ($section) {
                $storage = $section->storage;
                $subsections = $section->subsections;
            }
        } else {
            $subsection = Subsections::model()->findByPk($_SESSION['storage_to_review']['id']);
            $activeTab = $_SESSION['storage_to_review']['id'];
            if ($subsection) {
                $section = Sections::model()->with('subsections', 'storage')->findByPk($subsection->Section_ID);
                if ($section) {
                    $storage = $section->storage;
                    $subsections = $section->subsections;
                }
            }
        }

        if (!$storage || !$section || count($subsections) == 0) {
            Yii::app()->user->setFlash('success', "Please choose folder to review!");
            $this->redirect('/library');
        }

        usort($subsections, array($this, 'sortSubsections'));

        $subsections = Subsections::prepareSubsectionsToView($subsections, $section, $year);

        $this->render('view_storage', array(
            'storage' => $storage,
            'section' => $section,
            'subsections' => $subsections,
            'activeTab' => $activeTab,
            'year' => $year,
        ));
    }

    /**
     * Organize action
     */
    public function actionOrganize()
    {
        if (Yii::app()->user->projectID == 'all') {
            Yii::app()->user->setFlash('success', "Please select a specific Project for this process.");
            $this->redirect('/library');
            Yii::app()->end();
        }
        $active_item = null;
        $year = date('Y');
        if (isset($_SESSION['library_current_year'])) {
            $year = $_SESSION['library_current_year'];
        }

        Storages::checkLibraryStoragesForProjects($year);


        // process form
        if (isset($_POST['documents_to_assign'])) {
            $subsId = intval($_POST['subsection_id']);
            if ($subsId > 0) {
                foreach ($_POST['documents'] as $docId) {
                    $docId = intval($docId);
                    $access = 1;
                    if (isset($_POST['access'][$docId])) {
                        $access = intval($_POST['access'][$docId]);
                    }
                    if ($docId > 0 && $access <= 1) {
                        LibraryDocs::assignLBDocumentToSubsection($subsId, $docId, $access);
                    }
                }
                $storage = $_POST['subsection_type'] == Storages::CABINET ? 'Panel' : 'Tab';
                Yii::app()->user->setFlash('success', "Documents were successfully moved to $storage!");

                $sec_id = intval($_POST['section_id']);
                Storages::setActiveItem($sec_id);
            }
        }

        // get storages
        $cabinets = Storages::getProjectStorages(Yii::app()->user->clientID, Yii::app()->user->projectID, 'cabinets', $year);
        $shelves = Storages::getProjectStorages(Yii::app()->user->clientID, Yii::app()->user->projectID, 'shelves', $year);

        // get years
        $yearsList = Storages::getYearsList(Yii::app()->user->clientID, Yii::app()->user->projectID);

        $_SESSION['selected_item'] = array(
            'id' => 0,
            'rowType' => '',
        );

        // get unassigned documents
        $documents = LibraryDocs::getUnassignedLibraryDocuments(Yii::app()->user->clientID, Yii::app()->user->projectID, Yii::app()->user->userID, $year);
        // get unassigned documents from another period
        if (count($_SESSION['unassigned_items'])>0) {
            $another_documents = LibraryDocs::getUnassignedFromSession();
        } else {
            $another_documents = array();
        }

        $this->render('organize', array(
            'cabinets' => $cabinets,
            'shelves' => $shelves,
            'yearsList' => $yearsList,
            'year' => $year,
            'documents' => $documents,
            'another_documents' => $another_documents,
            'active_item' => $active_item
        ));
    }

    /**
     * Get tree by search query
     */
    public function actionGetTreeBySearchQuery()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['query'])) {
            $queryString = trim($_POST['query']);
            $storageType = (trim($_POST['type']) == 'shelves_table') ? 'shelves' : 'cabinets';
            $sortDirection = trim($_POST['sortDirection']);
            $organizePage = false;
            if (isset($_POST['organizePage']) && $_POST['organizePage'] == self::ORGANIZE_PAGE) {
                $organizePage = true;
            }

            $year = date('Y');
            if (isset($_SESSION['library_current_year'])) {
                $year = $_SESSION['library_current_year'];
            }

            if ($queryString == '') {
                $storagesList = Storages::getProjectStorages(Yii::app()->user->clientID, Yii::app()->user->projectID, $storageType, $year, $sortDirection);
            } else {
                $storagesList = Storages::getProjectStoragesBySearchQuery(Yii::app()->user->clientID, Yii::app()->user->projectID, $storageType, $queryString, $year, $sortDirection);
            }

            if ($storageType == 'cabinets') {
                $this->renderPartial('cabinets_list', array(
                    'cabinets' => $storagesList,
                    'organizePage' => $organizePage,
                ));
            } else if ($storageType == 'shelves') {
                $this->renderPartial('shelves_list', array(
                    'shelves' => $storagesList,
                    'organizePage' => $organizePage,
                ));
            }
        }
    }

    /**
     * Check existing of storage documents
     */
    public function actionCheckStorageDocuments()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['rowType']) && isset($_POST['storage']) && isset($_POST['id'])) {
            $storage = intval($_POST['storage']);
            $rowType = trim($_POST['rowType']);
            $id = intval($_POST['id']);

            // get count of documents
            $documentsCount = Sections::getDocumentsCount($id, $rowType);
            if ($documentsCount > 0) {
                echo 1;
                $_SESSION['storage_to_review'] = array(
                    'id' => $id,
                    'type' => $rowType,
                );
            } else {
                echo 0;
                if (isset($_SESSION['storage_to_review'])) {
                    unset($_SESSION['storage_to_review']);
                }
            }
        }
    }

    /**
     * Get document view action
     */
    public function actionGetDocumentView()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['tab_num']) && isset($_POST['doc_id']) && isset($_POST['subsectionId'])) {
            $tabNum = intval($_POST['tab_num']);
            $docId = intval($_POST['doc_id']);
            $subsectionId = intval($_POST['subsectionId']);

            if ($tabNum > 0 && Documents::hasAccess($docId)) {
                $document = Documents::model()->with('image')->findByPk($docId);
                $this->renderPartial('lib_view', array(
                    'document' => $document,
                    'tabNum' => $tabNum,
                    'subsectionID' => $subsectionId
                ));
            }
        }
    }

    /**
     * Get library form
     */
    public function actionGetLibraryForm()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['rowType']) && isset($_POST['storage']) && isset($_POST['action'])) {
            $rowType = trim($_POST['rowType']);
            $action = trim($_POST['action']);
            $back_url = trim($_POST['back_url']);
            $id = 0;
            if (isset($_POST['id'])) {
                $id = intval($_POST['id']);
            }
            $storageType = intval($_POST['storage']);

            if ($rowType == 'storage') {
                if ($action == 'add') {
                    $storage = new Storages();
                    $this->renderPartial('storage_form', array(
                        'storage' => $storage,
                        'action' => $action,
                        'id' => $id,
                        'storageType' => $storageType,
                        'rowType' => $rowType,
                        'back_url'=>$back_url
                    ));
                } else if ($action == 'edit') {
                    if (Storages::hasAccess($id, $rowType)) {
                        $storage = Storages::model()->findByPk($id);
                        $this->renderPartial('storage_form', array(
                            'storage' => $storage,
                            'action' => $action,
                            'id' => $id,
                            'storageType' => $storageType,
                            'rowType' => $rowType,
                            'back_url'=>$back_url
                        ));
                    }
                } else if ($action == 'add_sub') {
                    if (Storages::hasAccess($id, $rowType)) {
                        $section = new Sections();
                        $this->renderPartial('section_form', array(
                            'section' => $section,
                            'action' => 'add',
                            'id' => $id,
                            'storageType' => $storageType,
                            'rowType' => 'section',
                            'back_url'=>$back_url
                        ));
                    }
                } else if ($action == 'delete') {
                    if (Storages::hasAccess($id, $rowType)) {
                        $storage = Storages::model()->findByPk($id);
                        if ($storage->Created_By == Yii::app()->user->userID) {
                            Storages::deleteStorage($id);
                            Yii::app()->user->setFlash('success', ($storage->Storage_Type == Storages::SHELF ? "Shelf" : "Cabinet") . " has been deleted!");
                            if ($back_url) $this->redirect($back_url);
                        }
                    }
                }
            } else if ($rowType == 'section') {
                if ($action == 'add') {
                    if (Storages::hasAccess($id, $rowType)) {
                        $selectedSection = Sections::model()->with('storage')->findByPk($id);
                        $section = new Sections();
                        $this->renderPartial('section_form', array(
                            'section' => $section,
                            'action' => $action,
                            'id' => $selectedSection->storage->Storage_ID,
                            'storageType' => $storageType,
                            'rowType' => $rowType,
                            'back_url'=>$back_url
                        ));
                    }
                } else if ($action == 'edit') {
                    if (Storages::hasAccess($id, $rowType)) {
                        $section = Sections::model()->findByPk($id);
                        $this->renderPartial('section_form', array(
                            'section' => $section,
                            'action' => $action,
                            'id' => $id,
                            'storageType' => $storageType,
                            'rowType' => $rowType,
                            'back_url'=>$back_url
                        ));
                    }
                } else if ($action == 'add_sub') {
                    if (Storages::hasAccess($id, $rowType)) {
                        $subsection = new Subsections();
                        $this->renderPartial('subsection_form', array(
                            'subsection' => $subsection,
                            'action' => 'add',
                            'id' => $id,
                            'storageType' => $storageType,
                            'rowType' => 'subsection',
                            'back_url'=>$back_url
                        ));
                    }
                } else if ($action == 'delete') {
                    if (Storages::hasAccess($id, $rowType)) {
                        $section = Sections::model()->findByPk($id);
                        if ($section->Created_By == Yii::app()->user->userID) {
                            $_SESSION['selected_item'] = array(
                                'id' => $section->Storage_ID,
                                'rowType' => 'storage',
                            );
                            Sections::deleteSection($id);
                            Yii::app()->user->setFlash('success', ($section->Section_Type == Storages::SHELF ? "Binder" : "Folder") . " has been deleted!");
                            if ($back_url) $this->redirect($back_url);
                        }
                    }
                }
            } else if ($rowType == 'subsection') {
                if ($action == 'add') {
                    if (Storages::hasAccess($id, $rowType)) {
                        $selectedSubsection = Subsections::model()->with('section')->findByPk($id);
                        $subsection = new Subsections();
                        $this->renderPartial('subsection_form', array(
                            'subsection' => $subsection,
                            'action' => $action,
                            'id' => $selectedSubsection->section->Section_ID,
                            'storageType' => $storageType,
                            'rowType' => $rowType,
                            'back_url'=>$back_url
                        ));
                    }
                } else if ($action == 'edit') {
                    if (Storages::hasAccess($id, $rowType)) {
                        $subsection = Subsections::model()->findByPk($id);
                        $this->renderPartial('subsection_form', array(
                            'subsection' => $subsection,
                            'action' => $action,
                            'id' => $id,
                            'storageType' => $storageType,
                            'rowType' => $rowType,
                            'back_url'=>$back_url
                        ));
                    }
                } else if ($action == 'delete') {
                    if (Storages::hasAccess($id, $rowType)) {
                        $subsection = Subsections::model()->findByPk($id);
                        if ($subsection->Created_By == Yii::app()->user->userID) {
                            $_SESSION['selected_item'] = array(
                                'id' => $subsection->Section_ID,
                                'rowType' => 'section',
                            );
                            Subsections::deleteSubsection($id);
                            Yii::app()->user->setFlash('success', ($subsection->Subsection_Type == Storages::SHELF ? "Tab" : "Panel") . " has been deleted!");
                            if ($back_url) $this->redirect($back_url);
                        }
                    }
                }
            }
        }
    }

    /**
     * Get selected item info
     */
    public function actionGetItemInfo()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['rowType']) && isset($_POST['storage']) && isset($_POST['id'])) {
            $rowType = trim($_POST['rowType']);
            $id = intval($_POST['id']);
            $storageType = intval($_POST['storage']);

            $optionsList = array();
            $name = '';
            if ($rowType == 'storage') {
                $storage = Storages::model()->with('user')->findByPk($id);
                if ($storage) {
                    $name = $storage->Storage_Name;

                    if ($storage->user) {
                        $optionsList['Created By'] = $storage->user->person->First_Name . ' ' . $storage->user->person->Last_Name;
                    } else {
                        $optionsList['Created By'] = 'System';
                    }

                    $optionsList['Access'] = $storage->Access_Type == Storages::HAS_ACCESS ? 'For all users in Project' : 'Only for me';

                    $countOfDocuments = Sections::getDocumentsCount($id, $rowType);
                    $optionsList['Count of documents'] = $countOfDocuments;
                }
            } else if ($rowType == 'section') {
                $section = Sections::model()->with('user', 'folder_type')->findByPk($id);
                $name = $section->Section_Name;
                $optionsList['Category'] = $section->folder_type->Full_Name;
                if ($section->user) {
                    $optionsList['Created By'] = $section->user->person->First_Name . ' ' . $section->user->person->Last_Name;
                } else {
                    $optionsList['Created By'] = 'System';
                }
                $optionsList['Access'] = $section->Access_Type == Storages::HAS_ACCESS ? 'For all users in Project' : 'Only for me';

                $countOfDocuments = Sections::getDocumentsCount($id, $rowType);
                $optionsList['Count of documents'] = $countOfDocuments;
            } else if ($rowType == 'subsection') {
                $subsection = Subsections::model()->with('user')->findByPk($id);
                $name = $subsection->Subsection_Name;
                if ($subsection->user) {
                    $optionsList['Created By'] = $subsection->user->person->First_Name . ' ' . $subsection->user->person->Last_Name;
                } else {
                    $optionsList['Created By'] = 'System';
                }
                $optionsList['Access'] = $subsection->Access_Type == Storages::HAS_ACCESS ? 'For all users in Project' : 'Only for me';

                $countOfDocuments = Sections::getDocumentsCount($id, $rowType);
                $optionsList['Count of documents'] = $countOfDocuments;
            }

            $this->renderPartial('item_info', array(
                'optionsList' => $optionsList,
                'name' => $name,
            ));
        }
    }

    /**
     * Get form for Duplicate document action
     */
    public function actionGetDuplicateDocumentForm()
    {
        if (Yii::app()->request->isAjaxRequest) {
            $this->renderPartial('duplicate_form');
        }
    }

    /**
     * Get storages list for Duplicate document action
     */
    public function actionGetStorages()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['storageType']) && isset($_POST['value'])) {
            $storageType = trim($_POST['storageType']);
            $id = intval($_POST['value']);
            $folders = false;

            $year = date('Y');
            if (isset($_SESSION['library_current_year'])) {
                $year = $_SESSION['library_current_year'];
            }

            $storages = Storages::getStoragesList(Yii::app()->user->clientID, Yii::app()->user->projectID, $storageType, $id, $year);


            if ($_POST['storageType'] == 'sections') {
                $storage = Storages::model()->findByPk($id);
                if (isset($storage->Storage_Type) && $storage->Storage_Type == Storages::CABINET) {
                    $folders = true;
                }
            }



            $this->renderPartial('storages_options', array(
                'storages' => $storages,
                'storageType' => $storageType,
                'folders' => $folders,
            ));
        }
    }

    /**
     * Check Document Accordance To certain Section
     */
    public static function actionCheckDocumentAccordanceToSection()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['sectionID']) && isset($_POST['docId'])) {
            $sectionID = intval($_POST['sectionID']);
            $docId = intval($_POST['docId']);

            $accordance = Sections::checkDocumentAccordanceToSection($sectionID, $docId);

            echo $accordance;
        }
    }

    /**
     * Copy or remove document to certain subsection
     */
    public function actionDuplicateDocument()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['subsectionID']) && isset($_POST['docId']) && isset($_POST['currentSubsection']) && isset($_POST['action'])) {
            $subsectionID = intval($_POST['subsectionID']);
            $docId = intval($_POST['docId']);
            $currentSubsection = intval($_POST['currentSubsection']);
            $action = intval($_POST['action']);

            $currentLibDoc = LibraryDocs::model()->findByAttributes(array(
                'Document_ID' => $docId,
                'Subsection_ID' => $currentSubsection,
            ));

            $document = Documents::model()->findByPk($docId);

            if ($currentLibDoc && $document) {
                $libDoc = LibraryDocs::model()->findByAttributes(array(
                    'Document_ID' => $docId,
                    'Subsection_ID' => $subsectionID,
                ));

                $subsection = Subsections::model()->with('user', 'section.storage')->findByPk($subsectionID);

                if (!$libDoc && !($subsection->section->Folder_Cat_ID == Sections::W9_BOOK && $subsection->Created_By == 0 && $document->Document_Type == Documents::W9)) {

                    $libDoc = new LibraryDocs();
                    $libDoc->Document_ID = $docId;
                    $libDoc->Subsection_ID = $subsectionID;
                    $libDoc->Access_Type = $currentLibDoc->Access_Type;
                    if ($libDoc->validate()) {
                        $libDoc->save();
                        LibraryDocs::sortDocumentsInSubsection($subsectionID);
                    }
                }

                if ($action == 1) {
                    $currentLibDoc->delete();
                }

                Yii::app()->user->setFlash('success', "Document has been " . (($action == 1) ? 'moved' : 'copied') . "!");
            }
        }
    }

    /**
     * Set PO sorting direction
     */
    public function actionSetPoSorting()
    {
        if (Yii::app()->request->isAjaxRequest &&  isset($_POST['value'])) {
            $value = intval($_POST['value']);
            if ($value == 1) {
                $_SESSION['sort_po_by_vendor_name'] = true;
            } else {
                if (isset($_SESSION['sort_po_by_vendor_name'])) {
                    unset($_SESSION['sort_po_by_vendor_name']);
                }
            }
        }
    }

    /**
     * Get dropdown to set access to LB document
     */
    public function actionGetAccessDropDown()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['subsectionID']) && isset($_POST['docId'])) {
            $subsectionID = intval($_POST['subsectionID']);
            $docId = intval($_POST['docId']);
            if ($subsectionID > 0 && $docId > 0) {
                $libDoc = LibraryDocs::model()->with('document')->findByAttributes(array(
                    'Document_ID' => $docId,
                    'Subsection_ID' => $subsectionID,
                ));

                if ($libDoc) {
                    $document = $libDoc->document;
                    if ($document->Document_Type == Documents::LB && $document->User_ID == Yii::app()->user->userID) {
                        $this->renderPartial('access_drop_down', array(
                            'libDoc' => $libDoc,
                        ));
                    }
                }
            }
        }
    }

    /**
     * Set access to LB document
     */
    public function actionSetAccessToDoc()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['subsectionID']) && isset($_POST['docId']) && isset($_POST['value']) ) {
            $subsectionID = intval($_POST['subsectionID']);
            $docId = intval($_POST['docId']);
            $access = intval($_POST['value']);
            if ($subsectionID > 0 && $docId > 0 && $access >= 0 && $access <= 1) {
                $libDoc = LibraryDocs::model()->with('document')->findByAttributes(array(
                    'Document_ID' => $docId,
                    'Subsection_ID' => $subsectionID,
                ));

                if ($libDoc) {
                    $document = $libDoc->document;
                    if ($document->Document_Type == Documents::LB && $document->User_ID == Yii::app()->user->userID) {
                        $libDoc->Access_Type = $access;
                        if ($libDoc->validate()) {
                            $libDoc->save();
                        }
                    }
                }
            }
        }
    }

    /**
     * Change year
     */
    public function actionChangeYear()
    {
        if (Yii::app()->request->isAjaxRequest &&  isset($_POST['year'])) {
            $year = trim($_POST['year']);
            $yearsList = Storages::getYearsList(Yii::app()->user->clientID, Yii::app()->user->projectID);
            foreach ($yearsList as $yearItem) {
                if ($yearItem->Year == $year) {
                    $_SESSION['library_current_year'] = $yearItem->Year;
                }
            }
        }
    }

    /**
     * Sort subsections by name
     * @param $a
     * @param $b
     * @return int
     */
    public function sortSubsections($a, $b) {
        return strnatcmp($a->Subsection_Name, $b->Subsection_Name);
    }


    public function actionSetUnassignedToSession () {

        if (Yii::app()->request->isAjaxRequest &&  isset($_POST['year']) && isset($_POST['doc_id'])) {
            $key = intval($_POST['doc_id']);
            $year =intval($_POST['year']);
            $checked = strval($_POST['checked']) ? 1 :0;
            if ($checked) {
                $_SESSION['unassigned_items'][$key] = $year;
            } else {
                unset ($_SESSION['unassigned_items'][$key]);
            }
        }

    }
}