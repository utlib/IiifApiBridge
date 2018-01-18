<?php

/**
 * Controller for main menu actions.
 * @package IiifApiBridge/Controller
 */
class IiifApiBridge_MenuController extends Omeka_Controller_AbstractActionController {
    /**
     * Renders the main menu for admins only.
     * GET /iiif-api-bridge
     * @throws Omeka_Controller_Exception_403
     */
    public function indexAction() {
        // Restrict to admins only
        $this->_adminsOnly();
    }
    
    public function testAction() {
        // Restrict to admins only
        $this->_adminsOnly();
        // 
    }
    
    /**
     * Throw 403 status when the current user isn't an admin.
     * @throws Omeka_Controller_Exception_403
     */
    protected function _adminsOnly() {
        $current_user = current_user();
        if ($current_user->role != 'super' && $current_user->role != 'admin') {
            throw new Omeka_Controller_Exception_403;
        }
    }
}
