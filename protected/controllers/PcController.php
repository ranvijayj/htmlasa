<?php

class PcController extends Controller
{
    /**
     * Layout color
     * @var string
     */
    public $layoutColor = "#0078C1";

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
                'actions'=>array('clearpcstoreviewsession'),
                'users'=>array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('index', 'detail', 'getlistbysearchquery', 'getpcinfo', 'addpcsitemstosession',
                                 'printdocument', 'setdocidtoprintdocument', 'senddocumentbyemail'),
                'expression'=>function() {
                    $users = array('admin', 'user', 'approver', 'processor', 'db_admin', 'client_admin');
                    $clientID = isset(Yii::app()->user->clientID) ? Yii::app()->user->clientID : 0;
                    $companyServiceLevel = ClientServiceSettings::getClientServiceSettings($clientID);
                    $tier_settings = isset(Yii::app()->user->tier_settings) ? Yii::app()->user->tier_settings : null ;//array of aggregated settings for current user

                    if (isset(Yii::app()->user->id)
                        && in_array(Yii::app()->user->id, $users)
                        && $companyServiceLevel
                        && isset($tier_settings['pc'])
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
     * PCs list
     */
    public function actionIndex()
	{
        $pcs_to_review = array();
        $queryString = '';

        //get PCs list
        if (isset( $_SESSION['last_pcs_list_search']['options']) && count($_SESSION['last_pcs_list_search']['options']) > 0) {
            $queryString = $_SESSION['last_pcs_list_search']['query'];
            $options = $_SESSION['last_pcs_list_search']['options'];
            $sortOptions = $_SESSION['last_pcs_list_search']['sort_options'];

            // get PCs list
            $pcsList = Pcs::getListByQueryString($queryString, $options, $sortOptions);

            $_SESSION['last_pcs_list_search']['query'] = '';
            $_SESSION['last_pcs_list_search']['sort_options'] = array();
        } else {
            $pcsList = Pcs::getPCsList();
        }

        // pcs ids to review
        if (isset($_SESSION['pcs_to_review'])) {
            $pcs_to_review = $_SESSION['pcs_to_review'];
            $_SESSION['pcs_to_review'] = array();
        }

        $this->render('index', array(
            'pcsList' => $pcsList,
            'pcs_to_review' => $pcs_to_review,
            'queryString' => $queryString,
        ));
	}

    /**
     * PC detail page action
     * @param int $page
     */
    public function actionDetail($page = 1)
    {
        // check PC to review
        if (!isset($_SESSION['pcs_to_review']) || count($_SESSION['pcs_to_review']) == 0) {
            $_SESSION['pcs_to_review'] = Pcs::getLastClientsPCs();
            if (!isset($_SESSION['pcs_to_review']) || count($_SESSION['pcs_to_review']) == 0) {
                Yii::app()->user->setFlash('success', "Please choose PCs to review!");
                $this->redirect('/pc');
            }
        }

        $page = intval($page);
        $num_pages = count($_SESSION['pcs_to_review']);
        if ($page <= 0) {
            $page = 1;
        } else if ($page > $num_pages) {
            $page = $num_pages;
        }

        $docId = $_SESSION['pcs_to_review'][$page];

        $pc = Pcs::model()->with('document')->findByAttributes(array(
            'Document_ID' => $docId,
        ));

        $document = $pc->document;
        $user = $document->user;

        // get document's file
        $condition = new CDbCriteria();
        $condition->select = 'Mime_Type';
        $condition->condition = "Document_ID='" . $document->Document_ID . "'";
        $file = Images::model()->find($condition);

        $this->render('detail', array(
            'page' => $page,
            'num_pages' => $num_pages,
            'pc' => $pc,
            'user' => $user,
            'document' => $document,
            'file' => $file,
        ));
    }

    /**
     * Clear $_SESSION['pcs_to_review'] if we go to details page directly
     */
    public function actionClearPCsToReviewSession()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['clear'])) {
            $_SESSION['pcs_to_review'] = array();
            $_SESSION['last_pcs_list_search']['query'] = '';
            $_SESSION['last_pcs_list_search']['options'] = array();
            $_SESSION['last_pcs_list_search']['sort_options'] = array();
        }
    }

    /**
     * Add PCs items to session action
     */
    public function actionAddPCsItemsToSession()
    {
        if (isset($_POST['documents'])) {
            $_SESSION['pcs_to_review'] = array();
            $i = 1;
            foreach ($_POST['documents'] as $docId) {
                $docId = intval($docId);
                if ($docId > 0 && Documents::hasAccess($docId)) {
                    $document = Documents::model()->findByPk($docId);
                    $pc = Pcs::model()->findByAttributes(array(
                        'Document_ID' => $docId,
                    ));
                    if ($document && $pc) {
                        $_SESSION['pcs_to_review'][$i] = $docId;
                        $i++;
                    }
                }
            }

            $this->redirect('/pc/detail');
        }
    }

    /**
     * Get PCs list by search query action
     */
    public function actionGetListBySearchQuery()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['query'])) {
            $pcsList = array();

            // set query params
            $queryString = trim($_POST['query']);
            $options = array(
                'search_option_employee_name' => intval($_POST['search_option_employee_name']),
                'search_option_envelope_number' => intval($_POST['search_option_envelope_number']),
                'search_option_envelope_total' => intval($_POST['search_option_envelope_total']),
                'search_option_envelope_date' => intval($_POST['search_option_envelope_date']),
            );

            $sortOptions = array(
                'sort_by' => $_POST['sort_type'],
                'sort_direction' => $_POST['sort_direction'],
            );

            // set last search query params to session
            $_SESSION['last_pcs_list_search']['query'] = $queryString;
            $_SESSION['last_pcs_list_search']['options'] = $options;
            $_SESSION['last_pcs_list_search']['sort_options'] = $sortOptions;

            // get PCs list
            $pcsList = Pcs::getListByQueryString($queryString, $options, $sortOptions);

            $this->renderPartial('pcslist', array(
                'pcsList' => $pcsList,
            ));
        }
    }

    /**
     * Get PC info to sidebar
     */
    public function actionGetPCInfo()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['docId'])) {
            $docId = intval($_POST['docId']);
            if ($docId > 0) {
                $pc = Pcs::model()->with('document')->findByAttributes(array(
                    'Document_ID' => $docId,
                ));

                $document = $pc->document;
                $user = $document->user;

                $this->renderPartial('pc_info_block', array(
                    'pc' => $pc,
                    'user' => $user,
                    'document' => $document,
                ));
            }
        }
    }

    /**
     * Print document action
     */
    public function actionPrintDocument()
    {
        $docId = trim($_SESSION['pc_to_print']);

        //get document
        $document = Documents::model()->findByPk($docId);

        if ($document && Documents::hasAccess($docId)) {
            $condition = new CDbCriteria();
            $condition->select = 'Mime_Type';
            $condition->condition = "Document_ID='" . $document->Document_ID . "'";
            $file = Images::model()->find($condition);
            $this->renderPartial('print_document', array(
                'document' => $document,
                'file' => $file,
            ));
        }
    }

    /**
     * Set Doc ID of PC to print
     */
    public function actionSetDocIdToPrintDocument()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['docId'])) {
            $docId = intval($_POST['docId']);
            if ($docId > 0 && Documents::hasAccess($docId)) {
                $document = Documents::model()->findByPk($docId);
                if ($document) {
                    $_SESSION['pc_to_print'] = $docId;
                } else {
                    $_SESSION['pc_to_print'] = '';
                }
            }
        }
    }

    /**
     * Send document by email action
     */
    public function actionSendDocumentByEmail()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['email']) && isset($_POST['docId'])) {
            $docId = intval($_POST['docId']);
            $email = trim($_POST['email']);
            if ($docId > 0 && $email != '' && Documents::hasAccess($docId)) {
                $document = Documents::model()->findByPk($docId);
                $condition = new CDbCriteria();
                $condition->condition = "Document_ID='" . $document->Document_ID . "'";
                $file = Images::model()->find($condition);

                $pc = Pcs::model()->with('document')->findByAttributes(array(
                    'Document_ID' => $docId,
                ));

                $filePath = 'protected/data/docs_to_email/' . $file->File_Name;
                file_put_contents($filePath, stripslashes($file->Img));

                //send document
                Mail::sendDocument($email, $file->File_Name, $filePath, $pc->Employee_Name);

                //delete file
                unlink($filePath);

                echo 1;
            } else {
                echo 0;
            }
        }
    }
}