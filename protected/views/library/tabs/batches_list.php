<?
if (Yii::app()->user->projectID == 'all' )  $showAllProjects =true;
?>
<form action="batches/viewstorage" id="bathes_form_for_view" method="post">
<div class='list_block'>
    <table id="list_table_head">
        <thead>
        <tr  class="table_head">
            <th  class="width10">
                <input type="checkbox" id="check_all" name="check_all"/>
            </th>

            <th id="number_cell_header" class="width40" id="number_cell_header" style="text-align: center;">
                Number
            </th>

            <th id="date_cell_header" class="width50" style="text-align: center;">
                Date
            </th>

            <th class="width30" id="source_cell_header" style="text-align: center;">
                Type
            </th>

            <th class="width50" id="export_cell_header" style="text-align: center;">
                Export
            </th>
            <th class="width30" id="file_cell_header" style="text-align: center;">
                File
            </th>
            <th class="width40" id="uploaded_cell_header" style="text-align: center;">
                Status uploaded
            </th>
            <th class="width40" id="posted_cell_header" style="text-align: left;">
                Status  posted
            </th>
            <?if ($showAllProjects) { ?>
                <th class="width120" id="project_cell_header" >
                    Project
                </th>
            <?}?>


        </tr>
        </thead>
    </table>
    <div class="table_list_scroll_block">

        <form action="" id="detail_form" method="post">
            <table id="list_table">
                <tbody>


<?php
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
                                <a href="/documents/getbatchfiles?batch_id=<?=$batches->Batch_ID?>&file=report" target="_blank" > Open</a>
                            </td>
                            <td class="width40" style="text-align: center;" >
                               <input type="checkbox"  class="checkbox_uploaded" <?php echo  $batches->Batch_Uploaded ? 'checked="checked"' : ''; ?> >
                            </td>
                            <td class="width40" style="text-align: center;" >
                                <input type="checkbox" class="checkbox_posted" <?php echo  $batches->Batch_Posted ? 'checked="checked"' : ''; ?>>
                            </td>

                            <?if ($showAllProjects) {  ?>
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
                </tbody>
            </table>
        </form>
      </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        setEqualHeight($(".wrapper,.sidebar_right"));
        new BatchesList;
    });
</script>

<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/batches_list.js"></script>
