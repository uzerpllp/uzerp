<?php

use Phinx\Migration\AbstractMigration;


class SoLineNotes extends AbstractMigration
{
    //Cache keys to be cleaned on migration/rollback
    private $cache_keys = array(
        'uzerp[table_fields][so_lines]',
    );
    
    /**
     * Remove listed keys from mencache
     *
     * @param: array $keys array of key names to remove
     */
    private function CleanMemcache($keys)
    {
        $memcache = new Memcached();
        $memcache->addServer("localhost", 11211);
        file_put_contents('php://stderr', 'Removing keys from cache...' . PHP_EOL);
        foreach ($keys as $key)
        {
            $memcache->delete($key);
            file_put_contents('php://stderr', 'removed '. $key . PHP_EOL);
        }
    }
    
    /**
     * Add note field to so_lines table and allow null values
     */
    public function change()
    {
        $solines = $this->table('so_lines');
        $solines->addColumn('note', 'text', array('null' => true,))
                ->save();
        $this->CleanMemcache($this->cache_keys);
    }
}