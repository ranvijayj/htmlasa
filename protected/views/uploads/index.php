<?php
/* @var $this VendorController */

$this->breadcrumbs=array('Upload');
?>
<h1>Upload Images: <?=@CHtml::encode(Yii::app()->user->userLogin);?><span class="right items_to_review">Number Items: <span id="number_items"><?php echo count($current_uploads); ?></span> items</span></h1>

<div class="account_manage">
    <div class="account_header_left_for_uploads left">
        <button class="button" id="select_uploaded_file" style="z-index: 12147483583;">Select</button>
        <button <?php echo $enableUploading == 0 ? 'class="button" id="submit_uploaded_file"' : 'class="button disable_uploading" data-message="' . $disableUploadingMessage . '" data-type="' . $enableUploading . '"';?>>Upload</button>
        <?php if (count($current_uploads) > 0) { ?>
            <button style="margin-left: 170px;" class="clear_upload_session button" id="clear_upload_session">Clear List</button>
        <?php } ?>
    </div>
    <div class="right">
        <a class="button_margin_left right" href="<?=Yii::app()->createUrl("documents/deletedocuments")?>"> Delete </a>
        <?php
        if ($availableStorage != 0) {
            echo "<span class='account_manage_b_span'>Used " . number_format($usedStorage, 2) . "GB of " . number_format($availableStorage) . "GB (" . number_format(100*$usedStorage/$availableStorage, 1) . "%)</span>";
        }
        ?>

    </div>
</div>
<?php if(Yii::app()->user->hasFlash('success')):?>
    <div class="info">
        <button class="close-alert">&times;</button>
        <?php echo Yii::app()->user->getFlash('success'); ?>
    </div>
<?php endif; ?>
<div class="left_column" id="left_column">
    <h2>Current Upload:</h2>
    <table id="current_upload_grid" class="uploads_grid border0">
        <thead>
        <tr>
            <th class="width60">Doc Type</th><th class="width250">File Name</th><th class="width100">Additional fields</th>
            <th class="width10" style="padding: 0px;"></th>
        </tr>
        </thead>
        <tbody>
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
                    if ($clientServiceSettings->Service_Level_ID == ServiceLevelSettings::DEFAULT_SERVICE_LEVEL) {
                        $dropDownCell = ServiceLevelSettings::$serviceLevelAvailableDocTypes[$clientServiceSettings->Service_Level_ID]['docsHtml'];
                    } else {
                        $dropDownCell = '<div class="dropdown_cell_ul">
                                             ' . ServiceLevelSettings::$serviceLevelAvailableDocTypes[$clientServiceSettings->Service_Level_ID]['docsHtml'] . '
                                             <span class="dropdown_cell_value">' . $current_upload_file['doctype'] . '</span>
                                         </div>';
                    }

                    echo '<tr id="image' . $key . '">
                                  <td class="dropdown_cell_upload">' . $dropDownCell . '</td>
                                  <td class="uploaded_file_name"><span><img src="' . Yii::app()->request->baseUrl . '/images/file_types/' . $type . '.png" alt="' . strtoupper($type) . '" class="img_type" />' . CHtml::encode($filename) . '</span></td>
                                  <td class="additions_cell">
                                       ' . $complete . '
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
                    echo "<tr><td data-id='" . $last_image['Document_ID'] . "'><img src='" . Yii::app()->request->baseUrl . "/images/file_types/" . $type . ".png' alt='" . strtoupper($type) . "' class='img_type' />" . CHtml::encode($last_image['File_Name']) . "</td></tr>";
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

<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/ajaxupload.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/document_view.js"></script>

