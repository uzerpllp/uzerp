<?php
declare(strict_types=1);

use UzerpPhinx\UzerpMigration;

final class AddCountryOfOriginForSales extends UzerpMigration
{
    /**
     * Add country of origin to so_product_lines_header
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('so_product_lines_header');
        $table->addColumn('country_of_origin', 'string', ['null' => true, 'limit' => 2])
                ->save();
    }
}
