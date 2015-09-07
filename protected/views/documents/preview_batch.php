<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Preview document</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquerymin.js"></script>
</head>
<body>
<div class="w9_detail_block" id="w9_detail_block1" style="overflow: hidden;height: 100%;">

        <?php

            if (Helper::getMobileDetect()->isMobile() || Helper::getMobileDetect()->isTablet() || Helper::checkMobile()) {
                //echo '<iframe src="http://docs.google.com/viewer?url=' . Helper::generateGoogleDocsUrl($doc_id) . '&embedded=true" class="documet_file height100pn width100pn"></iframe> ';
            } else {
                $browser = Helper::getBrowser();
                if ($browser['name']=='Google Chrome') {
                    echo '<iframe src="/documents/getbatchfiles?batch_id='.$batch_id.'&file=report" class="documet_file height100pn width100pn" ></iframe>';
                } else {
                    echo '<object data="/documents/getbatchfiles?batch_id='.$batch_id.'&file=report#view=Fit" id="document_file" type="' . $mimetype . '" class="documet_file height100pn width100pn">

                     <br/><br/>
                                If you see this text, you probably need Acrobat Reader plugin installed <br/>
                                For Internet Explorer : <a href="http://get.adobe.com/reader/"> Get Adobe Acrobat Reader </a><br/>
                                For Opera : <a href="https://addons.opera.com/extensions/download/pdf-viewer/"> Get Opera PDF extension </a><br/>


                    </object>';
                }
            }

        ?>
</div>
