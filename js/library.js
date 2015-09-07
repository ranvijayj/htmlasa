function Library() {
    this.init();
    this.initLibrary();
}

Library.prototype = $.extend(LibraryTree.prototype, {
    /**
     * Initialize library
     */
    initLibrary: function() {

    }
});