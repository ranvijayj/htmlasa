(function($) {
    /**
     * Tooltip timeout to show
     * @type {boolean}
     */
    var tooltipTimeoutIn = false;

    /**
     * Tooltip timeout to hide
     * @type {boolean}
     */
    var tooltipTimeoutOut = false;

    /**
     * Perform tooltip on selected elements.
     *
     * @param {string} selector the inner selector of elements. Inner elements not referred to by this
     *      selector are left untouched.
     * @return {jQuery} the current jQuery object for chaining purposes.
     * @this {jQuery} the current jQuery object.
     */
    $.fn.ellipsisTooltip = function(selector) {
        var subjectElements, settings;

        subjectElements = $(this);

        // Do ellipsis on each subject element.
        subjectElements.each(function() {
            var elem = $(this);
            elem.hover(function() {
                showTooltip($(this));
            }, function() {
                hideTooltip();
            });
        });

        // Return jQuery object for chaining.
        return this;
    };

    /**
     * Show tooltip
     * @param elem
     */
    function showTooltip(elem) {
        var text = elem.attr('data');
        clearTimeout(tooltipTimeoutIn);
        clearTimeout(tooltipTimeoutOut);
        $('#tooltip').hide();
        tooltipTimeoutIn = setTimeout(function() {

        }, 200);
    }

    /**
     * Hide tooltip
     */
    function hideTooltip() {
        tooltipTimeoutOut = setTimeout(function() {
            $('#tooltip').fadeOut()
        }, 200);
    }
})(jQuery);