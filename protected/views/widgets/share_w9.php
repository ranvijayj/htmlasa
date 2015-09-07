<div class="modal_box" id="share_document_box" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <h2>Share Company W9:</h2>
    <form action="" id="share_document_form">
        <div class="row">
            <label for="share_document_company">
                Company:
            </label>
            <select id="share_document_company" class="txtfield" name="share_document_company">
                <option value="0">Select a company</option>
                <?php
                foreach ($companiesToShareW9 as $clientId => $companyName) {
                    echo '<option value="' . $clientId . '">' . $companyName . '</option>';
                }
                ?>
            </select>
            <div class="errorMessage hidden" id="share_document_company_error">Please choose company to share</div>
        </div>
        <div class="row">
            <label for="w9_access_type">
                Access type:
            </label>


            <?php echo CHtml::dropDownList('w9_access_type',$select,UsersSettings::$w9ShareTypes,
                                            array(
                                                'options' => array(
                                                    $user_settings->Default_W9_Share_Type=>array('selected'=>true),

                                                ),
                                                'class'=>'txtfield',
                                                'disabled'=> $fed_ids_match ? '' :"disabled"

                                            )); ?>
        </div>
        <input type="hidden" id="share_document_id" value="<?php echo $w9->W9_ID; ?>">
        <div class="center">
            <input class="button" type="submit" value="Share">
        </div>
    </form>
</div>