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
    }
}