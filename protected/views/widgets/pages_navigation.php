<div class="<?php echo isset($position) ? $position : 'right'; ?> items_switch">
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
    <span class="items_switch_counter"> <div style="display: inline" class='in_place_edit' data-editing="0"> <?php echo $page; ?> </div>  of <?php echo $num_pages; ?></span>
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