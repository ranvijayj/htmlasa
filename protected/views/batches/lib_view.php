<div class="document_block width100p" id="document_block<?php echo $tabNum; ?>" data-mime-type="application/pdf" data-id="<?php echo $batchID; ?>">
    <div class="gallery_detail_block" id="gallery_block<?php echo $tabNum; ?>" style="overflow: hidden" >
        <?php
        if (Helper::getMobileDetect()->isMobile() || Helper::getMobileDetect()->isTablet() || Helper::checkMobile()) {
//            echo '<iframe src="http://docs.google.com/viewer?url=' . Helper::generateGoogleDocsUrl($document->Document_ID) . '&embedded=true" class="documet_file height100pn width100pn"></iframe> ';
        } else {
            echo '<embed src="/documents/getbatchsummary?batch_id=' . $batchID . '" id="document_file" type="application/pdf" class="documet_file height100pn width100pn">';
        }

        ?>
    </div>
    <div class="w9_detail_block_bar">
        <div class="image_buttons right">
            <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/fullsize.jpg" alt=""  class="w9_detail_block_bar_button fullsize_button"/>

        </div>
    </div>
</div>

