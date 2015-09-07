<ul class="sidebar_list">
    <li><h2 class="sidebar_block_list_header"><?php  echo  CHtml::encode($company->Company_Name); ?></h2></li>
    <li>Company ID: <?php  echo  $company->Company_ID; ?></li>
    <li>Fed ID: <?php  echo  $company->Company_Fed_ID; ?></li>
    <li>Address: <?php  echo  CHtml::encode($address->Address1); ?></li>
    <li>City: <?php  echo  CHtml::encode($address->City); ?></li>
    <li>State: <?php  echo  CHtml::encode($address->State); ?></li>
    <li>Zip: <?php  echo  CHtml::encode($address->ZIP); ?></li>
</ul>
<span class="sidebar_block_header">Notes:</span>
<p>1) This company will be automatically activated after letter is generated</p>