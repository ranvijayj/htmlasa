<?php

class DocumentsController extends Controller
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
            $documentId = intval($_POST["id"]);
            $document = Documents::model()->with('images', 'user')->findByPk($documentId);

            if ($document) {
                $user = $document->user;
                $image = $document->image;

                if ($user) {
                    $person = $user->person;
                    $addresses = $person->adresses;

                    if (isset($addresses[0])) {
                        $address = $addresses[0];
                        $address->Address1 =  $_POST["Address1"];
                        $address->Address2 =  $_POST["Address2"];
                        $address->City =  $_POST["City"];
                        $address->State =  $_POST["State"];
                        $address->ZIP =  $_POST["ZIP"];
                        $address->Country =  $_POST["Country"];
                        $address->Phone =  $_POST["Phone"];
                        $address->Fax =  $_POST["Fax"];
                        if ($address->validate()) {
                            $address->save();
                            echo "adresses\n";
                        }
                    }

                    $user->User_Login = $_POST["User_Login"];
                    $user->User_Type = $_POST["User_Type"];
                    $user->Last_Login = $_POST["Last_Login"] ? $_POST["Last_Login"] : null;
                    $user->Active = intval($_POST["Active"]);
                    if ($user->validate()) {
                        $user->save();
                        echo "user\n";
                    }

                    $person->First_Name = $_POST["First_Name"];
                    $person->Last_Name = $_POST["Last_Name"];
                    $person->Email = $_POST["Email"];
                    $person->Mobile_Phone = $_POST["Mobile_Phone"];
                    $person->Direct_Phone = $_POST["Direct_Phone"];
                    $person->Direct_Fax = $_POST["Direct_Fax"];
                    if ($person->validate()) {
                        $person->save();
                        echo "person\n";
                    }
                }

                $document->Created = $_POST["Created"];
                if ($document->validate()) {
                    $document->save();
                    echo "document\n";
                }
/*
                $image->File_Name = $_POST["File_Name"];
                if ($image->validate()) {
                    $image->save();
                    echo "image\n";
                }
*/
            }

            die;
        }

        if (isset($_POST['oper']) && $_POST['oper'] == 'add') {
            die;
        }

        if (isset($_POST['oper']) && $_POST['oper'] == 'del') {
            $documentId = intval($_POST["id"]);
            $document = Documents::model()->findByPk($documentId);
            if ($document) {
                Documents::deleteDocument($documentId);
            }
            die;
        }

        $conn = mysql_connect(Yii::app()->params->dbhost, Yii::app()->params->dbuser, Yii::app()->params->dbpassword);
        mysql_select_db(Yii::app()->params->dbname);
        mysql_query("SET NAMES 'utf8'");

        Yii::import('ext.phpgrid.inc.jqgrid');

        // set columns
        $col = array();
        $col["title"] = "Document ID"; // caption of column
        $col["name"] = "Document_ID";
        $col["dbname"] = "documents.Document_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = false; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = false;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Document Type"; // caption of column
        $col["name"] = "Document_Type";
        $col["dbname"] = "documents.Created"; // grid column name, same as db field or alias from sql
        $col["editable"] = false; // this column is editable
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Created"; // caption of column
        $col["name"] = "Created";
        $col["dbname"] = "documents.Created"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "File Name"; // caption of column
        $col["name"] = "File_Name";
        $col["dbname"] = "images.File_Name"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = false; // this column is editable
        $col["viewable"] = true;
        $col["search"] = true;
        $col["default"] = "<span class='image_view' data='{Document_ID}'>{File_Name}</span>";
        $cols[] = $col;

        $col = array();
        $col["title"] = "Mime Type"; // caption of column
        $col["name"] = "Mime_Type";
        $col["dbname"] = "images.Mime_Type"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = false; // this column is editable
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "User ID"; // caption of column
        $col["name"] = "User_ID";
        $col["dbname"] = "documents.User_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = false; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = false;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Default Project"; // caption of column
        $col["name"] = "Default_Project";
        $col["dbname"] = "users.Default_Project"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = false; // this column is editable
        $col["hidden"] = true;
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = false;
        $cols[] = $col;

        $col = array();
        $col["title"] = "User Login"; // caption of column
        $col["name"] = "User_Login";
        $col["dbname"] = "users.User_Login"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "User Type"; // caption of column
        $col["name"] = "User_Type";
        $col["dbname"] = "users.User_Type"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["edittype"] = "select";
        $col["editoptions"] = array("value"=>'User:User;Admin:Admin;DB Admin:DB Admin;Data Entry Clerk:Data Entry Clerk');
        $cols[] = $col;

        $col = array();
        $col["title"] = "Last Login"; // caption of column
        $col["name"] = "Last_Login";
        $col["dbname"] = "users.Last_Login"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Active"; // caption of column
        $col["name"] = "Active";
        $col["dbname"] = "users.Active"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = true;
        $col["edittype"] = "select";
        $col["editoptions"] = array("value"=>'0:0;1:1');
        $cols[] = $col;

        $col = array();
        $col["title"] = "Last_IP"; // caption of column
        $col["name"] = "Last_IP";
        $col["dbname"] = "users.Last_IP"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = false; // this column is editable
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Person ID"; // caption of column
        $col["name"] = "Person_ID";
        $col["dbname"] = "persons.Person_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = false; // this column is editable
        $col["hidden"] = true;
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = false;
        $cols[] = $col;

        $col = array();
        $col["title"] = "First Name"; // caption of column
        $col["name"] = "First_Name";
        $col["dbname"] = "persons.First_Name"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Last Name"; // caption of column
        $col["name"] = "Last_Name";
        $col["dbname"] = "persons.Last_Name"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Email"; // caption of column
        $col["name"] = "Email";
        $col["dbname"] = "persons.Email"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Mobile Phone"; // caption of column
        $col["name"] = "Mobile_Phone";
        $col["dbname"] = "persons.Mobile_Phone"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Direct Phone"; // caption of column
        $col["name"] = "Direct_Phone";
        $col["dbname"] = "persons.Direct_Phone"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = false;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Direct Fax"; // caption of column
        $col["name"] = "Direct_Fax";
        $col["dbname"] = "persons.Direct_Fax"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = false;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Address ID"; // caption of column
        $col["name"] = "Address_ID";
        $col["dbname"] = "addresses.Address_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = false; // this column is editable
        $col["hidden"] = true;
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = false;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Address1"; // caption of column
        $col["name"] = "Address1";
        $col["dbname"] = "addresses.Address1"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Address2"; // caption of column
        $col["name"] = "Address2";
        $col["dbname"] = "addresses.Address2"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "City"; // caption of column
        $col["name"] = "City";
        $col["dbname"] = "addresses.City"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "State"; // caption of column
        $col["name"] = "State";
        $col["dbname"] = "addresses.State"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "ZIP"; // caption of column
        $col["name"] = "ZIP";
        $col["dbname"] = "addresses.ZIP"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Country"; // caption of column
        $col["name"] = "Country";
        $col["dbname"] = "addresses.Country"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Phone"; // caption of column
        $col["name"] = "Phone";
        $col["dbname"] = "addresses.Phone"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Fax"; // caption of column
        $col["name"] = "Fax";
        $col["dbname"] = "addresses.Fax"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = false;
        $cols[] = $col;

        $g = new jqgrid();

        $grid["caption"] = "Documents";
       // $grid["multiselect"] = true;
        $grid["autowidth"] = true;
        $grid["resizable"] = true;
        //$grid["toppager"] = true;
        $grid["sortname"] = 'documents.Document_ID';
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

        $g->select_command = "SELECT   addresses.*, persons.*, users.Default_Project,
                                       users.User_Login, users.User_Type, users.Last_Login, users.Active,
                                       users.Last_IP, documents.*, images.File_Name, images.Mime_Type
                              FROM documents
                              LEFT JOIN images ON images.Document_ID = documents.Document_ID
                              LEFT JOIN users ON users.User_ID = documents.User_ID
                              LEFT JOIN persons ON users.Person_ID = persons.Person_ID
                              LEFT JOIN person_addresses ON person_addresses.Person_ID = persons.Person_ID
                              LEFT JOIN addresses ON addresses.Address_ID = person_addresses.Address_ID";

        // set database table for CRUD operations
        $g->table = "documents";

        $g->set_columns($cols);

        // group columns header
        $g->set_group_header( array(
                "useColSpanStyle"=>true,
                "groupHeaders"=>array(
                    array(
                        "startColumnName"=>'Document_ID', // group starts from this column
                        "numberOfColumns"=>3, // group span to next 2 columns
                        "titleText"=>'Document Information' // caption of group header
                    ),
                    array(
                        "startColumnName"=>'File_Name', // group starts from this column
                        "numberOfColumns"=>2, // group span to next 2 columns
                        "titleText"=>'Image Information' // caption of group header
                    ),
                    array(
                        "startColumnName"=>'User_ID', // group starts from this column
                        "numberOfColumns"=>7, // group span to next 2 columns
                        "titleText"=>'User Information' // caption of group header
                    ),
                    array(
                        "startColumnName"=>'First_Name', // group starts from this column
                        "numberOfColumns"=>6, // group span to next 2 columns
                        "titleText"=>'Person Information' // caption of group header
                    ),
                    array(
                        "startColumnName"=>'Address1', // group starts from this column
                        "numberOfColumns"=>8, // group span to next 2 columns
                        "titleText"=>"User's Address" // caption of group header
                    )
                )
            )
        );

        // render grid and get html/js output
        $out = $g->render("documents");

        $this->render('index',array(
            'out'=>$out,
        ));
	}
}
