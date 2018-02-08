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
 * Utility for queuing tasks for manifests.
 *
 * @author University of Toronto Libraries
 * @package IiifApiBridge/Queue
 */
class IiifApiBridge_Queue_Manifest {
    
    /**
     * Submit a JSON manifest to the API for creation.
     * @param Collection $manifest
     * @param array $json The manifest JSON being sent.
     * @return IiifApiBridge_Task The created task.
     */
    public static function create($manifest, $json) {
        $pathComponents = split('/', $json['@id']);
        $convertedPath = '/' . join('/', array_slice($pathComponents, -2));
        return get_db()->getTable('IiifApiBridge_Task')->insertTaskFor($manifest, $convertedPath, 'POST', array(
            'manifest' => $json
        ));
    }
    
    /**
     * Submit a JSON manifest to the API for updating.
     * @param Collection $manifest
     * @param array $json The manifest JSON being sent.
     * @return IiifApiBridge_Task The created task.
     */
    public static function update($manifest, $json) {
        $pathComponents = split('/', $json['@id']);
        $convertedPath = '/' . join('/', array_slice($pathComponents, -2));
        return get_db()->getTable('IiifApiBridge_Task')->insertTaskFor($manifest, $convertedPath, 'PUT', array(
            'manifest' => $json
        ));
    }
    
    /**
     * Request that the API delete a manifest with the given item ID in its URI.
     * @param string $id The item ID.
     * @return IiifApiBridge_Task The created task.
     */
    public static function delete($id) {
        return get_db()->getTable('IiifApiBridge_Task')->insertTaskFor(array('id' => $id, 'type' => 'Collection'), "/{$id}/manifest", 'DELETE', NULL);
    }
    
}
