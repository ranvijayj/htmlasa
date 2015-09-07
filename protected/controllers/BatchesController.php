<?php

class BatchesController extends Controller
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
                'users'=>array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('index', 'detail', 'getlistbysearchquery', 'GetBatchesInfo',
                                  'getbatchfiles','UpdateBatchFlags','ViewStorage','GetBatchViewForLibrary'),
                'expression'=>function() {
                    $users = array('admin', 'user', 'approver', 'processor', 'db_admin', 'client_admin');
                    $clientID = isset(Yii::app()->user->clientID) ? Yii::app()->user->clientID : 0;
                    $companyServiceLevel = ClientServiceSettings::getClientServiceSettings($clientID);
                    if (

                        isset(Yii::app()->user->id)
                        && in_array(Yii::app()->user->id, $users)
                        && $companyServiceLevel
                        && isset(ServiceLevelSettings::$serviceLevelProtectedPagesAccess[$companyServiceLevel->Service_Level_ID])
                        && in_array('batches', ServiceLevelSettings::$serviceLevelProtectedPagesAccess[$companyServiceLevel->Service_Level_ID])
                        && $companyServiceLevel->Active_To >= date('Y-m-d')
                    )
                    {
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
     * Get Batches list by search query action
     */
    public function actionGetListBySearchQuery()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['query'])) {

//            var_dump($_POST);die;
             // set query params
            $queryString = trim($_POST['query']);
            $sortOptions = array(
                'sort_by' => $_POST['sort_type'],
                'sort_direction' => $_POST['sort_direction'],
            );

            // get batches list
            $batchesList = Batches::getListByQueryString($queryString,$sortOptions);

            //var_dump($batchesList);die;

            $this->renderPartial('application.views.library.tabs.partial_batches_list', array(
                 'batchesList' => $batchesList,
            )
            );
        }
    }




    /**
     * Returns info for detail panel
     */
    public function actionGetBatchesInfo()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['docId'])) {
            $docId = intval($_POST['docId']);
            if ($docId > 0) {
                $batch= Batches::model()->with('user')->with('client')->with('project')->findByPk($docId);

                $data['id']=$batch->Batch_ID;
                $data['First_Name']=$batch->user->person->First_Name;
                $data['Last_Name']=$batch->user->person->Last_Name;
                $data['user_name']=$batch->user->person->First_Name." ".$batch->user->person->Last_Name;
                $data['created']=$batch->Batch_Creation_Date;
                $data['amount']=$batch->Batch_Total;
                $data['company_name']=$batch->client->company->Company_Name;
                $data['type']=$batch->Batch_Export_Type;
                $data['format']=$batch->Batch_Export_Format;
                $data['description']="Some Description";
                $data["Project_Name"]=$batch->project->Project_Name;
                $data['export_link']='/documents/getbatchfiles?batch_id=' . $batch->Batch_ID . '&file=document';
                $data['report_link']='/documents/getbatchfiles?batch_id=' .$batch->Batch_ID . '&file=report';


                $this->renderPartial('application.views.library.tabs._info_block', array(
                    'data'=>$data,
                ));
            }
        }
    }

    /**
     * Updates flags Batch_Uploaded and Batch_Posted
     */
    public function actionUpdateBatchFlags()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['batch_id'])  && isset($_POST['checkbox_uploaded']) && isset($_POST['checkbox_posted'])) {

            $batch_id= intval($_POST['batch_id']);
            $checkbox_uploaded= intval($_POST['checkbox_uploaded']) == 1 ? 1 : 0;
            $checkbox_posted= intval($_POST['checkbox_posted']) == 1 ? 1 : 0;

            $batch= Batches::model()->findByPk($batch_id);

            if($batch){
                $batch->Batch_Uploaded = $checkbox_uploaded;
                $batch->Batch_Posted = $checkbox_posted;
                //var_dump($batch);die;
                //$batch->update();
                $batch->save();
            }
        }
    }


    /**
     * View folder documents action
     */
    public function actionViewStorage()
    {
        $this->layout='//layouts/library';
        if( isset($_POST['batches']) && count($_POST['batches'])>0 )
        {

            $batches =$_POST['batches'];
            //var_dump($batches);die;
            $this->render('view_storage', array(
                'batches' => $batches,

            ));
        } else {
            Yii::app()->user->setFlash('success', "Please choose batches to review!");
            $this->redirect('/library');
        }

    }


    /**
     * Get document view action
     */
    public function actionGetBatchViewForLibrary()
    {
        //var_dump($_POST);die;
        if (Yii::app()->request->isAjaxRequest && isset($_POST['batch_id']) ) {
            $batch_id = intval($_POST['batch_id']);
            $tabNum=intval($_POST['tab_num']);
            if ($batch_id > 0 ) {
                $this->renderPartial('lib_view', array(
                    'batchID'=>$batch_id ,
                    'tabNum'=>$tabNum,
                ));
            }
        }
    }




}