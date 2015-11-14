( function ($) {
    // plugin definition
    $.fn.hilight = function ( options ) {
        debug ( this );

        // build main options before element iteration
        var opts = $.extend ( {}, $.fn.hilight.defaults, options );

        // iterate and reformat each matched element
        return this.each ( function () {

            // build element specific options
            var o = $.meta ? $.extend({}, opts, $(this).data()) : opts;

            // update element styles
            $(this).css({
                backgroundColor: o.background,
                color:           o.foreground
            });
            $(this).html ( $.fn.hilight.format ( $(this).html ( ) ) );
        });
    };

    // private function for debugging
    function debug($obj) {
        if ( window.console && window.console.log ) {
            window.console.log ( 'hilight selection count: ' + $obj.size ( ) );
        }
    }

    // define and expose our format function
    $.fn.hilight.format = function(txt) {
        return '<strong>' + txt + '</strong>';
    };

    // plugin defaults
    $.fn.hilight.defaults = {
        foreground: 'red',
        background: 'yellow'
    };
})(jQuery);