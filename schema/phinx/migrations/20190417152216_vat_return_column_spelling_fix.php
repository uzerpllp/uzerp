<?php

use UzerpPhinx\UzerpMigration;

/**
 * Phinx Migration
 *
 * @author uzERP LLP and Steve Blamey <sblamey@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2019 uzERP LLP (support#uzerp.com). All rights reserved.
 */
class VatReturnColumnSpellingFix extends UzerpMigration
{
    public function change()
    {
        $table = $this->table('vat_return');
        $table->renameColumn('vat_due_aquisitions', 'vat_due_acquisitions')
              ->renameColumn('total_aquisitions_ex_vat', 'total_acquisitions_ex_vat')
              ->save();
    }
}
