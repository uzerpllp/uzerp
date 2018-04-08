<?php


use UzerpPhinx\UzerpMigration;

class DjjobQueue extends UzerpMigration
{
    /**
     * Change Method.
     *
     */
    public function change()
    {
        $jobs = $this->table('jobs');
        $jobs->addColumn('handler', 'string')
        ->addColumn('queue', 'string', ['limit' => 255], 'default', 'default')
        ->addColumn('attempts', 'integer', ['default' => 0])
        ->addColumn('run_at', 'datetime', ['null' => true])
        ->addColumn('locked_at', 'datetime', ['null' => true])
        ->addColumn('locked_by', 'string', ['limit' => 255, 'null' => true])
        ->addColumn('failed_at', 'datetime', ['null' => true])
        ->addColumn('error', 'string', ['null' => true])
        ->addColumn('created_at', 'datetime')
        ->save();
    }
}
