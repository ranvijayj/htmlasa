<?php

class VendorsController extends Controller
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
            $vendorId = intval($_POST["id"]);
            $vendor = Vendors::model()->with('client')->findByPk($vendorId);
            if ($vendor) {
                $client = $vendor->client;
                $company = $client->company;

                $vendor->Vendor_ID_Shortcut = $_POST["Vendor_ID_Shortcut"];
                $vendor->Vendor_Name_Checkprint = $_POST["Vendor_Name_Checkprint"];
                $vendor->Vendor_1099 = $_POST["Vendor_1099"];
                $vendor->Vendor_Default_GL = $_POST["Vendor_Default_GL"];
                $vendor->Vendor_Default_GL_Note = $_POST["Vendor_Default_GL_Note"];
                $vendor->Vendor_Note_General = $_POST["Vendor_Note_General"];
                $vendor->Active_Relationship = $_POST["Active_Relationship"];
                if ($vendor->validate()) {
                    $vendor->save();
                    echo "vendor\n";
                }

                if ($company->adreses) {
                    $addresses = $company->adreses;
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
                            echo "address\n";
                        }
                    }
                }

                $company->Company_Name = $_POST["Company_Name"];
                $company->Company_Fed_ID = $_POST["Company_Fed_ID"];
                $company->Email = $_POST["Email"];
                $company->SSN = $_POST["SSN"];
                $company->Business_NameW9 = $_POST["Business_NameW9"];
                if ($company->validate()) {
                    $company->save();
                    echo "company\n";
                }
            }
            die;
        }

        if (isset($_POST['oper']) && $_POST['oper'] == 'add') {
            die;
        }

        if (isset($_POST['oper']) && $_POST['oper'] == 'del') {
            $vendorId = intval($_POST["id"]);
            $vendor = Vendors::model()->findByPk($vendorId);
            if ($vendor) {
                $vendor->delete();
            }
            die;
        }

        $conn = mysql_connect(Yii::app()->params->dbhost, Yii::app()->params->dbuser, Yii::app()->params->dbpassword);
        mysql_select_db(Yii::app()->params->dbname);
        mysql_query("SET NAMES 'utf8'");

        Yii::import('ext.phpgrid.inc.jqgrid');

        // set columns
        $col = array();
        $col["title"] = "Vendor ID"; // caption of column
        $col["name"] = "Vendor_ID";
        $col["dbname"] = "vendors.Vendor_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = false; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = false;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Vendor Shortcut"; // caption of column
        $col["name"] = "Vendor_ID_Shortcut";
        $col["dbname"] = "vendors.Vendor_ID_Shortcut"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Vendor Checkprint"; // caption of column
        $col["name"] = "Vendor_Name_Checkprint";
        $col["dbname"] = "vendors.Vendor_Name_Checkprint"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Vendor 1099"; // caption of column
        $col["name"] = "Vendor_1099";
        $col["dbname"] = "vendors.Vendor_1099"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = true;
        $col["edittype"] = "select";
        $col["editoptions"] = array("value"=>'0:0;1:1');
        $cols[] = $col;

        $col = array();
        $col["title"] = "Client_Client_ID"; // caption of column
        $col["name"] = "Client_Client_ID";
        $col["dbname"] = "vendors.Client_Client_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = false; // this column is editable
        $col["viewable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Vendor Default GL"; // caption of column
        $col["name"] = "Vendor_Default_GL";
        $col["dbname"] = "vendors.Vendor_Default_GL"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Vendor GL Note"; // caption of column
        $col["name"] = "Vendor_Default_GL_Note";
        $col["dbname"] = "vendors.Vendor_Default_GL_Note"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = true;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Vendor Note General"; // caption of column
        $col["name"] = "Vendor_Note_General";
        $col["dbname"] = "vendors.Vendor_Note_General"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = true;
        $col["edittype"] = "textarea";
        $col["editoptions"] = array("cols"=>'20', 'rows' => '3');
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Active Relationship"; // caption of column
        $col["name"] = "Active_Relationship";
        $col["dbname"] = "vendors.Active_Relationship"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = true;
        $col["edittype"] = "select";
        $col["editoptions"] = array("value"=>'0:0;1:1');
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

        $col = array();
        $col["title"] = "Fed ID"; // caption of column
        $col["name"] = "Company_Fed_ID";
        $col["dbname"] = "companies.Company_Fed_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "SSN"; // caption of column
        $col["name"] = "SSN";
        $col["dbname"] = "companies.SSN"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Email"; // caption of column
        $col["name"] = "Email";
        $col["dbname"] = "companies.Email"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = false;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Business_NameW9"; // caption of column
        $col["name"] = "Business_NameW9";
        $col["dbname"] = "companies.Business_NameW9"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = false;
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

        $grid["caption"] = "Vendor List";
       // $grid["multiselect"] = true;
        $grid["autowidth"] = true;
        $grid["resizable"] = true;
        //$grid["toppager"] = true;
        $grid["sortname"] = 'companies.Company_Name';
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

        $g->select_command = "SELECT vendors.*, companies.*, addresses.*
                              FROM vendors
                              LEFT JOIN clients ON clients.Client_ID = vendors.Vendor_Client_ID
                              LEFT JOIN companies ON clients.Company_ID = companies.Company_ID
                              LEFT JOIN company_addresses ON company_addresses.Company_ID = companies.Company_ID
                              LEFT JOIN addresses ON addresses.Address_ID = company_addresses.Address_ID";

        // set database table for CRUD operations
        $g->table = "vendors";

        $g->set_columns($cols);

        // group columns header
        $g->set_group_header( array(
                "useColSpanStyle"=>true,
                "groupHeaders"=>array(
                    array(
                        "startColumnName"=>'Vendor_ID', // group starts from this column
                        "numberOfColumns"=>9, // group span to next 2 columns
                        "titleText"=>'Vendor Information' // caption of group header
                    ),
                    array(
                        "startColumnName"=>'Company_ID', // group starts from this column
                        "numberOfColumns"=>6, // group span to next 2 columns
                        "titleText"=>'Vendor Company' // caption of group header
                    ),
                    array(
                        "startColumnName"=>'Address1', // group starts from this column
                        "numberOfColumns"=>8, // group span to next 2 columns
                        "titleText"=>"Vendor's Address" // caption of group header
                    )
                )
            )
        );

        // render grid and get html/js output
        $out = $g->render("Vendors");

        $this->render('index',array(
            'out'=>$out,
        ));
	}
}