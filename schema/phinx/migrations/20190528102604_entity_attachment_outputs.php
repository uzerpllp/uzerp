<?php


use UzerpPhinx\UzerpMigration;

class EntityAttachmentOutputs extends UzerpMigration
{
    public function change()
    {
        $table = $this->table('entity_attachment_outputs');
        $table->addColumn('entity_attachment_id', 'integer')
              ->addColumn('tag', 'string')
              ->addColumn('print_order', 'integer', ['default' => 1])
              ->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
              ->addColumn('createdby', 'string', ['null' => true])
              ->addColumn('alteredby', 'string', ['null' => true])
              ->addColumn('lastupdated', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
              ->create();

        $this->query("ALTER TABLE entity_attachment_outputs OWNER TO \"www-data\"");
    }
}
