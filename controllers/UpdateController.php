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
    
    /**
     * Initiate an update for the given collection/item.
     * Redirect back if successful.
     * @throws Omeka_Controller_Exception_404
     */
    public function updateAction() {
        if (is_admin_theme()) {
            $thingType = $this->getParam('thing_type');
            $thingId = $this->getParam('thing_id');
            // Recognize collections and items only
            if (($thingType != 'Collection' && $thingType != 'Item')) {
                $this->_helper->flashMessenger(__("Invalid type."), 'error');
                Zend_Controller_Action_HelperBroker::getStaticHelper('redirector')->gotoUrl('/');
                return;
            }
            // Find thing, redirect if not found
            $thing = get_record_by_id($thingType, $thingId);
            if (empty($thing)) {
                $this->_helper->flashMessenger(__("Missing update target."), 'error');
                Zend_Controller_Action_HelperBroker::getStaticHelper('redirector')->gotoUrl('/');
                return;
            }
            // Force-sync item if it is a canvas or annotation
            if ($thingType == 'Item' && !IiifItems_Util_Canvas::isNonIiifItem($thing)) {
                if ($thing->item_type_id != get_option('iiifitems_annotation_item_type')) {
                    $json = IiifItems_Util_Canvas::buildCanvas($thing);
                    IiifApiBridge_Util_JsonTransform::transformCanvas($json, $thing);
                    IiifApiBridge_Queue_Canvas::update($thing, $json);
                    $this->_helper->flashMessenger(__("The canvas has been placed on API sync queue. Please check back for updates."), 'success');
                } else {
                    $annotatedItem = IiifItems_Util_Annotation::findAnnotatedItemFor($thing);
                    $json = IiifItems_Util_Annotation::buildAnnotation($thing);
                    IiifApiBridge_Util_JsonTransform::transformAnnotation($json, $thing, $annotatedItem);
                    IiifApiBridge_Queue_Annotation::update($thing, $json);
                    $this->_helper->flashMessenger(__("The annotation has been placed on API sync queue. Please check back for updates."), 'success');
                }
                Zend_Controller_Action_HelperBroker::getStaticHelper('redirector')->gotoUrl('/items/show/' . $thingId);
                return;
            }
            // Force-sync collection if it is a manifest or collection
            if ($thingType == 'Collection' && (IiifItems_Util_Manifest::isManifest($thing) || IiifItems_Util_Collection::isCollection($thing))) {
                if (IiifItems_Util_Manifest::isManifest($thing)) {
                    $parentCollection = IiifItems_Util_Manifest::findParentFor($thing);
                    $json = IiifItems_Util_Manifest::buildManifest($thing);
                    IiifApiBridge_Util_JsonTransform::transformManifest($json, $thing, $parentCollection);
                    IiifApiBridge_Queue_Manifest::update($thing, $json);
                    $this->_helper->flashMessenger(__("The manifest has been placed on API sync queue. Please check back for updates."), 'success');
                } else {
                    $parentCollection = IiifItems_Util_Collection::findParentFor($thing);
                    $json = IiifItems_Util_Collection::buildCollection($thing);
                    IiifApiBridge_Util_JsonTransform::transformCollection($json, $thing, $parentCollection);
                    IiifApiBridge_Queue_Collection::update($thing, $json);
                    $this->_helper->flashMessenger(__("The collection has been placed on API sync queue. Please check back for updates."), 'success');
                }
                Zend_Controller_Action_HelperBroker::getStaticHelper('redirector')->gotoUrl('/collections/show/' . $thingId);
                return;
            }
            // Didn't hit any of the above, indicate that there's nothing
            $this->_helper->flashMessenger(__("This cannot be synchronized with the IIIF API."), 'error');
            Zend_Controller_Action_HelperBroker::getStaticHelper('redirector')->gotoUrl('/' . strtolower($thingType) . '/show/' . $thingId);
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
