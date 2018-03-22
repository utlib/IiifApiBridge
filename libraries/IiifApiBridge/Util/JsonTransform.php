<?php

/**
 * Utilities for transforming IDs in JSON content
 * @package IiifApiBridge/Util
 */
class IiifApiBridge_Util_JsonTransform {
    /**
     * Transform the URIs in the JSON collection in-place to the recommended format.
     * @param array &$json
     * @param Collection $collection
     * @param Collection|null $parentCollection
     */
    public static function transformCollection(&$json, $collection, $parentCollection=NULL) {
        // Replace the top-level URI
        $json['@id'] = IiifApiBridge_Util_Uri::build(IiifApiBridge_Util_Uri::COLLECTION, NULL, $collection->id);
        // Add belongsTo attribute
        $root = get_option('iiifapibridge_api_root') . '/';
        $topName = get_option('iiifapibridge_api_top_name');
        if (!empty($parentCollection) || !empty($topName)) {
            $json['within'] = empty($parentCollection) ? (empty($topName) ? '' : IiifApiBridge_Util_Uri::topCollection()) : IiifApiBridge_Util_Uri::build(IiifApiBridge_Util_Uri::COLLECTION, NULL, $parentCollection->id, $root);
        }
        // Replace URI for sub-manifests
        if (!empty($json['manifests'])) {
            $referenceSubmanifests = IiifItems_Util_Collection::findSubmanifestsFor($collection);
            foreach ($json['manifests'] as $i => &$submanifest) {
                $submanifest['@id'] = IiifApiBridge_Util_Uri::build(IiifApiBridge_Util_Uri::MANIFEST, $referenceSubmanifests[$i]->id, NULL);
            } 
        }
        
        // Replace URI for sub-collections
        if (!empty($json['collections'])) {
            $referenceSubcollections = IiifItems_Util_Collection::findCollectionsFor($collection);
            foreach ($json['collections'] as $i => &$subcollection) {
                $subcollection['@id'] = IiifApiBridge_Util_Uri::build(IiifApiBridge_Util_Uri::COLLECTION, NULL, $referenceSubcollections[$i]->id);
            } 
        }
        
        // Replace URI for sub-members
        if (!empty($json['members'])) {
            $referenceSubmembers = IiifItems_Util_Collection::findSubmembersFor($collection);
            foreach ($json['members'] as $i => &$submember) {
                if (IiifItems_Util_Manifest::isManifest($submember)) {
                    $submember['@id'] = IiifApiBridge_Util_Uri::build(IiifApiBridge_Util_Uri::MANIFEST, $referenceSubmembers[$i]->id, NULL);
                } else {
                    $submember['@id'] = IiifApiBridge_Util_Uri::build(IiifApiBridge_Util_Uri::COLLECTION, NULL, $referenceSubmembers[$i]->id);
                }
            } 
        }
    }
    
    /**
     * Transform the URIs in the JSON manifest in-place to the recommended format.
     * @param array $json
     * @param Collection $manifest
     * @param Collection|null $parentCollection
     */
    public static function transformManifest(&$json, $manifest, $parentCollection=NULL) {
        // Find contained items
        $containedItems = get_db()->getTable('Item')->findBy(array('collection_id' => $manifest->id));
        $currentContainedItem = 0;
        // Replace top-level URI
        $json['@id'] = IiifApiBridge_Util_Uri::build(IiifApiBridge_Util_Uri::MANIFEST, $manifest->id, NULL);
        // Add belongsTo attribute
        $root = get_option('iiifapibridge_api_root') . '/';
        $topName = get_option('iiifapibridge_api_top_name');
        if (!empty($parentCollection) || !empty($topName)) {
            $json['within'] = empty($parentCollection) ? (empty($topName) ? '' : IiifApiBridge_Util_Uri::topCollection()) : IiifApiBridge_Util_Uri::build(IiifApiBridge_Util_Uri::COLLECTION, NULL, $parentCollection->id, $root);
        }
        // For each seqnum => sequence
        foreach ($json['sequences'] as $seqnum => &$sequence) {
            // Change JSON ID to recommended form, id=item.collection_id, name=seqnum
            $sequence['@id'] = IiifApiBridge_Util_Uri::build(IiifApiBridge_Util_Uri::SEQUENCE, $manifest->id, $seqnum);
            // For each canvas in the sequence
            foreach ($sequence['canvases'] as &$canvas) {
                // Find the corresponding item
                do {
                    $canvasItem = $containedItems[$currentContainedItem++];
                } while ($canvasItem->hasElementText('IIIF Item Metadata', 'Display as IIIF?') && $canvasItem->getElementTexts('IIIF Item Metadata', 'Display as IIIF?')[0]->text === 'Never');
                // Transform it
                self::transformCanvas($canvas, $canvasItem);
            }
        }
        // Remove search services
        if (!empty($json['services'])) {
            for ($i = 0; $i < count($json['services']); $i++) {
                $service = $json['services'][$i];
                if (self::__stringStartsWith($service['profile'], 'http://iiif.io/api/search/')) {
                    unset($json['services'][$i--]);
                }
            }
        }
    }
    
