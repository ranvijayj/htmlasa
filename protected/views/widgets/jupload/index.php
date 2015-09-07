
<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Bootstrap styles -->





<div class="container" style="width: 500px; position: inherit; ">
    <!-- The fileinput-button span is used to style the file input field as button -->
    <span class="btn btn-success fileinput-button">
        <i class="glyphicon glyphicon-plus"></i>
        <span>Select files...</span>
        <!-- The file input field used as target for the file upload widget -->
        <input id="fileupload" type="file" name="files[]" multiple>
    </span>
    <br>
    <br>
    <!-- The global progress bar -->
       <div id="progress" class="progress">
          <div class="progress-bar progress-bar-success progress-animated"></div>
      </div>
      <!-- The container for the uploaded files -->

    <div id="files" class="files"></div>
    <br>
</div>

    <script>
        /*jslint unparam: true */
        /*global window, $ */
        $(function () {
            'use strict';
            var url = '/uploads/UploadStatus';
            $('#fileupload').fileupload({
                url: url,
                dataType: 'json',
                done: function (e, data) {
                    $.each(data.result.files, function (index, file) {
                        $('<p/>').text(file.name).appendTo('#files');
                    });
                },
                progressall: function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    setTimeout($('#progressbar').val(progress), 1000)

                }
            }).prop('disabled', !$.support.fileInput)
                .parent().addClass($.support.fileInput ? undefined : 'disabled');
        });
    </script>
