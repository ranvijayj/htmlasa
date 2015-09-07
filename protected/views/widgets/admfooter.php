<admfooter style="display: none">
<br/>
    <select id="select_operation" name="operation[]" style="margin-left: 20px">
        <option disabled selected="selected">Select action</option>
        <option>Change_approval_value_to</option>
        <option>Change_batched_value_to</option>

    </select>

    <input type="text" id="new_value_to_change" size="10" value="0">
    <input type="submit" id="submit_items_for_changing" value="Change value for all selected items">


</admfooter>
<script type="text/javascript">
    $(document).ready(function() {

        $('#submit_items_for_changing').click(function(){
            event.stopPropagation();
            var arr= [];
            var action = $( "#select_operation option:selected" ).text();
            var value = $( "#new_value_to_change" ).val();
            var i = 0;


            $('.list_checkbox:checked').each(function() {
                arr[i] = $(this).val();
                i++;
            });
            console.log(arr);
            console.log(action);


            $.ajax({
                url: "/documents/admchange",
                data: { docIds: arr,
                        action:action,
                        value:value
                },
                type: "POST",
                success: function() {
                    window.location = '/ap';
                }
            });
        });


    });


</script>
