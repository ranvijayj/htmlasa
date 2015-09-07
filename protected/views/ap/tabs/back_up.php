<?php if ($backUp['file']) { ?>



    <div id="tab2_block">

        <div id="embeded_pdf">
            <?php $this->widget('application.components.ShowPdfWidget', array(
                'params' => array(
                    'doc_id'=>intval($backUp['document']->Document_ID),
                    'mime_type'=>$backUp['file']->Mime_Type,
                    'mode'=>Helper::isMobileComplexCheck()? 5 : 3,
                    'approved'=>1,
                ),
            )); ?>
        </div>


    </div>
<?php } else { ?>
    <div class="w9_detail_block">
        <p class="no_images">Currently this AP doesn't have any Backup.</p>
        <?echo '<a href="#" id="fileupload_link">Click to Upload Backup</a></p>';?>
    </div>
<?php } ?>

<script>
    $(document).ready(function() {
   /*     new FileUploading('PAY_BU');*/
     /*   new ProgressBar('PAY');*/

        $('#fileupload_link').click(function(event){
            event.stopPropagation();
            $('#fileupload').click();
        });
    });
</script>