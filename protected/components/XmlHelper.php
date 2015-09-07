<?php

/**
 * This is helper class
 * Contain helper methods
 */
class XmlHelper
{
    protected $wrapper;
    protected $xml;
    protected $filepath;

    function __construct($filepath) {
        $this->filepath = $filepath;
        $this->xml = new DOMDocument("1.0");
        $this->xml->preserveWhiteSpace = false;
        $this->xml->formatOutput = true;
        $this->xml->formatOutput = true;
        $this->wrapper = $this->xml->createElement("wrapper");
        return $this->xml;
    }

    public function appendClient($client_id){
        $xml_clients = $this->xml->createElement("client");
        $xml_row = $this->xml->createElement("row");
        $client = Clients::model()->findByPk($client_id);

        foreach ($client->attributes as $key=>$value) {
            $xml_field = $this->xml->createElement("field",htmlentities($value, ENT_QUOTES | 'ENT_XML1'));
            $xml_field->setAttribute('name', $key);
            $xml_row->appendChild($xml_field);
        }

        $xml_clients->appendChild($xml_row);
        $this->wrapper->appendChild($xml_clients);

    }

    public function appendCompany($client_id){
        $client = Clients::model()->with('company.adreses')->findByPk($client_id);

        $company = $client->company;

        $xml_company = $this->xml->createElement("company");
        $xml_row = $this->xml->createElement("row");

        foreach ($company->attributes as $key => $value) {
            $xml_field = $this->xml->createElement("field",htmlentities($value,ENT_QUOTES | 'ENT_XML1'));
            $xml_field->setAttribute('name', $key);
            $xml_row->appendChild($xml_field);
        }

        $xml_company->appendChild($xml_row);

        $xml_adreses = $this->xml->createElement("addresses");

        foreach ($company->adreses as $address) {

            $xml_address = $this->xml->createElement("address");
            $xml_row = $this->xml->createElement("row");
            foreach ($address->attributes as $key=>$value) {
                $xml_field = $this->xml->createElement("field",htmlentities($value,ENT_QUOTES | 'ENT_XML1'));
                $xml_field->setAttribute('name', $key);
                $xml_row->appendChild($xml_field);
            }
            $xml_address->appendChild($xml_row);

        }
        $xml_adreses->appendChild($xml_address);

        $xml_company->appendChild($xml_adreses);

        $this->wrapper->appendChild($xml_company);
    }

    public function appendUserClientList ($client_id){
        $users_client_list = UsersClientList::getClientsUsersArray($client_id);

        $xml_ucl = $this->xml->createElement("users_client_list");

        foreach ($users_client_list as $row) {
            $xml_row = $this->xml->createElement("row");

                foreach ($row as $key=>$value) {
                    $xml_field = $this->xml->createElement("field",htmlentities($value,ENT_QUOTES | 'ENT_XML1'));
                    $xml_field->setAttribute('name', $key);
                    $xml_row->appendChild($xml_field);
                }
            $xml_ucl->appendChild($xml_row);
        }
        $this->wrapper->appendChild($xml_ucl);
    }

    public function appendProjectsList ($client_id,$project_list){
        if (is_array($project_list)) {
            //add in condition here
            $condition = new CDbCriteria();
            $condition->addInCondition('Project_ID',$project_list);
            $projects = Projects::model()->with($condition)->findAllByAttributes(array(
                'Client_ID'=>$client_id
            ));
        } else {
            //selecting all projects
            $projects = Projects::model()->findAllByAttributes(array(
                'Client_ID'=>$client_id
            ));
        }

        $xml_proj = $this->xml->createElement("projects");

        foreach ($projects as $project) {
            $xml_row = $this->xml->createElement("row");
            foreach ($project->attributes as $key => $value) {
                $xml_field = $this->xml->createElement("field",htmlentities($value,ENT_QUOTES | 'ENT_XML1'));
                $xml_field->setAttribute('name', $key);
                $xml_row->appendChild($xml_field);
            }
            $xml_proj->appendChild($xml_row);
        }
        $this->wrapper->appendChild($xml_proj);
    }

    public function appendDocumentsList ($client_id,$docs_array){
        $xml_docs = $this->xml->createElement("client_id");
            $xml_field = $this->xml->createElement("field",$client_id);
        $xml_docs->appendChild($xml_field);

            $xml_files_names = $this->xml->createElement("files_names");
                $xml_field_1 = $this->xml->createElement("field",'documents.xml');
                    $xml_files_names->appendChild($xml_field_1);
                        foreach ($docs_array as $filename) {
                            $xml_field_2 = $this->xml->createElement("field",htmlentities($filename,ENT_QUOTES | 'ENT_XML1'));
                            $xml_files_names->appendChild($xml_field_2);
                        }
            $xml_docs->appendChild($xml_files_names);
        $this->wrapper->appendChild($xml_docs);
    }


