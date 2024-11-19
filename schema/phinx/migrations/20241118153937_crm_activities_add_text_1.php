<?php
declare(strict_types=1);

use UzerpPhinx\UzerpMigration;
use Phinx\Util\Literal;

final class CrmActivitiesAddText1 extends UzerpMigration
{
    /**
     * Add text1 column to CRM activities
     */
    public function change(): void
    {
        $table = $this->table('public.activities');
        $table->addColumn('text1', Literal::from('varchar(50)'), ['null' => true])
              ->save();
    }
}
