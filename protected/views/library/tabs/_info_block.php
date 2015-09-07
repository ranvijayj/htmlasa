<span class="sidebar_block_header">Details:</span>
<ul class="sidebar_list">
    <li><br/></li>
    <li></li>
    <li>Total: <span class="details_page_value"><?php echo CHtml::encode(number_format($data['amount'], 2)); ?></span></li>
    <li>Batch type: <span class="details_page_value"><?php echo $data['type']?></span></li>
    <li>Batch format: <span class="details_page_value"><?php echo $data['format']?></span></li>
    <li></li>
    <li></li>
    <li><br/></li>
    <li><br/></li>
    <li>Created: <br/><span class="created_on_date"><?php echo Helper::convertDate($data['created']) . ' by ' . $data['user_name'] . ' ' ; ?></span></li>


</ul>

