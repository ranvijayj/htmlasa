<div id="similar_files_block">
    <div id="similar_file_one" class="left">
        <div id="similar_file_one_conteiner">
            <div id="similar_file_one_block_header">
                <div id="new_file_name" data-filename="<?=$similar_file_to_upload['uploading']['name'];?>" style="display: none;"></div>
                <?php
                $type = explode('/', $similar_file_to_upload['uploading']['mimetype']);
                $type = $type[1];
                echo '<img src="' . Yii::app()->request->baseUrl . '/images/file_types/' . $type . '.png" class="img_type" />' . CHtml::encode($similar_file_to_upload['uploading']['name']) . ' - ' . 'file from current upload session';
                ?>
            </div>

            <div id="similar_file_one_block"
                <?php
                if (strpos($similar_file_to_upload['uploading']['mimetype'], 'pdf')) {
                    echo 'style="overflow: hidden"';
                }
                ?>
                >

                <?php
                if (strpos($similar_file_to_upload['uploading']['mimetype'], 'pdf')) {
                    if (Helper::getMobileDetect()->isMobile() || Helper::getMobileDetect()->isTablet() || Helper::checkMobile()) {
                        echo '<iframe src="http://docs.google.com/viewer?url=' . Helper::generateUploadsGoogleDocsUrl($similar_file_to_upload['uploading']['num']) . '&embedded=true" class="documet_file height100pn width100pn"></iframe> ';
                    } else {
                        echo '<embed src="/uploads/GetFileByName?filename=' . $similar_file_to_upload['uploading']['name'] . '" id="similar_file_one_file" type="' . $similar_file_to_upload['uploading']['mimetype'] .'" class="documet_file height100pn width100pn">';
                    }
                } else {
                    echo '<img src="/uploads/GetFileByName?filename=' . $similar_file_to_upload['uploading']['name'] . '" alt="" id="similar_file_one_file" title="" class="documet_file width100pn">';
                }
                ?>
            </div>

            <div class="w9_detail_block_bar">
                <div class="image_buttons right">
                    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/fullsize.jpg" alt=""  class="w9_detail_block_bar_button fullsize_button"/>
                    <?php
                    if (strpos($similar_file_to_upload['uploading']['mimetype'], 'pdf') === false) {
                        echo '<img src="' . Yii::app()->request->baseUrl . '/images/minlup.jpg" alt=""  class="w9_detail_block_bar_button minlup_button"/>
                              <img src="' . Yii::app()->request->baseUrl . '/images/pluslup.jpg" alt="" class="w9_detail_block_bar_button pluslup_button"/>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>


    <div id="similar_file_two" class="right">
        <div id="similar_file_two_conteiner">
            <div id="similar_file_two_block_header">
                <?php
                $type = explode('/', $similar_file_to_upload['uploaded']['mimetype']);
                $type = $type[1];
                echo '<img src="' . Yii::app()->request->baseUrl . '/images/file_types/' . $type . '.png" class="img_type" />' . CHtml::encode($similar_file_to_upload['uploaded']['name']) . ' - ' . 'file uploaded earlier';
                ?>
            </div>

            <div id="similar_file_two_block"
                <?php
                if (strpos($similar_file_to_upload['uploaded']['mimetype'], 'pdf')) {
                    echo 'style="overflow: hidden"';
                }
                ?>
                >

                <?php
                if (strpos($similar_file_to_upload['uploaded']['mimetype'], 'pdf')) {
                    if (Helper::getMobileDetect()->isMobile() || Helper::getMobileDetect()->isTablet() || Helper::checkMobile()) {
                        echo '<iframe src="http://docs.google.com/viewer?url=' . Helper::generateGoogleDocsUrl($similar_file_to_upload['uploaded']['doc_id']) . '&embedded=true" class="documet_file height100pn width100pn"></iframe> ';
                    } else {
                        echo '<embed src="/documents/getusersdocumentfile?doc_id=' . $similar_file_to_upload['uploaded']['doc_id'] . '" id="similar_file_two_file" type="' . $similar_file_to_upload['uploaded']['mimetype'] . '" class="documet_file height100pn width100pn">';
                    }
                } else {
                    echo '<img src="/documents/getusersdocumentfile?doc_id=' . $similar_file_to_upload['uploaded']['doc_id'] . '" alt="" id="similar_file_two_file" title="" class="documet_file width100pn">';
                }
                ?>
            </div>

            <div class="w9_detail_block_bar">
                <div class="image_buttons right">
                    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/fullsize.jpg" alt=""  class="w9_detail_block_bar_button fullsize_button"/>
                    <?php
                    if (strpos($similar_file_to_upload['uploaded']['mimetype'], 'pdf') === false) {
                        echo '<img src="' . Yii::app()->request->baseUrl . '/images/minlup.jpg" alt=""  class="w9_detail_block_bar_button minlup_button"/>
                          <img src="' . Yii::app()->request->baseUrl . '/images/pluslup.jpg" alt="" class="w9_detail_block_bar_button pluslup_button"/>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>