    public function appendVendorsList ($client_id){
        $sql='select vendors.*,companies.Company_Name,companies.Company_Fed_ID, companies.SSN,
		        CONCAT_WS(" , ",addresses.Address1,addresses.City,addresses.State,addresses.ZIP,addresses.Country) as FullAddress
            from vendors
            left join companies on (companies.Company_ID = vendors.Vendor_Client_ID)
			left join company_addresses on (company_addresses.Company_ID = companies.Company_ID )
			left join addresses on (addresses.Address_ID =  company_addresses.Address_ID )

            where Client_Client_ID = '.$client_id;

        $list= Yii::app()->db->createCommand($sql)->queryAll();
        $xml_vendors = $this->xml->createElement("vendors");

        foreach ($list as $row) {
            $xml_row = $this->xml->createElement("row");

            foreach ($row as $key=>$value) {
                $xml_field = $this->xml->createElement("field",htmlentities($value,ENT_QUOTES | 'ENT_XML1'));
                $xml_field->setAttribute('name', $key);
                $xml_row->appendChild($xml_field);
            }
            $xml_vendors->appendChild($xml_row);
        }
        $this->wrapper->appendChild($xml_vendors);
    }

    public function appendCoasList ($client_id,$project_list){
        if (is_array($project_list)) {
            //add in condition here
            $sql = 'select coa.*,coa_class.Class_Shortcut,coa_class.Class_Name from coa
            left join coa_class on (coa_class.COA_Class_ID = coa.COA_Class_ID)
            where coa.Client_ID='.$client_id.' and  coa.Project_ID in ('.implode(',',$project_list).')';
            $list= Yii::app()->db->createCommand($sql)->queryAll();

        } else {
            //select all
            $sql = 'select coa.*,coa_class.Class_Shortcut,coa_class.Class_Name from coa
            left join coa_class on (coa_class.COA_Class_ID = coa.COA_Class_ID)
            where coa.Client_ID='.$client_id;

            $list= Yii::app()->db->createCommand($sql)->queryAll();
        }

        $xml_coas = $this->xml->createElement("coas");

        foreach ($list as $row) {
            $xml_row = $this->xml->createElement("row");
            foreach ($row as $key=>$value) {
                $xml_field = $this->xml->createElement("field",htmlentities($value,ENT_QUOTES | 'ENT_XML1'));
                $xml_field->setAttribute('name', $key);
                $xml_row->appendChild($xml_field);
            }
            $xml_coas->appendChild($xml_row);
        }
        $this->wrapper->appendChild($xml_coas);
    }


    public function appendGeneralDocList ($client_id,$project_list,$doc_type){

        $condition = new CDbCriteria();
        $condition->condition =' documents.Client_ID = '.$client_id;
        $condition->addInCondition('documents.Project_ID ',$project_list);
        $condition->join = 'left join documents on documents.Document_ID = t.Document_ID';

        if ($doc_type=='PC') {
            $models = Pcs::model()->with('document')->findAll($condition);
        } else if ($doc_type=='W9') {
            $models = W9::model()->with('document')->findAll($condition);
        } else if ($doc_type=='JE') {
            $models = Journals::model()->with('document')->findAll($condition);
        } else if ($doc_type=='AR') {
            $models = Ars::model()->with('document')->findAll($condition);
        } else if ($doc_type=='PR') {
            $models = Payrolls::model()->with('document')->findAll($condition);
        } else if ($doc_type=='GF') {
            //$models = G::model()->findAll($condition);
        } else if ($doc_type=='PM') {
            $models = Payments::model()->with('document')->findAll($condition);
        } else if ($doc_type=='PC') {
            $models = Ars::model()->with('document')->findAll($condition);
        }




        $xml_doc = $this->xml->createElement("document");
        if($models) {
            foreach ($models as $model) {
                $xml_row = $this->xml->createElement("row");

                foreach ($model->attributes as $key => $value) {
                    $xml_field = $this->xml->createElement("field",htmlentities($value,ENT_QUOTES | 'ENT_XML1'));
                    $xml_field->setAttribute('name', $key);
                    $xml_row->appendChild($xml_field);
                }
                    //+ we need to insert several columns from document model
                        $xml_field = $this->xml->createElement("field",htmlentities($model->document->Origin,ENT_QUOTES | 'ENT_XML1'));
                        $xml_field->setAttribute('name', 'DocumentsOrigin');
                        $xml_row->appendChild($xml_field);
                            $xml_field = $this->xml->createElement("field",htmlentities($model->document->Created,ENT_QUOTES | 'ENT_XML1'));
                            $xml_field->setAttribute('name', 'DocumentsCreated');
                            $xml_row->appendChild($xml_field);
                                $xml_field = $this->xml->createElement("field",htmlentities($model->document->Project_ID,ENT_QUOTES | 'ENT_XML1'));
                                $xml_field->setAttribute('name', 'DocumentsProject_ID');
                                $xml_row->appendChild($xml_field);
                    // end of block

                $xml_doc_row = $this->xml->createElement("documents");
                foreach ($model->document->attributes as $key => $value) {
                    $xml_field = $this->xml->createElement("field",htmlentities($value,ENT_QUOTES | 'ENT_XML1'));
                    $xml_field->setAttribute('name', $key);
                    $xml_doc_row->appendChild($xml_field);
                }

                /**/
                $xml_image_row = $this->xml->createElement("images");
                    $xml_field = $this->xml->createElement("field",$model->document->image->Image_ID);
                    $xml_field->setAttribute('name', 'Image_ID');
                    $xml_image_row->appendChild($xml_field);

                            $filename = FileModification::prepareFileForExport($model->document->image->Document_ID,$doc_type,$this->filepath);
                            $xml_field = $this->xml->createElement("field",$filename);
                            $xml_field->setAttribute('name', 'File_Name');
                            $xml_image_row->appendChild($xml_field);
                            $xml_row->appendChild($xml_field);

                                $xml_field = $this->xml->createElement("field",$model->document->image->Mime_Type);
                                $xml_field->setAttribute('name', 'Mime_Type');
                                $xml_image_row->appendChild($xml_field);
                                $xml_row->appendChild($xml_field);

                                    $xml_field = $this->xml->createElement("field",$model->document->image->Pages_Count);
                                    $xml_field->setAttribute('name', 'Pages_Count');
                                    $xml_image_row->appendChild($xml_field);
                                    $xml_row->appendChild($xml_field);
                                /**/

                $xml_row->appendChild($xml_doc_row);
                $xml_row->appendChild($xml_image_row);
                $xml_doc->appendChild($xml_row);


            }
            $this->wrapper->appendChild($xml_doc);

        }
    }

