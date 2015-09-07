<?php $this->beginContent('//layouts/main_wrapper'); ?>
    <div class="container_library" id="page">
        <?php if(isset($this->breadcrumbs)):?>
            <?php $this->widget('zii.widgets.CBreadcrumbs', array(
			'links'=>$this->breadcrumbs,
		)); ?><!-- breadcrumbs -->
        <?php endif?>
        <div id="content">
            <?php echo $content; ?>
        </div><!-- content -->
        <div class="clear"></div>
    </div><!-- page -->
<?php $this->endContent(); ?>