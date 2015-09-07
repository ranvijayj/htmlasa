<?php
$this->breadcrumbs=array(
    'Library' => '/library',
    'Organize'
);

?>
<h1 class="redbg">Organization Detail: <?=@CHtml::encode(Yii::app()->user->userLogin);?><span class="right items_to_review">Items to Move: <span id="number_items">0</span> items</span></h1>
<div class="account_manage">
    <div class="account_header_left left" style="position: relative;">
        <button class="button left" id="move_docs">Move</button>
        <button class="button left" id="send_email" data-id="0" style="display: none;">Email</button>
        <button class="button left" id="print_doc"  data-id="0" style="display: none;">Print</button>

        <div id="menu_wrapper" style="position: absolute;left: 325px;top: 1px;z-index: 50;" >

            <select name="files" id="files">
                <optgroup>
                    <option disabled="disabled" selected>Select action</option>
                </optgroup>
                <optgroup label="General">
                    <option value="move_docs">Move</option>
                    <option disabled="disabled" data-action="send_email" disabled>Email</option>
                    <option disabled="disabled" data-action="print_doc" disabled>Print</option>
                </optgroup>
                <optgroup label="Cabinets">
                    <option disabled="disabled" data-action="add">Add Cabinet</option>
                    <option disabled="disabled" data-action="edit">Edit Cabinet</option>
                    <option disabled="disabled" data-action="del">Delete Cabinet</option>

                </optgroup>

                <optgroup label="Folder">
                    <option disabled="disabled" data-action="add_sub">Add Folder</option>
                    <option disabled="disabled" data-action="edit">Edit Folder</option>
                    <option disabled="disabled" data-action="del">Delete Folder</option>

                    <option disabled="disabled" data-action="add_sub">Add Panel</option>
                    <option disabled="disabled" data-action="edit">Edit Panel</option>
                    <option disabled="disabled" data-action="del">Delete Panel</option>
                </optgroup>

                <optgroup label="Shelves">
                    <option value="add" disabled="disabled" data-action="add">Add Shelf</option>
                    <option value="edit" disabled="disabled" data-action="edit">Edit Shelf</option>
                    <option value="del" disabled="disabled" data-action="del">Delete Shelf</option>

                    <option value="add_sub" disabled="disabled" data-action="add_sub">Add Binder</option>
                    <option value="add_sub" disabled="disabled" data-action="edit">Edit Binder</option>
                    <option value="del" disabled="disabled" data-action="del">Delete Binder</option>

                    <option value="add" disabled="disabled" data-action="add">Add tab</option>
                    <option value="edit" disabled="disabled" data-action="edit">Edit tab</option>
                    <option value="del" disabled="disabled" data-action="del">Delete tab</option>
                </optgroup>

            </select>
        </div>


        <div class="right">
            <span class="left search_block_label">Change Year: &nbsp;</span>
            <div class="search_block">
                <select id="library_years">
                    <?php
                    foreach ($yearsList as $yearItem) {
                        echo '<option value="' . $yearItem->Year . '" ' . (($yearItem->Year == $year) ? 'selected="selected"' : '') . '>' . $yearItem->Year .'</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>
    <div class="right">
        <span class="left search_block_label">Search: &nbsp;</span>
        <div class="search_block">
            <input type="text" name="search" value="" id="search_field" maxlength="250">
        </div>
    </div>
</div>
<?php if(Yii::app()->user->hasFlash('success')):?>
    <div class="info">
        <button class="close-alert">&times;</button>
        <?php echo Yii::app()->user->getFlash('success'); ?>
    </div>
<?php endif; ?>
    <div class="left_column" id="organize_left_column">
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
                        'title'=>'Cabinets',
                        'view'=>'tabs/cabinets_list',
                        'data'=> array('cabinets' => $cabinets, 'organizePage' => true),
                    ),
                    'tab2'=>array(
                        'title'=>'Shelves',
                        'view'=>'tabs/shelves_list',
                        'data'=> array('shelves' => $shelves, 'organizePage' => true),
                    ),
                ),
            ));
            ?>
        </div>
    </div>
    <div class="right_column" id="organize_right_column">
        <h2>Unassigned Documents:</h2>
        <table class="list_table_head">
            <thead>
            <tr class="table_head">
                <th class="width2">

                </th>
                <th class="width180">
                    Document Name
                </th>
                <th width="70">
                    Doc. Type
                </th>
                <th>
                    Access
                </th>
            </tr>
            </thead>
        </table>
        <div class="table_list_scroll_block">
            <form action="/library/organize" id="unassigned_documents" method="post">
                <table id="unassigned_documents_table" class='list_table'>
                    <tbody>
                    <?php
                    if (count($documents) > 0) {
                        foreach ($documents as $document) {
                            ?>
                            <tr id="doc<?php echo $document->Document_ID; ?>">
                                <td class="width12" style="padding-left: 0px; padding-right: 0px;">
                                    <input type="checkbox" class='list_checkbox' name="documents[<?php echo $document->Document_ID; ?>]" value="<?php echo $document->Document_ID; ?>" data-year="<?=date('Y',strtotime($document->Created)); ?>"/>
                                </td>
                                <td class="width30">
                                    <img src="/documents/getdocumentthumbnail?doc_id=<?php echo $document->Document_ID;?>" alt="<?php echo CHtml::encode($document->image->File_Name);?>" title="<?php echo CHtml::encode($document->image->File_Name);?>" width="30" height="39" />
                                </td>
                                <td class="width150">
                                    <?php echo Helper::cutText(15, 170, 13, $document->image->File_Name); ?>
                                </td>
                                <td width="50">
                                    <?php echo CHtml::encode($document->Document_Type); ?>
                                </td>
                                <td>
                                    <?php
                                        if ($document->Document_Type == Documents::LB) {
                                            ?>
                                            <select class="unassigned_documents_select" name="access[<?php echo $document->Document_ID; ?>]">
                                                <option value="0">Only for me</option>
                                                <option value="1" selected="selected">For all users in Project</option>
                                            </select>
                                            <?php
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php
                        }
                    } else if (count($another_documents) > 0 && count($documents) == 0) {
                        //for documents from another period
                        foreach ($another_documents as $document) {
                            ?>
                            <tr class="another_period" id="doc<?php echo $document->Document_ID; ?>">
                                <td class="width12" style="padding-left: 0px; padding-right: 0px;">
                                    <input type="checkbox" class='list_checkbox' checked name="documents[<?php echo $document->Document_ID; ?>]" value="<?php echo $document->Document_ID; ?>" data-year="<?=date('Y',strtotime($document->Created)); ?>"/>
                                </td>
                                <td class="width30">
                                    <img src="/documents/getdocumentthumbnail?doc_id=<?php echo $document->Document_ID;?>" alt="<?php echo CHtml::encode($document->image->File_Name);?>" title="<?php echo CHtml::encode($document->image->File_Name);?>" width="30" height="39" />
                                </td>
                                <td class="width150">
                                    <?php echo Helper::cutText(15, 170, 13, $document->image->File_Name); ?>
                                </td>
                                <td width="50">
                                    <?php echo CHtml::encode($document->Document_Type); ?>
                                </td>
                                <td>
                                    <?php
                                    if ($document->Document_Type == Documents::LB) {
                                        ?>
                                        <select class="unassigned_documents_select" name="access[<?php echo $document->Document_ID; ?>]">
                                            <option value="0">Only for me</option>
                                            <option value="1" selected="selected">For all users in Project</option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php
                        }

                    } else {
                        echo '<tr>
             <td>
                 Documents were not found.
             </td>
           </tr>';
                    }
                    ?>
                    </tbody>
                </table>
                <input type="hidden" name="subsection_id" value="" id="subsection_id_to_move"/>
                <input type="hidden" name="section_id" value="" id="section_id_to_move"/>
                <input type="hidden" name="subsection_type" value="" id="subsection_type_to_move"/>
                <input type="hidden" name="documents_to_assign" value=""/>
            </form>
        </div>
    </div>



    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/library_tree.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/library_organize.js"></script>

<script>
    $(document).ready(function() {
        setEqualHeight($(".wrapper,.sidebar_right"));

        new LibraryOrganize('<?php echo $activeTab; ?>','<?php echo Yii::app()->user->projectID; ?>','<?php echo Yii::app()->user->userType; ?>');
        //new LibraryTree('<?php echo $activeTab; ?>','<?php echo Yii::app()->user->projectID; ?>','<?php echo Yii::app()->user->userType; ?>');
    });
</script>
<?php
    $this->renderPartial('application.views.widgets.library_form');
?>