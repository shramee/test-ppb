(function ($) {

    window.setPageBuilderUploadButton = function () {

        $('#grid-styles-dialog .upload-button').click(function () {

            var $textField = $(this).parent().find('input');
            var textFieldID = $textField.attr('id');

            window.formfield = textFieldID;

            window.send_to_editor = function (html) {

                if (formfield) {

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
//                    var document = /(^.*\.pdf|doc|docx|ppt|pptx|odt*)/gi;
//                    var audio = /(^.*\.mp3|m4a|ogg|wav*)/gi;
//                    var video = /(^.*\.mp4|m4v|mov|wmv|avi|mpg|ogv|3gp|3g2*)/gi;

                    if (itemurl.match(image)) {
                        //btnContent = '<img src="'+itemurl+'" alt="" /><a href="#" class="mlu_remove button">Remove Image</a>';
                    } else {

                        // No output preview if it's not an image.
                        // btnContent = '';

                        // Standard generic output if it's not an image.
//                        html = '<a href="'+itemurl+'" target="_blank" rel="external">View File</a>';
//
//                        btnContent = '<div class="no_image"><span class="file_link">'+html+'</span><a href="#" class="mlu_remove button">Remove</a></div>';
                    }

                    $( '#' + formfield).val(itemurl);
//                    $( '#' + formfield).siblings( '.screenshot').slideDown().html(btnContent);
                    tb_remove();

                } else {
                    window.original_send_to_editor(html);
                }

                // Clear the formfield value so the other media library popups can work as they are meant to. - 2010-11-11.
                formfield = '';

            };
            tb_show('', 'media-upload.php?post_id=0&amp;type=image&amp;TB_iframe=true');
            return false;
        });
    };

})(jQuery);
