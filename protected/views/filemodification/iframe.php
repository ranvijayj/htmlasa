<div class="w9_detail_block" id="w9_detail_block1">

<?if (strpos($mime_type, 'pdf')) {
    if (Helper::getMobileDetect()->isMobile() || Helper::getMobileDetect()->isTablet() || Helper::checkMobile()) {
        echo '<iframe src="http://docs.google.com/viewer?url=' . Helper::generateGoogleDocsUrl($doc_id) . '&embedded=true" class="documet_file height100pn width100pn"></iframe> ';
    } else {
        $browser = Helper::getBrowser();
        if ($browser['name']=='Google Chrome') {
            echo '<iframe src="/documents/getdocumentfile?doc_id=' . $doc_id .'" class="documet_file height100pn width100pn" ></iframe>';
        } else {
            echo '<object data="/documents/getdocumentfile?doc_id=' . $doc_id . '#view=Fit" id="document_file" type="' . $mime_type . '" class="documet_file height100pn width100pn">

                     <br/><br/>
                                If you see this text, you probably need Acrobat Reader plugin installed <br/>
                                For Internet Explorer : <a href="http://get.adobe.com/reader/"> Get Adobe Acrobat Reader </a><br/>
                                For Opera : <a href="https://addons.opera.com/extensions/download/pdf-viewer/"> Get Opera PDF extension </a><br/>


                    </object>';
        }
    }
} else {
    echo '<img src="/documents/getdocumentfile?doc_id=' . $doc_id . '" alt="" id="document_file" title="" class="documet_file width100pn">';
}
?>

</div>
<div class="w9_detail_block_bar">
    <div class="image_buttons right">



        <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/fullsize.jpg" alt=""  class="w9_detail_block_bar_button fullsize_button"/>
        <?php
        if (strpos($mime_type, 'pdf') === false) {
            echo '<img src="' . Yii::app()->request->baseUrl . '/images/minlup.jpg" alt=""  class="w9_detail_block_bar_button minlup_button"/>
                      <img src="' . Yii::app()->request->baseUrl . '/images/pluslup.jpg" alt="" class="w9_detail_block_bar_button pluslup_button"/>';
        }

        echo $show_rotate_buttons_block;
        ?>
    </div>
</div>