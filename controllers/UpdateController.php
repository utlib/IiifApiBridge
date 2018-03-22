<?php

/*
 * Copyright 2018 University of Toronto Libraries.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Controller for update-related actions.
 *
 * @author University of Toronto Libraries
 * @package IiifApiBridge/Controller
 */
class IiifApiBridge_UpdateController extends Omeka_Controller_AbstractActionController {
    
    public function updateAction() {
        if (is_admin_theme()) {
            $this->_respondWithJson($_POST);
        } else {
            throw new Omeka_Controller_Exception_404;
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
    
}
