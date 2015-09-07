<?php
$this->breadcrumbs=array(
    'Library' => '/library',
    'Batches Details',
);

?>
    <h1 class="redbg">Document Detail: <?=@CHtml::encode(Yii::app()->user->userLogin);?></h1>
    <div class="account_manage">
        <div class="account_header_left left" style="width: 700px;">



        </div>

        <div class="right items_switch" id="items_switch">
            <button class="button left" id="activate_previous_doc">Prev</button>
        <span class="items_switch_counter" id="items_switch_counter">
            <input type="text" id="current_item_switch_counter" value="1" style="width:25px;">
            of
            <?= count($batches);?>
        </span>
            <button class="button right" id="activate_next_doc">Next</button>
        </div>
    </div>
<?php if(Yii::app()->user->hasFlash('success')):?>
    <div class="info">
        <button class="close-alert">&times;</button>
        <?php echo Yii::app()->user->getFlash('success'); ?>
    </div>
<?php endif; ?>
    <div class="wrapper_library">
        <div class='w9_list_view'>
            <?php
            $tab_css = '/css/jquery.yiitab.css';
            if (Helper::checkIE() || Helper::getMobileDetect()->isMobile() || Helper::getMobileDetect()->isTablet() || Helper::checkMobile()) {
                $tab_css = '/css/jquery.yiitabie.css';
            }

            $this->widget('CTabView', array(
                'activeTab'=>'tab1',
                'cssFile'=>$tab_css,
                'tabs'=>array(
                    'tab1'=>array(
                        'title'=>'Batch Detail',
                        'view'=>'tabs/lib_view',
                        'data'=> array('batches' => $batches),
                    ),
                ),
            ));


            ?>



        </div>
    </div>
    <script>
        $(document).ready(function() {
            new BatchesView('<?php echo (Helper::getMobileDetect()->isMobile() || Helper::getMobileDetect()->isTablet() || Helper::checkMobile()) ? 'mobile' : 'desctop' ;?>');
        });
    </script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/mousewheel.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/document_view.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/library_view.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/batches_view.js"></script>
