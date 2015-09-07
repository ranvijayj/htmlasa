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
            <th id="amount_cell_header" class="amount_cell width70">
                Amount
            </th>
            <th class="width70" id="due_date_cell_header">
                Due Date
            </th>
            <th>
                <span class="cutted_cell">Note</span>
            </th>
        </tr>
        </thead>
    </table>
    <div class="table_list_scroll_block">
    <form action="/ap/addapitemstosession" id="ap_detail_form" method="post">
        <div id="scroll_wrapper" >
        <table id="list_table">
            <tbody>
            <?php $this->renderPartial('application.views.ap.aplist', array(
                'apList' => $apList,
                'markSelctd' => false,
                'notes' => $notes,
            )); ?>
            </tbody>
        </table>
        </div>
    </form>
    </div>
</div>