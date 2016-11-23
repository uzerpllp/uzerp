<?php

use UzerpPhinx\UzerpMigration;

class SoDespatchlinesCsnote extends UzerpMigration
{
    //Cache keys to be cleaned on migration/rollback
    protected $cache_keys = array(
        'uzerp[table_fields][so_despatchlines]',
    );

    /**
     * Add cs_failure_note column to so_despatchlines table
     */
    public function change()
    {
        $sodespatchlines = $this->table('so_despatchlines');
        $sodespatchlines->addColumn('cs_failure_note', 'text', array('null' => true, 'after' => 'cs_failurecode_id'))
        ->update();
        $this->CleanMemcache($this->cache_keys);
    }
}
