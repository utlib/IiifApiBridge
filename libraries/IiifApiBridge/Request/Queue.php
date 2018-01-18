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
 * Helper for queue-related requests
 *
 * @author University of Toronto Libraries
 * @package IiifApiBridge/Request
 */
class IiifApiBridge_Request_Queue extends IiifApiBridge_BaseRequest {
    /**
     * Get the status of a queued CRUD operation.
     * @param string $queueUrl The URL to the queue entry
     * @return array The API's JSON response.
     * @throws Zend_Http_Client_Exception
     * @throws IiifApiBridge_Exception_FailedJsonRequestException
     */
    public function status($queueUrl) {
        return $this->authJsonRequest('GET', $queueUrl, array());
    }
}