<?php
Yii::app()->clientScript->registerScriptFile('/js/mousewheel.js', CClientScript::POS_HEAD);
?>
<?php
$this->renderPartial('//widgets/image_view_block');
if ($show_similar_files_block) {
?>

    <div id="similar_file_one_conteiner">
        <div id="similar_file_one_block_header">
            <?php

            $type = explode('/', $similar_file_to_upload['uploading']['mimetype']);
            $type = $type[1];
            echo '<img src="' . Yii::app()->request->baseUrl . '/images/file_types/' . $type . '.png" class="img_type" />' . CHtml::encode($similar_file_to_upload['uploading']['name']) . ' - ' . 'file from current upload session';

            ?>
        </div>
        <div id="similar_file_one_block"
            <?php
            if (strpos($similar_file_to_upload['uploading']['mimetype'], 'pdf')) {
                echo 'style="overflow: hidden"';
            }
            ?>
            >
            <?php
            if (strpos($similar_file_to_upload['uploading']['mimetype'], 'pdf')) {
                if (Helper::getMobileDetect()->isMobile() || Helper::getMobileDetect()->isTablet() || Helper::checkMobile()) {
                    echo '<iframe src="http://docs.google.com/viewer?url=' . Helper::generateUploadsGoogleDocsUrl($similar_file_to_upload['uploading']['num']) . '&embedded=true" class="documet_file height100pn width100pn"></iframe> ';
                } else {
                    echo '<embed src="/uploads/getdocumentfile?doc_num=' . $similar_file_to_upload['uploading']['num'] . '" id="similar_file_one_file" type="' . $similar_file_to_upload['uploading']['mimetype'] .'" class="documet_file height100pn width100pn">';
                }
            } else {
                echo '<img src="/uploads/getdocumentfile?doc_num=' . $similar_file_to_upload['uploading']['num'] . '" alt="" id="similar_file_one_file" title="" class="documet_file width100pn">';
            }
            ?>
        </div>
        <div class="w9_detail_block_bar">
            <div class="image_buttons right">
                <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/fullsize.jpg" alt=""  class="w9_detail_block_bar_button fullsize_button"/>
                <?php
                    if (strpos($similar_file_to_upload['uploading']['mimetype'], 'pdf') === false) {
                        echo '<img src="' . Yii::app()->request->baseUrl . '/images/minlup.jpg" alt=""  class="w9_detail_block_bar_button minlup_button"/>
                              <img src="' . Yii::app()->request->baseUrl . '/images/pluslup.jpg" alt="" class="w9_detail_block_bar_button pluslup_button"/>';
                    }
                ?>
            </div>
        </div>
    </div>

    <div id="similar_file_two_conteiner">
        <div id="similar_file_two_block_header">
            <?php

            $type = explode('/', $similar_file_to_upload['uploaded']['mimetype']);
            $type = $type[1];
            echo '<img src="' . Yii::app()->request->baseUrl . '/images/file_types/' . $type . '.png" class="img_type" />' . CHtml::encode($similar_file_to_upload['uploaded']['name']) . ' - ' . 'file uploaded earlier';

            ?>
        </div>
        <div id="similar_file_two_block"
            <?php
            if (strpos($similar_file_to_upload['uploaded']['mimetype'], 'pdf')) {
                echo 'style="overflow: hidden"';
            }
            ?>
            >
            <?php
            if (strpos($similar_file_to_upload['uploaded']['mimetype'], 'pdf')) {
                if (Helper::getMobileDetect()->isMobile() || Helper::getMobileDetect()->isTablet() || Helper::checkMobile()) {
                    echo '<iframe src="http://docs.google.com/viewer?url=' . Helper::generateGoogleDocsUrl($similar_file_to_upload['uploaded']['doc_id']) . '&embedded=true" class="documet_file height100pn width100pn"></iframe> ';
                } else {
                    echo '<embed src="/documents/getusersdocumentfile?doc_id=' . $similar_file_to_upload['uploaded']['doc_id'] . '" id="similar_file_two_file" type="' . $similar_file_to_upload['uploaded']['mimetype'] . '" class="documet_file height100pn width100pn">';
                }
            } else {
                echo '<img src="/documents/getusersdocumentfile?doc_id=' . $similar_file_to_upload['uploaded']['doc_id'] . '" alt="" id="similar_file_two_file" title="" class="documet_file width100pn">';
            }
            ?>
        </div>
        <div class="w9_detail_block_bar">
            <div class="image_buttons right">
                <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/fullsize.jpg" alt=""  class="w9_detail_block_bar_button fullsize_button"/>
                <?php
                if (strpos($similar_file_to_upload['uploaded']['mimetype'], 'pdf') === false) {
                    echo '<img src="' . Yii::app()->request->baseUrl . '/images/minlup.jpg" alt=""  class="w9_detail_block_bar_button minlup_button"/>
                          <img src="' . Yii::app()->request->baseUrl . '/images/pluslup.jpg" alt="" class="w9_detail_block_bar_button pluslup_button"/>';
                }
                ?>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#similar_file_one_conteiner').appendTo('#similar_file_one');
            $('#similar_file_two_conteiner').appendTo('#similar_file_two');
            setTimeout(function() {
                show_modal_box('#comparisonmodal', 910, 20);
                var uplPage = new UploadsPage;
                uplPage.fileToDelete = <?php echo $similar_file_to_upload['uploading']['num'];?>;
                uplPage.initComparisonBlocks();
            }, 200);
        })
    </script>
<?php
} else {
?>

    <script>
        $(document).ready(function() {
            new UploadsPage;

            <?php
                if (Yii::app()->user->projectID === 'all') {
                   ?>
                   setTimeout(function() {
                      show_alert('Please change Project before uploading files!', 430);
                   }, 300);
                   <?php
                }
            ?>
        });
    </script>

<?php
}
?>