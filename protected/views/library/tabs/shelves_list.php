<div class='list_block'>
    <table id="list_table_head">
        <thead>
        <tr class="table_head">
            <th class="width420" id="shelves_names_header">
                Storage Name
            </th>
            <?php
            if (!$organizePage) {
                ?>
                <th>
                    Category
                </th>
            <?php
            }
            ?>
        </tr>
        </thead>
    </table>
    <div class="table_list_scroll_block">
        <div id="shelves_table">
            <?php
            $this->renderPartial('application.views.library.shelves_list', array(
                'shelves' => $shelves,
                'organizePage' => $organizePage,
            ));
            ?>
        </div>
    </div>
</div>