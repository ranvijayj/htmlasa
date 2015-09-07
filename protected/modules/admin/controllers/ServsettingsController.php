<?php

class ServsettingsController extends Controller
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
            $setID = intval($_POST["id"]);
            $set = ServiceLevelSettings::model()->findByPk($setID);

            if ($set) {
                $set->Tier_Name = $_POST["Tier_Name"] ? $_POST["Tier_Name"] : null;
                $set->Users_Count = $_POST["Users_Count"] ? $_POST["Users_Count"] : null;
                $set->Projects_Count = $_POST["Projects_Count"] ? $_POST["Projects_Count"] : null;
                $set->Storage_Count = $_POST["Storage_Count"] ? $_POST["Storage_Count"] : null;
                $set->Base_Fee = $_POST["Base_Fee"] ? $_POST["Base_Fee"] : null;
                $set->Additional_User_Fee = $_POST["Additional_User_Fee"] ? $_POST["Additional_User_Fee"] : null;
                $set->Additional_Project_Fee = $_POST["Additional_Project_Fee"] ? $_POST["Additional_Project_Fee"] : null;
                $set->Additional_Storage_Fee = $_POST["Additional_Storage_Fee"] ? $_POST["Additional_Storage_Fee"] : null;
                $set->Trial_Period = $_POST["Trial_Period"] ? $_POST["Trial_Period"] : null;
                $set->Description = $_POST["Description"] ? $_POST["Description"] : null;
                if ($set->validate()) {
                    $set->save();
                    echo "settings\n";
                }
            }

            die;
        }

        if (isset($_POST['oper']) && $_POST['oper'] == 'add') {
            die;
        }

        if (isset($_POST['oper']) && $_POST['oper'] == 'del') {
            die;
        }

        $conn = mysql_connect(Yii::app()->params->dbhost, Yii::app()->params->dbuser, Yii::app()->params->dbpassword);
        mysql_select_db(Yii::app()->params->dbname);
        mysql_query("SET NAMES 'utf8'");

        Yii::import('ext.phpgrid.inc.jqgrid');

        // set columns
        $col = array();
        $col["title"] = "Service Level ID"; // caption of column
        $col["name"] = "Service_Level_ID";
        $col["dbname"] = "Service_Level_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = false; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Tier Name"; // caption of column
        $col["name"] = "Tier_Name";
        $col["dbname"] = "Tier_Name"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Users Count"; // caption of column
        $col["name"] = "Users_Count";
        $col["dbname"] = "Users_Count"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Projects Count"; // caption of column
        $col["name"] = "Projects_Count";
        $col["dbname"] = "Projects_Count"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Storage Count"; // caption of column
        $col["name"] = "Storage_Count";
        $col["dbname"] = "Storage_Count"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Base Fee"; // caption of column
        $col["name"] = "Base_Fee";
        $col["dbname"] = "Base_Fee"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Additional User Fee"; // caption of column
        $col["name"] = "Additional_User_Fee";
        $col["dbname"] = "Additional_User_Fee"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Additional Project Fee"; // caption of column
        $col["name"] = "Additional_Project_Fee";
        $col["dbname"] = "Additional_Project_Fee"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Additional Storage Fee"; // caption of column
        $col["name"] = "Additional_Storage_Fee";
        $col["dbname"] = "Additional_Storage_Fee"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Trial_Period"; // caption of column
        $col["name"] = "Trial_Period";
        $col["dbname"] = "Trial_Period"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Description"; // caption of column
        $col["name"] = "Description";
        $col["dbname"] = "Description"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = false;
        $col["edittype"] = "textarea";
        $col["editoptions"] = array("rows"=>15, "cols"=>80);
        $cols[] = $col;


        $g = new jqgrid();

        $grid["caption"] = "Service Level Settings";
        // $grid["multiselect"] = true;
        $grid["autowidth"] = true;
        $grid["resizable"] = true;
        //$grid["toppager"] = true;
        $grid["sortname"] = 'Service_Level_ID';
        $grid["sortorder"] = "ASC";
        $grid["add_options"] = array(
            'width'=>'600',
            "closeAfterEdit"=>true, // close dialog after add/edit
            "top"=>"200", // absolute top position of dialog
            "left"=>"200" // absolute left position of dialog
        );

        $g->set_options($grid);

        $g->set_actions(array(
                "add"=>true, // allow/disallow add
                "edit"=>true, // allow/disallow edit
                "delete"=>false, // allow/disallow delete
                "rowactions"=>true, // show/hide row wise edit/del/save option
                "export"=>true, // show/hide export to excel option
                "autofilter" => true, // show/hide autofilter for search
                "search" => "advance" // show single/multi field search condition (e.g. simple or advance)
            )
        );

        $g->select_command = "SELECT service_level_settings.*
                              FROM service_level_settings";

        // set database table for CRUD operations
        $g->table = "service_level_settings";

        $g->set_columns($cols);

        // group columns header
        $g->set_group_header( array(
                "useColSpanStyle"=>true,
                "groupHeaders"=>array(
                    array(
                        "startColumnName"=>'Service_Level_ID', // group starts from this column
                        "numberOfColumns"=>11, // group span to next 2 columns
                        "titleText"=>'Service Level Settings Information' // caption of group header
                    ),
                )
            )
        );

        // render grid and get html/js output
        $out = $g->render("service_level_settings");

        $this->render('index',array(
            'out'=>$out,
        ));
    }
}