    public function appendApsList ($client_id,$project_list,$doc_type){

        $condition = new CDbCriteria();
        $condition->condition =' documents.Client_ID = '.$client_id;
        $condition->addInCondition('documents.Project_ID ',$project_list);
        $condition->join = 'left join documents on documents.Document_ID = t.Document_ID';

        $models = Aps::model()->with('document')->findAll($condition);


        $xml_doc = $this->xml->createElement("document");
        if($models) {
            foreach ($models as $model) {
                $xml_row = $this->xml->createElement("row");

                foreach ($model->attributes as $key => $value) {
                    $xml_field = $this->xml->createElement("field",htmlentities($value,ENT_QUOTES | 'ENT_XML1'));
                    $xml_field->setAttribute('name', $key);
                    $xml_row->appendChild($xml_field);
                }

                    //+ we need to insert several columns from document model
                    $xml_field = $this->xml->createElement("field",htmlentities($model->document->Origin,ENT_QUOTES | 'ENT_XML1'));
                    $xml_field->setAttribute('name', 'DocumentsOrigin');
                    $xml_row->appendChild($xml_field);
                    $xml_field = $this->xml->createElement("field",htmlentities($model->document->Created,ENT_QUOTES | 'ENT_XML1'));
                    $xml_field->setAttribute('name', 'DocumentsCreated');
                    $xml_row->appendChild($xml_field);
                    $xml_field = $this->xml->createElement("field",htmlentities($model->document->Project_ID,ENT_QUOTES | 'ENT_XML1'));
                    $xml_field->setAttribute('name', 'DocumentsProject_ID');
                    $xml_row->appendChild($xml_field);
                    // end of block

                /*documents processing*/
                $xml_doc_row = $this->xml->createElement("documents");
                foreach ($model->document->attributes as $key => $value) {
                    $xml_field = $this->xml->createElement("field",htmlentities($value,ENT_QUOTES | 'ENT_XML1'));
                    $xml_field->setAttribute('name', $key);
                    $xml_doc_row->appendChild($xml_field);
                }
                /*end of documents processing*/


                /*bacups processing*/

                $bacup = Documents::model()->findByPk($model->AP_Backup_Document_ID);
                $xml_backup_row = $this->xml->createElement("backup");
                if ($bacup){
                    foreach ($bacup->attributes as $key => $value) {
                        $xml_field = $this->xml->createElement("field",htmlentities($value,ENT_QUOTES | 'ENT_XML1'));
                        $xml_field->setAttribute('name', $key);
                        $xml_backup_row->appendChild($xml_field);
                    }
                }
                /*end of bacups processing*/

                /*payments connected processing*/
                $payment = Payments::model()->findByPk($model->Payment_ID);
                $xml_payment_row = $this->xml->createElement("payments");
                if ($payment){
                    foreach ($payment->attributes as $key => $value) {
                        $xml_field = $this->xml->createElement("field",htmlentities($value,ENT_QUOTES | 'ENT_XML1'));
                        $xml_field->setAttribute('name', $key);
                        $xml_payment_row->appendChild($xml_field);
                    }
                }
                /*end of payments processing*/

                /*po connected processing*/
                $po = Pos::model()->findByPk($model->PO_ID);
                $xml_po_row = $this->xml->createElement("pos_connected");
                if ($po){
                    foreach ($po->attributes as $key => $value) {
                        $xml_field = $this->xml->createElement("field",htmlentities($value,ENT_QUOTES | 'ENT_XML1'));
                        $xml_field->setAttribute('name', $key);
                        $xml_po_row->appendChild($xml_field);
                    }
                }
                /*end of payments processing*/

                /*dists processing*/
                $xml_dists = $this->xml->createElement("gl_dist_detail");
                    foreach ($model->dists as $dist) {
                        $xml_dist_row = $this->xml->createElement("dist_row");

                        foreach ($dist as $key=>$value) {
                            $xml_field = $this->xml->createElement("field",htmlentities($value,ENT_QUOTES | 'ENT_XML1'));
                            $xml_field->setAttribute('name', $key);
                            $xml_dist_row->appendChild($xml_field);
                        }



                        $xml_dists->appendChild($xml_dist_row);
                    }

                /*end of dists processing*/


                /*images processing*/
                $xml_image_row = $this->xml->createElement("images");
                $xml_field = $this->xml->createElement("field",$model->document->image->Image_ID);
                $xml_field->setAttribute('name', 'Image_ID');
                $xml_image_row->appendChild($xml_field);


                $filename = FileModification::prepareFileForExport($model->document->image->Document_ID,$doc_type,$this->filepath);
                $xml_field = $this->xml->createElement("field",$filename);
                $xml_field->setAttribute('name', 'File_Name');
                $xml_image_row->appendChild($xml_field);
                    $xml_row->appendChild($xml_field);
                $xml_field = $this->xml->createElement("field",$model->document->image->Mime_Type);
                $xml_field->setAttribute('name', 'Mime_Type');
                $xml_image_row->appendChild($xml_field);
                    $xml_row->appendChild($xml_field);
                $xml_field = $this->xml->createElement("field",$model->document->image->Pages_Count);
                $xml_field->setAttribute('name', 'Pages_Count');
                $xml_image_row->appendChild($xml_field);
                    $xml_row->appendChild($xml_field);
                /*end of images processing*/

                $xml_row->appendChild($xml_dists);
                $xml_row->appendChild($xml_backup_row);
                $xml_row->appendChild($xml_payment_row);
                $xml_row->appendChild($xml_po_row);
                $xml_row->appendChild($xml_doc_row);
                $xml_row->appendChild($xml_image_row);
                $xml_doc->appendChild($xml_row);


            }



            $this->wrapper->appendChild($xml_doc);


        }
    }

