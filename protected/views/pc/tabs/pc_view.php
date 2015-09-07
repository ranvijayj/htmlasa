<div id="tab1_block">
    <div id="embeded_pdf">
        <?php $this->widget('application.components.ShowPdfWidget', array(
            'params' => array(
                'doc_id'=>$document->Document_ID,
                'mime_type'=>$file->Mime_Type,
                'mode'=>Helper::isMobileComplexCheck()? 5 : 3
            ),
        )); ?>
    </div>
</div>