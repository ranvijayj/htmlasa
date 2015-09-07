<?php

/**
 * This is the model class for table "vendors".
 *
 * The followings are the available columns in table 'vendors':
 * @property integer $Vendor_ID
 * @property string $Vendor_ID_Shortcut
 * @property integer $Vendor_Client_ID
 * @property integer $Client_Client_ID
 * @property string $Vendor_Name_Checkprint
 * @property integer $Vendor_1099
 * @property string $Vendor_Default_GL
 * @property string $Vendor_Default_GL_Note
 * @property string $Vendor_Note_General
 * @property integer $Active_Relationship
 * @property integer $Vendor_Checkprint_Add1
 * @property integer $Vendor_Checkprint_Add2
 * @property integer $Vendor_Checkprint_City
 * @property integer $Vendor_Checkprint_ST
 * @property integer $Vendor_Checkprint_Zip
 * @property integer $Vendor_Checkprint_Country
 * @property string $Vendor_Contact
 * @property string $Vendor_Phone
 */

class Vendors extends CActiveRecord
{
    /**
     * Relationship types
     */
    const ACTIVE_RELATIONSHIP = 1;
    const NOT_ACTIVE_RELATIONSHIP = 0;


    /**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'vendors';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Vendor_Client_ID, Client_Client_ID', 'required'),
			array('Vendor_Client_ID, Client_Client_ID, Vendor_1099, Active_Relationship', 'numerical', 'integerOnly'=>true),
			array('Vendor_Checkprint_ST', 'length', 'max'=>4),
			array('Vendor_ID_Shortcut', 'length', 'max'=>5),
			array('Vendor_Name_Checkprint', 'length', 'max'=>50),
			array('Vendor_Checkprint_Zip', 'length', 'max'=>15),
			array('Vendor_Default_GL, Vendor_Default_GL_Note', 'length', 'max'=>40),
			array('Vendor_Note_General,Vendor_Contact,Vendor_Phone', 'length', 'max'=>255),
			array('Vendor_ID', 'safe'),
			array('Vendor_Checkprint_Add1,Vendor_Checkprint_Add2,Vendor_Checkprint_City,Vendor_Checkprint_Country', 'length', 'max'=>45),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Vendor_ID_Shortcut, Vendor_Client_ID, Client_Client_ID, Vendor_Name_Checkprint, Vendor_1099, Vendor_Default_GL, Vendor_Default_GL_Note, Vendor_Note_General, Active_Relationship', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'Vendor_ID' => 'Database generated unique Vendor number',
			'Vendor_ID_Shortcut' => 'Vendor Shortcut',
			'Vendor_Client_ID' => 'Vendor Comapny',
			'Client_Client_ID' => 'Client Comapny',
			'Vendor_Name_Checkprint' => 'Check Print',
			'Vendor_1099' => 'Vendor 1099',
			'Vendor_Default_GL' => 'Default GL Code',
			'Vendor_Default_GL_Note' => 'Default GL Note',
			'Vendor_Note_General' => 'General note about this vendor',
			'Active_Relationship' => 'Active Relationship',
            'Vendor_Contact' => 'Vendor Contact',
            'Vendor_Phone' => 'Vendor Phone',
		);
	}

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'client' => array(self::BELONGS_TO, 'Clients', 'Vendor_Client_ID'),
        );
    }



    /**
     * Get list of company's vendors
     * @return array|mixed|null
     */
    public static function getCompanyVendors($limit)
    {
        $condition = new CDbCriteria();
        $condition->addCondition("t.Client_Client_ID= '" . Yii::app()->user->clientID . "'");
        $condition->addCondition("t.Active_Relationship= '" . self::ACTIVE_RELATIONSHIP . "'");
        $condition->order = 'company.Company_Name ASC';
        $condition->limit = $limit;
        $vendors = Vendors::model()->with('client.company.adreses')->findAll($condition);

        return $vendors;
    }

