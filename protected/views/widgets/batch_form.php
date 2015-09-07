<div class="modal_box" id="batch_export_modal_box" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <h1><?php echo $docType; ?> Batch Export</h1>
    <form id="batch_export_form">
        <div class="row">
            <label for="batch_export_type">
                Export Type:
            </label>
            <select id="batch_export_type" name="type" class="txtfield">
                <?php
                    foreach (Batches::$exportTypes as $value => $title) {
                        echo '<option value="' . $value . '" ' . (($value == $userSettings->Default_Export_Type) ? 'selected="selected"' : '') . ' >' . $title . '</option>';
                    }
                ?>
            </select>
        </div>
        <div class="row">
            <label for="batch_export_format">
                Export Format
            </label>
            <select id="batch_export_format" name="format" class="txtfield">
                <?php
                foreach (Batches::$exportFormats as $value => $title) {
                    echo '<option value="' . $value . '" ' . (($value == $userSettings->Default_Export_Format) ? 'selected="selected"' : '') . ' >' . $title . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="center">
            <input class="button" type="submit" value="Export">
        </div>
    </form>
</div>
<div class="modal_box" id="batch_export_result_box" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <h1><?php echo $docType; ?> Batch Export</h1>
    <span></span>

</div>