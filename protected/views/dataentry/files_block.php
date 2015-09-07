<img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
<div id="file_detail_block_conteiner">
    <div id="file_detail_block_header">
        <?php
            $type = explode('/', $file['mimetype']);
            $type = $type[1];
            echo '<img src="' . Yii::app()->request->baseUrl . '/images/file_types/' . $type . '.png" class="img_type" />' . $file['name'] ;
        ?>
    </div>
    <div id="file_detail_block"
        <?php
            if (strpos($file['mimetype'], 'pdf')) {
                echo 'style="overflow: hidden"';
            }
        ?>
        >
        <?php
        if (strpos($file['mimetype'], 'pdf')) {
            if (Helper::getMobileDetect()->isMobile() || Helper::getMobileDetect()->isTablet() || Helper::checkMobile()) {
                if ($session) {
                    echo '<iframe src="http://docs.google.com/viewer?url=' . Helper::generateUploadsGoogleDocsUrl($imgId) . '&embedded=true" class="documet_file height100pn width100pn"></iframe> ';
                } else {
                    echo '<iframe src="http://docs.google.com/viewer?url=' . Helper::generateGoogleDocsUrl($imgId) . '&embedded=true" class="documet_file height100pn width100pn"></iframe> ';
                }
            } else {
                echo '<embed src="' . $url . $imgId . '" id="document_file" type="' . $file['mimetype'] . '" class="documet_file height100pn width100pn">';
            }
        } else {
            echo '<img src="' . $url .'" alt="" id="document_file" title="" class="documet_file width100pn">';
        }
        ?>
    </div>
    <div class="w9_detail_block_bar">
        <div class="image_buttons right">
            <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/fullsize.jpg" alt=""  class="w9_detail_block_bar_button fullsize_button"/>
            <?php
            if (strpos($file['mimetype'], 'pdf') === false) {
                echo '<img src="' . Yii::app()->request->baseUrl . '/images/minlup.jpg" alt=""  class="w9_detail_block_bar_button minlup_button"/>
                      <img src="' . Yii::app()->request->baseUrl . '/images/pluslup.jpg" alt="" class="w9_detail_block_bar_button pluslup_button"/>';
            }
            ?>
        </div>
    </div>
</div>