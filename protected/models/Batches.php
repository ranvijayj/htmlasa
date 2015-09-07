<?php

/**
 * This is the model class for table "batches".
 *
 * The followings are the available columns in table 'batches':
 * @property integer $Batch_ID
 * @property integer $Client_ID
 * @property integer $User_ID
 * @property integer $Project_ID
 * @property string $Batch_Creation_Date
 * @property string $Batch_Export_Type
 * @property string $Batch_Source
 * @property string $Batch_Total
 * @property string $Batch_Document
 * @property string $Batch_Summary
 * @property integer $Batch_Posted
 * @property integer $Batch_Uploaded
 *
 */
class Batches extends CActiveRecord
{
    /**
     * Export types
     */
    const EXCEL = 'excel';
    const CSV = 'csv';
    const PDF = 'print';
    static $exportTypes = array(
        self::EXCEL => 'Excel',
        self::CSV => 'CSV',
        self::PDF => 'Print',
    );

    /**
     * Export formats
     */
    const MAS90 = 'MAS90';
    static $exportFormats = array(
        self::MAS90 => 'MAS90',
    );

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'batches';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Client_ID, User_ID, Project_ID, Batch_Creation_Date, Batch_Export_Type, Batch_Source', 'required'),
			array('Client_ID, User_ID, Project_ID,Batch_Uploaded,Batch_Posted', 'numerical', 'integerOnly'=>true),
			array('Batch_Export_Type', 'length', 'max'=>5),
			array('Batch_Source', 'length', 'max'=>2),
			array('Batch_Total', 'length', 'max'=>13),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Batch_ID, Client_ID, User_ID, Project_ID, Batch_Creation_Date, Batch_Export_Type, Batch_Source, Batch_Total, Batch_Document, Batch_Summary', 'safe', 'on'=>'search'),
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
            'user'=>array(self::BELONGS_TO, 'Users', 'User_ID', 'with' => 'person'),
            'client'=>array(self::BELONGS_TO, 'Clients', 'Client_ID', 'with' => 'company'),
            'project'=>array(self::BELONGS_TO, 'Projects', 'Project_ID'),
        );
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'Batch_ID' => 'Batch',
			'Client_ID' => 'Client',
			'User_ID' => 'User',
			'Project_ID' => 'Project',
			'Batch_Creation_Date' => 'Batch Creation Date',
			'Batch_Export_Type' => 'Batch Export Type',
			'Batch_Source' => 'Batch Source',
			'Batch_Total' => 'Batch Total',
            'Batch_Document' => 'Batch Document',
            'Batch_Summary' => 'Batch Summary',
            'Batch_Uploaded'=>'Batch Uploaded',
            'Batch_Posted'=>'Batch Posted',
		);
	}

    /**
     * Generate report files
     * @param $docType
     * @param $batchType
     * @param $batchFormat
     * @param $documents
     */
    public function generateReports($docType, $batchType, $batchFormat, $documents,$client_datetime, $batch_id)
    {
        if ($docType == Documents::AP) {
            $sql = "SELECT
                        a.Document_ID as docID,
                        v.Vendor_ID_Shortcut as VendorID,
                        com.Company_Name as vendorName,
                        a.Invoice_Number as InvNum,
                        a.Invoice_Date as InvDate,
                        a.Invoice_Due_Date as DueDate,
                        a.Invoice_Amount as InvAmt,
                        a.Invoice_Reference as InvDesc,
                        a.Detail_1099 ,
                        a.Detail_1099_Box_Number as Box,
                        d.GL_Dist_Detail_COA_Acct_Number as GLCode,
                        d.GL_Dist_Detail_Amt as GLAmt,
                        d.GL_Dist_Detail_Desc as GLDesc
                    FROM aps as a

                    LEFT JOIN gl_dist_details as d ON d.AP_ID = a.AP_ID
                    LEFT JOIN documents as doc ON doc.Document_ID = a.Document_ID
                    LEFT JOIN vendors as v ON v.Vendor_ID = a.Vendor_ID
                    LEFT JOIN clients as c ON v.Vendor_Client_ID = c.Client_ID
                    LEFT JOIN companies as com ON com.Company_ID = c.Company_ID
                    WHERE doc.Document_ID IN (" . implode(',', $documents) . ")";


        } else {
            $sql = "SELECT
                        p.Document_ID as docID,
                        v.Vendor_ID_Shortcut as VendorID,
                        com.Company_Name as vendorName,
                        p.PO_Number as InvNum,
                        p.PO_Date as InvDate,
                        p.PO_Total as InvAmt,
                        p.PO_Pmts_Tracking_Note as InvDesc,
                        d.PO_Dists_GL_Code as GLCode,
                        d.PO_Dists_Amount as GLAmt,
                        d.PO_Dists_Description as GLDesc
                    FROM pos as p
                    LEFT JOIN po_dists as d ON d.PO_ID = p.PO_ID
                    LEFT JOIN documents as doc ON doc.Document_ID = p.Document_ID
                    LEFT JOIN vendors as v ON v.Vendor_ID = p.Vendor_ID
                    LEFT JOIN clients as c ON v.Vendor_Client_ID = c.Client_ID
                    LEFT JOIN companies as com ON com.Company_ID = c.Company_ID
                    WHERE doc.Document_ID IN (" . implode(',', $documents) . ")";


        }

        $connection = Yii::app()->db;
        $command = $connection->createCommand($sql);
        $exportRows = $command->queryAll();


        if ($batchType == self::EXCEL) {
            //generating of main batch
            $documentFilePath = self::generateBatchExcel($exportRows, $batchFormat);
            //generating of pdf report
            $reportFilePath = $this->generateSummaryFPDF($exportRows,$client_datetime,$batch_id,$docType);
        } else if ($batchType == self::CSV) {
            //generating of main batch
            $documentFilePath = self::generateBatchCSV($exportRows, $batchFormat);
            //generating of pdf report
            $reportFilePath = $this->generateSummaryFPDF($exportRows,$client_datetime,$batch_id,$docType);
        } else if ($batchType == self::PDF) {
            //generating of pdf report
            $reportFilePath = $this->generateSummaryFPDF($exportRows,$client_datetime,$batch_id,$docType);
            //generating of main batch
            $documentFilePath = self::generateBatchPDF($exportRows, $batch_id,$client_datetime,$docType,$batchFormat);
            $reportFilePath = FileModification::concatFiles(array($reportFilePath,$documentFilePath));
        }

        //generation of report



        $this->Batch_Document = addslashes(fread(fopen($documentFilePath,"rb"),filesize($documentFilePath)));
        $this->Batch_Summary = addslashes(fread(fopen($reportFilePath,"rb"),filesize($reportFilePath)));

        @unlink($documentFilePath);
        @unlink($reportFilePath);
    }

    /**
     * Generates
     * @param $exportRows
     * @param string $batchFormat
     * @param $batch_id
     * @param $client_datetime
     * @param $doc_type
     * @return string
     */
    public static  function generateBatchPDF($exportRows,$batch_id,$client_datetime,$doc_type,$batchFormat='MAS90') {
        $pdf = new FpdfForBatchDetail('P','mm','Letter');
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->setVariabled($batch_id,$batchFormat,$client_datetime,$doc_type);
        $pdf->AliasNbPages();
        $pdf->AddPage('P');
        $pdf->SetFont('Times','',12);
        $pdf->SetXY(5,30);
        $pdf->SetFont('Times','',10);
        $pdf->PrintContent($exportRows);


        $path=Helper::createDirectory('temp_for_pdf');
        $batchFileName =$path."/Batch_PDF_". date("Y_m_d_H_i_s") . '.pdf';
        $pdf->Output($batchFileName, 'F');

        return $batchFileName;
    }

    /**
     *  Generate batch in CSV format
     * @param $exportRows
     * @param $batchFormat
     * @return string
     */
    public static function generateBatchCSV($exportRows, $batchFormat)
    {
        $path=Helper::createDirectory('batches');  // creates directory "protected/data/batches" if not exists
        $filename = $path.'/'."Batch_Excel". date('Y_m_d_H_i_s') . ".csv";
        $f = fopen($filename, 'w');
        if($batchFormat == self::MAS90) {
            fputs($f, '"VendorID";"InvNum";"InvDate";"InvAmt";"InvDesc";"GLCode";"GLAmt";"GLDesc"' . "\n");
            foreach($exportRows as $row) {
                fputs($f, '"' . $row['VendorID'] . '";"' . $row['InvNum'] . '";"' . Helper::convertDateSimple($row['InvDate']) . '";"' . $row['InvAmt'] . '";"' . $row['InvDesc'] . '";"' . $row['GLCode'] . '";"' . $row['GLAmt'] . '";"' . $row['GLDesc'] . '"' . "\n");
            }
        }
        fclose($f);
        return $filename;
    }

    /**
     * Generate batch in Excel format
     * @param $exportRows
     * @param $batchFormat
     * @return string
     */
    public static function generateBatchExcel($exportRows, $batchFormat)
    {
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel.Classes');
        new Helper();
        spl_autoload_unregister(array('YiiBase','autoload')); //needed for correct usage of PHPExcel
        include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $phpexcel = new PHPExcel();

        if ($batchFormat == self::MAS90) {
            $page = $phpexcel->setActiveSheetIndex(0);
            $page->setCellValue("A1", "VendorID:");
            $page->setCellValue("B1", "InvNum:");
            $page->setCellValue("C1", "InvDate");
            $page->setCellValue("D1", "InvAmt");
            $page->setCellValue("E1", "InvDesc");
            $page->setCellValue("F1", "GLCode");
            $page->setCellValue("G1", "GLAmt");
            $page->setCellValue("H1", "GLDesc");

            $page->getStyle('A1')->getFont()->setBold(true);
            $page->getStyle('B1')->getFont()->setBold(true);
            $page->getStyle('C1')->getFont()->setBold(true);
            $page->getStyle('D1')->getFont()->setBold(true);
            $page->getStyle('E1')->getFont()->setBold(true);
            $page->getStyle('F1')->getFont()->setBold(true);
            $page->getStyle('G1')->getFont()->setBold(true);
            $page->getStyle('H1')->getFont()->setBold(true);
            $page->getColumnDimension('A')->setAutoSize(true);
            $page->getColumnDimension('B')->setAutoSize(true);
            $page->getColumnDimension('C')->setAutoSize(true);
            $page->getColumnDimension('D')->setAutoSize(true);
            $page->getColumnDimension('E')->setAutoSize(true);
            $page->getColumnDimension('F')->setAutoSize(true);
            $page->getColumnDimension('G')->setAutoSize(true);
            $page->getColumnDimension('H')->setAutoSize(true);

            $x=2;
            foreach($exportRows as $row) {
                $page->setCellValue("A".$x,$row['VendorID']);
                $page->setCellValue("B".$x,$row['InvNum']);
                $page->setCellValue("C".$x,Helper::convertDateSimple($row['InvDate']));
                $page->setCellValue("D".$x,$row['InvAmt']);
                $page->setCellValue("E".$x,$row['InvDesc']);
                $page->setCellValue("F".$x,$row['GLCode']);
                $page->setCellValue("G".$x,$row['GLAmt']);
                $page->setCellValue("H".$x,$row['GLDesc']);
                $x++;
            }
        }

        $objWriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
        spl_autoload_register(array('YiiBase','autoload'));
        $path=Helper::createDirectory('batches');// creates directory "protected/data/batches" if not exists
        $filename = $path.'/'."Batch_Excel". date('Y_m_d_H_i_s') . ".xlsx";
        $objWriter->save($filename);

        return $filename;
    }



    public  function generateSummaryFPDF($exportRows,$client_datetime,$batch_id,$docType)
    {

        $pdf = new FpdfForBatchSummary('P','mm','Letter');
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->setVariabled($batch_id,'MAS90',$client_datetime,$docType);
        $pdf->AliasNbPages();
        $pdf->AddPage('P');
        $pdf->SetFont('Times','',12);
        $pdf->SetXY(5,30);
        $pdf->SetFont('Times','',10);
        $pdf->PrintContent($exportRows);
        //for($i=1;$i<=40;$i++) $pdf->Cell(0,10,'Printing line number '.$i,0,1);

        $path=Helper::createDirectory('batches');// creates directory "protected/data/batches" if not exists
        $fileName =$path."/BatchReport_". date("Y_m_d_H_i_s") . '.pdf';
        $pdf->Output($fileName, 'F');

        return $fileName;

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

		$criteria->compare('Batch_ID',$this->Batch_ID);
		$criteria->compare('Client_ID',$this->Client_ID);
		$criteria->compare('User_ID',$this->User_ID);
		$criteria->compare('Project_ID',$this->Project_ID);
		$criteria->compare('Batch_Creation_Date',$this->Batch_Creation_Date,true);
		$criteria->compare('Batch_Export_Type',$this->Batch_Export_Type,true);
		$criteria->compare('Batch_Source',$this->Batch_Source,true);
		$criteria->compare('Batch_Total',$this->Batch_Total,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Batches the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}




    public static  function returnBatchesByUserId ($uid)
    {
    $batches= Batches::model()->findAllByAttributes(array('User_ID'=>$uid));

    return $batches;


    }

    public static function getListByQueryString($queryString, $sortOptions, $limit = 50)
    {

        $condition = new CDbCriteria();
        $condition->select = 'Batch_ID,Batch_Creation_Date,Batch_Source,Batch_Export_Type ,Batch_Total,User_ID,
                              Batch_Uploaded,Batch_Posted';

        if (trim($queryString) != '') {
            $condition->compare('t.Batch_ID', $queryString, true, 'OR');
            $condition->compare('t.Batch_Creation_Date', $queryString, true, 'OR');
            $condition->compare('t.Batch_Creation_Date', date('Y-m-d',strtotime($queryString)), true, 'OR');
            $condition->compare('t.Batch_Source', $queryString, true, 'OR');
            $condition->compare('t.Batch_Export_Type', $queryString, true, 'OR');

        } else {
            $condition->limit = $limit;
        }

        $condition->order = $sortOptions['sort_by'] . " " . $sortOptions['sort_direction'];

        $condition->addCondition("t.Client_ID= '" . Yii::app()->user->clientID . "'");

        if (is_numeric(Yii::app()->user->projectID)) {
            $condition->addCondition("t.Project_ID= '" . Yii::app()->user->projectID . "'");
        }

        $batches = Batches::model()->with('project')->findAll($condition);

        if (!$batches) {
            $batches = array();
        }

        return $batches;
    }

    /**
     * Get Last Client Payments
     * @param int $limit
     * @return array|CActiveRecord|CActiveRecord[]|mixed|null
     */
   /* public static function getBatchesList($limit = 50,$uid)
    {
        $batches = new Batches();
        //$uid=Yii::app()->user->UserID;

        /*$condition = new CDbCriteria();
        $condition->join = "LEFT JOIN documents ON documents.Document_ID=t.Document_ID";
        //$condition->condition = "t.Payment_Amount='0'";
        $condition->addCondition("documents.Client_ID='" . Yii::app()->user->clientID . "'");

        if (is_numeric(Yii::app()->user->projectID)) {
            $condition->addCondition("documents.Project_ID= '" . Yii::app()->user->projectID . "'");
        }

        if (Yii::app()->user->id == 'user') {
            $condition->addCondition("documents.User_ID= '" . Yii::app()->user->userID . "'");
        }

        $condition->order = "t.Batch_ID DESC";
        $condition->limit = $limit;
        $batchesList = $batches->findAll($condition);*/
     /*   $sql = "SELECT b.Batch_ID,b.Batch_Creation_Date,b.Batch_Source,b.Batch_Export_Type ,b.Batch_Total,b.User_ID,
                       b.Batch_Uploaded,b.Batch_Posted,p.Project_Name
                FROM batches as b";

        $sql=$sql." LEFT JOIN projects as p on p.Project_ID=b.Project_ID ";
        $sql=$sql."WHERE b.User_ID='" . $uid . "'";
        $sql=$sql." ORDER BY b.Batch_Creation_Date DESC ";
        $sql=$sql." limit ".$limit;


        $connection = Yii::app()->db;
        $command = $connection->createCommand($sql);
        $exportRows = $command->queryAll();

        return $exportRows;
    }
*/



}
