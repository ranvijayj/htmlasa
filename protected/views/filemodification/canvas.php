<div class="canvas_modal_box" id="canvas_modal" >
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <div id="upper_bar" style="display: block; width: auto;height: 30px;">

    <?php
    //Buttons modification widget
    $this->renderPartial("application.views.filemodification.buttons",array(
        'buttons' => array('rotate_cw','rotate_ccw','delete','replace'),
        'docId'  => 1111,
    ));
    //end of widget
    ?>

    </div>

    <canvas id="canvas" width="210" height="297"></canvas>

    <? echo '<img id="background"   src="'.$images[0]["name"].'">';?>


    <div id="tools_bar"></div>
</div>




