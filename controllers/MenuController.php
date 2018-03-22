<?php

/**
 * Controller for main menu actions.
 * @package IiifApiBridge/Controller
 */
class IiifApiBridge_MenuController extends Omeka_Controller_AbstractActionController {
    
    /**
     * Redirect to main status menu.
     * GET /iiif-api-bridge
     */
    public function indexAction() {
        // Redirect to status
        $this->_helper->redirector->goto(array(), 'status');
    }
    
    /**
     * Renders the main status menu for admins only.
     * GET /iiif-api-bridge/status
     * @throws Omeka_Controller_Exception_403
     */
    public function statusAction() {
        // Restrict to admins only
        $this->_adminsOnly();
        // Embedded JS
        queue_js_file('status');
        // Authentication status message
        $this->view->auth_status = $this->__getAuthStatusMessage();
        // Daemon status message
        $this->view->daemon_status = $this->__getDaemonStatusMessage();
    }
    
    /**
     * The authentication status update AJAX endpoint.
     * Format: { "auth_status": "message" }
     */
    public function authStatusAction() {
        // Restrict to admins only
        $this->_adminsOnly();
        // Try to log in
        $authStatusMessage = $this->__getAuthStatusMessage();
        // Respond
        $this->_respondWithJson(array('auth_status' => $authStatusMessage));
    }
    
    /**
     * The daemon status update AJAX endpoint.
     * Format: { "daemon_status": "message" }
     */
    public function daemonStatusAction() {
        // Restrict to admins only
        $this->_adminsOnly();
        // Try to log in
        $daemonStatusMessage = $this->__getDaemonStatusMessage();
        // Respond
        $this->_respondWithJson(array('daemon_status' => $daemonStatusMessage));
    }
    
    public function daemonRestartAction() {
        Zend_Registry::get('bootstrap')->getResource('jobs')->sendLongRunning('IiifApiBridge_Job_UpdateDaemon', array());
        $processTable = get_db()->getTable('Process');
        $processSelect = $processTable->getSelect();
        $processSelect->order('id DESC');
        $processSelect->where("args LIKE '%IiifApiBridge_Job_UpdateDaemon%'");
        $daemonProcess = $processTable->fetchObject($processSelect);
        set_option('iiifapibridge_daemon_id', $daemonProcess->id);
        $this->daemonStatusAction();
    }
    
    /**
     * Return an authentication status message
     * @return string
     */
    private function __getAuthStatusMessage() {
        $requester = new IiifApiBridge_Request_Authentication();
        if ($requester->verify(get_option('iiifapibridge_api_key'))) {
            return '<p>' . __('You are signed into IIIF API.') . '</p>';
        } else {
            $message =  '<p>' . __('Failed to sign into IIIF API.') . '</p>';
            if (current_user()->role === 'super') {
                $message .= '<p>' . __('Please check your %sconfigurations for IIIF API Root and API Key%s.', '<a href="' . admin_url('plugins/config', array('name' => 'IiifApiBridge')) . '">', '</a>') . '</p>';
            } else {
                $message .= '<p>' . __('Please contact your system administrator ') . '</p>';
            }
            return $message;
        }
    }
    
    /**
     * Return a daemon status message
     * @return string
     */
    private function __getDaemonStatusMessage() {
        if ($daemonId = get_option('iiifapibridge_daemon_id')) {
            $daemon = get_record_by_id('Process', $daemonId);
            switch ($daemon->status) {
                case Process::STATUS_STARTING:
                    return __('The daemon is starting.');
                case Process::STATUS_IN_PROGRESS:
                    return __('The daemon is running.');
                case Process::STATUS_COMPLETED:
                    return __('The daemon has completed its current batch.');
                case Process::STATUS_STOPPED:
                    return __('The daemon is stopped.');
                case Process::STATUS_PAUSED:
                    return __('The daemon is paused.');
                case Process::STATUS_ERROR:
                    return __('The daemon has crashed from an unexpected error. Please manually restart the daemon if there are pending jobs.');
                default:
                    return '???';
            }
        } else {
            return __('The daemon is sleeping.');
        }
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
    
    /**
     * Respond with JSON data (no layout).
     * 
     * @param array $jsonData JSON data in nested array form
     * @param integer $status The HTTP response code
     */
    protected function _respondWithJson($jsonData, $status=200) {
        $response = $this->getResponse();
        $this->_helper->viewRenderer->setNoRender();
        $response->setHttpResponseCode($status);
        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Content-Type', 'application/json');
        $response->clearBody();
        $response->setBody(json_encode($jsonData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
    
    /**
     * Respond with raw data.
     * 
     * @param string $data Response data
     * @param integer $status The HTTP response code
     * @param string $mime The MIME type
     */
    protected function _respondWithRaw($data, $status=200, $mime='application/json') {
        $response = $this->getResponse();
        $this->_helper->viewRenderer->setNoRender();
        $response->setHttpResponseCode($status);
        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Content-Type', $mime);
        $response->clearBody();
        $response->setBody($data);
    }
}
