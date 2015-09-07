<?php if(Yii::app()->user->hasFlash('success')):?>
    <div class="info home_page_info">
        <button class="close-alert">&times;</button>
        <?php echo Yii::app()->user->getFlash('success'); ?>
    </div>
<?php endif; ?>
<?php $this->renderPartial('application.views.site.homepagelinks'); ?>
<?php
if (isset($showregistermodal)) {
    ?>
    <script type="text/javascript">
        $(document).ready(function() {
            setTimeout(function() {
                show_modal_box('#registerasclientadminmodal', 260, 50);
            }, 50)
        });
    </script>
<?php
}
?>
