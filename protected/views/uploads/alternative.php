<?php
/* @var $this VendorController */

$this->breadcrumbs=array('Upload');
?>
<h1>Upload Images: <?=@CHtml::encode(Yii::app()->user->userLogin);?><span class="right items_to_review">Number Items: <span id="number_items"><?php echo count($current_uploads); ?></span> items</span></h1>

<div class="account_manage">
    <div class="account_header_left_for_uploads left">

        <?echo '<div id="project_id" style="display: none" data-id="'.$enableUploading.'"></div>';?>

        <button id="file_upload_button" class="button">Upload files...</button>
        <input id="fileupload" type="file" name="files[]" multiple style="display: none;">

        <input id="filecounter" type="hidden" value="1">

        <button <?php

                        echo $enableUploading == 0 ? 'class="not_active_button" id="submit_uploaded_file"'
                                                   :
                                                   'class="not_active_button" data-message="' . $disableUploadingMessage . '" data-type="' . $enableUploading . '"';
                ?>
                >Save
        </button>
                <?php if (count($current_uploads) > 0) { ?>
            <button style="margin-left: 170px;" class="clear_upload_session button" id="clear_upload_session">Clear List</button>
        <?php } else { ?>
        <button style="margin-left: 170px; display: none;" class="clear_upload_session button" id="clear_upload_session" >Clear List</button>
        <?php } ?>
    </div>

    <div class="right" >
        <a class="button_margin_left button right" href="<?=Yii::app()->createUrl("documents/deletedocuments")?>"> Delete </a>
        <?php
            if ($availableStorage != 0) {
                echo "<span class='account_manage_b_span' style='padding-right:20px;'>Used " . number_format($usedStorage, 2) . "GB of " . number_format($availableStorage) . "GB (" . number_format(100*$usedStorage/$availableStorage, 1) . "%)</span>";
            }
        ?>
    </div>
</div>


<div class="left_column" id="left_column">
    <h2>Current Upload:</h2>
    <table id="current_upload_grid" class="uploads_grid border0">
        <thead>
        <tr>
            <th class="width60">Doc Type</th><th class="width250">File Name</th><th class="width100" colspan="2">Additional fields</th>
            <th class="width10" style="padding: 0px;"></th>
        </tr>
        </thead>
        <tbody>
<?$current_uploads = $_SESSION['current_upload_files']; ?>
<?php
if (count($current_uploads) > 0) {
    foreach ($current_uploads as $key => $current_upload_file) {
        // cut long filename
        if (strlen($current_upload_file['name']) > 38) {
            $filename = substr($current_upload_file['name'], 0 , 35) . '...';
        } else {
            $filename = $current_upload_file['name'];
        }
        $type = explode('/', $current_upload_file['mimetype']);
        $type = $type[1];

        // set additional fields cell
        if ($current_upload_file['doctype'] == Documents::W9 && $current_upload_file['complete'] == false) {
            $complete = '<span class="additional_field_pointer" style="color: #f00;" data="' . $key . '">REQUIRED</span>';
        } else if ($current_upload_file['doctype'] == Documents::W9 && $current_upload_file['complete'] == true) {
            $complete = '<span class="additional_field_pointer" style="color: #41B50B;" data="' . $key . '">Complete</span>';
        } else {
            $complete = "";
        }

        //set dropDown cell
        $dropDownCell = '<div class="dropdown_cell_ul">
                                             ' . Yii::app()->user->tier_settings['docsHtml'] . '
                                             <span class="dropdown_cell_value">' . $current_upload_file['doctype'] . '</span>
                                         </div>';


        echo '<tr id="image' . $key . '">
                                  <td class="dropdown_cell_upload">' . $dropDownCell . '</td>
                                  <td class="uploaded_file_name"><span><img src="' . Yii::app()->request->baseUrl . '/images/file_types/' . $type . '.png" alt="' . strtoupper($type) . '" class="img_type" />'
                                . CHtml::encode(Helper::shortenString($current_upload_file['name'],30)) . '</span></td>
                                  <td class="additions_cell">
                                       ' . $complete . '
                                  </td>
                                  <td class="dublicate"><span class="dublicate_field_pointer" style="font-size:12px; color: #f00;cursor:pointer;">
                                                       ' . $current_upload_file['dublicate'] . '</span>
                                  </td>
                                  <td style="padding: 0px;"><img src="' . Yii::app()->request->baseUrl . '/images/delete.png" alt="Delete file from upload session?" title="Delete file from upload session?" class="delete_file" id="delete_file_' . $key . '" style="cursor: pointer;"/></td>
                              </tr>';
    }
} else {
    echo '<tr id="no_images">
                          <td colspan="4">Select documents for uploading</td>
                      </tr>';
}
?>

        </tbody>
    </table>
    <div id="current_upload_files"><?php if (count($current_uploads) > 0) {
            foreach ($current_uploads as $current_upload_file) {
                echo "%" . $current_upload_file['name'];
            }
        }
    ?></div>
</div>
<div class="right_column" id="right_column">
    <!--
        <div id="preview_block"></div>
    -->
    <div id="upload_history_block">
        <h2>Upload History:</h2>
        <table id="last_upload_grid" class="uploads_grid border0">
            <thead>
            <tr><th>File Name</th></tr>
            </thead>
            <tbody>
            <?php if (count($last_images) > 0) {
                foreach ($last_images as $last_image) {
                    $type = explode('/', $last_image['Mime_Type']);
                    $type = $type[1];
                    echo "<tr><td data-id='" . $last_image['Document_ID'] . "'><img src='" . Yii::app()->request->baseUrl . "/images/file_types/" . $type . ".png' alt='" . strtoupper($type) . "' class='img_type' />" . Helper::cutText(12,500,50,$last_image['File_Name'])  . "</td></tr>";
                }
            } else {
                ?>
                <tr id="no_images">
                    <td>Documents were not found.</td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <div id="last_upload_files"><?php if (count($last_images) > 0) {
            foreach ($last_images as $last_image) {
                echo "%" . $last_image['File_Name'];
            }
        }
    ?></div>
</div>

<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/doc_create.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/uploads_page_alternative.js"></script>

    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/document_view.js"></script>

<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/progress_bar.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/file_uploading.js"></script>



<?php
Yii::app()->clientScript->registerScriptFile('/js/mousewheel.js', CClientScript::POS_HEAD);
?>
<?php
$this->renderPartial('//widgets/image_view_block');

?>



    <script>
        $(document).ready(function() {
            up=new UploadsPageAlternative();
         <?php
                if (Yii::app()->user->projectID === 'all') {
                   ?>
                   setTimeout(function() {
                      show_alert('Please select a specific Project for this process.', 430);
                   }, 300);
        <?}?>



        });


    </script>




