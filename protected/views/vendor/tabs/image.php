<?php if ($file) { ?>


    <div id="tab1_block">

        <?php if ($company->Temp_Fed_ID_Flag=='T' || $company->Temp_Fed_ID_Flag=='N') { ?>
            <div style="margin-left: 280px;padding-bottom: 5px;"> <a href="#" class="change_vendor" data-mode="vendor_detail_mode" data-fed_id="<?=$company->Company_Fed_ID?>" data-doc_id="<?=$document->Document_ID?>" >Click to change</a> </div>
            <input id="fileupload_add_block" type="file" name="files[]" style="display: none;">
            <script src="<?php echo Yii::app()->request->baseUrl.'/js/jupload/vendor/jquery.ui.widget.js'?>" > </script>
            <script src="<?php echo Yii::app()->request->baseUrl.'/js/jupload/jquery.fileupload.js'?>" > </script>

        <?}?>

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
    <p class="no_images">Currently this Vendor doesn't have any W9 uploaded.</p>
</div>
<?php } ?>