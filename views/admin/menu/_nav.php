<nav id="section-nav" class="navigation vertical">
    <?php
    $navArray = array(
        array(
            'label' => __('System Status'),
            'uri' => url('iiif-api-bridge/status'),
        ),
    );
    echo nav($navArray, 'admin_navigation_settings');
    ?>
</nav>
