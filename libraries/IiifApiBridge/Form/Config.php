<?php

/**
 * The main plugin configuration form.
 * @package IiifApiBridge/Form
 */
class IiifApiBridge_Form_Config extends Omeka_Form {
    /**
     * Sets up elements for this form.
     */
    public function init() {
        // Top-level parent
        parent::init();
        $this->applyOmekaStyles();
        $this->setAutoApplyOmekaStyles(false);
        $this->addElement('checkbox', 'iiifapibridge_daemon_enabled', array(
            'label' => __('Daemon Enabled?'),
            'description' => __("Whether to automatically run the update daemon upon creating, updating or deleting a IIIF-enabled resource. Please turn off before doing batch operations to minimize server load."),
            'value' => get_option('iiifapibridge_daemon_enabled'),
        ));
        $this->addElement('text', 'iiifapibridge_api_root', array(
            'label' => __('IIIF API Root'),
            'description' => __('The base URL of the IIIF API installation.'),
            'value' => get_option('iiifapibridge_api_root'),
        ));
        $this->addElement('text', 'iiifapibridge_api_key', array(
            'label' => __('API Key'),
            'description' => __('The authentication key for the account to use on IIIF API.'),
            'value' => get_option('iiifapibridge_api_key'),
        ));
        $this->addElement('text', 'iiifapibridge_api_top_name', array(
            'label' => __('Top-Level Collection Name'),
            'description' => __("Name of the top-level collection at the API to synchronize with. If left blank, this plugin will synchronize with the API's top-level collection."),
            'value' => get_option('iiifapibridge_api_top_name'),
        ));
        $this->addElement('text', 'iiifapibridge_api_prefix_name', array(
            'label' => __('Top-Level Prefix'),
            'description' => __("Prefix to add before item/collection IDs while mapping to API URIs."),
            'value' => get_option('iiifapibridge_api_prefix_name'),
        ));
    }
}