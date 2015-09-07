<?php

class QuestionsController extends Controller
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
            $question = UsersQuestions::model()->findByPk($id);
            if ($question) {

                $error_string= '';


                $question->Text = $_POST['Text'] ? $_POST["Text"] : ' ';


                if ($question->validate() && $error_string == '') {
                    $question->save();
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
            $question = new UsersQuestions();
            $question->Text = $_POST['Text'] ? $_POST["Text"] : ' ';

            if ($question->validate() && $error_string == '') {
                $question->save();
                echo "video\n";
            } else {
                die($error_string);
            }

        die;
        }

        if (isset($_POST['oper']) && $_POST['oper'] == 'del') {

            $id = intval($_POST["id"]);
            $question = UsersQuestions::model()->findByPk($id);
            if ($question) {
                    $question->delete();
            }

            die;


        }

        $conn = mysql_connect(Yii::app()->params->dbhost, Yii::app()->params->dbuser, Yii::app()->params->dbpassword);
        mysql_select_db(Yii::app()->params->dbname);
        mysql_query("SET NAMES 'utf8'");

        Yii::import('ext.phpgrid.inc.jqgrid');

        // set columns
        $col = array();
        $col["title"] = "Question_ID"; // caption of column
        $col["name"] = "Question_ID";
        $col["dbname"] = "Question_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = false; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Text"; // caption of column
        $col["name"] = "Text";
        $col["dbname"] = "Text"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        $g = new jqgrid();

        $grid["caption"] = "Security questions";
        // $grid["multiselect"] = true;
        $grid["autowidth"] = true;
        $grid["resizable"] = true;
        //$grid["toppager"] = true;
        $grid["sortname"] = 'Question_ID';
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

        $g->select_command = "SELECT users_questions.*
                              FROM users_questions";

        // set database table for CRUD operations
        $g->table = "users_questions";

        $g->set_columns($cols);

        // group columns header
        $g->set_group_header( array(
                "useColSpanStyle"=>true,
                "groupHeaders"=>array(
                    array(
                        "startColumnName"=>'Question_ID', // group starts from this column
                        "numberOfColumns"=>2, // group span to next 2 columns
                        "titleText"=>'Questions' // caption of group header
                    ),
                )
            )
        );

        // render grid and get html/js output
        $out = $g->render("Questions");

        $this->render('index',array(
            'out'=>$out,
        ));
    }
}
