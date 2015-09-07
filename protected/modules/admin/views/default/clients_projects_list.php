<?php

if ($clientID == 0) {
    echo '<tr id="project0">';
    echo '<td clospan="2">Choose company to view projects</td>';
    echo '</tr>';
} else if (count($projectsList)) {
    foreach ($projectsList as $id => $projectName) {
        echo '<tr id="project' . $id  . '">';
        echo '<td class="width180">' . CHtml::encode($projectName) .  '</td><td>' . $id . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr id="project0">';
    echo '<td clospan="2">Projects were not found</td>';
    echo '</tr>';
}
?>