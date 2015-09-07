<div id="tab1_block">
    <div class="w9_detail_block" id="w9_detail_block1"
        <?php
            if (strpos($file->Mime_Type, 'pdf')) {
                echo 'style="overflow: hidden"';
            }
        ?>
        >
        <?php
        if (strpos($file->Mime_Type, 'pdf')) {
            echo '<embed src="/documents/getdocumentfile?doc_id=' . $document->Document_ID . '" id="document_file" type="' . $file->Mime_Type . '" class="documet_file width100pn height100pn">';
        } else {
            echo '<img src="/documents/getdocumentfile?doc_id=' . $document->Document_ID . '" alt="" id="document_file" title="" class="documet_file width100pn">';
        }
        ?>
    </div>
    <div class="w9_detail_block_bar">
        <div class="image_buttons right">
            <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/fullsize.jpg" alt=""  class="w9_detail_block_bar_button fullsize_button"/>
            <?php
            if (strpos($file->Mime_Type, 'pdf')) {

            } else {
                echo '<img src="' . Yii::app()->request->baseUrl . '/images/minlup.jpg" alt=""  class="w9_detail_block_bar_button minlup_button"/>
                      <img src="' . Yii::app()->request->baseUrl . '/images/pluslup.jpg" alt="" class="w9_detail_block_bar_button pluslup_button"/>';
            }
            ?>
        </div>
    </div>
</div>