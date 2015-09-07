<?php

if (count($ap) > 0 && count($apBackup) > 0) { ?>
    <div id="tab2_block">

        <div class="embeded_pdf">
            <?php $this->widget('application.components.ShowPdfWidget', array(
                'params' => array(
                    'doc_id'=>intval($ap['payment']->AP_Backup_Document_ID),
                    'mime_type'=>$apBackup['image']->Mime_Type,
                    'mode'=>Helper::isMobileComplexCheck()? 5 : 3,
                    'approved'=>1 //needs for printing
                ),
            )); ?>
        </div>


    </div>
<?php } else { ?>
    <div class="w9_detail_block">
        <p class="no_images">Currently this Payment doesn't have any AP Backups.</p>
        <?

          //  echo "$ap['Count_AP']"
            if($ap['Count_AP']=='') {$ap['Count_AP']=0;}
            echo 'This payment connected to '.$ap['Count_AP'] .' Aps<br/>';
            if($ap['Count_AP']==0) {
                echo "You can't attach Backup without AP connected<br/>";
            }
            if($ap['Count_AP']==1) {
            echo '<a href="#" id="fileupload_link">Click to Upload Backup</a></p>';
            }
            if($ap['Count_AP']>1) {
            echo "You can't attach Backup because more than one AP is connected. <br/>You can attach backup from the 'AP Approval List » Detail view' " ;
            }

        ?>

        <input id="fileupload" type="file" name="files[]" style="display: none;" >
    </div>
<?php } ?>
<script>
    $(document).ready(function() {
        new FileUploading('PAY_BU');
        new ProgressBar('PAY');

        $('#fileupload_link').click(function(event){
            event.stopPropagation();
            $('#fileupload').click();

        });



    });
</script>