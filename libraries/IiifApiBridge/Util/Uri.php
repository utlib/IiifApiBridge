<?php

/**
 * Utilities for building recommended form URIs
 * @package IiifApiBridge/Util
 */
class IiifApiBridge_Util_Uri {
    /**
     * Indicate the collection type.
     */
    const COLLECTION = 'Collection';
    
    /**
     * Indicate the manifest type.
     */
    const MANIFEST = 'Manifest';
    
    /**
     * Indicate the sequence type.
     */
    const SEQUENCE = 'Sequence';
    
    /**
     * Indicate the canvas type.
     */
    const CANVAS = 'Canvas';
    
    /**
     * Indicate the annotation type.
     */
    const ANNOTATION = 'Annotation';
    
    /**
     * Indicate the annotation list type.
     */
    const ANNOTATION_LIST = 'AnnotationList';
    
    /**
     * Indicate the range type.
     */
    const RANGE = 'Range';
    
    /**
     * Indicate the layer type.
     */
    const LAYER = 'Layer';
    
    /**
     * Indicate the general resource type.
     */
    const RESOURCE = 'Resource';
    
    /**
     * Build a URI in IIIF Presentation API 2.x recommended form.
     * 
     * @param string $type The type of URI to build. 
     * @param string $id
     * @param string $name
     * @return string
     * @throws IiifApiBridge_Exception_UnknownUriTypeException
     */
    public static function build($type, $id, $name) {
        $serverUrlHelper = new Zend_View_Helper_ServerUrl;
        $urlRoot = $serverUrlHelper->serverUrl();
        $urlPrefix = public_url('');
        $hostPrefix = $urlRoot . $urlPrefix;
        switch ($type) {
            case self::COLLECTION:
                return $hostPrefix . 'collection/' . $name;
            case self::MANIFEST:
                return $hostPrefix . $id . '/manifest';
            case self::SEQUENCE:
                return $hostPrefix . $id . '/sequence/' . $name;
            case self::CANVAS:
                return $hostPrefix . $id . '/canvas/' . $name;
            case self::ANNOTATION:
                return $hostPrefix . $id . '/annotation/' . $name;
            case self::ANNOTATION_LIST:
                return $hostPrefix . $id . '/list/' . $name;
            case self::RANGE:
                return $hostPrefix . $id . '/range/' . $name;
            case self::LAYER:
                return $hostPrefix . $id . '/layer/' . $name;
            case self::RESOURCE:
                return $hostPrefix . $id . '/res/' . $name;
            default:
                throw new IiifApiBridge_Exception_UnknownUriTypeException;
        }
    }
}
