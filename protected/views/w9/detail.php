<?php
/* @var $this VendorController */

$this->breadcrumbs=array(
    'W9 List'=>array('/w9'),
    'Detail',
);
?>
<h1 class="w9_header">W9 Detail: <?=@CHtml::encode(Yii::app()->user->userLogin);?><span class="right items_to_review">W9s to review: <?php echo $num_pages; ?> items</span></h1>

<div class="account_manage">
    <div class="account_header_left left">
        <button class="button" id="send_document_by_fax" data="<?php echo $client->Client_ID; ?>">Fax</button>
        <button class="button" id="send_document_by_email" data="<?php echo $client->Client_ID; ?>">Email</button>
        <?php
        if (strpos($file->Mime_Type, 'pdf') === false) {
            echo '<button class="button" id="print_document" data="' .  $company->Company_Fed_ID . '">Print</button>';
        }
        if ($w9->Access_Type == 1) {
            echo '<button class="button" id="share_document" data-id="' . $w9->W9_ID . '">Share</button>';
        }

        if (Documents::hasDeletePermission($w9->Document_ID,Documents::W9,Yii::app()->user->userID,Yii::app()->user->clientID)) {
            echo '<button class="button" id="delete_document" data-href="' . Yii::app()->createUrl('documents/deletedocument', array('doc_for_delete'=>$w9->Document_ID) ). '">Delete</button>';
        }
        ?>
    </div>
    <?php $this->renderPartial('application.views.widgets.pages_navigation', array(
        'page' => $page,
        'num_pages' => $num_pages,
        'url' => '/w9/detail',
    )); ?>
</div>
<div class="wrapper">
    <div class="w9_details">
        <span class="right created_on">Created on: <span class="created_on_date"><?php echo Helper::convertDateString($lastDocument->Created); ?></span></span>
        <h2><?php echo CHtml::encode($company->Company_Name); ?></h2>
        <table class="details_table">
            <tr>
                <td class="width240">
                    <ul class="details_table_list">
                        <li>Fed ID: <span class="details_page_value"><?php echo $company->Company_Fed_ID; ?></span></li>
                        <li>Addre. 1: <span class="details_page_value"><?php echo $adress->Address1 ? CHtml::encode($adress->Address1) : '<span class="not_set">Not set</span>'; ?></span></li>
                        <li>Addre. 2: <span class="details_page_value"><?php echo $adress->Address2 ? CHtml::encode($adress->Address2) : '<span class="not_set">Not set</span>'; ?></span></li>
                    </ul>
                </td>
                <td>
                    <ul class="details_table_list">
                        <li>City/St/Zip: <span class="details_page_value"><?php echo Helper::createAddressLine('', $adress->City, $adress->State, $adress->ZIP); ?></span></li>
                        <li>Country: <span class="details_page_value"><?php echo $adress->Country ? CHtml::encode($adress->Country) : '<span class="not_set">Not set</span>'; ?></span></li>
                        <li>Contact - Phone: <span class="details_page_value"><?php echo (isset($adminPerson->First_Name) ? (CHtml::encode($adminPerson->First_Name . ' ' . $adminPerson->Last_Name)) : ''); if (isset($adminPerson->Direct_Phone) && $adminPerson->Direct_Phone) echo ', ' . CHtml::encode($adminPerson->Direct_Phone);?></span></li>
                    </ul>
                </td>
            </tr>
        </table>
    </div>


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
                'title'=>'Image View',
                'view'=>'tabs/image',
                'data'=>array('file' => $file, 'lastDocument' => $lastDocument),
            ),
            'tab2'=>array(
                'title'=>'Image View 2',
                'view'=>'tabs/tab_2',
                'data'=>array('file' => $file2, 'lastDocument' => $lastDocument2),
            ),
            'tab3'=>array(
                'title'=>'Image View 3',
                'view'=>'tabs/tab_3',
                'data'=>array('file' => $file3, 'lastDocument' => $lastDocument3),
            ),
        ),
    ));
    ?>

</div>
<div class="sidebar_right">
    <div class="sidebar_item sidebar_verified">
        <span class="center sidebar_block_header">Document Verified</span>
        <br/>
        <?php if ($w9->Verified == 1) {
            echo '<p class="center"><img src="' . Yii::app()->request->baseUrl . '/images/verified_doc.jpg" alt=""/></p>';
        } else {
            echo '<p class="center"><img src="' . Yii::app()->request->baseUrl . '/images/notverified_doc.jpg" alt=""/></p>';
        }
        ?>
    </div>
    <div class="sidebar_item" id="notes_blok">
        <span>Add an Internal Note:</span>
        <textarea name="" class="note_textarea" maxlength="250"></textarea>
        <input type="hidden" name="note_to_company" id="note_to_company" value="<?php echo $company->Company_ID; ?>"/>
        <button class="button block-center" id="add_note_button">Add Note</button>
        <div id="notes_list">
            <?php
                foreach ($notes as $note) {
                    echo '<div class="note_item">
                            <p class="note_date">' . Helper::convertDateString($note->Created) . '</p>
                            <p class="note_title">' . CHtml::encode($note->user->person->First_Name) . ' ' . CHtml::encode($note->user->person->Last_Name) . '</p>
                            <p class="note_body">' . CHtml::encode($note->Comment) . '</p>
                          </div>';
                }
            ?>
        </div>
    </div>
    <div class="sidebar_item OCR_Layer ">
        <span class="sidebar_block_header">Details</span>
    </div>
</div>
<script>
    $(document).ready(function() {
        setEqualHeight($(".wrapper,.sidebar_right"));
        new W9Detail;
    });
</script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/detail_page.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/w9_detail.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/document_view.js"></script>
<?php
Yii::app()->clientScript->registerScriptFile('/js/mousewheel.js', CClientScript::POS_HEAD);
?>
<?php $this->renderPartial('application.views.widgets.share_w9', array(
    'companiesToShareW9' => $companiesToShareW9,
    'w9' => $w9,
)); ?>