<h2>Payment History</h2>

<?php $restricted_users_array=array('user','data_entry_clerk');
$alowed_users_array=array('client_admin','db_admin','admin','approver','processor');
if(in_array($user_role,$alowed_users_array)){
    echo '<div id="user_allowed" data-id="allowed"></div>';
    ?>

<?php $this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'payments-grid',
    'dataProvider'=>$model->searchClientPayments(),
    'columns'=>array(
        array(
            'name'=>'Date',
            'value'=> 'Helper::convertDate($data->Payment_Date)',
            'sortable'=>true,
        ),
        array(
            'name'=>'Amount',
            'value'=>'number_format($data->Payment_Amount, 2)',
            'sortable'=>true,
        ),
        array(
            'name'=>'Paid',
            'value'=>'Y',
            'sortable'=>true,
        )
    ),
   'pager' => array(
            'class' => 'CLinkPager',
            'pageSize' => 5,
            'maxButtonCount'=>5,
   ),
)); ?>

<?php }?>