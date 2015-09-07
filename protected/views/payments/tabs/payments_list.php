<div class='list_block'>
    <table id="list_table_head">
        <thead>
        <tr class="table_head">
            <th class="width30">
                <input type="checkbox" id="check_all" name="check_all"/>
            </th>
            <th class="width180" id="vendor_name_cell_header">
                Payee
            </th>
            <th id="amount_cell_header" class="amount_cell width80">
                Amount
            </th>
            <th class="width100" id="check_number_cell_header">
                Ck. Num.
            </th>
            <th class="width80" id="check_date_cell_header">
                Ck. Date
            </th>
            <th>
                <span class="cutted_cell">Account Num</span>
            </th>
        </tr>
        </thead>
    </table>
    <div class="table_list_scroll_block">
    <form action="/payments/addpaymentsitemstosession" id="detail_form" method="post">
      <div id="scroll_wrapper" >
        <table id="list_table">
            <tbody>
            <?php $this->renderPartial('application.views.payments.paymentslist', array(
                'paymentsList' => $paymentsList,
            ));
            ?>
            </tbody>
        </table>
      </div>
    </form>
    </div>
</div>