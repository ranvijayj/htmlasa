<?if (strpos($mime_type, 'pdf')) {?>
<div id="top_canvas_wrapper" style="position: relative;">
    <div style="display: none;">
        <button id="prev">Previous</button>&nbsp; &nbsp;
        <button id="next">Next</button>
        &nbsp; &nbsp;
        <span>Page: <span id="page_num"></span> / <span id="page_count"></span></span>
    </div>


    <input id="doc_id_input" type="hidden" data-id="<?=$doc_id;?>">

    <div id="canvas_wrapper" style="padding: 10px;overflow:auto;max-height: 780px;min-height:780px; position: relative;background-color: #d3d3d3;">


    </div>

    <div class="buttons_container" style=" position: absolute;width: 280px;bottom: 65px;right: 20px;">
        <?  if ($approved == 1) {
            $display_option = '';
        } else {
            $display_option = 'display : none;';
        }
        ?>
        <div id="fittowidth" class="pdf_button" style="width: auto;float: right;"> <img src="/images/buttons/buttons4.png"> </div>
        <div id="zoom-out" class="pdf_button" style="width: auto;float: right;"> <img src="/images/buttons/buttons2.png"> </div>
        <div id="zoom-in" class="pdf_button" style="width: auto;float: right;"> <img src="/images/buttons/buttons3.png"> </div>
        <div id="autofit" class="pdf_button" style="width: auto;float:right;"> <img src="/images/buttons/buttons1.png"> </div>
    </div>
</div>


<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/pdf_js/build/pdf.js"></script>



<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/pdf_viewer.js"></script>


<script type="text/javascript">
    $(document).ready(function() {
        var doc_id = $('#doc_id_input').data('id');

        var fm=new AsaPdfViewer('canvas',doc_id,'false');



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
<? } else {
    echo '<img src="/documents/getdocumentfile?doc_id=' . $doc_id . '" alt="" id="document_file" title="" class="documet_file width100pn">';
    ?>
    <div class="w9_detail_block_bar">
        <div class="image_buttons right">



            <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/fullsize.jpg" alt=""  class="w9_detail_block_bar_button fullsize_button"/>
            <?php
            if (strpos($mime_type, 'pdf') === false) {
                echo '<img src="' . Yii::app()->request->baseUrl . '/images/minlup.jpg" alt=""  class="w9_detail_block_bar_button minlup_button"/>
                      <img src="' . Yii::app()->request->baseUrl . '/images/pluslup.jpg" alt="" class="w9_detail_block_bar_button pluslup_button"/>';
            }

            echo $show_rotate_buttons_block;
            ?>
        </div>
    </div>


<?}?>
