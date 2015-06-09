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

            var image_url = $t.css('background-image'),
                image;

            // Remove url() or in case of Chrome url("")
            image_url = image_url.match(/^url\("?(.+?)"?\)$/);

            if (image_url[1]) {
                image_url = image_url[1];
                image = new Image();

                // just in case it is not already loaded
                $(image).load(function () {
                    var ratio = image.width / image.height,
                        minHi = $t.height() + 500;

                    if ( ( minHi * ratio ) > $t.outerWidth() ) {
                        $t.css({
                            backgroundSize: 'auto ' + (minHi) + 'px'
                        });
                    } else {
                        $t.css({
                            backgroundSize: '100% auto'
                        });
                    }

                });

                image.src = image_url;
            }
        });
    })
});