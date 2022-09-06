<?php


use UzerpPhinx\UzerpMigration;

class UsersAddMfaDisabled extends UzerpMigration
{
    /**
     * Add mfa_enabled column to users table
     *
     * @return void
     */
    public function change()
    {
        $table = $this->table('users');
        $table->addColumn('mfa_enabled', 'boolean', ['null' => true, 'default' => false])
                ->save();
    }
}
