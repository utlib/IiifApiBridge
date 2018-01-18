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
 * Helper for authentication-related requests.
 *
 * @author University of Toronto Libraries
 * @package IiifApiBridge/Request
 */
class IiifApiBridge_Request_Authentication extends IiifApiBridge_BaseRequest {
    
    /**
     * Send a login request.
     * @param string $user The user name.
     * @param string $password The password.
     * @return string|null The token if the login was successful, null if unsuccessful.
     */
    public function login($user, $password) {
        try {
            $responseJson = $this->rawJsonRequest('POST', '/login', array(
                'username' => $user,
                'password' => $password,
            ));
            return $responseJson['token'];
        } catch (IiifApiBridge_Exception_FailedJsonRequestException $ex) {
            debug("IIIF API {$ex->getResponseCode()}: " . json_encode($ex->getResponseBody(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } catch (Exception $ex) {
            debug("IIIF API login exception: {$ex}");
        }
        return NULL;
    }
    
    /**
     * Send a token verification request.
     * @param string $token The token to verify.
     * @return boolean Whether the token is valid.
     */
    public function verify($token) {
        try {
            $this->rawJsonRequest('POST', '/verifyToken', array(
                'token' => $token,
            ));
            return true;
        } catch (IiifApiBridge_Exception_FailedJsonRequestException $ex) {
            debug("IIIF API {$ex->getResponseCode()}: " . json_encode($ex->getResponseBody(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } catch (Exception $ex) {
            debug("IIIF API login exception: {$ex}");
        }
        return false;
    }
    
}
