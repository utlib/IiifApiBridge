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
 * A daemon request expecting a 202 queue response.
 *
 * @author University of Toronto Libraries
 */
class IiifApiBridge_Request_Daemon extends IiifApiBridge_BaseRequest {

    /**
     * Push data to the remote location
     * @param string $url
     * @param string $verb
     * @param string|array $body
     */
    public function push($url, $verb, $body=null) {
        return $this->authJsonRequest($verb, $url, $body);
    }

}
