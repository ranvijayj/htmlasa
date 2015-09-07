<?php

/**
 * This is class to send fax
 */
class Fax
{
    /**
     * @var string
     */
    public $Username = '';

    /**
     * @var string
     */
    public $Password = '';

    /**
     * @var string
     */
    public $FaxNumber = '';

    /**
     * @var string
     */
    public $FileData = '';

    /**
     * @var string
     */
    public $FileType = '';

    /**
     * Constructor
     * @param $fax
     * @param $filePath
     * @param $mimetype
     */
    public function __construct($fax, $filePath, $mimetype)
    {
        /**************** Settings begin **************/
        $username          = Yii::app()->config->get('INTER_FAX_USERNAME');  // InterFAX username here
        $password          = Yii::app()->config->get('INTER_FAX_PASSWORD');  // InterFAX password here
        $faxnumber         = $fax;  // The destination fax number here, e.g. +497116589658
        $filename          = $filePath; // A file in your filesystem
        $filetype          = 'PDF'; // File format; supported types are listed at
        // http://www.interfax.net/en/help/supported_file_types
        /**************** Settings end ****************/

        if (strpos($mimetype, 'pdf') !== false) {
            $filetype = 'PDF';
        } else if (strpos($mimetype, 'jpeg') !== false || strpos($mimetype, 'jpg') !== false) {
            $filetype = 'JPG/JPEG';
        } else if (strpos($mimetype, 'bmp') !== false) {
            $filetype = 'BMP';
        } else if (strpos($mimetype, 'gif') !== false) {
            $filetype = 'GIF';
        } else if (strpos($mimetype, 'png') !== false) {
            $filetype = 'PNG';
        } else if (strpos($mimetype, 'tiff') !== false || strpos($mimetype, 'tif') !== false) {
            $filetype = 'TIF';
        }

        // Open File
        if( !($fp = fopen($filename, "r"))){
            // Error opening file
            return false;
        }

        // Read data from the file into $data
        $data = "";
        while (!feof($fp)) $data .= fread($fp,1024);
        fclose($fp);

        $this->Username  = $username;
        $this->Password  = $password;
        $this->FaxNumber = $faxnumber;
        $this->FileData  = $data;
        $this->FileType  = $filetype;
    }

    /**
     * Send document by fax
     */
    public function sendDocument()
    {
        if (Yii::app()->config->get('SEND_FAX')) {
            //send fax
            $client = new SoapClient("http://ws.interfax.net/dfs.asmx?WSDL");
            $result = $client->Sendfax($this);
            return $result->SendfaxResult;
        } else {
            return true;
        }
    }
}