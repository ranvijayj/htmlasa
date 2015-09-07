<?php if(Yii::app()->user->hasFlash('success')):?>
    <div class="info home_page_info">
        <button class="close-alert">&times;</button>
        <?php echo Yii::app()->user->getFlash('success'); ?>
    </div>
<?php endif; ?>

<div id="devise_hash" data-id="<?=$dev_hash?>"></div>

<?php $this->renderPartial('application.views.site.homepagelinks'); ?>
<?php $this->renderPartial('application.views.widgets.extralogin',array(
        'users_questions'=>$users_questions,
        'answers_errors'=>$answers_errors
        ));
?>

<?php if (isset($showloginmodal)) {?>
    <script type="text/javascript">
        $(document).ready(function() {

            setTimeout(function() {
                var modal_width=$('#loginmodal').outerWidth();
                $("#lean_overlay").css({"display":"block",opacity:0});$("#lean_overlay").fadeTo(200, 0.45);
                $('#loginmodal').css({"display":"block","position":"fixed","opacity":0,"z-index":11000,"left":"50%","margin-left":"-150px","top":"110px"});
                $('#loginmodal').fadeTo(200,1);
                $("#lean_overlay").click(function(){close_login('#loginmodal')});
                $('.hidemodal').click(function(){close_login('#loginmodal')});
            }, 300);
        });

        function close_login(modal_id) {
            $("#lean_overlay").fadeOut(200);
            $(modal_id).css({"display":"none"});
        }
    </script>
<?php } ?>

<?php if (isset($showextraloginmodal)) {?>
    <script type="text/javascript">
        $(document).ready(function() {

            setTimeout(function() {
                var modal_width=$('#extraloginmodal').outerWidth();
                $("#lean_overlay").css({"display":"block",opacity:0});$("#lean_overlay").fadeTo(200, 0.45);
                $('#extraloginmodal').css({"display":"block","position":"fixed","opacity":0,"z-index":11000,"left":"50%","margin-left":"-150px","top":"110px"});
                $('#extraloginmodal').fadeTo(200,1);
                $("#lean_overlay").click(function(){close_login('#extraloginmodal')});
                $('.hidemodal').click(function(){close_login('#extraloginmodal')});
            }, 300);
        });

        function close_login(modal_id) {
            $("#lean_overlay").fadeOut(200);
            $(modal_id).css({"display":"none"});
        }
    </script>
<?php } ?>


<?php
if (isset($showregistermodal)) {
    ?>
    <script type="text/javascript">
        $(document).ready(function() {
            setTimeout(function() {
                var modal_width=$('#registermodal').outerWidth()+20;
                $("#lean_overlay").css({"display":"block",opacity:0});$("#lean_overlay").fadeTo(200, 0.45);
                $('#registermodal').css({"display":"block","position":"fixed","opacity":0,"z-index":11000,"left":"50%","margin-left":"-150px","top":"50px"});
                $('#registermodal').fadeTo(200,1);
                $("#lean_overlay").click(function(){close_register('#registermodal')});
                $('.hidemodal').click(function(){close_register('#registermodal')});

                var clientId = $('#clientId').val();
                if (clientId == 0) {
                    $('#registermodal').css('width','560px');
                    $('#registermodal').css('marginLeft','-291px');
                    $('#companyInfo').show();
                } else {
                    $('#companyInfo').hide();
                }
            }, 300);
        });

        function close_register(modal_id) {
            $("#lean_overlay").fadeOut(200);
            $(modal_id).css({"display":"none"});
        }
    </script>
<?php
}
?>

<?php
if (isset($showforgotpasswordmodal)) {
    ?>
    <script type="text/javascript">
        $(document).ready(function() {
            setTimeout(function() {
                var modal_width=$('#forgotpasswordbox').outerWidth();
                $("#lean_overlay").css({"display":"block",opacity:0});
                $("#lean_overlay").fadeTo(200, 0.45);
                $('#forgotpasswordbox').css({"display":"block","position":"fixed","opacity":0,"z-index":11000,"left":"50%","margin-left":"-150px","top":"110px"});
                $('#forgotpasswordbox').fadeTo(200,1);
                $("#lean_overlay").click(function(){close_forgot_password('#forgotpasswordbox')});
                $('.hidemodal').click(function(){close_forgot_password('#forgotpasswordbox')});
            }, 300);
        });

        function close_forgot_password(modal_id) {
            $("#lean_overlay").fadeOut(200);
            $(modal_id).css({"display":"none"});
        }
    </script>
<?php
}
?>


<script type="text/javascript">
//  var ec = new evercookie();
//    ec.set("devise_hash", $('#devise_hash').data('id'));
</script>