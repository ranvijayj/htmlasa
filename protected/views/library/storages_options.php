<?php
$selected = '';
if ($storageType == 'storages') {
    echo '<option selected="selected" value="0">Chose cabinet/shelf</option>';
} elseif ($storageType == 'sections') {
    echo '<option selected="selected" value="0">Chose folder/binder</option>';
} elseif ($storageType == 'subsections') {
    $selected = (count($storages) == 1) ? '' : 'selected="selected"';
    echo '<option ' . $selected . ' value="0">Chose panel/tab</option>';
    $selected = (count($storages) == 1) ? 'selected="selected"' : '';
}

if ($folders) {
    foreach ($storages as $sectionTypeID => $drawer) {
        echo '<optgroup label="' . $drawer['title'] . '">';
        foreach ($drawer['sections'] as $id => $storageName) {
            echo '<option value="' . $id . '">' . $storageName . '</option>';
        }
        echo '</optgroup>';
    }
} else {
    foreach ($storages as $id => $storageName) {
        echo '<option value="' . $id . '" ' . $selected . '>' . CHtml::encode($storageName) . '</option>';
    }
}
