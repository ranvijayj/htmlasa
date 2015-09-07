<?php
if ($show) {
?>

<span class="sidebar_block_header"><?php echo $companyInfo['add_text']; ?></span>
<ul class="sidebar_list">
    <li><h2 class="sidebar_block_list_header"><?php  echo  CHtml::encode($companyInfo['name']); ?></h2></li>
    <li>Fed ID: <?php  echo  $companyInfo['fed_id']; ?></li>
    <li>Address: <?php  echo  CHtml::encode($companyInfo['adr']); ?></li>
    <li>City: <?php  echo  CHtml::encode($companyInfo['city']); ?></li>
    <li>State: <?php  echo  CHtml::encode($companyInfo['state']); ?></li>
    <li>Zip: <?php  echo  CHtml::encode($companyInfo['zip']); ?></li>
</ul>
<?php if ($companyInfo['client_admins']) { ?>
<span class="sidebar_block_header">Notes:</span>
<p>1) <?php  echo  CHtml::encode($companyInfo['client_admins']); ?></p>
<p>2) <?php  echo  CHtml::encode($companyInfo['come_from']); ?></p>
<p>3) <?php  echo  CHtml::encode($companyInfo['company_activated']); ?></p>
        <?php if ($active_client == 0) { ?>
            <p>4) <?php  echo  CHtml::encode($companyInfo['client_active']); ?></p>
        <?php } ?>
<?php } else if ($new_client == 1) { ?>

        <span class="sidebar_block_header">Notes:</span>
        <p>1) <?php  echo  CHtml::encode($companyInfo['company_activated']); ?></p>
        <p>2) <?php  echo  CHtml::encode($companyInfo['client_active']); ?></p>
    <?php } else if ($active_client == 0) {  ?>
        <span class="sidebar_block_header">Notes:</span>
        <p>1) <?php  echo  CHtml::encode($companyInfo['client_active']); ?></p>
    <?php } ?>
<?php
}
?>