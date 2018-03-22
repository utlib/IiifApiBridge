jQuery(function() {
    var refreshDaemonStatus = function() {
        jQuery.get(jQuery('#refresh_daemon_status').data('url')).done(function(data) {
            jQuery('#daemon_status').html(data['daemon_status']);
        });
        return false;
    };
    
    // Handle "Refresh" button under authentication status
    jQuery('#refresh_auth_status').on('click', function() {
        jQuery.get(jQuery(this).data('url')).done(function(data) {
            jQuery('#auth_status').html(data['auth_status']);
        });
        return false;
    });
    
    // Handle "Refresh" button under authentication status
    jQuery('#refresh_daemon_status').on('click', refreshDaemonStatus);
    
    // Handle Daemon force-restart
    jQuery('#restart_daemon').on('click', function() {
        jQuery.post(jQuery(this).data('url')).done(function(data) {
            jQuery('#daemon_status').html(data['daemon_status']);
        });
        return false;
    });
    
    // Refresh daemon status every 5 seconds
    setInterval(refreshDaemonStatus, 5000);
});
