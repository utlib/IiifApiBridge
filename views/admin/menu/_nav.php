<nav id="section-nav" class="navigation vertical">
    <?php
    $navArray = array(
        array(
            'label' => __('Import Items'),
            'uri' => url('iiif-items/import'),
        ),
    );
    echo nav($navArray, 'admin_navigation_settings');
    ?>
</nav>
