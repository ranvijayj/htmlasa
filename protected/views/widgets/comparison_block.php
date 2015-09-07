<div class="modal_box" id="comparisonmodal" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <h1 style="margin-bottom: 0px; line-height: 35px; padding: 0px;">File with the same contents was uploaded earlier</h1>

        <div id="similar_files_block">
            <div id="similar_files_block_wrapper">
                <div id="similar_file_one" class="left">

                </div>
                <div id="similar_file_two" class="right">

                </div>
        </div>
    </div>
    <div style="clear: both;"></div>
    <span style="padding-bottom: 5px; font-size: 15px;">Please make a decision either leave the file in current upload or delete selected file from current upload</span>
    <div class="center">

        <button id="delete_dublicate_file" class="button" style="margin-left: 15px;">Delete file</button>
        <button id="leave_dublicate_file" class="button" style="margin-left: 15px;">Leave file</button>
    </div>
</div>