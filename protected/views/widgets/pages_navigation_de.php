<div class="<?php echo isset($position) ? $position : 'right'; ?> items_switch_de">
    <?php
    if ($page == 1) {
        ?>
        <button class="not_active_button left">Prev</button>
    <?php
    } else if ($page == 2) {
        ?>
        <a href="<?=Yii::app()->createUrl($url);?>" class="button left">Prev</a>
    <?php
    } else {
        ?>
        <a href="<?=Yii::app()->createUrl($url,array('page'=>($page - 1)));?>" class="button left">Prev</a>
    <?php
    }
    ?>
    <span class="items_switch_counter_de">

        <input type="text" value="<?=$page?>" class="in_place_input" style="width:30px;" >
        of <?php echo $num_pages; ?>
    </span>
    <?php
    if ($page == $num_pages) {
        ?>
        <button class="not_active_button right">Next</button>
    <?php
    } else {
        ?>
        <a href="<?=Yii::app()->createUrl($url,array('page'=>($page + 1)));?>" class="button right">Next</a>
    <?php
    }
    ?>
</div>