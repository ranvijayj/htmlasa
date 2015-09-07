<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Print document</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquerymin.js"></script>
</head>
<body>
<div style="width: 640px">
        <?php
        if (strpos($file->Mime_Type, 'pdf')) {
            echo '<embed src="/documents/getdocumentfile?doc_id=' . $document->Document_ID . '" width="640" height="600" id="document" type="' . $file->Mime_Type . '">';
        } else {
            echo '<img src="/documents/getdocumentfile?doc_id=' . $document->Document_ID . '" alt="" title="" width="640" id="document">';
            ?>
            <script type="text/javascript">
                $(document).ready(function() {
                    setTimeout(function() {
                        window.print();
                        window.close();
                    }, 200)
                });
            </script>
            <?php
        }
        ?>
</div>
</body>
</html>