(function ($) {

    window.setRowOptionUploadButton = function () {

        $('#grid-styles-dialog .upload-button').click(function () {

            var $textField = $(this).parent().find('input');
            var field = $textField.attr('data-style-field');

            var $field = $('#grid-styles-dialog input[data-style-field="' + field + '"]');

            window.$formField = $field;

            window.send_to_editor = function (html) {

                if ($formField) {

                    // itemurl = $(html).attr( 'href' ); // Use the URL to the main image.

                    if ( $(html).html(html).find( 'img').length > 0 ) {

                        itemurl = $(html).html(html).find( 'img').attr( 'src' ); // Use the URL to the size selected.

                    } else {

                        // It's not an image. Get the URL to the file instead.

                        var htmlBits = html.split( "'" ); // jQuery seems to strip out XHTML when assigning the string to an object. Use alternate method.

                        itemurl = htmlBits[1]; // Use the URL to the file.

                        var itemtitle = htmlBits[2];

                        itemtitle = itemtitle.replace( '>', '' );
                        itemtitle = itemtitle.replace( '</a>', '' );

                    } // End IF Statement

                    var image = /(^.*\.jpg|jpeg|png|gif|ico*)/gi;

                    $formField.val(itemurl);

                    tb_remove();

                } else {
                    window.original_send_to_editor(html);
                }

                // Clear the formfield value so the other media library popups can work as they are meant to. - 2010-11-11.
                $formField = null;

            };
            tb_show('', 'media-upload.php?post_id=0&amp;type=image&amp;TB_iframe=true');
            return false;
        });
    };

})(jQuery);
