function ImageView() {
    var self = this;
    self.init();
}

ImageView.prototype = {
    /**
     * File to review with additional fields
     */
    fileToReview: 0,

    /**
     * Initialize method
     */
    init: function() {
        var self = this;
        setInterval(function() {
            $('.image_view').click(function() {
                var docId = $(this).attr('data');
                self.showImageView(docId)
            });
            $('.image_view').removeClass('image_view').addClass('image_view_touched');
        }, 2000)
    },


    /**
     * Initialize details block
     */
    initImageViewBlock: function() {
        var image_view = new DocumentView('#image_view_block', '#file_detail_block_conteiner', '#file_detail_block', 525, 76, 1000);
    },

    /**
     * Show block with file and additional fields
     */
    showImageView: function(docId) {
        var self = this;
        if (docId != 0) {
            $.ajax({
                url: "/admin/default/getimageviewblock",
                data: {docId: docId},
                async: false,
                type: "POST",
                success: function(msg) {
                    if (msg) {
                        $('#image_view_block').html(msg);
                        show_modal_box('#image_view_block', 725, 20);
                        self.initImageViewBlock();
                    }
                }
            });
        }
    }
}