<img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
<div id="file_detail_block_conteiner">
    <div id="file_detail_block_header">
        <?php
            echo '<img src="' . Yii::app()->request->baseUrl . '/images/file_types/' . $type . '.png" class="img_type" />' . $file['name'] ;
        ?>
    </div>


    <div id="embeded_pdf">

        <iframe src='/file/pdfjs?id="<?=urlencode($filepath)?>"&approved=<?=0?>' style="width: 99.9%;height: <?=800; ?>px; ">      </iframe>

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
