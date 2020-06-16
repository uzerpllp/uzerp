<?php

use UzerpPhinx\UzerpMigration;

/**
 * Phinx Migration - Add comp_class and active columns to st_typecodes table
 *
 * @author uzERP LLP and Steve Blamey <sblamey@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2020 uzERP LLP (support#uzerp.com). All rights reserved.
 */
class StTypeCodesAddCompClass extends UzerpMigration
{
    public function change()
    {
        $table = $this->table('st_typecodes');
        $table->addColumn('comp_class', 'text', ['null' => true])
              ->addColumn('active', 'boolean', ['default' => true])
              ->save();
    }
}
