<div id="po_tracking_list_cont">
    <table class="scroll_table_head center position_center">
        <thead>
        <tr>
            <th class="width60">

            </th>
            <th class="width205">
                Description
            </th>
            <th class="width55">
                Inv. Date
            </th>
            <th class="width80">
                Inv. #
            </th>
            <th class="width80">
                Pmt. Amt.
            </th>
            <th class="width75">
                Balance
            </th>
        </tr>
        </thead>
    </table>
    <div id="po_tracking_list_block">
        <form method="post" id="po_tracking_form">
        <table id="po_tracking_list">
            <tr>
                <td class="width60"></td>
                <td colspan="4" class="text_left">BEGINNING BALANCE:</td>
                <td class="width75"><?php echo $po->PO_Total ? CHtml::encode(number_format($po->PO_Total, 2)) : ''; ?></td>
            </tr>
            <tbody>
            <?php
            $rowNumb = 1;
            $balance = $po->PO_Total ? $po->PO_Total : 0;
            if (count($poTracks) > 0) {
                foreach($poTracks as $key => $poTrack) {
                    $balance -= $poTrack->PO_Trkng_Pmt_Amt;
                    echo '<tr class="in_place_edit" data-id="'.$poTrack->PO_Trkng_ID.'">
                                  <td class="width60" data-name="PO_Trkng_ID" data-initial-value="'.$rowNumb.'">
                                      ' . $rowNumb . '
                                  </td>
                                  <td class="text_left width200" data-name="PO_Trkng_Desc" data-initial-value="'.$poTrack->PO_Trkng_Desc.'">
                                      ' . $poTrack->PO_Trkng_Desc . '
                                  </td>
                                  <td class="width60" data-name="PO_Trkng_Inv_Date" data-initial-value="'.$poTrack->PO_Trkng_Inv_Date.'">
                                      ' . Helper::convertDate($poTrack->PO_Trkng_Inv_Date ). '
                                  </td>
                                  <td class="width75" data-name="PO_Trkng_Inv_Number" data-initial-value="'.$poTrack->PO_Trkng_Inv_Number.'">
                                      ' . Chtml::encode($poTrack->PO_Trkng_Inv_Number) . '
                                  </td>
                                  <td class="width75" data-name="PO_Trkng_Pmt_Amt" data-initial-value="'.$poTrack->PO_Trkng_Pmt_Amt.'">
                                      ' . Chtml::encode(number_format($poTrack->PO_Trkng_Pmt_Amt, 2)) . '
                                  </td>
                                  <td>
                                      ' . Chtml::encode(number_format($balance, 2)) . '
                                  </td>
                       </tr>';
                    $rowNumb++;
                }
            }
                  if ($balance > 0) {
                      echo '<tr>
                                  <td class="width60">
                                      <b>Add Row:</b>
                                  </td>
                                  <td class="width200 po_trak_inp_cell"><span><input type="text" placeholder="Description" maxlength="125" name="PoPmtsTraking[PO_Trkng_Desc]" value="' . (isset($pmtsTracking['PO_Trkng_Desc']) ? $pmtsTracking['PO_Trkng_Desc'] : '') . '"></span></td>
                                  <td class="width60 po_trak_inp_cell"><span><input  type="text" id="PO_Trkng_Inv_Date" placeholder="Inv. Date" maxlength="10" name="PoPmtsTraking[PO_Trkng_Inv_Date]" value="' . (isset($pmtsTracking['PO_Trkng_Inv_Date']) ? $pmtsTracking['PO_Trkng_Inv_Date'] : '') . '"></span></td>
                                  <td class="width75 po_trak_inp_cell"><span><input type="text" placeholder="Inv. #" maxlength="45" name="PoPmtsTraking[PO_Trkng_Inv_Number]" value="' . (isset($pmtsTracking['PO_Trkng_Inv_Number']) ? $pmtsTracking['PO_Trkng_Inv_Number'] : '') . '"></span></td>
                                  <td class="width75 po_trak_inp_cell"><span><input type="text" id="PO_Trkng_Pmt_Amt" placeholder="Pmt. Amt." maxlength="100" name="PoPmtsTraking[PO_Trkng_Pmt_Amt]" value="' . (isset($pmtsTracking['PO_Trkng_Pmt_Amt']) ? $pmtsTracking['PO_Trkng_Pmt_Amt'] : '') . '"></span></td>
                                  <td class="po_trak_inp_cell"><a href="javascript:void(0)" class="button_small" id="add_po_trak">Add</a></td>
                       </tr>';
                      $rowNumb++;
                  }
            while ($rowNumb <= 20) {
                echo '<tr class="">
                                  <td class="width60">
                                      ' . $rowNumb . '
                                  </td>
                                  <td class="width200"></td>
                                  <td class="width60"></td>
                                  <td class="width75"></td>
                                  <td class="width75"></td>
                                  <td></td>
                       </tr>';
                $rowNumb++;
            }

            ?>
            </tbody>
        </table>
        </form>
    </div>
    <?php
        if ($poError != '') {
            echo '<div class="errorMessage po_track_error">' . $poError . '</div>';
        }
    ?>
    <div class="po_track_note">
        Notes:
    </div>
    <div class="po_track_note_content" id="po_track_note_content"><?php echo nl2br(CHtml::encode($po->PO_Pmts_Tracking_Note)); ?></div>
    <div class="po_track_note_input_block" id="po_track_note_input_block"><textarea id="po_track_note_input" data-id="<?php echo $po->PO_ID; ?>"><?php echo $po->PO_Pmts_Tracking_Note; ?></textarea></div>
</div>

