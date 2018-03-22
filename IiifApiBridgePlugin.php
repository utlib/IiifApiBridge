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
        'after_save_item',
        'before_delete_item',
        'after_save_collection',
        'before_delete_collection',
	);

	protected $_filters = array(
        'admin_navigation_main',
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
        set_option('iiifapibridge_api_key', '');
        set_option('iiifapibridge_api_root', '');
        set_option('iiifapibridge_daemon_id', '');
        set_option('iiifapibridge_api_top_name', '');
        set_option('iiifapibridge_api_prefix_name', '');
	}
	
    /**
     * Hook: On uninstallation.
     */
	public function hookUninstall() {
		$db = get_db();
        $db->query("DROP TABLE IF EXISTS `{$db->prefix}iiif_api_bridge_tasks`;");
        delete_option('iiifapibridge_api_key');
        delete_option('iiifapibridge_api_root');
        delete_option('iiifapibridge_daemon_id');
        delete_option('iiifapibridge_api_top_name');
        delete_option('iiifapibridge_api_prefix_name');
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
        set_option('iiifapibridge_api_top_name', $data['iiifapibridge_api_top_name']);
        set_option('iiifapibridge_api_prefix_name', $data['iiifapibridge_api_prefix_name']);
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
     * Hook: After item is saved.
     * @param array $args
     */
    public function hookAfterSaveItem($args) {
        $item = $args['record'];
        if ($item->item_type_id != get_option('iiifitems_annotation_item_type')) {
            $json = IiifItems_Util_Canvas::buildCanvas($item);
            IiifApiBridge_Util_JsonTransform::transformCanvas($json, $item);
            if ($args['insert']) {
                IiifApiBridge_Queue_Canvas::create($item, $json);
            } else {
                IiifApiBridge_Queue_Canvas::update($item, $json);
            }
            if (!empty($item->collection_id)) {
                $this->hookAfterSaveCollection(array(
                    'record' => get_record_by_id('Collection', $item->collection_id),
                    'insert' => false,
                ));
            }
        } else {
            $annotatedItem = IiifItems_Util_Annotation::findAnnotatedItemFor($item);
            $json = IiifItems_Util_Annotation::buildAnnotation($item);
            IiifApiBridge_Util_JsonTransform::transformAnnotation($json, $item, $annotatedItem);
            if ($args['insert']) {
                IiifApiBridge_Queue_Annotation::create($item, $json);
            } else {
                IiifApiBridge_Queue_Annotation::update($item, $json);
            }
            if (!empty($annotatedItem->collection_id)) {
                $this->hookAfterSaveCollection(array(
                    'record' => get_record_by_id('Collection', $annotatedItem->collection_id),
                    'insert' => false,
                ));
            }
        }
    }
    
    /**
     * Hook: Before item is deleted.
     * @param array $args
     */
    public function hookBeforeDeleteItem($args) {
        $item = $args['record'];
        if ($item->item_type_id != get_option('iiifitems_annotation_item_type')) {
            IiifApiBridge_Queue_Canvas::delete($item->collection_id, $item->id);
            if (!empty($item->collection_id)) {
                $this->hookAfterSaveCollection(array(
                    'record' => get_record_by_id('Collection', $item->collection_id),
                    'insert' => false,
                ));
            }
        } else {
            $attachedItem = IiifItems_Util_Annotation::findAnnotatedItemFor($item);
            IiifApiBridge_Queue_Annotation::delete($attachedItem->collection_id, $item->id);
            if (!empty($attachedItem->collection_id)) {
                $this->hookAfterSaveCollection(array(
                    'record' => get_record_by_id('Collection', $attachedItem->collection_id),
                    'insert' => false,
                ));
            }
        }
    }
    
    /**
     * Hook: After collection is saved.
     * @param array $args
     * @param bool $bubble Whether to bubble up.
     */
    public function hookAfterSaveCollection($args, $bubble=true) {
        $collection = $args['record'];
        if (IiifItems_Util_Manifest::isManifest($collection)) {
            $parentCollection = IiifItems_Util_Manifest::findParentFor($collection);
            $json = IiifItems_Util_Manifest::buildManifest($collection);
            IiifApiBridge_Util_JsonTransform::transformManifest($json, $collection, $parentCollection);
            if ($args['insert']) {
                IiifApiBridge_Queue_Manifest::create($collection, $json);
            } else {
                IiifApiBridge_Queue_Manifest::update($collection, $json);
            }
            if (!empty($parentCollection) && $bubble) {
                $this->hookAfterSaveCollection(array(
                    'record' => $parentCollection,
                    'insert' => false,
                ), false);
            }
        } elseif (IiifItems_Util_Collection::isCollection($collection)) {
            $parentCollection = IiifItems_Util_Collection::findParentFor($collection);
            $json = IiifItems_Util_Collection::buildCollection($collection);
            IiifApiBridge_Util_JsonTransform::transformCollection($json, $collection, $parentCollection);
            if ($args['insert']) {
                IiifApiBridge_Queue_Collection::create($collection, $json);
            } else {
                IiifApiBridge_Queue_Collection::update($collection, $json);
            }
            if (!empty($parentCollection) && $bubble) {
                $this->hookAfterSaveCollection(array(
                    'record' => $parentCollection,
                    'insert' => false,
                ), false);
            }
        }
    }
    
    /**
     * Hook: Before collection is deleted.
     * @param array $args
     */
    public function hookBeforeDeleteCollection($args) {
        $collection = $args['record'];
        $parentCollection = IiifItems_Util_Collection::findParentFor($collection);
        if (IiifItems_Util_Manifest::isManifest($collection)) {
            IiifApiBridge_Queue_Manifest::delete($collection->id);
        } else if (IiifItems_Util_Collection::isCollection($collection)) {
            IiifApiBridge_Queue_Collection::delete($collection->id);
        }
        if (!empty($parentCollection)) {
            $this->hookAfterSaveCollection(array(
                'record' => $parentCollection,
                'insert' => false,
            ), false);
        }
    }
    
    /**
     * Filter: Admin-side main nav
     * Add the IIIF API Sync Bridge admin screen for admins and super-users
     * @param type $nav
     * @return type
     */
    public function filterAdminNavigationMain($nav) {
        $user = current_user();
        if (!empty($user) && ($user->role == 'super' || $user->role == 'admin')) {
            $nav[] = array(
                'label' => __("IIIF API Sync"),
                'uri' => url('iiif-api-bridge'),
            );
        }
        return $nav;
    }
}
