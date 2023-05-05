<?php
declare(strict_types=1);

use UzerpPhinx\UzerpMigration;

final class SiHeaderAddDespatchAction extends UzerpMigration
{
    /**
     * Add despatch_action column sales invoice header
     */
    public function change(): void
    {
        $table = $this->table('public.si_header');
        $table->addColumn('despatch_action', 'integer', ['null' => true])
              ->save();
    }
}
