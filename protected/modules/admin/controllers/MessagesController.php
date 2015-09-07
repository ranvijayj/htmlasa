<?php

class MessagesController extends Controller
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
            $message = CustomMessages::model()->findByPk($id);
            if ($message) {

                $error_string= '';

                $message->Message_Text = $_POST['Message_Text'] ? $_POST["Message_Text"] : ' ';
                $message->Message_Type = $_POST['Message_Type'] ? $_POST["Message_Type"] : ' ';

                if ($message->validate() && $error_string == '') {
                    $message->save();
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
            $message = new CustomMessages();
            $message->Message_Text = $_POST['Message_Text'] ? $_POST["Message_Text"] : ' ';
            $message->Message_Type = $_POST['Message_Type'] ? $_POST["Message_Type"] : ' ';

            if ($message->validate() && $error_string == '') {
                $message->save();
                
            } else {
                die($error_string);
            }

        die;
        }

        if (isset($_POST['oper']) && $_POST['oper'] == 'del') {

            $id = intval($_POST["id"]);
            $message = CustomMessages::model()->findByPk($id);
            if ($message) {
                    $message->delete();
            }

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
        $col["dbname"] = "Message_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = false; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Message_Text"; // caption of column
        $col["name"] = "Message_Text";
        $col["dbname"] = "Message_Text"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Message_Type"; // caption of column
        $col["name"] = "Message_Type";
        $col["dbname"] = "Message_Type"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        $g = new jqgrid();

        $grid["caption"] = "Custom messages";
        // $grid["multiselect"] = true;
        $grid["autowidth"] = true;
        $grid["resizable"] = true;
        //$grid["toppager"] = true;
        $grid["sortname"] = 'Message_ID';
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

        $g->select_command = "SELECT custom_messages.*
                              FROM custom_messages";

        // set database table for CRUD operations
        $g->table = "custom_messages";

        $g->set_columns($cols);

        // group columns header
        $g->set_group_header( array(
                "useColSpanStyle"=>true,
                "groupHeaders"=>array(
                    array(
                        "startColumnName"=>'Message_ID', // group starts from this column
                        "numberOfColumns"=>2, // group span to next 2 columns
                        "titleText"=>'ID' // caption of group header
                    ),
                )
            )
        );

        // render grid and get html/js output
        $out = $g->render("Messages");

        $this->render('index',array(
            'out'=>$out,
        ));
    }
}
