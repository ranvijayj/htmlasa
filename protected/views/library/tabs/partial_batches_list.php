<?php

if (Yii::app()->user->projectID == 'all' )  $showAllProjects =true;

if (count($batchesList) > 0) {
    foreach ($batchesList as $batches) {?>

        <tr id="doc<?php echo $batches->Batch_ID ?>">
            <td class="width10">
                <input type="checkbox" class='list_checkbox' name="batches[<?php echo $batches->Batch_ID ?>]" value="<?php echo $batches->Batch_ID; ?>"/>
            </td>
            <td  class="width40" style="text-align: center;">
                <?php echo $batches->Batch_ID; ?>
            </td>
            <td class="width50" style="text-align: center;">
                <?= Helper::convertDate($batches['Batch_Creation_Date']); ?>
            </td>

            <td class="width30" style="text-align: center;">
                <?=$batches['Batch_Source'] ?>
            </td>
            <td class="width50" style="text-align: center;">
                <a href="/documents/getbatchfiles?batch_id=<?=$batches->Batch_ID?>&file=document" > Download</a>
            </td>
            <td class="width30" style="text-align: center;">
                <a href="/documents/getbatchfiles?batch_id=<?=$batches->Batch_ID?>&file=report" > Open</a>
            </td>
            <td class="width40" style="text-align: center;" >
                <input type="checkbox"  class="checkbox_uploaded" <?php echo  $batches->Batch_Uploaded ? 'checked="checked"' : ''; ?> >
            </td>
            <td class="width40" style="text-align: center;" >
                <input type="checkbox" class="checkbox_posted" <?php echo  $batches->Batch_Posted ? 'checked="checked"' : ''; ?>>
            </td>



            <?if ($showAllProjects) { ?>
                <td class="width120" style="text-align: center;" >
                    <?= $batches->project->Project_Name;?>
                </td>
            <?}?>
        </tr>

    <?
    }
} else {
    echo '<tr>
             <td>
                 Batches were not found.
             </td>
           </tr>';
}
?>