    public function appendPosList ($client_id,$project_list,$doc_type){

        $condition = new CDbCriteria();
        $condition->condition =' documents.Client_ID = '.$client_id;
        $condition->addInCondition('documents.Project_ID ',$project_list);
        $condition->join = 'left join documents on documents.Document_ID = t.Document_ID';

        $models = Pos::model()->with('document')->findAll($condition);

        $xml_doc = $this->xml->createElement("document");
        if($models) {
            foreach ($models as $model) {
                $xml_row = $this->xml->createElement("row");

                foreach ($model->attributes as $key => $value) {
                    $xml_field = $this->xml->createElement("field",htmlentities($value,ENT_QUOTES | 'ENT_XML1'));
                    $xml_field->setAttribute('name', $key);
                    $xml_row->appendChild($xml_field);
                }

                //+ we need to insert several columns from document model
                    $xml_field = $this->xml->createElement("field",htmlentities($model->document->Origin,ENT_QUOTES | 'ENT_XML1'));
                    $xml_field->setAttribute('name', 'DocumentsOrigin');
                    $xml_row->appendChild($xml_field);
                            $xml_field = $this->xml->createElement("field",htmlentities($model->document->Created,ENT_QUOTES | 'ENT_XML1'));
                            $xml_field->setAttribute('name', 'DocumentsCreated');
                            $xml_row->appendChild($xml_field);
                                    $xml_field = $this->xml->createElement("field",htmlentities($model->document->Project_ID,ENT_QUOTES | 'ENT_XML1'));
                                    $xml_field->setAttribute('name', 'DocumentsProject_ID');
                                    $xml_row->appendChild($xml_field);
                // end of block

                /*documents processing*/
                $xml_doc_row = $this->xml->createElement("documents");
                foreach ($model->document->attributes as $key => $value) {
                    $xml_field = $this->xml->createElement("field",htmlentities($value,ENT_QUOTES | 'ENT_XML1'));
                    $xml_field->setAttribute('name', $key);
                    $xml_doc_row->appendChild($xml_field);
                }
                /*end of documents processing*/


                /*bacups processing*/

                $bacup = Documents::model()->findByPk($model->PO_Backup_Document_ID);
                $xml_backup_row = $this->xml->createElement("backup");
                if ($bacup){
                    foreach ($bacup->attributes as $key => $value) {
                        $xml_field = $this->xml->createElement("field",htmlentities($value,ENT_QUOTES | 'ENT_XML1'));
                        $xml_field->setAttribute('name', $key);
                        $xml_backup_row->appendChild($xml_field);
                    }
                }
                /*end of bacups processing*/

                /*po descriptions connected processing*/
                $xml_desc = $this->xml->createElement("po_desc_detail");
                foreach ($model->decr_details as $descs) {
                    $xml_dist_row = $this->xml->createElement("desc_row");

                    foreach ($descs as $key=>$value) {
                        $xml_field = $this->xml->createElement("field",htmlentities($value,ENT_QUOTES | 'ENT_XML1'));
                        $xml_field->setAttribute('name', $key);
                        $xml_dist_row->appendChild($xml_field);
                    }
                    $xml_desc->appendChild($xml_dist_row);
                /*end of descriptions processing*/

                /*dists processing*/
                $xml_dists = $this->xml->createElement("po_dists");
                foreach ($model->dists as $dist) {
                    $xml_dist_row = $this->xml->createElement("dist_row");

                    foreach ($dist as $key=>$value) {
                        $xml_field = $this->xml->createElement("field",htmlentities($value,ENT_QUOTES | 'ENT_XML1'));
                        $xml_field->setAttribute('name', $key);
                        $xml_dist_row->appendChild($xml_field);
                    }



                    $xml_dists->appendChild($xml_dist_row);
                }

                /*end of dists processing*/


                /*images processing*/
                $xml_image_row = $this->xml->createElement("images");
                $xml_field = $this->xml->createElement("field",$model->document->image->Image_ID);
                $xml_field->setAttribute('name', 'Image_ID');
                $xml_image_row->appendChild($xml_field);

                $filename = FileModification::prepareFileForExport($model->document->image->Document_ID,$doc_type,$this->filepath);
                $xml_field = $this->xml->createElement("field",$filename);
                $xml_field->setAttribute('name', 'File_Name');
                $xml_image_row->appendChild($xml_field);
                    $xml_row->appendChild($xml_field);

                $xml_field = $this->xml->createElement("field",$model->document->image->Mime_Type);
                $xml_field->setAttribute('name', 'Mime_Type');
                $xml_image_row->appendChild($xml_field);
                    $xml_row->appendChild($xml_field);

                $xml_field = $this->xml->createElement("field",$model->document->image->Pages_Count);
                $xml_field->setAttribute('name', 'Pages_Count');
                $xml_image_row->appendChild($xml_field);
                    $xml_row->appendChild($xml_field);
                /*end of images processing*/

                $xml_row->appendChild($xml_dists);
                $xml_row->appendChild($xml_desc);
                $xml_row->appendChild($xml_backup_row);
                $xml_row->appendChild($xml_doc_row);
                $xml_row->appendChild($xml_image_row);
                $xml_doc->appendChild($xml_row);


            }



            $this->wrapper->appendChild($xml_doc);


        }
    }
    }

