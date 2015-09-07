<?php $this->beginContent('//layouts/main_wrapper_db_admin'); ?>
    <div class="container_admin" id="page">
        <?php if(isset($this->breadcrumbs)):?>
            <?php $this->widget('zii.widgets.CBreadcrumbs', array(
			'links'=>$this->breadcrumbs,
		)); ?><!-- breadcrumbs -->
        <?php endif?>
        <?php echo $content; ?>
        <div class="clear"></div>
    </div><!-- page -->
<?php $this->endContent(); ?>