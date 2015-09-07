<?php

class MailsController extends Controller
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
            $mtID = intval($_POST["id"]);
            $mt = MailTemplates::model()->findByPk($mtID);

            if ($mt) {
                $mt->Title = $_POST["Title"] ? $_POST["Title"] : null;
                $mt->Message_Body = $_POST["Message_Body"] ? $_POST["Message_Body"] : null;
                if ($mt->validate()) {
                    $mt->save();
                    echo "mails\n";
                }
            }

            die;
        }

        if (isset($_POST['oper']) && $_POST['oper'] == 'add') {

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
        $col["title"] = "Message_ID"; // caption of column
        $col["name"] = "Message_ID";
        $col["dbname"] = "mail_templates.Message_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = false; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Title"; // caption of column
        $col["name"] = "Title";
        $col["dbname"] = "mail_templates.Title"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Message Body"; // caption of column
        $col["name"] = "Message_Body";
        $col["dbname"] = "mail_templates.Message_Body"; // grid column name, same as db field or alias from sql
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

        $grid["caption"] = "Mail Templates";
        // $grid["multiselect"] = true;
        $grid["autowidth"] = true;
        $grid["resizable"] = true;
        //$grid["toppager"] = true;
        $grid["sortname"] = 'mail_templates.Message_ID';
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

        $g->select_command = "SELECT mail_templates.*
                              FROM mail_templates";

        // set database table for CRUD operations
        $g->table = "mail_templates";

        $g->set_columns($cols);

        // group columns header
        $g->set_group_header( array(
                "useColSpanStyle"=>true,
                "groupHeaders"=>array(
                    array(
                        "startColumnName"=>'Note_ID', // group starts from this column
                        "numberOfColumns"=>3, // group span to next 2 columns
                        "titleText"=>'Mail Template Information' // caption of group header
                    ),
                )
            )
        );

        // render grid and get html/js output
        $out = $g->render("mail_templates");

        $this->render('index',array(
            'out'=>$out,
        ));
    }
}
