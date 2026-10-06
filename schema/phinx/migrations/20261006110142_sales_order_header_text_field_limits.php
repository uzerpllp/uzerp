<?php
declare(strict_types=1);

use UzerpPhinx\UzerpMigration;
use Phinx\Util\Literal;

final class SalesOrderHeaderTextFieldLimits extends UzerpMigration
{
    /**
     * Update text1 on so_header to type text
     * Update text2, text3 to varchar (removes space padding)
     *
     * @return void
     */
    public function up(): void
    {
        $table = $this->table('public.so_header');
        $table->changeColumn('text1', 'text', ['null' => true])
              ->changeColumn('text2', 'text', ['null' => true])
              ->changeColumn('text3', 'text', ['null' => true])
              ->save();
    }

    /**
     * Revert to type postgres character(50)
     *
     * @return void
     */
    public function down(): void
    {
        $table = $this->table('public.so_header');
        $table->changeColumn('text1', 'text', ['null' => true])
              ->changeColumn('text2', Literal::from('varchar(50)'), ['null' => true])
              ->changeColumn('text3', Literal::from('varchar(50)'), ['null' => true])
              ->save();
    }
}
