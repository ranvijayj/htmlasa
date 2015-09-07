<div class="modal_box" id="loginmodal" style="display:none; width: 300px !important; ">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <h2>Are you sure you want to exit?</h2>
    <div class="center">
        <a href="<?=$this->createUrl('/site/logout/');?>" class="button hidemodal">Yes</a>
        <button class="button hidemodal" style="margin-left: 30px;">No</button>
    </div>
</div>