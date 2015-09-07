<?php

class RpsettingsController extends Controller
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
            $rps = RemoteProcessingSettings::model()->findByPk($id);

            if ($rps) {

                $error_string= '';
                $rps->PerPageFee =$_POST['PerPageFee'] ? $_POST["PerPageFee"] : null;
                $rps->PerSecondFee =$_POST['PerSecondFee'] ? $_POST["PerSecondFee"] : null;
                $rps->PaperPageFee =$_POST['PaperPageFee'] ? $_POST["PaperPageFee"] : null;
                $rps->PerMBFee =$_POST['PerMBFee'] ? $_POST["PerMBFee"] : null;
                $rps->SetupCharge =$_POST['SetupCharge'] ? $_POST["SetupCharge"] : null;


                if ($rps->validate() && $error_string == '') {
                    $rps->save();
                } else {
                   die($error_string);
                }
            }
            die;
        }





        $conn = mysql_connect(Yii::app()->params->dbhost, Yii::app()->params->dbuser, Yii::app()->params->dbpassword);
        mysql_select_db(Yii::app()->params->dbname);
        mysql_query("SET NAMES 'utf8'");

        Yii::import('ext.phpgrid.inc.jqgrid');


        // set columns
        $col = array();
        $col["title"] = "rps_id"; // caption of column
        $col["name"] = "rps_id";
        $col["dbname"] = "rps_id"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = false; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "SetupCharge"; // caption of column
        $col["name"] = "SetupCharge";
        $col["dbname"] = "SetupCharge"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "PerPageFee"; // caption of column
        $col["name"] = "PerPageFee";
        $col["dbname"] = "PerPageFee"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "PerSecondFee"; // caption of column
        $col["name"] = "PerSecondFee";
        $col["dbname"] = "PerSecondFee"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "PerMBFee"; // caption of column
        $col["name"] = "PerMBFee";
        $col["dbname"] = "PerMBFee"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "PaperPageFee"; // caption of column
        $col["name"] = "PaperPageFee";
        $col["dbname"] = "PaperPageFee"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = true;
        $cols[] = $col;




        $g = new jqgrid();

        $grid["caption"] = "RP settings";
        // $grid["multiselect"] = true;
        $grid["autowidth"] = true;
        $grid["resizable"] = true;
        //$grid["toppager"] = true;
        $grid["sortname"] = 'RPS_ID';
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

        $g->select_command = "SELECT remote_processing_settings.*
                              FROM remote_processing_settings";

        // set database table for CRUD operations
        $g->table = "remote_processing_settings";

        $g->set_columns($cols);

        // group columns header
        $g->set_group_header( array(
                "useColSpanStyle"=>true,
                "groupHeaders"=>array(
                    array(
                        "startColumnName"=>'PerPageFee', // group starts from this column
                        "numberOfColumns"=>4, // group span to next 2 columns
                        "titleText"=>'Remote processing Settings' // caption of group header
                    ),
                )
            )
        );

        // render grid and get html/js output
        $out = $g->render("Rpsettings");

        $this->render('index',array(
            'out'=>$out,
        ));
    }
}
