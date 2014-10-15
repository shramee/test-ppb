/**
 * Main admin control for the Panel interface
 *
 * @copyright Greg Priday 2013
 * @license GPL 2.0 http://www.gnu.org/licenses/gpl-2.0.html
 */

( function($){

    var newPanelIdInit = 0;
    panels.undoManager = new UndoManager();
    
    /**
     * A jQuery function to get widget data from a panel object
     */
    $.fn.panelsGetPanelData = function(){
        var $$ = $(this);
        var data = {};
        var parts;

        if ( typeof $$.data('dialog') != 'undefined' && !$$.data('dialog').hasClass('ui-dialog-content-loading') ) {

            $$.data('dialog').find( '*[name^=widgets]' ).not( '[data-info-field]' ).each( function () {
                var $$ = $(this);
                var name = /widgets\[[0-9]+\]\[(.*)\]/.exec($$.attr('name'));
                name = name[1];

                parts = name.split('][');

                parts = parts.map(function(e){
                    if( !isNaN(parseFloat(e)) && isFinite(e) ) return parseInt(e);
                    else return e;
                });

                var previousSub = null;
                var sub = data;
                for(var i = 0; i < parts.length; i++) {
                    if(i == parts.length - 1) {

                        if ($$.attr('type') == 'checkbox') {

                            // for multi select checkboxes,
                            // for example, categories in post slider in WooSlider widget
                            if (parts[i] == '') {

								if (previousSub != null && i > 0) {
									if (!$.isArray(previousSub[parts[i - 1]])) {
										previousSub[parts[i - 1]] = [];
									}
									if ($$.is(':checked')) previousSub[parts[i - 1]].push($$.val());

								} else {
									if ($$.is(':checked')) sub[parts[i]] = true;
								}


							} else {
								if ($$.is(':checked')) {
									sub[parts[i]] = $$.val() != '' ? $$.val() : true;
								} else {
									// added this line of code for Pootle Post Loop Widget checkbox
									sub[parts[i]] = $$.val() == '1' ? '0' : false;
								}
							}
						} else {
								sub[parts[i]] = $$.val();
							}

						}
                    else {
                        if(typeof sub[parts[i]] == 'undefined') {
                            sub[parts[i]] = {};
                        }
                        previousSub = sub;
                        sub = sub[parts[i]];
                    }
                }
            } );

        }
        else if( $$.find('input[name$="[data]"]').val() == '' ) {
            data = {};
        }
        else {
            data = JSON.parse( $$.find('input[name$="[data]"]').val() );
        }

        return data;
    }

    /**
     * Create and return a new panel object
     *
     * @param type
     * @param data
     *
     * @return {*}
     */
    $.fn.panelsCreatePanel = function ( type, data ) {

        var newPanelId = newPanelIdInit++;

        var dialogWrapper = $( this );
        var $$ = dialogWrapper.find('.panel-type' ).filter(function() { return $(this).data('class') === type });

        if($$.length == 0) return null;

        // Hide the undo message
        $('#panels-undo-message' ).fadeOut(function(){ $(this ).remove() });
        var panel = $( '<div class="panel new-panel"><div class="panel-wrapper"><div class="title"><h4></h4><span class="actions"></span></div><small class="description"></small></div></div>' );

        var description = $$.find( '.description' ).html();
        var widgetName =  $$.find( 'h3' ).html();

        window.activeDialog = undefined;


        // normalize data.info.style to be object

        var widgetStyleJson = "{}";
        if (typeof data != 'undefined') {
            if (typeof data.info == 'undefined') {
                data.info = {};
            }
            if (typeof data.info.style == 'string') {
                widgetStyleJson = data.info.style;

                data.info.style = JSON.parse(data.info.style);

            } else if (typeof data.info.style == 'object') {
                widgetStyleJson = JSON.stringify(data.info.style);
            } else {
                widgetStyleJson = "{}";
            }
        }

        panel
            .attr('data-type', type)
            .append( $('<input type="hidden" name="widgets[' + newPanelId + '][data]" type="hidden">').val(JSON.stringify(data) ) )
            .append( $('<input type="hidden" name="widgets[' + newPanelId + '][info][raw]" type="hidden">').val(0) )
            .append( $('<input type="hidden" name="widgets[' + newPanelId + '][info][grid]" type="hidden">') )
            .append( $('<input type="hidden" name="widgets[' + newPanelId + '][info][cell]" type="hidden">') )
            .append( $('<input type="hidden" name="widgets[' + newPanelId + '][info][id]" type="hidden">').val(newPanelId) )
            .append( $('<input type="hidden" name="widgets[' + newPanelId + '][info][class]" type="hidden">').val(type) )
            .append( $('<input type="hidden" name="widgets[' + newPanelId + '][info][style]" type="hidden">').val(widgetStyleJson) )
            .append( $('<input type="hidden" name="panel_order[]" type="hidden">').val(newPanelId) )
            .data( {
                // We need this data to update the title
                'title-field': $$.attr( 'data-title-field' ),
                'title': $$.attr( 'data-title' ),
                'raw' : false
            } )
            .find( '.description' ).html( widgetName )
            .end().find( '.title h4' ).html( 'Blank title' );

        // Set the title
        panel.panelsSetPanelTitle( data );

        // Add the action buttons
        panel.find('.title .actions')
            .append(
                $('<a>' + panels.i10n.buttons.edit + '<a>' ).addClass('edit' )
            )
            .append(
                $('<a>' + panels.i10n.buttons.duplicate + '<a>' ).addClass('duplicate' )
            )
            .append(
                $('<a>' + panels.i10n.buttons.style + '<a>' ).addClass('style' )
            )
            .append(
                $('<a>' + panels.i10n.buttons.delete + '<a>' ).addClass('delete')
            );

        panels.setupPanelButtons(panel);

        return panel;
    };

    panels.setupPanelButtons = function($panel) {

        $panel.find('> .panel-wrapper > .title > h4').click( function () {
            $(this).closest('.panel').find('a.edit').click();
            return false;
        });

        $panel.find('> .panel-wrapper > .title > .actions > .edit').click(function(){

            var $currentPanel = $(this).closest('.panel');

            var type = $currentPanel.attr('data-type');

            if( typeof activeDialog != 'undefined' ) return false;

            // The done button
            var dialogButtons = {};
            var doneClicked = false;
            dialogButtons[ panels.i10n.buttons['done'] ] = function () {
                doneClicked = true;
                $( this ).trigger( 'panelsdone', $currentPanel, activeDialog );

                var panelData = $currentPanel.panelsGetPanelData();

                $currentPanel.find('input[name$="[data]"]').val( JSON.stringify( panelData ) );
                $currentPanel.panelsSetPanelTitle( panelData );
                $currentPanel.find('input[name$="[info][raw]"]').val(1);

                // Change the title of the panel
                activeDialog.dialog( 'close' );
            }

            // Create a dialog for this form
            activeDialog = $( '<div class="panel-dialog dialog-form"></div>' )
                .data('widget-type', type)
                .addClass( 'ui-dialog-content-loading' )
                .addClass( 'widget-dialog-' + type.toLowerCase() )
                .dialog( {
                    dialogClass: 'panels-admin-dialog',
                    autoOpen:    true,
                    modal:       false, // Disable modal so we don't mess with media editor. We'll create our own overlay.
                    draggable:   false,
                    resizable:   false,
                    title:       panels.i10n.messages.editWidget.replace( '%s', $currentPanel.data( 'title' ) ),
                    minWidth:    760,
                    maxHeight:   Math.min( Math.round($(window).height() * 0.875), 800),
                    create:      function(event, ui){
                        $(this ).closest('.ui-dialog' ).find('.show-in-panels' ).show();
                    },
                    open:        function () {
                        // This fixes the A element focus issue
                        $(this ).closest('.ui-dialog' ).find('a' ).blur();

                        var overlay = $('<div class="siteorigin-panels-ui-widget-overlay ui-widget-overlay ui-front"></div>').css('z-index', 80001);
                        $(this).data( 'overlay', overlay ).closest( '.ui-dialog' ).before( overlay );
                    },
                    close: function(){

                        if(!doneClicked) {
                            $( this ).trigger( 'panelsdone', $currentPanel, activeDialog );
                        }

                        // Destroy the dialog and remove it
                        $(this).data('overlay').remove();
                        $(this).dialog('destroy').remove();
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

            // This is so we can access the dialog (and its forms) later.
            $currentPanel.data('dialog', activeDialog);

            // Load the widget form
            var widgetClass = type;
            try {
                widgetClass = widgetClass.replace('\\\\', '\\');
            }
            catch(err){
                return;
            }


            $.post(
                ajaxurl,
                {
                    'action' : 'so_panels_widget_form',
                    'widget' : widgetClass,
                    'instance' : JSON.stringify( $currentPanel.panelsGetPanelData() ),
                    'raw' : $currentPanel.find('input[name$="[info][raw]"]').val()
                },
                function(result){
                    // the newPanelId is defined at the top of this function.
                    try {
                        var newPanelId = $currentPanel.find('> input[name$="[info][id]"]').val();

                        result = result.replace( /\{\$id\}/g, newPanelId );
                    }
                    catch (err) {
                        result = '';
                    }

                    activeDialog.html(result).dialog("option", "position", "center");

                    // This is to refresh the dialog positions
                    $( window ).resize();
                    $( document ).trigger('panelssetup', $currentPanel, activeDialog);
                    $( '#panels-container .panels-container' ).trigger( 'refreshcells' );

                    // This gives panel types a chance to influence the form
                    activeDialog.removeClass('ui-dialog-content-loading').trigger( 'panelsopen', $currentPanel, activeDialog );
                },
                'html'
            );

            return false;
        })

        $panel.find('> .panel-wrapper > .title > .actions > .duplicate').click(function(){

            var $currentPanel = $(this).closest('.panel');

            // Duplicate the widget
            var data = JSON.parse($currentPanel.find('input[name*="[data]"]').val());

            if (typeof data.info == 'undefined') {
                data.info = {};
                data.info.raw = $currentPanel.find('input[name*="[info][raw]"]').val();
                data.info.grid = $currentPanel.find('input[name*="[info][grid]"]').val();
                data.info.cell = $currentPanel.find('input[name*="[info][cell]"]').val();
                data.info.id = $currentPanel.find('input[name*="[info][id]"]').val();
                data.info.class = $currentPanel.find('input[name*="[info][class]"]').val();
            }
            if (typeof data.info.style == 'undefined') {
                data.info.style = JSON.parse($currentPanel.find('input[name*="[info][style]"]').val());
            }

            var duplicatePanel = $('#panels-dialog').panelsCreatePanel($currentPanel.attr('data-type'), data);
            window.panels.addPanel(duplicatePanel, $currentPanel.closest('.panels-container'), null, false);
            duplicatePanel.removeClass('new-panel');

            return false;
        });

        $panel.find('> .panel-wrapper > .title > .actions > .style').click(function(){

            var $currentPanel = $(this).closest('.panel');

            // style dialog
            if( typeof activeDialog != 'undefined' ) return false;

            // The done button
            var dialogButtons = {};
            var doneClicked = false;
            dialogButtons[ panels.i10n.buttons['done'] ] = function () {
                doneClicked = true;

                // Change the title of the panel
                activeDialog.dialog( 'close' );
            };

            // Create a dialog for this form
            activeDialog = $( '<div id="widget-styles-dialog" class="panel-dialog dialog-form"></div>' )
                .dialog( {
                    dialogClass: 'panels-admin-dialog',
                    autoOpen:    true,
                    modal:       false, // Disable modal so we don't mess with media editor. We'll create our own overlay.
                    draggable:   false,
                    resizable:   false,
                    title:       panels.i10n.messages.styleWidget,
                    minWidth:    500,
                    maxHeight:   Math.min( Math.round($(window).height() * 0.875), 800),
                    create:      function(event, ui){
                        $(this ).closest('.ui-dialog' ).find('.show-in-panels' ).show();
                    },
                    open:        function () {
                        // This fixes the A element focus issue
                        $(this ).closest('.ui-dialog' ).find('a' ).blur();

                        var overlay = $('<div class="siteorigin-panels-ui-widget-overlay ui-widget-overlay ui-front"></div>').css('z-index', 80001);
                        $(this).data( 'overlay', overlay ).closest( '.ui-dialog' ).before( overlay );

                    },
                    close: function(){
                        if(!doneClicked) {
                            $( this ).trigger( 'panelsdone', $currentPanel, activeDialog );
                        }

                        var backgroundColor = $(this).find('.widget-bg-color').val();
                        var borderWidth = $(this).find('.widget-border-width').val();
                        var borderColor = $(this).find('.widget-border-color').val();
                        var paddingTopBottom = $(this).find('.widget-padding-top-bottom').val();
                        var paddingLeftRight = $(this).find('.widget-padding-left-right').val();
                        var borderRadius = $(this).find('.widget-rounded-corners').val();

                        var styleData = {
                            backgroundColor: backgroundColor,
                            borderWidth: borderWidth,
                            borderColor: borderColor,
                            paddingTopBottom: paddingTopBottom,
                            paddingLeftRight: paddingLeftRight,
                            borderRadius: borderRadius
                        };
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
                        $(this).dialog('destroy').remove();
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

            // This is so we can access the dialog (and its forms) later.
            // this line cannot be used or will mess up panelsGetPanelData
//                    panel.data('dialog', activeDialog);

            activeDialog.html(
                '<div class="field">' +
                    '<label>Widget background color</label>' +
                    '<span><input class="widget-bg-color" type="text" data-style-field-type="color"/></span>' +
                '</div>' +
                '<div class="field">' +
                    '<label>Widget border</label>' +
                    '<span><input class="widget-border-width" type="number" min="0" max="100" step="1" value="0" /> px ' +
                        '<input class="widget-border-color" type="text" data-style-field-type="color" /></span>' +
                '</div>' +
                '<div class="field">' +
                    '<label>Widget padding (top/bottom)</label>' +
                    '<span><input class="widget-padding-top-bottom" type="number" min="0" max="100" step="1" value="0" /> px</span>' +
                '</div>' +
                '<div class="field">' +
                    '<label>Widget padding (left/right)</label>' +
                    '<span><input class="widget-padding-left-right" type="number" min="0" max="100" step="1" value="0" /> px</span>' +
                '</div>' +
                '<div class="field">' +
                    '<label>Widget rounded corners</label>' +
                    '<span><input class="widget-rounded-corners" type="number" min="0" max="100" step="1" value="0" /> px</span>' +
                '</div>'

                );

            var $hidden = $currentPanel.find('input[name$="[style]"]');
            var json = $hidden.val();
            var styleData = JSON.parse(json);

            activeDialog.find('.widget-bg-color').val(styleData.backgroundColor);
            activeDialog.find('.widget-border-width').val(styleData.borderWidth);
            activeDialog.find('.widget-border-color').val(styleData.borderColor);
            activeDialog.find('.widget-padding-top-bottom').val(styleData.paddingTopBottom);
            activeDialog.find('.widget-padding-left-right').val(styleData.paddingLeftRight);
            activeDialog.find('.widget-rounded-corners').val(styleData.borderRadius);

            activeDialog.find('[data-style-field-type="color"]')
                .wpColorPicker()
                .closest('p').find('a').click(function(){
                    $( '#widget-styles-dialog').dialog("option", "position", "center");
                });

            return false;
        });

        $panel.find('> .panel-wrapper > .title > .actions > .delete').click(function(){
            var $currentPanel = $(this).closest('.panel');
            // The delete button
            var deleteFunction = function ($panel) {
                // Add an entry to the undo manager

                panels.undoManager.register(
                    this,
                    function(type, data, container, position){
                        // Readd the panel
                        var panel = $('#panels-dialog').panelsCreatePanel(type, data, container);
                        panels.addPanel(panel, container, position, true);

                        // We don't want to animate the undone panels
                        $( '#panels-container .panel' ).removeClass( 'new-panel' );
                    },
                    [ $panel.attr('data-type'), $panel.panelsGetPanelData(), $panel.closest('.panels-container'), $panel.index() ],
                    'Remove Panel'
                );

                // Create the undo notification
                $('#panels-undo-message' ).remove();
                $('<div id="panels-undo-message" class="updated"><p>' + panels.i10n.messages.deleteWidget + ' - <a href="#" class="undo">' + panels.i10n.buttons.undo + '</a></p></div>' )
                    .appendTo('body')
                    .hide()
                    .slideDown()
                    .find('a.undo')
                    .click(function(){
                        panels.undoManager.undo();
                        $('#panels-undo-message' ).fadeOut(function(){
                            $(this ).remove();
                        });
                        return false;
                    })
                ;

                var remove = function () {
                    // Remove the panel and refresh the grid container cell sizes
                    var gridContainer = $panel.closest('.grid-container');
                    $panel.remove();
                    gridContainer.panelsResizeCells();
                };

                if(panels.animations) $panel.slideUp( remove );
                else {
                    $panel.hide();
                    remove();
                }
            };

            deleteFunction($currentPanel);
            return false;
        });
    };

    /**
     * Add a widget to the interface.
     *
     * @param panel The new panel (Widget) we're adding.
     * @param container The container we're adding it to
     * @param position The position
     * @param bool animate Should we animate the panel
     */
    panels.addPanel = function(panel, container, position, animate){
        if(container == null) container = $( '#panels-container .cell.cell-selected .panels-container' ).eq(0);
        if(container.length == 0) container = $( '#panels-container .cell .panels-container' ).eq(0);
        if(container.length == 0) {
            // There are no containers, so lets add one.
            panels.createGrid(1, [1]);
            container = $( '#panels-container .cell .panels-container' ).eq(0);
        }

        if (position == null) container.append( panel );
        else {
            var current = container.find('.panel' ).eq(position);
            if(current.length == 0) container.append( panel );
            else {
                panel.insertBefore(current);
            }
        }

        container.sortable( "refresh" ).trigger( 'refreshcells' );
        container.closest( '.grid-container' ).panelsResizeCells();
        if(animate) {
            if(panels.animations)
                $( '#panels-container .panel.new-panel' )
                    .hide()
                    .slideDown( 450 , function(){ panel.find('a.edit').click() } )
                    .removeClass( 'new-panel' );
            else {
                $( '#panels-container .panel.new-panel').show().removeClass( 'new-panel' );
                panel.find('a.edit').click();
            }
        }
    }

    /**
     * Set the title of the panel
     */
    $.fn.panelsSetPanelTitle = function ( data ) {
        return $(this ).each(function(){

            var titleValue = '';

            if( typeof data != 'undefined' ) {
                if( typeof data.title != 'undefined' && data.title != '' ) titleValue = data.title;
                else {
                    for ( var i in data ) {
                        if(typeof data[i] == 'string' && data[i] != '') {
                            titleValue = data[i];
                            break;
                        }
                    }
                }
            }

            try {
                titleValue = titleValue.substring(0, 80).replace(/(<([^>]+)>)/ig,"")
            }
            catch(err) {
                titleValue = '';
            }

            if (titleValue == '' || titleValue == null || titleValue == false || titleValue == '0') {
                titleValue = 'Blank title';
            }
            $(this ).find( 'h4' ).html( titleValue );
        });
    }

    /**
     * Loads panel data
     *
     * @param data
     */
    panels.loadPanels = function(data){
        panels.clearGrids();

        if( typeof data != 'undefined' && typeof data.grids != 'undefined' ) {
            // Create all the content
            for ( var gi in data.grids ) {
                var cellWeights = [];

                // Get the cell weights
                for ( var ci in data.grid_cells ) {
                    if ( Number( data.grid_cells[ci]['grid'] ) == gi ) {
                        cellWeights[cellWeights.length] = Number( data.grid_cells[ci].weight );
                    }
                }

                // Create the grids
                var grid = panels.createGrid( Number( data.grids[gi]['cells'] ), cellWeights, data.grids[gi]['style'] );

                // Add panels to the grid cells
                for ( var pi in data.widgets ) {

                    if( typeof  data.widgets[pi]['info'] == 'undefined') continue;

                    if ( Number( data.widgets[pi]['info']['grid'] ) == gi ) {
                        var pd = data.widgets[pi];
                        var panel = $('#panels-dialog').panelsCreatePanel( pd['info']['class'], pd );
                        grid
                            .find( '.panels-container' ).eq( Number( data.widgets[pi]['info']['cell'] ) )
                            .append( panel )
                    }
                }
            }
        }

        $( '#panels-container .panels-container' )
            .sortable( 'refresh' )
            .trigger( 'refreshcells' );

        // Remove the new-panel class from any of these created panels
        $( '#panels-container .panel' ).removeClass( 'new-panel' );
        
        // Make sure everything is sized properly
        $( '#panels-container .grid-container' ).each( function () {
            $( this ).panelsResizeCells();
        } );
    }

} )( jQuery );