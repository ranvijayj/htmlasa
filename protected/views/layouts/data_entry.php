<?php $this->beginContent('//layouts/main_wrapper'); ?>
    <div class="container container_data_entry" id="page">
        <div id="data_entry_menu" style="padding-left: 50px;">
            |&nbsp;&nbsp;<a href="<?=Yii::app()->createUrl('/dataentry/w9');?>">W9</a> &nbsp;
            |&nbsp;&nbsp;<a href="<?=Yii::app()->createUrl('/dataentry/po');?>">PO</a> &nbsp;
            |&nbsp;&nbsp;<a href="<?=Yii::app()->createUrl('/dataentry/ap');?>">AP</a> &nbsp;
            |&nbsp;&nbsp;<a href="<?=Yii::app()->createUrl('/dataentry/payments');?>">Payment</a> &nbsp;
            |&nbsp;&nbsp;<a href="<?=Yii::app()->createUrl('/dataentry/pc');?>">Expense/PC</a> &nbsp;
            |&nbsp;&nbsp;<a href="<?=Yii::app()->createUrl('/dataentry/payroll');?>">Payroll</a> &nbsp;
            |&nbsp;&nbsp;<a href="<?=Yii::app()->createUrl('/dataentry/je');?>">JE</a>&nbsp;&nbsp;
            |&nbsp;&nbsp;<a href="<?=Yii::app()->createUrl('/dataentry/ar');?>">AR</a>&nbsp;&nbsp;
            |&nbsp;&nbsp;<a href="<?=Yii::app()->createUrl('/dataentry/assign');?>">Assign</a>&nbsp;&nbsp;|
        </div>
        <?php if(isset($this->breadcrumbs)):?>
            <?php $this->widget('zii.widgets.CBreadcrumbs', array(
			'links'=>$this->breadcrumbs,
		)); ?><!-- breadcrumbs -->
        <?php endif?>
        <div id="two-column">
            <div id="content">
                <?php echo $content; ?>
            </div><!-- content -->
        </div>
        <div class="clear"></div>
    </div><!-- page -->
<?php $this->endContent(); ?>