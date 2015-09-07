<span class="sidebar_block_header">Details:</span>
<ul class="sidebar_list">
    <li><h2 class="sidebar_block_list_header"><?php echo isset($name) ? CHtml::encode($name) : ''; ?></h2></li>
    <?php
        foreach ($optionsList as $key => $value) {
            ?>
            <li><?php echo $key; ?>: <span class="details_page_value"><?php echo CHtml::encode($value); ?></span></li>
        <?php
        }
    ?>
</ul>