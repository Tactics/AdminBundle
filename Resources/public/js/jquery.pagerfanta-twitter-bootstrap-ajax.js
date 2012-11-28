jQuery(function($) {
    $(document).on('click', '.pagination > ul > li > a', function() {
        var element = $(this);
        var domElement = this;

        var pagination = element.closest('.pagination').parent();

        var cnt = 0;
        $('.pagination').each(function(){
            if ($(this).parent().get(0) == pagination.get(0))
            {
                return false;
            }
            cnt++;
        })
     
        $.ajax({
            url: element.attr('href'),
            type: "POST",
            dataType: "html",
            success: function(html) {
                var newElement = $(html).find('.pagination:eq(' + cnt + ')').parent();
                pagination.replaceWith(newElement);
            }
        });

        return false;

    });
});
