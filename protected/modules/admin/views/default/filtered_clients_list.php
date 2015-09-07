<?php
if (count($clientsList)) {
    foreach ($clientsList as $id => $companyName) {
        echo '<tr id="client' . $id  . '">';
        echo '<td class="width180">' . CHtml::encode($companyName) .  '</td><td>' . $id . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr id="client0">';
    echo '<td clospan="2">Companies were not found</td>';
    echo '</tr>';
}
?>