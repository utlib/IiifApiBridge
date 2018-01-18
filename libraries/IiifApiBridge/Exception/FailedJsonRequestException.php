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
 * Exception for when a JSON request fails (non-200 response)
 *
 * @author University of Toronto Libraries
 * @package IiifApiBridge/Exception
 */
class IiifApiBridge_Exception_FailedJsonRequestException extends Exception {
    
    /**
     * The HTTP response code.
     * @var int
     */
    private $__responseCode;
    
    /**
     * The decoded response body.
     * @var array
     */
    private $__responseBody;
    
    /**
     * 
     * @param int $responseCode
     * @param array $responseBody
     * @param string $message
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($responseCode, $responseBody, $message = "", $code = 0, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->__responseCode = $responseCode;
        $this->__responseBody = $responseBody;
    }
    
    /**
     * Return the HTTP response code.
     * @return int
     */
    public function getResponseCode() {
        return $this->__responseCode;
    }
    
    /**
     * Return the HTTP response body, JSON-decoded.
     * @return array
     */
    public function getResponseBody() {
        return $this->__responseBody;
    }
}
