<div id="tab3_block">
    <div class="embeded_pdf">
        <?php $this->widget('application.components.ShowPdfWidget', array(
            'params' => array(
                'doc_id'=>intval($document->Document_ID),
                'mime_type'=>$file->Mime_Type,
                'mode'=>Helper::isMobileComplexCheck()? 5 : 3,
                'approved'=>1 //needs for printing
            ),
        )); ?>
    </div>

</div>