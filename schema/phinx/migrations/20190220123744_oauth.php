<?php


use UzerpPhinx\UzerpMigration;

class Oauth extends UzerpMigration
{
    public function change()
    {
        $table = $this->table('oauth');
        $table->addColumn('target_key', 'string')
              ->addColumn('access_token', 'string')
              ->addColumn('refresh_token', 'string')
              ->addcolumn('expires', 'string')
              ->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
              ->addColumn('createdby', 'string', ['null' => true])
              ->addColumn('alteredby', 'string', ['null' => true])
              ->addColumn('lastupdated', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
              ->create();

        $this->query("ALTER TABLE oauth OWNER TO \"www-data\"");
    }
}
