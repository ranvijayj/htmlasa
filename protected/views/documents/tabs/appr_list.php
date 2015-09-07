<div class='list_block'>
    <table id="list_table_head">
        <thead>
        <tr class="table_head">
            <th class="width10">
                <input type="checkbox" id="check_all" name="check_all"/>
            </th>
            <th class="width130" id="vendor_name_cell_header">
                Vendor Name
            </th>
            <th class="width30" id="type_cell_header">
                Type
            </th>
            <th id="amount_cell_header" class="amount_cell width30">
                Amount
            </th>
            <th class="width90" id="control_cell_header">
                Control
            </th>

            <th>
                Note
            </th>
        </tr>
        </thead>
    </table>
    <div class="table_list_scroll_block">
    <form action="/documents/SendApprCueNotification" id="apr_detail_form" method="post">
        <table id="list_table">
            <tbody>
            <?php $this->renderPartial('application.views.documents.tabs._partial_cue_list', array(
                'cueApprList' => $cueApprList,
                'markSelctd' => false,
            )); ?>
            </tbody>
        </table>
    </form>
    </div>
</div>