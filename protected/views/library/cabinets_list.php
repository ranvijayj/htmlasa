<?php
if (count($cabinets) > 0) {
    foreach ($cabinets as $cabinet) {
        $empty = (count($cabinet['sections']) > 0) ? false : true;
        echo '<div data-storage="0" class="storage_block">';
        echo '<div class="table_row' . ($cabinet['selected'] ? ' selected_item' : '') . '" data-row-type="storage" data-storage="0" data-id="' . $cabinet['id'] . '" data-empty="' . $empty . '" data-deletable="' . (($cabinet['created'] == Yii::app()->user->userID) ? '1' : '0') . '">
                                 <span>
                                     ' . ((!$empty) ? '<span class="tree_nav" data-status="' . $cabinet['status'] . '"><span class="' . (($cabinet['status'] == 'opened') ? 'minus_icon' : 'plus_icon') . '"></span><span class="cabinet_' . $cabinet['status'] . '"></span></span>' : '<span class="wh20"></span><span class="cabinet_closed"></span></span>') .
                                     '<span class="lib_cont">'  .Helper::cutText(15,260,30,$cabinet['name'])  . '</span>
                                 </span>
                              </div>';
        if (count($cabinet['sections']) > 0) {
            $drawers =  $cabinet['sections'];
            foreach ($drawers as $category => $drawer) {
                echo '<div data-storage="0" class="drawer_block" ' . (($cabinet['status'] == 'opened') ? 'style="display: block"' : '') . '>';
                echo '<div class="table_row' . ($drawer['selected'] ? ' selected_item' : '') . '" data-row-type="drawer" data-storage="0" data-category="' . $category . '" data-sections="' . count($drawer['sections']) . '">
                                              <span class="width480 inblock">
                                                  <span class="wh44_20"></span><span class="tree_nav" data-status="' . $drawer['status'] . '"><span class="' . (($drawer['status'] == 'opened') ? 'minus_icon' : 'plus_icon') . '"></span></span>
                                                  <span class="lib_cont">'  . Helper::cutText(15,100,30,$drawer['title']) . '</span>
                                              </span>
                                            </div>';
                $folders = $drawer['sections'];
                foreach ($folders as $folder) {
                    $empty = (count($folder['subsections']) > 0) ? false : true;
                    echo '<div data-storage="0" class="section_block" ' . (($drawer['status'] == 'opened') ? 'style="display: block"' : '') . '>';
                    echo '<div class="table_row' . ($folder['selected'] ? ' selected_item' : '') . '" data-row-type="section" data-storage="0" data-category="' . $category . '" data-id="' . $folder['id'] . '" data-empty="' . $empty . '" data-deletable="' . (($folder['created'] == Yii::app()->user->userID) ? '1' : '0') . '" data-subsections="' . count($folder['subsections']) . '">
                                              <span class="width480 inblock">
                                                  <span class="wh66_20"></span>' . ((!$empty) ? '<span class="tree_nav" data-status="' . $folder['status'] . '"><span class="' . (($folder['status'] == 'opened') ? 'minus_icon' : 'plus_icon') . '"></span><span class="folder_' . $folder['status'] . '"></span></span>' : '<span class="wh20"></span><span class="folder_closed"></span>') .
                                                  '<span class="lib_cont">' . Helper::cutText(15,260,40,$folder['name']) . '</span>
                                              </span>
                                              ' . (($organizePage) ? '' : '
                                               <span>
                                                  <span class="lib_cont">'  . $folder['type'] . '</span>
                                              </span>') . '
                                            </div>';
                    if (count($folder['subsections']) > 0) {
                        $panels = $folder['subsections'];
                        foreach ($panels as $panel) {
                            echo '<div data-storage="0" class="subsection_block" ' . (($folder['status'] == 'opened') ? 'style="display: block"' : '') . '>';
                            echo '<div class="table_row' . ($panel['selected'] ? ' selected_item' : '') . '" data-row-type="subsection" data-storage="0" data-id="' . $panel['id'] . '" data-deletable="' . (($panel['created'] == Yii::app()->user->userID) ? '1' : '0') . '" data-subsections="' . count($folder['subsections']) . '">
                                 <span>
                                      <span class="wh110_20"></span><span class="panel_icon"></span><span class="lib_cont">'  . Helper::cutText(15,260,30,$panel['name'])  . '</span>
                                 </span>
                              </div>';
                            echo '</div>';
                        }
                    }
                    echo '</div>';
                }

                echo '</div>';
            }
        }
        echo '</div>';
    }
} else {
    echo '<div>
              Cabinets were not found.
          </div>';
}