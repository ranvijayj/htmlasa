<?php

class PosController extends Controller
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
            $poId = intval($_POST["id"]);
            $po = Pos::model()->with('vendor')->findByPk($poId);

            if ($po) {
                $vendor = $po->vendor;
                if ($vendor) {
                    $client = $vendor->client;
                    $company = $client->company;
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
                            echo "adresses\n";
                        }
                    }

                    if ($company) {
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
                }

                $po->PO_Account_Number = $_POST["PO_Account_Number"] ? $_POST["PO_Account_Number"] : null;
                $po->PO_Approval_Value = $_POST["PO_Approval_Value"];
                //$po->PO_Delivery_Chg = $_POST["PO_Delivery_Chg"] ? $_POST["PO_Delivery_Chg"] : null;
                //$po->PO_Other_Chg = $_POST["PO_Other_Chg"] ? $_POST["PO_Other_Chg"] : null;
                $po->Export_Batch_ID = $_POST["Export_Batch_ID"] ? $_POST["Export_Batch_ID"] : null;
                $po->PO_Subtotal = $_POST["PO_Subtotal"] ? $_POST["PO_Subtotal"] : null;
                $po->PO_Tax = $_POST["PO_Tax"] ? $_POST["PO_Tax"] : null;
                $po->PO_Total = $_POST["PO_Total"] ? $_POST["PO_Total"] : null;
                $po->PO_Date = $_POST["PO_Date"] ? $_POST["PO_Date"] : null;
                $po->PO_Previous_PO_Val = $_POST["PO_Previous_PO_Val"];
                $po->Payment_Type = $_POST["Payment_Type"];
                $po->PO_Card_Last_4_Digits = $_POST["PO_Card_Last_4_Digits"] ? $_POST["PO_Card_Last_4_Digits"] : null;
                $po->PO_Approved = $_POST["PO_Approved"];
                if ($po->validate()) {
                    $po->save();
                    echo "po\n";
                }

            }
            die;
        }

        if (isset($_POST['oper']) && $_POST['oper'] == 'add') {
            die;
        }

        if (isset($_POST['oper']) && $_POST['oper'] == 'del') {
            $apId = intval($_POST["id"]);
            $ap = Aps::model()->findByPk($apId);
            if ($ap) {
                Aps::deleteAP($apId);
            }
            die;
        }

        $conn = mysql_connect(Yii::app()->params->dbhost, Yii::app()->params->dbuser, Yii::app()->params->dbpassword);
        mysql_select_db(Yii::app()->params->dbname);
        mysql_query("SET NAMES 'utf8'");

        Yii::import('ext.phpgrid.inc.jqgrid');

        // set columns
        $col = array();
        $col["title"] = "PO ID"; // caption of column
        $col["name"] = "PO_ID";
        $col["dbname"] = "pos.PO_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = false; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = false;
        $cols[] = $col;

        // set columns
        $col = array();
        $col["title"] = "Document ID"; // caption of column
        $col["name"] = "Document_ID";
        $col["dbname"] = "pos.Document_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = false; // this column is editable
        $col["hidden"] = true;
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = false;
        $cols[] = $col;

        $approvalValues = array();
        for ($i = 0; $i <= 100; $i++) {
            $approvalValues[] = $i . ':' . $i;
        }

        $col = array();
        $col["title"] = "PO Approval Value"; // caption of column
        $col["name"] = "PO_Approval_Value";
        $col["dbname"] = "pos.PO_Approval_Value"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $col["edittype"] = "select";
        $col["editoptions"] = array("value"=>implode(';', $approvalValues));
        $cols[] = $col;

        $col = array();
        $col["title"] = "PO Previous PO Val"; // caption of column
        $col["name"] = "PO_Previous_PO_Val";
        $col["dbname"] = "pos.PO_Previous_PO_Val"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = true; // this column is editable
        $col["hidden"] = false;
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
        $col["edittype"] = "select";
        $col["editoptions"] = array("value"=>implode(';', $approvalValues));
        $cols[] = $col;

        $col = array();
        $col["title"] = "PO Number"; // caption of column
        $col["name"] = "PO_Number";
        $col["dbname"] = "pos.PO_Number"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = false; // this column is editable
        $col["viewable"] = true;
        $col["search"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "PO Account Number"; // caption of column
        $col["name"] = "PO_Account_Number";
        $col["dbname"] = "pos.PO_Account_Number"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "PO Subtotal"; // caption of column
        $col["name"] = "PO_Subtotal";
        $col["dbname"] = "pos.PO_Subtotal"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = false;
        $cols[] = $col;

        /*$col = array();
        $col["title"] = "PO Tax"; // caption of column
        $col["name"] = "PO_Tax";
        $col["dbname"] = "pos.PO_Tax"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = false;
        $cols[] = $col;

        $col = array();
        $col["title"] = "PO Delivery Chg"; // caption of column
        $col["name"] = "PO_Delivery_Chg";
        $col["dbname"] = "pos.PO_Delivery_Chg"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = false;
        $cols[] = $col;

        $col = array();
        $col["title"] = "PO Other Chg"; // caption of column
        $col["name"] = "PO_Other_Chg";
        $col["dbname"] = "pos.PO_Other_Chg"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = false;
        $cols[] = $col;*/

        $col = array();
        $col["title"] = "Export Batch ID"; // caption of column
        $col["name"] = "Export_Batch_ID";
        $col["dbname"] = "pos.Export_Batch_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["search"] = true;
        $col["viewable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "PO Total"; // caption of column
        $col["name"] = "PO_Total";
        $col["dbname"] = "pos.PO_Total"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = false;
        $cols[] = $col;

        $col = array();
        $col["title"] = "PO Date"; // caption of column
        $col["name"] = "PO_Date";
        $col["dbname"] = "pos.PO_Date"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = true;
        $col["formatter"] = "date";
        $col["formatoptions"] = array("srcformat"=>'Y-m-d',"newformat"=>'Y-m-d');
        $cols[] = $col;

        $col = array();
        $col["title"] = "Payment Type"; // caption of column
        $col["name"] = "Payment_Type";
        $col["dbname"] = "pos.Payment_Type"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["edittype"] = "select";
        $col["search"] = true;
        $col["editoptions"] = array("value"=>'OA:OA;CC:CC;DP:DP;CK:CK;PC:PC');
        $cols[] = $col;


        $col = array();
        $col["title"] = "PO Card Last 4 Digits"; // caption of column
        $col["name"] = "PO_Card_Last_4_Digits";
        $col["dbname"] = "pos.PO_Card_Last_4_Digits"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Approved"; // caption of column
        $col["name"] = "PO_Approved";
        $col["dbname"] = "pos.PO_Approved"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["edittype"] = "select";
        $col["search"] = true;
        $col["editoptions"] = array("value"=>'0:0;1:1');
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

        // set columns
        $col = array();
        $col["title"] = "SSN"; // caption of column
        $col["name"] = "SSN";
        $col["dbname"] = "companies.SSN"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = true;
        $col["sortable"] = true;
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

        $grid["caption"] = "PO";
       // $grid["multiselect"] = true;
        $grid["autowidth"] = true;
        $grid["resizable"] = false;
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

        $g->select_command = "SELECT   companies.*, addresses.*,
                                       pos.*, images.File_Name, images.Mime_Type
                              FROM pos
                              LEFT JOIN documents ON documents.Document_ID = pos.Document_ID
                              LEFT JOIN images ON images.Document_ID = documents.Document_ID
                              LEFT JOIN vendors ON pos.Vendor_ID = vendors.Vendor_ID
                              LEFT JOIN clients ON clients.Client_ID = vendors.Vendor_Client_ID
                              LEFT JOIN companies ON clients.Company_ID = companies.Company_ID
                              LEFT JOIN company_addresses ON company_addresses.Company_ID = companies.Company_ID
                              LEFT JOIN addresses ON addresses.Address_ID = company_addresses.Address_ID";

        // set database table for CRUD operations
        $g->table = "pos";

        $g->set_columns($cols);

        // group columns header
        $g->set_group_header( array(
                "useColSpanStyle"=>true,
                "groupHeaders"=>array(
                    array(
                        "startColumnName"=>'PO_ID', // group starts from this column
                        "numberOfColumns"=>13, // group span to next 13 columns
                        "titleText"=>'PO Information' // caption of group header
                    ),
                    array(
                        "startColumnName"=>'File_Name', // group starts from this column
                        "numberOfColumns"=>2, // group span to next 2 columns
                        "titleText"=>'Image Information' // caption of group header
                    ),
                    array(
                        "startColumnName"=>'Company_ID', // group starts from this column
                        "numberOfColumns"=>6, // group span to next 2 columns
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
        $out = $g->render("po");

        $this->render('index',array(
            'out'=>$out,
        ));
	}
}
