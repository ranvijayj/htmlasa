<?php

class W9sController extends Controller
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
            $w9Id = intval($_POST["id"]);
            $w9 = W9::model()->with('client')->findByPk($w9Id);

            if ($w9) {
                $client = $w9->client;
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

                $w9->Verified = $_POST["Verified"];
                $w9->Business_Name = $_POST["Business_Name"];
                $w9->Exempt = $_POST["Exempt"];
                $w9->Tax_Class = $_POST["Tax_Class"];
                $w9->Account_Nums = $_POST["Account_Nums"];
                $w9->Signed = $_POST["Account_Nums"];
                $w9->Signature_Date = $_POST["Signature_Date"] ? $_POST["Signature_Date"] : null;
                $w9->Revision_ID = $_POST["Revision_ID"];
                if ($w9->validate()) {
                    $w9->save();
                    echo "w9\n";
                }
            }

            die;
        }

        if (isset($_POST['oper']) && $_POST['oper'] == 'add') {
            die;
        }

        if (isset($_POST['oper']) && $_POST['oper'] == 'del') {
            $w9Id = intval($_POST["id"]);
            $w9 = W9::model()->findByPk($w9Id);
            if ($w9) {
                W9::deleteW9($w9Id);
            }
            die;
        }

        $conn = mysql_connect(Yii::app()->params->dbhost, Yii::app()->params->dbuser, Yii::app()->params->dbpassword);
        mysql_select_db(Yii::app()->params->dbname);
        mysql_query("SET NAMES 'utf8'");

        Yii::import('ext.phpgrid.inc.jqgrid');

        // set columns
        $col = array();
        $col["title"] = "W9 ID"; // caption of column
        $col["name"] = "W9_ID";
        $col["dbname"] = "w9.W9_ID"; // grid column name, same as db field or alias from sql
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
        $col["dbname"] = "w9.Document_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = false; // this column is editable
        $col["hidden"] = true;
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = false;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Business Name"; // caption of column
        $col["name"] = "Business_Name";
        $col["dbname"] = "w9.Business_Name"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = false;
        $cols[] = $col;

        $col = array();
        $col["title"] = "W9 Data Entry"; // caption of column
        $col["name"] = "W9_Data_Entry";
        $col["dbname"] = "w9.W9_Data_Entry"; // grid column name, same as db field or alias from sql
        $col["resizable"] = false;
        $col["editable"] = false; // this column is editable
        $col["hidden"] = true;
        $col["viewable"] = true;
        $col["search"] = false;
        $col["sortable"] = false;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Tax Class"; // caption of column
        $col["name"] = "Tax_Class";
        $col["dbname"] = "w9.Tax_Class"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["search"] = false;
        $col["edittype"] = "select";
        $col["editoptions"] = array("value"=>'0:0;SP:SP;C:C;CC:CC;CS:CS;PS:PS;TE:TE;LL:LL;LC:LC;LS:LS;LP:LP;OT:OT');
        $cols[] = $col;

        $col = array();
        $col["title"] = "Exempt"; // caption of column
        $col["name"] = "Exempt";
        $col["dbname"] = "w9.Exempt"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["edittype"] = "select";
        $col["search"] = false;
        $col["editoptions"] = array("value"=>'0:0;1:1');
        $cols[] = $col;

        $col = array();
        $col["title"] = "Account Nums"; // caption of column
        $col["name"] = "Account_Nums";
        $col["dbname"] = "w9.Account_Nums"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["search"] = false;
        $col["viewable"] = true;
        $cols[] = $col;

        $col = array();
        $col["title"] = "Signed"; // caption of column
        $col["name"] = "Signed";
        $col["dbname"] = "w9.Signed"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["edittype"] = "select";
        $col["search"] = false;
        $col["editoptions"] = array("value"=>'0:0;1:1');
        $cols[] = $col;

        $col = array();
        $col["title"] = "Signature Date"; // caption of column
        $col["name"] = "Signature_Date";
        $col["dbname"] = "w9.Signature_Date"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["search"] = false;
        $col["viewable"] = true;
        $col["formatter"] = "date";
        $col["formatoptions"] = array("srcformat"=>'Y-m-d',"newformat"=>'Y-m-d');
        $cols[] = $col;

        $col = array();
        $col["title"] = "Verified"; // caption of column
        $col["name"] = "Verified";
        $col["dbname"] = "w9.Verified"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["edittype"] = "select";
        $col["editoptions"] = array("value"=>'0:0;1:1');
        $cols[] = $col;

        $revisions = W9Revisions::model()->findAll();
        $revIds[] = '-1:-1';
        $revIds[] = '0:0';
        foreach ($revisions as $revision) {
            $revIds[] = $revision->Revision_ID . ':' . $revision->Revision_ID;
        }

        $col = array();
        $col["title"] = "Revision ID"; // caption of column
        $col["name"] = "Revision_ID";
        $col["dbname"] = "w9.Revision_ID"; // grid column name, same as db field or alias from sql
        $col["resizable"] = true;
        $col["editable"] = true; // this column is editable
        $col["viewable"] = true;
        $col["edittype"] = "select";
        $col["editoptions"] = array("value"=>implode(';', $revIds));
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

        $grid["caption"] = "W9";
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

        $g->select_command = "SELECT   companies.*, addresses.*,
                                       w9.*, images.File_Name, images.Mime_Type
                              FROM w9
                              LEFT JOIN documents ON documents.Document_ID = w9.Document_ID
                              LEFT JOIN images ON images.Document_ID = documents.Document_ID
                              LEFT JOIN clients ON clients.Client_ID = w9.Client_ID
                              LEFT JOIN companies ON clients.Company_ID = companies.Company_ID
                              LEFT JOIN company_addresses ON company_addresses.Company_ID = companies.Company_ID
                              LEFT JOIN addresses ON addresses.Address_ID = company_addresses.Address_ID";

        // set database table for CRUD operations
        $g->table = "w9";

        $g->set_columns($cols);

        // group columns header
        $g->set_group_header( array(
                "useColSpanStyle"=>true,
                "groupHeaders"=>array(
                    array(
                        "startColumnName"=>'W9_ID', // group starts from this column
                        "numberOfColumns"=>11, // group span to next 2 columns
                        "titleText"=>'W9 Information' // caption of group header
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
        $out = $g->render("w9");

        $this->render('index',array(
            'out'=>$out,
        ));
	}
}
