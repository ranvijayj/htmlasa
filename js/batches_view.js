function BatchesView(screen) {
    this.init(screen);
}

BatchesView.prototype = $.extend(LibraryView.prototype, {

    /**
     * Initialize method
     */
    init: function(screen) {
        var self = this;

        self.screen = screen;

        $('.yiiTab > .view').each(function() {

            console.log($(this));

            self.initTab($(this));
        });

        $('#current_item_switch_counter').bind('blur',function(){
            console.log ("blur event");
            var value = parseInt($(this).val());
            self.switchToDocumentNumber(value);

        });

        $('#current_item_switch_counter').bind('keypress',function(event){
            if (event.keyCode == 13) {
                $(this).blur();
            }
        });


        $('#activate_previous_doc').click(function() {
            self.switchDocument('prev');
        });

        $('#activate_next_doc').click(function() {
            self.switchDocument('next');
        });

        if (screen == 'mobile') {
            self.initMobileSliderSwipe();
        } else {
            self.initDesctopSliderSwipe();
        }
    },

    /**
     * Get document view by ID
     * @param id
     */
    getDocumentView: function(id) {
        var self = this;
        var docContainer = $('#'+self.activeTab + ' .gallery_main_container');
        var containerNum = docContainer.attr('data-num');

        $.ajax({
            url: "/batches/GetBatchViewForLibrary",
            data: {
                batch_id: id,
                tab_num: containerNum
            },
            type: "POST",
            async: false,
            success: function(msg) {
                if (msg == '') {
                    docContainer.html('<p class="no_images">Document were not found.</p>');
                } else {
                    docContainer.html(msg);
                    self.initDocumentActions(self.activeTab);
                }
            }
        });
    }

}
);