<?php
namespace UzerpPhinx;

/**
 * uzERP Phinx Migration Class
 *
 * @author uzERP LLP and Steve Blamey <sblamey@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2000-2015 uzERP LLP (support#uzerp.com). All rights reserved.
 */
class UzerpMigration extends \Phinx\Migration\AbstractMigration {
    
    // Cache keys to be cleaned on migration/rollback
    protected $cache_keys = [];

    // List of module components to be added/removed
    protected $module_components =[];
    
    /**
     * Remove listed keys from memcache
     *
     * @param array $keys  array of key names to remove
     */
    function cleanMemcache($keys)
    {
        $options = $this->getAdapter()->getOptions();
        $memcache = new \Memcached();
        $memcache->addServer("localhost", 11211);
        file_put_contents('php://stderr', 'Removing keys from cache...' . PHP_EOL);
        foreach ($keys as $key) {
            $memcache->delete($options['name'] . $key);
            file_put_contents('php://stderr', 'removed ' . $options['name'] . $key . PHP_EOL);
        }
    }


    /**
     * Add uzERP module components
     * 
     * This enables class autloading for the component in uzERP
     * by adding its details to the module_components table.
     * 
     * For example: schema/phinx/migrations/20190418105223_add_vat_module_components.php
     */
    public function addModuleComponents()
    {
        $components = [];

        foreach ($this->module_components as $component) {
            $component_record = $this->fetchRow("SELECT count(location) FROM module_components WHERE location = '{$component['location']}'");
            if ($component_record['count'] == 0) {
                $module = $component['module'];
                unset($component['module']);
                $module_record = $this->fetchRow("SELECT id FROM modules WHERE name = '{$module}'");
                $component['module_id'] = $module_record['id'];
                $component['createdby'] = 'admin';
                $components[] = $component;
            }
        }

        $table = $this->table('module_components');
        $table->insert($components);
        $table->save();
    }


    /**
     * Remove uzERP module components
     * 
     * This prevents class autloading for the component in uzERP
     * by removing its details from the module_components table.
     */
    public function removeModuleComponents()
    {
        $components = [];

        foreach ($this->module_components as $component) {
            $components[] = $component['name'];
        }
        $names = "'" . implode("','", $components) . "'";
        
        $sql = "DELETE FROM module_components WHERE name in ({$names})";
        $this->execute($sql);
    }
}
?>