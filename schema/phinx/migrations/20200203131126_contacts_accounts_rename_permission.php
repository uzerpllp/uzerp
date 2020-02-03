<?php


use UzerpPhinx\UzerpMigration;

/**
 * Set title of contact companys permission
 */
class ContactsAccountsRenamePermission extends UzerpMigration
{
    public function up()
    {
        $this->execute("update permissions set title = 'Companies' where permission = 'companys' and title = 'Accounts'");
    }

    public function down()
    {
        $this->execute("update permissions set title = 'Accounts' where permission = 'companys' and title = 'Companies'");
    }
}
