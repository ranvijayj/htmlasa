<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 9/18/14
 * Time: 1:02 PM
 */
echo '<div style="margin-left: 100px;width: 200px;">';
if($docId) {
    $id = intval($docId);
    $str=0;

    //check if current document can be deleted or replaced
    $doc_to_modify = Documents::model()->findByPk($id);
    $can_be_changed = false;
    if (
        //1
        (Documents::hasAccess($id) && Documents::hasDeletePermission($id, $doc_to_modify->Document_Type, Yii::app()->user->userID, Yii::app()->user->clientID))
        ||
        //2
        (Yii::app()->user->userType == UsersClientList::APPROVER)
    )
    {
            $can_be_changed = true;
    }

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
        //    <img src="'.Yii::app()->request->baseUrl.'/images/buttons/rotate.png"  > <span id="doc_id" data-id="33"></span>

    }
    if($button=="rotate_ccw") {
        echo '<div id="rotate_button_ccw" class="edit_button" data-id="'.$id.'"> <img src="'.Yii::app()->request->baseUrl.'/images/buttons/rotate_ccw.png"  > </div>';
        //    <img src="'.Yii::app()->request->baseUrl.'/images/buttons/rotate.png"  > <span id="doc_id" data-id="33"></span>

    }
    if($button=="zoom-in") {
        echo '<div id="zoom-in" class="edit_button" data-id="'.$id.'"> <img src="'.Yii::app()->request->baseUrl.'/images/buttons/zoom-in.png"  > </div>';
        //    <img src="'.Yii::app()->request->baseUrl.'/images/buttons/rotate.png"  > <span id="doc_id" data-id="33"></span>
    }
    if($button=="zoom-out") {
        echo '<div id="zoom-out" class="edit_button" data-id="'.$id.'"> <img src="'.Yii::app()->request->baseUrl.'/images/buttons/zoom-out.png"  > </div>';
        //    <img src="'.Yii::app()->request->baseUrl.'/images/buttons/rotate.png"  > <span id="doc_id" data-id="33"></span>
    }
    if($button=="delete") {
        if ($can_be_changed) {
            echo '<div id="delete-icon" class="edit_button" data-id="'.$id.'"> <img src="'.Yii::app()->request->baseUrl.'/images/buttons/delete-icon.png" title="Delete file"  > </div>';
        }
    }
    if($button=="reupload") {
        if ($can_be_changed) {
            echo '<div id="reupload-icon" class="edit_button" data-id="'.$id.'"> <img src="'.Yii::app()->request->baseUrl.'/images/buttons/reupload.png" title="Reupload file" > </div>';
        }
    }




}

echo '</div>';
?>

<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/file_modification.js"></script>

<script>

    $(document).ready(function() {
            $(".edit_button").click(function () {
                var fm= new FileModification(this.id,'<?=$id ?>',<?=$str ?>,'');
            });
        }
    );

</script>