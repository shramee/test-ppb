/**
 * Handles row styling.
 *
 * @copyright Greg Priday 2014
 * @license GPL 2.0 http://www.gnu.org/licenses/gpl-2.0.html
 */

jQuery( function($){
    // Create the dialog for setting up the style
    var buttons = {};
    buttons[panels.i10n.buttons.done] = function () {
        $( '#grid-styles-dialog' ).dialog( 'close' );
    };


    $gridStylesDialog = $( '#grid-styles-dialog');
    $gridStylesDialog.data('html', $( '#grid-styles-dialog').html() );
    $gridStylesDialog
        .show()
        .dialog( {
            dialogClass: 'panels-admin-dialog',
            autoOpen: false,
            modal: false, // Disable modal so we don't mess with media editor. We'll create our own overlay.
            draggable:   false,
            resizable:   false,
            title:   $( '#grid-styles-dialog' ).attr( 'data-title' ),
            maxHeight:   Math.round($(window).height() * 0.8),
            width: 500,
            open:    function () {
                $t = $(this);
                var overlay = $('<div class="siteorigin-panels ui-widget-overlay ui-widget-overlay ui-front"></div>').css('z-index', 80001);
                $t.data('overlay', overlay).closest('.ui-dialog').before(overlay);

                window.setRowOptionUploadButton();
                panels.rowVisualFields();
                var $bgImageFld = $t.find('[data-style-field=background_image]');
                $bgImageFld.on( 'change', panels.rowVisualFields );

            },
            close : function(){
                $(this).data('overlay').remove();

                var $bgImageFld = $t.find('[data-style-field=background_image]');
                $bgImageFld.off( 'change', panels.rowVisualFields );

                // Copy the dialog values back to the container style value fields
                var container = $( '#grid-styles-dialog').data('container');
                $( '#grid-styles-dialog [data-style-field]').each(function() {
                    var $$ = $(this);
                    var cf = container.find( '[data-style-field="' + $$.data('style-field') + '"]' );

                    switch($$.data('style-field-type')) {
                        case 'checkbox':
                            cf.val( $$.is(':checked') ? 'true' : '' );
                            break;
                        default :
                            cf.val( $$.val() );
                            break;
                    }
                });
            },
            buttons: buttons
        })
    ;

    panels.loadStyleValues = function(container){
        $( '#grid-styles-dialog')
            .data('container', container)
            .html( $( '#grid-styles-dialog').data('html') );

        // Copy the values of the hidden fields in the container over to the dialog.
        container.find("[data-style-field]").each(function(){
            var $$ = $(this);

            // Save the dialog field
            var df = $( '#grid-styles-dialog [data-style-field="' + $$.data('style-field') + '"]' );
            switch( df.data('style-field-type') ) {
                case 'checkbox':
                    df.attr('checked', $$.val() ? true : false);
                    break;
                default :
                    df.val( $$.val() );
                    break;
            }
        });

        $( '#grid-styles-dialog').dialog('open');

        // Now set up all the fields
        $( '#grid-styles-dialog [data-style-field-type="color"]')
            .wpColorPicker()
            .closest('p').find('a').click(function(){
                $( '#grid-styles-dialog').dialog("option", "position", "center");
            });
    }

    panels.rowVisualFields = function(){

        $t = $( '#grid-styles-dialog');

        var $bgImageFld = $t.find('[data-style-field=background_image]'),
            $bgImageOptions = $t.find('.field_background_image_repeat, .field_background_image_size');
        console.log($.trim($bgImageFld.val()));

        if ( '' == $.trim( $bgImageFld.val() ) ) {
            $bgImageOptions.hide();
        } else {
            $bgImageOptions.show();
        }
    };

    panels.rowVisualStylesInit
} );