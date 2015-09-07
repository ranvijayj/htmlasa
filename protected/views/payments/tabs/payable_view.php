<?php if (count($ap) > 0) { ?>
    <div id="tab1_block">

        <div class="embeded_pdf">
            <?php $this->widget('application.components.ShowPdfWidget', array(
                'params' => array(
                    'doc_id'=>intval($ap['payment']->Document_ID),
                    'mime_type'=>$ap['image']->Mime_Type,
                    'mode'=>Helper::isMobileComplexCheck()? 5 : 3,
                    'approved'=>1 //needs for printing
                ),
            )); ?>
        </div>

    </div>
<?php } else { ?>
    <div class="w9_detail_block">
        <p class="no_images">Currently this Payment doesn't have any APs.</p>
    </div>
<?php } ?>