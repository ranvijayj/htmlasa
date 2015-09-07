<?php
/* @var $this VendorController */

$this->breadcrumbs=array(
    'Payments List'=>array('/payments'),
    'Detail',
);
?>
<h1>Payments Check Detail: <?=@CHtml::encode(Yii::app()->user->userLogin);?><span class="right items_to_review">Payments to review: <?php echo $num_pages; ?> items</span></h1>



<div class="account_manage">
    <div class="account_header_left left">
        <button class="button" id="send_document_by_email" data="<?php echo $document->Document_ID; ?>">Email</button>


        <?php
        if (strpos($file->Mime_Type, 'pdf') === false) {
            echo '<button class="button" id="print_document" data="' .  $document->Document_ID . '">Print</button>';
        }

        if (Documents::hasDeletePermission($document->Document_ID,Documents::AP,Yii::app()->user->userID,Yii::app()->user->clientID)) {
            echo '<button class="button" id="delete_document" data-href="' . Yii::app()->createUrl('documents/deletedocument', array('doc_for_delete'=>$document->Document_ID) ). '">Delete</button>';
        }
        ?>

        <?if (1==1) {?>
            <button class="button" id="edit_dataentry" data="<?php echo $document->Document_ID; ?>" data-paym-id="<?=$payment->Payment_ID?>">Edit</button>
        <?}?>

    </div>
    <?php $this->renderPartial('application.views.widgets.pages_navigation', array(
        'page' => $page,
        'num_pages' => $num_pages,
        'url' => '/payments/detail',
    )); ?>
</div>

<div class="wrapper">
    <div class="w9_details">
        <span class="right created_on">Created on: <span class="created_on_date"><?php echo Helper::convertDateString($document->Created) . ' ' . CHtml::encode($user->person->First_Name) . ' ' . CHtml::encode($user->person->Last_Name); ?></span></span>
        <h2><?php echo isset($company->Company_Name) ? CHtml::encode($company->Company_Name) : '<span class="not_set">Vendor not attached</span>'; ?></h2>

        <!-- Important the next div data used in JS logic-->
        <div id="save_ap" data="<?php echo $ap['AP_ID']; ?>"></div>
        <!-- End of Important-->

        <table class="details_table">
            <tr>
                <td class="width240">
                    <ul class="details_table_list">
                        <li>Amount: <span class="details_page_value"><?php echo ($payment->Payment_Amount != 0) ? CHtml::encode(number_format($payment->Payment_Amount, 2)) : '<span class="not_set">Not set</span>'; ?></span></li>
                        <li>Date: <span class="details_page_value"><?php echo $payment->Payment_Check_Date ? CHtml::encode(Helper::convertDate($payment->Payment_Check_Date)) : '<span class="not_set">Not set</span>'; ?></span></li>
                        <li>Bank Name: <span class="details_page_value"><?php echo isset($payment->bank_account->Bank_Name) ? CHtml::encode($payment->bank_account->Bank_Name) : '<span class="not_set">Not set</span>'; ?></span></li>
                    </ul>
                </td>
                <td>
                    <ul class="details_table_list">
                        <li>Check Num: <span class="details_page_value"><?php echo $payment->Payment_Check_Number ? CHtml::encode($payment->Payment_Check_Number) : '<span class="not_set">Not set</span>'; ?></span></li>
                        <li>Account Code: <span class="details_page_value"><?php echo '<span class="not_set">Not set</span>'; //echo $payment->Vendor_ID; ?></span></li>
                        <li>Acct. Number: <span class="details_page_value"><?php echo isset($payment->bank_account->Account_Name) ? Chtml::encode($payment->bank_account->Account_Name . ' / ' . $payment->bank_account->Account_Number) : '<span class="not_set">Not set</span>'; ?></span></li>
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
        'activeTab'=>'tab3',
        'cssFile'=>$tab_css,
        'tabs'=>array(
            'tab1'=>array(
                'title'=>'Payable View',
                'view'=>'tabs/payable_view',
                'data'=>array('ap' => $ap),
            ),
            'tab2'=>array(
                'title'=>'AP Backup View',
                'view'=>'tabs/ap_detail_view',
                'data'=>array('ap' => $ap, 'apBackup' => $apBackup),
            ),
            'tab3'=>array(
                'title'=>'Check View',
                'view'=>'tabs/check_view',
                'data'=>array('file' => $file, 'document' => $document),
            ),
        ),
    ));
    ?>

</div>
<div class="sidebar_right">
    <div class="sidebar_item approval_progress">
        <span class="center">Approval Progress</span>
        <br/>
        <?php
            if (count($aps) > 0) {
        ?>
        <div id="progress_bar" data-id="<?=$aps[0]->Document_ID;//$payment->Document_ID; //?>">
            <div id="progress_line" style="width: 0%"  data-width="<?php echo $aps[0]->AP_Approval_Value ?>">

            </div>
        </div>
        <?php
            }
        ?>
    </div>
    <div class="sidebar_item">
        <span class="sidebar_block_header">Details:</span>
        <ul class="sidebar_list">
            <li>Address: <span class="details_page_value"><?php echo isset($address->Address1) ? CHtml::encode($address->Address1) : ''; ?></span></li>
            <li><span class="details_page_value" style="margin-left: 50px;"><?php echo isset($address->Address2) ? CHtml::encode($address->Address2) : ''; ?></span></li>
            <li>City: <span class="details_page_value"><?php echo isset($address->City) ? CHtml::encode($address->City) : ''; ?></span></li>
            <li>State, Zip: <span class="details_page_value"><?php echo isset($address->State) ? (CHtml::encode($address->State) . ', ' . CHtml::encode($address->ZIP)) : ''; ?></span></span></li>
            <li>Country: <span class="details_page_value"><?php echo isset($address->Country) ? CHtml::encode($address->Country) : ''; ?></span></li>
            <li>Account Num: <span class="details_page_value"><?php echo CHtml::encode($bank_account); ?></span></li>
        </ul>
        <span class="sidebar_block_header">Invoices Attached:</span>
        <div class="sidebar_attd_inv">
            <?php
            if (is_array($payment_invoices)) {
                $i = 1;
                $apClass = '';
                foreach ($aps as $apItem) {
                    if (count($aps) > 1) {
                        $apClass = ($i == 1) ? ' payment_invoice current_payment_invoice' : ' payment_invoice';
                        $i++;
                    }
                    echo '<span class="details_page_value' . $apClass . '" data-ap-id="' . $apItem->AP_ID . '">' . CHtml::encode($apItem->Invoice_Number) . ' / ' . CHtml::encode(number_format($apItem->Invoice_Amount, 2)) . '</span><br />';
                }
            }
            ?>
        </div>
    </div>


    <div class="sidebar_item OCR_Layer" id="payment_dists_block">
        <span class="sidebar_block_header">Distribution:</span>
        <?php
        if (isset($ap['payment']->dists) && is_array($ap['payment']->dists)) {
            $this->renderPartial('application.views.ap.dists_list', array('dists' => $ap['payment']->dists));
        }
        ?>
    </div>
</div>

<div id="dataentry_block" style="display: none">

<script>
    $(document).ready(function() {
        setEqualHeight($(".wrapper,.sidebar_right"));
        new PaymentsDetail('<?php echo $ap['payment']->AP_ID; ?>');
    });
</script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/doc_create.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/detail_page.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/payments_detail.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/document_view.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/file_uploading.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/progress_bar.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/asa_datepicker.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/payments_dataentry.js"></script>
<?php
Yii::app()->clientScript->registerScriptFile('/js/mousewheel.js', CClientScript::POS_HEAD);
?>