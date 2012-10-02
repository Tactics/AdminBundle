jQuery(document).ready(function($) {
    $('.subnav-item').hide();

    $('#main_nav > .dropdown').click(function() {
        updateSubnav($(this));

        return false;
    });
});

function updateSubnav(selected) {
    if (selected.hasClass('open')) {
        // "close" the selected item
        selected.removeClass('open');
        // hide all open subnav-items
        $('.subnav-item').hide();
        // decrease content padding
        $('#content').css('padding', '55px');
    } else {
        // remove the open class from the current open item
        $('#main_nav > .open').removeClass('open');
        // add open to the clicked item
        selected.addClass('open');
        // hide all current subnav-items
        $('.subnav-item').hide();
        // open those with the correct class
        var id = selected.find('a').attr('href');
        $('.' + id.substring(1, id.length)).toggle();
        // increase the content padding so we don't overlap
        var padding = $('.subnav').height() + 55;
        $('#content').css('padding-top', padding + 'px');
    }
}