/**
 * Initial setup for the panels interface
 *
 * @copyright Greg Priday 2013
 * @license GPL 2.0 http://www.gnu.org/licenses/gpl-2.0.html
 */

jQuery( function ( $ ) {
    panels.animations = $('#panels').data('animations');

    $( window ).bind( 'resize', function ( event ) {
        // ui-resizable elements trigger resize
        if ( $( event.target ).hasClass( 'ui-resizable' ) ) return;
        
        // Resize all the grid containers
        $( '#panels-container .grid-container' ).panelsResizeCells();
    } );

    // Create a sortable for the grids
    $( '#panels-container' ).sortable( {
        items:    '> .grid-container',
        handle:   '.grid-handle',
        tolerance:'pointer',
        stop:     function () {
            $( this ).find( '.cell' ).each( function () {
                // Store which grid this is in by finding the index of the closest .grid-container
                $( this ).find( 'input[name$="[grid]"]' ).val( $( '#panels-container .grid-container' ).index( $( this ).closest( '.grid-container' ) ) );
            } );

            $( '#panels-container .grid-container' ).trigger( 'refreshcells' );
        }
    } );

    // Create the add grid dialog
    var gridAddDialogButtons = {};
    gridAddDialogButtons[panels.i10n.buttons.add] = function () {
        var num = Number( $( '#grid-add-dialog' ).find( 'input' ).val() );

        if ( isNaN( num ) ) {
            alert( 'Invalid Number' );
            return false;
        }

        // Make sure the number is between 1 and 10.
        num = Math.min( 10, Math.max( 1, Math.round( num ) ) );
        var gridContainer = window.panels.createGrid( num );

        if(panels.animations) gridContainer.hide().slideDown();
        else gridContainer.show();

        $( '#grid-add-dialog' ).dialog( 'close' );
    };

    window.pootlePagePageSettingUploadButton = function () {

        $('#page-setting-dialog .upload-button').click(function () {

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

                    if (itemurl.match(image)) {
                        //btnContent = '<img src="'+itemurl+'" alt="" /><a href="#" class="mlu_remove button">Remove Image</a>';
                    } else {
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
            tb_show('', 'media-upload.php?post_id=0&amp;title=Background%20Image&amp;type=image&amp;TB_iframe=true');
            return false;
        });
    };

    $( '#page-setting-dialog').data('html', $( '#page-setting-dialog').html() );

    $('#page-setting-dialog').dialog({
        dialogClass: 'panels-admin-dialog',
        modal: false,
        autoOpen: false,
        width: 500,
        maxHeight:   Math.round($(window).height() * 0.8),
        draggable:   false,
        resizable:   false,
        title: $('#page-setting-dialog').attr('data-title'),
        open:    function () {
            var overlay = $('<div class="siteorigin-panels ui-widget-overlay ui-widget-overlay ui-front"></div>').css('z-index', 80001);
            $(this).data('overlay', overlay).closest('.ui-dialog').before(overlay);

            window.pootlePagePageSettingUploadButton();

            var fieldValues = JSON.parse($('#page-settings').val());
            if (typeof fieldValues != 'undefined' || fieldValues != null) {

                for (var fieldName in fieldValues) {

                    // Save the dialog field
                    var df = $( '#page-setting-dialog [data-style-field="' + fieldName + '"]' );
                    switch( df.data('style-field-type') ) {
                        case 'checkbox':
                            df.attr('checked', fieldValues[fieldName]);
                            break;
                        case 'color':
                            df.wpColorPicker('color', fieldValues[fieldName]);
                            break;
                        default :
                            df.val(fieldValues[fieldName]);
                            break;
                    }
                }
            }

        },
        close : function(){
            $(this).data('overlay').remove();

            // Copy the dialog values back to the hidden value
            var fieldValues = {};
            $( '#page-setting-dialog [data-style-field]').each(function() {
                var $$ = $(this);
                var fieldName = $$.data('style-field');

                switch($$.data('style-field-type')) {
                    case 'checkbox':
                        fieldValues[fieldName] = $$.is(':checked');
                        break;
                    default :
                        fieldValues[fieldName] = $$.val();
                        break;
                }
            });

            $('#page-settings').val(JSON.stringify(fieldValues));
        },
        buttons: {
            'Done': function () {
                $('#page-setting-dialog').dialog('close');
            }
        }
    });

    $( '#page-setting-dialog [data-style-field-type="color"]')
        .wpColorPicker()
        .closest('p').find('a').click(function(){
            $( '#page-setting-dialog').dialog("option", "position", "center");
        });

    // The done button
    var dialogButtons = {};
    var doneClicked = false;
    dialogButtons[ panels.i10n.buttons['done'] ] = function () {
        doneClicked = true;

        // Change the title of the panel
        $('#widget-styles-dialog').dialog( 'close' );
    };

    // Create a dialog for this form
    $('#widget-styles-dialog')
        .dialog( {
            dialogClass: 'panels-admin-dialog',
            autoOpen:    false,
            modal:       false, // Disable modal so we don't mess with media editor. We'll create our own overlay.
            draggable:   false,
            resizable:   false,
            title:       panels.i10n.messages.styleWidget,
            minWidth:    500,
            maxHeight:   Math.min( Math.round($(window).height() * 0.875), 800),
//            create:      function(event, ui){
//                $(this ).closest('.ui-dialog' ).find('.show-in-panels' ).show();
//            },
            open:        function () {
                // This fixes the A element focus issue
                $(this ).closest('.ui-dialog' ).find('a' ).blur();

                var overlay = $('<div class="siteorigin-panels-ui-widget-overlay ui-widget-overlay ui-front"></div>').css('z-index', 80001);
                $(this).data( 'overlay', overlay ).closest( '.ui-dialog' ).before( overlay );

                var $hidden = window.$currentPanel.find('input[name$="[style]"]');
                var json = $hidden.val();
                var styleData = JSON.parse(json);

                // from style data in hidden field, set the widget style dialog fields with data
                for (var key in styleData) {
                    if (styleData.hasOwnProperty(key)) {

                        var $field = $(this).find('input[dialog-field="' + key + '"]');
                        if ($field.attr('data-style-field-type') == "color") {
                            $field.wpColorPicker('color', styleData[key]);
                        } else {
                            $field.val(styleData[key]);
                        }

                    }
                }
            },
            close: function(){
                var $currentPanel = window.$currentPanel;
                if(!doneClicked) {
                    $( this ).trigger( 'panelsdone', $currentPanel,  $('#widget-styles-dialog') );
                }

                //
                // from values in dialog fields, set style data into hidden fields
                //

                var styleData = {};
                $(this).find('input[dialog-field]').each(function () {
                    var key = $(this).attr('dialog-field');
                    styleData[key] = $(this).val();
                });

//                        var backgroundColor = $(this).find('.widget-bg-color').val();
//                        var borderWidth = $(this).find('.widget-border-width').val();
//                        var borderColor = $(this).find('.widget-border-color').val();
//                        var paddingTop = $(this).find('.widget-padding-top').val();
//                        var paddingBottom = $(this).find('.widget-padding-bottom').val();
//                        var paddingLeft = $(this).find('.widget-padding-left').val();
//                        var paddingRight = $(this).find('.widget-padding-right').val();
//                        var marginTop = $(this).find('.widget-margin-top').val();
//                        var marginBottom = $(this).find('.widget-margin-bottom').val();
//                        var marginLeft = $(this).find('.widget-margin-left').val();
//                        var marginRight = $(this).find('.widget-margin-right').val();
//                        var borderRadius = $(this).find('.widget-rounded-corners').val();

//                        var styleData = {
//                            backgroundColor: backgroundColor,
//                            borderWidth: borderWidth,
//                            borderColor: borderColor,
//                            paddingTop: paddingTop,
//                            paddingBottom: paddingBottom,
//                            paddingLeft: paddingLeft,
//                            paddingRight: paddingRight,
//                            marginTop: marginTop,
//                            marginBottom: marginBottom,
//                            marginLeft: marginLeft,
//                            marginRight: marginRight,
//                            borderRadius: borderRadius
//                        };
                $currentPanel.find('input[name$="[style]"]').val(JSON.stringify(styleData));

                var allData = JSON.parse($currentPanel.find('input[name$="[data]"]').val());
                if (typeof allData.info == 'undefined') {
                    allData.info = {};
                }

                allData.info.raw = $currentPanel.find( 'input[name$="[info][raw]"]' ).val();
                allData.info.grid = $currentPanel.find( 'input[name$="[info][grid]"]' ).val();
                allData.info.cell = $currentPanel.find( 'input[name$="[info][cell]"]' ).val();
                allData.info.id = $currentPanel.find( 'input[name$="[info][id]"]' ).val();
                allData.info.class = $currentPanel.find( 'input[name$="[info][class]"]' ).val();

                allData.info.style = styleData;
                $currentPanel.find('input[name$="[data]"]').val(JSON.stringify(allData));

                // Destroy the dialog and remove it
                $(this).data('overlay').remove();
                activeDialog = undefined;
            },
            buttons: dialogButtons
        } )
        .keypress(function(e) {
            if (e.keyCode == $.ui.keyCode.ENTER) {
                if($(this ).closest('.ui-dialog' ).find('textarea:focus' ).length > 0) return;

                // This is the same as clicking the add button
                $(this ).closest('.ui-dialog').find('.ui-dialog-buttonpane .ui-button:eq(0)').click();
                e.preventDefault();
                return false;
            }
            else if (e.keyCode === $.ui.keyCode.ESCAPE) {
                $(this ).closest('.ui-dialog' ).dialog('close');
            }
        });

    $( '#widget-styles-dialog [data-style-field-type="color"]')
        .wpColorPicker()
        .closest('p').find('a').click(function(){
            $( '#widget-styles-dialog').dialog("option", "position", "center");
        });
//    $(this).find('[data-style-field-type="color"]')
//        .wpColorPicker()
//        .closest('p').find('a').click(function(){
//            $( '#widget-styles-dialog').dialog("option", "position", "center");
//        });


    //
    // Hide Element Dialog
    //
    $('#hide-element-dialog').dialog({
        dialogClass: 'panels-admin-dialog',
        modal: false,
        autoOpen: false,
        width: 500,
        maxHeight:   Math.round($(window).height() * 0.8),
        draggable:   false,
        resizable:   false,
        title: $('#hide-element-dialog').attr('data-title'),
        open:    function () {
            var overlay = $('<div class="siteorigin-panels ui-widget-overlay ui-widget-overlay ui-front"></div>').css('z-index', 80001);
            $(this).data('overlay', overlay).closest('.ui-dialog').before(overlay);

            var fieldValues = JSON.parse($('#hide-elements').val());
            if (typeof fieldValues != 'undefined' || fieldValues != null) {

                for (var fieldName in fieldValues) {

                    // Save the dialog field
                    var df = $( '#hide-element-dialog [data-style-field="' + fieldName + '"]' );
                    switch( df.data('style-field-type') ) {
                        case 'checkbox':
                            df.attr('checked', fieldValues[fieldName]);
                            break;
                        default :
                            df.val(fieldValues[fieldName]);
                            break;
                    }
                }
            }

        },
        close : function(){
            $(this).data('overlay').remove();

            // Copy the dialog values back to the hidden value
            var fieldValues = {};
            $( '#hide-element-dialog [data-style-field]').each(function() {
                var $$ = $(this);
                var fieldName = $$.data('style-field');

                switch($$.data('style-field-type')) {
                    case 'checkbox':
                        fieldValues[fieldName] = $$.is(':checked');
                        break;
                    default :
                        fieldValues[fieldName] = $$.val();
                        break;
                }
            });

            $('#hide-elements').val(JSON.stringify(fieldValues));
        },
        buttons: {
            'Done': function () {
                $('#hide-element-dialog').dialog('close');
            }
        }
    });

    // Create the dialog that we use to add new grids
    $( '#grid-add-dialog' )
        .show()
        .dialog( {
            dialogClass: 'panels-admin-dialog',
            autoOpen: false,
            modal: false, // Disable modal so we don't mess with media editor. We'll create our own overlay.
            title:   $( '#grid-add-dialog' ).attr( 'data-title' ),
            open:    function () {
                $( this ).find( 'input' ).val( 2 ).select();
                var overlay = $('<div class="siteorigin-panels ui-widget-overlay ui-widget-overlay ui-front"></div>').css('z-index', 80001);
                $(this).data('overlay', overlay).closest('.ui-dialog').before(overlay);
            },
            close : function(){
                $(this).data('overlay').remove();
            },
            buttons: gridAddDialogButtons
        })
        .on('keydown', function(e) {
            if (e.keyCode == $.ui.keyCode.ENTER) {
                // This is the same as clicking the add button
                gridAddDialogButtons[panels.i10n.buttons.add]();
                setTimeout(function(){$( '#grid-add-dialog' ).dialog( 'close' );}, 1)
            }
            else if (e.keyCode === $.ui.keyCode.ESCAPE) {
                $( '#grid-add-dialog' ).dialog( 'close' );
            }
        });
    ;

    // Create the main add widgets dialog
    $( '#panels-dialog' ).show()
        .dialog( {
            dialogClass: 'panels-admin-dialog',
            autoOpen:    false,
            resizable:   false,
            draggable:   false,
            modal:       false,
            title:       $( '#panels-dialog' ).attr( 'data-title' ),
            minWidth:    960,
            maxHeight:   Math.round($(window).height() * 0.8),
            open :       function () {
                var overlay = $('<div class="siteorigin-panels-ui-widget-overlay ui-widget-overlay ui-front"></div>').css('z-index', 80001);
                $(this).data('overlay', overlay).closest('.ui-dialog').before(overlay);
            },
            close:       function () {
                $(this).data('overlay').remove();
                if(panels.animations) $( '#panels-container .panel.new-panel' ).hide().slideDown( 1000 ).removeClass( 'new-panel' );
                else $( '#panels-container .panel.new-panel' ).show().removeClass( 'new-panel' );
            }
        } )
        .on('keydown', function(e) {
            if (e.keyCode === $.ui.keyCode.ESCAPE) {
                $(this ).dialog('close');
            }
        });
    
    $( '#so-panels-panels .handlediv' ).click( function () {
        // Trigger the resize to reorganise the columns
        setTimeout( function () {
            $( window ).resize();
        }, 150 );
    } );

    // The button for adding a panel
    $( '#panels .panels-add')
//        .button( {
//            //icons: {primary: 'ui-icon-add'},
//            text:  'Add Widget'
//        } )
        .click( function () {
            $('#panels-text-filter-input' ).val('').keyup();
            $( '#panels-dialog' ).dialog( 'open' );
            return false;
        } );

    // The button for adding a grid
    $( '#panels .grid-add' )
//        .button( {
////            icons: { primary: 'ui-icon-columns' },
//            text:  'Add Row'
//        } )
        .click( function () {
            $( '#grid-add-dialog' ).dialog( 'open' );
            return false;
        } );

    $('#add-to-panels .page-settings').click(function () {
        $( '#page-setting-dialog' ).dialog( 'open' );
        return false;
    });

    $('#add-to-panels .hide-elements').click(function () {
        $( '#hide-element-dialog' ).dialog( 'open' );
        return false;
    });

    // Set the default text of the SiteOrigin link
    $('#siteorigin-widgets-link').data('text', $('#siteorigin-widgets-link').html() );

    // Handle filtering in the panels dialog
    $( '#panels-text-filter-input' )
        .keyup( function (e) {
            if( e.keyCode == 13 ) {
                // If we pressed enter and there's only one widget, click it
                var p = $( '#panels-dialog .panel-type-list .panel-type:visible' );
                if( p.length == 1 ) p.click();
                return;
            }

            var value = $( this ).val().toLowerCase();

            // Filter the panels
            $( '#panels-dialog .panel-type-list .panel-type' )
                .show()
                .each( function () {
                    if ( value == '' ) return;

                    if ( $( this ).find( 'h3' ).html().toLowerCase().indexOf( value ) == -1 ) {
                        $( this ).hide();
                    }
                } )
        } )
        .click( function () {
            $( this ).keyup()
        } );

    // Handle adding a new panel
    $( '#panels-dialog .panel-type' ).click( function () {
        var panel = $('#panels-dialog').panelsCreatePanel( $( this ).attr('data-class') );
        panels.addPanel(panel, null, null, true);

        // Close the add panel dialog
        $( '#panels-dialog' ).dialog( 'close' );
    } );

    // Either setup an initial grid or load one from the panels data
    if ( typeof panelsData != 'undefined' ) panels.loadPanels(panelsData);
    else panels.createGrid( 1 );

    $( window ).resize( function () {
        // When the window is resized, we want to center any panels-admin-dialog dialogs
        $( '.panels-admin-dialog' ).filter( ':data(dialog)' ).dialog( 'option', 'position', 'center' );
    } );

    // Handle switching between the page builder and other tabs
    $( '#wp-content-editor-tools' )
        .find( '.wp-switch-editor' )
        .click(function () {
            var $$ = $(this);

            $( '#wp-content-editor-container, #post-status-info' ).show();
            $( '#so-panels-panels' ).hide();
            $( '#wp-content-wrap' ).removeClass('panels-active');

            $('#content-resize-handle' ).show();
        } ).end()
        .prepend(
            $( '<a id="content-panels" class="hide-if-no-js wp-switch-editor switch-panels">' + $( '#so-panels-panels h3.hndle span' ).html() + '</a>' )
                .click( function () {

                    var $$ = $( this );
                    // This is so the inactive tabs don't show as active
                    $( '#wp-content-wrap' ).removeClass( 'tmce-active html-active' );

                    // Hide all the standard content editor stuff
                    $( '#wp-content-editor-container, #post-status-info' ).hide();

                    // Show panels and the inside div
                    $( '#so-panels-panels' ).show().find('> .inside').show();
                    $( '#wp-content-wrap' ).addClass( 'panels-active' );

                    // Triggers full refresh
                    $( window ).resize();
                    $('#content-resize-handle' ).hide();

                    return false;
                } )
        );

    $( '#wp-content-editor-tools .wp-switch-editor' ).click(function(){
        // This fixes an occasional tab switching glitch
        var $$ = $(this);
        var p = $$.attr('id' ).split('-');
        $( '#wp-content-wrap' ).addClass(p[1] + '-active');

        if ($(this).is('.switch-panels')) {
            $('#insert-media-button').hide();
        } else {
            $('#insert-media-button').show();
        }

    });

    // This is for the home page panel
    $('#panels-home-page #post-body' ).show();
    $('#panels-home-page #post-body-wrapper' ).css('background', 'none');

    // Move the panels box into a tab of the content editor
    $( '#so-panels-panels' )
        .insertAfter( '#wp-content-editor-container' )
        .addClass( 'wp-editor-container' )
        .hide()
        .find( '.handlediv' ).remove()
        .end()
        .find( '.hndle' ).html('' ).append(
            $('#add-to-panels')
        );

    // append add row button
    var $addRowButton = $('<div class="add-row-button button dashicons-before dashicons-plus" data-tooltip="Add Row"></div>');
    var $addRowContainer = $('<div class="add-row-container"></div>');
    $addRowContainer.append($addRowButton);
    $('#so-panels-panels').append($addRowContainer);

    $('#so-panels-panels .add-row-container .add-row-button').click(function () {
        $( '#grid-add-dialog' ).dialog( 'open' );
    });

    // When the content panels button is clicked, trigger a window resize to set up the columns
    $('#content-panels' ).click(function(){
        $(window ).resize();
    });

    if ( typeof panelsData != 'undefined' || $('#panels-home-page' ).length) $( '#content-panels' ).click();
    // Click again after the panels have been set up
    setTimeout(function(){
        if ( typeof panelsData != 'undefined' || $('#panels-home-page' ).length) $( '#content-panels' ).click();
        $('#so-panels-panels .hndle' ).unbind('click');
        $('#so-panels-panels .cell' ).eq(0 ).click();
    }, 150);

    if($('#panels-home-page' ).length){
        // Lets do some home page settings
        $('#content-tmce, #content-html' ).remove();
        $('#content-panels' ).hide();

        // Initialize the toggle switch
        $('#panels-toggle-switch' )
            .mouseenter(function(){
                $(this ).addClass('subtle-move');
            })
            .click(function(){
                $(this ).toggleClass('state-off').toggleClass('state-on' ).removeClass('subtle-move');
                $('#panels-home-enabled' ).val( $(this ).hasClass('state-off') ? 'false' : 'true' );
            } );

        // Handle the previews
        $('#post-preview' ).click(function(event){
            var form = $('#panels-container' ).closest('form');
            var originalAction = form.attr('action');
            form.attr('action', panels.previewUrl ).attr('target', '_blank').submit().attr('action', originalAction).attr('target', '_self');
            event.preventDefault();
        });
    }

    // Add a hidden field to show that the JS is complete. If this doesn't run we assume that JS is broken and the interface hasn't loaded properly
    $('#panels').append('<input name="panels_js_complete" type="hidden" value="1" />');
} );