<div id="ap_dists_cont">
    <?php
    foreach ($dists as $dist) {
        echo '<span class="details_page_value">' . CHtml::encode(number_format($dist->GL_Dist_Detail_Amt, 2)) . ' / ' . CHtml::encode($dist->GL_Dist_Detail_Desc) . '</span><br />';
    }
    ?>
</div>