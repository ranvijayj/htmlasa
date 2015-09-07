<?php
$this->breadcrumbs=array(
	'Library' => '/library',
    $storage->Storage_Name,
    Sections::$folderCategoriesNames[$section->Folder_Cat_ID],
    $section->Section_Name,
);

?>
<h1 class="redbg">Document Detail: <?=@CHtml::encode(Yii::app()->user->userLogin);?><span class="right items_to_review">Year to view: <?php echo $year; ?></h1>
<div class="account_manage">
    <div class="account_header_left left" style="width: 700px;">
        <button class="button left" id="duplicate">Copy/Move</button>
        <button class="button left" id="send_email" data-id="0">Email</button>
        <button class="button left" id="print_doc" data-active="yes" data-id="0">Print</button>
        <?if ($section->folder_type->Folder_Cat_ID == Sections::JOURNAL_ENTRY || $section->folder_type->Folder_Cat_ID == Sections::PAYROLL || $section->folder_type->Folder_Cat_ID == Sections::ACCOUNTS_RECEIVABLE) {?>
            <button class="button" id="edit_dataentry" data-class="<?=$section->folder_type->Folder_Cat_ID;?>" >Edit</button>
        <?}?>

        <?php if ($section->folder_type->Folder_Cat_ID == Sections::PURCHASE_ORDER_LOG) {?>
            <div class="right">
                <span class="left search_block_label">Sort By: &nbsp;</span>
                <div class="search_block">
                    <select id="po_sorting">
                        <option value="0">PO Number</option>
                        <option <?php echo isset($_SESSION['sort_po_by_vendor_name']) ? 'selected="selected"' : ''; ?> value="1">Vendor Name</option>
                    </select>
                </div>
            </div>
        <? } ?>
        <div class="right" id="dropdownaccess_block">
            <span class="left search_block_label">Access to Doc.: &nbsp;</span>
            <div class="search_block" id="dropdownaccess"></div>
        </div>
    </div>
    <div class="right items_switch" id="items_switch">
        <button class="button left" id="activate_previous_doc">Prev</button>
        <span class="items_switch_counter" id="items_switch_counter"> <input type="text" id="current_item_switch_counter" value="1" style="width:25px;"> of
        <?php
        foreach ($subsections as $subsection) {
            if ($activeTab == $subsection['subsection']->Subsection_ID || $activeTab == 0) {
                echo count($subsection['documents']);
                break;
            }
        }
        ?>
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

        $tabs = array();
        $aTab = 'tab1';
        $i = 1;
        foreach ($subsections as $subsection) {
            $tabs['tab' . $i] = array(
                'title'=>Helper::cutText('15', '150', '13', $subsection['subsection']->Subsection_Name),
                'view'=>'tabs/lib_view',
                'data' => array('documents' => $subsection['documents'], 'tabNum' => $i, 'subsectionID' => $subsection['subsection']->Subsection_ID),
            );

            if ($activeTab == $subsection['subsection']->Subsection_ID) {
                $aTab = 'tab' . $i;
            }

            $i++;
        }

        $this->widget('CTabView', array(
            'activeTab'=>$aTab,
            'cssFile'=>$tab_css,
            'tabs'=>$tabs,
        ));
        ?>
    </div>

    <div id="dataentry_block" style="display: none"></div>
</div>
<script>
    $(document).ready(function() {
        new LibraryView('<?php echo '#'.$aTab;?>', '<?php echo (Helper::getMobileDetect()->isMobile() || Helper::getMobileDetect()->isTablet() || Helper::checkMobile()) ? 'mobile' : 'desctop' ;?>');

        var dpSettings = {
            dateFormat: "mm/dd/yy"
        }

        $('#dataentry_block').on ('focus','#Journals_JE_Date',function() {
            $(this).datepicker({
                dateFormat: "mm/dd/yy",
                onClose: function(selectedDate){
                    //check if the year part of the date correct
                    var arr = selectedDate.split('/');
                    if(isNaN(arr[2])){
                        //if not correct changing it 2xxx format
                        arr[2]=arr[2].replace(/_/g, '');
                        var res =parseInt(arr[2],10);
                        if( res<2000 ) { res=res+2000; arr[2]=res;}
                        var new_date=arr[0]+'/'+arr[1]+'/'+arr[2];
                        $(this).datepicker( "setDate", new_date );
                    }
                }
            });
            $(this).mask("99/99/9999");
        });

        $('#dataentry_block').on ('focus','#Ars_Invoice_Date',function() {
            $(this).datepicker({
                dateFormat: "mm/dd/yy",
                onClose: function(selectedDate){
                    //check if the year part of the date correct
                    var arr = selectedDate.split('/');
                    if(isNaN(arr[2])){
                        //if not correct changing it 2xxx format
                        arr[2]=arr[2].replace(/_/g, '');
                        var res =parseInt(arr[2],10);
                        if( res<2000 ) { res=res+2000; arr[2]=res;}
                        var new_date=arr[0]+'/'+arr[1]+'/'+arr[2];
                        $(this).datepicker( "setDate", new_date );
                    }
                }
            });
            $(this).mask("99/99/9999");
        });

        $('#dataentry_block').on ('focus','#Payrolls_Week_Ending',function() {
            $(this).datepicker({
                dateFormat: "mm/dd/yy",
                onClose: function(selectedDate){
                    //check if the year part of the date correct
                    var arr = selectedDate.split('/');
                    if(isNaN(arr[2])){
                        //if not correct changing it 2xxx format
                        arr[2]=arr[2].replace(/_/g, '');
                        var res =parseInt(arr[2],10);
                        if( res<2000 ) { res=res+2000; arr[2]=res;}
                        var new_date=arr[0]+'/'+arr[1]+'/'+arr[2];
                        $(this).datepicker( "setDate", new_date );
                    }
                }
            });
            $(this).mask("99/99/9999");
        });



    });
</script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/mousewheel.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/asa_datepicker.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/document_view.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/library_view.js"></script>
<?php
    $this->renderPartial('application.views.widgets.library_form');
?>