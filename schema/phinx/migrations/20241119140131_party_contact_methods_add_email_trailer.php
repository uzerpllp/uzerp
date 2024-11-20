<?php
declare(strict_types=1);

use UzerpPhinx\UzerpMigration;
use Phinx\Util\Literal;

final class PartyContactMethodsAddEmailTrailer extends UzerpMigration
{
    /**
     * Add email_trailer field ro party_contact_methods
     */
    public function change(): void
    {
        $table = $this->table('public.party_contact_methods');
        $table->addColumn('email_trailer', Literal::from('varchar(500)'), ['null' => true])
              ->save();
    }
}
