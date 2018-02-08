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
 * Utility for queuing tasks for collections.
 *
 * @author University of Toronto Libraries
 * @package IiifApiBridge/Queue
 */
class IiifApiBridge_Queue_Collection {
    
    /**
     * Submit a JSON collection to the API for creation.
     * @param Collection $collection
     * @param array $json The collection being sent.
     * @return IiifApiBridge_Task The created task.
     */
    public static function create($collection, $json) {
        return get_db()->getTable('IiifApiBridge_Task')->insertTaskFor($collection, '/collections', 'POST', array(
            'collection' => $json
        ));
    }
    
    /**
     * Submit a JSON collection to the API for updating.
     * @param Collection $collection
     * @param array $json The collection JSON being sent.
     * @return IiifApiBridge_Task The created task.
     */
    public static function update($collection, $json) {
        $pathComponents = split('/', $json['@id']);
        $convertedPath = '/' . join('/', array_slice($pathComponents, -2));
        return get_db()->getTable('IiifApiBridge_Task')->insertTaskFor($collection, $convertedPath, 'PUT', array(
            'collection' => $json
        ));
    }
    
    /**
     * Request that the API delete a collection with the given name in its URI.
     * @param string $name
     * @return IiifApiBridge_Task The created task.
     */
    public static function delete($name) {
        return get_db()->getTable('IiifApiBridge_Task')->insertTaskFor(array('id' => $name, 'type' => 'Collection'), '/collection/' . $name, 'DELETE', NULL);
    }
    
}
