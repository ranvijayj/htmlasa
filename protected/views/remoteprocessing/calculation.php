
<?php
/* @var $this RemoteProcessingController */
?>
<div style="padding-top: 20px;">
    Client title : <span class="title_fee">  1 </span> page </br>
    Users : <span class="users_fee">  1 </span> page </br>

    Vendors: <span id="vendors_quality"><?=$Vendors; ?> </span> / <span id="vendors_per_page"> 50 vendors per page </span> / <span class="pages_per_sheet"> <?= $PagesPerSheet;?> </span> =
    <? $s1=intval($Vendors /50/$PagesPerSheet)>0 ? intval($Vendors /50/$PagesPerSheet) : 1; echo $s1; ?>   </br> </br>

    COAs: <span id="coas_quality"> <?=$Coas?> </span> / <span id="coas_per_page"> 50 COAs per page </span> / <span class="pages_per_sheet"> <?= $PagesPerSheet;?> </span> =
    <?$s2=intval($Coas /50/$PagesPerSheet) > 0 ? intval($Coas /50/$PagesPerSheet) : 1;  echo $s2; ?>  </br>

    Notes: <span id="notes_chars_count"><?=$CountNotes?> </span> / <span id="chars_per_page"> 1000 </span> / <span class="pages_per_sheet"> <?= $PagesPerSheet;?> </span> =
    <?
    if ($CountNotes>0) {
        $s3=intval($CountNotes/1000/$PagesPerSheet)>0 ? intval($CountNotes/1000/$PagesPerSheet) : 1;
    } else {$s3=0;}
      echo $s3 ; ?>    </br>

    Documents: <span id="total_documents"><?=$GeneralFilesCount ?> </span> pages for metadata + <span id="total_pages"> <?=$TotalPages?> </span> pages for images / <span class="pages_per_sheet"> <?= $PagesPerSheet;?> pages per sheet </span>  =
    <? $s4=intval( ($GeneralFilesCount+ $TotalPages) /$PagesPerSheet); echo $s4; ?>
    </br>

    Index: <span class="index_pages">  2 </span> pages </br>

    <b>Total ( 1 + 1 + <?=$s1?> + <?=$s2?> + <?=$s3?> + <?=$s4?> + 2) = <?$total_pages = (1+1+2+$s1+$s2+$s3+$s4); echo $total_pages; ?> pages</b>
    <br/><br/>


    <b>Cost digital</b> : <?=$SetupFee?> setup fee + (   <?=$total_pages?> total pages * <?=$PpFeeDigital ?> fee per page) = $<?$total_cost =$SetupFee+($total_pages*$PpFeeDigital); echo $total_cost;?>
    <br/>
    <b>Cost paper</b> : <?=$SetupFee?> setup fee + (   <?=$total_pages?> total pages * <?=$PpFeePaper ?> fee per page) = $<?$total_cost =$SetupFee+($total_pages*$PpFeePaper); echo $total_cost;?>
    <br/>


</div>
<br /><br />
<a href="/remoteprocessing/export/<?=$cli_id?>" class="button" id="start_processing" style="padding: 1px 12px;">  Start processing  </a>

