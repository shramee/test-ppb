/**
 * Created by shramee on 15/5/15.
 */
    // This will handle stretching the cells.
jQuery(function($){

    // This will handle stretching the cells.
        $t = $('.panel-row-style.full-width-row');
        var fullContainer = $(window);

        var onResize = function(){
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
                'padding-right' : rightSpace
            });
        };

        $(window).resize( onResize );
        onResize();

        $t.css({
            'border-left' : 0,
            'border-right' : 0
        });

});