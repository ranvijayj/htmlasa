<?php

class VideosController extends Controller
{
    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public $layout='//layouts/admin_db';

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
            array('allow',  // allow all users to perform 'index' and 'view' actions
                'actions'=>array('index'),
                'users'=>array('db_admin'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    /**
     * Lists all models.
     */
    public function actionIndex()
    {
        if (isset($_POST['oper']) && $_POST['oper'] == 'edit') {
            $id = intval($_POST["id"]);
            $video = Videos::model()->findByPk($id);
            if ($video) {

                $error_string= '';

                $video->Video_Title =$_POST['Video_Title'] ? $_POST["Video_Title"] : null;
                $video->Video_Log_Line =$_POST['Video_Log_Line'] ? $_POST["Video_Log_Line"] : null;
                $video->Video_Desc =$_POST['Video_Desc'] ? $_POST["Video_Desc"] : null;
                $video->Link_Title =$_POST['Link_Title'] ? $_POST["Link_Title"] : null;
                $video->Sort_Order =$_POST['Sort_Order'] ? $_POST["Sort_Order"] : null;
                $video->Video_URL =$_POST['Video_URL'] ? addslashes($_POST['Video_URL']) : null;
                $video->Video_Password =$_POST['Video_Password'] ? $_POST["Video_Password"] : null;
                $video->Visibility =$_POST['Visibility'] ? $_POST["Visibility"] : null;

                if (intval($_POST['Visibility'])==4) {
                    $client_id = intval($_POST['Clients_Client_ID']);
                    $client = Clients::model()->findByPk($client_id);

                    if (!$client) {
                        $error_string = 'Client_ID should be real';
                    }
                }

                if (intval($_POST['Visibility'])==5) {
                    $client_id = intval($_POST['Clients_Client_ID']);
                    $project_id = intval($_POST['Clients_Client_ID']);

                    $project = Projects::model()->findByAttributes(array(
                        'Project_ID'=>$project_id,
                        'Client_ID'=>$client_id
                    ));

                    if (!$project) {
                        $error_string = 'There is no project for such Client_ID and Project_ID';
                    }
                }
                $video->Clients_Client_ID =$_POST['Clients_Client_ID'] ? $_POST["Clients_Client_ID"] : null;
                $video->Project_ID =$_POST['Project_ID'] ? $_POST["Project_ID"] : null;



                if ($video->validate() && $error_string == '') {
                    $video->save();
                    echo "video\n";
                } else {
                    die($error_string);
                }
            }

            die;
        }

        if (isset($_POST['oper']) && $_POST['oper'] == 'add') {

           // var_dump($_POST);die;
            $error_string= '';

            $video = new Videos();

            $video->Video_Title =$_POST['Video_Title'];
            $video->Video_Log_Line =$_POST['Video_Log_Line'];
            $video->Video_Desc =$_POST['Video_Desc'];
            $video->Link_Title =$_POST['Link_Title'];
            $video->Video_URL =addslashes($_POST['Video_URL']);
            $video->Video_Password =$_POST['Video_Password'];
            $video->Sort_Order =$_POST['Sort_Order'] ? $_POST["Sort_Order"] : 1;

            if (intval($_POST['Visibility'])==4) {
                $client_id = intval($_POST['Clients_Client_ID']);
                $client = Clients::model()->findByPk($client_id);

                if (!$client) {
                    $error_string = 'Client_ID should be real';
                }
            }

            if (intval($_POST['Visibility'])==5) {
                $client_id = intval($_POST['Clients_Client_ID']);
                $project_id = intval($_POST['Clients_Client_ID']);

                $project = Projects::model()->findByAttributes(array(
                    'Project_ID'=>$project_id,
                    'Client_ID'=>$client_id
                ));

                if (!$project) {
                    $error_string = 'There is no project for such Client_ID and Project_ID';
                }
            }




            $video->Visibility =$_POST['Visibility'];
            $video->Clients_Client_ID =$_POST['Clients_Client_ID'];
            $video->Project_ID =$_POST['Project_ID'];

            if ($error_string == '') {
                $video->save();
            } else {
                die($error_string);
            }


        }

        if (isset($_POST['oper']) && $_POST['oper'] == 'del') {

            $video_id = intval($_POST["id"]);
            $video =Videos::model()->findByPk($video_id);
            if ($video) {
                $video->delete();
            }
            die;


        }

        $conn = mysql_connect(Yii::app()->params->dbhost, Yii::app()->params->dbuser, Yii::app()->params->dbpassword);
        mysql_select_db(Yii::app()->params->dbname);
        mysql_query("SET NAMES 'utf8'");

        Yii::import('ext.phpgrid.inc.jqgrid');

        // set columns
        $col = array();
        $col["title"] = "Video_ID"; // caption of column
        $col["name"] = "Video_ID";
        $col["dbname"] = "Video_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = false; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Video_Title"; // caption of column
        $col["name"] = "Video_Title";
        $col["dbname"] = "Video_Title"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Video_Log_Line"; // caption of column
        $col["name"] = "Video_Log_Line";
        $col["dbname"] = "Video_Log_Line"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Video_Desc"; // caption of column
        $col["name"] = "Video_Desc";
        $col["dbname"] = "Video_Desc"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Link_Title"; // caption of column
        $col["name"] = "Link_Title";
        $col["dbname"] = "Link_Title"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Video_URL"; // caption of column
        $col["name"] = "Video_URL";
        $col["dbname"] = "Video_URL"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Video_Password"; // caption of column
        $col["name"] = "Video_Password";
        $col["dbname"] = "Video_Password"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Sort_Order"; // caption of column
        $col["name"] = "Sort_Order";
        $col["dbname"] = "Sort_Order"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Visibility"; // caption of column
        $col["name"] = "Visibility";
        $col["dbname"] = "Visibility"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Clients_Client_ID"; // caption of column
        $col["name"] = "Clients_Client_ID";
        $col["dbname"] = "Clients_Client_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

         // set columns
        $col = array();
        $col["title"] = "Project_ID"; // caption of column
        $col["name"] = "Project_ID";
        $col["dbname"] = "Project_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;







        $g = new jqgrid();

        $grid["caption"] = "Videos";
        // $grid["multiselect"] = true;
        $grid["autowidth"] = true;
        $grid["resizable"] = true;
        //$grid["toppager"] = true;
        $grid["sortname"] = 'Video_ID';
        $grid["sortorder"] = "ASC";
        $grid["add_options"] = array(
            'width'=>'420',
            "closeAfterEdit"=>true, // close dialog after add/edit
            "top"=>"200", // absolute top position of dialog
            "left"=>"200" // absolute left position of dialog
        );

        $g->set_options($grid);

        $g->set_actions(array(
                "add"=>true, // allow/disallow add
                "edit"=>true, // allow/disallow edit
                "delete"=>true, // allow/disallow delete
                "rowactions"=>true, // show/hide row wise edit/del/save option
                "export"=>true, // show/hide export to excel option
                "autofilter" => true, // show/hide autofilter for search
                "search" => "advance" // show single/multi field search condition (e.g. simple or advance)
            )
        );

        $g->select_command = "SELECT Videos.*
                              FROM Videos";

        // set database table for CRUD operations
        $g->table = "Videos";

        $g->set_columns($cols);

        // group columns header
        $g->set_group_header( array(
                "useColSpanStyle"=>true,
                "groupHeaders"=>array(
                    array(
                        "startColumnName"=>'Video_ID', // group starts from this column
                        "numberOfColumns"=>4, // group span to next 2 columns
                        "titleText"=>'Videos' // caption of group header
                    ),
                )
            )
        );

        // render grid and get html/js output
        $out = $g->render("Videos");

        $this->render('index',array(
            'out'=>$out,
        ));
    }
}
