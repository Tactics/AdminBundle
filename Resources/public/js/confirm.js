(function($) {
    $(function($) {
        $('.js-confirm-delete').live('click', function(event) {
            return confirm('Are you sure you wish to delete this object?');
        });
    });
})(jQuery);
