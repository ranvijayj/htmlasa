<div class="document_block width100p" id="document_block<?php echo "Tab1"; ?>" data-mime-type="<?php echo 'application/pdf'; ?>" data-id="<?php echo "480"; ?>"
     data-subsid="<?php echo "SubsectionID"; ?>">
    <div class="gallery_detail_block" id="gallery_block<?php echo "33"; ?>"
        <?php
        if (1==1) {
            echo 'style="overflow: hidden"';
        }
        ?>
        >
        <?php
        if ('pdf'=='pdf') {
            if (Helper::getMobileDetect()->isMobile() || Helper::getMobileDetect()->isTablet() || Helper::checkMobile()) {
                echo '<iframe src="http://docs.google.com/viewer?url=' . Helper::generateGoogleDocsUrlForBatch(480) . '&embedded=true"
                class="documet_file height100pn width100pn"></iframe> ';
            } else {
                echo '<embed src="/documents/GetBatchFiles?$batch_id=' . 480 . '" id="document_file" type="' . $document->image->Mime_Type . '" class="documet_file height100pn width100pn">';
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

