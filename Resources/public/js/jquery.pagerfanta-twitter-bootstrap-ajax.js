jQuery(function($) {
    $('.pagination > ul > li > a').on('click', function() {
        var element = $(this);

        $.ajax({
            url: element.attr('href'),
            type: "POST",
            dataType: "html",
            success: function(html) {
                element.closest('.pagination').replaceWith($('.pagination', $(html)));
            }
        });

        return false;

    });
});
