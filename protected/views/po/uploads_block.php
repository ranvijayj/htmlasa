    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <div class="left uploads_block_left">
        <p>PO Detail: Upload <?php echo (!$detailsPage) ? '& Entry:' : 'BU'; ?></p>
        <div id="additional_fields_block_conteiner">
            <div id="additional_fields_block_file_conteiner">
                <div id="additional_fields_block_file"
                    <?php
                    if (strpos($file['mimetype'], 'pdf')) {
                        echo 'style="overflow: hidden"';
                    }
                    ?>
                    >
                    <?php
                    if (strpos($file['mimetype'], 'pdf')) {
                        if (Helper::getMobileDetect()->isMobile() || Helper::getMobileDetect()->isTablet() || Helper::checkMobile()) {
                            echo '<iframe src="http://docs.google.com/viewer?url=' . Helper::generatePOBUGoogleDocsUrl() . '&embedded=true" class="documet_file height100pn width100pn"></iframe> ';
                        } else {
                            echo '<embed src="/po/getdocumentfile" id="document_file" type="' . $file['mimetype'] . '" class="documet_file height100pn width100pn">';
                        }
                    } else {
                        echo '<img src="/po/getdocumentfile" alt="" id="document_file" title="" class="documet_file width100pn">';
                    }
                    ?>
                </div>
                <div class="w9_detail_block_bar">
                    <div class="image_buttons right">
                        <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/fullsize.jpg" alt=""  class="w9_detail_block_bar_button fullsize_button"/>
                        <?php
                        if (strpos($file['mimetype'], 'pdf') === false) {
                            echo '<img src="' . Yii::app()->request->baseUrl . '/images/minlup.jpg" alt=""  class="w9_detail_block_bar_button minlup_button"/>
                                  <img src="' . Yii::app()->request->baseUrl . '/images/pluslup.jpg" alt="" class="w9_detail_block_bar_button pluslup_button"/>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="right uploads_block_right">
        <button class="button" id="upload_file">Upload</button> <button class="button hidemodal">Cancel</button>
        <br/><br/>
        <label>
            Doc Type:
        </label>
        <table>
            <tr><td class="dropdown_cell_upload"><?php echo (!$detailsPage) ? '<div class="dropdown_cell_ul"><ul><li>W9</li><li>BU</li></ul><span class="dropdown_cell_value">BU</span></div>' : 'BU'; ?></td></tr>
        </table>
        <?php
            if (!$detailsPage) {
                ?>
                <div class="clear"></div>
                <label>
                    File Name:
                </label><br/>
                <?php
                    $type = explode('/', $file['mimetype']);
                    $type = $type[1];
                    echo '<img src="' . Yii::app()->request->baseUrl . '/images/file_types/' . $type . '.png" class="img_type" />' . (strlen($file['name'])>25 ?substr_replace($file['name'],'...', 22):$file['name']);
                ?>
                <p id="uploaded_file_name"></p>
                <label>
                    Additional Fields:
                </label>
                <span id="additional_fields_pointer">NOT REQUIRED</span>
                <div class="additional_fields_form" style="display: none;">
                    <label>
                        Federal ID Number (EIN):
                    </label><br/>
                    <input type="text" name="add_fed_id" id="add_fed_id" value=""/><br/>
                    <span id="fed_id_status"></span><br/>
                    <label>
                        Company Name:
                    </label><br/>
                    <input type="text" name="add_com_name" id="add_com_name" disabled="disabled" value=""/>
                    <span id="com_name_status"></span><br/>
                </div>
                <?php
            }
        ?>
    </div>
    <div class="clear"></div>
