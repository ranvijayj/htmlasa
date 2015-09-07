<?php
/* @var $this VendorController */


$this->breadcrumbs=array(
    'Uploads'=>array('/uploads'),
    'Delete',
);

$path_to_pdfjs=Yii::getPathOfAlias('ext.pdfJs.assets');
$url_to_pdfjs=Yii::app()->getAssetManager()->publish($path_to_pdfjs);

?>
<h1>Delete documents: <?=@CHtml::encode(Yii::app()->user->userLogin);?><span class="right items_to_review">Number Items: <span id="number_items"><?php echo count($current_uploads); ?></span> items</span></h1>

<div class="account_manage">
    <div class="account_header_left left">

        <button class="button" id="delete_document">Delete</button>


    </div>
    <span class="account_manage_b_span">
    <?/*if ($availableStorage != 0) {
            echo "<span class='right'>Used " . number_format($usedStorage, 2) . "GB of " . number_format($availableStorage) . "GB (" . number_format(100*$usedStorage/$availableStorage, 1) . "%)</span>";
        }*/?>
    </span>
        <div class="right">
            <span class="left search_block_label">Search: &nbsp;</span>
            <div class="search_block">
                <input type="text" name="search" value="<?php echo $searchQuery; ?>" id="search_field" maxlength="250">
                <div id="search_options">
                    <span class="search_options_header">Search in the fields:</span><br/>
                    <?php
                    $options = array(

                        'search_option_filename' => array('File Name', 1),
                        'search_option_doctype' => array('Document Type', 0),
                        'search_option_date' => array('Date Created', 0),
                        'search_option_createdby' => array('Created By', 0),
                        'search_option_modified' => array('Modified By', 0),
                    );

                    echo Helper::getSearchOptionsHtml(array(
                        'session_name' => 'last_delete_list_search',
                        'options' => $options,
                    ));

                    ?>
                </div>
            </div>
        </div>

</div>
<?php if(Yii::app()->user->hasFlash('success')):?>
    <div class="info">
        <button class="close-alert">&times;</button>
        <?php echo Yii::app()->user->getFlash('success'); ?>
    </div>
<?php endif; ?>
    <div style="min-height: 200px;">
        <form action="/documents/deletedocuments" id="delete_doc_form" name="delete_doc_form" method="post">

        <table id="files_for_delete" class="uploads_grid border0">
            <thead>
            <tr><th><input type='checkbox' class='check_uncheck_all' name= value='"'/></th>
                <th id="type_cell_header">DocType</th>
                <th id="name_cell_header"> File Name</th>
                <th id="date_cell_header">Date Created</th>
                <th id="user_cell_header">Created by</th>
                <th></th>
                <th id="control_cell_header">Change Detail</th></tr>
            </thead>
            <tbody>

            <?php $this->renderPartial('application.views.documents.tabs._partial_delete_list', array(
                'doclist' => $doclist,
                'markSelctd' => false,
            )); ?>

            </tbody>
        </table>
    </form>
        <div id="align_right">
            <?php
            /*$this->widget('CLinkPager', array(
                'pages' => $pages,
                'maxButtonCount'=>'5',
            ))*/
            ?>
        </div>
    </div>
    <div id="canvas_block" class="modal_box" style="display: none"></div>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/mousewheel.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/document_view.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/delete_docs.js"></script>

    <script src="<?php echo $url_to_pdfjs?>/build/pdf.js"></script>

    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/file_modification.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            var dd=new DeleteDocs;
            $('.show_canvas').on('click',function() {
                var fm=new FileModification('canvas',$(this).data('id'),'false','<?=$url_to_pdfjs;?>');
                fm.showCanvas($(this).data('id'));

            });




        });
    </script>
<?php
$this->renderPartial('//widgets/image_view_block');
?>