<?php
declare(strict_types=1);

use UzerpPhinx\UzerpMigration;
use Phinx\Util\Literal;

final class PartyContactMethodsAddEmailTrailerExtend extends UzerpMigration
{
    /**
     * Update email_trailer field to allow more characters
     */
    public function change(): void
    {
        $table = $this->table('public.party_contact_methods');
        $table->changeColumn('email_trailer', Literal::from('varchar(4000)'), ['null' => true])
              ->save();
    }
}