    public function  saveToFile($filename){

        $this->xml->appendChild($this->wrapper);
        $this->xml->save($this->filepath.'/'.$filename);
        return $this->filepath.'/'.$filename;


    }

    public static function AnalizeDocumentXls ($path) {
        $fullpath = $path.'/'.'documents.xml';
        $xml = simplexml_load_file($fullpath);
        $files = array();
        foreach ($xml->client_id->files_names->field as $field){
            if (strip_tags($field->asXml()) != 'documents.xml') {
                array_push($files, strip_tags($field->asXml()) );
            }
        }
        unset($xml);
    return $files;

    }

    public static function generateGeneralPDF ($path) {
        $xml = simplexml_load_file($path.'/general.xml');

        $client_result = array();
        foreach ($xml->client->row->field as $field){
            $attrib = self::xml_attribute($field,'name');
            $client_result[$attrib] = strip_tags($field->asXml())  ;
        }

        $company_result = array();
        foreach ($xml->company->row->field as $field){
            $attrib = self::xml_attribute($field,'name');
            $company_result[$attrib] = strip_tags($field->asXml())  ;
        }

        $address_result = array();
        foreach ($xml->company->addresses->address->row->field as $field){
            $attrib = self::xml_attribute($field,'name');
            $address_result[$attrib] = strip_tags($field->asXml());
        }

        $users_result = array();
        foreach ($xml->users_client_list->row as $row){
            foreach ($row->field as $field) {
                $attrib = self::xml_attribute($field,'name');
                $address_row[$attrib] = strip_tags($field->asXml());
            }
            $users_result[] = $address_row;
        }

        $project_result = array();
        foreach ($xml->projects->row as $row){
            foreach ($row->field as $field) {
                $attrib = self::xml_attribute($field,'name');
                $rrow[$attrib] = strip_tags($field->asXml());
            }
            $project_result[] = $rrow;
        }
        unset($xml);

        $pdf = new FpdfForRpPartialWrapper('P','mm','Letter');
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->setGeneralVariabled($company_result,$address_result,$users_result,$project_result);
        $pdf->setPageNo(1);
        //$pdf->AliasNbPages();
        $pdf->AddPage('P');
        $pdf->SetFont('Times','',12);
        $pdf->SetXY(5,30);
        $pdf->SetFont('Times','',10);
        $pdf->PrintGeneralContent();

        //$path=Helper::createDirectory('batches');// creates directory "protected/data/batches" if not exists
        $fileName = 'BookGeneral.pdf';
        $pdf->Output($path.'/'.$fileName, 'F');

        $last_page = $pdf->custom_page_num;
        $pdf->Close();
        return array('filename'=>$fileName, 'lastpage'=>$last_page);
    }

