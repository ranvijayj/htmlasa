<select id="dropdownaccess_sel">
    <option value="0" <?php echo ($libDoc->Access_Type == 0) ? 'selected="selected"' : ''; ?>>Only for me</option>
    <option value="1" <?php echo ($libDoc->Access_Type == 1) ? 'selected="selected"' : ''; ?>>For all users in Project</option>
</select>