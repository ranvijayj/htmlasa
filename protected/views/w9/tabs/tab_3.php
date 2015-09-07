<?php if ($lastDocument) { ?>
    <div id="tab3_block">

        <div id=""embeded_pdf>
            <?php $this->widget('application.components.ShowPdfWidget', array(
                'params' => array(
                    'doc_id'=>intval($lastDocument->Document_ID),
                    'mime_type'=>$file->Mime_Type,
                    'mode'=>Helper::isMobileComplexCheck()? 5 : 3,
                    'approved'=>1
                ),
            )); ?>
        </div>

    </div>
<?php } else { ?>
    <div class="w9_detail_block">
        <p class="no_images">There is no previous available W9s for this Company</p>
    </div>
<?php } ?>