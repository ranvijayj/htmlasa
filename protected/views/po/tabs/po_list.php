<div class='list_block'>
    <table id="list_table_head">
        <thead>
        <tr class="table_head">
            <th class="width30">
                <input type="checkbox" id="check_all" name="check_all"/>
            </th>
            <th class="width140" id="vendor_name_cell_header">
                Vendor Name
            </th>
            <th id="number_cell_header" class="amount_cell width30" >
                Number
            </th>
            <th id="amount_cell_header" class="amount_cell width90" >
                Total Amount
            </th>
            <th class="width70" id="budget_cell_header">
                +/- Budget
            </th>
            <th>
                <span class="cutted_cell">Note</span>
            </th>
        </tr>
        </thead>
    </table>
    <div class="table_list_scroll_block">
    <form action="/po/addpoitemstosession" id="po_detail_form" method="post">
        <div id="scroll_wrapper" >
        <table id="list_table">
            <tbody>
            <?php $this->renderPartial('application.views.po.polist', array(
                'poList' => $poList,
                'markSelctd' => false,
                'notes' => $notes,
                'budgets' => $budgets,
            )); ?>
            </tbody>
        </table>
        </div>
    </form>
    </div>
</div>