<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 10/30/14
 * Time: 3:53 PM
 */ ?>



<div>
    <button id="prev">Previous</button>&nbsp; &nbsp;
    <button id="next">Next</button>
    &nbsp; &nbsp;
    <span>Page: <span id="page_num"></span> / <span id="page_count"></span></span>
</div>
<br/><br/>
<div style="height: 700px;width:700px; overflow:auto; ">
    <canvas id="the-canvas" style="border:1px solid black"></canvas>
</div>


<div id="upper_bar" style="display: block; width: auto;height: 30px;position: absolute;top: 17px;left: 200px;">
<?php //Buttons modification widget
    $this->renderPartial("application.views.filemodification.js_buttons",array(
        'buttons' => array('rotate_cw','rotate_ccw','zoom-in','zoom-out'),
        'docId'  => $doc_id
    ));
    //end of widget
?>
</div>