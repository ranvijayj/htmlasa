<?php

/**
 * This is helper class
 * Contain helper methods
 */
class Helper
{
    /**
     * Creates pagination links
     * @param $url
     * @param $page_text
     * @param $page
     * @param $items
     * @param $num
     */
    public static function createPaginationLinks($url, $page_text, $page, $items, $num)
    {
        $total = intval(($items - 1) / $num) + 1;
        $page = intval($page);
        if(empty($page) or $page < 0) $page = 1;
        if($page > $total) $page = $total;
        $start = $page * $num - $num +1;

        if (($start + $num - 1) < $items) {
            echo '<div class="summary">Displaying ' . $start . '-' . ($start + $num - 1) . ' of ' . $items . ' results.</div>';
        } else {
            echo '<div class="summary">Displaying ' . $start . '-' . $items . ' of ' . $items . ' results.</div>';
        }

        if ($total > 1) {
            echo '<div class="pager">Go to page: <ul id="yw1" class="yiiPager"><li class="first hidden">';
            if ($page == 1) {
                echo '<li class="previous hidden"><a href="#">&lt; Previous</a></li>';
            } else if ($page == 2) {
                echo '<li class="previous"><a href="' . $url . '">&lt; Previous</a></li>';
            } else {
                echo '<li class="previous"><a href="' . $url . '&' . $page_text . '=' . ($page - 1) . '">&lt; Previous</a></li>';
            }
            if($page - 2 > 0) {
                echo '<li class="page"><a href="' . $url . '&' . $page_text . '=' . ($page - 2) . '">' . ($page - 2) . '</a></li>';
            }
            if($page - 1 > 0) {
                echo '<li class="page"><a href="' . $url . '&' . $page_text . '=' . ($page - 1) . '">' . ($page - 1) . '</a></li>';
            }
            echo '<li class="page selected"><a href="#">' . $page . '</a></li>';
            if($page + 1 <= $total) {
                echo '<li class="page"><a href="' . $url . '&' . $page_text . '=' . ($page + 1) . '">' . ($page + 1) . '</a></li>';
            }
            if($page + 2 <= $total) {
                echo '<li class="page"><a href="' . $url . '&' . $page_text . '=' . ($page + 2) . '">' . ($page + 2) . '</a></li>';
            }

            if ($page + 1 > $total) {
                echo '<li class="next hidden"><a href="#">Next &gt;</a></li>';
            } else {
                echo '<li class="next"><a href="' . $url . '&' . $page_text . '=' . ($page + 1) . '">Next &gt;</a></li>';
            }
            echo '</div>';
        }
    }

    /**
     * Generates password from 8 alphanumeric characters
     * @return string
     */
    public static function generatePassword()
    {
        $password = '';
        $chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
        $numChars = strlen($chars);
        for ($i = 0; $i < 8; $i++) {
            $password .= substr($chars, rand(1, $numChars) - 1, 1);
        }
        return $password;
    }

    /**
     * Converts date from 2013-12-02 10:16:43 to 12/02/2013 format
     * @param $date
     * @return string
     */
    public static function convertDate($date)
    {

        if (php_sapi_name()!='cli'){
        $date = date("Y-m-d H:i:s", strtotime($date) - date('Z') + Yii::app()->user->userTimezoneOffset);
        } else {
            $date = date("Y-m-d H:i:s", strtotime($date) - date('Z'));
        }
        $dateParts = explode(' ', $date);
        $dateParts = explode('-', $dateParts[0]);

        $formattedDate = $dateParts[1] . '/' . $dateParts[2] . '/' . $dateParts[0];

        return $formattedDate;
    }




    /**
     * Converts date from 2013-12-02 10:16:43 to 12/02/2013 10:16:43 format
     * @param $date
     * @return string
     */
    public static function convertDateString($date)
    {
        $date = date("Y-m-d H:i:s", strtotime($date) - date('Z') + Yii::app()->user->userTimezoneOffset);
        $dateTimeParts = explode(' ', $date);
        $dateParts = explode('-', $dateTimeParts[0]);
        $dateTimeParts[0] = $dateParts[1] . '/' . $dateParts[2] . '/' . $dateParts[0];
        $formattedDate = implode(' ', $dateTimeParts);
        return $formattedDate;
    }

