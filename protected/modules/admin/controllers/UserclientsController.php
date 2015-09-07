<?php

class UserclientsController extends Controller
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
            $ids = explode(',', $_POST["id"]);
            $userId = $ids[0];
            $clientId = $ids[1];

            if (is_numeric($userId) && is_numeric($clientId)) {
                $userClientRow = UsersClientList::model()->with('client', 'user')->findByAttributes(array(
                    'User_ID' => $userId,
                    'Client_ID' => $clientId,
                ));

                if ($userClientRow) {
                    $client = $userClientRow->client;
                    $company = $client->company;
                    $user = $userClientRow->user;
                    $person = $user->person;
                    $addresses = $company->adreses;

                    $userClientRow->User_Type = $_POST["User_Type"];
                    $userClientRow->User_Approval_Value = intval($_POST["User_Approval_Value"]);
                    if ($userClientRow->validate()) {
                        $userClientRow->save();
                        echo "UsersClientList\n";
                    }

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

                    if ($company) {
                        $company->Company_Name = $_POST["Company_Name"];
                        if ($company->validate()) {
                            $company->save();
                            echo "company\n";
                        }
                    }

                    $user->User_Login = $_POST["User_Login"];
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
            }

            die;
        }

        if (isset($_POST['oper']) && $_POST['oper'] == 'add') {
            die;
        }

        if (isset($_POST['oper']) && $_POST['oper'] == 'del') {
            $ids = explode(',', $_POST["id"]);
            $userId = $ids[0];
            $clientId = $ids[1];
            if (is_numeric($userId) && is_numeric($clientId)) {
                $userClientRow = UsersClientList::model()->with('client', 'user')->findByAttributes(array(
                    'User_ID' => $userId,
                    'Client_ID' => $clientId,
                ));

                if ($userClientRow) {
                    $userClientRow->delete();
                    UsersProjectList::model()->deleteAllByAttributes(array(
                        'User_ID' => $userId,
                        'Client_ID' => $clientId,
                    ));
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
        $col["title"] = "Joined Key (User_ID, Client_ID)"; // caption of column
        $col["name"] = "joined_key";
        $col["dbname"] = "joined_key"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = false; // this column is editable
        $col["hidden"] = true;
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = false;
        $cols[] = $col;

        $col = array();
        $col["title"] = "User Type"; // caption of column
        $col["name"] = "User_Type";
        $col["dbname"] = "users_client_list.User_Type"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["edittype"] = "select";
        $col["editoptions"] = array("value"=>'User:User;Approver:Approver;Processor:Processor;Client Admin:Client Admin');
        $cols[] = $col;

        $approvalValues = array();
        for ($i = 0; $i <= 100; $i++) {
            $approvalValues[] = $i . ':' . $i;
        }

        $col = array();
        $col["title"] = "User Approval Value"; // caption of column
        $col["name"] = "User_Approval_Value";
        $col["dbname"] = "users_client_list.User_Approval_Value"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = true;
        $col["edittype"] = "select";
        $col["editoptions"] = array("value"=>implode(';', $approvalValues));
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "User ID"; // caption of column
        $col["name"] = "User_ID";
        $col["dbname"] = "users.User_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = false; // this column is editable
        $col["hidden"] = false;
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
        $col["title"] = "Company ID"; // caption of column
        $col["name"] = "Company_ID";
        $col["dbname"] = "companies.Company_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = false; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = false;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Company Name"; // caption of column
        $col["name"] = "Company_Name";
        $col["dbname"] = "companies.Company_Name"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
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

        $grid["caption"] = "Client/User List";
       // $grid["multiselect"] = true;
        $grid["autowidth"] = true;
        $grid["resizable"] = true;
        //$grid["toppager"] = true;
        $grid["sortname"] = 'users.User_ID';
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

        $g->select_command = "SELECT   concat(users_client_list.User_ID, ',', users_client_list.Client_ID) as joined_key,
                                       users_client_list.User_Type, users_client_list.User_Approval_Value,
                                       companies.Company_Name, companies.Company_ID, addresses.*, persons.*,
                                       users.User_ID, users.Default_Project, users.User_Login, users.Last_Login,
                                       users.Active, users.Last_IP
                              FROM users_client_list
                              LEFT JOIN clients ON clients.Client_ID = users_client_list.Client_ID
                              LEFT JOIN companies ON clients.Company_ID = companies.Company_ID
                              LEFT JOIN users ON users.User_ID = users_client_list.User_ID
                              LEFT JOIN persons ON users.Person_ID = persons.Person_ID
                              LEFT JOIN company_addresses ON company_addresses.Company_ID = companies.Company_ID
                              LEFT JOIN addresses ON addresses.Address_ID = company_addresses.Address_ID";

        // set database table for CRUD operations
        $g->table = "users_client_list";

        $g->set_columns($cols);


        // group columns header
        $g->set_group_header( array(
                "useColSpanStyle"=>true,
                "groupHeaders"=>array(
                    array(
                        "startColumnName"=>'User_Type', // group starts from this column
                        "numberOfColumns"=>2, // group span to next 2 columns
                        "titleText"=>'User-Client Rel.' // caption of group header
                    ),
                    array(
                        "startColumnName"=>'User_ID', // group starts from this column
                        "numberOfColumns"=>5, // group span to next 2 columns
                        "titleText"=>'User Information' // caption of group header
                    ),
                    array(
                        "startColumnName"=>'First_Name', // group starts from this column
                        "numberOfColumns"=>6, // group span to next 2 columns
                        "titleText"=>'Person Information' // caption of group header
                    ),
                    array(
                        "startColumnName"=>'Company_ID', // group starts from this column
                        "numberOfColumns"=>2, // group span to next 2 columns
                        "titleText"=>'Company Information' // caption of group header
                    ),
                    array(
                        "startColumnName"=>'Address1', // group starts from this column
                        "numberOfColumns"=>8, // group span to next 2 columns
                        "titleText"=>"Company's Address" // caption of group header
                    )
                )
            )
        );

        // render grid and get html/js output
        $out = $g->render("Client_User_List");

        $this->render('index',array(
            'out'=>$out,
        ));
	}
}
