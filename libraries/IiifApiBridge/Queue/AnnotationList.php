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
 * Utility for queuing tasks for annotation lists.
 *
 * @author University of Toronto Libraries
 * @package IiifApiBridge/Queue
 */
class IiifApiBridge_Queue_AnnotationList {
    
    /**
     * Submit a JSON annotation list to the API for creation.
     * @param Item $item The annotated item.
     * @param array $json The annotation list JSON being sent.
     * @return IiifApiBridge_Task The created task.
     */
    public static function create($item, $json) {
        $pathComponents = split('/', $json['@id']);
        $convertedPath = '/' . join('/', array_slice($pathComponents, -3, 2));
        return get_db()->getTable('IiifApiBridge_Task')->insertTaskFor($item, $convertedPath, 'POST', array(
            'annotationList' => $json
        ));
    }
    
    /**
     * Submit a JSON annotation list to the API for updating.
     * @param Item $item The annotated item.
     * @param array $json The annotation list JSON being sent.
     * @return IiifApiBridge_Task The created task.
     */
    public static function update($item, $json) {
        $pathComponents = split('/', $json['@id']);
        $convertedPath = '/' . join('/', array_slice($pathComponents, -3));
        return get_db()->getTable('IiifApiBridge_Task')->insertTaskFor($item, $convertedPath, 'PUT', array(
            'annotationList' => $json
        ));
    }
    
    /**
     * Request that the API delete an annotation list with the given item ID and name in its URI.
     * @param string $id The item ID.
     * @param string $name The entry name.
     * @return IiifApiBridge_Task The created task.
     */
    public static function delete($id, $name) {
        return get_db()->getTable('IiifApiBridge_Task')->insertTaskFor(array('id' => $id, 'type' => 'Item'), "/{$id}/list/{$name}", 'DELETE', array());
    }
    
}