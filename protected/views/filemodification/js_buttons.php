<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 9/18/14
 * Time: 1:02 PM
 */
echo '<div style="margin-left: 100px;width: 200px;">';
if($docId) {
    $id=$docId;
    $str=0;
} else if ($file_name)  {
    $id=$file_name;
    $str=1;

} else if ($imgId)  {
    $id= $imgId;
    $str=0;
}

foreach ($buttons as $button)
{
    if($button=="rotate_cw") {
        echo '<div id="rotate_button_cw" class="edit_button" data-id="'.$id.'"> <img src="'.Yii::app()->request->baseUrl.'/images/buttons/rotate_cw.png"  > </div>';

    }
    if($button=="rotate_ccw") {
        echo '<div id="rotate_button_ccw" class="edit_button" data-id="'.$id.'"> <img src="'.Yii::app()->request->baseUrl.'/images/buttons/rotate_ccw.png"  > </div>';

    }
    if($button=="zoom-in") {
        echo '<div id="zoom-in" class="edit_button" data-id="'.$id.'"> <img src="'.Yii::app()->request->baseUrl.'/images/buttons/zoom-in.png"  > </div>';
    }
    if($button=="zoom-out") {
        echo '<div id="zoom-out" class="edit_button" data-id="'.$id.'"> <img src="'.Yii::app()->request->baseUrl.'/images/buttons/zoom-out.png"  > </div>';
    }



}
echo '</div>';
?>