    public static function generateVendorsPDF ($path,$next_page_num) {
        $xml = simplexml_load_file($path.'/vendors.xml');

        $vendors_result = array();
        foreach ($xml->vendors->row as $row){
            foreach ($row->field as $field) {
                $attrib = self::xml_attribute($field,'name');
                $rrow[$attrib] = strip_tags($field->asXml());
            }
            $vendors_result[] = $rrow;
        }
        unset($xml);

        $pdf = new FpdfForRpPartialWrapper('P','mm','Letter');
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->setVendorsVariabled($vendors_result);
        $pdf->setPageNo($next_page_num);
        //$pdf->AliasNbPages();
        $pdf->AddPage('P');
        $pdf->SetFont('Times','',12);
        $pdf->SetXY(5,30);
        $pdf->SetFont('Times','',10);
        $pdf->PrintVendorsContent();

        $fileName = 'BookVendors.pdf';
        $pdf->Output($path.'/'.$fileName, 'F');

        $last_page = $pdf->custom_page_num;
        $pdf->Close();

        return array('filename'=>$fileName, 'lastpage'=>$last_page);

    }

    public static function generateCoasPDF ($path,$next_page_num) {
        $xml = simplexml_load_file($path.'/coas.xml');

        $coas_result = array();
        foreach ($xml->coas->row as $row){
            foreach ($row->field as $field) {
                $attrib = self::xml_attribute($field,'name');
                $rrow[$attrib] = strip_tags($field->asXml());
            }
            $coas_result[] = $rrow;
        }
        unset($xml);

        $pdf = new FpdfForRpPartialWrapper('P','mm','Letter');
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->setCoasVariabled($coas_result);
        $pdf->setPageNo($next_page_num);
        $pdf->AddPage('P');
        $pdf->SetFont('Times','',12);
        $pdf->SetXY(5,30);
        $pdf->SetFont('Times','',10);
        $pdf->PrintCoasContent();

        $fileName = 'BookCoas.pdf';
        $pdf->Output($path.'/'.$fileName, 'F');

        $last_page = $pdf->custom_page_num;
        $pdf->Close();
        return array('filename'=>$fileName, 'lastpage'=>$last_page);

    }

    public static function generateCommonDocPDF ($path,$shortcut,$next_page) {

        ini_set("memory_limit","512M");
        set_time_limit(0);
        $xml = simplexml_load_file($path.'/docs_'.$shortcut.'.xml');

        $doc_native_result = array();
        $doc_documents_data = array();
        $doc_images_data = array();
        $rows = $xml->document->row;
        if (count($rows)>0) {
            foreach ($xml->document->row as $row){
                foreach ($row->field as $field) {
                    $attrib = self::xml_attribute($field,'name');
                    $rrow[$attrib] = strip_tags($field->asXml());
                }
                $doc_native_result[] = $rrow;
                unset($rrow);

                foreach ($row->documents->field as $field) {

                    $attrib = self::xml_attribute($field,'name');
                    $rrow[$attrib] = strip_tags($field->asXml());
                }
                $doc_documents_data[] = $rrow;
                unset($rrow);

                foreach ($row->images as $field) {
                    $attrib = self::xml_attribute($field,'name');
                    $rrow[$attrib] = strip_tags($field->asXml());
                }
                $doc_images_data[] = $rrow;
                unset($rrow);


            }
        }
        unset($xml);
//        var_dump($doc_native_result); die;
        $pdf = new FpdfForRpPartialWrapper('P','mm','Letter');
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->setGeneralDocsVariabled($doc_native_result,$doc_documents_data,$doc_images_data,$path,$shortcut);
        $pdf->setPageNo($next_page);
        //$pdf->AliasNbPages();
        $pdf->AddPage('P');
        $pdf->SetFont('Times','',12);
        $pdf->SetXY(5,30);
        $pdf->SetFont('Times','',10);
        $pdf->PrintGeneralDocsContent();

        $fileName = $path."/Book_".$shortcut.'.pdf';
        $pdf->Output($fileName, 'F');

        $last_page = $pdf->custom_page_num;
        $pdf->Close();


        return array('filename'=>$fileName, 'lastpage'=>$last_page);
    }

