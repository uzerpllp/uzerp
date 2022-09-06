<?php


use UzerpPhinx\UzerpMigration;

class UsersAddMfaColumns extends UzerpMigration
{
    /**
     * Add mfa columns to users table
     *
     * @return void
     */
    public function change()
    {
        $table = $this->table('users');
        $table->addColumn('mfa_enrolled', 'boolean', ['null' => true, 'default' => false]);
        $table->addColumn('uuid', 'uuid', ['null' => true]);
        $table->addColumn('mfa_sid', 'string', ['null' => true])
              ->save();
    }
}
