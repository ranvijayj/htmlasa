/**
 * Created by lee on 8/29/14.
 */

function ProgressBar(mode) {
    var self = this;
    this.init(mode);

}

ProgressBar.prototype = {
    progressbar:false,
    max: false,
    interval:false,
    global_caption:false,


    init: function(mode) {
        var self = this;
        self.progressbar=$( "#progressbar_jui" );
        self.progressbar.progressbar({value: false});

        if (mode=="upload") {
            $(".progress-label").text("Starting uploading");
            $("#progress_bar_container").show();

        } else if (mode=='savefile'){

            $(".progress-label").text("Starting saving file");
            $(".canceltext").hide();
            $("#progress_bar_container").show();

        } else if (mode=='vendors_copy'){
            self.global_caption="Copying vendors...";
            $(".progress-label").text("Copying vendors...");
            $(".canceltext").hide();
            $("#progress_bar_container").show();
        } else if (mode=='vendors_import'){
            self.global_caption="Importing vendors...";
            $(".progress-label").text("Importing vendors...");
            $(".canceltext").hide();
            $("#progress_bar_container").show();
        } else if (mode=='coa_import'){
            self.global_caption="Importing coas...";
            $(".progress-label").text("Importing coas...");
            $(".canceltext").hide();
            $("#progress_bar_container").show();

        } else if (mode=='ap_approve'){
            self.global_caption="Approving items...";
            $(".progress-label").text("Approving items...");

            $(".canceltext").hide();
            $("#progress_bar_container").show();
        } else if (mode=='doc_search'){
                $(".progress-label").text("Searching...");
                $(".canceltext").hide();
                $("#progress_bar_container").show();
        } else {


            $(".progress-label").text("Document creating..");
        }



        $('#save_ap').click(function() {

            self.setCaption("Document creation");

            self.done();
            $('#ap_creating_form').submit();
        });


    },

    setCaption: function(text){
        $(".progress-label").text(text);
        $("#progress_bar_container").show();
    },

    stepTo:function(percent) {
        var self = this;
        var progressTimer = setTimeout( progress, 10 );

        function progress() {
            var val = self.progressbar.progressbar( "value" ) || 0;
            self.progressbar.progressbar( "value", val + Math.floor( Math.random() * 3 ) );
            //self.progressbar.progressbar( "value", val);
            if ( val < percent ) {
                progressTimer = setTimeout( progress, 10 );
            } else if (val==100 || val==percent) {
                return true;
            }
        }
    },

    setValue:function(percent) {
        var self = this;
        self.progressbar.progressbar( "value", percent);
    },


    done:function() {
        var self = this;
        var progressTimer = setTimeout( progress, 10 );
        var d = $.Deferred();

        function progress() {
            var val = self.progressbar.progressbar( "value" );
            self.progressbar.progressbar( "value", val + Math.floor( Math.random() * 3 ) );
            if ( val <= 99 ) {
                progressTimer = setTimeout( progress, 10 );

            } else  {

                d.resolve();
                self.hide_progress();
                //return true;
                return d;

            }

        }

    },

    doneQuick:function() {
        var self = this;
        var progressTimer = setTimeout( progress, 1 );
        var d = $.Deferred();

        function progress() {
            var val = self.progressbar.progressbar( "value" );
            self.progressbar.progressbar( "value", val + Math.floor( Math.random() * 3 ) );
            if ( val <= 99 ) {
                progressTimer = setTimeout( progress, 1 );

            } else  {

                d.resolve();
                self.hide_progress();
                //return true;
                return d;

            }

        }

    },

    hide_progress: function(){
        $("#progress_bar_container").hide();
    },

    indeterminate: function(){
        var self = this;
        //$("#progress_bar_container").hide();
        progressbarValue = self.progressbar.find( ".ui-progressbar-value" );
        progressbarValue.css({
            "background": '#d9ebf7'
        });
        self.progressbar.progressbar( "option", "value", false );
    },


    startListen: function () {
        var self = this;
        var pb_interval= setInterval(function() {
            //self.stepTo(5);
            self.executeProgressListening(pb_interval);
        },2000);
    },

    executeProgressListening: function (pb_interval) {
        var self = this;
        $.ajax({
            url: "/site/progressbar",
            type: "POST",
            async:true,
            dataType: 'json',

            success: function(data) {
                if (data.success) {

                    if ( data.progress <= 99 ) {
                        self.stepTo(data.progress);

                        $(".progress-label").text(self.global_caption+" "+data.progress+"%");


                    } else {
                        self.done();
                        clearInterval(pb_interval);
                    }

                }
            }
        });



    }





/*  test: function() {

        var progressbar = $('#progressbar'),
        max = progressbar.attr('max'),
        time = (1000/max)*5,
        value = progressbar.val();


        var loading = function() {
            value += 1;
            addValue = progressbar.val(value);

            $('.progress-value').html(value + '%');

            if (value == max) {
                clearInterval(animate);
            }
        };


        var animate = setInterval(function() {
        file_upload_progress();
        loading();
      }, time);
        $('#left_column').disable;
        //alert("Inside test after All");


    },

    test3: function() {
        $('#progress_bar_container').show();
        var progressbar = $('#progressbar'),
            max = 100
            t = (1000/max)*5,
            val = 0;


        var loading = function() {
            val += 20;
            $( "#progressbar" ).progressbar({
                value:val
            });



            if (val == max) {
                clearInterval(animate);
            }
        };


        var animate = setInterval(function() {
           alert("inside interval");
            loading();
        }, t);



    },

    test2: function(){


        var v = 50;

        $( "#progressbar_j" ).progressbar({
            value:v
        });
        /*var animate = setInterval(function() {
            v=v+25;
            $( "#progressbar_j" ).progressbar({
                value:v
            });
            if(v>100) {clearInterval(animate);}
        }, 1000);

    },

    file_upload_progress : function() {

        $.ajax({
            url: 'uploads/uploadstatus',
            dataType: 'json',
            success: function(data){
                if(data.percent) {

                    self.loading_func(data.percent);
                    /*$("#bar").progressbar({
                        value: Math.ceil(data.percent) // Заполняем прогресс бар
                    });
                    $('.ui-progressbar-value').text(data.percent+'%'); // Отображаем на прогресс баре процент загрузки



                }
            }
        });
    },

    loading_func : function(persent) {
            //time = (1000/max)*5,
            //value = self.progressbar.val();
            //value += 1;

        addValue = self.progressbar.val(value);

        $('.progress-value').html(value + '%');

        if (value == self.max) {
            clearInterval(animate);
        }

    }*/

}


