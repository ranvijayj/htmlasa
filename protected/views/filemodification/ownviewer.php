<!--
PDF.js own viewer
custom viewer based on PDF.JS sample customised as a built-in Chrome viewer. Has text selection functionallity
-->

<div id="top_canvas_wrapper" style="position: relative;">
    <div style="display: none;">
        <button id="prev">Previous</button>&nbsp; &nbsp;
        <button id="next">Next</button>
        &nbsp; &nbsp;
        <span>Page: <span id="page_num"></span> / <span id="page_count"></span></span>
    </div>


    <input id="doc_id_input" type="hidden" data-id="<?=$doc_id?>">

    <div id="canvas_wrapper" style="padding: 10px;overflow:auto;max-height: 780px;min-height:780px; position: relative;background-color: #d3d3d3;">


    </div>

    <div id="chrome_like_buttons" style="position: absolute;bottom: 35px;width: 95%;height: 10%;margin: 2px;border: 0px solid;">
        <div class="buttons_container" style="display: none; position: relative;float: right;padding-right: 20px;">
            <div id="autofit" class="pdf_button" style="width: auto;"> <img src="/images/buttons/buttons1.png"> </div>
            <div id="zoom-out" class="pdf_button" style="width: auto;"> <img src="/images/buttons/buttons2.png"> </div>
            <div id="zoom-in" class="pdf_button" style="width: auto;"> <img src="/images/buttons/buttons3.png"> </div>

            <?if ($approved) {?>
            <div id="print_button" class="pdf_button" style="width: auto;"> <img src="/images/buttons/buttons5.png"> </div>
            <?}?>

            <div id="fittowidth" class="pdf_button" style="width: auto;"> <img src="/images/buttons/buttons4.png"> </div>
        </div>
    </div>

</div>


<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/pdf_js/build/pdf.js"></script>
<!-- These files are viewer components that you will need to get text-selection to work -->
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/pdf_js/build/ui_utils.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/pdf_js/build/text_layer_builder.js"></script>



<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/pdf_viewer.js"></script>


<script type="text/javascript">
    $(document).ready(function() {
        var doc_id = $('#doc_id_input').data('id');

        var fm=new AsaPdfViewer('canvas',doc_id,'false');

        $('#chrome_like_buttons').mouseenter(function () {
                $('.buttons_container').fadeIn('slow');
        });

        $('#chrome_like_buttons').mouseleave(function () {
                $('.buttons_container').fadeOut('slow');
            console.log('mouseout');
        });



        $('canvas').bind('contextmenu', function(e) {
            return false;
        });
         $('#canvas_wrapper').bind('contextmenu', function(e) {
            return false;
        });
         $('.pdfpage').bind('contextmenu', function(e) {
            return false;
        });

        $('.pdfpage canvas').bind('contextmenu', function(e) {
            return false;
        });








    });
</script>
