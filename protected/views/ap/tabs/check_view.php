<?php if (count($check) > 0) { ?>



    <div id="tab3_block">

        <div id="embeded_pdf">
            <?php $this->widget('application.components.ShowPdfWidget', array(
                'params' => array(
                    'doc_id'=>intval($check['payment']->Document_ID),
                    'mime_type'=>$check['image']->Mime_Type,
                    'mode'=>Helper::isMobileComplexCheck()? 5 : 3,
                    'approved'=>1
                ),
            )); ?>
        </div>


    </div>
<?php } else { ?>
    <div class="w9_detail_block">
        <p class="no_images">Currently this AP doesn't have any Checks.</p>
    </div>
<?php } ?>