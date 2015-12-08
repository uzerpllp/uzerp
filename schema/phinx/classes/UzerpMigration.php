<?php
namespace UzerpPhinx;

class UzerpMigration extends \Phinx\Migration\AbstractMigration {
    
    // Cache keys to be cleaned on migration/rollback
    protected $cache_keys = array();
    
    /**
     * Remove listed keys from memcache
     *
     * @param array $keys
     *            array of key names to remove
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
}

?>