    /**
     * Transform the URIs in the JSON canvas in-place to the recommended format.
     * @param array $json
     * @param Item $item
     */
    public static function transformCanvas(&$json, $item) {
        // Change the top-level URI
        $root = get_option('iiifapibridge_api_root') . '/';
        $json['@id'] = IiifApiBridge_Util_Uri::build(IiifApiBridge_Util_Uri::CANVAS, $item->collection_id, $item->id, $root);
        // For each imagenum => image
        foreach ($json['images'] as $imageNum => &$image) {
            // Change URI to recommended form, id=item.collection_id, name=i-item.id-imagenum
            $image['@id'] = IiifApiBridge_Util_Uri::build(IiifApiBridge_Util_Uri::ANNOTATION, $item->collection_id, 'i-' . $item->id . '-' . $imageNum);
            // Change resource URI to recommended form, id=item.collection_id, name=i-item.id-imagenum
            $image['resource']['@id'] = IiifApiBridge_Util_Uri::build(IiifApiBridge_Util_Uri::RESOURCE, $item->collection_id, 'i-' . $item->id . '-' . $imageNum);
            // Change attachment URI to recommended form
            $image['on'] = $json['@id'];
        }
        // For each otherContent
        if (!empty($json['otherContent'])) {
            // Replace main annotation list URI to recommended form, id=item.collection_id, name=item.id
            foreach ($json['otherContent'] as &$otherContent) {
                if ($otherContent['@type'] === 'sc:AnnotationList') {
                    $otherContent['@id'] = IiifApiBridge_Util_Uri::build(IiifApiBridge_Util_Uri::ANNOTATION_LIST, $item->collection_id, $item->id);
                }
            }
        }
        // If empty, add dummy annotation list for future annotations to latch onto
        else {
            $json['otherContent'] = array(
                array(
                    '@id' => IiifApiBridge_Util_Uri::build(IiifApiBridge_Util_Uri::ANNOTATION_LIST, $item->collection_id, $item->id),
                    '@type' => 'sc:AnnotationList',
                ),
            );
        }
    }
    
    /**
     * Transform the URIs in the JSON annotation in-place to the recommended format.
     * @param array $json
     * @param Item $item
     */
    public static function transformAnnotation(&$json, $item, $attachedItem) {
        // Get the URI root
        $root = get_option('iiifapibridge_api_root') . '/';
        // Replace JSON ID with transformed URL, id=attached_item.collection_id, name=item.id
        $json['@id'] = IiifApiBridge_Util_Uri::build(IiifApiBridge_Util_Uri::ANNOTATION, $attachedItem->collection_id, $item->id);
        // Belongs entirely within the annotation list of attached item
        $json['belongsTo'] = array(IiifApiBridge_Util_Uri::build(IiifApiBridge_Util_Uri::ANNOTATION_LIST, $attachedItem->collection_id, $attachedItem->id, $root));
        $json['embeddedEntirely'] = true;
        // COMPROMISE: Replace resource with a format that the API understands (strip tags, add @id)
        $json['resource'] = $json['resource'][0];
        $json['resource']['@id'] = IiifApiBridge_Util_Uri::build(IiifApiBridge_Util_Uri::RESOURCE, $attachedItem->collection_id, "a-{$item->id}");
        // If on is a string containing xywh=
        if (is_string($json['on']) && ($sp = strpos($json['on'], '#xywh=')) !== FALSE) {
            // Replace JSON ID with transformed URL (id=attached_item.collection_id, name=$attached_item.id), plus xywh= portion
            $json['on'] = IiifApiBridge_Util_Uri::build(IiifApiBridge_Util_Uri::CANVAS, $attachedItem->collection_id, $attachedItem->id, $root) . substr($json['on'], $sp);
        }
        // If on is an array
        elseif (is_array($json['on'])) {
            // COMPROMISE: Take on XYWH area of first region
            $json['on'] = IiifApiBridge_Util_Uri::build(IiifApiBridge_Util_Uri::CANVAS, $attachedItem->collection_id, $attachedItem->id, $root) . '#' . $json['on'][0]['selector']['default']['value'];
        }
    }
    
    /**
     * Transform the URIs in the JSON annotation list in-place to the recommended format.
     * @param array $json
     * @param Item $item The item to which this annotation list belongs.
     */
    public static function transformAnnotationList(&$json, $item) {
        // Find annotations under this item
        $referenceAnnotations = IiifItems_Util_Annotation::findAnnotationItemsUnder($item);
        // Transform the top-level URI
        $root = get_option('iiifapibridge_api_root') . '/';
        $json['@id'] = IiifApiBridge_Util_Uri::build(IiifApiBridge_Util_Uri::ANNOTATION_LIST, $item->collection_id, $item->id, $root);
        // Transform the annotation URIs
        foreach ($json['resources'] as $annoNum => &$annotation) {
            self::transformAnnotation($annotation, $referenceAnnotations[$annoNum], $item);
        }
    }
    
    /**
     * Return whether a string starts with the given substring.
     * @param string $str The source string.
     * @param string $substr The substring to check.
     * @return boolean
     */
    private static function __stringStartsWith($str, $substr) {
        return substr($str, 0, count($substr)) === $substr;
    }
}
