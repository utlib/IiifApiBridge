<?php

defined('IIIF_API_BRIDGE_DIRECTORY') or define('IIIF_API_BRIDGE_DIRECTORY', dirname(__FILE__));

/**
 * The main plugin class.
 * @package IiifApiBridge
 */
class IiifApiBridgePlugin extends Omeka_Plugin_AbstractPlugin
{
	protected $_hooks = array(
		'install',
		'uninstall',
		'upgrade',
		'config',
		'config_form',
		'define_routes',
        'iiifitems_new_collection',
        'iiifitems_edit_collection',
        'iiifitems_delete_collection',
        'iiifitems_new_manifest',
        'iiifitems_edit_manifest',
        'iiifitems_delete_manifest',
        'iiifitems_new_canvas',
        'iiifitems_edit_canvas',
        'iiifitems_delete_canvas',
        'iiifitems_new_annotation',
        'iiifitems_edit_annotation',
        'iiifitems_delete_annotation',
	);

	protected $_filters = array(
	);

    /**
     * Hook: On installation.
     */
	public function hookInstall() {
		$db = get_db();
        $db->query("CREATE TABLE IF NOT EXISTS `{$db->prefix}iiif_api_bridge_tasks` (" .
<<<SQL
`id` INT(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
`record_id` INT(10) NOT NULL,
`record_type` VARCHAR(50) NOT NULL,
`status` VARCHAR(8) NOT NULL DEFAULT 'queued',
`url` VARCHAR(255) NOT NULL,
`verb` VARCHAR(8) NOT NULL,
`data` MEDIUMTEXT,
`added` TIMESTAMP DEFAULT '2018-02-01 00:00:00',
`modified` TIMESTAMP DEFAULT NOW() ON UPDATE NOW()
SQL
        . ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
	}
	
    /**
     * Hook: On uninstallation.
     */
	public function hookUninstall() {
		$db = get_db();
        $db->query("DROP TABLE IF EXISTS `{$db->prefix}iiif_api_bridge_tasks`;");
	}

    /**
     * Hook: On upgrade.
     * Run all pending migrations in order.
     */
	public function hookUpgrade($args) {
		$oldVersion = $args['old_version'];
        $newVersion = $args['new_version'];
        $doMigrate = false;

        $versions = array();
        foreach (glob(IIIF_API_BRIDGE_DIRECTORY . '/libraries/IiifApiBridge/Migration/*.php') as $migrationFile) {
            $className = 'IiifApiBridge_Migration_' . basename($migrationFile, '.php');
            include $migrationFile;
            $versions[$className::$version] = new $className();
        }
        uksort($versions, 'version_compare');

        foreach ($versions as $version => $migration) {
            if (version_compare($version, $oldVersion, '>')) {
                $doMigrate = true;
            }
            if ($doMigrate) {
                $migration->up();
                if (version_compare($version, $newVersion, '>')) {
                    break;
                }
            }
        }
	}

    /**
     * Hook: On plugin configuration form submission.
     * Process submitted configurations.
     * 
     * @param array $args
     * @throws Omeka_Validate_Exception
     */
	public function hookConfig($args) {
		$csrfValidator = new Omeka_Form_SessionCsrf;
        if (!$csrfValidator->isValid($args['post'])) {
            throw Omeka_Validate_Exception(__("Invalid CSRF token."));
        }
        $data = $args['post'];
        set_option('iiifapibridge_api_root', rtrim($data['iiifapibridge_api_root'], '/'));
        set_option('iiifapibridge_api_key', $data['iiifapibridge_api_key']);
	}

    /**
     * Hook: On display plugin configuration form.
     * Render plugin configuration form.
     */
	public function hookConfigForm() {
		require IIIF_API_BRIDGE_DIRECTORY . '/config_form.php';
	}

    /**
     * Hook: Define routes.
     * Pull routes from routes.ini.
     * 
     * @param array $args
     */
	public function hookDefineRoutes($args) {
		$args['router']->addConfig(new Zend_Config_Ini(dirname(__FILE__) . '/routes.ini', 'routes'));
	}
    
    /**
     * Hook: IIIF Toolkit generated a new collection.
     * 
     * @param array $args
     */
    public function hookIiifitemsNewCollection($args) {
        $collection = $args['collection'];
        $parentCollection = $args['parent_collection'];
        $json = $args['json'];
        IiifApiBridge_Util_JsonTransform::transformCollection($json, $collection, $parentCollection);
        IiifApiBridge_Queue_Collection::create($collection, $json);
    }
    
    /**
     * Hook: IIIF Toolkit edited a collection.
     * 
     * @param array $args
     */
    public function hookIiifitemsEditCollection($args) {
        $collection = $args['collection'];
        $parentCollection = $args['parent_collection'];
        $json = $args['json'];
        IiifApiBridge_Util_JsonTransform::transformCollection($json, $collection, $parentCollection);
        IiifApiBridge_Queue_Collection::update($collection, $json);
    }
    
    /**
     * Hook: IIIF Toolkit deleted a collection.
     * 
     * @param array $args
     */
    public function hookIiifitemsDeleteCollection($args) {
        $collection = $args['collection'];
//        $parentCollection = $args['parent_collection'];
        IiifApiBridge_Queue_Collection::delete($collection->id);
    }
    
    /**
     * Hook: IIIF Toolkit generated a new manifest.
     * 
     * @param array $args
     */
    public function hookIiifitemsNewManifest($args) {
        $manifest = $args['manifest'];
        $parentCollection = $args['parent_collection'];
        $json = $args['json'];
        IiifApiBridge_Util_JsonTransform::transformManifest($json, $manifest, $parentCollection);
        IiifApiBridge_Queue_Manifest::create($manifest, $json);
    }
    
    /**
     * Hook: IIIF Toolkit edited a manifest.
     * 
     * @param array $args
     */
    public function hookIiifitemsEditManifest($args) {
        $manifest = $args['manifest'];
        $parentCollection = $args['parent_collection'];
        $json = $args['json'];
        IiifApiBridge_Util_JsonTransform::transformManifest($json, $manifest, $parentCollection);
        IiifApiBridge_Queue_Manifest::update($manifest, $json);
    }
    
    /**
     * Hook: IIIF Toolkit deleted a manifest.
     * 
     * @param array $args
     */
    public function hookIiifitemsDeleteManifest($args) {
        $manifest = $args['manifest'];
//        $parentCollection = $args['parent_collection'];
        IiifApiBridge_Queue_Manifest::delete($manifest->id);
    }
    
    /**
     * Hook: IIIF Toolkit generated a new canvas.
     * 
     * @param array $args
     */
    public function hookIiifitemsNewCanvas($args) {
        $canvas = $args['item'];
        $json = $args['json'];
        IiifApiBridge_Util_JsonTransform::transformCanvas($json, $canvas);
        IiifApiBridge_Queue_Canvas::create($canvas, $json);
    }
    
    /**
     * Hook: IIIF Toolkit edited a manifest.
     * 
     * @param array $args
     */
    public function hookIiifitemsEditCanvas($args) {
        $canvas = $args['item'];
        $json = $args['json'];
        IiifApiBridge_Util_JsonTransform::transformCanvas($json, $canvas);
        IiifApiBridge_Queue_Canvas::update($canvas, $json);
    }
    
    /**
     * Hook: IIIF Toolkit deleted a manifest.
     * 
     * @param array $args
     */
    public function hookIiifitemsDeleteCanvas($args) {
        $canvas = $args['item'];
        IiifApiBridge_Queue_Canvas::delete($canvas->collection_id, $canvas->id);
    }
    
    /**
     * Hook: IIIF Toolkit generated a new annotation.
     * 
     * @param array $args
     */
    public function hookIiifitemsNewAnnotation($args) {
        $annotation = $args['annotation'];
        $attachedItem = $args['attached_item'];
        $json = $args['json'];
        IiifApiBridge_Util_JsonTransform::transformAnnotation($json, $annotation, $attachedItem);
        IiifApiBridge_Queue_Annotation::create($annotation, $json);
    }
    
    /**
     * Hook: IIIF Toolkit edited a annotation.
     * 
     * @param array $args
     */
    public function hookIiifitemsEditAnnotation($args) {
        $annotation = $args['annotation'];
        $attachedItem = $args['attached_item'];
        $json = $args['json'];
        IiifApiBridge_Util_JsonTransform::transformAnnotation($json, $annotation, $attachedItem);
        IiifApiBridge_Queue_Annotation::update($annotation, $json);
    }
    
    /**
     * Hook: IIIF Toolkit deleted a annotation.
     * 
     * @param array $args
     */
    public function hookIiifitemsDeleteAnnotation($args) {
        $annotation = $args['annotation'];
        $attachedItem = $args['attached_item'];
        IiifApiBridge_Queue_Annotation::delete($attachedItem->collection_id, $annotation->id);
    }
}
