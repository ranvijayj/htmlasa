<?php


    if (count($batches) > 0)
    {
        ?>
        <div id="gallery_main_container1" class="gallery_main_container" data-num="1">

            <?php
            foreach ($batches as $batchID) {
            $this->renderPartial('application.views.batches.lib_view', array(
                'tabNum' => 1,
                'batchID' => $batchID,
            ));
                break;
            }
            ?>


        </div>


        <div class="gallery_thumbs">
            <div class="left scroll_left_block">
                <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/arrow-left.png" alt="left" data-active="no" class="scroll_left not_active_arrow"/>
            </div>
            <div class="thumb_images">
                <div class="slider tab1" data-active="yes">
                    <?php
                    $i = 1;
                    foreach ($batches as $batchID) {
                        echo '<div class="doc_thumb_image"' . (($i == 1) ? ' data-active="yes"  style="opacity: 1;"' : 'data-active="no"') . ' data-id="' . $batchID. '" data-numb="' . $i . '"><img data-src="/documents/getbatchthumbnail?batch_id=' . $batchID . '" src="' . ($i <= 16 ? '/documents/getbatchthumbnail?batch_id=' . $batchID . '' : '') . '" alt="" title="" class="width100" /></div>';
                        $i++;
                    }
                    ?>
                </div>
            </div>
            <div class="right scroll_right_block">
                <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/arrow-right.png" alt="right" data-active="yes" class="scroll_right"/>
            </div>
            <div class="clear"></div>
        </div>
        <?php
    } else {
    ?>
        <div class="gallery_detail_block">
            <p class="no_images">Currently this tab doesn't have any Documents.</p>
        </div>
    <?php
    }
?>

