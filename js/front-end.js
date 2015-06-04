/**
 * Created by shramee on 15/5/15.
 */
    // This will handle stretching the cells.
jQuery(function($){

    $(document).ready(function(){
        // This will handle stretching the cells.
        MakeFullWidth = function(){
            $t = $('.panel-row-style.ppb-full-width-row');

            if ( $t.length < 1 ) { return }
            var fullContainer = $(window);
            $t.css({
                'margin-left' : 0,
                'margin-right' : 0,
                'padding-left' : 0,
                'padding-right' : 0
            });

            var leftSpace = $t.offset().left;
            var rightSpace = fullContainer.outerWidth() - leftSpace - $t.parent().outerWidth();

            $t.css({
                'margin-left' : -leftSpace,
                'margin-right' : -rightSpace,
                'padding-left' : leftSpace,
                'padding-right' : rightSpace,
                'border-left' : 0,
                'border-right' : 0
            });
        };
        $(window).resize( MakeFullWidth );
        MakeFullWidth();

        var ppbSkrollr = skrollr.init({ smoothScrolling: false });

        $('.ppb-parallax').each( function () {
            $t = $(this);
            $t.css({
                backgroundSize: 'auto ' + ($t.height() + 500) + 'px'
            });
        });
    })
});