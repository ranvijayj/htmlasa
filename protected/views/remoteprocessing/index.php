<?php
/* @var $this RemoteProcessingController */

$this->breadcrumbs=array(
	'Remote Processing',
);
?>
<h1>Remote Processing </h1>

<!--<div class="account_manage">
    <div class="account_header_left left">

    </div>

</div>-->



<div class="wrapper" style="min-height: 700px;">

    <div id= "show_pay_dialog" data-prid="<?=$show_pay_dialog?>" ></div>
    <?php if(Yii::app()->user->hasFlash('success')):?>
        <div class="info">
            <button class="close-alert">&times;</button>
            <?php echo Yii::app()->user->getFlash('success'); ?>
        </div>
    <?php endif; ?>

<?
$this->renderPartial('client_select_view', array(
    'clientlist' => $clientsList,
));

?>
</div>

<div class="sidebar_right">
    <div class="row">

        <span class="sidebar_block_header"> Select client for procesing</span>

        <input class="client_list" size="30">
    </div>


    <div id="projects_info" style="display: none;">

    </div>

    <br/><br/><br/>
    <span class="sidebar_block_header">Current client's previous books</span>
    <div id="existing_exports" style="margin-top: 3px">
        <? foreach ($rp_list as $rp_item) {?>
            <?
            $prices = RemoteProcessing::CalculateBookPaySums($rp_item->PR_ID);

            $item_price = $prices['pdf_prise'];
            $default_paper_cost = $prices['paper_default_price'];

            ?>
          <? if (!$rp_item->Payment) {?>
                <span class="details_page_value"><a href="#" class="rp_item" id="<?=$rp_item->PR_ID?>" data-prise='<?=$item_price?>'  style="margin-top-top: 3px;">Pay for book from <?=$rp_item->Created?></a></span><br/>
          <? } else  {?>
               <span class="details_page_value"><a href="/remoteprocessing/getbookfile?rp_id=<?=$rp_item->PR_ID?>" class="rp_download"  data-prise='<?=100?>'  style="margin-top-top: 3px;">Download book from <?=$rp_item->Created?></a></span><br/>
              <? if (!$rp_item->AnalogPayment) {?>
                     <span class="details_page_value"><a href="" class="request_paper_book" id="<?=$rp_item->PR_ID?>" data-prise='<?=$item_price?>' data-analogprise='<?=$default_paper_cost?>'  style="color: #808080;">Request paper book</a> </span><br/>
               <?}?>
          <? } ?>
           <br/>
        <?}?>
    </div>


    <div class="modal_box" id="ask_using_last_card" style="display:none;">
        <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
        <h2>Do you want to use last Credit Card to pay <span id="sum_to_pay"></span> ?</h2>
        <div id="last_cc_info">

        </div>

        <div class="center">
            <button class="button hidemodal" id="use_last_card" style="display: inline-block;">Use This Card</button>
            <button class="button hidemodal" id="use_new_card" style="margin-left: 30px;">Use Other Card</button>
        </div>
    </div>

    <?$this->renderPartial('application.views.widgets.stripe_payment_widget');?>
    <?$this->renderPartial('application.views.widgets.book_calculation_widget');?>

</div>
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/remote_processing.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/remote_processing_payment.js"></script>
<script>
    $(document).ready(function() {
        var rp = new RemoteProcessing;
        var rpp = new RemoteProcessingPayment('<?php echo Yii::app()->config->get('STRIPE_PUBLISH_KEY'); ?>');

    });
</script>


