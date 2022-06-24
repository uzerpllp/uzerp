<?php


use UzerpPhinx\UzerpMigration;

class SalesInvoiceDelTerm extends UzerpMigration
{
    /**
     * Add delivery term id to Sales Invoice Header table
     *
     * @return void
     */
    public function change()
    {
        $table = $this->table('si_header');
        $table->addColumn('delivery_term_id', 'integer', ['null' => true])
              ->addForeignKey('delivery_term_id', 'sy_delivery_terms', 'id', ['delete' => 'NO ACTION', 'update' => 'NO_ACTION'])
              ->save();
    }
}
