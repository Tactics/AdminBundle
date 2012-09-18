jQuery(document).ready(function($) {
    $('#toon_meer').click(function() {
        if($(this).attr('data-status') == 'inactive') {
            $(this).text($(this).attr('data-active-text'));
            $(this).attr('data-status', 'active');
        } else {
            $(this).text($(this).attr('data-inactive-text'));
            $(this).attr('data-status', 'inactive');
        }
    });
});