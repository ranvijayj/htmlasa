<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Preview document</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquerymin.js"></script>
</head>
<body>


<div id="embeded_pdf">
    <?php $this->widget('application.components.ShowPdfWidget', array(
        'params' => array(
            'doc_id'=>$doc_id,
            'mime_type'=>$mimetype,
            'mode'=>Helper::isMobileComplexCheck()? 5 : 3
        ),
    )); ?>
</div>
