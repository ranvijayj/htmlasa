
<?php
$model = Batches::model()->findByPk($bathes);


?>$document->image->Mime_Type $document->Document_ID
<div class="document_block width100p"
     id="document_block<?php echo $tabNum; ?>"
     data-mime-type="<?php echo $document->image->Mime_Type; ?>"
     data-id="<?php echo $document->Document_ID; ?>" data-subsid="<?php echo $subsectionID; ?>">

        <div class="gallery_detail_block" id="gallery_block<?php echo $tabNum; ?>"
        <?php
            if (strpos($document->image->Mime_Type, 'pdf')) {
                echo 'style="overflow: hidden"';
            }
        ?>
        >

        <?php
        if (strpos($document->image->Mime_Type, 'pdf')) {
            if (Helper::getMobileDetect()->isMobile() || Helper::getMobileDetect()->isTablet() || Helper::checkMobile()) {
                echo '<iframe src="http://docs.google.com/viewer?url=' . Helper::generateGoogleDocsUrl($document->Document_ID) . '&embedded=true" class="documet_file height100pn width100pn"></iframe> ';
            } else {
                echo '<embed src="/documents/getdocumentfile?doc_id=' . $document->Document_ID . '" id="document_file" type="' . $document->image->Mime_Type . '" class="documet_file height100pn width100pn">';
            }
        } else {
            echo '<img src="/documents/getdocumentfile?doc_id=' . $document->Document_ID . '" alt="" id="document_file" title="" class="documet_file width100pn">';
        }
        ?>
    </div>
    <div class="w9_detail_block_bar">
        <div class="image_buttons right">
            <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/fullsize.jpg" alt=""  class="w9_detail_block_bar_button fullsize_button"/>
            <?php
            if (strpos($document->image->Mime_Type, 'pdf') === false) {
                echo '<img src="' . Yii::app()->request->baseUrl . '/images/minlup.jpg" alt=""  class="w9_detail_block_bar_button minlup_button"/>
                      <img src="' . Yii::app()->request->baseUrl . '/images/pluslup.jpg" alt="" class="w9_detail_block_bar_button pluslup_button"/>';
            }
            ?>
        </div>
    </div>
</div>