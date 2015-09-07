<?php

if (count($shelves) > 0) {
    foreach ($shelves as $shelf) {
        $empty = (count($shelf['sections']) > 0) ? false : true;
        echo '<div data-storage="1" class="storage_block">';
        echo '<div class="table_row' . ($shelf['selected'] ? ' selected_item' : '') . '" data-row-type="storage" data-storage="1" data-id="' . $shelf['id'] . '" data-empty="' . $empty . '" data-deletable="' . (($shelf['created'] == Yii::app()->user->userID) ? '1' : '0') . '">
                  <span>
                      ' . ((!$empty) ? '<span class="tree_nav" data-status="' . $shelf['status'] . '"><span class="' . (($shelf['status'] == 'opened') ? 'minus_icon' : 'plus_icon') . '"></span><span class="shelf"></span></span>' : '<span class="wh20"></span><span class="shelf"></span></span>') .
            '<span class="lib_cont">'  . Helper::cutText(15,260,30,$shelf['name']) . '</span>
                  </span>
             </div>';
        if (count($shelf['sections']) > 0) {
            $binders = $shelf['sections'];
            foreach ($binders as $binder) {
                $empty = (count($binder['subsections']) > 0) ? false : true;
                echo '<div data-storage="1" class="section_block" ' . (($shelf['status'] == 'opened') ? 'style="display: block"' : '') . '>';
                echo '<div class="table_row' . ($binder['selected'] ? ' selected_item' : '') . '" data-row-type="section" data-storage="1" data-id="' . $binder['id'] . '" data-empty="' . $empty . '" data-deletable="' . (($binder['created'] == Yii::app()->user->userID) ? '1' : '0') . '" data-subsections="' . count($binder['subsections']) . '">
                                               <span class="width430 inblock">
                                                    <span class="wh44_20"></span>' . ((!$empty) ? '<span class="tree_nav" data-status="' . $binder['status'] . '"><span class="' . (($binder['status'] == 'opened') ? 'minus_icon' : 'plus_icon') . '"></span><span class="folder_' . $binder['status'] . '"></span></span>' : '<span class="wh20"></span><span class="folder_closed"></span>') .
                    '<span class="lib_cont">'  . Helper::cutText(15,260,30,$binder['name']) . '</span>
                                               </span>
                                               ' . (($organizePage) ? '' : '
                                               <span>
                                                  <span class="lib_cont">'  . $binder['type'] . '</span>
                                              </span>') . '
                                            </div>';
                if (count($binder['subsections']) > 0) {
                    $tabs = $binder['subsections'];
                    foreach ($tabs as $tab) {
                        echo '<div data-storage="1" class="subsection_block" ' . (($binder['status'] == 'opened') ? 'style="display: block"' : '') . '>';
                        echo '<div class="table_row' . ($tab['selected'] ? ' selected_item' : '') . '" data-row-type="subsection" data-storage="1" data-id="' . $tab['id'] . '" data-deletable="' . (($tab['created'] == Yii::app()->user->userID) ? '1' : '0') . '" data-subsections="' . count($binder['subsections']) . '">
                                                       <span>
                                                           <span class="wh88_20"></span><span class="panel_icon"></span><span class="lib_cont">'  . Helper::cutText(15,260,30,$tab['name']) . '</span>
                                                        </span>
                                                    </div>';
                        echo '</div>';
                    }
                }
                echo '</div>';
            }
        }
        echo '</div>';
    }
} else {
    echo '<div>
              Shelves were not found.
          </div>';
}