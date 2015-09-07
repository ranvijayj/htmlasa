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
            <th class="width70" id="shortcut_cell_header">
                Shortcut
            </th>
            <th class="width75" id="number_cell_header">
                Number
            </th>
            <th>
                <span class="cutted_cell">Address + City + St + Zip</span>
            </th>
        </tr>
        </thead>
    </table>
    <div class="table_list_scroll_block">

    <form action="/vendor/addvendoritemstosession" id="detail_form" method="post">
        <div id="scroll_wrapper" >
            <?//displaying w9 of the company user logged in?>
            <?//deleted from rev 12859?>
                <table id="list_table">
                    <tbody>
                    <?php $this->renderPartial('application.views.vendor.vendorlist', array(
                        'vendorsList' => $vendorsList,
                    )); ?>
                    </tbody>
                </table>
        </div>
    </form>
    </div>
</div>