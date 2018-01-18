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
		
	}
	
    /**
     * Hook: On uninstallation.
     */
	public function hookUninstall() {
		
	}

    /**
     * Hook: On upgrade.
     * Run all pending migrations in order.
     */
	public function hookUpgrade() {
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
    }
    
    /**
     * Hook: IIIF Toolkit deleted a collection.
     * 
     * @param array $args
     */
    public function hookIiifitemsDeleteCollection($args) {
        $collection = $args['collection'];
        $parentCollection = $args['parent_collection'];
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
    }
    
    /**
     * Hook: IIIF Toolkit deleted a manifest.
     * 
     * @param array $args
     */
    public function hookIiifitemsDeleteManifest($args) {
        $manifest = $args['manifest'];
        $parentCollection = $args['parent_collection'];
    }
    
    /**
     * Hook: IIIF Toolkit generated a new canvas.
     * 
     * @param array $args
     */
    public function hookIiifitemsNewCanvas($args) {
        $canvas = $args['canvas'];
        $json = $args['json'];
    }
    
    /**
     * Hook: IIIF Toolkit edited a manifest.
     * 
     * @param array $args
     */
    public function hookIiifitemsEditCanvas($args) {
        $canvas = $args['canvas'];
        $json = $args['json'];
    }
    
    /**
     * Hook: IIIF Toolkit deleted a manifest.
     * 
     * @param array $args
     */
    public function hookIiifitemsDeleteCanvas($args) {
        $canvas = $args['canvas'];
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
    }
    
    /**
     * Hook: IIIF Toolkit deleted a annotation.
     * 
     * @param array $args
     */
    public function hookIiifitemsDeleteAnnotation($args) {
        $annotation = $args['annotation'];
        $attachedItem = $args['attached_item'];
    }
}
