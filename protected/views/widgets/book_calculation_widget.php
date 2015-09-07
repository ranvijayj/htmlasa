<div class="modal_box" id="book_calulation_widget" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>

    <h2>Book info:</h2>

    <br>
    <table>
    <tr>
        <td><span class="search_block_label"> Please input how many copies you want to order : </span></td>
        <td><input size="5" id="book_copies_count" name="copies_count" class="recountable" style="width: 100px;" value="1"></td>
    </tr>

    <tr>
        <td>
            <span class="search_block_label"> Please select how many pages should be printed on one sheet of paper : </span>
        </td>
        <td>
            <select id="book_pages_on_sheet" class="recountable" name="book_pages_on_sheet" style="width: 100px;">
                <option selected="selected">1</option>
                <option>2</option>
                <option>4</option>
            </select>
        </td>
    </tr>

    <tr>
        <td>
            <span class="search_block_label"> Please choose poligraphy quality : colored or black/white </span>
        </td>
        <td>
            <select id="book_quality" class="recountable" name="quality" style="width: 100px;"  >
                <option selected="selected">single sided</option>
                <option>double sided</option>
            </select>
        </td>
    </tr>


  </table>


    <input class="button" id="analog_book_prize" type="submit" value="Pay">
    <br>
</div>






















