<?php 
echo head(array(
    'title' => __('IIIF API Bridge'),
));
include __DIR__ . '/_nav.php';
echo flash();
?>

<h2><?php echo __("API Authentication"); ?></h2>

<div id="auth_status"><?php echo $auth_status; ?></div>

<button id="refresh_auth_status" class="green button" data-url="<?php echo admin_url(array(), 'iiifapibridge_auth_status'); ?>"><?php echo __('Refresh'); ?></button>

<h2><?php echo __("Sync Daemon"); ?></h2>

<p id="daemon_status"><?php echo $daemon_status; ?></p>

<button id="refresh_daemon_status" class="green button" data-url="<?php echo admin_url(array(), 'iiifapibridge_daemon_status'); ?>"><?php echo __('Refresh'); ?></button>

<button id="restart_daemon" class="blue button" data-url="<?php echo admin_url(array(), 'iiifapibridge_daemon_restart'); ?>"><?php echo __('Force Restart'); ?></button>
