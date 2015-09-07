<div id="tab1_block">
    <div class="document_block" id="document_block"
        <?php
        if (strpos($image->Mime_Type, 'pdf')) {
            echo 'style="overflow: hidden"';
        }
        ?>
        >
        <?php
        if (strpos($image->Mime_Type, 'pdf')) {
            if (Helper::getMobileDetect()->isMobile() || Helper::getMobileDetect()->isTablet() || Helper::checkMobile()) {
                echo '<iframe src="http://docs.google.com/viewer?url=' . Helper::generateGoogleDocsUrl($image->Document_ID) . '&embedded=true" class="documet_file height100pn width100pn"></iframe> ';
            } else {
                echo '<embed src="/documents/getusersdocumentfile?doc_id=' . $image->Document_ID . '" id="document_file" type="' . $image->Mime_Type . '" class="documet_file width100pn height100pn">';
            }
        } else {
            echo '<img src="/documents/getusersdocumentfile?doc_id=' . $image->Document_ID . '" alt="" id="document_file" title="" class="documet_file height100pn">';
        }
        ?>
    </div>
    <div class="w9_detail_block_bar">
        <div class="image_buttons right">
            <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/fullsize.jpg" alt=""  class="w9_detail_block_bar_button fullsize_button"/>
            <?php
            if (strpos($image->Mime_Type, 'pdf') === false) {
                echo '<img src="' . Yii::app()->request->baseUrl . '/images/minlup.jpg" alt=""  class="w9_detail_block_bar_button minlup_button"/>
                      <img src="' . Yii::app()->request->baseUrl . '/images/pluslup.jpg" alt="" class="w9_detail_block_bar_button pluslup_button"/>';
            }
            ?>
        </div>
    </div>
</div>