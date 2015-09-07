<div class="modal_box" id="ap_dists_modal" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <h2>AP Distributions</h2>
    <div class="de_dists_fields">
        <table id="attached_invoices_head" class="width280">
            <thead>
            <tr>
                <th class="width90">
                    GL Code
                </th>
                <th class="width70">
                    Amount
                </th>
                <th>
                    Desc.
                </th>
            </tr>
            </thead>
        </table>
        <form method="post">
        <div id="attached_dists_block_add">
                <table id="attached_invoices_add">
                    <tbody>
                    <?php
                    foreach($dists as $key => $dist) {
                        echo '<tr>
                                  <td class="width100">
                                      <input type="text" maxlength="63" value="' . $dist['GL_Dist_Detail_COA_Acct_Number'] . '" name="Dist[' . $key . '][GL_Dist_Detail_COA_Acct_Number]">
                                  </td>
                                  <td class="width80">
                                      <input type="text" class="gl_amount" value="' . $dist['GL_Dist_Detail_Amt'] . '" name="Dist[' . $key . '][GL_Dist_Detail_Amt]">
                                  </td>
                                  <td>
                                      <input type="text" value="' . $dist['GL_Dist_Detail_Desc'] . '" name="Dist[' . $key . '][GL_Dist_Detail_Desc]" maxlength="125">
                                  </td>
                               </tr>';
                    }
                    ?>
                    </tbody>
                </table>
        </div>
        <span id="add_invoice_add">+add item</span>
        <span id="remove_invoice_add">-remove item</span>
            <?php
            if ($invalidDistsSum != '') {
                echo '<div class="errorMessage">' . $invalidDistsSum . '</div>';
            }
            ?>
        <br/>
        <div class="center"> <?php echo CHtml::submitButton('Save',array('id'=>'newcompanybtn','class'=>'button hidemodal')); ?></div>
        </form>
    </div>
</div>