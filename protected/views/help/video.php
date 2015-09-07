<?php
/* @var $this VendorController */

$this->breadcrumbs=array(
    'Help',
);

?>
<h1>Help: <?=@CHtml::encode(Yii::app()->user->userLogin);?></h1>



<div class="account_manage">
    <div class="account_header_left left">

    </div>
    <div class="right">
        <span class="left search_block_label">Search: &nbsp;</span>
        <div class="search_block">
            <input type="text" name="search" value="<?php echo $queryString; ?>" id="search_field" maxlength="250">
            <div id="search_options">
                <span class="search_options_header">Search in the fields:</span><br/>
                <?php
                echo Helper::getSearchOptionsHtml(array(
                    'session_name' => 'last_payments_list_search',
                    'options' => array(
                        'search_option_title' => array('Title', 1),
                        'search_option_log_line' => array('Log line', 0),
                        'search_option_description' => array('Description', 0),
                        'search_option_link_name' => array('Link Name', 0),

                    ),
                ));

                ?>
            </div>
        </div>
    </div>
</div>

<?php if(Yii::app()->user->hasFlash('success')):?>

    <div class="info">
        <button class="close-alert">&times;</button>
        <?php echo Yii::app()->user->getFlash('success'); ?>
    </div>
<?php endif; ?>
<div class="wrapper">

    <div id="video_player_wraper">

    </div>
 </div>

<div class="sidebar_right" id="sidebar" style="position: relative;">

    <div  id="video_list" style="min-height: 500px;">
        <?
        //var_dump($videos);
        foreach ($videos as $video) {
          $str = '<a class="video_link" data-id="'.$video->Video_ID.'" href="'.$video->Video_URL.'">'. $video->Video_Log_Line.'</a><br />';

          if ($video->Visibility !=5 && $video->Visibility !=4) {
                  echo $str;
          } else if ($video->Visibility ==4) {
              if(Yii::app()->user->clientID == $video->Clients_Client_ID) {
                  echo $str;
              }
          } else if ($video->Visibility ==5) {
              if(Yii::app()->user->clientID == $video->Clients_Client_ID && Yii::app()->user->projectID == $video->Project_ID) {
                  echo $str;
              }
          }
        }
        ?>

    </div>

</div>




<script>
    $(document).ready(function() {
       setEqualHeight($(".wrapper,.sidebar_right"));
        new VideoHelp;
    });
</script>

<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/w9_list.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/video_help.js"></script>