jQuery(function() {
    
    // Handle "Refresh" button under authentication status
    jQuery('#refresh_auth_status').on('click', function() {
        jQuery.get(jQuery(this).data('url')).done(function(data) {
            jQuery('#auth_status').html(data['auth_status']);
        });
        return false;
    });
    
    // Handle "Refresh" button under authentication status
    jQuery('#refresh_daemon_status').on('click', function() {
        jQuery.get(jQuery(this).data('url')).done(function(data) {
            jQuery('#daemon_status').html(data['daemon_status']);
        });
        return false;
    });
    
});
