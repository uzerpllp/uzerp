<?php


use UzerpPhinx\UzerpMigration;


/**
 * Phinx Migration - 
 * Create VAT adjustments table to allow adjustments prior to submitting to HMRC
 * Only allow for certain columns on the return to be adjusted
 *
 * @author uzERP LLP and Martyn Shiner <mshiner@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2021 uzERP LLP (support#uzerp.com). All rights reserved.
 */

class VatAdjustmentTable extends UzerpMigration
{
    /**
     * Create VAT adjustments table to allow adjustments prior to submitting to HMRC
     * Only allow for certain columns on the return to be adjusted
     * 
     */
    public function change()
    {
        $table = $this->table('vat_adjustment');
        $table->addColumn('usercompanyid', 'biginteger')
              ->addColumn('vat_return_id', 'integer')
              ->addColumn('reference', 'text', ['null' => false])
              ->addColumn('comment', 'text', ['null' => true])
              ->addColumn('vat_due_sales', 'decimal', ['scale' => 2, 'precision' => 15, 'null' => false, 'default' => 0])
              ->addColumn('vat_reclaimed_curr_period', 'decimal', ['scale' => 2, 'precision' => 15, 'null' => false, 'default' => 0])
              ->addColumn('total_value_sales_ex_vat', 'decimal', ['scale' => 2, 'precision' => 15, 'null' => false, 'default' => 0])
              ->addColumn('total_value_purchase_ex_vat', 'decimal', ['scale' => 2, 'precision' => 15, 'null' => false, 'default' => 0])
              ->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
              ->addColumn('createdby', 'string', ['null' => true])
              ->addColumn('alteredby', 'string', ['null' => true])
              ->addColumn('lastupdated', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
              ->addForeignKey('alteredby', 'users', 'username', ['constraint' => 'vat_adjustments_alteredby_fkey','delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'])
              ->addForeignKey('createdby', 'users', 'username', ['constraint' => 'vat_adjustments_createdby_fkey','delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'])
              ->addForeignKey('usercompanyid', 'system_companies', 'id', ['constraint' => 'vat_adjustments_usercompanyid_fkey','delete'=> 'CASCADE', 'update'=> 'CASCADE'])
              ->addForeignKey('vat_return_id', 'vat_return', 'id', ['constraint' => 'vat_adjustments_vat_return_fkey','delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'])
              ->addIndex(['vat_return_id', 'reference'], ['unique' => true,])
              ->create();
              
        $this->query("ALTER TABLE vat_adjustment OWNER TO \"www-data\"");
    }
}
