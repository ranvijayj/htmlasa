
<?php
/* @var $this RemoteProcessingController */
?>
<br/><br/>
<span class="sidebar_block_header"> Projects available </span>

<form id="remote_processing_form" method="post" action="/remoteprocessing/process">
    <input type="hidden" name="client_id" value=<?=$cli_id?>>
    <table style="border: 1px solid;">


                <?foreach ($projects as $project) {
                    echo '<tr>';
                    echo '<td><input type="checkbox" class="project_list_checkbox" name=projects[] checked value='.$project['Project_ID'].' >'.$project['Project_Name'].'</td>';
                    echo '</tr>';

                } ?>

    </table>
</form>