    public static function generateApDocPDF ($path,$shortcut,$next_page) {

        ini_set("memory_limit","512M");
        set_time_limit(0);
        $xml = simplexml_load_file($path.'/docs_'.$shortcut.'.xml');

        $doc_native_result = array();
        $doc_documents_data = array();
        $doc_images_data = array();

        $dists_data = array();
        $bacup_data = array();
        $payment_data = array();
        $po_data = array();

        foreach ($xml->document->row as $row){
            foreach ($row->field as $field) {
                $attrib = self::xml_attribute($field,'name');
                $rrow[$attrib] = strip_tags($field->asXml());
            }
            //then we need to create nested array with dists
            //so
                foreach ($row->gl_dist_detail->dist_row as $temp_smth) {
                    foreach ($temp_smth as $field) {
                        $attrib = self::xml_attribute($field,'name');
                        $dist_rrow[$attrib] = strip_tags($field->asXml());
                    }
                    $dists_temp_row[] = $dist_rrow;
                }
            $rrow['dists']=$dists_temp_row;
            $doc_native_result[] = $rrow;
            unset($rrow);unset($dists_temp_row);


                /*foreach ($row->documents->field as $field) {

                    $attrib = self::xml_attribute($field,'name');
                    $rrow[$attrib] = strip_tags($field->asXml());
                }

                $doc_documents_data[] = $rrow;
                $current_doc_id = $doc_documents_data['Document_ID'];
                unset($rrow);*/

                    /*foreach ($row->images as $field) {
                        $attrib = self::xml_attribute($field,'name');
                        $rrow[$attrib] = strip_tags($field->asXml());
                    }
                    $doc_images_data[] = $rrow;
                    unset($rrow);*/

                        /*foreach ($row->backup as $field) {
                            $attrib = self::xml_attribute($field,'name');
                            $rrow[$attrib] = strip_tags($field->asXml());
                        }
                        $bacup_data[] = $rrow;
                        unset($rrow);

                            foreach ($row->payments as $field) {
                                $attrib = self::xml_attribute($field,'name');
                                $rrow[$attrib] = strip_tags($field->asXml());
                            }
                            $payment_data[] = $rrow;
                            unset($rrow);

                                    foreach ($row->pos_connected as $field) {
                                        $attrib = self::xml_attribute($field,'name');
                                        $rrow[$attrib] = strip_tags($field->asXml());
                                    }
                                    $po_data[] = $rrow;
                                    unset($rrow);*/



        }
        unset($xml);

        $pdf = new FpdfForRpPartialWrapper('P','mm','Letter');
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->setGeneralDocsVariabled($doc_native_result,$doc_documents_data,$doc_images_data,$path,$shortcut);
        $pdf->setPageNo($next_page);
        $pdf->AddPage('P');
        $pdf->SetFont('Times','',12);
        $pdf->SetXY(5,30);
        $pdf->SetFont('Times','',10);
        $pdf->PrintApContent();

        $fileName = $path."/Book_".$shortcut.'.pdf';
        $pdf->Output($fileName, 'F');

        $last_page = $pdf->custom_page_num;
        $pdf->Close();


        return array('filename'=>$fileName, 'lastpage'=>$last_page);
    }


