<?php if ($document) { ?>
    <div id="tab2_block">

        <div id="embeded_pdf">
            <?php $this->widget('application.components.ShowPdfWidget', array(
                'params' => array(
                    'doc_id'=>$document->Document_ID,
                    'mime_type'=>$file->Mime_Type,
                    'mode'=>Helper::isMobileComplexCheck()? 5 : 3,
                    'approved'=>1
                ),
            )); ?>
        </div>


    </div>
<?php } else { ?>
    <div class="w9_detail_block">
        <p class="no_images">There is no previous available W9s for this Vendor</p>
    </div>
<?php } ?>