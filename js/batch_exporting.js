function BatchExporting(docType, listClass) {
    this.init(docType, listClass);
}

BatchExporting.prototype = {
    /*
     * Type of documents
     */
    docType: null,

    /**
     *
     */
    listClass: null,
    PB: null,

    /*
     * Initialize method
     */
    init: function(docType, listClass) {
        var self = this;

        self.docType = docType;
        self.listClass = listClass;



        $("#submit_for_batch").click(function () {
            if ($(this).hasClass('button')) {
                show_modal_box('#batch_export_modal_box');

            }
        });

        $('#batch_export_form').submit(function(event) {
            event.preventDefault();
            close_modal_box('#batch_export_modal_box');

            self.PB= new ProgressBar();
            $("#progress_bar_container").fadeIn('fast');
            $( "#progressbar_jui" ).progressbar({value: false});

            var pb_interval= setInterval(function() {
                self.PB.stepTo(10);
                self.startProgressListening(pb_interval);
                },300);

            setTimeout(function() {
                self.batch();
            }, 210);

        });


    },

    startProgressListening: function(pb_interval) {

            $.ajax({
                url: "/site/progressbar",
                type: "POST",
                async:true,
                dataType: 'json',

                success: function(data) {
                    if (data.success) {

                        if ( data.progress <= 99 ) {
                            $( "#progressbar_jui" ).progressbar({
                                    value: data.progress
                            });

                        } else {
                            clearInterval(pb_interval);
                        }

                    } else {
                        show_alert("Batch wasn't generated!", 400);
                        $("#progress_bar_container").fadeOut(500);

                    }
                }
            });


    },

    batch: function() {
        var self = this;
        var documents = [];
        var checked = $(".list_checkbox:checked");
        checked.each(function() {
            var docId = $(this).val();
            documents.push(docId);
        });
        //show_alert('Please wait...');
        $.ajax({
            url: "/documents/startbatch",
            data: {
                documents: documents,
                date_time: new Date(),
                batchType: $('#batch_export_type').val(),
                batchFormat: $('#batch_export_format').val(),
                docType: self.docType
            },
            type: "POST",
            dataType: 'json',

            success: function(data) {
                if (data.success == 1) {

                    close_alert();

                    self.listClass.updateList();

                    window.location = data.urlDocument;
                    window.open(data.urlReport,'_blank' );

                    //window.open(data.urlDocument, 'popup', 'toolbar=no, menubar=no, status=no, location=no, resizable=no, width=640, height=600');
                    //window.open(data.urlReport, '_blank', 'toolbar=no, menubar=no, status=no, location=no, resizable=no, width=640, height=600');
                    // ' <div class="close_div" style="position: absolute;top: -9px;right: -10px;background-color: #ffffff;"> <a href="#">Close</a> </div> '+
                    /*var new_block =
                        '<div id="batch_link" style="position: relative; padding-left: 10px;">' +

                            '<span style="font-size: 18px;">New batch was generated. </span>'+'<br/>'+
                            '<a class="download" href="'+data.urlDocument+'" > Download batch now</a>'+'<br/>'+
                            '<a class="testlink" target="_newtab" href="'+data.urlReport+'" >Open report to review in new tab</a> <br/>'+
                         '</div><br/><br/>';

                    $('.sidebar_right').append(new_block);*/

                    //$('#batch_export_result_box span').html(new_block);

                    //show_modal_box('#batch_export_result_box',300);

                    /*$('.testlink').bind('click', function (){
                        console.log("click event");
                    });

                    var a = $('.testlink')[0];
                    a.setAttribute('href', data.urlReport);
                    a.setAttribute('target', '_blank');
                    a.click();*/
                    //window.open(data.urlReport,'_blank' );

                    /*setTimeout(function () {
                        //window.open(data.urlReport, '_newtab');
                        //uncomment window.open(data.urlReport, '_blank', 'toolbar=no, menubar=no, status=no, location=no, resizable=no, width=640, height=600');
                    },1000)
*/

                    self.listClass.updateList();

                } else {
                    show_alert("Batch wasn't generated!", 400);
                    $("#progressbar_jui").fadeOut(500);
                }
            }
        });
        self.PB.done();
        //$("#progressbar_jui").fadeOut(500);
    }
}