    /**
     * Get list of External company vendors
     * @return array|CActiveRecord|CActiveRecord[]|mixed|null
     */
    public static function getExternalClients()
    {
        $client = Clients::model()->with('vendors_list.client')->findByPk(Yii::app()->user->clientID);
        if (isset($client->vendors_list)) {
            $clientVendors = $client->vendors_list;
        } else {
            $clientVendors = array();
        }

        $clientVendorsIds = array();
        foreach($clientVendors as $clientVendor) {
            $clientVendorsIds[] = $clientVendor->client->Client_ID;
        }
        $clientVendorsIds[] = Yii::app()->user->clientID;

        // get External Vendors list condition
        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN w9 ON w9.Client_ID = t.Client_ID";
        if (count($clientVendorsIds) > 0) {
            $condition->addNotInCondition("t.Client_ID", $clientVendorsIds);
        }
        $condition->addCondition("w9.W9_Owner_ID = '" . Yii::app()->user->clientID ."'");
        $condition->order = "Company_Name ASC";

        $clients = Clients::model()->with('company')->findAll($condition);
        return $clients;
    }

    /**
     * Get filtered list of company's vendors
     * @param $queryString
     * @param $options
     * @param $sortOptions
     * @param bool $externalVendors
     * @return array|CActiveRecord|CActiveRecord[]|mixed|null
     */
    public static function getListByQueryString($queryString, $options, $sortOptions, $limit,$offset,$externalVendors = false)
    {

        //var_dump($limit);die;
        $vendors = array();

        // get Vendors list condition
        $condition = new CDbCriteria();
        $condition1 = new CDbCriteria();
        $condition->join = "LEFT JOIN clients ON t.Vendor_Client_ID=clients.Client_ID
                            LEFT JOIN companies as com ON clients.Company_ID=com.Company_ID
                            LEFT JOIN company_addresses ON company_addresses.Company_ID = com.Company_ID
                            LEFT JOIN addresses ON addresses.Address_ID = company_addresses.Address_ID";

        $countCond = 0;

        if ($options['search_option_temporary']) {

            $condition1->addCondition("com.Temp_Fed_ID_Flag ='T' ");

        }
        if ($options['search_option_international']) {
            $condition1->addCondition("com.Temp_Fed_ID_Flag ='N' ");

        }


        if (count($options) > 0 && trim($queryString) != '') {
            if ($options['search_option_fed_id']) {
                $condition->compare('com.Company_Fed_ID', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_com_name']) {
                $condition->compare('com.Company_Name', $queryString, true, 'OR');
                $countCond++;
            }

            if (isset($options['search_option_shortcut']) && $options['search_option_shortcut']) {
                $condition->compare('t.Vendor_ID_Shortcut', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_addr1']) {
                $condition->compare('addresses.Address1', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_addr2']) {
                $condition->compare('addresses.Address2', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_city']) {
                $condition->compare('addresses.City', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_state']) {
                $condition->compare('addresses.State', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_zip']) {
                $condition->compare('addresses.ZIP', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_country']) {
                $condition->compare('addresses.Country', $queryString, true, 'OR');
                $countCond++;
            }
            if ($options['search_option_phone']) {
                $condition->compare('addresses.Phone', $queryString, true, 'OR');
                $countCond++;
            }

        //var_dump($queryString);die;

        }

        if ($countCond == 0 && trim($queryString) != '') {
            $condition->compare('com.Company_Fed_ID', $queryString, true, 'OR');
            $condition->compare('com.Company_Name', $queryString, true, 'OR');
            $condition->compare('addresses.Address1', $queryString, true, 'OR');
            $condition->compare('addresses.Address2', $queryString, true, 'OR');
            $condition->compare('addresses.City', $queryString, true, 'OR');
            $condition->compare('addresses.State', $queryString, true, 'OR');
            $condition->compare('addresses.ZIP', $queryString, true, 'OR');
            $condition->compare('addresses.Country', $queryString, true, 'OR');
            $condition->compare('addresses.Phone', $queryString, true, 'OR');
        }

        $condition->limit = $limit;
        $condition->offset=intval($offset);


        if ($externalVendors) {
            //if ($queryString != '' || $options['search_option_temporary'] || $options['search_option_international']) {
                $condition->join = "LEFT JOIN companies as com ON t.Company_ID=com.Company_ID
                                LEFT JOIN company_addresses ON company_addresses.Company_ID = com.Company_ID
                                LEFT JOIN addresses ON addresses.Address_ID = company_addresses.Address_ID
                                LEFT JOIN w9 ON w9.Client_ID = t.Client_ID";

                $client = Clients::model()->with('vendors_list.client')->findByPk(Yii::app()->user->clientID);
                if (isset($client->vendors_list)) {
                    $clientVendors = $client->vendors_list;
                    //var_dump($clientVendors);die;
                } else {
                    $clientVendors = array();
                }

                $clientVendorsIds = array();

                foreach($clientVendors as $clientVendor) {
                    $clientVendorsIds[] = $clientVendor->client->Client_ID;
                }
                $clientVendorsIds[] = Yii::app()->user->clientID;


                // get External Vendors list condition
                if (count($clientVendorsIds) > 0) {
                    //to find vendors that client_ID not in the list of vendors already found above
                    //@todo: uncomment string below
                    $condition->addNotInCondition("t.Client_ID", $clientVendorsIds);
                }
                $condition->addCondition("w9.W9_Owner_ID = '" . Yii::app()->user->clientID ."'");

                $condition->order = $sortOptions['sort_by'] . " " . $sortOptions['sort_direction'];

                $condition->mergeWith($condition1);

                $vendors = Clients::model()->with('company.adreses')->findAll($condition);

           // } else {
             //   $vendors = array();
            //}
        } else {
            $condition->addCondition("t.Client_Client_ID= '" . Yii::app()->user->clientID . "'");
            $condition->addCondition("t.Active_Relationship= '" . self::ACTIVE_RELATIONSHIP . "'");

            $condition->order = $sortOptions['sort_by'] . " " . $sortOptions['sort_direction'];
            $condition->mergeWith($condition1);
            $vendors = Vendors::model()->with('client.company.adreses')->findAll($condition);
        }

        return $vendors;
    }

    /**
     * Get client's vendors Shortcut List
     * @param $clientID
     * @return array
     */
    public static function getClientVendorsShortcutList($clientID)
    {
        $vendorsCP = array();

        $condition = new CDbCriteria();
        $condition->condition = "t.Client_Client_ID = '" . $clientID . "'";
        $condition->addCondition("t.Active_Relationship = '" . self::ACTIVE_RELATIONSHIP . "'");
        $condition->order = 'company.Company_Name ASC';
        $vendors = Vendors::model()->with('client.company')->findAll($condition);

        if ($vendors) {
            foreach ($vendors as $vendor) {
                $vendorsCP[$vendor->Vendor_ID] =  ($vendor->Vendor_ID_Shortcut ? $vendor->Vendor_ID_Shortcut . ' - ' : '') . $vendor->client->company->Company_Name;
            }
        }

        return $vendorsCP;
    }

    /**
     * Get client's vendors Shortcut List by Fed_ID for current client
     * @param $clientID
     * @return array
     */
    public static function getVendorsShortcutListByFed($fed_id)
    {
        //$vendorsCP = array();

        $condition = new CDbCriteria();
        $condition->condition = "t.Client_Client_ID = '" . Yii::app()->user->clientID . "'";
        $condition->join = "left join clients on (clients.Client_ID = t.Vendor_Client_ID)
                            left join companies c on (c.Company_ID = clients.Company_ID)";
        $condition->addCondition("t.Active_Relationship = '" . self::ACTIVE_RELATIONSHIP . "'");
        $condition->addCondition("c.Company_Fed_ID = '" . $fed_id . "'");

        $vendor = Vendors::model()->find($condition);

        return array(
            'id'=>$vendor->Vendor_ID,
            'name'=>$vendor->client->company->Company_Name
        );
    }


    /**
     * Get vendors to approve to session
     * @return array
     */
    public static function getVendorsToApproveToSession()
    {
        $vendorsList = array();
        $client = Clients::model()->with('vendors_list')->findByPk(Yii::app()->user->clientID);
        if (isset($client->vendors_list)) {
            $vendors = $client->vendors_list;
        } else {
            $vendors = array();
        }

        $i = 1;
        foreach($vendors as $vendor) {
            $vendorsList[$i] = $vendor->Vendor_ID;
            $i++;
        }

        return $vendorsList;
    }

    /**
     * Copy Vendors list from one client to another
     * @param $toClientId
     * @param $fromClientId
     */
    public static function copyVendorsList($toClientId, $fromClientId)
    {
        //get existing vendors for client

        $sql='select v1.Vendor_ID_Shortcut,v1.Vendor_Client_ID,v1.Vendor_Name_Checkprint, v1.Vendor_1099,v1.Vendor_Default_GL,v1.Vendor_Default_GL_Note from vendors v1
        left join clients c on (c.Client_ID = v1.Client_Client_ID)
        where v1.Client_Client_ID = '.$fromClientId.
            ' and v1.Vendor_Client_ID not in (
			select v2.Vendor_Client_ID from vendors v2
					left join clients c on (c.Client_ID = v2.Client_Client_ID)
			where v2.Client_Client_ID = '.$toClientId. ' and v2.Active_Relationship=1)';

        $list= Yii::app()->db->createCommand($sql)->queryAll();

        $all_amount=count($list);
        $i=0;
        $pb= ProgressBar::init();

        foreach ($list as $vendor) {
            $newVendor = Vendors::model()->findByAttributes(array(
                'Client_Client_ID' => $toClientId,
                'Vendor_Client_ID' => $vendor['Vendor_Client_ID'],
            ));
            if ($newVendor === null) {
                $newVendor = new Vendors();
                $newVendor->Vendor_ID_Shortcut =$vendor['Vendor_ID_Shortcut'];
                $newVendor->Vendor_Client_ID = $vendor['Vendor_Client_ID'];
                $newVendor->Client_Client_ID = $toClientId;
                $newVendor->Vendor_Name_Checkprint = $vendor['Vendor_Name_Checkprint'];
                $newVendor->Vendor_1099 =$vendor['Vendor_1099'];
                $newVendor->Vendor_Default_GL = $vendor['Vendor_Default_GL'];
                $newVendor->Vendor_Default_GL_Note =$vendor['Vendor_Default_GL_Note'] ;
                $newVendor->Active_Relationship = self::ACTIVE_RELATIONSHIP;
                $newVendor->save();

                $w9 = W9::getCompanyW9($vendor['Vendor_Client_ID'], $fromClientId);
                if ($w9 !== null) {
                    $extW9 = W9::model()->findByAttributes(array(
                        'Document_ID' => $w9->Document_ID,
                        'W9_Owner_ID' => $toClientId,
                    ));
                    if ($extW9 === null) {
                        $w9->share($toClientId, $w9->Access_Type);
                    }
                }
            } else {
                $newVendor->Active_Relationship = self::ACTIVE_RELATIONSHIP;
                $newVendor->save();
            }
            $i++;
            $percent=intval($i/$all_amount*100);
            session_start();
            $_SESSION['progress']=$percent;
            session_write_close();
        }

    }

    public static function exportVendors($vendorsList)
    {
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel.Classes');

        spl_autoload_unregister(array('YiiBase','autoload'));

        include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $phpexcel = new PHPExcel();
        $page = $phpexcel->setActiveSheetIndex(0);
        //Fed ID, Company name, address, city, st, zip short cut
        $page->setCellValue("A1", "Fed ID");
        $page->setCellValue("B1", "Company Name");
        $page->setCellValue("C1", "Address");
        $page->setCellValue("D1", "City");
        $page->setCellValue("E1", "State");
        $page->setCellValue("F1", "ZIP");
        $page->setCellValue("G1", "Shortcut");
        $page->setCellValue("H1", "Checkprint");
        $page->setCellValue("I1", "1099");
        $page->setCellValue("J1", "Default GL");
        $page->setCellValue("K1", "Default GL Note");
        $page->setCellValue("L1", "Note General");

        $page->getStyle('A1')->getFont()->setBold(true);
        $page->getStyle('B1')->getFont()->setBold(true);
        $page->getStyle('C1')->getFont()->setBold(true);
        $page->getStyle('D1')->getFont()->setBold(true);
        $page->getStyle('E1')->getFont()->setBold(true);
        $page->getStyle('F1')->getFont()->setBold(true);
        $page->getStyle('G1')->getFont()->setBold(true);
        $page->getStyle('H1')->getFont()->setBold(true);
        $page->getStyle('I1')->getFont()->setBold(true);
        $page->getStyle('J1')->getFont()->setBold(true);
        $page->getStyle('K1')->getFont()->setBold(true);
        $page->getStyle('L1')->getFont()->setBold(true);
        $page->getColumnDimension('A')->setAutoSize(true);
        $page->getColumnDimension('B')->setAutoSize(true);
        $page->getColumnDimension('C')->setAutoSize(true);
        $page->getColumnDimension('D')->setAutoSize(true);
        $page->getColumnDimension('E')->setAutoSize(true);
        $page->getColumnDimension('F')->setAutoSize(true);
        $page->getColumnDimension('G')->setAutoSize(true);
        $page->getColumnDimension('H')->setAutoSize(true);
        $page->getColumnDimension('I')->setAutoSize(true);
        $page->getColumnDimension('J')->setAutoSize(true);
        $page->getColumnDimension('K')->setAutoSize(true);
        $page->getColumnDimension('L')->setAutoSize(true);

        $i = 3;
        foreach ($vendorsList as $vendor) {
            $page->setCellValue("A" . $i, $vendor->client->company->Company_Fed_ID);
            $page->setCellValue("B" . $i, $vendor->client->company->Company_Name);
            $page->setCellValue("C" . $i, $vendor->client->company->adreses[0]->Address1);
            $page->setCellValue("D" . $i, $vendor->client->company->adreses[0]->City);
            $page->setCellValue("E" . $i, $vendor->client->company->adreses[0]->State);
            $page->setCellValue("F" . $i, $vendor->client->company->adreses[0]->ZIP);
            $page->setCellValue("G" . $i, $vendor->Vendor_ID_Shortcut);
            $page->setCellValue("H" . $i, $vendor->Vendor_Name_Checkprint);
            $page->setCellValue("I" . $i, ($vendor->Vendor_1099 ? 'yes' : 'no'));
            $page->setCellValue("J" . $i, $vendor->Vendor_Default_GL);
            $page->setCellValue("K" . $i, $vendor->Vendor_Default_GL_Note);
            $page->setCellValue("L" . $i, $vendor->Vendor_Note_General);

            $i++;
        }

        $page->setTitle(date('Y_m_d'));
        $objWriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'Vendors_List_' . date('Y_m_d') . '.xlsx' . '"');
        header('Cache-Control: max-age=0');

        $objWriter->save('php://output');
        die;
    }

    /**
     * Prepare Vendors list for import
     * @param $vendors
     * @return array
     */
    public static function prepareVendorsListForImport($vendors)
    {
        $result = array();
        $i = 1;
        foreach ($vendors as $vendor) {
            $fedId = isset($vendor[0]) ? trim($vendor[0]) : null;
            $companyName = isset($vendor[1]) ? trim($vendor[1]) : null;
            $address = isset($vendor[2]) ? trim($vendor[2]) : null;
            $city = isset($vendor[3]) ? trim($vendor[3]) : null;
            $state = isset($vendor[4]) ? trim($vendor[4]) : null;
            $zip = isset($vendor[5]) ? trim($vendor[5]) : null;
            $shortcut = isset($vendor[6]) ? trim($vendor[6]) : null;
            $checkprint = isset($vendor[7]) ? trim($vendor[7]) : null;
            $v1099 = isset($vendor[8]) ? (strtolower(trim($vendor[8])) == 'yes' ? 1 : 0) : 0;
            $defGL = isset($vendor[9]) ? trim($vendor[9]) : null;
            $defGLNote = isset($vendor[10]) ? trim($vendor[10]) : null;
            $noteGeneral = isset($vendor[11]) ? trim($vendor[11]) : null;

            if (preg_match('/^(\d{2}\-\d{7})|(\d{3}\-\d{2}\-\d{4})$/', $fedId) && strlen($companyName) > 0 && strlen($companyName) < 80
                && strlen($address) <= 45 && strlen($city) <= 45
                && strlen($state) <= 4 && strlen($zip) <= 15
                && strlen($defGL) <= 40 && strlen($defGLNote) <= 40
                && strlen($noteGeneral) <= 255) {
                $result[$i] = array(
                    'fedId' => $fedId,
                    'companyName' => $companyName,
                    'address' => $address,
                    'city' => $city,
                    'state' => $state,
                    'zip' => $zip,
                    'shortcut' => $shortcut,
                    'checkprint' => $checkprint,
                    'v1099' => $v1099,
                    'defG' => $defGL,
                    'defGLNote' => $defGLNote,
                    'noteGeneral' => $noteGeneral,
                );
            }
            $i++;
        }

        return $result;
    }

    /**
     * Import Vendors
     * @param $clientID
     * @param $importedVendors
     */
    public static function importVendors($clientID, $importedVendors)
    {

        $all_amount=count($importedVendors);

        $i=0;
        $pb= ProgressBar::init();

        foreach($importedVendors as $importedVendor) {
            $company = Companies::model()->with('client')->findByAttributes(array(
                'Company_Fed_ID' => $importedVendor['fedId'],
            ));

            if ($company) {
                $client = $company->client;
            } else {
                //if company does not exists, create new company
                $client = Companies::createEmptyCompany($importedVendor['fedId'],  $importedVendor['companyName'], $importedVendor);
            }

            $vendorClientId = $client->Client_ID;

            $vendor = Vendors::model()->findByAttributes(array(
                'Client_Client_ID' => $clientID,
                'Vendor_Client_ID' => $vendorClientId,
            ));
            $newVendor = false;

            if (!$vendor) {
                $vendor = new Vendors();
                $newVendor = true;
            } else {
                $vendor->Active_Relationship = self::ACTIVE_RELATIONSHIP;
                $vendor->save();
            }

            $vendor->Vendor_ID_Shortcut = $importedVendor['shortcut'];
            $vendor->Vendor_Client_ID = $vendorClientId;
            $vendor->Client_Client_ID = $clientID;
            $vendor->Vendor_Name_Checkprint = $importedVendor['checkprint'];
            $vendor->Vendor_1099 = $importedVendor['v1099'];
            $vendor->Vendor_Default_GL = $importedVendor['defG'];
            $vendor->Vendor_Default_GL_Note = $importedVendor['defGLNote'];
            $vendor->Vendor_Note_General = $importedVendor['noteGeneral'];

            if ($vendor->validate()) {
                $vendor->save();
            } else if ($newVendor) {
                $vendor = new Vendors();
                $vendor->Vendor_ID_Shortcut = '';
                $vendor->Vendor_Client_ID = $vendorClientId;
                $vendor->Client_Client_ID = $clientID;
                $vendor->Vendor_Name_Checkprint = '';
                $vendor->Vendor_1099 = '';
                $vendor->Vendor_Default_GL = '';
                $vendor->Vendor_Default_GL_Note = '';
                $vendor->Vendor_Note_General = '';
                $vendor->Active_Relationship = self::ACTIVE_RELATIONSHIP;
                $vendor->save();
            }

            $i++;
            $percent=intval($i/$all_amount*100);
            session_start();
            $_SESSION['progress']=$percent;
            session_write_close();
        }
    }

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('Vendor_ID',$this->Vendor_ID);
		$criteria->compare('Vendor_ID_Shortcut',$this->Vendor_ID_Shortcut,true);
		$criteria->compare('Vendor_Client_ID',$this->Vendor_Client_ID);
		$criteria->compare('Client_Client_ID',$this->Client_Client_ID);
		$criteria->compare('Vendor_Name_Checkprint',$this->Vendor_Name_Checkprint,true);
		$criteria->compare('Vendor_1099',$this->Vendor_1099);
		$criteria->compare('Vendor_Default_GL',$this->Vendor_Default_GL,true);
		$criteria->compare('Vendor_Default_GL_Note',$this->Vendor_Default_GL_Note,true);
		$criteria->compare('Vendor_Note_General',$this->Vendor_Note_General,true);
		$criteria->compare('Active_Relationship',$this->Active_Relationship);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Vendors the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}


}
