<?php
if ($show) {
?>

    <span class="sidebar_block_header"><?php echo $companyInfo['add_text']; ?></span>
    <ul class="sidebar_list">
        <li><h2 class="sidebar_block_list_header"><?php  echo  CHtml::encode(''); ?></h2></li>

    </ul>
    <span class="sidebar_block_header">Notes:</span>

    <p>1) <?php  echo  CHtml::encode($companyInfo['come_from']); ?></p>


<?}?>