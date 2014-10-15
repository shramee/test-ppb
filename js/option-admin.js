(function ($) {

    $(document).ready(function () {
        $( ".panel-type-list" ).sortable({
            connectWith: ".panel-type-list",
            update: function (event, ui) {
                moveVisualEditorToTopLeft();

                updateUsedList();
                updateUnusedList();

            }
        }).disableSelection();

        moveVisualEditorToTopLeft();
    });

    function updateUsedList() {
        var value = [];
        $('.panel-type-list.used-list > .panel-type').each(function () {
           var widgetClass = $(this).attr('data-class');
            value.push(widgetClass);
        });

        $('#pootlepage_widgets_used').val(JSON.stringify(value));
    }

    function updateUnusedList() {
        var value = [];
        $('.panel-type-list.unused-list > .panel-type').each(function () {
            var widgetClass = $(this).attr('data-class');
            value.push(widgetClass);
        });

        $('#pootlepage_widgets_unused').val(JSON.stringify(value));
    }

    function moveVisualEditorToTopLeft() {
        var $widget = $('.panel-type-list.used-list > .panel-type[data-class=Pootle_Text_Widget]');
        if ($widget.length > 0) {
            $('.panel-type-list.used-list').prepend($widget);
        }
    }

})(jQuery);

