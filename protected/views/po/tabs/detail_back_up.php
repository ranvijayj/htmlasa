<?php
/**
 * this view used for "po/detail" page
 */ 
if ($backUp['file']) {
?>
    <div id="tab2_block">
        <div id="embeded_pdf">
            <?php $this->widget('application.components.ShowPdfWidget', array(
                'params' => array(
                    'doc_id'=>intval($backUp['document']->Document_ID),
                    'mime_type'=>$backUp['file']->Mime_Type,
                    'mode'=>Helper::isMobileComplexCheck()? 5 : 3,
                    'approved'=>$approved
                ),
            )); ?>
        </div>
    </div>
<?php } else { ?>
    <div class="w9_detail_block">
        <p class="no_images">This PO doesn't have any Backup.

            <a href="#" id="fileupload_link">Click to Upload Backup</a></p>
            <input id="fileupload" type="file" name="files[]" style="display: none;" >


    </div>
<?php } ?>
<script>
    $(document).ready(function() {
        new FileUploading('PO_BU');
        new ProgressBar('PO');

        $('#fileupload_link').click(function(event){
            event.stopPropagation();
            $('#fileupload').click();

        });



    });
</script>