<?php
if (count($projects) > 0) {
    if ($showAllProjects) {
        echo '<option value="all">All Projects</option>';
    }
    foreach($projects as $project) {
        if ($currentProject == $project->Project_ID) {
            echo '<option value="' . $project->Project_ID . '" selected="selected">' . CHtml::encode($project->Project_Name) . '</option>';
        } else {
            echo '<option value="' . $project->Project_ID . '">' . CHtml::encode($project->Project_Name) . '</option>';
        }
    }
} else {
    echo '<option value="0" selected="selected">You are not linked to any project of this company</option>';
}
?>