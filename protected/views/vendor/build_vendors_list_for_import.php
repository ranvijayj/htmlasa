<img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
<div class="left uploads_block_left">
    <p>Import Format Preview:</p>
    <div id="additional_fields_block_conteiner">
        <table id="list_table_head">
            <thead>
            <tr class="table_head">
                <th class="width70">
                    Fed ID
                </th>
                <th class="width250">
                    Company Name
                </th>
                <th class="width40">
                    Shortcut
                </th>
                <th>
                    Checkprint
                </th>
            </tr>
            </thead>
        </table>
        <div id="import_coa_scroll_block">
                <table id="list_table">
                    <tbody>
                    <?php
                    if (count($vendors) > 0) {
                        foreach ($vendors as $row => $vendor) {
                            ?>
                            <tr class="row_for_import">
                                <td class="width70">
                                    <?php echo Helper::cutText(14, 90, 10, $vendor['fedId']); ?>
                                </td>
                                <td class="width250">
                                    <?php echo Helper::cutText(14, 280, 20, $vendor['companyName']); ?>
                                </td>
                                <td class="width40">
                                    <?php echo Helper::cutText(14, 50, 5, $vendor['shortcut']); ?>
                                </td>
                                <td>
                                    <?php echo Helper::cutText(14, 50, 8, $vendor['checkprint']); ?>
                                </td>
                            </tr>
                        <?php
                        }
                    } else {
                        echo '<tr>
             <td>
                 Vendors not found.
             </td>
           </tr>';
                    }
                    ?>
                    </tbody>
                </table>
        </div>
    </div>
</div>
<div class="right uploads_block_right">
    <button class="button" id="submit_import">Import</button> <button class="button hidemodal">Cancel</button>
    <br/>
    <div class="additional_fields_form">
        <form action="/vendor" method="post" id="import_vendors_form">
            <input type="hidden" value="1" name="import_vendors_form">
        </form>
    </div>
</div>
<div class="clear"></div>
