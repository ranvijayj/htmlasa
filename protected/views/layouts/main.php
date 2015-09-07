<?php $this->beginContent('//layouts/main_wrapper'); ?>
    <div class="container" id="page">

        <div id="progress_bar_container" style="display: none;">
            <div class="canceltext"> CANCEL </div>
            <div class="progress-label">Starting upload...</div>
            <div id="progressbar_jui"></div>
        </div>

        <div id="video_pass" style="display: none;font-family: 'Arial Narrow',Arial;font-size: 17.7px; position: relative;float: right; border: 1px solid red;padding: 4px;margin: 15px 1px 1px 0px;color: #000000;" >
            Password: Dataentry23
        </div>


        <?  if (isset(Yii::app()->user->userType)) {
                if (Yii::app()->user->userType == Users::DB_ADMIN) {?>

                    <div id="db_fake_breadcrumb" style="display: none;font-family: 'Arial Narrow',Arial;font-size: 17.7px; position: relative;float: right; padding: 4px 4px 4px 3px;margin: 18px 1px 1px 0px;color: #000000;" >
                        <a style="
                            font-weight: normal;
                            font-family: 'Arial Narrow', Arial;
                            text-decoration: none;
                            font-size: 18px;"
                        href="/admin/users">Forward to DB Admin Panel</a> »
                    </div>

                <?}
            }?>


        <?php if(isset($this->breadcrumbs)):?>
            <?php $this->widget('zii.widgets.CBreadcrumbs', array(
			'links'=>$this->breadcrumbs,
		)); ?><!-- breadcrumbs -->
        <?php endif?>
        <?php echo $content; ?>
        <div class="clear"></div>
    </div><!-- page -->
<?php $this->endContent(); ?>