    public static function generatePoDocPDF ($path,$shortcut,$next_page) {

        ini_set("memory_limit","512M");
        set_time_limit(0);
        $xml = simplexml_load_file($path.'/docs_'.$shortcut.'.xml');

        $doc_native_result = array();
        $doc_documents_data = array();
        $doc_images_data = array();

        $dists_data = array();
        $bacup_data = array();
        $payment_data = array();
        $po_data = array();

        foreach ($xml->document->row as $row){
            foreach ($row->field as $field) {
                $attrib = self::xml_attribute($field,'name');
                $rrow[$attrib] = strip_tags($field->asXml());
            }
            //then we need to create nested array with dists
            //so
            foreach ($row->po_dists->dist_row as $temp_smth) {
                foreach ($temp_smth as $field) {
                    $attrib = self::xml_attribute($field,'name');
                    $dist_rrow[$attrib] = strip_tags($field->asXml());
                }
                $dists_temp_row[] = $dist_rrow;
            }
            $rrow['dists']=$dists_temp_row;
            $doc_native_result[] = $rrow;
            unset($rrow);unset($dists_temp_row);


            /*foreach ($row->documents->field as $field) {

                $attrib = self::xml_attribute($field,'name');
                $rrow[$attrib] = strip_tags($field->asXml());
            }

            $doc_documents_data[] = $rrow;
            $current_doc_id = $doc_documents_data['Document_ID'];
            unset($rrow);

            foreach ($row->images as $field) {
                $attrib = self::xml_attribute($field,'name');
                $rrow[$attrib] = strip_tags($field->asXml());
            }
            $doc_images_data[] = $rrow;
            unset($rrow);

            foreach ($row->backup as $field) {
                $attrib = self::xml_attribute($field,'name');
                $rrow[$attrib] = strip_tags($field->asXml());
            }
            $bacup_data[] = $rrow;
            unset($rrow);

            foreach ($row->payments as $field) {
                $attrib = self::xml_attribute($field,'name');
                $rrow[$attrib] = strip_tags($field->asXml());
            }
            $payment_data[] = $rrow;
            unset($rrow);

            foreach ($row->pos_connected as $field) {
                $attrib = self::xml_attribute($field,'name');
                $rrow[$attrib] = strip_tags($field->asXml());
            }
            $po_data[] = $rrow;
            unset($rrow);*/



        }
        unset($xml);

       // var_dump($doc_native_result);die;
        $pdf = new FpdfForRpPartialWrapper('P','mm','Letter');
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->setGeneralDocsVariabled($doc_native_result,$doc_documents_data,$doc_images_data,$path,$shortcut);
        $pdf->setPageNo($next_page);
        $pdf->AddPage('P');
        $pdf->SetFont('Times','',12);
        $pdf->SetXY(5,30);
        $pdf->SetFont('Times','',10);
        $pdf->PrintPoContent();

        $fileName = $path."/Book_".$shortcut.'.pdf';
        $pdf->Output($fileName, 'F');

        $last_page = $pdf->custom_page_num;
        $pdf->Close();


        return array('filename'=>$fileName, 'lastpage'=>$last_page);

    }


    public static function concatFiles($files_array,$path,$filename) {
        require_once(Yii::app()->basePath.'/extensions/Fpdf/fpdf.php');
        require_once(Yii::app()->basePath.'/extensions/Fpdi/fpdi.php');

        //profiling
        xhprof_enable(XHPROF_FLAGS_MEMORY);

        $pdf = new FpdfForRpWrapper('P','mm','Letter');
        $pdf->SetAutoPageBreak(true, 10);
        //$pdf->setVariabled($batch_id,'MAS90',$client_datetime,$docType);
        $pdf->AliasNbPages();
        $j=0;
        foreach ($files_array AS $file) {
            // get the page count
            $pageCount = $pdf->setSourceFile($path.'/'.$file);
            // iterate through all pages
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                // import a page
                $templateId = $pdf->importPage($pageNo);
                // get the size of the imported page
                $size = $pdf->getTemplateSize($templateId);

                // create a page (landscape or portrait depending on the imported page size)
                if ($size['w'] > $size['h']) {
                    $pdf->AddPage('L', array($size['w'], $size['h']));
                } else {
                    $pdf->AddPage('P', array($size['w'], $size['h']));
                }

                // use the imported page
                $pdf->useTemplate($templateId);
                $j++;
            }

        }

        // Output the new PDF. Here we need to overwrite existing file so first array element is used.
        $filename  = $path.'/'.$filename.'.pdf';
        $pdf->Output($filename,'F');


        // stop profiler
        $xhprof_data = xhprof_disable();
        include_once "/usr/share/php/xhprof_lib/utils/xhprof_lib.php";
        include_once "/usr/share/php/xhprof_lib/utils/xhprof_runs.php";
        $xhprof_runs = new XHProfRuns_Default();
        $run_id = $xhprof_runs->save_run($xhprof_data, "concat_pdf");

        return array(
            'filename'=>$filename,
            'page_count'=>$j);



    }

    public static function commandLineConcat($files_array,$path) {

        chdir($path);
        $string_file_list = '';
        foreach ($files_array AS $file) {
              $string_file_list.= $file.' ';
        }

        $command = 'gs -o SummaryPDF.pdf -sDEVICE=pdfwrite -dPDFSETTINGS=/prepress '.$string_file_list;
        exec($command);

        $filename  = $path.'/SummaryPDF.pdf';
        $page_count = FileModification::getPdfPagesCount($filename);

        return array(
            'filename'=>$filename,
            'page_count'=>$page_count);

    }


    /**
     * Returns value attribute of xml node
     * @param $object
     * @param $attribute
     * @return string
     */
    public static function xml_attribute($object, $attribute)
    {
        if(isset($object[$attribute]))
            return (string) $object[$attribute];
    }


}