    /**
     * Converts date from 2013-12-02 10:16:43 to 12/02 format
     * @param $date
     * @return string
     */
    public static function convertDateDayMonth($date)
    {

        if (php_sapi_name()!='cli'){
            $date = date("Y-m-d H:i:s", strtotime($date) - date('Z') + Yii::app()->user->userTimezoneOffset);
        } else {
            $date = date("Y-m-d H:i:s", strtotime($date) - date('Z'));
        }
        /*$dateParts = explode(' ', $date);
        $dateParts = explode('-', $dateParts[0]);

        $formattedDate = $dateParts[1] . '/' . $dateParts[2] . '/' . $dateParts[0];*/

        return date('m/d',strtotime($date));
    }

    /**
     * Converts date from 2013-12-02 10:16:43 to 12/02 format without timezone transformation
     * @param $date
     * @return string
     */
    public static function convertDateDayMonthSimple($date)
    {

        if (php_sapi_name()!='cli'){
            $date = date("Y-m-d H:i:s", strtotime($date));
        } else {
            $date = date("Y-m-d H:i:s", strtotime($date));
        }
        /*$dateParts = explode(' ', $date);
        $dateParts = explode('-', $dateParts[0]);

        $formattedDate = $dateParts[1] . '/' . $dateParts[2] . '/' . $dateParts[0];*/

        return date('m/d',strtotime($date));
    }

    /**
     * Converts date from 2013-12-02 10:16:43 to 12/02/2013 format
     * @param $date
     * @return string
     */
    public static function convertDateFromIntClient($date)
    {

        if (php_sapi_name()!='cli'){
            $date = date("Y-m-d H:i:s", $date - date('Z') + Yii::app()->user->userTimezoneOffset);
        } else {
            $date = date("Y-m-d H:i:s", $date - date('Z'));
        }
        //$dateParts = explode(' ', $date);
        //$dateParts = explode('-', $dateParts[0]);

        //$formattedDate = $dateParts[1] . '/' . $dateParts[2] . '/' . $dateParts[0];

        return $date;
    }

    /**
     * Converts date from int to server timezone date
     * @param $date
     * @return string
     */
    public static function convertDateFromIntServer($date)
    {
        if (php_sapi_name()!='cli'){
            $date = date("Y-m-d H:i:s", $date  );
        } else {
            $date = date("Y-m-d H:i:s", $date  );
        }
        //$dateParts = explode(' ', $date);
        //$dateParts = explode('-', $dateParts[0]);

        //$formattedDate = $dateParts[1] . '/' . $dateParts[2] . '/' . $dateParts[0];

        return $date;
    }



    /**
     * Converts date from 2013-12-02 10:16:43 to 12/02/2013 format
     * WITHOUT TIMEZONES
     * @param $date
     * @return string
     */
    public static function convertDateSimple($date)
    {

        if (php_sapi_name()!='cli'){
            $date = date("Y-m-d H:i:s", strtotime($date));
        } else {
            $date = date("Y-m-d H:i:s", strtotime($date));
        }
        $dateParts = explode(' ', $date);
        $dateParts = explode('-', $dateParts[0]);

        $formattedDate = $dateParts[1] . '/' . $dateParts[2] . '/' . $dateParts[0];

        return $formattedDate;
    }



    /**
     * Converts date to server timezone from 12/02/2013 to 2013-12-02 00:00:00 format
     * @param $date
     * @return string
     */
    public static function convertDateToServerTimezone($date)
    {
        $dateParts = explode('/', $date);
        $formattedDate = $dateParts[2] . '-' . $dateParts[0] . '-' . $dateParts[1];
        $formattedDate = date("Y-m-d H:m:s", strtotime($formattedDate) + date('Z') - Yii::app()->user->userTimezoneOffset);
        return $formattedDate;
    }

    /**
     * Converts date to server from 12/02/2013 to 2013-12-02 format
     * @param $date
     * @return string
     */
    public static function convertDateToServer($date)
    {
        $dateParts = explode('/', $date);
        $formattedDate = $dateParts[2] . '-' . $dateParts[0] . '-' . $dateParts[1];
        return $formattedDate;
    }

