<div class="modal_box" id="audit_view_block" style="display:none;">


    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/cancel.png" alt="" class="hidemodal cancelbutton"/>

    <div style="float: left;width: 260px;padding-top: 15px;padding-left: 10px;">
        <h4><b>Document Approval Audit Trail</b></h4>
    </div>


    <div style="padding: 10px;float: right;">

        <button class="button" id="print_audit" data="<?php echo $client->Client_ID; ?>">Print...</button>

        <button class="button hidemodal" id="cansel_audit" data="<?php echo $client->Client_ID; ?>">Cancel</button>

    </div>
    <div  id="audit_view_block_detail" style="border: 1px solid;float: right;overflow-y:auto;overflow-x:hidden; height: 250px ;" ></div>

    <div style="float: left; margin: 10px 10px 10px 0px;">
    <input type="checkbox" id="audit_checkbox" > Show/hide full information
    </div>


</div>
<script>
    $(document).ready(function() {

        $('#audit_checkbox').on('change',function (event) {
           // event.preventDefault();
            var audit_mode = '';
            if( !$(this).prop('checked')) {
                audit_mode = 'Approved';
            }
            $.ajax({
                url: "/documents/ViewAudits",
                data: {
                    docId: $('#audit_doc_id').data('id'),
                    audit_mode: audit_mode
                },
                type: "POST",
                success: function(data) {
                    $('#audit_view_block_detail').html(data);

                    //show_modal_box('#audit_view_block',505)
                }
            });
        });

        $('#print_audit').on('click',function () {
            var audit_mode = '';
            if( !$('#audit_checkbox').prop('checked')) {
                audit_mode = 'Approved';
            }
            var docId = $('#audit_doc_id').data('id');
            var url= '/documents/printaudit/doc/'+docId+'/action/'+audit_mode;
            window.open(url,'_blank');

        })


    });
</script>
