<div class='list_block'>
    <table id="list_table_head">
        <thead>
        <tr class="table_head">
            <th class="width30">
                <input type="checkbox" id="check_all" name="check_all"/>
            </th>
            <th class="width235" id="name_cell_header">
                Employee Name
            </th>
            <th id="number_cell_header" class="width100">
                Number
            </th>
            <th class="amount_cell width80" id="amount_cell_header">
                Total
            </th>
            <th id="date_cell_header">
                Date
            </th>
        </tr>
        </thead>
    </table>
    <div class="table_list_scroll_block">
    <form action="/pc/addpcsitemstosession" id="detail_form" method="post">
        <table id="list_table">
            <tbody>
            <?php $this->renderPartial('application.views.pc.pcslist', array(
                'pcsList' => $pcsList,
            ));
            ?>
            </tbody>
        </table>
    </form>
    </div>
</div>