<?php
class FileController extends Controller
{
    public $layout='//layouts/column1';

    /**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

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
                'actions'=>array('index','print','rotate','ShowCanvas','ShowPdf'),
                'users' => array('admin', 'approver', 'processor', 'db_admin', 'client_admin'),
            ),

            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('UpdatePagesCount','ClearCache'),
                'users' => array('db_admin'),
            ),

            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }


    public function actionIndex(){


    }




    public function actionPdfJS(){
        $doc_id = urldecode($_GET['id']);
        $approved = $_GET['approved'];

        $result = $this->renderPartial('application.views.filemodification.viewer',array(
                'doc_id' => $doc_id,
                'approved' => $approved
            )
        );
    }


    public function actionShowCanvas(){
        if(Yii::app()->request->isAjaxRequest && $_POST['docId']){
            $doc_id= intval($_POST['docId']);

            if ($doc_id > 0 && Documents::hasAccess($doc_id)) {

               $result = $this->renderPartial('application.views.filemodification.prevnext',array (
                    'doc_id'=>$doc_id)
               );

            }
        }

    }


    public function actionRotate(){

        $result['success'] = false;

        if(Yii::app()->request->isAjaxRequest && $_POST['docID']){

            // start profiling
            //xhprof_enable(XHPROF_FLAGS_MEMORY);

            $doc_id= intval($_POST['docID']);

            $rotate_direction=strval ($_POST['action']);

            if ($doc_id > 0 && Documents::hasAccess($doc_id)) {

                                     $return_array=FileModification::prepareFile($doc_id);

                if($return_array['ext']!='pdf'){
                    $return_array = FileModification::ImageToPdf($return_array['path_to_dir'],$return_array['filename'],$return_array['ext']);
                }

                if(!$result['error']) {
                    $result=FileModification::rotateFile($return_array['path_to_dir'],$return_array['filename'],$rotate_direction);
                    $result['success'] = true;
                } else {
                    $result['success'] = false;
                    $result['error_message'] = "File was not rendered.";
                }


                if(!$result['error']) {
                $return_array = FileModification::writeToBase($return_array['path_to_dir'],$return_array['filename'],'application/pdf',$doc_id);

                    $result['file_id'] = FileCache::updateFileInCache($doc_id);

                    $result['success'] = true;

                } else {
                    $result['success'] = false;
                    $result['error_message'] = "File was not rendered.";
                }


            }

            // stop profiler
           /* $xhprof_data = xhprof_disable();
            include_once "/usr/share/php/xhprof_lib/utils/xhprof_lib.php";
            include_once "/usr/share/php/xhprof_lib/utils/xhprof_runs.php";
            $xhprof_runs = new XHProfRuns_Default();
            $run_id = $xhprof_runs->save_run($xhprof_data, "filemodification");
            */

        }

        echo CJSON::encode($result);
    }

    public function actionRotateNotSaved(){
        $result['success'] = false;
        if(Yii::app()->request->isAjaxRequest && $_POST['docID']){

            $filepath=strval($_POST['docID']);
            if(is_file($filepath)) {

                $path_parts = pathinfo($filepath);
                $return_array['ext']=$path_parts['extension'];
                $return_array['path_to_dir']=$path_parts['dirname'];
                $return_array['filename']=$path_parts['basename'];

                $rotate_direction=strval ($_POST['action']);

                if($return_array['ext']!='pdf'){
                    $return_array = FileModification::ImageToPdf($return_array['path_to_dir'],$return_array['filename'],$return_array['ext']);
                }

                if(!$result['error']) {
                    $result=FileModification::rotateFile($return_array['path_to_dir'],$return_array['filename'],$rotate_direction);
                    $result['success'] = true;
                } else {
                    $result['success'] = false;
                    $result['error_message'] = "File was not rendered.";
                }
            }
        }

        echo CJSON::encode($result);
    }





    /**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionZendPdf()
	{
            set_include_path( '/home/asaap/src/protected/extensions/' );

            require_once 'Zend/Pdf.php';
            require_once 'Zend/Pdf/Style.php';
            require_once 'Zend/Pdf/Color/Cmyk.php';
            require_once 'Zend/Pdf/Color/Html.php';
            require_once 'Zend/Pdf/Color/GrayScale.php';
            require_once 'Zend/Pdf/Color/Rgb.php';
            require_once 'Zend/Pdf/Page.php';
            require_once 'Zend/Pdf/Font.php';

            //$argv[1]='/home/asaap/src/protected/data/Pdf/test.pdf';
            $argv[1]='/home/asaap/src/protected/data/Pdf/document1.pdf';
            $argv[2]='/home/asaap/src/protected/data/Pdf/test_new.pdf';

            if (!isset($argv[1])) {
                echo "USAGE: php demo.php <pdf_file> [<output_pdf_file>]\n";
                exit;
            }

            try {
                $pdf = Zend_Pdf::load($argv[1]);
            } catch (Zend_Pdf_Exception $e) {
                if ($e->getMessage() == 'Can not open \'' . $argv[1] . '\' file for reading.') {
                    // Create new PDF if file doesn't exist
                    $pdf = new Zend_Pdf();

                    if (!isset($argv[2])) {
                        // force complete file rewriting (instead of updating)
                        $argv[2] = $argv[1];
                    }
                } else {
                    // Throw an exception if it's not the "Can't open file" exception
                    throw $e;

                }


            }

        //$pdf->pages = array_reverse($pdf->pages);
        // Add new page generated by Zend_Pdf object (page is attached to the specified the document)
        $pdf->pages[] = ($page1 = $pdf->newPage('A4'));

        //saving here

            $pdf->save($argv[2]);

        //    $pdf->save($argv[1], true /* update */);



	}

	/**
	 * This is the action to handle external exceptions.
	 */
    public function actionError()
        {
            if($error=Yii::app()->errorHandler->error)
            {

                if(Yii::app()->request->isAjaxRequest)
                    echo $error['message'];
                else
                    $this->render('error', $error);
            }
        }


   public function actionShowPdf()
    {
        if (isset($_GET['doc_id'])) {
            //var_dump($_GET);die;
            $doc_id = intval($_GET['doc_id']);
            /*$baseUrl = Yii::app()->baseUrl;
            $cs = Yii::app()->getClientScript();
            $cs->registerScriptFile($baseUrl.'/protected/views/filemodification.pdfjs.webvie.js');
            $cs->registerCssFile($baseUrl.'/css/yourcss.css');*/


            $this->renderPartial('application.views.filemodification.viewer',array(
                'doc_id'=>$doc_id
                )
            );
        }

    }

    public static function ShowPdf($doc_id,$mode)
    {
        //mode 1- old style output
        //     2 - new style output


        $url = '/documents/getdocumentfile?doc_id='.$doc_id;
        /*$baseUrl = Yii::app()->baseUrl;
        $cs = Yii::app()->getClientScript();
        $cs->registerScriptFile($baseUrl.'/protected/views/filemodification.pdfjs.webvie.js');
        $cs->registerCssFile($baseUrl.'/css/yourcss.css');*/

        if ($mode==2) {
            $result = renderPartial('application.views.filemodification.viewer',array(
                    '$url'=>$url
                ),true
            );
        }

        if ($mode==1) {


            $mime_type = Images::model()->findByAttributes(array(
                'Document_ID'=>$doc_id
            ))->Mime_Type;


            $result = FileController::renderPartial('application.views.filemodification.iframe',array(
                    'mime_type'=>$mime_type,
                    'doc_id'=>$doc_id
                ),true
            );
        }
        return $result;
    }

    public function actionClearCache() {
        FileCache::deleteCache('','');

        $this->redirect('/');
    }

}