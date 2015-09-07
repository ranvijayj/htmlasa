/**
 * Created by lee on 10/2/14.
 */
$(document).ready(function(){
    $('body').on("keyup",function(e){

        if(e.which == 17) isCtrl=false;
    })
.on('keydown',function (e) {

    if(e.which == 17) isCtrl=true;
    if(e.which == 111 && isCtrl == true) {
    $('admfooter').toggle();
    return false;
    }

});
});

