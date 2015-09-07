<?php
$this->breadcrumbs=array(
	'Library',
);
//echo Yii::app()->user->userType;die;
?>
<h1 class="redbg">Library: <?=@CHtml::encode(Yii::app()->user->userLogin);?><span class="right items_to_review">Year to view: <?php echo $year; ?></h1>

    <div class="account_manage">
    <div class="account_header_left left">
        <div id="cabinets_actions" <?php echo $activeTab == 'tab2' ? 'style="display: none;"' : '' ; ?>>
            <?php
                if (Yii::app()->user->projectID != 'all') {
                    ?>
                    <button class="button left library_action" data-row-type="storage" data-storage="0" data-action="add">Add Cabinet</button>
                    <?php
                }
            ?>
        </div>
        <div id="shelves_actions"  <?php echo $activeTab == 'tab2' ? 'style="display: block;' : '' ; ?>>
            <?php
            if (Yii::app()->user->projectID != 'all') {
                ?>
                <button class="button left library_action" data-row-type="storage" data-storage="1" data-action="add">Add Shelf</button>
                <?php
            }
            ?>
        </div>
        <button class="button right" id="view_section_docs">View Docs</button>
        <button class="button right" id="view_section_docs_batches" style="display:none">View Docs</button>
    </div>
    <div class="right">
        <span class="left search_block_label">Search: &nbsp;</span>
        <div class="search_block">
            <input type="text" name="search" value="" id="search_field" maxlength="250" autocomplete="off" placeholder="Search library">
            <input type="text" name="search" value="" id="batch_search_field" maxlength="250" style="display: none;" autocomplete="off" placeholder="Search batches">
        </div>
    </div>
</div>
<?php if(Yii::app()->user->hasFlash('success')):?>
    <div class="info">
        <button class="close-alert">&times;</button>
        <?php echo Yii::app()->user->getFlash('success'); ?>
    </div>
<?php endif; ?>
<div class="wrapper">
    <div class='w9_list_view'>
        <?php
        $tab_css = '/css/jquery.yiitab.css';
        if (Helper::checkIE() || Helper::getMobileDetect()->isMobile() || Helper::getMobileDetect()->isTablet() || Helper::checkMobile()) {
            $tab_css = '/css/jquery.yiitabie.css';
        }

        $this->widget('CTabView', array(
            'activeTab'=>$activeTab,
            'cssFile'=>$tab_css,
            'tabs'=>array(
                'tab1'=>array(
                    'title'=>'Cabinets',
                    'view'=>'tabs/cabinets_list',
                    'data'=> array('cabinets' => $cabinets, 'organizePage' => false),
                ),
                'tab2'=>array(
                    'title'=>'Shelves',
                    'view'=>'tabs/shelves_list',
                    'data'=> array('shelves' => $shelves, 'organizePage' => false),
                ),
                'tab3'=>array(
                    'title'=>'Batch',
                    'view'=>'tabs/batches_list',
                    'data'=> array('batchesList'=>$batchesList, 'organizePage' => false),
                ),
            ),
        ));
        ?>
    </div>
</div>
<div class="sidebar_right" id="sidebar">
    <div class="sidebar_item details_sidebar_block" id="storage_info">
        <span class="sidebar_block_header">Details:</span>
    </div>
    <div class="sidebar_item" id="library_years_list">
        <span class="library_years_list_header">Archival Repository:</span>
        <div id="library_years_list_block">
            <ul>
            <?php
            foreach ($yearsList as $yearItem) {
                $class = 'library_year_link';
                if ($year == $yearItem->Year) {
                    $class  = "library_year_item";
                }
                echo "<li><a href='javascript:void(0)' class='" . $class . "' data-year='" . $yearItem->Year . "'>Archive of "
                    . $yearItem->Year
                    .  " year</a></li>";
            }
            ?>
            </ul>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        setEqualHeight($(".wrapper,.sidebar_right"));
        new LibraryTree('<?php echo $activeTab; ?>','<?php echo Yii::app()->user->projectID; ?>','<?php echo Yii::app()->user->userType; ?>');

        <?php
            if ($showLibraryForm) {
                echo "setTimeout(function() {
                         show_modal_box('#library_form_modal');
                      }, 200);";
            }
        ?>
    });
</script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/library_tree.js"></script>
<?php
    if ($showLibraryForm) {
        $this->renderPartial('application.views.widgets.library_form', array('content' => $formContent));
    } else {
        $this->renderPartial('application.views.widgets.library_form');
    }
?>