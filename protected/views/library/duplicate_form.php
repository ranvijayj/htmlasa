<h1>Duplicate Document</h1>
<img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt='' class="hidemodal cancelbutton"/>
<form id="duplicateform">
    <div class="row">
        <div style="width: 150px; float: left;">
            <select name="duplicate_type" class="txtfield" style="width: 150px;" id="duplicate_type">
                <option  value="1">Move </option>
                <option  selected="selected" value="2">Copy </option>
            </select>
        </div>
        <div style="width: 100px; float: right; line-height: 36px; font-size: 15px;">
            document to:
        </div>
        <div style="clear: both;"></div>
    </div>
    <div class="row">
        <label for="new_storage_type" class="error required">Storage type</label>
        <select name="new_storage_type" class="txtfield" id="new_storage_type">
            <option selected="selected" value="0">Chose storage type</option>
            <option value="1">Cabinet</option>
            <option value="2">Shelf</option>
        </select>
    </div>
    <div class="row">
        <label for="new_storage" class="error required">Cabinet/Shelf</label>
        <select name="new_storage" class="txtfield" id="new_storage" disabled="disabled">
            <option selected="selected" value="0">Chose cabinet/shelf</option>
        </select>
    </div>
    <div class="row">
        <label for="new_section" class="error required">Folder/Binder</label>
        <select name="new_section" class="txtfield" id="new_section" disabled="disabled">
            <option selected="selected" value="0">Chose folder/binder</option>
        </select>
    </div>
    <div class="row">
        <label for="new_subsection" class="error required">Panel/Tab <span class="required">*</span></label>
        <select name="new_subsection" class="txtfield" id="new_subsection" disabled="disabled">
            <option selected="selected" value="0">Chose panel/tab</option>
        </select>
        <div class="errorMessage hidden" id="new_subsection_error"></div>
    </div>
    <div style="clear: both; height: 10px;"></div>
    <div class="center">
        <button id="submit_duplicate" class="button ">Submit</button>
    </div>
</form>

