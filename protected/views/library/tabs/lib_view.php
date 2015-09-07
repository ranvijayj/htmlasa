<?php
    if (count($documents) > 0)
    {
        ?>
        <div id="gallery_main_container<?php echo $tabNum; ?>" class="gallery_main_container" data-num="<?php echo $tabNum; ?>">
            <?php
            $this->renderPartial('application.views.library.lib_view', array(
                'tabNum' => $tabNum,
                'document' => $documents[0],
                'subsectionID' => $subsectionID,
            ));
            ?>
        </div>

        <div class="gallery_thumbs tab<?=$tabNum?>">
            <div class="left scroll_left_block">
                <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/arrow-left.png" alt="left" data-active="no" class="scroll_left not_active_arrow tab<?=$tabNum?>"/>
            </div>
            <div class="thumb_images">
                <div class="slider tab<?=$tabNum?>" data-active="yes">
                    <?php
                    $i = 1;
                    foreach ($documents as $document) {
                        echo '<div class="doc_thumb_image"' . (($i == 1) ? ' data-active="yes"  style="opacity: 1;"' : 'data-active="no"') . ' data-id="' . $document->Document_ID . '" data-numb="' . $i . '"><img data-src="/documents/getdocumentthumbnail?doc_id=' . $document->Document_ID . '" src="' . ($i <= 16 ? '/documents/getdocumentthumbnail?doc_id=' . $document->Document_ID . '' : '') . '" alt="" title="" class="width100" /></div>';
                        $i++;
                    }
                    ?>
                </div>
            </div>
            <div class="right scroll_right_block">
                <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/arrow-right.png" alt="right" data-active="yes" class="scroll_right tab<?=$tabNum?>"/>
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