    /**
     * Converts date to server from 120213 to 2013-12-02 format
     * @param $date
     * @return string
     */
    public static function convertShortDateToServer($date)
    {
        if (preg_match('/^\d{6}$/', $date)) {
            $month = substr($date, 0, 2);
            $day = substr($date, 2, 2);
            $year = '20' . substr($date, 4, 2);
            $date = $year . '-' . $month . '-' . $day;
        }
        return $date;
    }

    /**
     * Check user input date
     * @param $date
     * @return mixed
     */
    public static function checkDate($date)
    {
        //convert date string to server format
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
            $date = Helper::convertDateToServer($date);
        }

        if (preg_match('/^\d{6}$/', $date)) {
            $date = Helper::convertShortDateToServer($date);
        }

        if ($date == '') {
            $date = null;
        }

        return $date;
    }

    /**
     * Replace matches in the text
     * @param string $text
     * @param array $replacedValues
     * @return string
     */
    public static function replaceMatches($text, $replacedValues)
    {
        foreach ($replacedValues as $search => $replace) {

            $text = str_replace($search, $replace, $text);
        }
        return $text;
    }

    /**
     * Check browser type for IE
     * @return bool
     */
    public static function checkIE()
    {
        $ie = false;
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            if (stristr($user_agent, 'MSIE 7.0') || stristr($user_agent, 'MSIE 8.0')
                || stristr($user_agent, 'MSIE 9.0') || stristr($user_agent, 'MSIE 10.0')
                || stristr($user_agent, 'MSIE 11.0') || stristr($user_agent, 'MSIE 12.0')
                || preg_match('/Mozilla\/\d{1,2}\.\d{1,2}\s\(Windows\sNT\s\d{1,2}\.\d{1,2}\;\sTrident\/\d{1,2}\.\d{1,2}\;\srv\:\d{1,2}\.\d{1,2}\)\slike\sGecko/', $user_agent)) {
                $ie = true;
            }
        }

        return $ie;
    }



    public static function isMobileComplexCheck () {
       if (Helper::getMobileDetect()->isMobile() || Helper::getMobileDetect()->isTablet() || Helper::checkMobile()) {
           return true;
       } else return false;
    }
    /**
     * Return MobileDetect instance
     * @return MobileDetect
     */
    public static function getMobileDetect() {
        $mobileDetect = new MobileDetect();
        return $mobileDetect;
    }

    /**
     * Check for mobile devices
     * @return bool
     */
    public static function checkMobile() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        $ipod = strpos($user_agent,"iPod");
        $iphone = strpos($user_agent,"iPhone");
        $android = strpos($user_agent,"Android");
        $symb = strpos($user_agent,"Symbian");
        $winphone = strpos($user_agent,"WindowsPhone");
        $wp7 = strpos($user_agent,"WP7");
        $wp8 = strpos($user_agent,"WP8");
        $operam = strpos($user_agent,"Opera M");
        $palm = strpos($user_agent,"webOS");
        $berry = strpos($user_agent,"BlackBerry");
        $mobile = strpos($user_agent,"Mobile");
        $htc = strpos($user_agent,"HTC_");
        $fennec = strpos($user_agent,"Fennec/");

        if ($ipod || $iphone || $android || $symb || $winphone || $wp7 || $wp8 || $operam || $palm || $berry || $mobile || $htc || $fennec)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Create address line from address parts
     * @param $address
     * @param $city
     * @param $state
     * @param $zip
     * @param bool $cut
     * @param int $font_size
     * @param int $max_width
     * @param int $mux_chars_without_cutting
     * @param string $font_name
     * @return string
     */
    public static function createAddressLine($address, $city, $state, $zip, $cut = false, $font_size = 14, $max_width = 100, $mux_chars_without_cutting = 50, $font_name = 'css/font/ARIALN.TTF')
    {
        $addressLine = Helper::createFullAddressLine($address, $city, $state, $zip);

        if ($cut) {
            $addressLine = Helper::cutText($font_size, $max_width, $mux_chars_without_cutting, $addressLine, $font_name);
        }

        if ($addressLine == '') {
            $addressLine = '<span class="not_set">Not set</span>';
        }

        return $addressLine;
    }

    /**
     * Create address line from address parts
     * @param $address
     * @param $city
     * @param $state
     * @param $zip
     * @return string
     */
    public static function createFullAddressLine($address, $city, $state, $zip)
    {
        $addressParts = array();

        if ($address != '') {
            $addressParts[] = $address;
        }

        if ($city != '') {
            $addressParts[] = $city;
        }

        if ($state != '') {
            $addressParts[] = $state;
        }

        if ($zip != '') {
            $addressParts[] = $zip;
        }

        $addressLine = CHtml::encode(implode(', ', $addressParts));

        return $addressLine;
    }

    /**
     * Cut text to certain width
     * @param $font_size
     * @param $max_width
     * @param $mux_chars_without_cutting
     * @param $text
     * @param string $font_name
     * @return string
     */
    public static  function cutText($font_size, $max_width, $mux_chars_without_cutting, $text, $font_name = 'css/font/ARIALN.TTF') {
        if (strlen($text) > $mux_chars_without_cutting) {
            $fullText = $text;
            $textLength = strlen($text);
            $bbox = ImageTTFBbox($font_size, 0, $font_name, $text);
            $width_text = $bbox[2] - $bbox[0];

            $wasCutted = false;
            while ($width_text > $max_width) {
                $decr = ceil($textLength*(1-$max_width/$width_text));
                $textLength -= $decr + 3;
                $text = trim(substr($text, 0, $textLength)) . '...';
                $bbox = ImageTTFBbox($font_size, 0, $font_name, $text);
                $width_text = $bbox[2] - $bbox[0];
                $wasCutted = true;
            }

            if ($wasCutted) {
                $text = '<span class="cutted_cell" title="' . $fullText . '" style="font-size: ' . $font_size . 'px;">' . CHtml::encode($text) . '</span>';
            } else {
                $text = CHtml::encode($text);
            }
        } else {
            $text = CHtml::encode($text);
        }
        return $text;
    }

    /**
     * Prepare Account number for display
     * @param $acctNum
     * @param $numChars
     * @return string
     */
    public static function prepareAcctNum($acctNum, $numChars)
    {
        if (strlen($acctNum) > $numChars) {
            $acctNum = substr($acctNum, strlen($acctNum) - $numChars, $numChars);
        } else if (strlen($acctNum) < $numChars) {
            $len = strlen($acctNum);
            for ($i = $len; $i < $numChars; $i++) {
                $acctNum = '0' . $acctNum;
            }
        }

        return $acctNum;
    }

    /**
     * Generate xml sitemap
     * @param $urls
     * @param $changefreq
     * @return string
     */
    public static function buildXmlSitemap($urls, $changefreq)
    {
        // Set head of xml
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                    <urlset
                        xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
                        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
                        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
        ';

        // Set body of xml
        foreach($urls as $key =>$record)
        {
            if (isset($record['url']))
            {
                $url = $record['url'];
                $priority = $record['priority'];
                $xml .= "
                        <url>
                            <loc>http://$url</loc>
                            <priority>$priority</priority>
                            <changefreq>$changefreq</changefreq>
                        </url>
                    ";
            }
        }

        // Set footer of xml
        $xml .="</urlset>";
        return $xml;
    }

    /**
     * Get Search Options html
     * @param $options
     * @return string
     */
    public static function getSearchOptionsHtml($options)
    {
        $optionsHtml = '';
        $session_name = $options['session_name'];
        foreach ($options['options'] as $key => $option) {
            if ($key=='delimiter') {
                $optionsHtml.= "<div class='delimiter' style='border-top: 1px solid;'></div>";
            } else {
                $optionsHtml .= "<label><input type='checkbox' id='$key' value='1' name='$key' " . ((isset($_SESSION[$session_name]['options'][$key])) ? (($_SESSION[$session_name]['options'][$key]) ? 'checked="checked"' : '') : (($option[1]) ? 'checked="checked"' : '')) . " />" . $option[0] . "</label><br/>\r\n";
            }

        }
        return $optionsHtml;
    }

    /**
     * Generate URL for Google Docs Viewer
     * @param $docId
     * @return string
     */
    public static function generateGoogleDocsUrl($docId) {
        Helper::deleteObsoleteGDocsAccessLinks();
        $url = '';
        if (Documents::hasAccess($docId)) {
            $link = new GoogleDocsAccess();
            $link->Access_Code = Helper::generatePassword();
            $link->Document_ID = $docId;
            $link->Access_Time = date('Y-m-d H:i:s');
            if ($link->validate()) {
                $link->save();
            }
            $url = 'http%3A%2F%2F' . Yii::app()->config->get('SITE_URL') . '%2Fdocuments%2Fgetgdocumentfile%3Fdoc_id%3D' . $docId . '%26code%3D' . $link->Access_Code;
        }
        return $url;
    }

    /**
     * Generate URL for Google Docs Viewer
     * @param $docId
     * @return string
     */
    public static function generateGoogleDocsUrlForBatch($batchID) {
        Helper::deleteObsoleteGDocsAccessLinks();
        $url = '';
        if (Documents::hasAccess($docId)) {
            $link = new GoogleDocsAccess();
            $link->Access_Code = Helper::generatePassword();
            $link->Document_ID = $batchID;
            $link->Access_Time = date('Y-m-d H:i:s');
            if ($link->validate()) {
                $link->save();
            }
            $url = 'http%3A%2F%2F' . Yii::app()->config->get('SITE_URL') . '%2Fdocuments%getbatchfiles%3Fbatch_id%3D' . $docId . '%26code%3D' . $link->Access_Code;
        }
        return $url;
    }



    /**
     * Generate URL for Google Docs Viewer for uploading BU for PO
     * @return string
     */
    public static function generatePOBUGoogleDocsUrl() {
        Helper::deleteObsoleteGDocsAccessLinks();
        $url = '';
        if (isset($_SESSION['po_upload_file']) && $_SESSION['po_upload_file']) {
            $link = new GoogleDocsAccess();
            $link->Access_Code = $_SESSION['po_upload_file']['filepath'];
            $link->Document_ID = Yii::app()->user->userID;
            $link->Access_Time = date('Y-m-d H:i:s');
            if ($link->validate()) {
                $link->save();
            }
            $url = 'http%3A%2F%2F' . Yii::app()->config->get('SITE_URL') . '%2Fpo%2Fgetdocumentfileforgoogle%3Fdoc_id%3D' . Yii::app()->user->userID . '%26code%3D' . urlencode($_SESSION['po_upload_file']['filepath']);
        }
        return $url;
    }

    /**
     * Generate URL for Google Docs Viewer for uploading BU for AP
     * @return string
     */
    public static function generateAPBUGoogleDocsUrl() {
        Helper::deleteObsoleteGDocsAccessLinks();
        $url = '';
        if (isset($_SESSION['ap_upload_file']) && $_SESSION['ap_upload_file']) {
            $link = new GoogleDocsAccess();
            $link->Access_Code = $_SESSION['ap_upload_file']['filepath'];
            $link->Document_ID = Yii::app()->user->userID;
            $link->Access_Time = date('Y-m-d H:i:s');
            if ($link->validate()) {
                $link->save();
            }
            $url = 'http%3A%2F%2F' . Yii::app()->config->get('SITE_URL') . '%2Fpo%2Fgetdocumentfileforgoogle%3Fdoc_id%3D' . Yii::app()->user->userID . '%26code%3D' . urlencode($_SESSION['ap_upload_file']['filepath']);
        }
        return $url;
    }

    /**
     * Generate URL for Google Docs Viewer for Uploads page
     * @param $docNum
     * @return string
     */
    public static function generateUploadsGoogleDocsUrl($docNum) {
        Helper::deleteObsoleteGDocsAccessLinks();
        $url = '';
        if (isset($_SESSION['current_upload_files'][$docNum]) && $_SESSION['current_upload_files'][$docNum]) {
            $link = new GoogleDocsAccess();
            $link->Access_Code = $_SESSION['current_upload_files'][$docNum]['filepath'];
            $link->Document_ID = Yii::app()->user->userID;
            $link->Access_Time = date('Y-m-d H:i:s');
            if ($link->validate()) {
                $link->save();
            }
            $url = 'http%3A%2F%2F' . Yii::app()->config->get('SITE_URL') . '%2Fuploads%2Fgetdocumentfileforgoogle%3Fdoc_id%3D' . Yii::app()->user->userID . '%26code%3D' . urlencode($_SESSION['current_upload_files'][$docNum]['filepath']);
        }
        return $url;
    }

    /**
     * Delete obsolete Google Docs Viewer links
     */
    public static function deleteObsoleteGDocsAccessLinks()
    {
        $obsoleteAccessTime = date('Y-m-d H:i:s', time()-300);
        $condition = new CDbCriteria();
        $condition->condition = "Access_Time < '" . $obsoleteAccessTime . "'";
        GoogleDocsAccess::model()->deleteAll($condition);
    }

    /**
     * Get template for company letter
     * @param $companyId
     * @return string
     */
    public static function getCompanyTemplate($companyId)
    {
        $templateBody = '';
        $trans = array(' '=>'-', '/'=>'-', '\\'=>'-', '~'=>'-', '&'=>'-', '?'=>'-', ','=>'-', '"'=>'-', "'"=>'-');
        $company = Companies::model()->with('client')->findByPk($companyId);
        if ($company) {
            // check existing of client-admin
            if (UsersClientList::checkClientForAdmins($company->client->Client_ID)) {
                $company->Auth_Code = NULL;
                $company->Auth_Url = NULL;
                $company->save();
            } else {
                if (!$company->Auth_Url) {
                    //check company with such URL
                    $url = trim(substr(strtr(strtolower($company->Company_Name), $trans),0,8));
                    $i = 0;
                    do {
                        if ($i != 0 && strlen($url) < $i) {
                            break;
                        } else if ($i == 0) {
                            $url = trim(substr(strtr(strtolower($company->Company_Name), $trans),0,8));
                        } else if ($i<=strlen($url)) {
                            $url = substr($url, 0, strlen($url) - $i) . substr($url, 0, $i);
                        }

                        $companies = Companies::model()->findByAttributes(array(
                            'Auth_Url' => $url,
                        ));
                        $i++;
                    } while ($companies);

                    $company->Auth_Url = $url;
                    $company->save();
                } else {
                    $url = $company->Auth_Url;
                }

                $template = MailTemplates::model()->findByPk(5015);
                $templateBody = $template->Message_Body;
                $templateTitle = $template->Title;
                $replacedValues = array(
                    '{{company}}' => $company->Company_Name,
                    '{{register_link}}' => Yii::app()->config->get('SITE_URL') . '/register/' . $url,
                    '{{auth_code}}' => $company->Auth_Code,
                    '{{message_title}}' => $templateTitle,
                    '{{support_number}}' => Yii::app()->config->get('SUPPORT_NUMBER'),
                    '{{http_host}}' => Yii::app()->config->get('SITE_URL'),
                    '{{support_email}}' => Yii::app()->config->get('SUPPORT_EMAIL'),
                );
                $templateBody = Helper::replaceMatches($templateBody, $replacedValues);
            }
        }

        return $templateBody;
    }

    /**
     * Multi implode of array
     * @param $sep
     * @param $array
     * @return string
     */
    public static function multiImplode($sep, $array) {

        $_array = array();
        foreach($array as $val)
            $_array[] = is_array($val)? Helper::multiImplode($sep, $val) : $val;
        return implode($sep, $_array);


    }

    /**
     * Remove document from view session for detail pages
     * @param $docId
     * @param $sessionName
     */
    public static function removeDocumentFromViewSession($docId, $sessionName)
    {
        if (isset($_SESSION[$sessionName])) {
            foreach($_SESSION[$sessionName] as $key => $doc) {
                if ($doc == $docId) {
                    unset($_SESSION[$sessionName][$key]);
                    $posToReview = $_SESSION[$sessionName];
                    $_SESSION[$sessionName] = array();
                    $i = 1;
                    foreach ($posToReview as $k => $val) {
                        $_SESSION[$sessionName][$i] = $val;
                        $i++;
                    }
                    break;
                }
            }
        }
    }

    /**
     * generate directory
     */
    public static function createDirectory($category)
    {
        $path=Yii::app()->getBasePath()."/data/".$category;
        if(!is_dir($path)) {
           mkdir($path,0775);
           chmod($path, 0775);
        }
        return $path;
    }

    public static function emptyDirectory($path)
    {
        if (is_dir($path) === true)
        {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($files as $file)
            {
                if (in_array($file->getBasename(), array('.', '..')) !== true)
                {
                    if ($file->isDir() === true)
                    {
                        rmdir($file->getPathName());
                    }

                    else if (($file->isFile() === true) || ($file->isLink() === true))
                    {
                        unlink($file->getPathname());
                    }
                }
            }

            return rmdir($path);
        }

        else if ((is_file($path) === true) || (is_link($path) === true))
        {
            return unlink($path);
        }

        return false;
    }

    /**
     * generate directory
     */
    public static function createImageDirectory($category)
    {
        $path=Yii::getPathOfAlias('webroot')."/images/out/".$category;
        if(!is_dir($path)) {
            mkdir($path);
            chmod($path, 0777);
        }
        return $path;
    }


    public static function getIp (){

        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
            return $_SERVER["HTTP_X_FORWARDED_FOR"];

        if (isset($_SERVER["HTTP_CLIENT_IP"]))
            return $_SERVER["HTTP_CLIENT_IP"];

        return $_SERVER["REMOTE_ADDR"];

    }

    /**
     * Returns OS and it's version from user_agent string
     * @return string
     */
    public static function getOs (){

        $user_agent     =   $_SERVER['HTTP_USER_AGENT'];

            $os_platform    =   "Unknown OS Platform";

            $os_array       =   array(
                '/windows nt 6.3/i'     =>  'Windows 8.1',
                '/windows nt 6.2/i'     =>  'Windows 8',
                '/windows nt 6.1/i'     =>  'Windows 7',
                '/windows nt 6.0/i'     =>  'Windows Vista',
                '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
                '/windows nt 5.1/i'     =>  'Windows XP',
                '/windows xp/i'         =>  'Windows XP',
                '/windows nt 5.0/i'     =>  'Windows 2000',
                '/windows me/i'         =>  'Windows ME',
                '/win98/i'              =>  'Windows 98',
                '/win95/i'              =>  'Windows 95',
                '/win16/i'              =>  'Windows 3.11',
                '/macintosh|mac os x/i' =>  'Mac OS X',
                '/mac_powerpc/i'        =>  'Mac OS 9',
                '/linux/i'              =>  'Linux',
                '/ubuntu/i'             =>  'Ubuntu',
                '/iphone/i'             =>  'iPhone',
                '/ipod/i'               =>  'iPod',
                '/ipad/i'               =>  'iPad',
                '/android/i'            =>  'Android',
                '/blackberry/i'         =>  'BlackBerry',
                '/webos/i'              =>  'Mobile'
            );

            foreach ($os_array as $regex => $value) {

                if (preg_match($regex, $user_agent)) {
                    $os_platform    =   $value;
                }

            }

            return $os_platform;
    }

    /**
     * Returns array with browser's name,version,platform
     * @return array
     */
    public static function getBrowser()
    {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version= "";

        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        }
        elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        }
        elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        // Next get the name of the useragent yes seperately and for good reason
        if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
        {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        }
        elseif(preg_match('/Firefox/i',$u_agent))
        {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        }
        elseif(preg_match('/Chrome/i',$u_agent) && !preg_match('/OPR/i',$u_agent) )
        {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        }
        elseif(preg_match('/Safari/i',$u_agent) && !preg_match('/OPR/i',$u_agent))
        {
            $bname = 'Apple Safari';
            $ub = "Safari";
        }
        elseif(preg_match('/Opera/i',$u_agent) || preg_match('/OPR/i',$u_agent))
        {
            $bname = 'Opera';
            $ub = "Opera";
        }
        elseif(preg_match('/Netscape/i',$u_agent))
        {
            $bname = 'Netscape';
            $ub = "Netscape";
        }

        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
            ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }

        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
                $version= $matches['version'][0];
            }
            else {
                $version= $matches['version'][1];
            }
        }
        else {
            $version= $matches['version'][0];
        }

        // check if we have a number
        if ($version==null || $version=="") {$version="?";}

        return array(
            'userAgent' => $u_agent,
            'name'      => $bname,
            'version'   => $version,
            'platform'  => $platform,
            'pattern'    => $pattern
        );
    }

    public static function shortenString($str,$width){
        if (strlen($str)<$width) {
            return $str;
        } else {
            $str = substr($str,0,$width-3).'...';
            return $str;
        }


    }

    public static function truncLongWords($str, $max_word_length) {
        $result_str ='';
        $parts = explode(' ',$str);

        foreach ($parts as $part) {
              $result_str .= Helper::shortenString($part,$max_word_length).' ';
        }

        return $result_str;
    }

    public static function truncLongWordsToTable($str, $max_word_length) {

        if (strlen($str)>20) {
            $parts = explode(' ',$str);
            $tota_char_count = 0;

            $i =0;
            $result_str='';

            foreach ($parts as $part) {
                $tota_char_count += strlen($part);
                if ($i==0 || $tota_char_count<13) {
                    $result_str .= Helper::shortenString($part,$max_word_length).' ';
                } else {
                    $result_str .= '<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.Helper::shortenString($part,$max_word_length).'</span><br>';
                }

                $i++;

            }


            return $result_str;

        } else return $str;

    }



    public static function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public static function dirSize ($dir)
    {
        $count_size = 0;
        $count = 0;
        $dir_array = scandir($dir);
        foreach($dir_array as $key=>$filename){
            if($filename!=".." && $filename!="."){
                if(is_dir($dir."/".$filename)){
                    $new_foldersize = self::dirSize($dir."/".$filename);
                    $count_size = $count_size+ $new_foldersize;
                }else if(is_file($dir."/".$filename)){
                    $count_size = $count_size + filesize($dir."/".$filename);
                    $count++;
                }
            }
        }
        return $count_size;
    }

    public static function splitEmails($str){
        $result_array = explode(',',$str);
        $result_array1 = explode (';',$str);

        if ( count($result_array)>1 && count($result_array1)==1 ) {
            return $result_array;
        } else if ( count($result_array)==1 && count($result_array1) > 1) {
            return $result_array1;
        } else  {
            $result =  array_merge($result_array,$result_array1);
            return array_unique($result);

        }

    }

    public static function getMimeTypeByFilePAth($filepath) {
        $pathParts = explode('.', $filepath);
        $extension = strtolower($pathParts[(count($pathParts) - 1)]);
        if ($extension=='pdf') {$mimeType = 'application/pdf';}
        else if ($extension=='png') {$mimeType = 'image/png';}
        else { $mimeType = 'image/jpeg';}
        return $mimeType;
    }

    public static function calculatePeriodsBetweenDates($prev_date,$new_date) {
        $periods = 0;
        if (date('m/d/Y',strtotime($prev_date)) != date('m/d/Y',strtotime($new_date))) {

            while (strtotime($prev_date) < strtotime($new_date)) {
                $periods++;
                $prev_date = strtotime(date("m/d/Y", strtotime($prev_date)) . " +1 month");
                $prev_date = date('m/d/Y',$prev_date);
            }
        }
        //$periods++;

        return $periods;
    }

    public static function checkUserClientProjectToSwitch($cid,$pid,$uid) {

        if ($uid!=0 && $uid != Yii::app()->user->userID) {
            $action = 3; // user changed;

        } else if ($cid!=0 && $cid != Yii::app()->user->clientID) {
            $action = 1;//"client (and project) change";

        } else if ($pid!=0 && $pid != Yii::app()->user->projectID) {
            $action = 2; // "pro change";

        } else {
            $action = 0; //nothing to change
        }

        if ($action) {
            $client_change_array = array(
                'cid' => $cid,
                'pid' => $pid,
                'uid' => ($uid == Yii::app()->user->userID) ? 0 : $uid,
                'uname' => Users::model()->findByPk($uid)->User_Login
            );

            $_SESSION['url_after_relogin'] = $_SERVER['REDIRECT_URL'].'?'.$_SERVER['REDIRECT_QUERY_STRING'];

        } else {
            $client_change_array = array( );
        }

        return $client_change_array;

    }

}