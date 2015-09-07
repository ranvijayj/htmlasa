<?php

if (count($companies['not_printed']) > 0) {
    foreach ($companies['not_printed'] as $company) {
        echo '<tr id="empty_company' . $company->Company_ID . '">';
        echo '<td class="width280 text_left">' . CHtml::encode($company->Company_Name) .  '</td><td class="status width50">N</td><td><a href="/admin/default/generateletter?id=' . $company->Company_ID . '" target="_blank" class="button_small">Print</a></td>';
        echo '</tr>';
    }
}

if (count($companies['printed']) > 0) {
    foreach ($companies['printed'] as $company) {
        echo '<tr id="empty_company' . $company->Company_ID . '">';
        echo '<td class="width280 text_left">' . CHtml::encode($company->Company_Name) .  '</td><td class="status width50">Y</td><td><a href="/admin/default/generateletter?id=' . $company->Company_ID . '" target="_blank" class="button_small">Print</a></td>';
        echo '</tr>';
    }
}

if (count($companies['printed']) == 0 && count($companies['not_printed']) == 0) {
    echo '<tr id="empty_company0">';
    echo '<td clospan="2">Companies were not found</td>';
    echo '</tr>';
}

?>