/* 
 * global admin.js file
 * 
 * only stuff that we need on (almost) every page is allowed.
 * 
 * this stuff slows down every single page load!
 */

/**
 * jQuery ajax activity indicator
 */
jQuery(function($) {
    var indicator = $('.ajaxIndicator');
    
    var ajaxRequests = 0;
    
    $(document).ajaxSend(function(a, b, c) {
        ajaxRequests++;
        indicator.show();
    });
    $(document).ajaxStop(function() {
        ajaxRequests--;
        if (! ajaxRequests)
        {
            indicator.hide();
        }        
    });
});
