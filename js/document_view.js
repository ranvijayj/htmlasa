function DocumentView(document_view_block, block_to_move, file_view_block, document_view_block_height, bottom_bar_height,  z_index) {
    var self = this;
    self.init(document_view_block, block_to_move, file_view_block, document_view_block_height, bottom_bar_height,  z_index);
}

DocumentView.prototype = {
    /**
     * Document size parameters
     */
    lastSize: 1,
    docWidth: 0,
    docHeight: 0,
    fullSize: false,

    /**
     * Last cursor position
     */
    top: 0,
    left: 0,

    /**
     * Scrolling timeout
     */
    timeout: 0,

    /**
     * If mouse is down on document
     */
    isMouseDown: false,

    /**
     * If mouse is over the document
     */
    overDocument: false,

    /**
     * Window params
     */
    widowWidth: $(window).width(),
    windowHeight: $(window).height(),

    /**
     * Initialize method
     */
    init: function(document_view_block, block_to_move, file_view_block, document_view_block_height, bottom_bar_height,  z_index) {

        var self = this;

        $("#lean_overlay").keydown(function(event) {
            alert("Keydown");
            if ((event.keyCode == 27 || event.charCode == 27 || event.which == 27)) {alert ("ESC KEY");}
        });


        // change document size
        $(block_to_move + ' .pluslup_button').click(function() {
             self.resizeImage($(this), true);
            //embed.width(width);
            //embed.height(height);
        });

        // change document size
        $(block_to_move + ' .minlup_button').click(function() {
            self.resizeImage($(this), false);
        });

        // change document size
        $(block_to_move + ' .fullsize_button').click(function() {
            if (self.fullSize) {
                var block = $(block_to_move);
                block.appendTo(document_view_block);
                block.css('position', 'relative');
                block.css('z-index', z_index);
                block.css('height', '100%');
                $(file_view_block).css('height', document_view_block_height + 'px');
                $("html, body").css('overflow', 'auto');
                self.fullSize = false;
            } else {
                var block = $(block_to_move);
                block.prependTo('body');
                block.css('position', 'absolute');
                block.css('z-index', '100000');
                block.css('background-color', '#fff');
                var height = $(window).height();
                block.css('height', height + 'px');
                $("html, body").animate({scrollTop:0}, 0);
                $("html, body").css('overflow', 'hidden');
                $(file_view_block).css('height', (height-bottom_bar_height) + 'px');
                self.fullSize = true;
            }
        });

        $(document).keydown(function(event) {
            if ((event.keyCode == 27 || event.charCode == 27 || event.which == 27) && self.fullSize) {
                var block = $(block_to_move);
                block.appendTo(document_view_block);
                block.css('position', 'relative');
                block.css('z-index', z_index);
                block.css('height', '100%');
                $(file_view_block).css('height', document_view_block_height + 'px');
                $("html, body").css('overflow', 'auto');
                self.fullSize = false;
            }
        });

        $(file_view_block + ' img.documet_file').mousedown(function(event) {
            event.stopPropagation();
            self.isMouseDown = true;
            self.top = event.pageY;
            self.left = event.pageX;
            $(this).parent().css('cursor', 'move');
            event.preventDefault();
            return false;
        });

        $(file_view_block + ' img.documet_file').parent().mousedown(function(event) {
            event.stopPropagation();
            self.isMouseDown = true;
            self.top = event.pageY;
            self.left = event.pageX;
            $(this).css('cursor', 'move');
        });

        $('body').mouseup(function(){
            self.isMouseDown = false;
            $(file_view_block).css('cursor', 'default');
        });

        $(file_view_block + ' img.documet_file').parent().mousemove(function(event) {
            event.stopImmediatePropagation();
            event.preventDefault();
            if (self.isMouseDown) {
                var scrollTop = $(this).scrollTop();
                var scrollLeft = $(this).scrollLeft();

                scrollTop -= (event.pageY - self.top)*2;
                scrollLeft -= (event.pageX - self.left)*2;

                scrollTop = (scrollTop < 0) ? 0 : scrollTop;
                scrollLeft = (scrollLeft < 0) ? 0 : scrollLeft;

                $(this).scrollTop(scrollTop);
                $(this).scrollLeft(scrollLeft);

                self.top = event.pageY;
                self.left = event.pageX;
            }
        });

        $(file_view_block + ' .documet_file').parent().mouseenter(function() {
            self.overDocument = true;
        });

        $(file_view_block + ' .documet_file').parent().mouseleave(function() {
            self.overDocument = false;
        });

        $(file_view_block + ' img.documet_file').parent().mousewheel(function(event, delta) {
            if (event.ctrlKey) {
                event.stopPropagation();
                event.preventDefault();
                if (delta > 0) {
                    self.resizeImage($(block_to_move + ' .pluslup_button'), true);
                } else {
                    self.resizeImage($(block_to_move + ' .minlup_button'), false);
                }
            }
        });

        $(window).resize(function(event) {
            /*
            if (self.overDocument) {

            }

            if (self.overDocument === false) {
               alert(self.overDocument);
                self.widowWidth = $(window).width();
               alert(self.widowWidth);
                self.windowHeight = $(window).height();
            }            */
        });

        $('#progress_bar').unbind('click');
        $('#progress_bar').on('click',function (event) {
            event.preventDefault();

            var docId = $(this).data('id')

            if (docId) {
                $.ajax({
                    url: "/documents/ViewAudits",
                    data: {
                        docId: docId ,
                        audit_mode: 'Approved'
                    },
                    type: "POST",
                    success: function(data) {
                        $('#audit_view_block_detail').html(data);
                        $('#audit_checkbox').removeAttr('checked');
                        show_modal_box('#audit_view_block',505)
                    }
                });
            }

        });



    },

    /**
     * Resize image
     */
    resizeImage: function(button, direction) {
        var self = this;
        if (direction) {
            self.lastSize = self.lastSize + 0.1;
            var embed = button.parent().parent().parent().find('.documet_file');
            if (self.docWidth == 0 || self.docHeight == 0) {
                self.docWidth = embed.width();
                self.docHeight = embed.height();
            }
            var width = parseInt(self.lastSize*self.docWidth);
            var height = parseInt(self.lastSize*self.docHeight);
            embed.stop().animate({width: width+ 'px', height: height+ 'px'}, 150);
        } else {
            if (self.lastSize > 0.5) {
                self.lastSize = self.lastSize - 0.1;
                var embed = button.parent().parent().parent().find('.documet_file');
                if (self.docWidth == 0 || self.docHeight == 0) {
                    self.docWidth = embed.width();
                    self.docHeight = embed.height();
                }
                var width = parseInt(self.lastSize*self.docWidth);
                var height = parseInt(self.lastSize*self.docHeight);
                embed.stop().animate({width: width+ 'px', height: height+ 'px'}, 150);
                //embed.width(width);
                //embed.height(height);
            }
        }
    }
}