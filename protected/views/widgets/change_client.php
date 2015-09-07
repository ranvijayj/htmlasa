<div class="modal_box" id="change_client" style="display:none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>
    <h2>Change Company & Project</h2>
    <form action="/site/changeclient">
        <div class="row">
            <label for="change_client_id">
                Company:
            </label>
            <select id="change_client_id" class="txtfield" name="change_client_id">
                <option value="0">Select a company</option>
            </select>
        </div>
        <div class="row">
            <label for="change_project_id">
                Project:
            </label>
            <select id="change_project_id" class="txtfield" name="change_project_id">
                <option value="0">Select a project</option>
            </select>
        </div>
        <div class="center">
            <input class="button" type="submit" value="Change">
        </div>
    </form>
    <script type="text/javascript">
        $(document).ready(function() {
            $("#change_client form").submit(function(event) {
                if ($('#change_client_id').val() == 0 || $('#change_project_id').val() == 0) {
                    event.preventDefault();
                    close_change_client_box()
                }
            });
        });
    </script>
</div>