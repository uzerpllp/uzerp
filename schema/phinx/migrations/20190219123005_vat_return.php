<?php


use UzerpPhinx\UzerpMigration;

class VatReturn extends UzerpMigration
{
    /**
     * Create VAT table for HMRC VAT
     */
    public function change()
    {
        $table = $this->table('vat_return');
        $table->addColumn('usercompanyid', 'biginteger')
              ->addColumn('year', 'integer', ['limit' => 4])
              ->addColumn('tax_period', 'integer', ['limit' => 2,])
              ->addColumn('period_key', 'string', ['limit' => 10, 'null' => true])
              ->addColumn('vat_due_sales', 'decimal', ['scale' => 2, 'precision' => 15, 'null' => true])
              ->addColumn('vat_due_aquisitions', 'decimal', ['scale' => 2, 'precision' => 15, 'null' => true])
              ->addColumn('total_vat_due', 'decimal', ['scale' => 2, 'precision' => 15, 'null' => true])
              ->addColumn('vat_reclaimed_curr_period', 'decimal', ['scale' => 2, 'precision' => 15, 'null' => true])
              ->addColumn('net_vat_due', 'decimal', ['scale' => 2, 'precision' => 15, 'null' => true])
              ->addColumn('total_value_sales_ex_vat', 'decimal', ['scale' => 2, 'precision' => 15, 'null' => true])
              ->addColumn('total_value_purchase_ex_vat', 'decimal', ['scale' => 2, 'precision' => 15, 'null' => true])
              ->addColumn('total_value_goods_supplied_ex_vat', 'decimal', ['scale' => 2, 'precision' => 15, 'null' => true])
              ->addColumn('total_aquisitions_ex_vat', 'decimal', ['scale' => 2, 'precision' => 15, 'null' => true])
              ->addColumn('finalised', 'boolean', ['default' => false])
              ->addColumn('processing_date', 'datetime', ['null' => true])
              ->addColumn('form_bundle', 'string', ['limit' => 12, 'null' => true])
              ->addColumn('payment_indicator', 'string', ['limit' => 6, 'null' => true])
              ->addColumn('charge_ref_number', 'string', ['limit' => 16, 'null' => true])
              ->addColumn('receipt_id_header', 'string', ['limit' => 36, 'null' => true])
              ->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
              ->addColumn('createdby', 'string', ['null' => true])
              ->addColumn('alteredby', 'string', ['null' => true])
              ->addColumn('lastupdated', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
              ->addIndex(['year', 'tax_period'], ['unique' => true,])
              ->create();
              
        $this->query("ALTER TABLE vat_return OWNER TO \"www-data\"");
    }
}
