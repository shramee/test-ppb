
(function( exports, $ ){
    var api = wp.customize;

    api.FontControl = api.Control.extend({
        ready: function() {
            var control = this,
                picker = this.container.find('.color-picker-hex');

            picker.val( control.settings['font_color']()).wpColorPicker({
                change: function() {
                    control.settings['font_color'].set( picker.wpColorPicker('color') );
                },
                clear: function() {
                    control.settings['font_color'].set( false );
                }
            });
        }
    });

    api.controlConstructor['font'] = api.FontControl;

    api.BorderControl = api.Control.extend({
        ready: function() {
            var control = this,
                picker = this.container.find('.color-picker-hex');

            picker.val( control.settings['border_color']()).wpColorPicker({
                change: function() {
                    control.settings['border_color'].set( picker.wpColorPicker('color') );
                },
                clear: function() {
                    control.settings['border_color'].set( false );
                }
            });
        }
    });

    api.controlConstructor['border'] = api.BorderControl;

    $(document).ready(function () {
        $('#customize-controls .customize-control-slider').each(function () {
            var $control = $(this);
            var $currentValueText = $control.find('.current-value-text');
            $control.find('.slider').change(function () {
                $currentValueText.text($(this).val() + $currentValueText.attr('unit'));
            });
            $control.find('.slider').bind('input', function () {
                $currentValueText.text($(this).val() + $currentValueText.attr('unit'));
            });
        });

        $("#customize-controls .customize-control-font").each(function () {

            $(this).find('.sc-font-size-unit').change(function () {
                var $fontControl = $(this).closest('.customize-control-font');
                var $fontSizeInput = $fontControl.find('.sc-font-size-number');

                if ($(this).val() == 'px') {
                    $fontSizeInput.attr('min', '10');
                    $fontSizeInput.attr('max', '100');
                    $fontSizeInput.attr('step', '1');

                    var number = parseFloat($fontSizeInput.val());
                    if (number < 10) {
                        number = 10;
                        $fontSizeInput.val(number).change();
                    } else if (number > 100) {
                        number = 100;
                        $fontSizeInput.val(number).change();
                    }

                } else if ($(this).val() == 'em') {
                    $fontSizeInput.attr('min', '0.1');
                    $fontSizeInput.attr('max', '10');
                    $fontSizeInput.attr('step', '0.1');

                    var number = parseFloat($fontSizeInput.val());
                    if (number < 0.1) {
                        number = 0.1;
                        $fontSizeInput.val(number).change();
                    } else if (number > 10) {
                        number = 10;
                        $fontSizeInput.val(number).change();
                    }
                }
            }).change();
        });

        var $resetRow = $("<li class='reset-row'></li>");
        var $resetButton = $('<input class="reset-button button" type="button" value="Reset to Default" />');

        $resetButton.click(function () {
            // global section
            $('#customize-control-bg_color .wp-picker-default').click(); // reset to default color
            resetSliderControl('page_width');
            $('#customize-control-page_full_width input[type="checkbox"]').prop('checked', false);
            $('#customize-control-background_image .actions a').click(); // remove background image

            $('#customize-control-logo_image .actions a.remove').click(); // remove logo image

            // font section
            for (var i = 0; i < 6; ++i) {
                var ele = 'h' + (i + 1) + "_font";
                resetFontControl(ele);
            }
            $('#customize-control-a_link_color .wp-picker-default').click(); // reset to default link color

            // nav section
            resetSliderControl('nav_opacity');
            $('#customize-control-nav_bg_color .wp-picker-default').click(); // reset to default nav bg color
            $('#customize-control-fix_nav_bar_to_top input[type="checkbox"]').prop('checked', false);
            $('#customize-control-nav_align select').val('left');
            $('#customize-control-nav_sub_item_align select').val('center');
            resetFontControl('nav_font');


        });

        function resetFontControl(controlName) {
            $('#customize-control-' + controlName + " .sc-font-family-list").val('').change(); // default font family
            $('#customize-control-' + controlName + " .sc-font-weight-style-list").val('').change(); // default font weight and style
            $('#customize-control-' + controlName + " .wp-picker-default").click(); // reset to default color
            var $fontSizeNumber = $('#customize-control-' + controlName + " .sc-font-size-number");
            $fontSizeNumber.val(parseInt($fontSizeNumber.attr('default'))).change(); // default font size
        }

        function resetSliderControl(controlName) {
            var $slider = $('#customize-control-' + controlName + ' .slider');
            $slider.val(parseInt($slider.attr('default'))).change();
        }

//        $resetRow.append($resetButton);
//        $('#customize-theme-controls').append($resetRow);

    });

    function setHiddenField($fontControl) {
        var fontFamily = $fontControl.find('.sc-font-family-list').val();
        var fontWeightStyle = $fontControl.find('.sc-font-weight-style-list').val();
        var fontColor = $fontControl.find('.sc-font-color-text-box').val();
        var fontSize = $fontControl.find('.sc-font-size-number').val();

        var value = {
            'font_id': fontFamily,
            'font_size': {'amount': fontSize },
            'font_color': fontColor,
            'font_weight_style': fontWeightStyle
        };

        var json = JSON.stringify(value);
        $fontControl.find('.sc-font-value').val(json);
    }

})( wp, jQuery );
