<?php

use UzerpPhinx\UzerpMigration;
/**
 * Phinx Migration
 *
 * Create SO Costs table for sales order product line header costs enhamncement
 *
 * @author uzERP LLP and Martyn Shiner <mshiner@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2000-2017 uzERP LLP (support@uzerp.com). All rights reserved.
 */

class SoCostsTable extends UzerpMigration
{
    public function change()
    {
      $table = $this->table('so_costs');
      $table->addColumn('product_header_id', 'biginteger')
            ->addColumn('cost', 'decimal', ['scale'=>2,'default'=>0])
            ->addColumn('mat', 'decimal', ['scale'=>2,'default'=>0])
            ->addColumn('lab', 'decimal', ['scale'=>2,'default'=>0])
            ->addColumn('osc', 'decimal', ['scale'=>2,'default'=>0])
            ->addColumn('ohd', 'decimal', ['scale'=>2,'default'=>0])
            ->addColumn('time', 'decimal', ['scale'=>2,'default'=>0])
            ->addColumn('time_period', 'char')
            ->addColumn('usercompanyid', 'biginteger')
            ->addColumn('lastupdated', 'timestamp', ['with time zone'=>FALSE, 'default'=>'CURRENT_TIMESTAMP'])
            ->addColumn('alteredby', 'char' ['null' => true])
            ->addColumn('created', 'timestamp', ['with time zone'=>FALSE, 'default'=>'CURRENT_TIMESTAMP'])
            ->addColumn('createdby', 'char', ['null' => true])
            ->addForeignKey('alteredby', 'users', 'username', ['constraint' => 'so_costs_alteredby_fkey','delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'])
            ->addForeignKey('createdby', 'users', 'username', ['constraint' => 'so_costs_createdby_fkey','delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'])
            ->addForeignKey('product_header_id', 'so_product_lines_header', 'id', ['constraint' => 'so_costs_product_header_id','delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'])
            ->addForeignKey('usercompanyid', 'system_companies', 'id', ['constraint' => 'so_costs_usercompanyid_fkey','delete'=> 'CASCADE', 'update'=> 'CASCADE'])
            ->addIndex(['product_header_id'], ['unique' => true,'name' => 'so_costs_so_product_header_id_key'])
            ->create();
    }
}
