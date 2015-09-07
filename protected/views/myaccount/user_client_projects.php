<?php
    if (count($userProjects) > 0) {
        $projectsList = '';
        foreach ($userProjects as $projectID => $projectName) {
            $projectsList .= "<li style='min-height: 22px; min-width: 120px; text-align: left; padding-left: 2px;'><span class='user_project_id'>" . $projectID . "</span> / <span class='user_project_name'>" . CHtml::encode($projectName) . "</span></li>";
        }
        echo $projectsList;
    }
