<?php

class NotesController extends Controller
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
            $noteId = intval($_POST["id"]);
            $note = Notes::model()->findByPk($noteId);

            if ($note) {
                $note->Comment = $_POST["Comment"] ? $_POST["Comment"] : null;
                $note->Created = $_POST["Created"] ? $_POST["Created"] : null;
                if ($note->validate()) {
                    $note->save();
                    echo "note\n";
                }
            }

            die;
        }

        if (isset($_POST['oper']) && $_POST['oper'] == 'add') {
            die;
        }

        if (isset($_POST['oper']) && $_POST['oper'] == 'del') {
            $noteId = intval($_POST["id"]);
            $note = Notes::model()->findByPk($noteId);
            if ($note) {
                $note->delete();
            }
            die;
        }

        $conn = mysql_connect(Yii::app()->params->dbhost, Yii::app()->params->dbuser, Yii::app()->params->dbpassword);
        mysql_select_db(Yii::app()->params->dbname);
        mysql_query("SET NAMES 'utf8'");

        Yii::import('ext.phpgrid.inc.jqgrid');

        // set columns
        $col = array();
        $col["title"] = "Note ID"; // caption of column
        $col["name"] = "Note_ID";
        $col["dbname"] = "notes.Note_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = false; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Document ID"; // caption of column
        $col["name"] = "Document_ID";
        $col["dbname"] = "notes.Document_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = false; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "User ID"; // caption of column
        $col["name"] = "User_ID";
        $col["dbname"] = "notes.User_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = false; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Company ID"; // caption of column
        $col["name"] = "Company_ID";
        $col["dbname"] = "notes.Company_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = false; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Client ID"; // caption of column
        $col["name"] = "Client_ID";
        $col["dbname"] = "notes.Client_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = false; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Comment"; // caption of column
        $col["name"] = "Comment";
        $col["dbname"] = "notes.Comment"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Created"; // caption of column
        $col["name"] = "Created";
        $col["dbname"] = "notes.Created"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = false;
        $cols[] = $col;


        $g = new jqgrid();

        $grid["caption"] = "Notes";
        // $grid["multiselect"] = true;
        $grid["autowidth"] = true;
        $grid["resizable"] = true;
        //$grid["toppager"] = true;
        $grid["sortname"] = 'notes.Note_ID';
        $grid["sortorder"] = "ASC";
        $grid["add_options"] = array(
            'width'=>'420',
            "closeAfterEdit"=>true, // close dialog after add/edit
            "top"=>"200", // absolute top position of dialog
            "left"=>"200" // absolute left position of dialog
        );

        $g->set_options($grid);

        $g->set_actions(array(
                "add"=>false, // allow/disallow add
                "edit"=>true, // allow/disallow edit
                "delete"=>true, // allow/disallow delete
                "rowactions"=>true, // show/hide row wise edit/del/save option
                "export"=>true, // show/hide export to excel option
                "autofilter" => true, // show/hide autofilter for search
                "search" => "advance" // show single/multi field search condition (e.g. simple or advance)
            )
        );

        $g->select_command = "SELECT notes.*
                              FROM notes";

        // set database table for CRUD operations
        $g->table = "notes";

        $g->set_columns($cols);

        // group columns header
        $g->set_group_header( array(
                "useColSpanStyle"=>true,
                "groupHeaders"=>array(
                    array(
                        "startColumnName"=>'Note_ID', // group starts from this column
                        "numberOfColumns"=>7, // group span to next 2 columns
                        "titleText"=>'Note Information' // caption of group header
                    ),
                )
            )
        );

        // render grid and get html/js output
        $out = $g->render("notes");

        $this->render('index',array(
            'out'=>$out,
        ));
    }
}
