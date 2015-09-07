<div class="modal_box" id="copy_vendors_box" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <h2>Copy List from Company:</h2>
    <form action="" id="copy_vendors_form">
        <div class="row">
            <label for="copy_vendors_company">
                Company:
            </label>
            <select id="copy_vendors_company" class="txtfield" name="copy_vendors_company">
                <?php
                    if (count($companiesToCopyList) > 0) {
                        ?>
                        <option value="0">Select a company</option>
                        <?php
                        foreach ($companiesToCopyList as $clientId => $companyName) {
                            echo '<option value="' . $clientId . '">' . $companyName . '</option>';
                        }
                    } else {
                        ?>
                        <option value="0">You are not linked to other companies</option>
                        <?php
                    }
                ?>
            </select>
            <div class="errorMessage hidden" id="copy_vendors_company_error">Please choose company to copy Vendors list</div>
        </div>
        <input type="hidden" id="copy_vendors_type" value="0">
        <div class="center">
            <input class="button" id="copy_vendors_submit" type="submit" value="Import">
        </div>
    </form>
</div>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/progress_bar.js"></script>
<script>
    $(document).ready(function() {

      $('#copy_vendors_submit').click(function () {
        var pb= new ProgressBar("vendors_copy");
        pb.startListen();
      });
    });
</script>